const { BASE_URL, TENANT_ID } = require('./utils/config.js');

App({
  globalData: {
    userInfo: null,
    tenantId: 0,
    tenantName: '',
  },
  onLaunch() {
    const token = wx.getStorageSync('user_token');
    if (token) {
      // 可在此校验 token 是否仍有效
    }
    this.fetchTenantConfig();
  },
  fetchTenantConfig() {
    try {
      const account = wx.getAccountInfoSync();
      const appId = (account && account.miniProgram && account.miniProgram.appId) ? account.miniProgram.appId : '';
      if (!appId) {
        this.globalData.tenantId = TENANT_ID || 0;
        return;
      }
      wx.request({
        url: BASE_URL + '/miniapp/getConfig',
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
          if (!this.globalData.tenantId) {
            this.globalData.tenantId = TENANT_ID || 0;
          }
        },
        fail: () => {
          this.globalData.tenantId = TENANT_ID || 0;
        },
      });
    } catch (e) {
      this.globalData.tenantId = TENANT_ID || 0;
    }
    if (!this.globalData.tenantId) {
      this.globalData.tenantId = TENANT_ID || 0;
    }
  },
});
