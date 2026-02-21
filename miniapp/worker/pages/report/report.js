// 报工提交页已合并到 task 页，此页可作占位或跳转
Page({
  onLoad(options) {
    const id = options.allocation_id;
    if (id) {
      wx.redirectTo({ url: '/pages/task/task?allocation_id=' + id });
    } else {
      wx.switchTab({ url: '/pages/index/index' });
    }
  },
});
