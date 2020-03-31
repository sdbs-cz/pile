# Generated by Django 3.0.4 on 2020-04-01 10:21
from urllib.parse import unquote

from django.db import migrations


# noinspection PyPep8Naming
def localize_file(apps, schema_editor):
    Document = apps.get_model('pile', 'Document')
    for document in Document.objects.all():
        if not bool(document.file) and "pile.sdbs.cz" in document.external_url:
            document.file.name = unquote(document.external_url.split("/")[-1])
            document.save()


class Migration(migrations.Migration):
    dependencies = [
        ('pile', '0007_document_status'),
    ]

    operations = [
        migrations.RunPython(localize_file)
    ]
