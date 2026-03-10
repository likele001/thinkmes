const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, detail: null, allocations: [], loading: true },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    if (!id) { this.setData({ loading: false }); return; }
    this.setData({ id });
    adminApi.getProductionPlanDetail(id).then((res) => {
      this.setData({ detail: res.data || null });
    }).catch(() => {});
    adminApi.getProductionPlanAllocations(id).then((res) => {
      const allocations = (res.data && res.data.list) || [];
      this.setData({ allocations, loading: false });
    }).catch(() => { this.setData({ loading: false }); });
  },
  goAllocation(e) {
    const id = e.currentTarget.dataset.id;
    if (id) wx.navigateTo({ url: '/pages/allocation-detail/allocation-detail?id=' + id });
  },
  goEdit() {
    if (this.data.id) wx.navigateTo({ url: '/pages/plan-edit/plan-edit?id=' + this.data.id });
  },
  goAllocate() { if (this.data.id) wx.navigateTo({ url: '/pages/plan-allocate/plan-allocate?id=' + this.data.id }); },
  goAllocations() { if (this.data.id) wx.navigateTo({ url: '/pages/plan-allocations/plan-allocations?id=' + this.data.id }); },
  goProgressStats() { if (this.data.id) wx.navigateTo({ url: '/pages/plan-progress-stats/plan-progress-stats?id=' + this.data.id }); },
  goProgress() { if (this.data.id) wx.navigateTo({ url: '/pages/plan-progress/plan-progress?id=' + this.data.id }); },
});
