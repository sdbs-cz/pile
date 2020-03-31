from django.urls import path

from . import views

app_name = 'pile'
urlpatterns = [
    path('', views.IndexView.as_view(), name='index'),
    path('tag/<name_or_id>', views.TagView.as_view(), name='tag'),
    path('item/<int:document_id>', views.DocumentView.as_view(), name='document'),
    path('label/<int:document_id>', views.DocumentWithLabel.as_view(), name='label'),
    path('feed', views.RecentlyUploadedFeed())
]
