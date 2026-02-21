const api = require('../../utils/api.js');

Page({
  data: {
    allocationId: 0,
    info: null,
    workType: 'piece',
    selectedItems: [],
    workHours: '',
    images: [],
    submitting: false,
  },
  onLoad(options) {
    const id = options.allocation_id || 0;
    if (!id) {
      wx.showToast({ title: '参数错误', icon: 'none' });
      return;
    }
    this.setData({ allocationId: id });
    this.load();
  },
  load() {
    api.getTaskInfo(this.data.allocationId)
      .then((res) => {
        this.setData({ info: res.data });
      })
      .catch(() => {});
  },
  switchWorkType(e) {
    this.setData({ workType: e.detail.value });
  },
  toggleItem(e) {
    const no = e.currentTarget.dataset.no;
    let selected = this.data.selectedItems.slice();
    const idx = selected.indexOf(no);
    if (idx >= 0) selected.splice(idx, 1);
    else selected.push(no);
    this.setData({ selectedItems: selected });
  },
  inputHours(e) {
    this.setData({ workHours: e.detail.value });
  },
  chooseImage() {
    const that = this;
    wx.chooseMedia({
      count: 3 - this.data.images.length,
      mediaType: ['image'],
      success(res) {
        const tempFiles = (res.tempFiles || []).map(f => f.tempFilePath);
        that.setData({ images: that.data.images.concat(tempFiles) });
      },
    });
  },
  removeImage(e) {
    const idx = e.currentTarget.dataset.idx;
    const images = this.data.images.slice();
    images.splice(idx, 1);
    this.setData({ images });
  },
  submit() {
    const { allocationId, workType, selectedItems, workHours, images, info } = this.data;
    if (!info || info.pending_qty <= 0) {
      wx.showToast({ title: '无可报数量', icon: 'none' });
      return;
    }
    if (workType === 'piece') {
      if (!selectedItems.length) {
        wx.showToast({ title: '请选择要报工的产品编号', icon: 'none' });
        return;
      }
      if (selectedItems.length > info.pending_qty) {
        wx.showToast({ title: '选择数量不能超过待报数量', icon: 'none' });
        return;
      }
    } else {
      const h = parseFloat(workHours);
      if (isNaN(h) || h <= 0) {
        wx.showToast({ title: '请输入有效工时', icon: 'none' });
        return;
      }
    }
    this.setData({ submitting: true });
    const uploads = (images.length ? Promise.all(images.map(path => api.uploadImage(path))) : Promise.resolve([]))
      .then((urls) => {
        const data = { allocation_id: allocationId, work_type: workType };
        if (workType === 'piece') {
          data.item_nos = selectedItems;
          if (urls && urls.length) data.images = urls;
        } else {
          data.work_hours = parseFloat(workHours);
        }
        return api.submitReport(data);
      });
    uploads
      .then(() => {
        this.setData({ submitting: false });
        wx.showToast({ title: '报工成功' });
        setTimeout(() => { wx.navigateBack(); }, 1500);
      })
      .catch(() => { this.setData({ submitting: false }); });
  },
});
