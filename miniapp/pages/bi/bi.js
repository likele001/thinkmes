const { adminApi } = require('../../utils/api.js');

function todayStr() {
  const d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}
function monthStart() {
  const d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-01';
}

Page({
  data: {
    currentTab: 0,
    todayStr: todayStr(),
    startDate: monthStart(),
    endDate: todayStr(),
    qualityStart: monthStart(),
    qualityEnd: todayStr(),
    costStart: monthStart(),
    costEnd: todayStr(),
    overview: null,
    loadingOverview: false,
    efficiencyList: [],
    loadingEfficiency: false,
    qualityList: [],
    loadingQuality: false,
    costList: [],
    loadingCost: false,
  },

  onLoad() {
    if (!getApp().checkAdminLogin()) {
      wx.redirectTo({ url: '/pages/login/login' });
      return;
    }
    this.loadOverview();
  },

  onShow() {
    if (!getApp().checkAdminLogin()) return;
    if (this.data.currentTab === 0 && !this.data.overview) this.loadOverview();
  },

  switchTab(e) {
    const tab = parseInt(e.currentTarget.dataset.tab, 10);
    this.setData({ currentTab: tab });
    if (tab === 1 && this.data.efficiencyList.length === 0) this.loadEfficiency();
    if (tab === 2 && this.data.qualityList.length === 0) this.loadQuality();
    if (tab === 3 && this.data.costList.length === 0) this.loadCost();
  },

  loadOverview() {
    this.setData({ loadingOverview: true });
    adminApi.getDashboardData()
      .then((res) => {
        this.setData({
          overview: res.data || null,
          loadingOverview: false,
        });
      })
      .catch(() => { this.setData({ loadingOverview: false }); });
  },

  onStartDateChange(e) {
    this.setData({ startDate: e.detail.value });
  },
  onEndDateChange(e) {
    this.setData({ endDate: e.detail.value });
  },
  onQualityStartChange(e) {
    this.setData({ qualityStart: e.detail.value });
  },
  onQualityEndChange(e) {
    this.setData({ qualityEnd: e.detail.value });
  },
  onCostStartChange(e) {
    this.setData({ costStart: e.detail.value });
  },
  onCostEndChange(e) {
    this.setData({ costEnd: e.detail.value });
  },

  loadEfficiency() {
    this.setData({ loadingEfficiency: true });
    adminApi.getBiProductionEfficiency({
      start_date: this.data.startDate,
      end_date: this.data.endDate,
    })
      .then((res) => {
        this.setData({
          efficiencyList: (res.data && res.data.list) || [],
          loadingEfficiency: false,
        });
      })
      .catch(() => { this.setData({ loadingEfficiency: false }); });
  },

  loadQuality() {
    this.setData({ loadingQuality: true });
    adminApi.getBiQualityAnalysis({
      start_date: this.data.qualityStart,
      end_date: this.data.qualityEnd,
    })
      .then((res) => {
        this.setData({
          qualityList: (res.data && res.data.list) || [],
          loadingQuality: false,
        });
      })
      .catch(() => { this.setData({ loadingQuality: false }); });
  },

  loadCost() {
    this.setData({ loadingCost: true });
    adminApi.getBiCostAnalysis({
      start_date: this.data.costStart,
      end_date: this.data.costEnd,
    })
      .then((res) => {
        this.setData({
          costList: (res.data && res.data.list) || [],
          loadingCost: false,
        });
      })
      .catch(() => { this.setData({ loadingCost: false }); });
  },
});
