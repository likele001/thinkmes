const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, plan: null, loading: true, rows: [{ processId: 0, processLabel: '', userId: 0, userLabel: '', quantity: '' }], processOptions: [], userOptions: [], submitting: false },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    if (!id) { this.setData({ loading: false }); return; }
    this.setData({ id });
    Promise.all([
      adminApi.getProductionPlanDetail(id),
      adminApi.getProcesses(1, 200),
      adminApi.getUsers(1, 200),
    ]).then(([resPlan, resP, resU]) => {
      const plan = resPlan.data || null;
      const processOptions = (resP.data && resP.data.list) || [];
      const userOptions = (resU.data && resU.data.list) || [];
      const uOpts = userOptions.map((u) => ({ id: u.id, label: (u.nickname || u.username || '') + ' (ID:' + u.id + ')' }));
      this.setData({ plan, processOptions, userOptions: uOpts, loading: false });
    }).catch(() => { this.setData({ loading: false }); });
  },
  goBack() { wx.navigateBack(); },
  pickProcess(e) {
    const idx = parseInt(e.currentTarget.dataset.idx, 10);
    const list = this.data.processOptions;
    if (!list.length) { wx.showToast({ title: '暂无工序', icon: 'none' }); return; }
    const opts = list.map((p) => ({ id: p.id, label: p.name }));
    getApp().globalData.modelSelectList = opts;
    getApp().globalData.planAllocateRowIndex = idx;
    getApp().globalData.planAllocateField = 'process';
    wx.navigateTo({
      url: '/pages/model-select/model-select',
      events: {
        selectModel: (data) => {
          const i = getApp().globalData.planAllocateRowIndex;
          const rows = this.data.rows.slice();
          if (rows[i] !== undefined) {
            rows[i] = { ...rows[i], processId: data.id, processLabel: data.label };
            this.setData({ rows });
          }
        },
      },
    });
  },
  pickUser(e) {
    const idx = parseInt(e.currentTarget.dataset.idx, 10);
    const list = this.data.userOptions;
    if (!list.length) { wx.showToast({ title: '暂无员工', icon: 'none' }); return; }
    getApp().globalData.modelSelectList = list;
    getApp().globalData.planAllocateRowIndex = idx;
    getApp().globalData.planAllocateField = 'user';
    wx.navigateTo({
      url: '/pages/model-select/model-select',
      events: {
        selectModel: (data) => {
          const i = getApp().globalData.planAllocateRowIndex;
          const rows = this.data.rows.slice();
          if (rows[i] !== undefined) {
            rows[i] = { ...rows[i], userId: data.id, userLabel: data.label };
            this.setData({ rows });
          }
        },
      },
    });
  },
  inputQty(e) {
    const idx = parseInt(e.currentTarget.dataset.idx, 10);
    const rows = this.data.rows.slice();
    if (rows[idx] !== undefined) {
      rows[idx].quantity = e.detail.value;
      this.setData({ rows });
    }
  },
  addRow() {
    const rows = this.data.rows.slice();
    rows.push({ processId: 0, processLabel: '', userId: 0, userLabel: '', quantity: '' });
    this.setData({ rows });
  },
  submit() {
    const { plan, rows } = this.data;
    if (!plan || !plan.order_id || !plan.model_id) {
      wx.showToast({ title: '计划数据不完整', icon: 'none' });
      return;
    }
    const allocations = rows
      .filter((r) => r.processId > 0 && r.userId > 0 && (parseInt(r.quantity, 10) || 0) > 0)
      .map((r) => ({ model_id: plan.model_id, process_id: r.processId, user_id: r.userId, quantity: parseInt(r.quantity, 10) || 0 }));
    if (allocations.length === 0) {
      wx.showToast({ title: '请至少添加一行有效分工（工序+员工+数量）', icon: 'none' });
      return;
    }
    this.setData({ submitting: true });
    adminApi.batchCreateAllocation({ order_id: plan.order_id, plan_id: plan.id, allocations })
      .then(() => {
        wx.showToast({ title: '分配成功' });
        setTimeout(() => wx.navigateBack(), 1000);
      })
      .catch(() => {})
      .finally(() => { this.setData({ submitting: false }); });
  },
});
