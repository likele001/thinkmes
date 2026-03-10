const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    detail: null,
    allocations: [],
    modelRows: [],
    loading: true,
  },

  onLoad(options) {
    const id = options.id || options.order_id || 0;
    if (!id) {
      wx.showToast({ title: '缺少订单ID', icon: 'none' });
      this.setData({ loading: false });
      return;
    }
    this.setData({ id });
    this.load();
  },

  load() {
    const orderId = this.data.id;
    Promise.all([
      adminApi.getOrderDetail(orderId),
      adminApi.getAllocations(1, 200, '', orderId),
    ])
      .then(([orderRes, allocRes]) => {
        const detail = orderRes.data || null;
        const allocList = (allocRes.data && allocRes.data.list) ? allocRes.data.list : [];
        const modelRows = [];
        const completedByModel = {};
        allocList.forEach((a) => {
          const mid = a.model_id || 0;
          completedByModel[mid] = (completedByModel[mid] || 0) + (a.completed_quantity || 0);
        });
        const orderModels = detail && (detail.order_models || detail.orderModels) ? (detail.order_models || detail.orderModels) : [];
        orderModels.forEach((om) => {
          const model = om.model || {};
          const product = model.product || {};
          modelRows.push({
            product_name: product.name || '-',
            model_name: model.name || '-',
            quantity: om.quantity || 0,
            completed: completedByModel[om.model_id] || 0,
          });
        });
        const allocations = allocList.map((a) => {
          const order = a.order || {};
          const model = a.model || {};
          const product = model.product || {};
          const process = a.process || {};
          const user = a.user || {};
          return {
            id: a.id,
            product_name: product.name || '-',
            model_name: model.name || '-',
            process_name: process.name || '-',
            quantity: a.quantity || 0,
            completed_quantity: a.completed_quantity || 0,
            user_name: user.nickname || user.username || '-',
            status: a.status,
            status_text: a.status === 0 ? '待生产' : (a.status === 1 ? '进行中' : (a.status === 2 ? '已完成' : '已取消')),
          };
        });
        this.setData({ detail, allocations, modelRows, loading: false });
      })
      .catch((err) => {
        this.setData({ loading: false });
        wx.showToast({ title: (err && err.msg) ? err.msg : '加载失败', icon: 'none' });
      });
  },

  goEdit() {
    wx.navigateTo({ url: '/pages/order-edit/order-edit?id=' + this.data.id });
  },
  goAllocationDetail(e) {
    const id = e.currentTarget.dataset.id;
    if (id) wx.navigateTo({ url: '/pages/allocation-detail/allocation-detail?id=' + id });
  },
  confirmDelete() {
    wx.showModal({
      title: '确认删除',
      content: '确定删除该订单？',
      success: (res) => {
        if (res.confirm) {
          adminApi.deleteOrder(this.data.id).then(() => {
            wx.showToast({ title: '已删除' });
            setTimeout(() => wx.navigateBack(), 500);
          }).catch(() => {});
        }
      },
    });
  },
});
