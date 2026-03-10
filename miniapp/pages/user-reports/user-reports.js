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
          let t = item.create_time;
          if (typeof t === 'string') t = parseInt(t, 10);
          let str = '';
          if (t && !isNaN(t)) {
            const date = new Date(t * 1000);
            const y = date.getFullYear();
            const m = date.getMonth() + 1;
            const d_ = date.getDate();
            const h = date.getHours();
            const min = date.getMinutes();
            str = y + '-' + (m < 10 ? '0' : '') + m + '-' + (d_ < 10 ? '0' : '') + d_ + ' ' + (h < 10 ? '0' : '') + h + ':' + (min < 10 ? '0' : '') + min;
          } else {
            str = '-';
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

  goDetail(e) {
    const id = e.currentTarget.dataset.id;
    if (id) wx.navigateTo({ url: '/pages/report-detail/report-detail?id=' + id + '&from=user' });
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
