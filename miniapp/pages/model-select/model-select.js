const app = getApp();

Page({
  data: {
    keyword: '',
    fullList: [],
    filteredList: [],
    focusSearch: true,
  },
  onLoad() {
    const list = app.globalData.modelSelectList || [];
    const listNorm = list.map((m) => ({
      id: m.id,
      label: typeof m.label === 'string' ? m.label : (m.name || '') + (m.model_code ? ' ' + m.model_code : ''),
    }));
    this.setData({ fullList: listNorm, filteredList: listNorm });
    this.eventChannel = this.getOpenerEventChannel();
  },
  onSearch(e) {
    const keyword = (e.detail.value || '').trim().toLowerCase();
    const fullList = this.data.fullList;
    const filteredList = keyword
      ? fullList.filter((m) => (m.label || '').toLowerCase().indexOf(keyword) >= 0)
      : fullList;
    this.setData({ keyword: e.detail.value, filteredList });
  },
  goBack() { wx.navigateBack(); },
  onSelect(e) {
    const id = e.currentTarget.dataset.id;
    const label = e.currentTarget.dataset.label;
    if (this.eventChannel) {
      this.eventChannel.emit('selectModel', { id, label });
    }
    wx.navigateBack();
  },
});
