const { adminApi } = require('../../utils/api.js');

Page({
  data: { list: [], total: 0, page: 1, limit: 20, loading: false, noMore: false, statusIndex: 0, statusList: ['全部', '启用', '禁用'] },

  onLoad() { this.load(); },

  load() {
    if (this.data.loading) return;
    if (this.data.noMore && this.data.page > 1) return;
    this.setData({ loading: true });
    adminApi.getProcesses(this.data.page, this.data.limit)
      .then((res) => {
        const d = res.data || {};
        const newList = d.list || d.rows || [];
        const list = this.data.page === 1 ? newList : (this.data.list || []).concat(newList);
        const total = d.total || list.length;
        this.setData({ list, total, loading: false, noMore: list.length >= total });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  onReachBottom() { if (!this.data.noMore && !this.data.loading) { this.setData({ page: this.data.page + 1 }); this.load(); } },
  onStatusChange(e) { const i = parseInt(e.detail.value, 10); this.setData({ statusIndex: i, page: 1, list: [], noMore: false }); this.load(); },

  goIndex() { wx.navigateTo({ url: '/pages/index/index' }); },
  goAdd() { wx.navigateTo({ url: '/pages/process-edit/process-edit' }); },
  goEdit(e) { const id = e.currentTarget.dataset.id; if (id) wx.navigateTo({ url: '/pages/process-edit/process-edit?id=' + id }); },
  goPrices(e) { const id = e.currentTarget.dataset.id; if (id) wx.navigateTo({ url: '/pages/process-prices/process-prices?process_id=' + id }); },
  confirmDelete(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    wx.showModal({ title: '确认删除', content: '确定删除该工序？', success: (res) => {
      if (res.confirm) adminApi.deleteProcess(id).then(() => { wx.showToast({ title: '已删除' }); this.setData({ page: 1, list: [], noMore: false }); this.load(); }).catch(() => {});
    }});
  },
});
