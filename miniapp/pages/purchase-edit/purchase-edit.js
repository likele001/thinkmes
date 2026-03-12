const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, purchase_no: '', supplier_id: '', remark: '', loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) {
      adminApi.getPurchaseDetail(id).then((res) => {
        const d = res.data || {};
        this.setData({ purchase_no: d.purchase_no || d.order_no || '', supplier_id: d.supplier_id != null ? String(d.supplier_id) : '', remark: d.remark || '' });
      }).catch(() => {});
    }
  },

  goBack() { wx.navigateBack(); },
  inputNo(e) { this.setData({ purchase_no: e.detail.value }); },
  inputSupplierId(e) { this.setData({ supplier_id: e.detail.value }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },

  submit() {
    const { id, purchase_no, supplier_id } = this.data;
    if (!supplier_id || isNaN(parseInt(supplier_id, 10))) { wx.showToast({ title: '请填写供应商ID', icon: 'none' }); return; }
    this.setData({ loading: true });
    const data = { purchase_no: (purchase_no || '').trim(), supplier_id: parseInt(supplier_id, 10), remark: this.data.remark };
    const p = id ? adminApi.updatePurchase({ id, ...data }) : adminApi.createPurchase(data);
    p.then(() => { wx.showToast({ title: '保存成功' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
