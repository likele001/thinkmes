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
    statusList: ['全部', '待审核', '已通过', '已拒绝'],
  },

  onLoad() {
    if (!getApp().checkAdminLogin()) {
      wx.redirectTo({ url: '/pages/login/login' });
      return;
    }
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
    adminApi.getReports(this.data.page, this.data.limit, status)
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

  onStatusChange(e) {
    const i = parseInt(e.detail.value, 10);
    this.setData({ statusIndex: i, page: 1, list: [], noMore: false });
    this.load();
  },

  goDetail(e) {
    const id = e.currentTarget.dataset.id;
    const status = e.currentTarget.dataset.status;
    if (!id) return;
    // 未审核(0) -> 审核页；已通过(1)/已拒绝(2) -> 报工详情
    if (Number(status) === 0) {
      wx.navigateTo({ url: '/pages/audit/audit?report_id=' + id });
    } else {
      wx.navigateTo({ url: '/pages/report-detail/report-detail?id=' + id });
    }
  },

  auditReport(e) {
    const id = e.currentTarget.dataset.id;
    const status = parseInt(e.currentTarget.dataset.status, 10);
    if (!id) return;
    const title = status === 1 ? '通过' : '拒绝';
    wx.showModal({
      title: '确认' + title,
      content: '确定' + title + '该报工？',
      success: (res) => {
        if (res.confirm) {
          adminApi.auditReport(id, status).then(() => {
            wx.showToast({ title: title + '成功' });
            const list = (this.data.list || []).filter((item) => item.id !== id);
            this.setData({ list, total: Math.max(0, (this.data.total || 0) - 1) });
          }).catch(() => {});
        }
      },
    });
  },

  goIndex() { wx.navigateTo({ url: '/pages/index/index' }); },
  goAllocations() { wx.navigateTo({ url: '/pages/allocations/allocations' }); },
  goOrders() { wx.navigateTo({ url: '/pages/orders/orders' }); },
  stopPropagation() {},
});
