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
    adminApi.getWarehouseDetail(this.data.id)
      .then((res) => {
        const detail = res.data && typeof res.data === 'object' ? res.data : null;
        this.setData({ detail, loading: false });
      })
      .catch(() => { this.setData({ detail: null, loading: false }); });
  },

  goBack() { wx.navigateBack(); },
  goEdit() { wx.navigateTo({ url: '/pages/warehouse-edit/warehouse-edit?id=' + this.data.id }); },
  goStock() { wx.navigateTo({ url: '/pages/stock/stock?warehouse_id=' + this.data.id }); },
});
