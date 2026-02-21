const api = require('../../utils/api.js');

Page({
  data: {
    list: [],
    total: 0,
    page: 1,
    limit: 20,
    status: '',
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
    api.getReports(this.data.page, this.data.limit, this.data.status)
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
        const total = d.total || 0;
        this.setData({
          list: fullList,
          total,
          loading: false,
          noMore: fullList.length >= total,
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
