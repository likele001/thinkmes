const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, plan: null, progress: {}, loading: true },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    if (!id) { this.setData({ loading: false }); return; }
    this.setData({ id });
    adminApi.getProductionPlanProgress(id).then((res) => {
      const d = res.data || {};
      this.setData({ plan: d.plan || null, progress: { total_quantity: d.plan && d.plan.total_quantity, completed_quantity: d.completed_quantity, reported_quantity: d.reported_quantity, progress: d.progress }, loading: false });
    }).catch(() => { this.setData({ loading: false }); });
  },
});
