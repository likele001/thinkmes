const app = getApp();

Page({
  data: {
    selectedMode: '',
    username: '',
    password: '',
    loading: false,
  },

  onLoad() {
    if (app.checkAdminLogin()) {
      wx.redirectTo({ url: '/pages/index/index' });
      return;
    }
    if (app.checkUserLogin()) {
      wx.reLaunch({ url: '/pages/user-index/user-index' });
      return;
    }
  },

  selectAdminMode() {
    this.setData({ selectedMode: 'admin' });
  },

  selectUserMode() {
    this.setData({ selectedMode: 'user' });
    this.enterUserMode();
  },

  backToModeSelect() {
    this.setData({ selectedMode: '' });
  },

  enterUserMode() {
    if (app.checkUserLogin()) {
      wx.reLaunch({ url: '/pages/user-index/user-index' });
      return;
    }
    const tenantId = app.globalData.tenantId || wx.getStorageSync('tenant_id');
    if (!tenantId) {
      this.fetchTenantThenLogin();
      return;
    }
    this.doUserLogin();
  },

  fetchTenantThenLogin() {
    try {
      const account = wx.getAccountInfoSync();
      const appId = (account && account.miniProgram && account.miniProgram.appId) ? account.miniProgram.appId : '';
      if (!appId) {
        wx.showToast({ title: '未获取到小程序信息', icon: 'none' });
        return;
      }
      wx.showLoading({ title: '获取租户配置...' });
      wx.request({
        url: app.globalData.baseUrl + '/miniapp/getConfig',
        method: 'GET',
        data: { appid: appId },
        success: (res) => {
          wx.hideLoading();
          if (res.statusCode === 200 && res.data && res.data.code === 1 && res.data.data && res.data.data.tenant_id) {
            app.globalData.tenantId = res.data.data.tenant_id;
            app.globalData.tenantName = res.data.data.name || '';
            wx.setStorageSync('tenant_id', app.globalData.tenantId);
            this.doUserLogin();
          } else {
            wx.showToast({ title: (res.data && res.data.msg) || '未配置租户小程序，请在后台配置', icon: 'none', duration: 2500 });
          }
        },
        fail: () => {
          wx.hideLoading();
          wx.showToast({ title: '网络错误', icon: 'none' });
        },
      });
    } catch (e) {
      wx.showToast({ title: '未获取到小程序信息', icon: 'none' });
    }
  },

  doUserLogin() {
    wx.login({
      success: (res) => {
        if (!res.code) {
          wx.showToast({ title: '获取登录态失败', icon: 'none' });
          return;
        }
        app.userLogin(res.code)
          .then(() => {
            wx.reLaunch({ url: '/pages/user-index/user-index' });
          })
          .catch(() => {});
      },
      fail: () => wx.showToast({ title: '登录失败', icon: 'none' }),
    });
  },

  inputUsername(e) {
    this.setData({ username: e.detail.value });
  },

  inputPassword(e) {
    this.setData({ password: e.detail.value });
  },

  adminLogin() {
    const { username, password } = this.data;
    if (!username || !password) {
      wx.showToast({ title: '请输入用户名和密码', icon: 'none' });
      return;
    }
    this.setData({ loading: true });
    app.adminLogin(username.trim(), password)
      .then((res) => {
        this.setData({ loading: false });
        wx.showToast({ title: '登录成功', icon: 'success' });
        setTimeout(() => wx.redirectTo({ url: '/pages/index/index' }), 500);
      })
      .catch(() => { this.setData({ loading: false }); });
  },
});
