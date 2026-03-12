const { adminApi } = require('../../utils/api.js');

Page({
  data: { code: '', result: null, resultRows: [], loading: false },

  goBack() { wx.navigateBack(); },
  inputCode(e) { this.setData({ code: e.detail.value }); },

  query() {
    const code = (this.data.code || '').trim();
    if (!code) { wx.showToast({ title: '请输入追溯码', icon: 'none' }); return; }
    this.setData({ loading: true, result: null });
    adminApi.queryTraceCode(code).then((res) => {
      const d = res.data || {};
      const rows = [];
      if (d.code) rows.push({ label: '追溯码', value: d.code });
      if (d.product_name) rows.push({ label: '产品', value: d.product_name });
      if (d.model_name) rows.push({ label: '型号', value: d.model_name });
      if (d.createtime_text) rows.push({ label: '生成时间', value: d.createtime_text });
      this.setData({ result: d, resultRows: rows.length ? rows : [{ label: '结果', value: JSON.stringify(d) }], loading: false });
    }).catch(() => { this.setData({ loading: false }); });
  },
});
