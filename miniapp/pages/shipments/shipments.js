const { adminApi } = require('../../utils/api.js');

Page({
  data: { list: [], page: 1, limit: 20, loading: false, noMore: false },

  onLoad() { this.load(); },

  load() {
    if (this.data.loading) return;
    this.setData({ loading: true });
    adminApi.getShipmentList(this.data.page, this.data.limit)
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
  goAdd() { wx.navigateTo({ url: '/pages/shipment-edit/shipment-edit' }); },
  goDetail(e) { const id = e.currentTarget.dataset.id; if (id) wx.navigateTo({ url: '/pages/shipment-detail/shipment-detail?id=' + id }); },
  goEdit(e) { const id = e.currentTarget.dataset.id; if (id) wx.navigateTo({ url: '/pages/shipment-edit/shipment-edit?id=' + id }); },
  confirmDelete(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    wx.showModal({ title: '确认删除', content: '确定删除该发货单？', success: (res) => {
      if (res.confirm) adminApi.deleteShipment(id).then(() => { wx.showToast({ title: '已删除' }); this.setData({ page: 1, list: [], noMore: false }); this.load(); }).catch(() => {});
    }});
  },
});
