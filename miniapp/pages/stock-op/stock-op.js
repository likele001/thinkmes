const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    type: 'in',
    typeText: '入库',
    materialId: '',
    quantity: '',
    actualQuantity: '',
    remark: '',
    loading: false,
  },
  onLoad(options) {
    const type = options.type || 'in';
    const typeMap = { in: '入库', out: '出库', check: '盘点' };
    this.setData({
      type,
      typeText: typeMap[type] || '入库',
    });
    wx.setNavigationBarTitle({ title: this.data.typeText });
  },
  inputMaterialId(e) { this.setData({ materialId: e.detail.value }); },
  inputQuantity(e) { this.setData({ quantity: e.detail.value }); },
  inputActualQuantity(e) { this.setData({ actualQuantity: e.detail.value }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },
  submit() {
    const { type, materialId, quantity, actualQuantity, remark } = this.data;
    const mid = parseInt(materialId, 10);
    if (!mid) { wx.showToast({ title: '请输入物料ID', icon: 'none' }); return; }
    if (type === 'check') {
      const q = parseFloat(actualQuantity);
      if (isNaN(q) && actualQuantity !== '0') { wx.showToast({ title: '请输入实盘数量', icon: 'none' }); return; }
    } else {
      const q = parseFloat(quantity);
      if (!q || q <= 0) { wx.showToast({ title: '请输入数量', icon: 'none' }); return; }
    }
    this.setData({ loading: true });
    let p;
    if (type === 'in') {
      p = adminApi.stockIn({ material_id: mid, quantity: parseFloat(quantity) || 0, remark: remark.trim() });
    } else if (type === 'out') {
      p = adminApi.stockOut({ material_id: mid, quantity: parseFloat(quantity) || 0, remark: remark.trim() });
    } else {
      p = adminApi.stockCheck({ material_id: mid, actual_quantity: parseFloat(actualQuantity) || 0, remark: remark.trim() });
    }
    p.then(() => {
      wx.showToast({ title: this.data.typeText + '成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
