const { adminApi } = require('../../utils/api.js');

function today() {
  const d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

Page({
  data: { startDate: today(), endDate: today(), stats: null, loading: false },

  onLoad() { this.load(); },

  load() {
    this.setData({ loading: true });
    adminApi.getReportStatistics({ start_date: this.data.startDate, end_date: this.data.endDate })
      .then((res) => { this.setData({ stats: res.data || null, loading: false }); })
      .catch(() => { this.setData({ loading: false }); });
  },

  onStartDateChange(e) { this.setData({ startDate: e.detail.value }); },
  onEndDateChange(e) { this.setData({ endDate: e.detail.value }); },
  goIndex() { wx.navigateTo({ url: '/pages/index/index' }); },
});
