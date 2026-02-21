const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    detail: null,
    loading: true,
  },

  onLoad(options) {
    const id = options.id || 0;
    if (!id) return;
    this.setData({ id });
    adminApi.getOrderDetail(id)
      .then((res) => {
        this.setData({ detail: res.data || null, loading: false });
      })
      .catch(() => { this.setData({ loading: false }); });
  },
});
