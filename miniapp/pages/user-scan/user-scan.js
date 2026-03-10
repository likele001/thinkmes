Page({
  onLoad() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
  },

  startScan() {
    wx.scanCode({
      onlyFromCamera: true,
      scanType: ['qrCode'],
      success: (res) => {
        const result = (res.result || '').trim();
        let allocationId = '';
        if (/^\d+$/.test(result)) {
          allocationId = result;
        } else if (result.indexOf('allocation_id=') !== -1) {
          const m = result.match(/allocation_id=(\d+)/);
          if (m) allocationId = m[1];
        } else if (result.indexOf('allocation_id') !== -1) {
          try {
            const j = JSON.parse(result);
            allocationId = String(j.allocation_id || j.id || '');
          } catch (e) {
            const m = result.match(/"allocation_id"\s*:\s*(\d+)/);
            if (m) allocationId = m[1];
          }
        }
        if (allocationId) {
          wx.redirectTo({ url: '/pages/user-task/user-task?allocation_id=' + allocationId });
        } else {
          wx.showToast({ title: '未识别到分工码', icon: 'none' });
        }
      },
      fail: (err) => {
        if (err.errMsg && err.errMsg.indexOf('cancel') !== -1) return;
        wx.showToast({ title: '扫码失败', icon: 'none' });
      },
    });
  },

  goBack() {
    wx.navigateBack();
  },
});
