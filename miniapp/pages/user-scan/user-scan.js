const { userApi } = require('../../utils/api.js');

function parseAllocationId(result) {
  const s = (result && result.result) ? String(result.result).trim() : '';
  if (!s) return null;
  const num = parseInt(s, 10);
  if (!isNaN(num) && num > 0) return num;
  const m = s.match(/allocation_id=(\d+)/i) || s.match(/id=(\d+)/);
  return m ? parseInt(m[1], 10) : null;
}

Page({
  data: {
    scanning: false,
    scanResult: null,
  },

  onLoad() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
  },

  scanCode() {
    this.setData({ scanning: true });
    wx.scanCode({
      onlyFromCamera: true,
      scanType: ['qrCode'],
      success: (res) => {
        const allocationId = parseAllocationId(res);
        if (!allocationId) {
          this.setData({ scanning: false });
          wx.showToast({ title: '无效的二维码', icon: 'none' });
          return;
        }
        userApi.getTaskInfo(allocationId)
          .then((apiRes) => {
            const d = apiRes.data || {};
            if (!d.allocation_id) {
              wx.showToast({ title: d.msg || '该任务不存在或无权操作', icon: 'none' });
              this.setData({ scanning: false });
              return;
            }
            this.setData({
              scanResult: {
                allocation_id: d.allocation_id,
                product_name: d.product_name || '产品',
                model_name: d.model_name || '',
                process_name: d.process_name || '',
                order_no: d.order_no || '',
                assign_qty: d.assign_qty ?? 0,
                pending_qty: d.pending_qty ?? 0,
              },
              scanning: false,
            });
          })
          .catch(() => {
            this.setData({ scanning: false });
            wx.showToast({ title: '获取任务信息失败', icon: 'none' });
          });
      },
      fail: () => {
        this.setData({ scanning: false });
        wx.showToast({ title: '扫码取消或失败', icon: 'none' });
      },
    });
  },

  goToReport() {
    const id = this.data.scanResult && this.data.scanResult.allocation_id;
    if (!id) return;
    if ((this.data.scanResult.pending_qty || 0) <= 0) {
      wx.showToast({ title: '该任务已报满', icon: 'none' });
      return;
    }
    wx.navigateTo({ url: '/pages/user-task/user-task?allocation_id=' + id });
  },

  rescan() {
    this.setData({ scanResult: null });
  },
});
