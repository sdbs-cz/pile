# Generated by Django 3.0.4 on 2020-03-29 09:32

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('pile', '0005_auto_20200320_1329'),
    ]

    operations = [
        migrations.RemoveField(
            model_name='document',
            name='hidden',
        ),
        migrations.AddField(
            model_name='document',
            name='public',
            field=models.BooleanField(default=True),
        ),
        migrations.AlterField(
            model_name='document',
            name='tags',
            field=models.ManyToManyField(blank=True, related_name='documents', to='pile.Tag'),
        ),
    ]
