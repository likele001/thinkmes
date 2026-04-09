const api = require('../../utils/api.js');

Page({
  data: {
    list: [],
    page: 1,
    limit: 20,
    hasMore: true,
    loading: false,
    filterRead: '',
  },

  onLoad() {
    this.reload();
  },

  onPullDownRefresh() {
    this.reload().finally(() => wx.stopPullDownRefresh());
  },

  changeFilter(e) {
    const v = e.currentTarget.dataset.v;
    let filterRead = '';
    if (v === '0') filterRead = 0;
    else if (v === '1') filterRead = 1;
    this.setData({ filterRead }, () => this.reload());
  },

  reload() {
    this.setData({ page: 1, list: [], hasMore: true });
    return this.loadPage(1);
  },

  loadMore() {
    if (this.data.loading || !this.data.hasMore) return;
    return this.loadPage(this.data.page + 1);
  },

  loadPage(page) {
    this.setData({ loading: true });
    return api.userApi
      .getNotifications(page, this.data.limit, this.data.filterRead)
      .then((res) => {
        const d = (res && res.data) ? res.data : {};
        const rows = d.list || [];
        const total = d.total || 0;
        const list = (page === 1) ? rows : this.data.list.concat(rows);
        this.setData({
          list,
          page,
          hasMore: list.length < total,
        });
      })
      .finally(() => this.setData({ loading: false }));
  },

  openItem(e) {
    const id = parseInt(e.currentTarget.dataset.id, 10);
    if (!id) return;
    api.userApi.readNotifications([id]).then(() => {
      const list = this.data.list.map((x) => (x.id === id ? { ...x, is_read: 1 } : x));
      this.setData({ list });
    });
  },

  markAllRead() {
    wx.showModal({
      title: '确认操作',
      content: '确定将全部通知标记为已读？',
      success: (res) => {
        if (!res.confirm) return;
        api.userApi.readNotifications([]).then(() => this.reload());
      },
    });
  },
});

