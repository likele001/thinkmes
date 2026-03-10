const { adminApi } = require('../../utils/api.js');

Page({
  data: { code: '', result: null, searched: false, loading: false },
  onCodeInput(e) {
    this.setData({ code: (e.detail.value || '').trim(), result: null, searched: false });
  },
  query() {
    const code = (this.data.code || '').trim();
    if (!code) {
      wx.showToast({ title: '请输入追溯码', icon: 'none' });
      return;
    }
    this.setData({ loading: true, searched: true });
    adminApi.queryTraceCode(code).then((res) => {
      const result = res.data || null;
      let productName = '-';
      if (result && result.model && result.model.product) productName = result.model.product.name || '-';
      else if (result && result.model) productName = result.model.name || '-';
      this.setData({ result, productName, loading: false });
    }).catch(() => { this.setData({ result: null, loading: false }); });
  },
});
