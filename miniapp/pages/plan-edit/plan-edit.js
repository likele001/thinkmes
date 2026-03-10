const { adminApi } = require('../../utils/api.js');

function dateToTimestamp(str) {
  if (!str || !str.trim()) return null;
  const d = new Date(str.trim());
  return isNaN(d.getTime()) ? null : Math.floor(d.getTime() / 1000);
}

function timestampToDate(ts) {
  if (!ts || ts <= 0) return '';
  const d = new Date(ts * 1000);
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return y + '-' + m + '-' + day;
}

Page({
  data: {
    id: 0,
    planName: '',
    orderId: 0,
    modelId: 0,
    orderLabel: '请选择订单',
    modelLabel: '请选择型号',
    orderIndex: 0,
    modelIndex: 0,
    orderOptions: [],
    modelOptions: [],
    totalQuantity: '',
    plannedStartDate: '',
    plannedEndDate: '',
    status: 0,
    statusText: '待开始',
    statusList: [
      { value: 0, text: '待开始' },
      { value: 1, text: '进行中' },
      { value: 2, text: '已完成' },
      { value: 3, text: '已暂停' },
    ],
    statusIndex: 0,
    remark: '',
    loading: false,
  },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    adminApi.getOrders(1, 200).then((res) => {
      const list = (res.data && res.data.list) || [];
      const orderOptions = list.map((o) => ({ id: o.id, label: (o.order_name || o.order_no || '订单') + ' #' + o.id }));
      this.setData({ orderOptions });
      if (id) {
        adminApi.getProductionPlanDetail(id).then((res) => {
          const d = res.data || {};
          const oid = d.order_id || 0;
          const mid = d.model_id || 0;
          const oi = orderOptions.findIndex((o) => o.id === oid);
          this.setData({
            planName: d.plan_name || '',
            orderId: oid,
            orderLabel: oi >= 0 ? orderOptions[oi].label : '请选择订单',
            orderIndex: oi >= 0 ? oi : 0,
            totalQuantity: String(d.total_quantity || ''),
            plannedStartDate: timestampToDate(d.planned_start_time),
            plannedEndDate: timestampToDate(d.planned_end_time),
            remark: d.remark || '',
            status: d.status !== undefined ? d.status : 0,
            statusText: this.data.statusList[d.status >= 0 ? d.status : 0].text,
            statusIndex: d.status >= 0 && d.status <= 3 ? d.status : 0,
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
  pickStartDate(e) { this.setData({ plannedStartDate: e.detail.value }); },
  pickEndDate(e) { this.setData({ plannedEndDate: e.detail.value }); },
  pickStatus(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.statusList;
    if (list[i]) this.setData({ statusIndex: i, status: list[i].value, statusText: list[i].text });
  },
  inputPlanName(e) { this.setData({ planName: e.detail.value }); },
  inputTotalQuantity(e) { this.setData({ totalQuantity: e.detail.value }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },
  submit() {
    const { id, planName, orderId, modelId, totalQuantity, plannedStartDate, plannedEndDate, status, remark } = this.data;
    if (!planName || !planName.trim()) { wx.showToast({ title: '请输入计划名称', icon: 'none' }); return; }
    const qty = parseInt(totalQuantity, 10) || 0;
    if (!id && (orderId <= 0 || modelId <= 0 || qty <= 0)) {
      wx.showToast({ title: '请选择订单、型号并填写计划数量', icon: 'none' });
      return;
    }
    this.setData({ loading: true });
    const row = {
      plan_name: planName.trim(),
      order_id: orderId,
      model_id: modelId,
      total_quantity: qty,
      planned_start_time: dateToTimestamp(plannedStartDate),
      planned_end_time: dateToTimestamp(plannedEndDate),
      status,
      remark: (remark || '').trim(),
    };
    if (row.planned_start_time == null) delete row.planned_start_time;
    if (row.planned_end_time == null) delete row.planned_end_time;
    const p = id ? adminApi.updateProductionPlan({ id, row }) : adminApi.createProductionPlan({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
