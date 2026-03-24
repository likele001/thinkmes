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
    dateMode: 'month',
    orderNo: '',
    productName: '',
    totalWage: '0',
    totalQuantity: 0,
    filterExpanded: false,
    loadedOnce: false,
  },

  onLoad() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    const now = new Date();
    const ym = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    this.setData({ selectedMonth: ym });
    this.load({ reset: true });
  },

  onShow() {
    if (this.data.loadedOnce && getApp().checkUserLogin()) {
      this.load({ reset: true });
    }
  },

  onPullDownRefresh() {
    this.load({ reset: true }).then(() => wx.stopPullDownRefresh());
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

  load(opts = {}) {
    const reset = !!opts.reset;
    const nextPage = reset ? 1 : this.data.page;
    const prevList = reset ? [] : (this.data.wageList || []);
    this.setData({ loading: true, ...(reset ? { page: 1, hasMore: true, wageList: [] } : {}) });
    const { limit } = this.data;
    const optsReq = this.buildOpts();
    return userApi.getWages(nextPage, limit, optsReq)
      .then((res) => {
        const raw = res.data || {};
        const list = raw.list || raw.rows || raw.data || (Array.isArray(raw) ? raw : []) || [];
        const mapped = Array.isArray(list) ? list : [];
        const merged = nextPage === 1 ? mapped : [...prevList, ...mapped];
        const dedupMap = {};
        const unique = [];
        merged.forEach((r) => {
          const key = r.id || `${r.order_no || ''}-${r.create_time || ''}-${unique.length}`;
          if (!dedupMap[key]) { dedupMap[key] = true; unique.push(r); }
        });
        const totalFromApi = raw.total || raw.count || raw.total_count;
        const totalQuantity = unique.reduce((s, r) => s + (Number(r.quantity) || 0), 0);
        const totalWageRaw = raw.totalWage != null ? String(raw.totalWage) : unique.reduce((s, r) => s + (Number(r.wage) ?? Number(r.total_wage) ?? 0), 0).toFixed(2);
        const totalWage = typeof totalWageRaw === 'number' ? totalWageRaw.toFixed(2) : totalWageRaw;
        const hasMore = totalFromApi != null ? unique.length < Number(totalFromApi) : mapped.length >= limit;
        this.setData({
          wageList: unique,
          loading: false,
          hasMore,
          totalWage,
          totalQuantity,
          loadedOnce: true,
          page: nextPage + 1,
        });
      })
      .catch((err) => {
        this.setData({ loading: false });
        wx.showToast({ title: (err && err.msg) || '加载失败', icon: 'none' });
      });
  },

  loadMore() {
    if (this.data.loading || !this.data.hasMore) return;
    this.load({ reset: false });
  },

  refresh() {
    this.load({ reset: true });
  },

  toggleFilter() {
    this.setData({ filterExpanded: !this.data.filterExpanded });
  },

  onMonthChange(e) {
    const v = e.detail.value;
    if (!v) return;
    const parts = v.split('-');
    const month = parts[0] + '-' + (parts[1] || '01');
    this.setData({ selectedMonth: month, dateMode: 'month' });
    this.load({ reset: true });
  },

  onWorkDateChange(e) {
    const v = e.detail.value;
    this.setData({ workDate: v, dateMode: 'single' });
    this.load({ reset: true });
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
    this.setData({ filterExpanded: false });
    this.load({ reset: true });
  },

  clearFilter() {
    const now = new Date();
    const ym = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    this.setData({
      orderNo: '', productName: '', startDate: '', endDate: '', workDate: '',
      selectedMonth: ym, dateMode: 'month', filterExpanded: false,
    });
    this.load({ reset: true });
  },
});
