const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, detail: null, items: [], loading: true },
  onLoad(options) {
    const id = options.id || 0;
    if (!id) { this.setData({ loading: false }); return; }
    this.setData({ id });
    adminApi.getBomDetail(id).then((res) => {
      const d = res.data || null;
      if (d && d.status !== undefined) {
        const st = d.status;
        d.status_text = st === 0 ? '草稿' : (st === 1 ? '审核中' : (st === 2 ? '已发布' : '未知'));
      }
      this.setData({ detail: d, loading: false });
    }).catch(() => { this.setData({ loading: false }); });
    adminApi.getBomItems(id).then((res) => {
      this.setData({ items: (res.data && res.data.list) || res.data || [] });
    }).catch(() => {});
  },
  goEdit() {
    if (this.data.id) wx.navigateTo({ url: '/pages/bom-edit/bom-edit?id=' + this.data.id });
  },
  approveBom() {
    if (!this.data.id) return;
    wx.showModal({ title: '确认', content: '确定审核通过该BOM？', success: (res) => {
      if (res.confirm) adminApi.approveBom(this.data.id, 1).then(() => {
          wx.showToast({ title: '审核通过' });
          this.setData({ detail: { ...this.data.detail, status: 2, status_text: '已发布' } });
        }).catch(() => {});
    }});
  },
});
