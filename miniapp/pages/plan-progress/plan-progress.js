const { adminApi } = require('../../utils/api.js');

Page({
  data: { planId: 0, list: [], loading: false },

  onLoad(options) {
    const planId = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ planId });
    if (planId) this.load();
  },

  load() {
    this.setData({ loading: true });
    adminApi.getProductionPlanProgress(this.data.planId)
      .then((res) => { this.setData({ list: (res.data && (res.data.list || res.data.rows)) || res.data || [], loading: false }); })
      .catch(() => { this.setData({ loading: false }); });
  },

  goBack() { wx.navigateBack(); },
});
