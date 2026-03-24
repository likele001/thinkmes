const { BASE_URL, TENANT_ID } = require('./utils/config.js');

App({
  globalData: {
    userInfo: null,
    adminInfo: null,
    token: null,
    adminToken: null,
    userToken: null,
    baseUrl: BASE_URL,
    tenantId: 0,
    tenantName: '',
    isAdminMode: false,
    autoUserLoginDone: false,
    autoUserLoginOk: false,
    needBindEmployee: false,
    /** 管理端权限节点（与 PC 一致），用于首页菜单显隐 */
    scanworkNodes: null,
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
      const nodes = wx.getStorageSync('scanworkNodes');
      if (nodes && Array.isArray(nodes)) {
        this.globalData.scanworkNodes = nodes;
      }
    } else if (userToken) {
      this.globalData.userToken = userToken;
      this.globalData.userInfo = userInfo;
      this.globalData.isAdminMode = false;
      this.globalData.token = userToken;
    }
    this.fetchTenantConfig();
    // 无管理员态时，延迟尝试一次员工端自动登录（静默），成功与否由登录页根据结果跳转
    if (!adminToken && !adminInfo) {
      setTimeout(() => { this.tryAutoUserLogin(); }, 600);
    }
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

  /** 静默尝试员工端自动登录（不弹 toast），成功写 token，结果供登录页判断 */
  tryAutoUserLogin() {
    if (this.globalData.userToken || wx.getStorageSync('user_token')) {
      this.globalData.autoUserLoginDone = true;
      this.globalData.autoUserLoginOk = true;
      return;
    }
    const tenantId = this.globalData.tenantId || wx.getStorageSync('tenant_id') || TENANT_ID;
    if (!tenantId) {
      this.globalData.autoUserLoginDone = true;
      this.globalData.autoUserLoginOk = false;
      return;
    }
    wx.login({
      success: (res) => {
        if (!res.code) return;
        wx.request({
          url: this.globalData.baseUrl + '/miniapp/login',
          method: 'POST',
          data: { code: res.code, tenant_id: tenantId, nickname: '', avatar: '' },
          header: { 'content-type': 'application/json' },
          success: (reqRes) => {
            if (reqRes.statusCode !== 200 || !reqRes.data) return;
            const data = reqRes.data;
            if (data.code === 1 && data.data && data.data.token) {
              const token = data.data.token;
              const userInfo = data.data;
              this.globalData.userToken = token;
              this.globalData.userInfo = userInfo;
              this.globalData.token = token;
              this.globalData.isAdminMode = false;
              wx.setStorageSync('user_token', token);
              wx.setStorageSync('user_info', userInfo);
              this.globalData.autoUserLoginDone = true;
              this.globalData.autoUserLoginOk = true;
              return;
            }
            if (data.data && data.data.need_bind === true) {
              this.globalData.autoUserLoginDone = true;
              this.globalData.autoUserLoginOk = false;
              this.globalData.needBindEmployee = true;
              return;
            }
            this.globalData.autoUserLoginDone = true;
            this.globalData.autoUserLoginOk = false;
          },
          fail: () => {
            this.globalData.autoUserLoginDone = true;
            this.globalData.autoUserLoginOk = false;
          },
        });
      },
      fail: () => {
        this.globalData.autoUserLoginDone = true;
        this.globalData.autoUserLoginOk = false;
      },
    });
  },

  // 通用请求封装（支持超时、重试）
  _requestCore(options, tokenKey, logoutFn) {
    const token = this.globalData[tokenKey] || wx.getStorageSync(tokenKey === 'adminToken' ? 'adminToken' : 'user_token');
    const url = this.globalData.baseUrl + (options.url || '');
    const method = options.method || 'GET';
    const data = options.data || {};
    const timeout = options.timeout || 15000;
    const maxRetry = options.retry != null ? options.retry : 1;
    let attempt = 0;

    const doRequest = () => new Promise((resolve, reject) => {
      const start = Date.now();
      const requestTask = wx.request({
        url,
        method,
        data,
        timeout,
        header: {
          'content-type': options.contentType || 'application/json',
          'Authorization': token ? 'Bearer ' + token : '',
        },
        success(res) {
          const duration = Date.now() - start;
          if (res.statusCode === 200) {
            if (res.data && res.data.code === 1) {
              resolve({ ok: true, data: res.data, duration });
            } else {
              reject({ type: 'business', msg: (res.data && res.data.msg) || '请求失败', data: res.data, duration });
            }
          } else if (res.statusCode === 401) {
            logoutFn && logoutFn();
            reject({ type: 'auth', statusCode: 401, msg: '登录已过期，请重新登录', data: res.data });
            setTimeout(() => { wx.reLaunch({ url: '/pages/login/login' }); }, 500);
          } else if (res.statusCode === 403) {
            reject({ type: 'forbidden', statusCode: 403, msg: (res.data && res.data.msg) || '无权限访问', data: res.data });
          } else if (res.statusCode >= 500) {
            reject({ type: 'server', statusCode: res.statusCode, msg: '服务器繁忙，请稍后重试', data: res.data });
          } else {
            reject({ type: 'http', statusCode: res.statusCode, msg: '网络异常', data: res.data });
          }
        },
        fail(err) {
          const isTimeout = err && (err.errMsg || '').includes('timeout');
          reject({ type: isTimeout ? 'timeout' : 'network', msg: isTimeout ? '请求超时，请检查网络' : '网络错误，请检查网络连接', err });
        },
      });
      // 不支持原生超时时使用手动超时兜底
      if (timeout > 0) {
        setTimeout(() => { try { requestTask.abort && requestTask.abort(); } catch(e) {} }, timeout + 5000);
      }
    });

    const tryRequest = () => {
      attempt++;
      return doRequest().catch((err) => {
        const shouldRetry = attempt < maxRetry && (err.type === 'timeout' || err.type === 'network');
        if (shouldRetry) {
          return new Promise((r) => setTimeout(r, 1000 * attempt)).then(tryRequest);
        }
        throw err;
      });
    };

    return tryRequest().then((res) => res.data).catch((err) => {
      const silent = !!options.silent;
      if (!silent && err.type !== 'auth') {
        wx.showToast({ title: err.msg || '请求失败', icon: 'none' });
      }
      return Promise.reject(err);
    });
  },

  // 管理员端请求（Scanwork API）
  request(options) {
    return this._requestCore(options, 'adminToken', () => { this.adminLogout(); });
  },

  // 员工端请求（Worker / Miniapp API）
  userRequest(options) {
    return this._requestCore(options, 'userToken', () => { this.userLogout(); });
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
        // 拉取权限节点，与 PC 角色一致，供首页菜单显隐
        this.request({ url: '/scanwork/getScanworkMenu', method: 'GET' })
          .then((menuRes) => {
            const nodes = (menuRes.data && menuRes.data.nodes) || [];
            this.globalData.scanworkNodes = nodes;
            wx.setStorageSync('scanworkNodes', nodes);
          })
          .catch(() => {
            this.globalData.scanworkNodes = [];
            wx.setStorageSync('scanworkNodes', []);
          });
      }
      return res;
    });
  },

  adminLogout() {
    this.globalData.adminToken = null;
    this.globalData.adminInfo = null;
    this.globalData.scanworkNodes = null;
    this.globalData.isAdminMode = false;
    if (this.globalData.token === this.globalData.adminToken) {
      this.globalData.token = this.globalData.userToken;
    }
    wx.removeStorageSync('adminToken');
    wx.removeStorageSync('adminInfo');
    wx.removeStorageSync('scanworkNodes');
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
      if (!this.globalData.scanworkNodes && wx.getStorageSync('scanworkNodes')) {
        this.globalData.scanworkNodes = wx.getStorageSync('scanworkNodes');
      }
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
