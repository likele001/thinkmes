const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, name: '', contact: '', phone: '', address: '', loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) {
      adminApi.getSupplierDetail(id).then((res) => {
        const d = res.data || {};
        this.setData({ name: d.name || d.supplier_name || '', contact: d.contact || '', phone: d.phone || '', address: d.address || '' });
      }).catch(() => {});
    }
  },

  goBack() { wx.navigateBack(); },
  inputName(e) { this.setData({ name: e.detail.value }); },
  inputContact(e) { this.setData({ contact: e.detail.value }); },
  inputPhone(e) { this.setData({ phone: e.detail.value }); },
  inputAddress(e) { this.setData({ address: e.detail.value }); },

  submit() {
    const { id, name, contact, phone, address } = this.data;
    if (!name || !name.trim()) { wx.showToast({ title: '请输入名称', icon: 'none' }); return; }
    this.setData({ loading: true });
    const data = { name: name.trim(), contact: (contact || '').trim(), phone: (phone || '').trim(), address: (address || '').trim() };
    const p = id ? adminApi.updateSupplier({ id, ...data }) : adminApi.createSupplier(data);
    p.then(() => { wx.showToast({ title: '保存成功' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
