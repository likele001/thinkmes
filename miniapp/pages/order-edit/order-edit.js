const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    detail: null,
    order_name: '',
    customer_id: '',
    customer_name: '',
    customer_phone: '',
    customerName: '',
    customerList: [],
    customerIndex: 0,
    delivery_date: '',
    status: 1,
    statusText: '进行中',
    statusList: [{ value: 0, text: '待生产' }, { value: 1, text: '进行中' }, { value: 2, text: '已完成' }, { value: 3, text: '已取消' }],
    statusIndex: 1,
    remark: '',
    modelList: [],
    modelRows: [{ model_index: 0, model_id: 0, model_label: '', quantity: 1 }],
    submitting: false,
  },

  onLoad(options) {
    const id = options.id || 0;
    this.setData({ id: id ? parseInt(id, 10) : 0 });
    this.loadCustomers();
    this.loadModels();
    if (this.data.id) this.loadOrder();
  },

  loadCustomers() {
    adminApi.getCustomerList(1, 500).then((res) => {
      const list = res.data && res.data.list ? res.data.list : [];
      this.setData({ customerList: list });
      if (this.data.id) this.matchCustomerIndex();
    }).catch(() => {});
  },

  loadModels() {
    adminApi.getModels(1, 500).then((res) => {
      const list = res.data && res.data.list ? res.data.list : [];
      const modelList = list.map((m) => {
        const p = m.product || {};
        const label = (p.name ? p.name + ' - ' : '') + (m.name || '');
        return { id: m.id, label };
      });
      this.setData({ modelList });
      this.syncModelRowsFromList();
    }).catch(() => {});
  },

  syncModelRowsFromList() {
    const modelList = this.data.modelList;
    const rows = this.data.modelRows.map((r) => {
      const idx = modelList.findIndex((m) => m.id === r.model_id);
      const found = idx >= 0 ? modelList[idx] : null;
      return { ...r, model_index: idx >= 0 ? idx : 0, model_label: found ? found.label : (r.model_label || '') };
    });
    this.setData({ modelRows: rows });
  },

  loadOrder() {
    if (!this.data.id) return;
    adminApi.getOrderDetail(this.data.id).then((res) => {
      const d = res.data || {};
      const statusList = this.data.statusList;
      let statusIndex = 0;
      for (let i = 0; i < statusList.length; i++) {
        if (statusList[i].value === (d.status ?? 1)) { statusIndex = i; break; }
      }
      let delivery_date = '';
      if (d.delivery_time) {
        const t = parseInt(d.delivery_time, 10);
        if (t) {
          const dt = new Date(t * 1000);
          delivery_date = dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');
        }
      }
      const orderModels = d.order_models || d.orderModels || [];
      const modelList = this.data.modelList;
      const modelRows = orderModels.length
        ? orderModels.map((om) => {
            const mid = om.model_id || 0;
            const model = om.model || {};
            const product = model.product || {};
            const label = (product.name ? product.name + ' - ' : '') + (model.name || '');
            const idx = modelList.findIndex((m) => m.id === mid);
            return {
              model_index: idx >= 0 ? idx : 0,
              model_id: mid,
              model_label: label,
              quantity: om.quantity || 1,
            };
          })
        : [{ model_index: 0, model_id: 0, model_label: '', quantity: 1 }];
      this.setData({
        detail: d,
        order_name: d.order_name || d.order_no || '',
        customer_id: d.customer_id || '',
        customer_name: d.customer_name || '',
        customer_phone: d.customer_phone || '',
        customerName: d.customer_name || '',
        delivery_date,
        status: d.status ?? 1,
        statusText: statusList[statusIndex].text,
        statusIndex,
        remark: d.remark || '',
        modelRows,
      });
      this.matchCustomerIndex();
      this.syncModelRowsFromList();
    }).catch((err) => {
      wx.showToast({ title: (err && err.msg) ? err.msg : '加载失败', icon: 'none' });
    });
  },

  matchCustomerIndex() {
    const list = this.data.customerList;
    const cid = this.data.customer_id;
    for (let i = 0; i < list.length; i++) {
      if (String(list[i].id) === String(cid)) {
        this.setData({
          customerIndex: i,
          customerName: list[i].customer_name || list[i].name || '',
          customer_name: list[i].customer_name || list[i].name || '',
          customer_phone: list[i].contact_phone || list[i].customer_phone || '',
        });
        return;
      }
    }
  },

  inputOrderName(e) { this.setData({ order_name: e.detail.value }); },
  inputCustomerName(e) { this.setData({ customer_name: e.detail.value }); },
  inputCustomerPhone(e) { this.setData({ customer_phone: e.detail.value }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },

  pickCustomer(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.customerList;
    const item = list[i];
    if (item) {
      this.setData({
        customerIndex: i,
        customer_id: item.id,
        customerName: item.customer_name || item.name || '',
        customer_name: item.customer_name || item.name || '',
        customer_phone: item.contact_phone || item.customer_phone || '',
      });
    }
  },

  pickDeliveryDate(e) {
    this.setData({ delivery_date: e.detail.value });
  },

  pickStatus(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.statusList;
    const item = list[i];
    if (item) this.setData({ statusIndex: i, status: item.value, statusText: item.text });
  },

  openModelSelect(e) {
    const rowIndex = parseInt(e.currentTarget.dataset.row, 10);
    const modelList = this.data.modelList;
    if (!modelList.length) { wx.showToast({ title: '型号列表加载中', icon: 'none' }); return; }
    getApp().globalData.modelSelectList = modelList;
    getApp().globalData.modelSelectRowIndex = rowIndex;
    wx.navigateTo({
      url: '/pages/model-select/model-select',
      events: {
        selectModel: (data) => {
          const r = getApp().globalData.modelSelectRowIndex;
          const rows = this.data.modelRows.slice();
          if (rows[r] !== undefined) {
            rows[r] = { ...rows[r], model_id: data.id, model_label: data.label };
            this.setData({ modelRows: rows });
          }
        },
      },
    });
  },

  pickModel(e) {
    const rowIndex = parseInt(e.currentTarget.dataset.row, 10);
    const idx = parseInt(e.detail.value, 10);
    const modelList = this.data.modelList;
    const item = modelList[idx];
    if (!item) return;
    const rows = this.data.modelRows.slice();
    rows[rowIndex] = { ...rows[rowIndex], model_index: idx, model_id: item.id, model_label: item.label };
    this.setData({ modelRows: rows });
  },

  inputQty(e) {
    const rowIndex = parseInt(e.currentTarget.dataset.row, 10);
    const qty = parseInt(e.detail.value, 10) || 0;
    const rows = this.data.modelRows.slice();
    rows[rowIndex] = { ...rows[rowIndex], quantity: qty <= 0 ? 1 : qty };
    this.setData({ modelRows: rows });
  },

  addModel() {
    const rows = this.data.modelRows.slice();
    rows.push({ model_index: 0, model_id: 0, model_label: '', quantity: 1 });
    this.setData({ modelRows: rows });
  },

  removeModel(e) {
    const rowIndex = parseInt(e.currentTarget.dataset.row, 10);
    const rows = this.data.modelRows.filter((_, i) => i !== rowIndex);
    if (rows.length === 0) rows.push({ model_index: 0, model_id: 0, model_label: '', quantity: 1 });
    this.setData({ modelRows: rows });
  },

  submit() {
    const { id, order_name, customer_id, customer_name, customer_phone, delivery_date, status, remark, modelRows } = this.data;
    if (!order_name.trim()) {
      wx.showToast({ title: '请输入订单名称', icon: 'none' });
      return;
    }
    const models = modelRows.filter((r) => r.model_id > 0).map((r) => ({ model_id: r.model_id, quantity: r.quantity <= 0 ? 1 : r.quantity }));
    if (models.length === 0) {
      wx.showToast({ title: '请至少添加一个订单型号及数量', icon: 'none' });
      return;
    }
    const row = {
      order_name: order_name.trim(),
      customer_id: customer_id || 0,
      customer_name: (customer_name || '').trim(),
      customer_phone: (customer_phone || '').trim(),
      status,
      remark: (remark || '').trim(),
    };
    if (delivery_date) row.delivery_time = delivery_date;
    this.setData({ submitting: true });
    if (id) {
      adminApi.updateOrder({ id, row, models }).then(() => {
        this.setData({ submitting: false });
        wx.showToast({ title: '保存成功' });
        setTimeout(() => wx.navigateBack(), 800);
      }).catch((err) => {
        this.setData({ submitting: false });
        wx.showToast({ title: (err && err.msg) ? err.msg : '保存失败', icon: 'none' });
      });
    } else {
      adminApi.createOrder({ row, models }).then(() => {
        this.setData({ submitting: false });
        wx.showToast({ title: '创建成功' });
        setTimeout(() => wx.navigateBack(), 800);
      }).catch((err) => {
        this.setData({ submitting: false });
        wx.showToast({ title: (err && err.msg) ? err.msg : '创建失败', icon: 'none' });
      });
    }
  },

  confirmDelete() {
    if (!this.data.id) return;
    wx.showModal({
      title: '确认删除',
      content: '确定删除该订单？',
      success: (res) => {
        if (res.confirm) {
          adminApi.deleteOrder(this.data.id).then(() => {
            wx.showToast({ title: '已删除' });
            setTimeout(() => wx.navigateBack(), 500);
          }).catch(() => {});
        }
      },
    });
  },
});
