const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    name: '',
    code: '',
    address: '',
    remark: '',
    status: 1,
    statusText: '启用',
    statusList: [{ value: 1, text: '启用' }, { value: 0, text: '禁用' }],
    statusIndex: 0,
    is_default: 0,
    isDefaultText: '否',
    isDefaultList: [{ value: 0, text: '否' }, { value: 1, text: '是' }],
    isDefaultIndex: 0,
    loading: false,
  },
  onLoad(options) {
    const id = options.id || 0;
    this.setData({ id: id ? parseInt(id, 10) : 0 });
    if (this.data.id) {
      adminApi.getWarehouseDetail(this.data.id).then((res) => {
        const d = res.data || {};
        const st = d.status !== undefined ? d.status : 1;
        const def = d.is_default !== undefined ? d.is_default : 0;
        this.setData({
          name: d.name || '',
          code: d.code || '',
          address: d.address || '',
          remark: d.remark || '',
          status: st,
          statusText: st === 1 ? '启用' : '禁用',
          statusIndex: st === 1 ? 0 : 1,
          is_default: def,
          isDefaultText: def === 1 ? '是' : '否',
          isDefaultIndex: def === 1 ? 1 : 0,
        });
      }).catch(() => {});
    }
  },
  inputName(e) { this.setData({ name: e.detail.value }); },
  inputCode(e) { this.setData({ code: e.detail.value }); },
  inputAddress(e) { this.setData({ address: e.detail.value }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },
  pickStatus(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.statusList;
    if (list[i]) this.setData({ statusIndex: i, status: list[i].value, statusText: list[i].text });
  },
  pickIsDefault(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.isDefaultList;
    if (list[i]) this.setData({ isDefaultIndex: i, is_default: list[i].value, isDefaultText: list[i].text });
  },
  submit() {
    const { id, name, code, address, remark, status, is_default } = this.data;
    if (!name || !name.trim()) { wx.showToast({ title: '请输入仓库名称', icon: 'none' }); return; }
    this.setData({ loading: true });
    const row = { name: name.trim(), code: (code || '').trim(), address: (address || '').trim(), remark: (remark || '').trim(), status, is_default };
    const p = id ? adminApi.updateWarehouse({ id, row }) : adminApi.createWarehouse({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
