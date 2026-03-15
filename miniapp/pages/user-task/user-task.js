const { userApi } = require('../../utils/api.js');
const { toFullImageUrl } = require('../../utils/image.js');

Page({
  data: {
    allocationId: 0,
    task: {},
    loading: true,
    quantity: '',
    images: [],
    itemNoList: [],
    selectedCount: 0,
    submitting: false,
  },

  onLoad(options) {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    const allocationId = parseInt(options.allocation_id, 10) || 0;
    this.setData({ allocationId });
    if (allocationId) this.load();
    else this.setData({ loading: false });
  },

  load() {
    const { allocationId } = this.data;
    if (!allocationId) return this.setData({ loading: false });
    this.setData({ loading: true });
    userApi.getTaskInfo(allocationId)
      .then((res) => {
        const d = res.data || {};
        const itemNos = Array.isArray(d.item_nos) ? d.item_nos : [];
        const itemNoList = itemNos.map((no) => ({ no: String(no), selected: false, images: [] }));
        this.setData({
          task: {
            allocation_id: d.allocation_id,
            product_name: d.product_name || '产品',
            model_name: d.model_name || '',
            process_name: d.process_name || '',
            order_no: d.order_no || '',
            order_name: d.order_name || '',
            assign_qty: d.assign_qty ?? 0,
            reported_qty: d.reported_qty ?? 0,
            pending_qty: d.pending_qty ?? 0,
            item_nos: itemNos,
          },
          itemNoList,
          selectedCount: 0,
          loading: false,
        });
      })
      .catch(() => this.setData({ loading: false }));
  },

  toggleItemNo(e) {
    const no = e.currentTarget.dataset.no;
    const list = this.data.itemNoList.map((row) =>
      row.no === no ? { ...row, selected: !row.selected } : row
    );
    const selectedCount = list.filter((r) => r.selected).length;
    this.setData({ itemNoList: list, selectedCount });
  },

  chooseImageForNo(e) {
    const no = e.currentTarget.dataset.no;
    const row = this.data.itemNoList.find((r) => r.no === no);
    if (!row) return;
    const max = 9 - (row.images || []).length;
    if (max <= 0) return;
    wx.chooseMedia({
      count: max,
      mediaType: ['image'],
      sourceType: ['album', 'camera'],
      success: (res) => {
        const files = res.tempFiles || [];
        if (files.length === 0) return;
        wx.showLoading({ title: '上传中...' });
        let done = 0;
        const total = files.length;
        const added = [];
        files.forEach((f) => {
          userApi.uploadImage(f.tempFilePath)
            .then((url) => {
              added.push(toFullImageUrl(url) || url);
              done++;
              if (done >= total) {
                wx.hideLoading();
                const list = this.data.itemNoList.map((r) =>
                  r.no === no ? { ...r, images: (r.images || []).concat(added) } : r
                );
                this.setData({ itemNoList: list });
              }
            })
            .catch(() => {
              done++;
              if (done >= total) wx.hideLoading();
              wx.showToast({ title: '部分图片上传失败', icon: 'none' });
            });
        });
      },
    });
  },

  delImageForNo(e) {
    const no = e.currentTarget.dataset.no;
    const index = e.currentTarget.dataset.index;
    const list = this.data.itemNoList.map((r) => {
      if (r.no !== no) return r;
      const img = (r.images || []).slice();
      img.splice(index, 1);
      return { ...r, images: img };
    });
    this.setData({ itemNoList: list });
  },

  submitBySelect() {
    const { allocationId, task, itemNoList } = this.data;
    if (!allocationId || !task.allocation_id) {
      wx.showToast({ title: '任务无效', icon: 'none' });
      return;
    }
    const selected = (itemNoList || []).filter((r) => r.selected);
    if (selected.length === 0) {
      wx.showToast({ title: '请至少选择一个产品编号', icon: 'none' });
      return;
    }
    const pending = task.pending_qty || 0;
    if (selected.length > pending) {
      wx.showToast({ title: '选择数量不能超过待报数量 ' + pending, icon: 'none' });
      return;
    }
    const itemNos = selected.map((r) => r.no);
    const images = {};
    selected.forEach((r) => {
      if (r.images && r.images.length) images[r.no] = r.images;
    });
    this.setData({ submitting: true });
    const payload = {
      allocation_id: allocationId,
      work_type: 'piece',
      quantity: itemNos.length,
      item_nos: itemNos,
    };
    if (Object.keys(images).length) payload.images = images;
    userApi.submitReport(payload)
      .then(() => {
        this.setData({ submitting: false });
        wx.showToast({ title: '报工成功', icon: 'success' });
        this.load();
      })
      .catch(() => this.setData({ submitting: false }));
  },

  inputQuantity(e) {
    this.setData({ quantity: e.detail.value });
  },

  chooseImage() {
    const max = 9 - (this.data.images || []).length;
    if (max <= 0) return;
    wx.chooseMedia({
      count: max,
      mediaType: ['image'],
      sourceType: ['album', 'camera'],
      success: (res) => {
        const files = res.tempFiles || [];
        if (files.length === 0) return;
        wx.showLoading({ title: '上传中...' });
        let done = 0;
        const total = files.length;
        const list = (this.data.images || []).slice();
        files.forEach((f) => {
          userApi.uploadImage(f.tempFilePath)
            .then((url) => {
              list.push(toFullImageUrl(url) || url);
              done++;
              if (done >= total) {
                wx.hideLoading();
                this.setData({ images: list });
              }
            })
            .catch(() => {
              done++;
              if (done >= total) wx.hideLoading();
              wx.showToast({ title: '部分图片上传失败', icon: 'none' });
            });
        });
      },
    });
  },

  delImage(e) {
    const i = e.currentTarget.dataset.index;
    const list = (this.data.images || []).slice();
    list.splice(i, 1);
    this.setData({ images: list });
  },

  submit() {
    const { allocationId, task, quantity, images } = this.data;
    if (!allocationId || !task.allocation_id) {
      wx.showToast({ title: '任务无效', icon: 'none' });
      return;
    }
    const qty = parseInt(String(quantity).trim(), 10);
    if (isNaN(qty) || qty < 1) {
      wx.showToast({ title: '请输入有效数量', icon: 'none' });
      return;
    }
    const pending = task.pending_qty || 0;
    if (qty > pending) {
      wx.showToast({ title: '数量不能超过待报数量 ' + pending, icon: 'none' });
      return;
    }
    const itemNos = [];
    for (let i = 0; i < qty; i++) itemNos.push(String(i + 1));
    this.setData({ submitting: true });
    const payload = {
      allocation_id: allocationId,
      work_type: 'piece',
      quantity: itemNos.length,
      item_nos: itemNos,
    };
    if (images && images.length) payload.images = { images: images };
    userApi.submitReport(payload)
      .then(() => {
        this.setData({ submitting: false, quantity: '', images: [] });
        wx.showToast({ title: '报工成功', icon: 'success' });
        this.load();
      })
      .catch(() => this.setData({ submitting: false }));
  },

  goBack() {
    wx.navigateBack();
  },
});
