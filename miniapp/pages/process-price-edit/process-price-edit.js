const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    modelId: 0,
    processId: 0,
    modelLabel: '请选择产品型号',
    processLabel: '请选择工序',
    modelIndex: 0,
    processIndex: 0,
    modelOptions: [],
    processOptions: [],
    price: '',
    timePrice: '',
    status: 1,
    statusText: '正常',
    statusList: [{ value: 1, text: '正常' }, { value: 0, text: '禁用' }],
    statusIndex: 0,
    loading: false,
  },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    Promise.all([
      adminApi.getModels(1, 200),
      adminApi.getProcesses(1, 200),
    ]).then(([resM, resP]) => {
      const models = (resM.data && resM.data.list) || [];
      const processes = (resP.data && resP.data.list) || [];
      const modelOptions = models.map((m) => ({
        id: m.id,
        label: (m.product && m.product.name ? m.product.name + ' - ' : '') + (m.name || '型号' + m.id),
      }));
      this.setData({ modelOptions, processOptions: processes });
      if (id) {
        adminApi.getProcessPriceList(1, 300).then((res) => {
          const list = (res.data && res.data.list) || [];
          const d = list.find((x) => x.id == id);
          if (!d) {
            // 可能被 status 过滤，再试不传 status 或单独请求
            this.setData({ loading: false });
            return;
          }
          const mi = modelOptions.findIndex((o) => o.id === d.model_id);
          const pi = processes.findIndex((p) => p.id === d.process_id);
          const st = d.status !== undefined ? d.status : 1;
          const si = this.data.statusList.findIndex((s) => s.value === st);
          this.setData({
            modelId: d.model_id,
            processId: d.process_id,
            modelLabel: mi >= 0 ? modelOptions[mi].label : '请选择产品型号',
            processLabel: pi >= 0 ? processes[pi].name : '请选择工序',
            modelIndex: mi >= 0 ? mi : 0,
            processIndex: pi >= 0 ? pi : 0,
            price: String(d.price ?? ''),
            timePrice: String(d.time_price ?? ''),
            status: st,
            statusText: this.data.statusList[si >= 0 ? si : 0].text,
            statusIndex: si >= 0 ? si : 0,
          });
        }).catch(() => {});
      }
    }).catch(() => {});
  },
  openModelSelect() {
    const modelOptions = this.data.modelOptions;
    if (!modelOptions.length) { wx.showToast({ title: '型号列表加载中', icon: 'none' }); return; }
    getApp().globalData.modelSelectList = modelOptions;
    wx.navigateTo({
      url: '/pages/model-select/model-select',
      events: {
        selectModel: (data) => {
          const idx = this.data.modelOptions.findIndex((o) => o.id === data.id);
          this.setData({ modelId: data.id, modelLabel: data.label, modelIndex: idx >= 0 ? idx : 0 });
        },
      },
    });
  },
  pickModel(e) {
    const i = parseInt(e.detail.value, 10);
    const opts = this.data.modelOptions;
    if (opts[i]) this.setData({ modelIndex: i, modelId: opts[i].id, modelLabel: opts[i].label });
  },
  pickProcess(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.processOptions;
    if (list[i]) this.setData({ processIndex: i, processId: list[i].id, processLabel: list[i].name });
  },
  pickStatus(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.statusList;
    if (list[i]) this.setData({ statusIndex: i, status: list[i].value, statusText: list[i].text });
  },
  inputPrice(e) { this.setData({ price: e.detail.value }); },
  inputTimePrice(e) { this.setData({ timePrice: e.detail.value }); },
  submit() {
    const { id, modelId, processId, price, timePrice, status } = this.data;
    if (!modelId || !processId) { wx.showToast({ title: '请选择产品型号和工序', icon: 'none' }); return; }
    this.setData({ loading: true });
    const row = {
      model_id: modelId,
      process_id: processId,
      price: parseFloat(price) || 0,
      time_price: parseFloat(timePrice) || 0,
      status,
    };
    const p = id ? adminApi.updateProcessPrice({ id, row }) : adminApi.createProcessPrice({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
