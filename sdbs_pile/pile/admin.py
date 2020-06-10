from django import forms
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


class DocumentAdminForm(forms.ModelForm):
    related = forms.ModelMultipleChoiceField(queryset=Document.objects.none(), required=False)

    class Meta:
        model = Document
        fields = '__all__'

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields['related'].queryset = Document.objects.exclude(pk=self.instance.pk)


class DocumentAdmin(admin.ModelAdmin):
    exclude = ('is_removed',)
    list_display = ('title', 'author', 'published', 'media_type', 'status', 'has_file', 'public', 'filed_under')
    list_filter = ('tags', 'media_type', 'status', DocumentExternalListFilter, 'public')
    search_fields = ('title', 'author', 'published')
    actions = ('make_published', 'make_hidden')
    form = DocumentAdminForm

    def has_file(self, document: Document):
        return document.file is not None and str(document.file).strip() != ''

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
