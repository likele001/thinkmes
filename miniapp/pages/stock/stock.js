const { adminApi } = require('../../utils/api.js');

Page({
  data: { list: [], page: 1, limit: 20, loading: false, noMore: false, warehouseId: '' },

  onLoad(options) {
    const warehouseId = options.warehouse_id || '';
    this.setData({ warehouseId });
    this.load();
  },

  load() {
    if (this.data.loading) return;
    this.setData({ loading: true });
    adminApi.getStockList(this.data.page, this.data.limit)
      .then((res) => {
        const d = res.data || {};
        let newList = d.list || d.rows || [];
        if (this.data.warehouseId) newList = newList.filter((i) => String(i.warehouse_id) === String(this.data.warehouseId));
        const list = this.data.page === 1 ? newList : (this.data.list || []).concat(newList);
        const total = d.total || list.length;
        this.setData({ list, loading: false, noMore: list.length >= total });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  onReachBottom() { if (!this.data.noMore && !this.data.loading) { this.setData({ page: this.data.page + 1 }); this.load(); } },

  goBack() { wx.navigateBack(); },
  goOp() { wx.navigateTo({ url: '/pages/stock-op/stock-op' }); },
});
