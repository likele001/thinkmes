const { adminApi } = require('../../utils/api.js');

Page({
  data: { productId: '', list: [], page: 1, limit: 20, loading: false, noMore: false },

  onLoad(options) {
    const productId = options.product_id || '';
    this.setData({ productId });
    this.load();
  },

  load() {
    if (this.data.loading) return;
    this.setData({ loading: true });
    adminApi.getModels(this.data.page, this.data.limit, this.data.productId || undefined)
      .then((res) => {
        const d = res.data || {};
        const newList = d.list || d.rows || [];
        const list = this.data.page === 1 ? newList : (this.data.list || []).concat(newList);
        const total = d.total || list.length;
        this.setData({ list, loading: false, noMore: list.length >= total });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  onReachBottom() { if (!this.data.noMore && !this.data.loading) { this.setData({ page: this.data.page + 1 }); this.load(); } },

  goBack() { wx.navigateBack(); },
  goAdd() { wx.navigateTo({ url: '/pages/product-batch-models/product-batch-models?product_id=' + this.data.productId + '&action=add' }); },
  goEdit(e) { const id = e.currentTarget.dataset.id; if (id) wx.showToast({ title: '请使用工价页维护', icon: 'none' }); },
  goPrices(e) { const id = e.currentTarget.dataset.id; if (id) wx.navigateTo({ url: '/pages/process-prices/process-prices?model_id=' + id }); },
  confirmDelete(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    wx.showModal({ title: '确认删除', content: '确定删除该型号？', success: (res) => {
      if (res.confirm) adminApi.deleteProductModel(id).then(() => { wx.showToast({ title: '已删除' }); this.setData({ page: 1, list: [], noMore: false }); this.load(); }).catch(() => {});
    }});
  },
});
