const { restaurantApi } = require('../../utils/restaurant_api');

Page({
  data: {
    token: '',
    items: [],
    total: '0.00',
  },
  onLoad(query) {
    const app = getApp();
    const token = (query && query.token) ? query.token : (app.globalData.tableToken || '');
    this.setData({ token });
  },
  onShow() {
    if (!this.data.token) return;
    this.refresh();
  },
  refresh() {
    restaurantApi.cartGet(this.data.token).then((r) => {
      if (!r || r.code !== 1) return;
      const d = r.data || {};
      const items = (d.items || []).map((x) => ({
        id: x.id,
        name: x.name,
        price: Number(x.price || 0).toFixed(2),
        quantity: x.quantity,
      }));
      this.setData({ items, total: Number(d.total_amount || 0).toFixed(2) });
    });
  },
  chgQty(e) {
    const id = parseInt(e.currentTarget.dataset.id, 10);
    const delta = parseInt(e.currentTarget.dataset.delta, 10);
    const items = this.data.items || [];
    let cur = null;
    for (let i = 0; i < items.length; i++) if (items[i].id === id) { cur = items[i]; break; }
    if (!cur) return;
    const next = Math.max(0, Number(cur.quantity || 0) + delta);
    restaurantApi.cartUpdate({ token: this.data.token, id, quantity: next }).then(() => this.refresh());
  },
  remove(e) {
    const id = parseInt(e.currentTarget.dataset.id, 10);
    restaurantApi.cartRemove({ token: this.data.token, id }).then(() => this.refresh());
  },
  clear() {
    restaurantApi.cartClear({ token: this.data.token }).then(() => this.refresh());
  },
  checkout() {
    restaurantApi.orderCreate({ token: this.data.token, remark: '' }).then((r) => {
      if (!r || r.code !== 1) {
        wx.showToast({ title: (r && r.msg) ? r.msg : '下单失败', icon: 'none' });
        return;
      }
      wx.showToast({ title: '下单成功' });
      wx.navigateTo({ url: '/pages/order/index?token=' + encodeURIComponent(this.data.token) });
    });
  },
});

