const { adminApi } = require('../../utils/api.js');

// 首页菜单与 PC 权限节点对应（node 为空表示不校验，始终显示）
const MENU_ITEMS = [
  { node: 'mes/order', text: '订单管理', handler: 'goOrders' },
  { node: 'mes/customer', text: '客户管理', handler: 'goCustomers' },
  { node: 'mes/allocation', text: '分工管理', handler: 'goAllocations' },
  { node: 'mes/report', text: '报工审核', handler: 'goReports' },
  { node: 'mes/report', text: '待审核报工', handler: 'goActiveReports' },
  { node: 'mes/report', text: '报工统计', handler: 'goReportStatistics' },
  { node: 'mes/product', text: '产品/型号', handler: 'goProducts' },
  { node: 'mes/process', text: '工序管理', handler: 'goProcesses' },
  { node: 'mes/process_price', text: '工序工价', handler: 'goProcessPrices' },
  { node: 'mes/material', text: '物料', handler: 'goMaterials' },
  { node: 'mes/supplier', text: '供应商', handler: 'goSuppliers' },
  { node: 'mes/warehouse', text: '仓库', handler: 'goWarehouses' },
  { node: 'mes/stock', text: '库存', handler: 'goStock' },
  { node: 'mes/bom', text: 'BOM', handler: 'goBom' },
  { node: 'mes/production_plan', text: '生产计划', handler: 'goPlans' },
  { node: 'mes/purchase', text: '采购', handler: 'goPurchases' },
  { node: 'mes/shipment', text: '发货', handler: 'goShipments' },
  { node: 'mes/quality', text: '质检', handler: 'goQuality' },
  { node: 'mes/wage', text: '工资', handler: 'goAdminWages' },
  { node: 'mes/trace_code', text: '追溯码', handler: 'goTrace' },
  { node: 'mes/after_sales', text: '售后', handler: 'goAftersales' },
  { node: 'mes/bi', text: 'BI看板', handler: 'goBi' },
  { node: null, text: '切换员工端', handler: 'switchToUser' },
  { node: null, text: '退出登录', handler: 'logout' },
];

const PALETTES = ['blue', 'indigo', 'mint', 'amber', 'purple', 'teal'];

Page({
  data: {
    adminInfo: null,
    dashboard: null,
    stats: null,
    statItems: [],
    loading: false,
    error: '',
    todayDate: '',
    menuList: [],
    loaded: false,
  },

  onShow() {
    if (!getApp().checkAdminLogin()) {
      wx.redirectTo({ url: '/pages/login/login' });
      return;
    }
    this.setToday();
    this.syncNodesAndMenu();
    this.loadDashboard();
  },

  onPullDownRefresh() {
    this.loadDashboard(() => {
      if (wx.stopPullDownRefresh) wx.stopPullDownRefresh();
    });
  },

  setToday() {
    const weekDays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
    const d = new Date();
    const todayStr = `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日 ${weekDays[d.getDay()]}`;
    const adminInfo = getApp().globalData.adminInfo || wx.getStorageSync('adminInfo') || {};
    const avatarChar = (adminInfo.nickname || adminInfo.username || '管').slice(0, 1);
    this.setData({
      adminInfo,
      avatarChar,
      todayDate: todayStr,
    });
  },

  syncNodesAndMenu() {
    const app = getApp();
    let nodes = app.globalData.scanworkNodes;
    if (!Array.isArray(nodes)) {
      nodes = wx.getStorageSync('scanworkNodes') || [];
      if (Array.isArray(nodes)) app.globalData.scanworkNodes = nodes;
    }
    if (!nodes || !nodes.length) {
      this.buildMenuList(['*']);
      adminApi.getScanworkMenu()
        .then((res) => {
          const newNodes = (res.data && res.data.nodes) || [];
          app.globalData.scanworkNodes = newNodes;
          wx.setStorageSync('scanworkNodes', newNodes);
          this.buildMenuList(newNodes);
        })
        .catch(() => { this.buildMenuList([]); });
    } else {
      this.buildMenuList(nodes);
    }
  },

  refreshMenu() {
    this.syncNodesAndMenu();
  },

  hasNode(nodes, node) {
    if (!node) return true;
    if (!nodes || !nodes.length) return false;
    if (nodes.indexOf('*') !== -1) return true;
    if (nodes.indexOf(node) !== -1) return true;
    const prefix = `${node}/`;
    return nodes.some((n) => n && n.indexOf(prefix) === 0);
  },

  buildMenuList(nodes = []) {
    const list = MENU_ITEMS.filter((item) => this.hasNode(nodes, item.node))
      .map((item, idx) => ({
        ...item,
        abbr: item.text.slice(0, 2),
        tone: PALETTES[idx % PALETTES.length],
      }));
    this.setData({ menuList: list });
  },

  onMenuTap(e) {
    const handler = e.currentTarget.dataset.handler;
    if (handler && typeof this[handler] === 'function') this[handler]();
  },

  onStatTap(e) {
    const handler = e.currentTarget.dataset.handler;
    if (handler && typeof this[handler] === 'function') this[handler]();
  },

  loadDashboard(done) {
    const finish = () => {
      if (typeof done === 'function') done();
      if (wx.stopPullDownRefresh) wx.stopPullDownRefresh();
    };
    this.setData({ loading: true, error: '' });
    adminApi.getDashboardData()
      .then((res) => {
        const d = res.data || {};
        const orderData = d.order_data || {};
        const planData = d.plan_data || {};
        const totalOrders = (orderData[0] || 0) + (orderData[1] || 0) + (orderData[2] || 0) + (orderData[3] || 0);
        const totalPlans = (planData[0] || 0) + (planData[1] || 0) + (planData[2] || 0) + (planData[3] || 0);
        const today = d.today || {};
        const statItems = [
          { key: 'totalOrders', label: '总订单数', value: totalOrders, tap: 'goOrders' },
          { key: 'activeAllocations', label: '进行中分工', value: d.active_allocations || 0, tap: 'goAllocations' },
          { key: 'pendingReports', label: '待审核报工', value: d.pending_reports || 0, tap: 'goReports' },
          { key: 'todayQuantity', label: '今日报工量', value: today.quantity ?? 0, tap: 'goReportStatistics' },
          { key: 'todayWage', label: '今日工资', value: `¥${(today.wage ?? 0).toFixed(2)}`, tap: 'goAdminWages' },
          { key: 'totalPlans', label: '生产计划数', value: totalPlans, tap: 'goPlans' },
        ].map((item, idx) => ({ ...item, tone: PALETTES[idx % PALETTES.length] }));

        this.setData({
          dashboard: d,
          loading: false,
          loaded: true,
          stats: {
            totalOrders,
            activeAllocations: d.active_allocations || 0,
            pendingReports: d.pending_reports || 0,
            todayQuantity: today.quantity ?? 0,
            todayWage: (today.wage ?? 0).toFixed(2),
            totalPlans,
          },
          statItems,
        });
        finish();
      })
      .catch((err) => {
        const msg = (err && err.msg) || (err && err.message) || '加载失败，请稍后再试';
        this.setData({ loading: false, loaded: true, error: msg });
        wx.showToast({ icon: 'none', title: msg });
        if (err && err.statusCode === 403) {
          this.setData({ stats: null, statItems: [] });
        }
        finish();
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
