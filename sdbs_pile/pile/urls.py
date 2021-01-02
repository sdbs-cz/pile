from django.urls import path

from . import views
from .views import IPFSView, ExternalLinkView

app_name = 'pile'
urlpatterns = [
    path('', views.IndexView.as_view(), name='index'),
    path('tag/<name_or_id>', views.TagView.as_view(), name='tag'),
    path('item/<int:document_id>', views.DocumentView.as_view(), name='document'),
    path('label/<int:document_id>', views.LabelView.as_view(), name='label'),
    path('retrieve/<int:document_id>', views.DocumentWithLabelView.as_view(), name='retrieve'),
    path('image/<int:document_id>', views.BrandedImageView.as_view(), name='image'),
    path('feed', views.RecentlyUploadedFeed()),
    path('api/external_links', ExternalLinkView),
    path('api/ipfs_cids', IPFSView)
]
