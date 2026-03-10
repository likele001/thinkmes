const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    list: [],
    total: 0,
    page: 1,
    limit: 20,
    statusFilter: '',
    loading: false,
    noMore: false,
  },

  onLoad() {
    this.load();
  },

  setStatus(e) {
    const status = e.currentTarget.dataset.status;
    this.setData({ statusFilter: status, page: 1, list: [], noMore: false });
    this.load();
  },

  load() {
    if (this.data.loading || this.data.noMore) return;
    this.setData({ loading: true });
    const status = this.data.statusFilter;
    adminApi.getReports(this.data.page, this.data.limit, status)
      .then((res) => {
        const d = res.data || {};
        const list = this.data.page === 1 ? (d.list || []) : (this.data.list || []).concat(d.list || []);
        this.setData({
          list,
          total: d.total || 0,
          loading: false,
          noMore: list.length >= (d.total || 0),
        });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  onReachBottom() {
    if (this.data.noMore || this.data.loading) return;
    this.setData({ page: this.data.page + 1 });
    this.load();
  },

  goDetail(e) {
    const id = e.currentTarget.dataset.id;
    wx.navigateTo({ url: '/pages/report-detail/report-detail?report_id=' + id });
  },

  goAudit(e) {
    const id = e.currentTarget.dataset.id;
    wx.navigateTo({ url: '/pages/audit/audit?report_id=' + id });
  },

  confirmDelete(e) {
    const id = e.currentTarget.dataset.id;
    wx.showModal({
      title: '确认删除',
      content: '确定删除该报工记录？',
      success: (res) => {
        if (res.confirm) {
          adminApi.deleteReport(id)
            .then(() => {
              wx.showToast({ title: '已删除' });
              this.setData({ page: 1, list: [], noMore: false });
              this.load();
            })
            .catch(() => {});
        }
      },
    });
  },
});
