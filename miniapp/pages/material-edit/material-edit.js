const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    name: '',
    code: '',
    spec: '',
    unit: 'pcs',
    current_price: '0',
    min_stock: '0',
    remark: '',
    status: 1,
    statusText: '启用',
    statusList: [{ value: 1, text: '启用' }, { value: 0, text: '禁用' }],
    statusIndex: 0,
    loading: false,
  },
  onLoad(options) {
    const id = options.id || 0;
    this.setData({ id: id ? parseInt(id, 10) : 0 });
    if (this.data.id) {
      adminApi.getMaterialDetail(this.data.id).then((res) => {
        const d = res.data || {};
        const st = d.status !== undefined && d.status !== null ? d.status : 1;
        const statusIndex = this.data.statusList.findIndex((s) => s.value === st);
        this.setData({
          name: d.name || '',
          code: d.code || '',
          spec: d.spec || '',
          unit: d.unit || 'pcs',
          current_price: d.current_price !== undefined && d.current_price !== null ? String(d.current_price) : '0',
          min_stock: d.min_stock !== undefined && d.min_stock !== null ? String(d.min_stock) : '0',
          remark: d.remark || '',
          status: st,
          statusText: this.data.statusList[statusIndex >= 0 ? statusIndex : 0].text,
          statusIndex: statusIndex >= 0 ? statusIndex : 0,
        });
      }).catch(() => {});
    }
  },
  inputName(e) { this.setData({ name: e.detail.value }); },
  inputCode(e) { this.setData({ code: e.detail.value }); },
  inputSpec(e) { this.setData({ spec: e.detail.value }); },
  inputUnit(e) { this.setData({ unit: e.detail.value }); },
  inputCurrentPrice(e) { this.setData({ current_price: e.detail.value }); },
  inputMinStock(e) { this.setData({ min_stock: e.detail.value }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },
  pickStatus(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.statusList;
    if (list[i]) this.setData({ statusIndex: i, status: list[i].value, statusText: list[i].text });
  },
  submit() {
    const { id, name, code, spec, unit, current_price, min_stock, remark, status } = this.data;
    if (!name || !name.trim()) { wx.showToast({ title: '请输入物料名称', icon: 'none' }); return; }
    this.setData({ loading: true });
    const row = {
      name: name.trim(),
      code: (code || '').trim(),
      spec: (spec || '').trim(),
      unit: (unit || 'pcs').trim(),
      current_price: parseFloat(current_price) || 0,
      min_stock: parseFloat(min_stock) || 0,
      remark: (remark || '').trim(),
      status,
    };
    const p = id ? adminApi.updateMaterial({ id, row }) : adminApi.createMaterial({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
