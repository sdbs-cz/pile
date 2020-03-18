from django.core.exceptions import ValidationError
from django.core.files.storage import FileSystemStorage
from django.db import models
from model_utils.models import SoftDeletableModel


class Tag(SoftDeletableModel):
    name = models.CharField(max_length=128, null=False, blank=False)
    description = models.TextField(null=False, blank=True)

    def __str__(self):
        return self.name


class Document(SoftDeletableModel):
    title = models.CharField(max_length=512, null=False, blank=False)
    description = models.TextField(null=False, blank=True)
    author = models.CharField(max_length=512, null=False, blank=True)
    published = models.CharField(max_length=128, null=False, blank=True)
    external_url = models.URLField(null=True, blank=True)
    file = models.FileField(null=True, blank=True, storage=FileSystemStorage(location='docs'))
    tags = models.ManyToManyField(Tag, related_name="documents")
    uploaded = models.DateTimeField(auto_now_add=True, null=True)

    class Meta:
        ordering = ['-id']

    @property
    def url(self):
        if self.file:
            return self.file.url
        return self.external_url

    def clean(self):
        if not (self.file or self.external_url):
            raise ValidationError("An uploaded document or an external URL is required.")

    def __str__(self):
        return f"{self.title}{f' ({self.author})' if self.author else ''}"
