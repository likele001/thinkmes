const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, detail: null, loading: true },

  onLoad(options) {
    const id = options.id || 0;
    if (!id) return;
    this.setData({ id });
    adminApi.getAllocationDetail(id).then((res) => {
      const d = res.data || null;
      if (d) {
        const st = d.status;
        d.status_text = st === 0 ? '待生产' : (st === 1 ? '进行中' : (st === 2 ? '已完成' : '已取消'));
        const order = d.order || {};
        const model = d.model || {};
        const product = model.product || {};
        d.order_no = order.order_no || order.order_name || '';
        d.product_name = product.name || '';
        d.model_name = model.name || '';
        d.process_name = (d.process && d.process.name) ? d.process.name : '';
      }
      this.setData({ detail: d, loading: false });
    }).catch(() => { this.setData({ loading: false }); });
  },
  goBack() { wx.navigateBack(); },
  goEdit() {
    wx.navigateTo({ url: '/pages/allocation-edit/allocation-edit?id=' + this.data.id });
  },
  generateQrcode() {
    if (!this.data.id) return;
    adminApi.generateQrcode(this.data.id).then(() => {
      wx.showToast({ title: '二维码已生成' });
    }).catch(() => {});
  },
  confirmDelete() {
    wx.showModal({ title: '确认删除', content: '确定删除该分工？', success: (res) => {
      if (res.confirm) adminApi.deleteAllocation(this.data.id).then(() => { wx.showToast({ title: '已删除' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {});
    }});
  },
});
