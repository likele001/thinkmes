const app = getApp();

Page({
  data: {
    selectedMode: '',
    username: '',
    password: '',
    loading: false,
    waitingAuto: true,
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
    this._autoLoginCheck = setInterval(() => this.checkAutoLoginResult(), 350);
    setTimeout(() => {
      if (this._autoLoginCheck) {
        clearInterval(this._autoLoginCheck);
        this._autoLoginCheck = null;
      }
      if (this.data.waitingAuto) {
        this.setData({ waitingAuto: false });
        this.applyAutoLoginResult();
      }
    }, 2500);
  },

  onUnload() {
    if (this._autoLoginCheck) {
      clearInterval(this._autoLoginCheck);
      this._autoLoginCheck = null;
    }
  },

  checkAutoLoginResult() {
    if (!app.globalData.autoUserLoginDone) return;
    if (this._autoLoginCheck) {
      clearInterval(this._autoLoginCheck);
      this._autoLoginCheck = null;
    }
    this.setData({ waitingAuto: false });
    this.applyAutoLoginResult();
  },

  applyAutoLoginResult() {
    if (app.checkUserLogin()) {
      wx.reLaunch({ url: '/pages/user-index/user-index' });
      return;
    }
    if (app.globalData.needBindEmployee) {
      wx.navigateTo({ url: '/pages/bind-employee/bind-employee' });
      return;
    }
    if (!this.data.selectedMode) {
      this.setData({ selectedMode: '' });
    }
  },

  onShow() {
    const storedTenantId = wx.getStorageSync('tenant_id');
    if (storedTenantId && app.globalData.tenantId !== storedTenantId) {
      app.globalData.tenantId = storedTenantId;
      app.globalData.tenantName = wx.getStorageSync('tenant_name') || '';
    }
    if (!this.data.waitingAuto && !this.data.selectedMode && app.globalData.needBindEmployee) {
      wx.navigateTo({ url: '/pages/bind-employee/bind-employee' });
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
        const tenantId = app.globalData.tenantId || wx.getStorageSync('tenant_id');
        wx.request({
          url: app.globalData.baseUrl + '/miniapp/login',
          method: 'POST',
          data: { code: res.code, tenant_id: tenantId, nickname: '', avatar: '' },
          header: { 'content-type': 'application/json' },
          success: (reqRes) => {
            if (reqRes.statusCode === 200 && reqRes.data && reqRes.data.code === 1 && reqRes.data.data) {
              const data = reqRes.data.data;
              if (data.token) {
                app.globalData.userToken = data.token;
                app.globalData.userInfo = data;
                app.globalData.token = data.token;
                app.globalData.isAdminMode = false;
                wx.setStorageSync('user_token', data.token);
                wx.setStorageSync('user_info', data);
                wx.reLaunch({ url: '/pages/user-index/user-index' });
                return;
              }
              if (data.need_bind === true) {
                app.globalData.needBindEmployee = true;
                wx.navigateTo({ url: '/pages/bind-employee/bind-employee' });
                return;
              }
            }
            wx.showToast({ title: (reqRes.data && reqRes.data.msg) || '登录失败', icon: 'none' });
          },
          fail: () => wx.showToast({ title: '网络错误', icon: 'none' }),
        });
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
