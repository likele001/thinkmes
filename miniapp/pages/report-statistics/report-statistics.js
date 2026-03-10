const { adminApi } = require('../../utils/api.js');

function monthStart() {
  const d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-01';
}
function todayStr() {
  const d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

Page({
  data: {
    startDate: monthStart(),
    endDate: todayStr(),
    todayStr: todayStr(),
    summary: null,
    loading: false,
  },

  onLoad() {
    this.load();
  },

  onStartChange(e) {
    this.setData({ startDate: e.detail.value });
  },
  onEndChange(e) {
    this.setData({ endDate: e.detail.value });
  },

  load() {
    this.setData({ loading: true });
    adminApi.getReportStatistics({
      start_date: this.data.startDate,
      end_date: this.data.endDate,
    })
      .then((res) => {
        this.setData({
          summary: (res.data && res.data.summary) || null,
          loading: false,
        });
      })
      .catch(() => { this.setData({ loading: false }); });
  },
});
