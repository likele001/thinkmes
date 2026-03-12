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
    adminApi.getShipmentDetail(this.data.id)
      .then((res) => {
        const raw = res.data && typeof res.data === 'object' ? res.data : null;
        if (!raw) {
          this.setData({ detail: null, items: [], loading: false });
          return;
        }
        const detail = raw;
        if (detail.customer) detail.customer_name = detail.customer.name;
        if (detail.order) detail.order_no = detail.order.order_no;
        const items = detail.items || [];
        this.setData({ detail, items, loading: false });
      })
      .catch(() => { this.setData({ detail: null, items: [], loading: false }); });
  },

  goBack() { wx.navigateBack(); },
  goEdit() { wx.navigateTo({ url: '/pages/shipment-edit/shipment-edit?id=' + this.data.id }); },
});
