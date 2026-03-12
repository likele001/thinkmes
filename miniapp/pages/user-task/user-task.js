const { userApi } = require('../../utils/api.js');

Page({
  data: {
    allocationId: 0,
    task: {},
    loading: true,
    quantity: '',
    submitting: false,
  },

  onLoad(options) {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    const allocationId = parseInt(options.allocation_id, 10) || 0;
    this.setData({ allocationId });
    if (allocationId) this.load();
    else this.setData({ loading: false });
  },

  load() {
    const { allocationId } = this.data;
    if (!allocationId) return this.setData({ loading: false });
    this.setData({ loading: true });
    userApi.getTaskInfo(allocationId)
      .then((res) => {
        const d = res.data || {};
        this.setData({
          task: {
            allocation_id: d.allocation_id,
            product_name: d.product_name || '产品',
            model_name: d.model_name || '',
            process_name: d.process_name || '',
            order_no: d.order_no || '',
            order_name: d.order_name || '',
            assign_qty: d.assign_qty ?? 0,
            reported_qty: d.reported_qty ?? 0,
            pending_qty: d.pending_qty ?? 0,
          },
          loading: false,
        });
      })
      .catch(() => this.setData({ loading: false }));
  },

  inputQuantity(e) {
    this.setData({ quantity: e.detail.value });
  },

  submit() {
    const { allocationId, task, quantity } = this.data;
    const qty = parseInt(String(quantity).trim(), 10);
    if (!allocationId || !task.allocation_id) {
      wx.showToast({ title: '任务无效', icon: 'none' });
      return;
    }
    if (isNaN(qty) || qty < 1) {
      wx.showToast({ title: '请输入有效数量', icon: 'none' });
      return;
    }
    const pending = (task.pending_qty || 0);
    if (qty > pending) {
      wx.showToast({ title: '数量不能超过待报数量 ' + pending, icon: 'none' });
      return;
    }
    this.setData({ submitting: true });
    const itemNos = [];
    for (let i = 0; i < qty; i++) {
      itemNos.push(String(i + 1));
    }
    userApi.submitReport({
      allocation_id: allocationId,
      work_type: 'piece',
      quantity: qty,
      item_nos: itemNos,
    })
      .then(() => {
        this.setData({ submitting: false, quantity: '' });
        wx.showToast({ title: '报工成功', icon: 'success' });
        this.load();
      })
      .catch(() => this.setData({ submitting: false }));
  },

  goBack() {
    wx.navigateBack();
  },
});
