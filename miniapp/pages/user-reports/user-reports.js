const { userApi } = require('../../utils/api.js');

Page({
  data: {
    list: [],
    total: 0,
    page: 1,
    limit: 20,
    loading: false,
    noMore: false,
  },

  onShow() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    this.setData({ page: 1, noMore: false });
    this.load();
  },

  load() {
    if (this.data.loading || this.data.noMore) return;
    this.setData({ loading: true });
    userApi.getReports(this.data.page, this.data.limit)
      .then((res) => {
        const d = res.data || {};
        const rawList = d.list || [];
        const list = rawList.map((item) => {
          const t = item.create_time;
          let str = '';
          if (t) {
            const date = new Date(t * 1000);
            str = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate() + ' ' + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes();
          }
          return { ...item, create_time_str: str };
        });
        const prevList = this.data.page === 1 ? [] : (this.data.list || []);
        const fullList = prevList.concat(list);
        this.setData({
          list: fullList,
          total: d.total || 0,
          loading: false,
          noMore: fullList.length >= (d.total || 0),
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
