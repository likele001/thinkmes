const api = require('../../utils/api.js');

Page({
  data: {
    list: [],
    total: 0,
    page: 1,
    limit: 20,
    workDate: '',
    loading: false,
    noMore: false,
  },
  onShow() {
    this.setData({ page: 1, noMore: false });
    this.load();
  },
  load() {
    if (this.data.loading || this.data.noMore) return;
    this.setData({ loading: true });
    api.getWages(this.data.page, this.data.limit, this.data.workDate)
      .then((res) => {
        const d = res.data || {};
        const list = this.data.page === 1 ? (d.list || []) : (this.data.list || []).concat(d.list || []);
        const total = d.total || 0;
        this.setData({
          list,
          total,
          loading: false,
          noMore: list.length >= total,
        });
      })
      .catch(() => { this.setData({ loading: false }); });
  },
  onReachBottom() {
    if (this.data.noMore || this.data.loading) return;
    this.setData({ page: this.data.page + 1 });
    this.load();
  },
  onPullDownRefresh() {
    this.setData({ page: 1, noMore: false });
    this.load();
    wx.stopPullDownRefresh();
  },
});
