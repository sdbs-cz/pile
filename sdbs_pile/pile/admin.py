from django.contrib import admin
from django.db.models import Q

from sdbs_pile.pile.models import Tag, Document


class TagAdmin(admin.ModelAdmin):
    exclude = ('is_removed',)
    list_display = ('name', 'document_count')

    @staticmethod
    def document_count(tag: Tag):
        return tag.documents.count()


class DocumentExternalListFilter(admin.SimpleListFilter):
    title = 'document location'
    parameter_name = 'external'

    def lookups(self, request, model_admin):
        return (
            (False, 'Local'),
            (True, 'External'),
        )

    def queryset(self, request, queryset):
        if self.value() == "True":
            return queryset.filter(Q(external_url__isnull=False) & ~Q(external_url__contains="pile.sdbs.cz"))
        else:
            return queryset.filter(Q(external_url__isnull=False) | Q(external_url__contains="pile.sdbs.cz"))


class DocumentAdmin(admin.ModelAdmin):
    exclude = ('is_removed',)
    list_filter = ('tags', DocumentExternalListFilter)
    search_fields = ('title', 'author', 'published')
    list_display = ('title', 'author', 'published', 'filed_under')

    @staticmethod
    def filed_under(document: Document):
        return ", ".join(tag.name for tag in document.tags.all())


admin.site.register(Tag, TagAdmin)
admin.site.register(Document, DocumentAdmin)
