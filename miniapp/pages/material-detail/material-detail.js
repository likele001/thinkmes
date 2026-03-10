const { adminApi } = require('../../utils/api.js');

Page({
  data: { id: 0, detail: null, loading: true },
  onLoad(options) {
    const id = options.id || 0;
    if (!id) { this.setData({ loading: false }); return; }
    this.setData({ id });
    adminApi.getMaterialDetail(id)
      .then((res) => {
        this.setData({ detail: res.data || null, loading: false });
      })
      .catch(() => { this.setData({ loading: false }); });
  },
  goEdit() {
    if (this.data.id) wx.navigateTo({ url: '/pages/material-edit/material-edit?id=' + this.data.id });
  },
});
