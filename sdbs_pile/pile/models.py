from django.core.exceptions import ValidationError
from django.core.files.storage import FileSystemStorage
from django.db import models
from django.db.models import Count
from model_utils.models import SoftDeletableModel


class Tag(SoftDeletableModel):
    name = models.CharField(max_length=128, null=False, blank=False)
    description = models.TextField(null=False, blank=True)

    @property
    def documents_exclude_hidden(self):
        return Document.exclude_hidden.filter(tags__in=[self])

    def __str__(self):
        return self.name


class DocumentManager(models.Manager):
    def __init__(self, include_hidden=True):
        super(DocumentManager, self).__init__()
        self._include_hidden = include_hidden

    def get_queryset(self):
        if self._include_hidden:
            return super().get_queryset()
        else:
            return super().get_queryset().filter(hidden=False)

    def untagged(self):
        return self.get_queryset().annotate(tag_count=Count('tags')).filter(tag_count=0)


class Document(SoftDeletableModel):
    title = models.CharField(max_length=512, null=False, blank=False)
    description = models.TextField(null=False, blank=True)
    author = models.CharField(max_length=512, null=False, blank=True)
    published = models.CharField(max_length=128, null=False, blank=True)
    external_url = models.URLField(null=True, blank=True)
    file = models.FileField(null=True, blank=True, storage=FileSystemStorage(location='docs'))
    hidden = models.BooleanField(default=False, null=False, blank=False)
    tags = models.ManyToManyField(Tag, related_name="documents", blank=True)
    uploaded = models.DateTimeField(auto_now_add=True, null=True)

    objects = DocumentManager()
    exclude_hidden = DocumentManager(include_hidden=False)

    @property
    def url(self):
        if self.file:
            return f"/docs/{self.file.url}"
        return self.external_url

    class Meta:
        ordering = ['-id']
        permissions = [
            ("see_hidden", "Can see hidden documents")
        ]

    def get_absolute_url(self):
        from django.urls import reverse
        return reverse('pile:document', args=[str(self.id)])

    def clean(self):
        if not (self.file or self.external_url):
            raise ValidationError("An uploaded document or an external URL is required.")

    def __str__(self):
        return f"{self.title}{f' ({self.author})' if self.author else ''}"
