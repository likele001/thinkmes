const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, plan_name: '', plan_code: '', order_id: '', model_id: '', total_quantity: '', loading: false },

  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    if (id) {
      adminApi.getProductionPlanDetail(id).then((res) => {
        const d = res.data || {};
        this.setData({ plan_name: d.plan_name || '', plan_code: d.plan_code || '', order_id: d.order_id != null ? String(d.order_id) : '', model_id: d.model_id != null ? String(d.model_id) : '', total_quantity: d.total_quantity != null ? String(d.total_quantity) : '' });
      }).catch(() => {});
    }
  },

  goBack() { wx.navigateBack(); },
  inputName(e) { this.setData({ plan_name: e.detail.value }); },
  inputCode(e) { this.setData({ plan_code: e.detail.value }); },
  inputOrderId(e) { this.setData({ order_id: e.detail.value }); },
  inputModelId(e) { this.setData({ model_id: e.detail.value }); },
  inputQuantity(e) { this.setData({ total_quantity: e.detail.value }); },

  submit() {
    const { id, plan_name, plan_code, order_id, model_id, total_quantity } = this.data;
    if (!plan_name || !plan_name.trim()) { wx.showToast({ title: '请输入计划名称', icon: 'none' }); return; }
    if (!model_id || !total_quantity || isNaN(parseInt(total_quantity, 10))) { wx.showToast({ title: '请填写型号ID和计划数量', icon: 'none' }); return; }
    this.setData({ loading: true });
    const data = { plan_name: plan_name.trim(), plan_code: (plan_code || '').trim(), order_id: order_id ? parseInt(order_id, 10) : undefined, model_id: parseInt(model_id, 10), total_quantity: parseInt(total_quantity, 10) };
    const p = id ? adminApi.updateProductionPlan({ id, ...data }) : adminApi.createProductionPlan(data);
    p.then(() => { wx.showToast({ title: '保存成功' }); setTimeout(() => wx.navigateBack(), 500); }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
