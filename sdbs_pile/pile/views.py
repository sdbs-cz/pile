# Create your views here.
from django.db.models import Count
from django.views.generic import TemplateView

from sdbs_pile.pile.models import Tag, Document


class BasePileView(TemplateView):
    def get_context_data(self, **kwargs):
        return {
            'tags': sorted(sorted(Tag.objects.all(), key=lambda tag: tag.name),
                           key=lambda tag: tag.documents.count(), reverse=True),
            'document_count': Document.objects.count(),
            'untagged_count': Document.objects.annotate(tag_count=Count('tags')).filter(tag_count__gt=0).count()
        }


class IndexView(BasePileView):
    template_name = "front_intro.html"

    def get_context_data(self, **kwargs):
        base_context_data = super(IndexView, self).get_context_data(**kwargs)

        return {
            'recent_documents': Document.objects.order_by('-uploaded')[:5],
            **base_context_data
        }


class TagView(BasePileView):
    template_name = "front_doc_listing.html"

    def get_context_data(self, name_or_id: str):
        base_context_data = super(TagView, self).get_context_data()

        if name_or_id == "*":
            tag = None
            documents = Document.objects.all()
        else:
            try:
                tag = Tag.objects.get(id=int(name_or_id))
            except ValueError:
                tag = Tag.objects.get(name=name_or_id)
            documents = tag.documents.all()

        return {
            'tag': tag,
            'documents': documents,
            **base_context_data
        }


class DocumentView(BasePileView):
    template_name = "front_doc_detail.html"

    def get_context_data(self, document_id: int):
        base_context_data = super(DocumentView, self).get_context_data()

        return {
            'document': Document.objects.get(pk=document_id),
            **base_context_data
        }
