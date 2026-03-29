const { restaurantApi } = require('../../utils/restaurant_api');

Page({
  data: {
    token: '',
    tableName: '-',
    categories: [],
    currentCatId: null,
    items: [],
    combos: [],
    showOpt: false,
    optItem: {},
    optGroups: [],
    selected: {},
  },
  onLoad(query) {
    const app = getApp();
    const token = (query && query.token) ? query.token : (app.globalData.tableToken || '');
    this.setData({ token });
    if (!token) {
      wx.showToast({ title: '缺少桌码token', icon: 'none' });
      return;
    }
    this.loadTable();
    this.loadMenu();
  },
  loadTable() {
    restaurantApi.tableInfo(this.data.token).then((r) => {
      if (!r || r.code !== 1) return;
      const d = r.data || {};
      const t = d.table || {};
      this.setData({ tableName: t.name || ('#' + (t.id || '')) });
    });
  },
  loadMenu() {
    restaurantApi.menu(this.data.token).then((r) => {
      if (!r || r.code !== 1) {
        wx.showToast({ title: (r && r.msg) ? r.msg : '加载失败', icon: 'none' });
        return;
      }
      const cats = (r.data && r.data.categories) ? r.data.categories : [];
      const current = cats.length ? cats[0].id : null;
      this.setData({ categories: cats, currentCatId: current });
      this.applyCat(current);
    });
  },
  applyCat(catId) {
    const cats = this.data.categories || [];
    let cat = null;
    for (let i = 0; i < cats.length; i++) {
      if (cats[i].id === catId) { cat = cats[i]; break; }
    }
    const items = (cat && cat.items) ? cat.items.map((it) => ({
      id: it.id,
      name: it.name,
      price: Number(it.price || 0).toFixed(2),
      option_groups: it.option_groups || [],
      hasOptions: (it.option_groups && it.option_groups.length) ? true : false,
    })) : [];
    const combos = (cat && cat.combos) ? cat.combos.map((c) => {
      const its = c.items || [];
      const parts = its.map((x) => (x.name || ('#' + x.item_id)) + '*' + (x.quantity || 1));
      return { id: c.id, name: c.name, price: Number(c.price || 0).toFixed(2), itemsText: parts.join('，') };
    }) : [];
    this.setData({ items, combos });
  },
  switchCat(e) {
    const id = parseInt(e.currentTarget.dataset.id, 10);
    this.setData({ currentCatId: id });
    this.applyCat(id);
  },
  goCart() {
    wx.navigateTo({ url: '/pages/cart/index?token=' + encodeURIComponent(this.data.token) });
  },
  addCombo(e) {
    const comboId = parseInt(e.currentTarget.dataset.id, 10);
    restaurantApi.cartAdd({ token: this.data.token, product_type: 'combo', combo_id: comboId, quantity: 1 }).then((r) => {
      if (r && r.code === 1) wx.showToast({ title: '已加入' });
      else wx.showToast({ title: (r && r.msg) ? r.msg : '失败', icon: 'none' });
    });
  },
  tapItem(e) {
    const id = parseInt(e.currentTarget.dataset.id, 10);
    const items = this.data.items || [];
    let it = null;
    for (let i = 0; i < items.length; i++) if (items[i].id === id) { it = items[i]; break; }
    if (!it) return;
    if (!it.hasOptions) {
      restaurantApi.cartAdd({ token: this.data.token, product_type: 'item', item_id: it.id, quantity: 1, option_ids: [] }).then((r) => {
        if (r && r.code === 1) wx.showToast({ title: '已加入' });
        else wx.showToast({ title: (r && r.msg) ? r.msg : '失败', icon: 'none' });
      });
      return;
    }
    const groups = (it.option_groups || []).map((g) => {
      const min = parseInt(g.min_select || 0, 10) || 0;
      const max = parseInt(g.max_select || 0, 10) || 1;
      const required = g.required === 1;
      const minNeed = required ? (min < 1 ? 1 : min) : 0;
      const ruleText = (required ? '必选' : '可选') + ' ' + minNeed + '-' + max + '项';
      const opts = (g.options || []).map((o) => ({
        id: o.id,
        name: o.name,
        price_delta: Number(o.price_delta || 0).toFixed(2),
        group_id: g.id,
        checked: false,
      }));
      return { id: g.id, name: g.name, min: minNeed, max, required, ruleText, options: opts };
    });
    this.setData({ showOpt: true, optItem: it, optGroups: groups, selected: {} });
  },
  toggleOption(e) {
    const optId = parseInt(e.detail.value[0] || e.currentTarget.value || 0, 10);
    const gid = parseInt(e.currentTarget.dataset.gid, 10);
    if (!optId || !gid) return;
    const groups = this.data.optGroups || [];
    for (let i = 0; i < groups.length; i++) {
      const g = groups[i];
      if (g.id !== gid) continue;
      let count = 0;
      for (let j = 0; j < g.options.length; j++) if (g.options[j].checked) count++;
      for (let j = 0; j < g.options.length; j++) {
        const o = g.options[j];
        if (o.id === optId) {
          const next = !o.checked;
          if (next && count >= g.max) {
            wx.showToast({ title: '最多选择' + g.max + '项', icon: 'none' });
            return;
          }
          o.checked = next;
          break;
        }
      }
    }
    this.setData({ optGroups: groups });
  },
  closeOpt() {
    this.setData({ showOpt: false });
  },
  confirmOpt() {
    const groups = this.data.optGroups || [];
    const optionIds = [];
    for (let i = 0; i < groups.length; i++) {
      const g = groups[i];
      let count = 0;
      for (let j = 0; j < g.options.length; j++) {
        if (g.options[j].checked) {
          count++;
          optionIds.push(g.options[j].id);
        }
      }
      if (g.required && count < g.min) {
        wx.showToast({ title: '请在「' + g.name + '」至少选' + g.min + '项', icon: 'none' });
        return;
      }
      if (count > g.max) {
        wx.showToast({ title: '「' + g.name + '」最多选' + g.max + '项', icon: 'none' });
        return;
      }
    }
    restaurantApi.cartAdd({ token: this.data.token, product_type: 'item', item_id: this.data.optItem.id, quantity: 1, option_ids: optionIds }).then((r) => {
      if (r && r.code === 1) {
        wx.showToast({ title: '已加入' });
        this.setData({ showOpt: false });
      } else {
        wx.showToast({ title: (r && r.msg) ? r.msg : '失败', icon: 'none' });
      }
    });
  },
  noop() {},
});

