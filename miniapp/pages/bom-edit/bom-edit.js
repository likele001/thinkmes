const { adminApi } = require('../../utils/api.js');

Page({
  data: {
    id: 0,
    bom_no: '',
    bom_name: '',
    productId: 0,
    modelId: 0,
    productLabel: '请选择产品',
    modelLabel: '请选择型号',
    productIndex: 0,
    modelIndex: 0,
    productOptions: [],
    modelOptions: [],
    allModels: [],
    version: '1.0',
    base_quantity: '1',
    status: 0,
    statusText: '草稿',
    statusList: [
      { value: 0, text: '草稿' },
      { value: 1, text: '审核中' },
      { value: 2, text: '已发布' },
      { value: 3, text: '已废弃' },
    ],
    statusIndex: 0,
    loading: false,
  },
  goBack() { wx.navigateBack(); },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    Promise.all([
      adminApi.getProducts(1, 200),
      adminApi.getModels(1, 200),
    ]).then(([resP, resM]) => {
      const products = (resP.data && resP.data.list) || [];
      const allModels = (resM.data && resM.data.list) || [];
      const productOptions = products.map((p) => ({ id: p.id, label: p.name || '产品' + p.id }));
      const modelOptions = allModels.map((m) => ({
        id: m.id,
        product_id: m.product_id,
        label: (m.product && m.product.name ? m.product.name + ' - ' : '') + (m.name || '型号' + m.id),
      }));
      this.setData({ productOptions, allModels: modelOptions, modelOptions });
      if (id) {
        adminApi.getBomDetail(id).then((res) => {
          const d = res.data || {};
          const pid = d.product_id || 0;
          const mid = d.model_id || 0;
          const pi = productOptions.findIndex((o) => o.id === pid);
          const mi = modelOptions.findIndex((o) => o.id === mid);
          const st = d.status !== undefined ? d.status : 0;
          const si = this.data.statusList.findIndex((s) => s.value === st);
          this.setData({
            bom_no: d.bom_no || '',
            bom_name: d.bom_name || d.name || '',
            productId: pid,
            modelId: mid,
            productLabel: pi >= 0 ? productOptions[pi].label : '请选择产品',
            modelLabel: mi >= 0 ? modelOptions[mi].label : '请选择型号',
            productIndex: pi >= 0 ? pi : 0,
            modelIndex: mi >= 0 ? mi : 0,
            version: d.version || '1.0',
            base_quantity: String(d.base_quantity !== undefined ? d.base_quantity : 1),
            status: st,
            statusText: this.data.statusList[si >= 0 ? si : 0].text,
            statusIndex: si >= 0 ? si : 0,
          });
        }).catch(() => {});
      }
    }).catch(() => {});
  },
  pickProduct(e) {
    const i = parseInt(e.detail.value, 10);
    const opts = this.data.productOptions;
    if (!opts[i]) return;
    const productId = opts[i].id;
    const modelOptions = this.data.allModels.filter((m) => m.product_id === productId);
    if (modelOptions.length === 0) modelOptions.push({ id: 0, label: '无型号' });
    this.setData({
      productIndex: i,
      productId,
      productLabel: opts[i].label,
      modelOptions,
      modelIndex: 0,
      modelId: modelOptions[0] && modelOptions[0].id ? modelOptions[0].id : 0,
      modelLabel: modelOptions[0] ? modelOptions[0].label : '请选择型号',
    });
  },
  openModelSelect() {
    const modelOptions = this.data.modelOptions;
    if (!modelOptions.length) { wx.showToast({ title: '请先选择产品', icon: 'none' }); return; }
    getApp().globalData.modelSelectList = modelOptions;
    wx.navigateTo({
      url: '/pages/model-select/model-select',
      events: {
        selectModel: (data) => {
          const idx = this.data.modelOptions.findIndex((o) => o.id === data.id);
          this.setData({ modelId: data.id, modelLabel: data.label, modelIndex: idx >= 0 ? idx : 0 });
        },
      },
    });
  },
  pickModel(e) {
    const i = parseInt(e.detail.value, 10);
    const opts = this.data.modelOptions;
    if (opts[i]) this.setData({ modelIndex: i, modelId: opts[i].id, modelLabel: opts[i].label });
  },
  pickStatus(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.statusList;
    if (list[i]) this.setData({ statusIndex: i, status: list[i].value, statusText: list[i].text });
  },
  inputBomNo(e) { this.setData({ bom_no: e.detail.value }); },
  inputBomName(e) { this.setData({ bom_name: e.detail.value }); },
  inputVersion(e) { this.setData({ version: e.detail.value }); },
  inputBaseQuantity(e) { this.setData({ base_quantity: e.detail.value }); },
  submit() {
    const { id, bom_no, bom_name, productId, modelId, version, base_quantity, status } = this.data;
    if (!bom_name || !bom_name.trim()) { wx.showToast({ title: '请输入BOM名称', icon: 'none' }); return; }
    if (!productId || !modelId) { wx.showToast({ title: '请选择产品和型号', icon: 'none' }); return; }
    this.setData({ loading: true });
    const row = {
      bom_no: (bom_no || '').trim(),
      bom_name: bom_name.trim(),
      product_id: productId,
      model_id: modelId,
      version: (version || '1.0').trim(),
      base_quantity: parseInt(base_quantity, 10) || 1,
      status,
    };
    const p = id ? adminApi.updateBom({ id, row }) : adminApi.createBom({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
