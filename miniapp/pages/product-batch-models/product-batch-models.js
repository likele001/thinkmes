const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    productId: 0,
    productIndex: 0,
    productLabel: '请选择产品',
    productOptions: [],
    processOptions: [],
    rows: [],
    loading: false,
    submitting: false,
  },
  onLoad() {
    Promise.all([
      adminApi.getProducts(1, 500),
      adminApi.getProcesses(1, 200),
    ]).then(([resP, resProc]) => {
      const products = (resP.data && resP.data.list) || [];
      const processes = (resProc.data && resProc.data.list) || [];
      const productOptions = products.map((p) => ({ id: p.id, label: p.name || '产品' + p.id }));
      if (productOptions.length) productOptions.unshift({ id: 0, label: '请选择产品' });
      const defaultPrices = {};
      processes.forEach((proc) => {
        defaultPrices[proc.id] = { price: '', time_price: '' };
      });
      const rows = (this.data.rows.length ? this.data.rows : [{ name: '', model_code: '', color: '', specification: '', remark: '', prices: {} }]).map((r) => ({
        name: r.name || '',
        model_code: r.model_code || '',
        color: r.color || '',
        specification: r.specification || '',
        remark: r.remark || '',
        prices: r.prices && Object.keys(r.prices).length ? r.prices : { ...defaultPrices },
      }));
      rows.forEach((r) => {
        processes.forEach((proc) => {
          if (!r.prices[proc.id]) r.prices[proc.id] = { price: '', time_price: '' };
        });
      });
      this.setData({
        productOptions: productOptions.length ? productOptions : [{ id: 0, label: '请选择产品' }],
        processOptions: processes,
        rows,
      });
    }).catch(() => {});
  },
  pickProduct(e) {
    const i = parseInt(e.detail.value, 10);
    const opts = this.data.productOptions;
    if (opts[i] && opts[i].id) {
      this.setData({
        productIndex: i,
        productId: opts[i].id,
        productLabel: opts[i].label,
      });
    }
  },
  addRow() {
    const defaultPrices = {};
    (this.data.processOptions || []).forEach((proc) => {
      defaultPrices[proc.id] = { price: '', time_price: '' };
    });
    const rows = (this.data.rows || []).concat([
      { name: '', model_code: '', color: '', specification: '', remark: '', prices: { ...defaultPrices } },
    ]);
    this.setData({ rows });
  },
  removeRow(e) {
    const idx = e.currentTarget.dataset.index;
    const rows = this.data.rows.filter((_, i) => i !== idx);
    if (rows.length === 0) rows.push({ name: '', model_code: '', color: '', specification: '', remark: '', prices: {} });
    this.setData({ rows });
  },
  inputModelName(e) {
    const idx = e.currentTarget.dataset.index;
    const rows = this.data.rows.slice();
    if (rows[idx]) rows[idx].name = e.detail.value || '';
    this.setData({ rows });
  },
  inputModelCode(e) {
    const idx = e.currentTarget.dataset.index;
    const rows = this.data.rows.slice();
    if (rows[idx]) rows[idx].model_code = e.detail.value || '';
    this.setData({ rows });
  },
  inputColor(e) {
    const idx = e.currentTarget.dataset.index;
    const rows = this.data.rows.slice();
    if (rows[idx]) rows[idx].color = e.detail.value || '';
    this.setData({ rows });
  },
  inputSpecification(e) {
    const idx = e.currentTarget.dataset.index;
    const rows = this.data.rows.slice();
    if (rows[idx]) rows[idx].specification = e.detail.value || '';
    this.setData({ rows });
  },
  inputRemark(e) {
    const idx = e.currentTarget.dataset.index;
    const rows = this.data.rows.slice();
    if (rows[idx]) rows[idx].remark = e.detail.value || '';
    this.setData({ rows });
  },
  inputPrice(e) {
    const { index, processId } = e.currentTarget.dataset;
    const rows = this.data.rows.slice();
    const row = rows[index];
    if (!row) return;
    if (!row.prices) row.prices = {};
    if (!row.prices[processId]) row.prices[processId] = {};
    row.prices[processId].price = e.detail.value || '';
    this.setData({ rows });
  },
  inputTimePrice(e) {
    const { index, processId } = e.currentTarget.dataset;
    const rows = this.data.rows.slice();
    const row = rows[index];
    if (!row) return;
    if (!row.prices) row.prices = {};
    if (!row.prices[processId]) row.prices[processId] = {};
    row.prices[processId].time_price = e.detail.value || '';
    this.setData({ rows });
  },
  submit() {
    const { productId, rows, processOptions } = this.data;
    if (!productId) {
      wx.showToast({ title: '请选择产品', icon: 'none' });
      return;
    }
    const hasName = rows.some((r) => (r.name || '').trim());
    if (!hasName) {
      wx.showToast({ title: '请至少填写一条型号名称', icon: 'none' });
      return;
    }
    const models = rows
      .map((r) => {
        const name = (r.name || '').trim();
        if (!name) return null;
        const prices = (processOptions || []).map((proc) => {
          const p = (r.prices || {})[proc.id] || {};
          const price = parseFloat(p.price) || 0;
          const time_price = parseFloat(p.time_price) || 0;
          return { process_id: proc.id, price, time_price };
        }).filter((p) => p.price > 0 || p.time_price > 0);
        return {
          name,
          model_code: (r.model_code || '').trim(),
          color: (r.color || '').trim(),
          specification: (r.specification || '').trim(),
          remark: (r.remark || '').trim(),
          description: '',
          prices,
        };
      })
      .filter(Boolean);
    if (models.length === 0) {
      wx.showToast({ title: '请至少填写一条有效型号', icon: 'none' });
      return;
    }
    this.setData({ submitting: true });
    adminApi
      .batchAddProductModels({ product_id: productId, models })
      .then((res) => {
        const msg = (res.msg || '') + (res.data && res.data.added !== undefined ? '（成功 ' + res.data.added + ' 个）' : '');
        wx.showToast({ title: msg || '添加成功', icon: 'none', duration: 2500 });
        setTimeout(() => {
          wx.showModal({
            title: '提示',
            content: '是否继续批量添加？',
            success: (r) => {
              if (r.confirm) {
                const defaultPrices = {};
                (this.data.processOptions || []).forEach((proc) => {
                  defaultPrices[proc.id] = { price: '', time_price: '' };
                });
                this.setData({
                  rows: [{ name: '', model_code: '', color: '', specification: '', remark: '', prices: { ...defaultPrices } }],
                  submitting: false,
                });
              } else {
                wx.navigateBack();
              }
            },
          });
        }, 500);
      })
      .catch(() => {})
      .finally(() => { this.setData({ submitting: false }); });
  },
});
