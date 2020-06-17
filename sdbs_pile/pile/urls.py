from django.urls import path

from . import views
from .views import IPFSView

app_name = 'pile'
urlpatterns = [
    path('', views.IndexView.as_view(), name='index'),
    path('tag/<name_or_id>', views.TagView.as_view(), name='tag'),
    path('item/<int:document_id>', views.DocumentView.as_view(), name='document'),
    path('label/<int:document_id>', views.LabelView.as_view(), name='label'),
    path('retrieve/<int:document_id>', views.DocumentWithLabelView.as_view(), name='retrieve'),
    path('feed', views.RecentlyUploadedFeed()),
    path('api/ipfs_cids', IPFSView)
]
