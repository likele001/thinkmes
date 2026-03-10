const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    name: '',
    code: '',
    description: '',
    sort: 0,
    remark: '',
    status: 1,
    statusText: '正常',
    statusList: [{ value: 1, text: '正常' }, { value: 0, text: '禁用' }],
    statusIndex: 0,
    loading: false,
  },
  onLoad(options) {
    const id = options.id || 0;
    this.setData({ id: id ? parseInt(id, 10) : 0 });
    if (this.data.id) {
      adminApi.getProcesses(1, 200).then((res) => {
        const list = (res.data && res.data.list) || [];
        const d = list.find((x) => x.id == this.data.id) || {};
        const st = d.status !== undefined ? d.status : 1;
        const statusIndex = this.data.statusList.findIndex((s) => s.value === st);
        this.setData({
          name: d.name || '',
          code: d.code || '',
          description: d.description || '',
          sort: d.sort !== undefined ? d.sort : 0,
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
  inputDescription(e) { this.setData({ description: e.detail.value }); },
  inputSort(e) { this.setData({ sort: parseInt(e.detail.value, 10) || 0 }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },
  pickStatus(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.statusList;
    if (list[i]) this.setData({ statusIndex: i, status: list[i].value, statusText: list[i].text });
  },
  submit() {
    const { id, name, code, description, sort, remark, status } = this.data;
    if (!name || !name.trim()) { wx.showToast({ title: '请输入工序名称', icon: 'none' }); return; }
    this.setData({ loading: true });
    const row = { name: name.trim(), code: (code || '').trim(), description: (description || '').trim(), sort: parseInt(sort, 10) || 0, remark: (remark || '').trim(), status };
    const p = id ? adminApi.updateProcess({ id, row }) : adminApi.createProcess({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
