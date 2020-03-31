# Create your views here.
import io
from operator import itemgetter

import weasyprint
from django.contrib.syndication.views import Feed
from django.core.exceptions import ObjectDoesNotExist
from django.http import Http404, FileResponse
from django.template.loader import render_to_string
from django.utils.text import slugify
from django.views import View
from django.views.generic import TemplateView

from sdbs_pile.pile.models import Tag, Document


class BasePileViewMixin(View):
    @property
    def include_hidden(self):
        return self.request.user.has_perm('document.see_hidden')

    @property
    def documents(self):
        return Document.objects if self.include_hidden else Document.exclude_hidden


class BasePileView(BasePileViewMixin, TemplateView):
    def get_context_data(self, **kwargs):
        tags = list(Tag.objects.all())
        tags.sort(key=lambda tag: tag.name)
        tags = [(tag, (tag.documents if self.include_hidden else tag.documents_exclude_hidden).count()) for tag in tags]
        tags.sort(key=itemgetter(1), reverse=True)

        return {
            'tags': tags,
            'document_count': self.documents.count(),
            'untagged_count': self.documents.untagged().count()
        }


class IndexView(BasePileView):
    template_name = "front_intro.html"

    def get_context_data(self, **kwargs):
        base_context_data = super(IndexView, self).get_context_data(**kwargs)

        return {
            'recent_documents': self.documents.order_by('-uploaded')[:5],
            **base_context_data
        }


class TagView(BasePileView):
    template_name = "front_doc_listing.html"

    def get_context_data(self, name_or_id: str):
        base_context_data = super(TagView, self).get_context_data()

        if name_or_id == "*":
            tag = None
            documents = self.documents.all()
        elif name_or_id == "_":
            tag = "UNTAGGED"
            documents = self.documents.untagged()
        else:
            try:
                try:
                    tag = Tag.objects.get(id=int(name_or_id))
                except ValueError:
                    tag = Tag.objects.get(name=name_or_id)
                documents = tag.documents.all() if self.include_hidden else tag.documents_exclude_hidden
            except ObjectDoesNotExist:
                raise Http404

        return {
            'tag': tag if tag != "UNTAGGED" else None,
            'untagged': tag == "UNTAGGED",
            'documents': documents,
            **base_context_data
        }


class DocumentView(BasePileView):
    template_name = "front_doc_detail.html"

    def get_context_data(self, document_id: int):
        base_context_data = super(DocumentView, self).get_context_data()

        try:
            document = self.documents.get(pk=document_id)
        except ObjectDoesNotExist:
            raise Http404

        return {
            'document': document,
            **base_context_data
        }


class DocumentWithLabel(BasePileViewMixin):
    def get(self, request, document_id: int):
        try:
            document = self.documents.get(pk=document_id)
        except ObjectDoesNotExist:
            raise Http404

        label_html = render_to_string("label.html", {'document': document})

        stream = io.BytesIO()
        weasyprint.HTML(base_url=request.build_absolute_uri(), string=label_html).write_pdf(stream)
        stream.seek(0)

        return FileResponse(stream,
                            filename=f"pile_{document.id}__{slugify(document.title)}.pdf",
                            content_type="application/pdf")


class RecentlyUploadedFeed(Feed):
    title = "The /-\\ pile"
    link = "https://pile.sbds.cz"
    description = "A list of most recently uploaded documents."

    def items(self):
        return Document.exclude_hidden.order_by('-uploaded')[:5]

    def item_title(self, item):
        return item.title

    def item_description(self, item):
        return item.description
