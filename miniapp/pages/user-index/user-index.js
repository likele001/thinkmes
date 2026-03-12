const { userApi } = require('../../utils/api.js');

Page({
  data: {
    welcomeName: '员工',
    todayDate: '',
    avatarText: '工',
    metrics: { today_task_count: 0, today_report_quantity: 0, today_wage: 0 },
    tasks: [],
    loading: true,
  },

  onLoad() {
    const app = getApp();
    const userInfo = app.globalData.userInfo || wx.getStorageSync('user_info') || {};
    const name = userInfo.nickname || userInfo.username || '员工';
    this.setData({
      welcomeName: name,
      todayDate: new Date().toLocaleDateString('zh-CN', { year: 'numeric', month: '2-digit', day: '2-digit' }),
      avatarText: (name && String(name).charAt(0)) || '工',
    });
  },

  onShow() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    const userInfo = getApp().globalData.userInfo || wx.getStorageSync('user_info') || {};
    const name = userInfo.nickname || userInfo.username || '员工';
    this.setData({
      welcomeName: name,
      avatarText: (name && String(name).charAt(0)) || '工',
    });
    this.load();
  },

  load() {
    this.setData({ loading: true });
    userApi.getDashboard()
      .then((res) => {
        const d = res.data || {};
        this.setData({
          metrics: d.metrics || { today_task_count: 0, today_report_quantity: 0, today_wage: 0 },
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
    wx.navigateTo({ url: '/pages/user-task/user-task?allocation_id=' + id });
  },

  goAdmin() {
    if (getApp().checkAdminLogin()) {
      wx.navigateTo({ url: '/pages/index/index' });
    } else {
      wx.showToast({ title: '请先登录管理端', icon: 'none' });
    }
  },

  goTasks() {
    wx.pageScrollTo({ selector: '.menu-section', scrollWithAnimation: true });
  },

  goScan() {
    wx.navigateTo({ url: '/pages/user-scan/user-scan' });
  },

  goReports() {
    wx.switchTab({ url: '/pages/user-reports/user-reports' });
  },

  goWages() {
    wx.switchTab({ url: '/pages/user-wages/user-wages' });
  },

  goProfile() {
    wx.switchTab({ url: '/pages/user-profile/user-profile' });
  },
});
