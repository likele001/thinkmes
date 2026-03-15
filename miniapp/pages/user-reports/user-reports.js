const { userApi } = require('../../utils/api.js');

function parseDateSafe(t) {
  if (t == null) return null;
  if (typeof t === 'number') return new Date(t * 1000);
  const s = String(t).trim();
  if (!s) return null;
  // iOS 兼容：将 "yyyy-MM-dd HH:mm:ss" 转为 "yyyy-MM-ddTHH:mm:ss" 或 "yyyy/MM/dd HH:mm:ss"
  const normalized = s.replace(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?/, function (_, y, M, d, h, m, sec) {
    return y + '/' + M + '/' + d + ' ' + h + ':' + m + (sec != null ? ':' + sec : ':00');
  });
  const d = new Date(normalized);
  return isNaN(d.getTime()) ? null : d;
}

function formatReportTime(item) {
  if (item.createtime_text) return item.createtime_text;
  const d = parseDateSafe(item.create_time);
  if (!d) return '';
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  const h = String(d.getHours()).padStart(2, '0');
  const min = String(d.getMinutes()).padStart(2, '0');
  return `${y}-${m}-${day} ${h}:${min}`;
}

Page({
  data: {
    records: [],
    loading: true,
    page: 1,
    limit: 20,
    hasMore: true,
    totalQuantity: 0,
    totalWage: 0,
    pendingCount: 0,
  },

  onLoad() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    this.load();
  },

  onShow() {
    if (getApp().checkUserLogin()) this.load();
  },

  onPullDownRefresh() {
    this.setData({ page: 1, hasMore: true });
    this.load().then(() => wx.stopPullDownRefresh());
  },

  load() {
    this.setData({ loading: true });
    const { page, limit } = this.data;
    return userApi.getReports(page, limit)
      .then((res) => {
        const raw = res.data;
        const list = raw && (raw.rows || raw.list || raw.data || Array.isArray(raw) ? (raw.rows || raw.list || raw.data || raw) : []) || [];
        const records = (Array.isArray(list) ? list : []).map((r) => ({
          ...r,
          createtime_text: formatReportTime(r),
          statusText: r.status === 1 ? '已审核' : r.status === 2 ? '审核被拒绝' : '待审核',
        }));
        const totalQuantity = records.reduce((s, r) => s + (Number(r.quantity) || 0), 0);
        const totalWage = records.reduce((s, r) => s + (Number(r.wage) || 0), 0);
        const pendingCount = records.filter((r) => r.status !== 1 && r.status !== 2).length;
        this.setData({
          records: page === 1 ? records : [...(this.data.records || []), ...records],
          loading: false,
          hasMore: records.length >= limit,
          totalQuantity,
          totalWage: totalWage.toFixed(2),
          pendingCount,
        });
      })
      .catch(() => this.setData({ loading: false }));
  },

  goDetail(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    wx.navigateTo({ url: '/pages/user-report-detail/user-report-detail?id=' + id });
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
});
