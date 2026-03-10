const { adminApi } = require('../../utils/api.js');

Page({
  data: { reportId: '', result: null },
  onReportIdInput(e) {
    this.setData({ reportId: (e.detail.value || '').trim(), result: null });
  },
  generate() {
    const reportId = (this.data.reportId || '').trim();
    if (!reportId) {
      wx.showToast({ title: '请输入报工ID', icon: 'none' });
      return;
    }
    adminApi.generateTraceCode({ report_id: parseInt(reportId, 10) }).then((res) => {
      const d = res.data || {};
      this.setData({ result: { trace_code: d.trace_code || '', qr_url: d.qr_url || '' } });
      wx.showToast({ title: res.msg || '生成成功' });
    }).catch(() => {});
  },
});
