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
    loadedOnce: false,
  },

  onLoad() {
    if (!getApp().checkUserLogin()) {
      wx.reLaunch({ url: '/pages/login/login' });
      return;
    }
    this.load({ reset: true });
  },

  onShow() {
    if (this.data.loadedOnce && getApp().checkUserLogin()) {
      this.load({ reset: true });
    }
  },

  onPullDownRefresh() {
    this.setData({ page: 1, hasMore: true });
    this.load().then(() => wx.stopPullDownRefresh());
  },

  load(opts = {}) {
    const reset = !!opts.reset;
    const nextPage = reset ? 1 : this.data.page;
    const prevRecords = reset ? [] : (this.data.records || []);
    this.setData({ loading: true, ...(reset ? { page: 1, hasMore: true, records: [] } : {}) });
    const { limit } = this.data;
    return userApi.getReports(nextPage, limit)
      .then((res) => {
        const raw = res.data || {};
        const list = raw && (raw.rows || raw.list || raw.data || (Array.isArray(raw) ? raw : [])) || [];
        const mapped = (Array.isArray(list) ? list : []).map((r) => ({
          ...r,
          createtime_text: formatReportTime(r),
          statusText: r.status === 1 ? '已审核' : r.status === 2 ? '审核被拒绝' : '待审核',
        }));
        const merged = nextPage === 1 ? mapped : [...prevRecords, ...mapped];
        // 去重（按 id/report_id）
        const dedupMap = {};
        const unique = [];
        merged.forEach((r) => {
          const key = r.id || r.report_id || `${r.order_id || ''}-${r.create_time || ''}-${unique.length}`;
          if (!dedupMap[key]) {
            dedupMap[key] = true;
            unique.push(r);
          }
        });
        const totalFromApi = raw.total || raw.count || raw.total_count;
        const totalQuantity = unique.reduce((s, r) => s + (Number(r.quantity) || 0), 0);
        const totalWage = unique.reduce((s, r) => s + (Number(r.wage) || 0), 0);
        const pendingCount = unique.filter((r) => r.status !== 1 && r.status !== 2).length;
        const hasMore = totalFromApi != null
          ? unique.length < Number(totalFromApi)
          : mapped.length >= limit;
        this.setData({
          records: unique,
          loading: false,
          hasMore,
          totalQuantity,
          totalWage: totalWage.toFixed(2),
          pendingCount,
          loadedOnce: true,
          page: nextPage + 1,
        });
      })
      .catch((err) => {
        this.setData({ loading: false });
        wx.showToast({ title: (err && err.msg) || '加载失败，请重试', icon: 'none' });
      });
  },

  goDetail(e) {
    const id = e.currentTarget.dataset.id;
    if (!id) return;
    wx.navigateTo({ url: '/pages/user-report-detail/user-report-detail?id=' + id });
  },

  loadMore() {
    if (this.data.loading || !this.data.hasMore) return;
    this.load({ reset: false });
  },

  refresh() {
    this.load({ reset: true });
  },
});
