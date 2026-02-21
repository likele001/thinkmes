const { BASE_URL, TENANT_ID } = require('./utils/config.js');

App({
  globalData: {
    userInfo: null,
    adminInfo: null,
    token: null,
    adminToken: null,
    userToken: null,
    baseUrl: BASE_URL,
    tenantId: 0,           // 从后台「租户小程序配置」根据 AppID 拉取，未拉取到则用 config 的 TENANT_ID
    tenantName: '',
    isAdminMode: false,
  },

  onLaunch() {
    const adminToken = wx.getStorageSync('adminToken');
    const adminInfo = wx.getStorageSync('adminInfo');
    const userToken = wx.getStorageSync('user_token');
    const userInfo = wx.getStorageSync('user_info');
    if (adminToken && adminInfo) {
      this.globalData.adminToken = adminToken;
      this.globalData.adminInfo = adminInfo;
      this.globalData.isAdminMode = true;
      this.globalData.token = adminToken;
    } else if (userToken) {
      this.globalData.userToken = userToken;
      this.globalData.userInfo = userInfo;
      this.globalData.isAdminMode = false;
      this.globalData.token = userToken;
    }
    // 根据当前小程序 AppID 从后台获取租户配置（租户小程序配置表）
    this.fetchTenantConfig();
  },

  fetchTenantConfig() {
    try {
      const account = wx.getAccountInfoSync();
      const appId = (account && account.miniProgram && account.miniProgram.appId) ? account.miniProgram.appId : '';
      if (!appId) return;
      wx.request({
        url: this.globalData.baseUrl + '/miniapp/getConfig',
        method: 'GET',
        data: { appid: appId },
        success: (res) => {
          if (res.statusCode === 200 && res.data && res.data.code === 1 && res.data.data) {
            const d = res.data.data;
            this.globalData.tenantId = d.tenant_id || 0;
            this.globalData.tenantName = d.name || '';
            if (this.globalData.tenantId) {
              wx.setStorageSync('tenant_id', this.globalData.tenantId);
              wx.setStorageSync('tenant_name', this.globalData.tenantName);
            }
          }
        },
      });
    } catch (e) {}
    if (!this.globalData.tenantId) {
      this.globalData.tenantId = TENANT_ID || 0;
    }
  },

  // 管理员端请求（Scanwork API）
  request(options) {
    const token = this.globalData.adminToken || wx.getStorageSync('adminToken');
    const url = this.globalData.baseUrl + (options.url || '');
    const method = options.method || 'GET';
    const data = options.data || {};
    return new Promise((resolve, reject) => {
      wx.request({
        url,
        method,
        data,
        header: {
          'content-type': options.contentType || 'application/json',
          'Authorization': token ? 'Bearer ' + token : '',
        },
        success(res) {
          if (res.statusCode === 200) {
            if (res.data && res.data.code === 1) {
              resolve(res.data);
            } else {
              wx.showToast({ title: (res.data && res.data.msg) || '请求失败', icon: 'none' });
              reject(res.data);
            }
          } else if (res.statusCode === 401) {
            getApp().adminLogout();
            wx.showToast({ title: '登录已过期', icon: 'none' });
            reject(res.data);
          } else {
            wx.showToast({ title: '网络错误', icon: 'none' });
            reject(res.data);
          }
        },
        fail: reject,
      });
    });
  },

  // 员工端请求（Worker / Miniapp API）
  userRequest(options) {
    const token = this.globalData.userToken || wx.getStorageSync('user_token');
    const url = this.globalData.baseUrl + (options.url || '');
    const method = options.method || 'GET';
    const data = options.data || {};
    return new Promise((resolve, reject) => {
      wx.request({
        url,
        method,
        data,
        header: {
          'content-type': options.contentType || 'application/json',
          'Authorization': token ? 'Bearer ' + token : '',
        },
        success(res) {
          if (res.statusCode === 200) {
            if (res.data && res.data.code === 1) {
              resolve(res.data);
            } else {
              wx.showToast({ title: (res.data && res.data.msg) || '请求失败', icon: 'none' });
              reject(res.data);
            }
          } else if (res.statusCode === 401) {
            getApp().userLogout();
            wx.showToast({ title: '登录已过期', icon: 'none' });
            reject(res.data);
          } else {
            wx.showToast({ title: '网络错误', icon: 'none' });
            reject(res.data);
          }
        },
        fail: reject,
      });
    });
  },

  // 管理员登录
  adminLogin(username, password) {
    return this.request({
      url: '/scanwork/adminLogin',
      method: 'POST',
      data: { username, password },
    }).then((res) => {
      if (res.data && res.data.token) {
        this.globalData.adminToken = res.data.token;
        this.globalData.adminInfo = res.data.admin_info || res.data;
        this.globalData.token = res.data.token;
        this.globalData.isAdminMode = true;
        wx.setStorageSync('adminToken', res.data.token);
        wx.setStorageSync('adminInfo', this.globalData.adminInfo);
      }
      return res;
    });
  },

  adminLogout() {
    this.globalData.adminToken = null;
    this.globalData.adminInfo = null;
    this.globalData.isAdminMode = false;
    if (this.globalData.token === this.globalData.adminToken) {
      this.globalData.token = this.globalData.userToken;
    }
    wx.removeStorageSync('adminToken');
    wx.removeStorageSync('adminInfo');
  },

  // 员工端登录（微信 code + 租户；租户来自 getConfig 或 config 兜底）
  userLogin(code, nickname, avatar) {
    const tenantId = this.globalData.tenantId || wx.getStorageSync('tenant_id') || TENANT_ID;
    return new Promise((resolve, reject) => {
      wx.request({
        url: this.globalData.baseUrl + '/miniapp/login',
        method: 'POST',
        data: { code, tenant_id: tenantId, nickname: nickname || '', avatar: avatar || '' },
        header: { 'content-type': 'application/json' },
        success: (res) => {
          if (res.statusCode === 200 && res.data && res.data.code === 1 && res.data.data && res.data.data.token) {
            const token = res.data.data.token;
            const userInfo = res.data.data;
            this.globalData.userToken = token;
            this.globalData.userInfo = userInfo;
            this.globalData.token = token;
            this.globalData.isAdminMode = false;
            wx.setStorageSync('user_token', token);
            wx.setStorageSync('user_info', userInfo);
            resolve(res.data);
          } else {
            wx.showToast({ title: (res.data && res.data.msg) || '登录失败', icon: 'none' });
            reject(res.data);
          }
        },
        fail: reject,
      });
    });
  },

  userLogout() {
    this.globalData.userToken = null;
    this.globalData.userInfo = null;
    this.globalData.isAdminMode = false;
    if (this.globalData.token === this.globalData.userToken) {
      this.globalData.token = this.globalData.adminToken;
    }
    wx.removeStorageSync('user_token');
    wx.removeStorageSync('user_info');
  },

  checkAdminLogin() {
    const token = wx.getStorageSync('adminToken');
    const adminInfo = wx.getStorageSync('adminInfo');
    if (token && adminInfo) {
      this.globalData.adminToken = token;
      this.globalData.adminInfo = adminInfo;
      this.globalData.token = token;
      this.globalData.isAdminMode = true;
      return true;
    }
    return false;
  },

  checkUserLogin() {
    const token = wx.getStorageSync('user_token');
    if (token) {
      this.globalData.userToken = token;
      this.globalData.userInfo = wx.getStorageSync('user_info');
      this.globalData.token = token;
      this.globalData.isAdminMode = false;
      return true;
    }
    return false;
  },
});
