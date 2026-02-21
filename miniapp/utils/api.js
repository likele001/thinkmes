const app = getApp();

// 员工端 API（userRequest）
const userApi = {
  getDashboard() {
    return app.userRequest({ url: '/worker/dashboard', method: 'GET' });
  },
  getTaskInfo(allocationId) {
    return app.userRequest({ url: '/worker/taskInfo', method: 'GET', data: { allocation_id: allocationId } });
  },
  submitReport(data) {
    return app.userRequest({ url: '/worker/report', method: 'POST', data });
  },
  getReports(page, limit, status) {
    const data = { page: page || 1, limit: limit || 20 };
    if (status !== undefined && status !== '') data.status = status;
    return app.userRequest({ url: '/worker/reports', method: 'GET', data });
  },
  getWages(page, limit, workDate) {
    const data = { page: page || 1, limit: limit || 20 };
    if (workDate) data.work_date = workDate;
    return app.userRequest({ url: '/worker/wages', method: 'GET', data });
  },
  uploadImage(filePath) {
    const token = wx.getStorageSync('user_token') || '';
    const { BASE_URL } = require('./config.js');
    return new Promise((resolve, reject) => {
      wx.uploadFile({
        url: BASE_URL + '/worker/uploadImage',
        filePath,
        name: 'image',
        header: { Authorization: token ? 'Bearer ' + token : '' },
        success(res) {
          try {
            const data = JSON.parse(res.data);
            if (data.code === 1 && data.data && data.data.url) resolve(data.data.url);
            else reject(data);
          } catch (e) { reject(e); }
        },
        fail: reject,
      });
    });
  },
};

// 管理端 API（request，Scanwork）
const adminApi = {
  checkToken() {
    return app.request({ url: '/scanwork/checkToken', method: 'GET' });
  },
  getDashboardData() {
    return app.request({ url: '/scanwork/getDashboardData', method: 'GET' });
  },
  getOrders(page, limit) {
    return app.request({ url: '/scanwork/getOrders', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getOrderDetail(id) {
    return app.request({ url: '/scanwork/getOrderDetail', method: 'GET', data: { id } });
  },
  getAllocations(page, limit, status) {
    const data = { page: page || 1, limit: limit || 20 };
    if (status !== undefined && status !== '') data.status = status;
    return app.request({ url: '/scanwork/getAllocations', method: 'GET', data });
  },
  getReports(page, limit, status) {
    const data = { page: page || 1, limit: limit || 20 };
    if (status !== undefined && status !== '') data.status = status;
    return app.request({ url: '/scanwork/getReports', method: 'GET', data });
  },
  getActiveReports(page, limit) {
    return app.request({ url: '/scanwork/getActiveReports', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getReportDetail(reportId) {
    return app.request({ url: '/scanwork/getReportDetail', method: 'GET', data: { report_id: reportId } });
  },
  auditReport(reportId, status, auditReason) {
    return app.request({
      url: '/scanwork/auditReport',
      method: 'POST',
      data: { report_id: reportId, status, audit_reason: auditReason || '' },
    });
  },
};

module.exports = {
  userApi,
  adminApi,
};
