const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    reportId: 0,
    detail: null,
    status: 1,
    auditReason: '',
    submitting: false,
  },

  onLoad(options) {
    const id = options.report_id || 0;
    if (!id) return;
    this.setData({ reportId: id });
    adminApi.getReportDetail(id)
      .then((res) => this.setData({ detail: res.data }))
      .catch(() => {});
  },

  radioChange(e) {
    this.setData({ status: parseInt(e.detail.value, 10) });
  },

  inputReason(e) {
    this.setData({ auditReason: e.detail.value });
  },

  submit() {
    const { reportId, status, auditReason } = this.data;
    this.setData({ submitting: true });
    adminApi.auditReport(reportId, status, auditReason)
      .then(() => {
        this.setData({ submitting: false });
        wx.showToast({ title: '审核成功' });
        setTimeout(() => wx.navigateBack(), 1000);
      })
      .catch(() => { this.setData({ submitting: false }); });
  },
});
