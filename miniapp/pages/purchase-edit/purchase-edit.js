const { adminApi } = require('../../utils/api.js');

function timestampToDate(ts) {
  if (!ts || ts <= 0) return '';
  const d = new Date(ts * 1000);
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return y + '-' + m + '-' + day;
}

function dateToTimestamp(str) {
  if (!str || !str.trim()) return null;
  const d = new Date(str.trim());
  return isNaN(d.getTime()) ? null : Math.floor(d.getTime() / 1000);
}

Page({
  data: {
    id: 0,
    materialId: 0,
    materialLabel: '请选择物料',
    materialIndex: 0,
    materialOptions: [],
    in_quantity: '',
    in_time_date: '',
    in_no: '',
    supplierId: 0,
    supplierLabel: '请选择供应商',
    supplierIndex: 0,
    supplierOptions: [],
    warehouseId: 0,
    warehouseLabel: '请选择仓库',
    warehouseIndex: 0,
    warehouseOptions: [],
    remark: '',
    loading: false,
  },
  onLoad(options) {
    const id = options.id ? parseInt(options.id, 10) : 0;
    this.setData({ id });
    Promise.all([
      adminApi.getMaterialList(1, 200),
      adminApi.getSupplierList(1, 200),
      adminApi.getWarehouseList(1, 200),
    ]).then(([resM, resS, resW]) => {
      const materials = (resM.data && resM.data.list) || [];
      const suppliers = (resS.data && resS.data.list) || [];
      const warehouses = (resW.data && resW.data.list) || [];
      const materialOptions = materials.map((m) => ({ id: m.id, label: (m.name || '') + ' ' + (m.code || '') }));
      const supplierOptions = suppliers.map((s) => ({ id: s.id, label: s.name || '供应商' + s.id }));
      this.setData({ materialOptions, supplierOptions, warehouseOptions: warehouses });
      if (id) {
        adminApi.getPurchaseDetail(id).then((res) => {
          const d = res.data || {};
          const mid = d.material_id || 0;
          const sid = d.supplier_id || 0;
          const wid = d.warehouse_id || 0;
          const mi = this.data.materialOptions.findIndex((o) => o.id === mid);
          const si = supplierOptions.findIndex((o) => o.id === sid);
          const wi = warehouses.findIndex((w) => w.id === wid);
          this.setData({
            materialId: mid,
            materialLabel: mi >= 0 ? this.data.materialOptions[mi].label : '请选择物料',
            materialIndex: mi >= 0 ? mi : 0,
            in_quantity: String(d.in_quantity ?? ''),
            in_time_date: timestampToDate(d.in_time),
            in_no: d.in_no || '',
            supplierId: sid,
            supplierLabel: si >= 0 ? supplierOptions[si].label : '请选择供应商',
            supplierIndex: si >= 0 ? si : 0,
            warehouseId: wid,
            warehouseLabel: wi >= 0 ? warehouses[wi].name : '请选择仓库',
            warehouseIndex: wi >= 0 ? wi : 0,
            remark: d.remark || '',
          });
        }).catch(() => {});
      }
    }).catch(() => {});
  },
  pickMaterial(e) {
    const i = parseInt(e.detail.value, 10);
    const opts = this.data.materialOptions;
    if (opts[i]) this.setData({ materialIndex: i, materialId: opts[i].id, materialLabel: opts[i].label });
  },
  pickSupplier(e) {
    const i = parseInt(e.detail.value, 10);
    const opts = this.data.supplierOptions;
    if (opts[i]) this.setData({ supplierIndex: i, supplierId: opts[i].id, supplierLabel: opts[i].label });
  },
  pickWarehouse(e) {
    const i = parseInt(e.detail.value, 10);
    const list = this.data.warehouseOptions;
    if (list[i]) this.setData({ warehouseIndex: i, warehouseId: list[i].id, warehouseLabel: list[i].name });
  },
  pickInTime(e) { this.setData({ in_time_date: e.detail.value }); },
  inputInQuantity(e) { this.setData({ in_quantity: e.detail.value }); },
  inputInNo(e) { this.setData({ in_no: e.detail.value }); },
  inputRemark(e) { this.setData({ remark: e.detail.value }); },
  submit() {
    const { id, materialId, in_quantity, in_time_date, in_no, supplierId, warehouseId, remark } = this.data;
    if (!id && (!materialId || !parseFloat(in_quantity))) {
      wx.showToast({ title: '请选择物料并填写入库数量', icon: 'none' });
      return;
    }
    this.setData({ loading: true });
    const row = {
      material_id: materialId,
      in_quantity: parseFloat(in_quantity) || 0,
      in_time: dateToTimestamp(in_time_date),
      in_no: (in_no || '').trim(),
      supplier_id: supplierId || 0,
      warehouse_id: warehouseId || 0,
      remark: (remark || '').trim(),
    };
    if (row.in_time == null) delete row.in_time;
    const p = id ? adminApi.updatePurchase({ id, row }) : adminApi.createPurchase({ row });
    p.then(() => {
      wx.showToast({ title: id ? '保存成功' : '添加成功' });
      setTimeout(() => wx.navigateBack(), 1500);
    }).catch(() => {}).finally(() => { this.setData({ loading: false }); });
  },
});
