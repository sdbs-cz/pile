# Generated by Django 3.0.4 on 2020-06-09 08:32

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('pile', '0010_document_media_type'),
    ]

    operations = [
        migrations.AddField(
            model_name='document',
            name='related',
            field=models.ManyToManyField(related_name='_document_related_+', to='pile.Document'),
        ),
    ]