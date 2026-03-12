const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, detail: {}, loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) this.load();
  },

  load() {
    this.setData({ loading: true });
    adminApi.getPurchaseDetail(this.data.id)
      .then((res) => {
        const detail = res.data && typeof res.data === 'object' ? res.data : null;
        if (detail && detail.material) detail.material_name = detail.material.name;
        if (detail && detail.supplier) detail.supplier_name = detail.supplier.name;
        if (detail && detail.warehouse) detail.warehouse_name = detail.warehouse.name;
        this.setData({ detail, loading: false });
      })
      .catch(() => { this.setData({ detail: null, loading: false }); });
  },

  goBack() { wx.navigateBack(); },
  goEdit() { wx.navigateTo({ url: '/pages/purchase-edit/purchase-edit?id=' + this.data.id }); },
  confirmInbound() {
    adminApi.purchaseInbound(this.data.id).then(() => {
      wx.showToast({ title: '已确认入库' });
      this.load();
    }).catch(() => {});
  },
});
