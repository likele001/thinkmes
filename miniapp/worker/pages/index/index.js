const api = require('../../utils/api.js');

Page({
  data: {
    metrics: { today_report_quantity: 0, today_wage: 0, pending_reports: 0 },
    tasks: [],
    loading: true,
  },
  onShow() {
    const token = wx.getStorageSync('user_token');
    if (!token) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    this.load();
  },
  load() {
    this.setData({ loading: true });
    api.getDashboard()
      .then((res) => {
        const d = res.data || {};
        this.setData({
          metrics: d.metrics || { today_report_quantity: 0, today_wage: 0, pending_reports: 0 },
          tasks: d.tasks || [],
          loading: false,
        });
      })
      .catch(() => { this.setData({ loading: false }); });
  },
  onPullDownRefresh() {
    this.load();
    wx.stopPullDownRefresh();
  },
  goTask(e) {
    const id = e.currentTarget.dataset.id;
    const pending = e.currentTarget.dataset.pending;
    if (pending <= 0) {
      wx.showToast({ title: '该任务已报满', icon: 'none' });
      return;
    }
    wx.navigateTo({ url: '/pages/task/task?allocation_id=' + id });
  },
});
