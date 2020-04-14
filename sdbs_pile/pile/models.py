import bleach
import markdown2
from django.core.exceptions import ValidationError
from django.core.files.storage import FileSystemStorage
from django.db import models
from django.db.models import Count, Q, QuerySet
from model_utils.models import SoftDeletableModel


class Tag(SoftDeletableModel):
    name = models.CharField(max_length=128, null=False, blank=False)
    description = models.TextField(null=False, blank=True)

    @property
    def documents_exclude_hidden(self):
        return Document.objects.exclude_hidden().filter(tags__in=[self])

    def __str__(self):
        return self.name


class DocumentQuerySet(QuerySet):
    def exclude_hidden(self):
        return super().filter(public=True)

    def untagged(self):
        return super().annotate(tag_count=Count('tags')).filter(tag_count=0)

    def local(self):
        return super().filter((Q(file__isnull=False) & ~Q(file='')) | Q(external_url__contains="pile.sdbs.cz"))

    def external(self):
        return super().filter((Q(file__isnull=True) | Q(file='')) & ~Q(external_url__contains="pile.sdbs.cz"))


class Document(SoftDeletableModel):
    class DocumentStatus(models.TextChoices):
        REFERENCE = "REF", "Referential"
        STANDARD = "STD", "Standard"
        FRAGMENT = "FRG", "Fragment"

    title = models.CharField(max_length=512, null=False, blank=False)
    author = models.CharField(max_length=512, null=False, blank=True)
    published = models.CharField(max_length=128, null=False, blank=True)
    description = models.TextField(max_length=2048, null=False, blank=True)
    external_url = models.URLField(null=True, blank=True)
    file = models.FileField(null=True, blank=True, storage=FileSystemStorage(location='docs'))
    public = models.BooleanField(default=True, null=False, blank=False)
    status = models.CharField(null=False, blank=False,
                              max_length=3, choices=DocumentStatus.choices, default=DocumentStatus.STANDARD)
    tags = models.ManyToManyField(Tag, related_name="documents", blank=True)
    uploaded = models.DateTimeField(auto_now_add=True, null=True)

    objects = DocumentQuerySet.as_manager()

    @property
    def html_description(self):
        return markdown2.markdown(self.description)

    @property
    def plain_description(self):
        return bleach.clean(self.html_description, tags=[], strip=True)

    @property
    def url(self):
        if self.file:
            return f"/docs/{self.file.url}"
        return self.external_url

    @property
    def is_local_pdf(self):
        return self.file.name is not None and self.file.name.endswith(".pdf")

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
