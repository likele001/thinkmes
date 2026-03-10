const { adminApi } = require('../../utils/api.js');

function timestampToDate(ts) {
  if (!ts || ts <= 0) return '';
  const d = new Date(ts * 1000);
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return y + '-' + m + '-' + day;
}

function dateToTimestamp(str) {
  if (!str || !str.trim()) return null;
  const d = new Date(str.trim());
  return isNaN(d.getTime()) ? null : Math.floor(d.getTime() / 1000);
}

Page({
  data: {
    id: 0,
    orderId: 0,
    orderLabel: '请选择订单',
    orderIndex: 0,
    orderOptions: [],
    modelOptions: [],
    shipmentTimeDate: '',
    remark: '',
    models: [{ model_id: 0, modelIndex: 0, modelLabel: '选择型号', quantity: '' }],
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
        adminApi.getShipmentDetail(id).then((res) => {
          const d = res.data || {};
          const oid = d.order_id || 0;
          const oi = orderOptions.findIndex((o) => o.id === oid);
          this.setData({
            orderId: oid,
            orderLabel: oi >= 0 ? orderOptions[oi].label : '请选择订单',
            orderIndex: oi >= 0 ? oi : 0,
            shipmentTimeDate: timestampToDate(d.shipment_time),
            remark: d.remark || '',
          });
          if (oid) {
            adminApi.getOrderModels(oid).then((r) => {
              const orderModels = (r.data && r.data.list) || [];
              const modelOptions = orderModels.map((m) => ({
                id: m.model_id,
                label: (m.model && m.model.name ? m.model.name : '') + (m.product_name ? ' - ' + m.product_name : '') || '型号' + m.model_id,
              }));
              const items = (d.items || []).map((it) => {
                const mi = modelOptions.findIndex((o) => o.id === it.model_id);
                return {
                  model_id: it.model_id,
                  modelIndex: mi >= 0 ? mi : 0,
                  modelLabel: mi >= 0 ? modelOptions[mi].label : '选择型号',
                  quantity: String(it.quantity || ''),
                };
              });
              this.setData({
                modelOptions: modelOptions.length ? modelOptions : [{ id: 0, label: '无型号' }],
                models: items.length ? items : [{ model_id: 0, modelIndex: 0, modelLabel: '选择型号', quantity: '' }],
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
      models: [{ model_id: 0, modelIndex: 0, modelLabel: '选择型号', quantity: '' }],
    });
    adminApi.getOrderModels(orderId).then((r) => {
      const orderModels = (r.data && r.data.list) || [];
      const modelOptions = orderModels.map((m) => ({
        id: m.model_id,
        label: (m.model && m.model.name ? m.model.name : '') + (m.product_name ? ' - ' + m.product_name : '') || '型号' + m.model_id,
      }));
      this.setData({
        modelOptions: modelOptions.length ? modelOptions : [{ id: 0, label: '无型号' }],
        models: [{ model_id: 0, modelIndex: 0, modelLabel: '选择型号', quantity: '' }],
      });
    }).catch(() => {});
  },
  pickShipmentTime(e) { this.setData({ shipmentTimeDate: e.detail.value }); },
  openModelSelectForRow(e) {
    const rowIndex = parseInt(e.currentTarget.dataset.idx, 10);
    const modelOptions = this.data.modelOptions;
    if (!modelOptions.length || (modelOptions.length === 1 && modelOptions[0].id === 0)) {
      wx.showToast({ title: '请先选择订单', icon: 'none' });
      return;
    }
    const list = modelOptions.filter((o) => o.id);
    if (!list.length) { wx.showToast({ title: '该订单暂无型号', icon: 'none' }); return; }
    getApp().globalData.modelSelectList = list;
    getApp().globalData.modelSelectRowIndex = rowIndex;
    wx.navigateTo({
      url: '/pages/model-select/model-select',
      events: {
        selectModel: (data) => {
          const r = getApp().globalData.modelSelectRowIndex;
          const models = this.data.models.slice();
          if (models[r] !== undefined) {
            models[r] = { ...models[r], model_id: data.id, modelLabel: data.label };
            this.setData({ models });
          }
        },
      },
    });
  },
  pickModelRow(e) {
    const idx = parseInt(e.currentTarget.dataset.idx, 10);
    const i = parseInt(e.detail.value, 10);
    const opts = this.data.modelOptions;
    const models = this.data.models.slice();
    if (opts[i] && models[idx] !== undefined) {
      models[idx] = { ...models[idx], modelIndex: i, model_id: opts[i].id, modelLabel: opts[i].label };
      this.setData({ models });
    }
  },
  inputModelQty(e) {
    const idx = parseInt(e.currentTarget.dataset.idx, 10);
    const models = this.data.models.slice();
    if (models[idx] !== undefined) {
      models[idx].quantity = e.detail.value;
      this.setData({ models });
    }
  },
  addModelRow() {
    const models = this.data.models.slice();
    models.push({ model_id: 0, modelIndex: 0, modelLabel: '选择型号', quantity: '' });
    this.setData({ models });
  },
  delModelRow(e) {
    const idx = parseInt(e.currentTarget.dataset.idx, 10);
    const models = this.data.models.filter((_, i) => i !== idx);
    if (models.length === 0) models.push({ model_id: 0, modelIndex: 0, modelLabel: '选择型号', quantity: '' });
    this.setData({ models });
  },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },
  submit() {
    const { id, orderId, shipmentTimeDate, remark, models, modelOptions } = this.data;
    if (!orderId) { wx.showToast({ title: '请选择订单', icon: 'none' }); return; }
    const items = models
      .map((m) => ({ model_id: m.model_id, quantity: parseInt(m.quantity, 10) || 0 }))
      .filter((it) => it.model_id > 0 && it.quantity > 0);
    if (!id && items.length === 0) {
      wx.showToast({ title: '请添加至少一条发货明细（型号+数量）', icon: 'none' });
      return;
    }
    this.setData({ loading: true });
    const row = {
      order_id: orderId,
      shipment_time: dateToTimestamp(shipmentTimeDate),
      remark: (remark || '').trim(),
    };
    if (row.shipment_time == null) delete row.shipment_time;
    const p = id
      ? adminApi.updateShipment({ id, row, items })
      : adminApi.createShipment({ row, items });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
