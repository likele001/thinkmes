const { BASE_URL } = require('./utils/config');

App({
  globalData: {
    baseUrl: BASE_URL,
    tenantId: 0,
    tableToken: '',
  },
  onLaunch(options) {
    const scene = (options && options.query && options.query.scene) ? decodeURIComponent(options.query.scene) : '';
    if (scene) {
      const parsed = this.parseScene(scene);
      if (parsed.tenantId) this.globalData.tenantId = parsed.tenantId;
      if (parsed.token) this.globalData.tableToken = parsed.token;
    }
  },
  parseScene(scene) {
    const res = { tenantId: 0, token: '' };
    const parts = String(scene).split('|');
    for (let i = 0; i < parts.length; i++) {
      const p = parts[i];
      if (p.startsWith('t')) res.tenantId = parseInt(p.slice(1), 10) || 0;
      if (p.startsWith('tk')) res.token = p.slice(2);
    }
    return res;
  },
});

