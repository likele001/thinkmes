const app = getApp();

Page({
  data: {
    userInfo: {},
    avatarText: '工',
  },

  onLoad() {
    if (!app.checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    this.loadUser();
  },

  onShow() {
    this.loadUser();
  },

  loadUser() {
    const userInfo = app.globalData.userInfo || wx.getStorageSync('user_info') || {};
    const name = userInfo.nickname || userInfo.username || '员工';
    const avatarText = (name && String(name).charAt(0)) || '工';
    this.setData({ userInfo, avatarText });
  },

  goReports() {
    wx.switchTab({ url: '/pages/user-reports/user-reports' });
  },

  goWages() {
    wx.switchTab({ url: '/pages/user-wages/user-wages' });
  },

  goScan() {
    wx.navigateTo({ url: '/pages/user-scan/user-scan' });
  },

  goIndex() {
    wx.switchTab({ url: '/pages/user-index/user-index' });
  },

  logout() {
    wx.showModal({
      title: '确认退出',
      content: '确定要退出登录吗？',
      success: (res) => {
        if (res.confirm) {
          app.userLogout && app.userLogout();
          wx.reLaunch({ url: '/pages/login/login' });
        }
      },
    });
  },
});
