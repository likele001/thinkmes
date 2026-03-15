const { userApi } = require('../../utils/api.js');
const { toFullImageUrls } = require('../../utils/image.js');

Page({
  data: { id: 0, detail: {}, loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) this.load(id);
  },

  load(reportId) {
    const id = reportId != null && reportId > 0 ? reportId : this.data.id;
    if (!id || id <= 0) return;
    this.setData({ loading: true });
    userApi.getReportDetail(id)
      .then((res) => {
        const raw = res.data;
        const detail = raw && typeof raw === 'object' ? raw : {};
        if (detail.images && detail.images.length) detail.images = toFullImageUrls(detail.images);
        if (detail.audit_images && detail.audit_images.length) detail.audit_images = toFullImageUrls(detail.audit_images);
        if (detail.audit_videos && detail.audit_videos.length) detail.audit_videos = toFullImageUrls(detail.audit_videos);
        this.setData({ detail, loading: false });
      })
      .catch(() => { this.setData({ detail: {}, loading: false }); });
  },

  goBack() { wx.navigateBack(); },
  previewImage(e) {
    const url = e.currentTarget.dataset.url;
    const urls = e.currentTarget.dataset.urls || [url];
    if (url) wx.previewImage({ current: url, urls: Array.isArray(urls) ? urls : [url] });
  },
});
