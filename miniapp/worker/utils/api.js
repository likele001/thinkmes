const { request } = require('./request.js');
const { BASE_URL, TENANT_ID } = require('./config.js');

function getTenantId() {
  const app = getApp();
  if (app && app.globalData && app.globalData.tenantId) {
    return app.globalData.tenantId;
  }
  const stored = wx.getStorageSync('tenant_id');
  if (stored) return stored;
  return TENANT_ID || 0;
}

// 员工端 API 封装
const api = {
  // 登录（微信 code + 租户ID，租户来自 getConfig 或 config 兜底）
  login(code, nickname, avatar) {
    const tenantId = getTenantId();
    return new Promise((resolve, reject) => {
      wx.request({
        url: BASE_URL + '/miniapp/login',
        method: 'POST',
        data: { code, tenant_id: tenantId, nickname: nickname || '', avatar: avatar || '' },
        header: { 'content-type': 'application/json' },
        success(res) {
          if (res.statusCode === 200 && res.data && res.data.code === 1) {
            resolve(res.data);
          } else {
            if (res.data && res.data.msg) wx.showToast({ title: res.data.msg, icon: 'none' });
            reject(res.data);
          }
        },
        fail: reject,
      });
    });
  },
  // 工作台
  getDashboard() {
    return request({ url: '/worker/dashboard', method: 'GET' });
  },
  // 任务详情（扫码或点任务进）
  getTaskInfo(allocationId) {
    return request({ url: '/worker/taskInfo', method: 'GET', data: { allocation_id: allocationId } });
  },
  // 提交报工
  submitReport(data) {
    return request({ url: '/worker/report', method: 'POST', data });
  },
  // 我的报工记录
  getReports(page = 1, limit = 20, status = '') {
    const data = { page, limit };
    if (status !== '') data.status = status;
    return request({ url: '/worker/reports', method: 'GET', data });
  },
  // 我的工资
  getWages(page = 1, limit = 20, workDate = '') {
    const data = { page, limit };
    if (workDate) data.work_date = workDate;
    return request({ url: '/worker/wages', method: 'GET', data });
  },
  // 上传图片（用于报工）
  uploadImage(filePath) {
    const token = wx.getStorageSync('user_token') || '';
    return new Promise((resolve, reject) => {
      wx.uploadFile({
        url: require('./config.js').BASE_URL + '/worker/uploadImage',
        filePath,
        name: 'image',
        header: { 'Authorization': token ? 'Bearer ' + token : '' },
        success(res) {
          try {
            const data = JSON.parse(res.data);
            if (data.code === 1 && data.data && data.data.url) {
              resolve(data.data.url);
            } else {
              wx.showToast({ title: data.msg || '上传失败', icon: 'none' });
              reject(data);
            }
          } catch (e) {
            reject(e);
          }
        },
        fail: reject,
      });
    });
  },
};

module.exports = api;
