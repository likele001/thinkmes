const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, name: '', code: '', address: '', loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) {
      adminApi.getWarehouseDetail(id).then((res) => {
        const d = res.data || {};
        this.setData({ name: d.name || d.warehouse_name || '', code: d.code || d.warehouse_code || '', address: d.address || '' });
      }).catch(() => {});
    }
  },

  goBack() { wx.navigateBack(); },
  inputName(e) { this.setData({ name: e.detail.value }); },
  inputCode(e) { this.setData({ code: e.detail.value }); },
  inputAddress(e) { this.setData({ address: e.detail.value }); },

  submit() {
    const { id, name, code, address } = this.data;
    if (!name || !name.trim()) { wx.showToast({ title: '请输入仓库名称', icon: 'none' }); return; }
    this.setData({ loading: true });
    const data = { name: name.trim(), code: (code || '').trim(), address: (address || '').trim() };
    const p = id ? adminApi.updateWarehouse({ id, ...data }) : adminApi.createWarehouse(data);
    p.then(() => { wx.showToast({ title: '保存成功' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
