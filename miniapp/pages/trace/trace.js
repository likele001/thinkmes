const { adminApi } = require('../../utils/api.js');

Page({
  data: { list: [], page: 1, limit: 20, loading: false, noMore: false },

  onLoad() { this.load(); },

  load() {
    if (this.data.loading) return;
    this.setData({ loading: true });
    adminApi.getTraceCodeList(this.data.page, this.data.limit)
      .then((res) => {
        const d = res.data || {};
        const newList = d.list || d.rows || [];
        const list = this.data.page === 1 ? newList : (this.data.list || []).concat(newList);
        const total = d.total || list.length;
        this.setData({ list, loading: false, noMore: list.length >= total });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  onReachBottom() { if (!this.data.noMore && !this.data.loading) { this.setData({ page: this.data.page + 1 }); this.load(); } },

  goIndex() { wx.navigateTo({ url: '/pages/index/index' }); },
  goGenerate() { wx.navigateTo({ url: '/pages/trace-generate/trace-generate' }); },
  goQuery() { wx.navigateTo({ url: '/pages/trace-query/trace-query' }); },
});
