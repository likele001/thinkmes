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
  goAdd() {
    wx.navigateTo({ url: '/pages/allocation-edit/allocation-edit' });
  },
  goDetail(e) {
    const id = e.currentTarget.dataset.id;
    if (id) wx.navigateTo({ url: '/pages/allocation-detail/allocation-detail?id=' + id });
  },
  goEdit(e) {
    const id = e.currentTarget.dataset.id;
    if (id) wx.navigateTo({ url: '/pages/allocation-edit/allocation-edit?id=' + id });
  },
  confirmDelete(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    wx.showModal({ title: '确认删除', content: '确定删除该分工？', success: (res) => {
      if (res.confirm) adminApi.deleteAllocation(id).then(() => { wx.showToast({ title: '已删除' }); this.setData({ page: 1, list: [], noMore: false }); this.load(); }).catch(() => {}); }});
  },
  goIndex() { wx.navigateTo({ url: '/pages/index/index' }); },
  goReports() { wx.navigateTo({ url: '/pages/reports/reports' }); },
  goOrders() { wx.navigateTo({ url: '/pages/orders/orders' }); },
  refresh() { this.setData({ page: 1, list: [], noMore: false }); this.load(); },
  loadMore() {
    if (this.data.noMore || this.data.loading) return;
    this.setData({ page: this.data.page + 1 });
    this.load();
  },
  stopPropagation() {},
});
