const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    reportId: 0,
    detail: null,
    status: 1,
    qualityStatus: 1,
    auditReason: '',
    auditNotes: '',
    uploadedAuditImages: [],
    uploadedAuditVideos: [],
    submitting: false,
    loading: true,
  },

  onLoad(options) {
    const id = options.report_id || options.id || 0;
    if (!id) return;
    this.setData({ reportId: id });
    adminApi.getReportDetail(id)
      .then((res) => {
        const d = res.data || null;
        if (d) {
          const origin = (getApp().globalData.baseUrl || '').replace(/\/api\/?$/, '');
          const toFull = (u) => (typeof u !== 'string' || u.indexOf('http') === 0 ? u : (origin ? origin + (u[0] === '/' ? u : '/' + u) : u));
          if (d.images) d.images = d.images.map(toFull);
          if (d.audit_images) d.audit_images = d.audit_images.map(toFull);
          if (d.audit_videos) d.audit_videos = d.audit_videos.map(toFull);
        }
        this.setData({ detail: d, loading: false });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  radioChange(e) {
    this.setData({ status: parseInt(e.detail.value, 10) });
  },
  qualityChange(e) {
    this.setData({ qualityStatus: parseInt(e.detail.value, 10) });
  },
  inputReason(e) {
    this.setData({ auditReason: e.detail.value });
  },
  inputNotes(e) {
    this.setData({ auditNotes: e.detail.value });
  },

  chooseAuditImage() {
    wx.chooseMedia({
      count: 9 - (this.data.uploadedAuditImages || []).length,
      mediaType: ['image'],
      success: (res) => {
        const files = res.tempFiles || [];
        const list = (this.data.uploadedAuditImages || []).slice();
        if (files.length === 0) return;
        wx.showLoading({ title: '上传中...' });
        let done = 0;
        const total = files.length;
        files.forEach((f) => {
          adminApi.uploadAuditImage(f.tempFilePath)
            .then((url) => {
              list.push(url);
              done++;
              if (done >= total) {
                wx.hideLoading();
                this.setData({ uploadedAuditImages: list });
              }
            })
            .catch(() => {
              done++;
              if (done >= total) wx.hideLoading();
            });
        });
      },
    });
  },
  delAuditImage(e) {
    const i = e.currentTarget.dataset.index;
    const list = (this.data.uploadedAuditImages || []).slice();
    list.splice(i, 1);
    this.setData({ uploadedAuditImages: list });
  },
  chooseAuditVideo() {
    wx.chooseMedia({
      count: 3 - (this.data.uploadedAuditVideos || []).length,
      mediaType: ['video'],
      success: (res) => {
        const files = res.tempFiles || [];
        const list = (this.data.uploadedAuditVideos || []).slice();
        if (files.length === 0) return;
        wx.showLoading({ title: '上传中...' });
        let done = 0;
        const total = files.length;
        files.forEach((f) => {
          adminApi.uploadAuditVideo(f.tempFilePath)
            .then((url) => {
              list.push(url);
              done++;
              if (done >= total) {
                wx.hideLoading();
                this.setData({ uploadedAuditVideos: list });
              }
            })
            .catch(() => {
              done++;
              if (done >= total) wx.hideLoading();
            });
        });
      },
    });
  },
  delAuditVideo(e) {
    const i = e.currentTarget.dataset.index;
    const list = (this.data.uploadedAuditVideos || []).slice();
    list.splice(i, 1);
    this.setData({ uploadedAuditVideos: list });
  },

  submit() {
    const { reportId, status, auditReason, qualityStatus, auditNotes, uploadedAuditImages, uploadedAuditVideos } = this.data;
    if (status === 2 && !auditReason.trim()) {
      wx.showToast({ title: '拒绝请填写原因', icon: 'none' });
      return;
    }
    this.setData({ submitting: true });
    adminApi.auditReport(reportId, status, auditReason.trim(), auditNotes.trim(), qualityStatus, uploadedAuditImages || [], uploadedAuditVideos || [])
      .then(() => {
        this.setData({ submitting: false });
        wx.showToast({ title: '审核成功' });
        setTimeout(() => wx.navigateBack(), 1000);
      })
      .catch(() => { this.setData({ submitting: false }); });
  },
});
