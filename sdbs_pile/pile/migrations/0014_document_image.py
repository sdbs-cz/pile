# Generated by Django 3.0.4 on 2021-01-02 11:34

import django.core.files.storage
from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('pile', '0013_auto_20200727_1533'),
    ]

    operations = [
        migrations.AddField(
            model_name='document',
            name='image',
            field=models.ImageField(blank=True, null=True, storage=django.core.files.storage.FileSystemStorage(location='docs/images'), upload_to=''),
        ),
    ]