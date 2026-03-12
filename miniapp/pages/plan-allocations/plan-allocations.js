const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, plan: null, list: [], loading: true },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    if (!id) { this.setData({ loading: false }); return; }
    this.setData({ id });
    adminApi.getProductionPlanDetail(id).then((res) => {
      this.setData({ plan: res.data || null });
    }).catch(() => {});
    adminApi.getProductionPlanAllocations(id).then((res) => {
      const list = (res.data && res.data.list) || [];
      this.setData({ list, loading: false });
    }).catch(() => { this.setData({ loading: false }); });
  },
  goBack() { wx.navigateBack(); },
  goAllocation(e) {
    const id = e.currentTarget.dataset.id;
    if (id) wx.navigateTo({ url: '/pages/allocation-detail/allocation-detail?id=' + id });
  },
});
