const { adminApi } = require('../../utils/api.js');

Page({
  data: { planId: '', stats: null, statsRows: [], loading: false },

  onLoad(options) {
    const planId = options.id || options.plan_id || '';
    this.setData({ planId });
    this.load();
  },

  load() {
    this.setData({ loading: true });
    adminApi.getProductionPlanProgressStats(this.data.planId || undefined)
      .then((res) => {
        const d = res.data || {};
        const rows = [];
        if (d.total_quantity != null) rows.push({ label: '计划总量', value: d.total_quantity });
        if (d.finished_quantity != null) rows.push({ label: '已完成', value: d.finished_quantity });
        if (d.completion_rate != null) rows.push({ label: '完成率', value: d.completion_rate + '%' });
        this.setData({ stats: d, statsRows: rows, loading: false });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  goBack() { wx.navigateBack(); },
});
