const { adminApi, userApi } = require('../../utils/api.js');

Page({
  data: {
    reportId: 0,
    detail: null,
    loading: true,
    fromUser: false,
  },

  onLoad(options) {
    const id = options.report_id || options.id || 0;
    const fromUser = options.from === 'user';
    if (!id) {
      wx.showToast({ title: '参数错误', icon: 'none' });
      return;
    }
    this.setData({ reportId: id, fromUser });
    if (fromUser) wx.setNavigationBarTitle({ title: '报工详情' });
    this.load();
  },

  fullUrl(url) {
    if (!url || url.indexOf('http') === 0) return url;
    const base = (getApp().globalData.baseUrl || '').replace(/\/api\/?$/, '');
    return base ? base + (url[0] === '/' ? url : '/' + url) : url;
  },

  load() {
    const api = this.data.fromUser ? userApi.getReportDetail(this.data.reportId) : adminApi.getReportDetail(this.data.reportId);
    api.then((res) => {
      const d = res.data || null;
      if (d) {
        const origin = (getApp().globalData.baseUrl || '').replace(/\/api\/?$/, '');
        const toFull = (u) => (!u || u.indexOf('http') === 0 ? u : (origin ? origin + (u[0] === '/' ? u : '/' + u) : u));
        if (d.images) d.images = d.images.map(toFull);
        if (d.audit_images) d.audit_images = d.audit_images.map(toFull);
        if (d.audit_videos) d.audit_videos = d.audit_videos.map(toFull);
        let createTimeStr = '';
        const t = d.create_time;
        if (t != null && !isNaN(Number(t))) {
          const date = new Date(Number(t) * 1000);
          const y = date.getFullYear();
          const m = date.getMonth() + 1;
          const day = date.getDate();
          const h = date.getHours();
          const min = date.getMinutes();
          createTimeStr = y + '-' + (m < 10 ? '0' : '') + m + '-' + (day < 10 ? '0' : '') + day + ' ' + (h < 10 ? '0' : '') + h + ':' + (min < 10 ? '0' : '') + min;
        }
        d.create_time_str = createTimeStr || '-';
      }
      this.setData({ detail: d, loading: false });
    }).catch(() => { this.setData({ loading: false }); });
  },

  goAudit() {
    wx.navigateTo({ url: '/pages/audit/audit?report_id=' + this.data.reportId });
  },

  confirmDelete() {
    wx.showModal({
      title: '确认删除',
      content: '确定删除该报工记录？',
      success: (res) => {
        if (res.confirm) {
          adminApi.deleteReport(this.data.reportId)
            .then(() => {
              wx.showToast({ title: '已删除' });
              setTimeout(() => wx.navigateBack(), 500);
            })
            .catch(() => {});
        }
      },
    });
  },
});
