const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    orderId: 0,
    orderLabel: '请选择订单',
    orderIndex: 0,
    orderOptions: [],
    type: 1,
    typeText: '退货',
    typeList: [
      { value: 1, text: '退货' },
      { value: 2, text: '换货' },
      { value: 3, text: '维修' },
    ],
    typeIndex: 0,
    status: 0,
    statusText: '待处理',
    statusList: [
      { value: 0, text: '待处理' },
      { value: 1, text: '处理中' },
      { value: 2, text: '已完成' },
      { value: 3, text: '已取消' },
    ],
    statusIndex: 0,
    content: '',
    loading: false,
  },
  goBack() { wx.navigateBack(); },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    adminApi.getOrders(1, 200).then((res) => {
      const list = (res.data && res.data.list) || [];
      const orderOptions = list.map((o) => ({ id: o.id, label: (o.order_name || o.order_no || '订单') + ' #' + o.id }));
      this.setData({ orderOptions });
      if (id) {
        adminApi.getAfterSalesDetail(id).then((res) => {
          const d = res.data || {};
          const oid = d.order_id || 0;
          const oi = orderOptions.findIndex((o) => o.id === oid);
          const type = d.type !== undefined ? d.type : 1;
          const typeIndex = this.data.typeList.findIndex((t) => t.value === type);
          const status = d.status !== undefined ? d.status : 0;
          const statusIndex = this.data.statusList.findIndex((s) => s.value === status);
          this.setData({
            orderId: oid,
            orderLabel: oi >= 0 ? orderOptions[oi].label : '请选择订单',
            orderIndex: oi >= 0 ? oi : 0,
            type,
            typeText: this.data.typeList[typeIndex >= 0 ? typeIndex : 0].text,
            typeIndex: typeIndex >= 0 ? typeIndex : 0,
            status,
            statusText: this.data.statusList[statusIndex >= 0 ? statusIndex : 0].text,
            statusIndex: statusIndex >= 0 ? statusIndex : 0,
            content: d.content || d.remark || '',
          });
        }).catch(() => {});
      }
    }).catch(() => {});
  },
  pickOrder(e) {
    const i = parseInt(e.detail.value, 10);
    const opts = this.data.orderOptions;
    if (opts[i]) this.setData({ orderIndex: i, orderId: opts[i].id, orderLabel: opts[i].label });
  },
  pickType(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.typeList;
    if (list[i]) this.setData({ typeIndex: i, type: list[i].value, typeText: list[i].text });
  },
  pickStatus(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.statusList;
    if (list[i]) this.setData({ statusIndex: i, status: list[i].value, statusText: list[i].text });
  },
  inputContent(e) { this.setData({ content: e.detail.value }); },
  submit() {
    const { id, orderId, type, status, content } = this.data;
    if (!orderId) { wx.showToast({ title: '请选择订单', icon: 'none' }); return; }
    this.setData({ loading: true });
    const row = {
      order_id: orderId,
      type,
      status,
      content: (content || '').trim(),
      remark: (content || '').trim(),
    };
    const p = id ? adminApi.updateAfterSales({ id, row }) : adminApi.createAfterSales({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
