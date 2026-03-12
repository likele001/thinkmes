const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, shipment_no: '', customer_id: '', remark: '', loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) {
      adminApi.getShipmentDetail(id).then((res) => {
        const d = res.data || {};
        this.setData({ shipment_no: d.shipment_no || d.order_no || '', customer_id: d.customer_id != null ? String(d.customer_id) : '', remark: d.remark || '' });
      }).catch(() => {});
    }
  },

  goBack() { wx.navigateBack(); },
  inputNo(e) { this.setData({ shipment_no: e.detail.value }); },
  inputCustomerId(e) { this.setData({ customer_id: e.detail.value }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },

  submit() {
    const { id, shipment_no, customer_id } = this.data;
    if (!customer_id || isNaN(parseInt(customer_id, 10))) { wx.showToast({ title: '请填写客户ID', icon: 'none' }); return; }
    this.setData({ loading: true });
    const data = { shipment_no: (shipment_no || '').trim(), customer_id: parseInt(customer_id, 10), remark: this.data.remark };
    const p = id ? adminApi.updateShipment({ id, ...data }) : adminApi.createShipment(data);
    p.then(() => { wx.showToast({ title: '保存成功' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
