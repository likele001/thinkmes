const api = require('../../utils/api.js');

Page({
  data: {
    loading: false,
  },
  onLoad(options) {
    const token = wx.getStorageSync('user_token');
    if (token) {
      wx.reLaunch({ url: '/pages/index/index' });
      return;
    }
  },
  doLogin() {
    this.setData({ loading: true });
    wx.login({
      success: (res) => {
        if (!res.code) {
          this.setData({ loading: false });
          wx.showToast({ title: '获取 code 失败', icon: 'none' });
          return;
        }
        api.login(res.code)
          .then((data) => {
            this.setData({ loading: false });
            if (data.data && data.data.token) {
              wx.setStorageSync('user_token', data.data.token);
              wx.setStorageSync('user_info', data.data);
              wx.reLaunch({ url: '/pages/index/index' });
              return;
            }
            if (data.data && data.data.need_bind === true) {
              wx.navigateTo({ url: '/pages/bind-employee/bind-employee' });
              return;
            }
            wx.showToast({ title: '请绑定员工账号', icon: 'none' });
          })
          .catch(() => { this.setData({ loading: false }); });
      },
      fail: () => { this.setData({ loading: false }); wx.showToast({ title: '登录失败', icon: 'none' }); },
    });
  },
});
