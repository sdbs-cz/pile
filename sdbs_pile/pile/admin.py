from django.contrib import admin

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
            return queryset.external()
        elif self.value() == "False":
            return queryset.local()
        else:
            return queryset


class DocumentAdmin(admin.ModelAdmin):
    exclude = ('is_removed',)
    list_display = ('title', 'author', 'published', 'has_file', 'public', 'filed_under')
    list_filter = ('tags', 'public', DocumentExternalListFilter)
    search_fields = ('title', 'author', 'published')
    actions = ('make_published', 'make_hidden')

    def has_file(self, document: Document):
        return document.file is not None and document.file != ''

    has_file.boolean = True

    @staticmethod
    def filed_under(document: Document):
        return ", ".join(tag.name for tag in document.tags.all())

    def make_published(self, _, queryset):
        queryset.update(public=True)

    make_published.short_description = "Mark selected articles as public"

    def make_hidden(self, _, queryset):
        queryset.update(public=False)

    make_hidden.short_description = "Mark selected articles as hidden"


admin.site.site_title = '/-\\ pile'
admin.site.site_header = '/-\\ pile administration'

admin.site.register(Tag, TagAdmin)
admin.site.register(Document, DocumentAdmin)
