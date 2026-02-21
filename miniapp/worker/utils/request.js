const { BASE_URL } = require('./config.js');

function request(options) {
  const token = wx.getStorageSync('user_token') || '';
  let url = BASE_URL + (options.url || '');
  const data = options.data || {};
  if (options.method === 'GET' && Object.keys(data).length) {
    const qs = Object.keys(data).map(k => k + '=' + encodeURIComponent(data[k])).join('&');
    url += (url.indexOf('?') >= 0 ? '&' : '?') + qs;
  }
  return new Promise((resolve, reject) => {
    wx.request({
      url,
      method: options.method || 'GET',
      data: options.method === 'GET' ? {} : data,
      header: {
        'content-type': options.contentType || 'application/json',
        'Authorization': token ? 'Bearer ' + token : '',
        ...options.header,
      },
      success(res) {
        if (res.statusCode === 200) {
          const data = res.data;
          if (data && data.code === 1) {
            resolve(data);
          } else {
            wx.showToast({ title: data.msg || '请求失败', icon: 'none' });
            reject(data);
          }
        } else {
          wx.showToast({ title: '网络错误', icon: 'none' });
          reject(res);
        }
      },
      fail(err) {
        wx.showToast({ title: '网络错误', icon: 'none' });
        reject(err);
      },
    });
  });
}

module.exports = { request };
