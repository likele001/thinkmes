App({
  globalData: {
    userInfo: null,
    tenantId: 0,
  },
  onLaunch() {
    const token = wx.getStorageSync('user_token');
    if (token) {
      // 可在此校验 token 是否仍有效
    }
  },
});
