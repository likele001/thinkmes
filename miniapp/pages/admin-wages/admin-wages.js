const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    list: [],
    stats: null,
    total: 0,
    page: 1,
    limit: 20,
    loading: false,
    noMore: false,
    filterWorkDate: '',
    filterUserIndex: 0,
    userOptions: [{ id: '', name: '全部员工' }],
    totalWageSum: '0.00',
    totalQuantitySum: 0,
  },

  onLoad() {
    this.loadStats();
    this.load();
  },

  loadStats() {
    adminApi.getWageStatistics()
      .then((res) => {
        const data = res.data || {};
        const list = data.list || [];
        const userOptions = [{ id: '', name: '全部员工' }];
        list.forEach((item) => {
          userOptions.push({
            id: String(item.user_id),
            name: item.nickname || '用户' + item.user_id,
          });
        });
        let totalWageSum = 0;
        let totalQuantitySum = 0;
        list.forEach((item) => {
          totalWageSum += parseFloat(item.total_wage || 0);
          totalQuantitySum += parseInt(item.total_quantity || 0, 10);
        });
        this.setData({
          stats: data,
          userOptions,
          totalWageSum: totalWageSum.toFixed(2),
          totalQuantitySum,
        });
      })
      .catch(() => {});
  },

  load() {
    if (this.data.loading || this.data.noMore) return;
    this.setData({ loading: true });
    const params = {};
    if (this.data.filterWorkDate) params.work_date = this.data.filterWorkDate;
    if (this.data.filterUserIndex > 0 && this.data.userOptions[this.data.filterUserIndex]) {
      params.user_id = this.data.userOptions[this.data.filterUserIndex].id;
    }
    adminApi.getWageList(this.data.page, this.data.limit, params)
      .then((res) => {
        const d = res.data || {};
        const list = this.data.page === 1 ? (d.list || []) : (this.data.list || []).concat(d.list || []);
        this.setData({
          list,
          total: d.total || 0,
          loading: false,
          noMore: list.length >= (d.total || 0),
        });
      })
      .catch(() => { this.setData({ loading: false }); });
  },

  onWorkDateChange(e) {
    const v = e.detail.value || '';
    this.setData({ filterWorkDate: v, page: 1, list: [], noMore: false });
    this.load();
  },

  onUserChange(e) {
    const idx = parseInt(e.detail.value, 10) || 0;
    this.setData({ filterUserIndex: idx, page: 1, list: [], noMore: false });
    this.load();
  },

  onReachBottom() {
    if (this.data.noMore || this.data.loading) return;
    this.setData({ page: this.data.page + 1 });
    this.load();
  },
});
