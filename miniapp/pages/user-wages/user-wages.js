const { userApi } = require('../../utils/api.js');

Page({
  data: {
    wageList: [],
    loading: true,
    page: 1,
    limit: 20,
    hasMore: true,
    workDate: '',
    selectedMonth: '',
    totalWage: '0',
    totalQuantity: 0,
  },

  onLoad() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    const now = new Date();
    const ym = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    this.setData({ selectedMonth: ym });
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
    const { page, limit, workDate } = this.data;
    return userApi.getWages(page, limit, workDate || undefined)
      .then((res) => {
        const raw = res.data;
        const list = raw && (raw.rows || raw.list || raw.data || (Array.isArray(raw) ? raw : [])) || [];
        const wageList = Array.isArray(list) ? list : [];
        const totalQuantity = wageList.reduce((s, r) => s + (Number(r.quantity) || 0), 0);
        const totalWage = wageList.reduce((s, r) => s + (Number(r.wage) || 0), 0).toFixed(2);
        this.setData({
          wageList: page === 1 ? wageList : [...(this.data.wageList || []), ...wageList],
          loading: false,
          hasMore: wageList.length >= limit,
          totalWage,
          totalQuantity,
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
