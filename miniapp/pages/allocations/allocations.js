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
    adminApi.getAllocations(this.data.page, this.data.limit)
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
});
