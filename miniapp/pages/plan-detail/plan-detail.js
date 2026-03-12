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
    adminApi.getProductionPlanDetail(this.data.id)
      .then((res) => {
        const raw = res.data;
        const detail = raw && typeof raw === 'object' ? raw : {};
        if (detail.model && detail.model.product) {
          detail.product_name = detail.product_name || detail.model.product.name;
          detail.product_model = detail.product_model || detail.model.name;
        }
        if (detail.order) detail.order_no = detail.order_no || detail.order.order_no;
        this.setData({ detail, loading: false });
      })
      .catch(() => { this.setData({ detail: null, loading: false }); });
  },

  goBack() { wx.navigateBack(); },
  goEdit() { wx.navigateTo({ url: '/pages/plan-edit/plan-edit?id=' + this.data.id }); },
  goAllocations() { wx.navigateTo({ url: '/pages/plan-allocations/plan-allocations?id=' + this.data.id }); },
  goProgress() { wx.navigateTo({ url: '/pages/plan-progress/plan-progress?id=' + this.data.id }); },
});
