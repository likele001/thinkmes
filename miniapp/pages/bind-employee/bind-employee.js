const app = getApp();

Page({
  data: {
    username: '',
    password: '',
    loading: false,
  },

  inputUsername(e) {
    this.setData({ username: e.detail.value });
  },

  inputPassword(e) {
    this.setData({ password: e.detail.value });
  },

  backToLogin() {
    wx.navigateBack();
  },

  submit() {
    const { username, password } = this.data;
    if (!username || !password) {
      wx.showToast({ title: '请输入用户名和密码', icon: 'none' });
      return;
    }
    const tenantId = app.globalData.tenantId || wx.getStorageSync('tenant_id');
    if (!tenantId) {
      wx.showToast({ title: '未获取到租户信息，请返回重试', icon: 'none' });
      return;
    }
    this.setData({ loading: true });
    wx.login({
      success: (res) => {
        if (!res.code) {
          this.setData({ loading: false });
          wx.showToast({ title: '获取登录态失败', icon: 'none' });
          return;
        }
        wx.request({
          url: app.globalData.baseUrl + '/miniapp/bindWithEmployee',
          method: 'POST',
          data: {
            code: res.code,
            tenant_id: tenantId,
            username: username.trim(),
            password: password,
          },
          header: { 'content-type': 'application/json' },
          success: (reqRes) => {
            this.setData({ loading: false });
            if (reqRes.statusCode === 200 && reqRes.data && reqRes.data.code === 1 && reqRes.data.data && reqRes.data.data.token) {
              const data = reqRes.data.data;
              app.globalData.userToken = data.token;
              app.globalData.userInfo = data;
              app.globalData.token = data.token;
              app.globalData.isAdminMode = false;
              app.globalData.needBindEmployee = false;
              wx.setStorageSync('user_token', data.token);
              wx.setStorageSync('user_info', data);
              wx.showToast({ title: '绑定成功', icon: 'success' });
              setTimeout(() => wx.reLaunch({ url: '/pages/user-index/user-index' }), 500);
            } else {
              wx.showToast({ title: (reqRes.data && reqRes.data.msg) || '绑定失败', icon: 'none' });
            }
          },
          fail: () => {
            this.setData({ loading: false });
            wx.showToast({ title: '网络错误', icon: 'none' });
          },
        });
      },
      fail: () => {
        this.setData({ loading: false });
        wx.showToast({ title: '登录失败', icon: 'none' });
      },
    });
  },
});
