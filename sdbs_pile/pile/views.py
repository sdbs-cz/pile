# Create your views here.
import io
import logging
import re
from datetime import datetime
from operator import itemgetter
from random import choice

import weasyprint
from PIL import Image
from PyPDF2 import PdfFileWriter, PdfFileReader
from django.contrib.staticfiles import finders
from django.contrib.syndication.views import Feed
from django.core.exceptions import ObjectDoesNotExist, PermissionDenied
from django.http import Http404, FileResponse, HttpRequest, HttpResponse
from django.shortcuts import redirect
from django.template.loader import render_to_string
from django.utils.text import slugify
from django.views import View
from django.views.generic import TemplateView

from sdbs_pile import settings
from sdbs_pile.pile.models import Tag, Document, DocumentLink


class BasePileView(TemplateView):
    def get_context_data(self, **kwargs):
        tags = list(Tag.objects.all())
        tags.sort(key=lambda tag: tag.name)
        tags = [(tag, tag.documents.count()) for tag in tags]
        tags.sort(key=itemgetter(1), reverse=True)

        return {
            'tags': tags,
            'document_count': Document.objects.count(),
            'untagged_count': Document.objects.all().untagged().count(),
            'can_see_hidden': settings.STATIC_PILE or self.request.user.has_perm('pile.see_hidden'),
            'STATIC_PILE': settings.STATIC_PILE
        }


class IndexView(BasePileView):
    template_name = "front_intro.html"

    def get_context_data(self, **kwargs):
        base_context_data = super(IndexView, self).get_context_data(**kwargs)

        return {
            'recent_documents': Document.objects.order_by('-uploaded')[:10],
            'random_document': choice(Document.objects.all()[5:]) \
                if not settings.STATIC_PILE and Document.objects.count() > 0 else None,
            **base_context_data
        }


class TagView(BasePileView):
    template_name = "front_doc_listing.html"

    def get_context_data(self, name_or_id: str):
        base_context_data = super(TagView, self).get_context_data()

        if name_or_id == "*":
            tag = None
            documents = Document.objects.all()
        elif name_or_id == "_":
            tag = "UNTAGGED"
            documents = Document.objects.all().untagged()
        else:
            try:
                try:
                    tag = Tag.objects.get(id=int(name_or_id))
                except ValueError:
                    tag = Tag.objects.get(name=name_or_id)
                documents = tag.documents.all()
            except ObjectDoesNotExist:
                raise Http404

        status_documents = {s: [] for s in Document.DocumentStatus}
        for document in documents:
            status_documents[document.status] += [document]

        return {
            'tag': tag if tag != "UNTAGGED" else None,
            'untagged': tag == "UNTAGGED",
            'status_documents': status_documents,
            **base_context_data
        }


class DocumentView(BasePileView):
    template_name = "front_doc_detail.html"

    def get_context_data(self, document_id: int):
        base_context_data = super(DocumentView, self).get_context_data()

        try:
            document = Document.objects.get(pk=document_id)
        except ObjectDoesNotExist:
            raise Http404

        return {
            'document': document,
            **base_context_data
        }


class LabelView(View):
    def get(self, request: HttpRequest, document_id: int):
        try:
            document = Document.objects.get(pk=document_id)
        except ObjectDoesNotExist:
            raise Http404

        label_stream = self.get_label(request, document)
        return FileResponse(label_stream,
                            filename=f"pile_{document.id}__label__{slugify(document.title)}.pdf",
                            content_type="application/pdf")

    @staticmethod
    def get_label(request, document):
        label_html = render_to_string("label.html", {'document': document})

        stream = io.BytesIO()
        weasyprint.HTML(base_url=request.build_absolute_uri(), string=label_html).write_pdf(stream)
        stream.seek(0)
        return stream


class DocumentWithLabelView(View):
    def get(self, request: HttpRequest, document_id: int):
        try:
            document = Document.objects.get(pk=document_id)
        except ObjectDoesNotExist:
            raise Http404

        if not self.request.user.has_perm('pile.see_hidden') and not document.public and not settings.STATIC_PILE:
            raise PermissionDenied()

        if document.is_local_pdf:
            try:
                label_stream = LabelView.get_label(request, document)

                with open(document.file.path, 'rb') as document_fp:
                    writer = PdfFileWriter()
                    for reader in map(PdfFileReader, (label_stream, document_fp)):
                        for n in range(reader.getNumPages()):
                            writer.addPage(reader.getPage(n))
                    writer.addMetadata({u'/Title': f"/-\\ pile #{document.id}: {document.title}"})
                    final_stream = io.BytesIO()
                    writer.write(final_stream)

                final_stream.seek(0)
                return FileResponse(final_stream,
                                    filename=f"pile_{document.id}__label__{slugify(document.title)}.pdf",
                                    content_type="application/pdf")
            except Exception as exc:
                logging.exception(exc)

        return redirect(document.url)


class BrandedImageView(View):
    def get(self, request: HttpRequest, document_id: int):
        try:
            document = Document.objects.get(pk=document_id)
        except ObjectDoesNotExist:
            raise Http404

        margin = 32
        pile_image = Image.open(finders.find('pile_300dpi.png')).resize((256 - margin, 256 - margin))
        image = Image.open(document.image) if document.image else document.image_first_page

        result = Image.new('RGBA', (256, 256), (0, 0, 0, 0))

        if image:
            image.thumbnail((256, 256))
            result.paste(image, ((256 - image.size[0]) // 2, (256 - image.size[1]) // 2))

        result.paste(pile_image, (margin//2, margin//2), pile_image)

        if image:
            result = result.crop(result.getbbox())

        img_byte_arr = io.BytesIO()
        result.save(img_byte_arr, format='PNG')
        img_byte_arr = img_byte_arr.getvalue()

        return HttpResponse(img_byte_arr, content_type="image/png")


class RecentlyUploadedFeed(Feed):
    title = "The /-\\ pile"
    link = "https://pile.sbds.cz"
    description = "A list of most recently uploaded documents."

    def items(self):
        return Document.objects.order_by('-uploaded')[:5]

    def item_title(self, item: Document):
        return item.title

    def item_description(self, item: Document):
        return item.html_description

    def item_pubdate(self, item: Document):
        return item.uploaded or datetime.now()


def ExternalLinkView(request: HttpRequest):
    external_links = DocumentLink.objects.order_by('order', '-document_id').values_list("url", flat=True)
    external_links = [link for link in external_links if "pile.sdbs.cz" not in link]
    return HttpResponse("\n".join(external_links), content_type='text/plain')


def IPFSView(request: HttpRequest):
    ipfs_matches = [re.search(r'Qm[\w]{44}', link.url)
                    for link in DocumentLink.objects.order_by('order', '-document_id') if 'ipfs' in link.url]
    ipfs_cids = [match.group(0) for match in ipfs_matches if match]
    return HttpResponse("\n".join(ipfs_cids), content_type='text/plain')
