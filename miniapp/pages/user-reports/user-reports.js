const { userApi } = require('../../utils/api.js');

Page({
  data: {
    records: [],
    loading: true,
    page: 1,
    limit: 20,
    hasMore: true,
    totalQuantity: 0,
    totalWage: 0,
    pendingCount: 0,
  },

  onLoad() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    this.load();
  },

  onShow() {
    if (getApp().checkUserLogin()) this.load();
  },

  onPullDownRefresh() {
    this.setData({ page: 1, hasMore: true });
    this.load().then(() => wx.stopPullDownRefresh());
  },

  load() {
    this.setData({ loading: true });
    const { page, limit } = this.data;
    return userApi.getReports(page, limit)
      .then((res) => {
        const raw = res.data;
        const list = raw && (raw.rows || raw.list || raw.data || Array.isArray(raw) ? (raw.rows || raw.list || raw.data || raw) : []) || [];
        const records = Array.isArray(list) ? list : [];
        const totalQuantity = records.reduce((s, r) => s + (Number(r.quantity) || 0), 0);
        const totalWage = records.reduce((s, r) => s + (Number(r.wage) || 0), 0);
        const pendingCount = records.filter(r => r.status !== 1).length;
        this.setData({
          records: page === 1 ? records : [...(this.data.records || []), ...records],
          loading: false,
          hasMore: records.length >= limit,
          totalQuantity,
          totalWage: totalWage.toFixed(2),
          pendingCount,
        });
      })
      .catch(() => this.setData({ loading: false }));
  },

  loadMore() {
    if (this.data.loading || !this.data.hasMore) return;
    this.setData({ page: this.data.page + 1 });
    this.load();
  },

  refresh() {
    this.setData({ page: 1, hasMore: true });
    this.load();
  },
});
