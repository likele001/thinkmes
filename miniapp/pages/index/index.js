const { adminApi } = require('../../utils/api.js');

// 首页菜单与 PC 权限节点对应（node 为空表示不校验，始终显示）
const MENU_ITEMS = [
  { node: 'mes/order', icon: '📋', text: '订单管理', handler: 'goOrders' },
  { node: 'mes/customer', icon: '👥', text: '客户管理', handler: 'goCustomers' },
  { node: 'mes/allocation', icon: '🔧', text: '分工管理', handler: 'goAllocations' },
  { node: 'mes/report', icon: '✅', text: '报工审核', handler: 'goReports' },
  { node: 'mes/report', icon: '⚡', text: '待审核报工', handler: 'goActiveReports' },
  { node: 'mes/report', icon: '📊', text: '报工统计', handler: 'goReportStatistics' },
  { node: 'mes/product', icon: '📦', text: '产品/型号', handler: 'goProducts' },
  { node: 'mes/process', icon: '⚙️', text: '工序管理', handler: 'goProcesses' },
  { node: 'mes/process_price', icon: '💰', text: '工序工价', handler: 'goProcessPrices' },
  { node: 'mes/material', icon: '🧱', text: '物料', handler: 'goMaterials' },
  { node: 'mes/supplier', icon: '🏢', text: '供应商', handler: 'goSuppliers' },
  { node: 'mes/warehouse', icon: '🏭', text: '仓库', handler: 'goWarehouses' },
  { node: 'mes/stock', icon: '📊', text: '库存', handler: 'goStock' },
  { node: 'mes/bom', icon: '📑', text: 'BOM', handler: 'goBom' },
  { node: 'mes/production_plan', icon: '📅', text: '生产计划', handler: 'goPlans' },
  { node: 'mes/purchase', icon: '🛒', text: '采购', handler: 'goPurchases' },
  { node: 'mes/shipment', icon: '🚚', text: '发货', handler: 'goShipments' },
  { node: 'mes/quality', icon: '🔍', text: '质检', handler: 'goQuality' },
  { node: 'mes/wage', icon: '💵', text: '工资', handler: 'goAdminWages' },
  { node: 'mes/trace_code', icon: '🔗', text: '追溯码', handler: 'goTrace' },
  { node: 'mes/after_sales', icon: '🛠️', text: '售后', handler: 'goAftersales' },
  { node: 'mes/bi', icon: '📈', text: 'BI看板', handler: 'goBi' },
  { node: null, icon: '👷', text: '切换员工端', handler: 'switchToUser' },
  { node: null, icon: '🔒', text: '退出登录', handler: 'logout' },
];

Page({
  data: {
    adminInfo: null,
    dashboard: null,
    stats: null,
    loading: true,
    todayDate: '',
    menuList: [],
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
    const app = getApp();
    let nodes = app.globalData.scanworkNodes;
    if (!nodes || !Array.isArray(nodes)) {
      nodes = wx.getStorageSync('scanworkNodes') || [];
      if (nodes && nodes.length) app.globalData.scanworkNodes = nodes;
    }
    if (nodes.length === 0) {
      this.buildMenuList(['*']);
      adminApi.getScanworkMenu()
        .then((res) => {
          nodes = (res.data && res.data.nodes) || [];
          app.globalData.scanworkNodes = nodes;
          wx.setStorageSync('scanworkNodes', nodes);
          this.buildMenuList(nodes);
        })
        .catch(() => { this.buildMenuList([]); });
    } else {
      this.buildMenuList(nodes);
    }
    this.loadDashboard();
  },

  hasNode(nodes, node) {
    if (!node) return true;
    if (!nodes || !nodes.length) return false;
    if (nodes.indexOf('*') !== -1) return true;
    if (nodes.indexOf(node) !== -1) return true;
    const prefix = node + '/';
    return nodes.some(function (n) { return n && n.indexOf(prefix) === 0; });
  },

  buildMenuList(nodes) {
    const list = MENU_ITEMS.filter(function (item) {
      return this.hasNode(nodes, item.node);
    }, this);
    this.setData({ menuList: list });
  },

  onMenuTap(e) {
    const handler = e.currentTarget.dataset.handler;
    if (handler && this[handler]) this[handler]();
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
      .catch((err) => {
        this.setData({ loading: false });
        if (err && err.statusCode === 403) {
          this.setData({ stats: null });
        }
      });
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
