function request(opts) {
  const app = getApp();
  const baseUrl = (app && app.globalData && app.globalData.baseUrl) ? app.globalData.baseUrl : '';
  const tenantId = (app && app.globalData && app.globalData.tenantId) ? app.globalData.tenantId : 0;
  const url = (baseUrl || '') + (opts.url || '');
  const data = Object.assign({}, opts.data || {});
  if (tenantId && data.tenant_id === undefined) data.tenant_id = tenantId;
  return new Promise((resolve, reject) => {
    wx.request({
      url,
      method: opts.method || 'GET',
      data,
      header: Object.assign({ 'content-type': 'application/x-www-form-urlencoded' }, opts.header || {}),
      success(res) { resolve(res.data); },
      fail(err) { reject(err); },
    });
  });
}

module.exports = { request };

