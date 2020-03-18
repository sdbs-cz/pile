from django.contrib import admin

from sdbs_pile.pile.models import Tag, Document


class TagAdmin(admin.ModelAdmin):
    exclude = ('is_removed',)

    @staticmethod
    def document_count(tag: Tag):
        return tag.documents.count()

    list_display = ('name', 'document_count')


class DocumentAdmin(admin.ModelAdmin):
    exclude = ('is_removed',)

    @staticmethod
    def filed_under(document: Document):
        return ", ".join(tag.name for tag in document.tags.all())

    list_display = ('title', 'filed_under')


admin.site.register(Tag, TagAdmin)
admin.site.register(Document, DocumentAdmin)
