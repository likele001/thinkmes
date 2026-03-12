const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    list: [],
    total: 0,
    page: 1,
    limit: 20,
    loading: false,
    noMore: false,
    statusIndex: 0,
    statusList: ['全部', '待分配', '进行中', '已完成'],
  },

  onLoad() {
    this.load();
  },

  getStatusParam() {
    const i = this.data.statusIndex;
    if (i === 0) return '';
    return i - 1;
  },

  load() {
    if (this.data.loading) return;
    if (this.data.noMore && this.data.page > 1) return;
    this.setData({ loading: true });
    const status = this.getStatusParam();
    adminApi.getOrders(this.data.page, this.data.limit, status)
      .then((res) => {
        const d = res.data || {};
        const newList = d.list || [];
        const list = this.data.page === 1 ? newList : (this.data.list || []).concat(newList);
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

  loadMore() {
    if (this.data.noMore || this.data.loading) return;
    this.setData({ page: this.data.page + 1 });
    this.load();
  },

  refresh() {
    this.setData({ page: 1, noMore: false });
    this.load();
  },

  onStatusChange(e) {
    const i = parseInt(e.detail.value, 10);
    this.setData({ statusIndex: i, page: 1, list: [], noMore: false });
    this.load();
  },

  goIndex() {
    wx.navigateTo({ url: '/pages/index/index' });
  },
  goAllocations() {
    wx.navigateTo({ url: '/pages/allocations/allocations' });
  },
  goReports() {
    wx.navigateTo({ url: '/pages/reports/reports' });
  },

  goAdd() {
    wx.navigateTo({ url: '/pages/order-edit/order-edit' });
  },
  goDetail(e) {
    const id = e.currentTarget.dataset.id;
    if (id) wx.navigateTo({ url: '/pages/order-detail/order-detail?id=' + id });
  },
  goEdit(e) {
    const id = e.currentTarget.dataset.id;
    if (id) wx.navigateTo({ url: '/pages/order-edit/order-edit?id=' + id });
  },
  confirmDelete(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    wx.showModal({
      title: '确认删除',
      content: '确定删除该订单？',
      success: (res) => {
        if (res.confirm) {
          adminApi.deleteOrder(id).then(() => {
            wx.showToast({ title: '已删除' });
            this.setData({ page: 1, list: [], noMore: false });
            this.load();
          }).catch(() => {});
        }
      },
    });
  },

  stopPropagation() {},
});
