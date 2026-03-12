const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    adminInfo: null,
    dashboard: null,
    stats: null,
    loading: true,
    todayDate: '',
  },

  onShow() {
    if (!getApp().checkAdminLogin()) {
      wx.redirectTo({ url: '/pages/login/login' });
      return;
    }
    const weekDays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
    const d = new Date();
    const todayStr = d.getFullYear() + '年' + (d.getMonth() + 1) + '月' + d.getDate() + '日 ' + weekDays[d.getDay()];
    this.setData({
      adminInfo: getApp().globalData.adminInfo || wx.getStorageSync('adminInfo'),
      todayDate: todayStr,
    });
    this.loadDashboard();
  },

  loadDashboard() {
    this.setData({ loading: true });
    adminApi.getDashboardData()
      .then((res) => {
        const d = res.data || {};
        const orderData = d.order_data || { 0: 0, 1: 0, 2: 0, 3: 0 };
        const planData = d.plan_data || { 0: 0, 1: 0, 2: 0, 3: 0 };
        const totalOrders = (orderData[0] || 0) + (orderData[1] || 0) + (orderData[2] || 0) + (orderData[3] || 0);
        const totalPlans = (planData[0] || 0) + (planData[1] || 0) + (planData[2] || 0) + (planData[3] || 0);
        const today = d.today || {};
        this.setData({
          dashboard: d,
          loading: false,
          stats: {
            totalOrders,
            activeAllocations: d.active_allocations || 0,
            pendingReports: d.pending_reports || 0,
            todayQuantity: today.quantity ?? 0,
            todayWage: (today.wage ?? 0).toFixed(2),
            totalPlans,
          },
        });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  goOrders() {
    wx.navigateTo({ url: '/pages/orders/orders' });
  },

  goCustomers() {
    wx.navigateTo({ url: '/pages/customers/customers' });
  },

  goSuppliers() {
    wx.navigateTo({ url: '/pages/suppliers/suppliers' });
  },

  goAllocations() {
    wx.navigateTo({ url: '/pages/allocations/allocations' });
  },

  goReports() {
    wx.navigateTo({ url: '/pages/reports/reports' });
  },

  goActiveReports() {
    wx.navigateTo({ url: '/pages/active-reports/active-reports' });
  },

  goReportStatistics() {
    wx.navigateTo({ url: '/pages/report-statistics/report-statistics' });
  },

  goProducts() {
    wx.navigateTo({ url: '/pages/products/products' });
  },

  goProcesses() {
    wx.navigateTo({ url: '/pages/processes/processes' });
  },

  goProcessPrices() {
    wx.navigateTo({ url: '/pages/process-prices/process-prices' });
  },

  goMaterials() {
    wx.navigateTo({ url: '/pages/materials/materials' });
  },

  goWarehouses() {
    wx.navigateTo({ url: '/pages/warehouses/warehouses' });
  },

  goStock() {
    wx.navigateTo({ url: '/pages/stock/stock' });
  },

  goBom() {
    wx.navigateTo({ url: '/pages/bom/bom' });
  },

  goPlans() {
    wx.navigateTo({ url: '/pages/plans/plans' });
  },

  goPurchases() {
    wx.navigateTo({ url: '/pages/purchases/purchases' });
  },

  goShipments() {
    wx.navigateTo({ url: '/pages/shipments/shipments' });
  },

  goQuality() {
    wx.navigateTo({ url: '/pages/quality/quality' });
  },

  goAdminWages() {
    wx.navigateTo({ url: '/pages/admin-wages/admin-wages' });
  },

  goTrace() {
    wx.navigateTo({ url: '/pages/trace/trace' });
  },

  goAftersales() {
    wx.navigateTo({ url: '/pages/aftersales/aftersales' });
  },

  goBi() {
    wx.navigateTo({ url: '/pages/bi/bi' });
  },

  logout() {
    wx.showModal({
      title: '确认退出',
      content: '确定要退出管理端吗？',
      success: (res) => {
        if (res.confirm) {
          getApp().adminLogout();
          wx.reLaunch({ url: '/pages/login/login' });
        }
      },
    });
  },

  switchToUser() {
    if (getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/user-index/user-index' });
    } else {
      wx.reLaunch({ url: '/pages/login/login' });
    }
  },
});
