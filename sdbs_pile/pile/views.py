# Create your views here.
import io
import logging
from datetime import datetime
from operator import itemgetter
from random import choice

import weasyprint
from PyPDF2 import PdfFileWriter, PdfFileReader
from django.contrib.syndication.views import Feed
from django.core.exceptions import ObjectDoesNotExist, PermissionDenied
from django.http import Http404, FileResponse, HttpRequest
from django.shortcuts import redirect
from django.template.loader import render_to_string
from django.utils.text import slugify
from django.views import View
from django.views.generic import TemplateView

from sdbs_pile.pile.models import Tag, Document


class BasePileView(TemplateView):
    def get_context_data(self, **kwargs):
        tags = list(Tag.objects.all())
        tags.sort(key=lambda tag: tag.name)
        tags = [(tag, tag.documents.count()) for tag in tags]
        tags.sort(key=itemgetter(1), reverse=True)

        return {
            'tags': tags,
            'document_count': Document.objects.count(),
            'untagged_count': Document.objects.untagged().count(),
            'can_see_hidden': self.request.user.has_perm('pile.see_hidden')
        }


class IndexView(BasePileView):
    template_name = "front_intro.html"

    def get_context_data(self, **kwargs):
        base_context_data = super(IndexView, self).get_context_data(**kwargs)

        return {
            'recent_documents': Document.objects.order_by('-uploaded')[:5],
            'random_document': choice(Document.objects.all()[5:]) if Document.objects.count() > 0 else None,
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
            documents = Document.objects.untagged()
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

        if not self.request.user.has_perm('pile.see_hidden') and not document.public:
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


class RecentlyUploadedFeed(Feed):
    title = "The /-\\ pile"
    link = "https://pile.sbds.cz"
    description = "A list of most recently uploaded documents."

    def items(self):
        return Document.objects.exclude_hidden().order_by('-uploaded')[:5]

    def item_title(self, item: Document):
        return item.title

    def item_description(self, item: Document):
        return item.description

    def item_pubdate(self, item: Document):
        return item.uploaded or datetime.now()
