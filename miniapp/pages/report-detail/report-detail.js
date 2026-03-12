const { adminApi } = require('../../utils/api.js');
const { toFullImageUrls } = require('../../utils/image.js');

Page({
  data: { id: 0, detail: {}, loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) this.load();
  },

  load() {
    this.setData({ loading: true });
    adminApi.getReportDetail(this.data.id)
      .then((res) => {
        const raw = res.data;
        const detail = raw && (typeof raw === 'object') ? raw : {};
        // 接口返回的图片多为相对路径 /uploads/...，小程序需用完整 https 地址
        if (detail.images && detail.images.length) detail.images = toFullImageUrls(detail.images);
        if (detail.audit_images && detail.audit_images.length) detail.audit_images = toFullImageUrls(detail.audit_images);
        if (detail.audit_videos && detail.audit_videos.length) detail.audit_videos = toFullImageUrls(detail.audit_videos);
        this.setData({ detail, loading: false });
      })
      .catch(() => { this.setData({ detail: null, loading: false }); });
  },

  goBack() { wx.navigateBack(); },
  previewImage(e) {
    const url = e.currentTarget.dataset.url;
    const urls = e.currentTarget.dataset.urls || [url];
    if (url) wx.previewImage({ current: url, urls: Array.isArray(urls) ? urls : [url] });
  },
  audit(e) {
    const status = e.currentTarget.dataset.status;
    adminApi.auditReport(this.data.id, parseInt(status, 10)).then(() => {
      wx.showToast({ title: status === '1' ? '已通过' : '已拒绝' });
      this.load();
    }).catch(() => {});
  },
});
