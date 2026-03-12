const { adminApi } = require('../../utils/api.js');

Page({
  data: { model_id: '', count: '1', loading: false },

  goBack() { wx.navigateBack(); },
  inputModelId(e) { this.setData({ model_id: e.detail.value }); },
  inputCount(e) { this.setData({ count: e.detail.value }); },

  submit() {
    const count = parseInt(this.data.count, 10);
    if (!count || count < 1) { wx.showToast({ title: '请输入数量', icon: 'none' }); return; }
    this.setData({ loading: true });
    const data = { count };
    if (this.data.model_id) data.model_id = parseInt(this.data.model_id, 10);
    adminApi.generateTraceCode(data).then(() => { wx.showToast({ title: '生成成功' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
