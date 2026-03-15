const { userApi } = require('../../utils/api.js');

Page({
  data: {
    wageList: [],
    loading: true,
    page: 1,
    limit: 20,
    hasMore: true,
    workDate: '',
    selectedMonth: '',
    startDate: '',
    endDate: '',
    dateMode: 'month', // 'month' | 'single' | 'range'
    orderNo: '',
    productName: '',
    totalWage: '0',
    totalQuantity: 0,
    filterExpanded: false,
  },

  onLoad() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    const now = new Date();
    const ym = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    this.setData({ selectedMonth: ym });
    this.load();
  },

  onShow() {
    if (getApp().checkUserLogin()) this.load();
  },

  onPullDownRefresh() {
    this.setData({ page: 1, hasMore: true });
    this.load().then(() => wx.stopPullDownRefresh());
  },

  buildOpts() {
    const { dateMode, selectedMonth, workDate, startDate, endDate, orderNo, productName } = this.data;
    const opts = {};
    if (dateMode === 'single' && workDate) opts.work_date = workDate;
    else if (dateMode === 'range' && startDate && endDate) {
      opts.start_date = startDate;
      opts.end_date = endDate;
    } else if (selectedMonth) opts.month = selectedMonth;
    if (orderNo && orderNo.trim()) opts.order_no = orderNo.trim();
    if (productName && productName.trim()) opts.product_name = productName.trim();
    return opts;
  },

  load() {
    this.setData({ loading: true });
    const { page, limit } = this.data;
    const opts = this.buildOpts();
    return userApi.getWages(page, limit, opts)
      .then((res) => {
        const raw = res.data || {};
        const list = raw.list || raw.rows || raw.data || (Array.isArray(raw) ? raw : []) || [];
        const wageList = Array.isArray(list) ? list : [];
        const totalQuantity = wageList.reduce((s, r) => s + (Number(r.quantity) || 0), 0);
        const totalWage = raw.totalWage != null ? String(raw.totalWage) : wageList.reduce((s, r) => s + (Number(r.wage) ?? Number(r.total_wage) ?? 0), 0).toFixed(2);
        this.setData({
          wageList: page === 1 ? wageList : [...(this.data.wageList || []), ...wageList],
          loading: false,
          hasMore: wageList.length >= limit,
          totalWage: typeof totalWage === 'number' ? totalWage.toFixed(2) : totalWage,
          totalQuantity,
        });
      })
      .catch(() => this.setData({ loading: false }));
  },

  loadMore() {
    if (this.data.loading || !this.data.hasMore) return;
    this.setData({ page: this.data.page + 1 });
    this.load();
  },

  refresh() {
    this.setData({ page: 1, hasMore: true });
    this.load();
  },

  toggleFilter() {
    this.setData({ filterExpanded: !this.data.filterExpanded });
  },

  onMonthChange(e) {
    const v = e.detail.value;
    if (!v) return;
    const parts = v.split('-');
    const month = parts[0] + '-' + (parts[1] || '01');
    this.setData({ selectedMonth: month, dateMode: 'month', page: 1, hasMore: true });
    this.load();
  },

  onWorkDateChange(e) {
    const v = e.detail.value;
    this.setData({ workDate: v, dateMode: 'single', page: 1, hasMore: true });
    this.load();
  },

  onStartDateChange(e) {
    this.setData({ startDate: e.detail.value, dateMode: 'range' });
  },
  onEndDateChange(e) {
    this.setData({ endDate: e.detail.value, dateMode: 'range' });
  },

  onOrderNoInput(e) {
    this.setData({ orderNo: e.detail.value || '' });
  },
  onProductNameInput(e) {
    this.setData({ productName: e.detail.value || '' });
  },

  applyFilter() {
    this.setData({ page: 1, hasMore: true, filterExpanded: false });
    this.load();
  },

  clearFilter() {
    const now = new Date();
    const ym = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    this.setData({
      orderNo: '', productName: '', startDate: '', endDate: '', workDate: '',
      selectedMonth: ym, dateMode: 'month', page: 1, hasMore: true, filterExpanded: false,
    });
    this.load();
  },
});
