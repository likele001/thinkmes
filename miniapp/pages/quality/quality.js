const { adminApi } = require('../../utils/api.js');

Page({
  data: { standards: [], checks: [], tab: 'standards', loading: false },
  onLoad() {
    this.loadStandards();
    this.loadChecks();
  },
  loadStandards() {
    adminApi.getQualityStandards(1, 50)
      .then((res) => {
        const d = res.data || {};
        this.setData({ standards: d.list || [] });
      })
      .catch(() => {});
  },
  loadChecks() {
    adminApi.getQualityChecks(1, 50)
      .then((res) => {
        const d = res.data || {};
        this.setData({ checks: d.list || [], loading: false });
      })
      .catch(() => { this.setData({ loading: false }); });
  },
  switchTab(e) {
    this.setData({ tab: e.currentTarget.dataset.tab });
  },
});
