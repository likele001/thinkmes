const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    orderId: 0,
    modelId: 0,
    processId: 0,
    userId: 0,
    orderLabel: '请选择订单',
    modelLabel: '请选择型号',
    processLabel: '请选择工序',
    userLabel: '请选择员工',
    orderIndex: 0,
    modelIndex: 0,
    processIndex: 0,
    userIndex: 0,
    orderOptions: [],
    modelOptions: [],
    processOptions: [],
    userOptions: [],
    quantity: '',
    status: 0,
    statusText: '待开始',
    statusList: [
      { value: 0, text: '待开始' },
      { value: 1, text: '进行中' },
      { value: 2, text: '已完成' },
    ],
    statusIndex: 0,
    loading: false,
  },
  goBack() { wx.navigateBack(); },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    Promise.all([
      adminApi.getOrders(1, 200),
      adminApi.getProcesses(1, 200),
      adminApi.getUsers(1, 200),
    ]).then(([resO, resP, resU]) => {
      const orders = (resO.data && resO.data.list) || [];
      const processes = (resP.data && resP.data.list) || [];
      const users = (resU.data && resU.data.list) || [];
      const orderOptions = orders.map((o) => ({ id: o.id, label: (o.order_name || o.order_no || '订单') + ' #' + o.id }));
      const userOptions = users.map((u) => ({ id: u.id, label: (u.nickname || u.username || '') + ' (ID:' + u.id + ')' }));
      this.setData({ orderOptions, processOptions: processes, userOptions });
      if (id) {
        adminApi.getAllocationDetail(id).then((res) => {
          const d = res.data || {};
          const oid = d.order_id || 0;
          const mid = d.model_id || 0;
          const pid = d.process_id || 0;
          const uid = d.user_id || 0;
          const oi = orderOptions.findIndex((o) => o.id === oid);
          const pi = processes.findIndex((p) => p.id === pid);
          const ui = userOptions.findIndex((u) => u.id === uid);
          this.setData({
            orderId: oid,
            orderLabel: oi >= 0 ? orderOptions[oi].label : '请选择订单',
            orderIndex: oi >= 0 ? oi : 0,
            processId: pid,
            processLabel: pi >= 0 ? processes[pi].name : '请选择工序',
            processIndex: pi >= 0 ? pi : 0,
            userId: uid,
            userLabel: ui >= 0 ? userOptions[ui].label : '请选择员工',
            userIndex: ui >= 0 ? ui : 0,
            quantity: String(d.quantity || ''),
            status: d.status !== undefined ? d.status : 0,
            statusText: this.data.statusList[d.status >= 0 && d.status <= 2 ? d.status : 0].text,
            statusIndex: d.status >= 0 && d.status <= 2 ? d.status : 0,
          });
          if (oid) {
            adminApi.getOrderModels(oid).then((r) => {
              const models = (r.data && r.data.list) || [];
              const modelOptions = models.map((m) => ({
                id: m.model_id,
                label: (m.model && m.model.name ? m.model.name : '') + (m.product_name ? ' - ' + m.product_name : '') || '型号' + m.model_id,
              }));
              const mi = modelOptions.findIndex((o) => o.id === mid);
              this.setData({
                modelOptions,
                modelId: mid,
                modelLabel: mi >= 0 ? modelOptions[mi].label : (modelOptions[0] ? modelOptions[0].label : '请选择型号'),
                modelIndex: mi >= 0 ? mi : 0,
              });
            }).catch(() => {});
          }
        }).catch(() => {});
      }
    }).catch(() => {});
  },
  pickOrder(e) {
    const i = parseInt(e.detail.value, 10);
    const opts = this.data.orderOptions;
    if (!opts[i]) return;
    const orderId = opts[i].id;
    this.setData({
      orderIndex: i,
      orderId,
      orderLabel: opts[i].label,
      modelOptions: [],
      modelIndex: 0,
      modelId: 0,
      modelLabel: '请选择型号',
    });
    adminApi.getOrderModels(orderId).then((r) => {
      const models = (r.data && r.data.list) || [];
      const modelOptions = models.map((m) => ({
        id: m.model_id,
        label: (m.model && m.model.name ? m.model.name : '') + (m.product_name ? ' - ' + m.product_name : '') || '型号' + m.model_id,
      }));
      this.setData({ modelOptions });
    }).catch(() => {});
  },
  openModelSelect() {
    const modelOptions = this.data.modelOptions;
    if (!modelOptions.length) { wx.showToast({ title: '请先选择订单', icon: 'none' }); return; }
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
  pickUser(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.userOptions;
    if (list[i]) this.setData({ userIndex: i, userId: list[i].id, userLabel: list[i].label });
  },
  pickStatus(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.statusList;
    if (list[i]) this.setData({ statusIndex: i, status: list[i].value, statusText: list[i].text });
  },
  inputQuantity(e) { this.setData({ quantity: e.detail.value }); },
  submit() {
    const { id, orderId, modelId, processId, userId, quantity, status } = this.data;
    const qty = parseInt(quantity, 10) || 0;
    if (!id) {
      if (orderId <= 0 || modelId <= 0 || processId <= 0 || userId <= 0 || qty <= 0) {
        wx.showToast({ title: '请填写订单、型号、工序、员工和数量', icon: 'none' });
        return;
      }
    }
    this.setData({ loading: true });
    if (id) {
      const row = { order_id: orderId, model_id: modelId, process_id: processId, user_id: userId, quantity: qty, status };
      adminApi.updateAllocation({ id, row }).then(() => {
        wx.showToast({ title: '保存成功' });
        setTimeout(() => wx.navigateBack(), 1500);
      }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
    } else {
      adminApi.createAllocation({ order_id: orderId, model_id: modelId, process_id: processId, user_id: userId, quantity: qty }).then(() => {
        wx.showToast({ title: '添加成功' });
        setTimeout(() => wx.navigateBack(), 1500);
      }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
    }
  },
});
