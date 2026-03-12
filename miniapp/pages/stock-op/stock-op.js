const { adminApi } = require('../../utils/api.js');

Page({
  data: { opTypes: [{ value: 'in', text: '入库' }, { value: 'out', text: '出库' }], opIndex: 0, warehouse_id: '', material_id: '', quantity: '', remark: '', loading: false },

  goBack() { wx.navigateBack(); },
  pickOp(e) { this.setData({ opIndex: parseInt(e.detail.value, 10) }); },
  inputWarehouseId(e) { this.setData({ warehouse_id: e.detail.value }); },
  inputMaterialId(e) { this.setData({ material_id: e.detail.value }); },
  inputQuantity(e) { this.setData({ quantity: e.detail.value }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },

  submit() {
    const { warehouse_id, material_id, quantity, opTypes, opIndex } = this.data;
    const type = opTypes[opIndex].value;
    if (!warehouse_id || !material_id || !quantity || isNaN(parseInt(quantity, 10))) {
      wx.showToast({ title: '请填写仓库、物料和数量', icon: 'none' });
      return;
    }
    this.setData({ loading: true });
    const data = { warehouse_id: parseInt(warehouse_id, 10), material_id: parseInt(material_id, 10), quantity: parseInt(quantity, 10), remark: this.data.remark };
    const p = type === 'in' ? adminApi.stockIn(data) : adminApi.stockOut(data);
    p.then(() => { wx.showToast({ title: '操作成功' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
