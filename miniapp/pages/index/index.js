const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    adminInfo: null,
    dashboard: null,
    loading: true,
  },

  onShow() {
    if (!getApp().checkAdminLogin()) {
      wx.redirectTo({ url: '/pages/login/login' });
      return;
    }
    this.setData({
      adminInfo: getApp().globalData.adminInfo || wx.getStorageSync('adminInfo'),
    });
    this.loadDashboard();
  },

  loadDashboard() {
    this.setData({ loading: true });
    adminApi.getDashboardData()
      .then((res) => {
        this.setData({ dashboard: res.data || null, loading: false });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  goOrders() {
    wx.navigateTo({ url: '/pages/orders/orders' });
  },

  goAllocations() {
    wx.navigateTo({ url: '/pages/allocations/allocations' });
  },

  goReports() {
    wx.navigateTo({ url: '/pages/reports/reports' });
  },

  goActiveReports() {
    wx.navigateTo({ url: '/pages/active-reports/active-reports' });
  },

  logout() {
    wx.showModal({
      title: '确认退出',
      content: '确定要退出管理端吗？',
      success: (res) => {
        if (res.confirm) {
          getApp().adminLogout();
          wx.reLaunch({ url: '/pages/login/login' });
        }
      },
    });
  },

  switchToUser() {
    if (getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/user-index/user-index' });
    } else {
      wx.reLaunch({ url: '/pages/login/login' });
    }
  },
});
