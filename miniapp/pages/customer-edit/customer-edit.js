const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, customer_name: '', contact_phone: '', contact_person: '', address: '', loading: false },
  goBack() { wx.navigateBack(); },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) {
      adminApi.getCustomerDetail(id).then((res) => {
        const d = res.data || {};
        this.setData({
          customer_name: d.customer_name || d.name || '',
          contact_phone: d.contact_phone || d.customer_phone || '',
          contact_person: d.contact_person || '',
          address: d.address || '',
        });
      }).catch(() => {});
    }
  },
  inputName(e) { this.setData({ customer_name: e.detail.value }); },
  inputPhone(e) { this.setData({ contact_phone: e.detail.value }); },
  inputPerson(e) { this.setData({ contact_person: e.detail.value }); },
  inputAddress(e) { this.setData({ address: e.detail.value }); },
  submit() {
    const { id, customer_name, contact_phone, contact_person, address } = this.data;
    if (!customer_name || !customer_name.trim()) { wx.showToast({ title: '请输入客户名称', icon: 'none' }); return; }
    this.setData({ loading: true });
    const row = {
      customer_name: customer_name.trim(),
      contact_phone: (contact_phone || '').trim(),
      contact_person: (contact_person || '').trim(),
      address: (address || '').trim(),
    };
    const p = id ? adminApi.updateCustomer({ id, row }) : adminApi.createCustomer({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
