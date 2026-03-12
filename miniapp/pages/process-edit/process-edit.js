const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, name: '', description: '', status: 1, statusText: '启用', statusList: [{ value: 1, text: '启用' }, { value: 0, text: '禁用' }], statusIndex: 0, loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) adminApi.getProcesses(1, 200).then((res) => {
      const list = (res.data && (res.data.list || res.data.rows)) || [];
      const item = list.find((p) => p.id === id) || {};
      this.setData({ name: item.name || '', description: item.description || '', status: item.status !== undefined ? item.status : 1, statusIndex: item.status === 0 ? 1 : 0, statusText: item.status === 0 ? '禁用' : '启用' });
    }).catch(() => {});
  },

  goBack() { wx.navigateBack(); },
  inputName(e) { this.setData({ name: e.detail.value }); },
  inputDesc(e) { this.setData({ description: e.detail.value }); },
  pickStatus(e) { const i = parseInt(e.detail.value, 10); const list = this.data.statusList; this.setData({ statusIndex: i, status: list[i].value, statusText: list[i].text }); },

  submit() {
    const { id, name, description, status } = this.data;
    if (!name || !name.trim()) { wx.showToast({ title: '请输入工序名称', icon: 'none' }); return; }
    this.setData({ loading: true });
    const data = { name: name.trim(), description: (description || '').trim(), status };
    const p = id ? adminApi.updateProcess({ id, ...data }) : adminApi.createProcess(data);
    p.then(() => { wx.showToast({ title: '保存成功' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
