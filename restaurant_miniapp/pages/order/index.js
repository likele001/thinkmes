const { restaurantApi } = require('../../utils/restaurant_api');

function fmt(ts) {
  if (!ts) return '-';
  const d = new Date(ts * 1000);
  const pad = (n) => (n < 10 ? '0' + n : '' + n);
  return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

Page({
  data: {
    token: '',
    orders: [],
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
    restaurantApi.orderList(this.data.token).then((r) => {
      if (!r || r.code !== 1) return;
      const list = (r.data && r.data.list) ? r.data.list : [];
      const orders = list.map((o) => ({
        id: o.id,
        order_no: o.order_no,
        total_amount: Number(o.total_amount || 0).toFixed(2),
        status: o.status,
        timeText: fmt(o.create_time),
        showDetail: false,
        loading: false,
        detailLoaded: false,
        items: [],
      }));
      this.setData({ orders });
    });
  },
  toggleDetail(e) {
    const id = parseInt(e.currentTarget.dataset.id, 10);
    const orders = this.data.orders.slice();
    let idx = -1;
    for (let i = 0; i < orders.length; i++) if (orders[i].id === id) { idx = i; break; }
    if (idx < 0) return;
    const o = orders[idx];
    o.showDetail = !o.showDetail;
    orders[idx] = o;
    this.setData({ orders });
    if (!o.showDetail || o.detailLoaded) return;
    o.loading = true;
    orders[idx] = o;
    this.setData({ orders });
    restaurantApi.orderDetail(this.data.token, id).then((r) => {
      o.loading = false;
      if (r && r.code === 1 && r.data) {
        o.detailLoaded = true;
        o.items = (r.data.items || []).map((it) => ({
          id: it.item_id + '_' + (it.option_key || '') + '_' + (it.combo_id || 0),
          name: it.name || ('#' + it.item_id),
          options_text: it.options_text || '',
          quantity: it.quantity || 0,
        }));
      }
      orders[idx] = o;
      this.setData({ orders });
    });
  },
});

