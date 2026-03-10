const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, plan: null, stats: {}, loading: true },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    if (!id) { this.setData({ loading: false }); return; }
    this.setData({ id });
    adminApi.getProductionPlanDetail(id).then((res) => {
      this.setData({ plan: res.data || null });
    }).catch(() => {});
    adminApi.getProductionPlanProgressStats(id).then((res) => {
      this.setData({ stats: res.data || {}, loading: false });
    }).catch(() => { this.setData({ loading: false }); });
  },
});
