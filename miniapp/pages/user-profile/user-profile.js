const app = getApp();

Page({
  data: {
    userInfo: null,
  },

  onShow() {
    if (!app.checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    this.setData({
      userInfo: app.globalData.userInfo || wx.getStorageSync('user_info'),
    });
  },

  goAdmin() {
    if (app.checkAdminLogin()) {
      wx.navigateTo({ url: '/pages/index/index' });
    } else {
      wx.showToast({ title: '请使用管理员账号登录', icon: 'none' });
    }
  },

  logout() {
    wx.showModal({
      title: '确认退出',
      content: '确定要退出登录吗？',
      success: (res) => {
        if (res.confirm) {
          app.userLogout();
          wx.reLaunch({ url: '/pages/login/login' });
        }
      },
    });
  },
});
