const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    list: [],
    total: 0,
    page: 1,
    limit: 20,
    loading: false,
    noMore: false,
  },

  onLoad() {
    this.load();
  },

  load() {
    if (this.data.loading || this.data.noMore) return;
    this.setData({ loading: true });
    adminApi.getActiveReports(this.data.page, this.data.limit)
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

  goAudit(e) {
    const id = e.currentTarget.dataset.id;
    wx.navigateTo({ url: '/pages/audit/audit?report_id=' + id });
  },
  goIndex() { wx.navigateTo({ url: '/pages/index/index' }); },
  goReports() { wx.navigateTo({ url: '/pages/reports/reports' }); },
  goAllocations() { wx.navigateTo({ url: '/pages/allocations/allocations' }); },
  refresh() { this.setData({ page: 1, list: [], noMore: false }); this.load(); },
  loadMore() {
    if (this.data.noMore || this.data.loading) return;
    this.setData({ page: this.data.page + 1 });
    this.load();
  },
});
