const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, model_id: '', process_id: '', price: '', time_price: '', loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) {
      adminApi.getProcessPriceList(1, 500).then((res) => {
        const list = (res.data && (res.data.list || res.data.rows)) || [];
        const item = list.find((p) => p.id === id) || {};
        this.setData({ model_id: item.model_id ? String(item.model_id) : '', process_id: item.process_id ? String(item.process_id) : '', price: item.price != null ? String(item.price) : '', time_price: item.time_price != null ? String(item.time_price) : '' });
      }).catch(() => {});
    }
  },

  goBack() { wx.navigateBack(); },
  inputModelId(e) { this.setData({ model_id: e.detail.value }); },
  inputProcessId(e) { this.setData({ process_id: e.detail.value }); },
  inputPrice(e) { this.setData({ price: e.detail.value }); },
  inputTimePrice(e) { this.setData({ time_price: e.detail.value }); },

  submit() {
    const { id, model_id, process_id, price } = this.data;
    const time_price = this.data.time_price ? parseFloat(this.data.time_price) : undefined;
    if (!price || isNaN(parseFloat(price))) { wx.showToast({ title: '请输入单价', icon: 'none' }); return; }
    if (!id && (!model_id || !process_id)) { wx.showToast({ title: '请填写型号ID和工序ID', icon: 'none' }); return; }
    this.setData({ loading: true });
    const data = { model_id: model_id ? parseInt(model_id, 10) : undefined, process_id: process_id ? parseInt(process_id, 10) : undefined, price: parseFloat(price), time_price };
    const p = id ? adminApi.updateProcessPrice({ id, ...data }) : adminApi.createProcessPrice(data);
    p.then(() => { wx.showToast({ title: '保存成功' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
