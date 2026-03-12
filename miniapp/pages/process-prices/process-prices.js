const { adminApi } = require('../../utils/api.js');

Page({
  data: { list: [], page: 1, limit: 20, loading: false, noMore: false, processId: '' },

  onLoad(options) {
    const processId = options.process_id || '';
    this.setData({ processId });
    this.load();
  },

  load() {
    if (this.data.loading) return;
    this.setData({ loading: true });
    adminApi.getProcessPriceList(this.data.page, this.data.limit, this.data.processId || undefined)
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
  goAdd() { wx.navigateTo({ url: '/pages/process-price-edit/process-price-edit' }); },
  goEdit(e) { const id = e.currentTarget.dataset.id; if (id) wx.navigateTo({ url: '/pages/process-price-edit/process-price-edit?id=' + id }); },
  confirmDelete(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    wx.showModal({ title: '确认删除', content: '确定删除该工价？', success: (res) => {
      if (res.confirm) adminApi.deleteProcessPrice(id).then(() => { wx.showToast({ title: '已删除' }); this.setData({ page: 1, list: [], noMore: false }); this.load(); }).catch(() => {});
    }});
  },
});
