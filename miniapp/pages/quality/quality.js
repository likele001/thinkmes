const { adminApi } = require('../../utils/api.js');

Page({
  data: { tabIndex: 0, standards: [], checks: [], page: 1, limit: 20, loading: false },

  onLoad() { this.loadStandards(); this.loadChecks(); },

  setTab(e) { const i = parseInt(e.currentTarget.dataset.index, 10); this.setData({ tabIndex: i }); },

  loadStandards() {
    adminApi.getQualityStandards(1, this.data.limit).then((res) => {
      const d = res.data || {};
      this.setData({ standards: d.list || d.rows || [] });
    }).catch(() => {});
  },
  loadChecks() {
    adminApi.getQualityChecks(1, this.data.limit).then((res) => {
      const d = res.data || {};
      this.setData({ checks: d.list || d.rows || [] });
    }).catch(() => {});
  },

  goIndex() { wx.navigateTo({ url: '/pages/index/index' }); },
});
