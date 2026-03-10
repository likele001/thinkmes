const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, name: '', contact_person: '', contact_phone: '', address: '', loading: false },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) {
      adminApi.getSupplierDetail(id).then((res) => {
        const d = res.data || {};
        this.setData({
          name: d.name || '',
          contact_person: d.contact_person || '',
          contact_phone: d.contact_phone || '',
          address: d.address || '',
        });
      }).catch(() => {});
    }
  },
  inputName(e) { this.setData({ name: e.detail.value }); },
  inputPerson(e) { this.setData({ contact_person: e.detail.value }); },
  inputPhone(e) { this.setData({ contact_phone: e.detail.value }); },
  inputAddress(e) { this.setData({ address: e.detail.value }); },
  submit() {
    const { id, name, contact_person, contact_phone, address } = this.data;
    if (!name || !name.trim()) { wx.showToast({ title: '请输入供应商名称', icon: 'none' }); return; }
    this.setData({ loading: true });
    const row = {
      name: name.trim(),
      contact_person: (contact_person || '').trim(),
      contact_phone: (contact_phone || '').trim(),
      address: (address || '').trim(),
    };
    const p = id ? adminApi.updateSupplier({ id, row }) : adminApi.createSupplier({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
