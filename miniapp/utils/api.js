function getAppRef() { return getApp(); }

// 员工端 API（userRequest）
const userApi = {
  getDashboard() {
    return getAppRef().userRequest({ url: '/worker/dashboard', method: 'GET' });
  },
  getTaskInfo(allocationId) {
    return getAppRef().userRequest({ url: '/worker/taskInfo', method: 'GET', data: { allocation_id: allocationId } });
  },
  submitReport(data) {
    return getAppRef().userRequest({ url: '/worker/report', method: 'POST', data });
  },
  getReports(page, limit, status) {
    const data = { page: page || 1, limit: limit || 20 };
    if (status !== undefined && status !== '') data.status = status;
    return getAppRef().userRequest({ url: '/worker/reports', method: 'GET', data });
  },
  getReportDetail(reportId) {
    return getAppRef().userRequest({ url: '/worker/reportDetail', method: 'GET', data: { report_id: reportId } });
  },
  getWages(page, limit, workDate) {
    const data = { page: page || 1, limit: limit || 20 };
    if (workDate) data.work_date = workDate;
    return getAppRef().userRequest({ url: '/worker/wages', method: 'GET', data });
  },
  uploadImage(filePath) {
    const token = wx.getStorageSync('user_token') || '';
    const { BASE_URL } = require('./config.js');
    return new Promise((resolve, reject) => {
      wx.uploadFile({
        url: BASE_URL + '/worker/uploadImage',
        filePath,
        name: 'image',
        header: { Authorization: token ? 'Bearer ' + token : '' },
        success(res) {
          try {
            const data = JSON.parse(res.data);
            if (data.code === 1 && data.data && data.data.url) resolve(data.data.url);
            else reject(data);
          } catch (e) { reject(e); }
        },
        fail: reject,
      });
    });
  },
};

// 管理端 API（request，Scanwork）
const adminApi = {
  checkToken() {
    return getAppRef().request({ url: '/scanwork/checkToken', method: 'GET' });
  },
  getDashboardData() {
    return getAppRef().request({ url: '/scanwork/getDashboardData', method: 'GET' });
  },
  getOrders(page, limit) {
    return getAppRef().request({ url: '/scanwork/getOrders', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getOrderDetail(id) {
    return getAppRef().request({ url: '/scanwork/getOrderDetail', method: 'GET', data: { order_id: id } });
  },
  getOrderModels(orderId) {
    return getAppRef().request({ url: '/scanwork/getOrderModels', method: 'GET', data: { order_id: orderId } });
  },
  createOrder(data) {
    return getAppRef().request({ url: '/scanwork/createOrder', method: 'POST', data });
  },
  updateOrder(data) {
    return getAppRef().request({ url: '/scanwork/updateOrder', method: 'POST', data });
  },
  deleteOrder(id) {
    return getAppRef().request({ url: '/scanwork/deleteOrder', method: 'POST', data: { id } });
  },
  getAllocations(page, limit, status, orderId) {
    const data = { page: page || 1, limit: limit || 20 };
    if (status !== undefined && status !== '') data.status = status;
    if (orderId) data.order_id = orderId;
    return getAppRef().request({ url: '/scanwork/getAllocations', method: 'GET', data });
  },
  getAllocationDetail(allocationId) {
    return getAppRef().request({ url: '/scanwork/getAllocationDetail', method: 'GET', data: { allocation_id: allocationId } });
  },
  createAllocation(data) {
    return getAppRef().request({ url: '/scanwork/createAllocation', method: 'POST', data });
  },
  batchCreateAllocation(data) {
    return getAppRef().request({ url: '/scanwork/batchCreateAllocation', method: 'POST', data });
  },
  updateAllocation(data) {
    return getAppRef().request({ url: '/scanwork/updateAllocation', method: 'POST', data });
  },
  deleteAllocation(id) {
    return getAppRef().request({ url: '/scanwork/deleteAllocation', method: 'POST', data: { id } });
  },
  generateQrcode(allocationId) {
    return getAppRef().request({ url: '/scanwork/generateQrcode', method: 'POST', data: { id: allocationId } });
  },
  getReports(page, limit, status) {
    const data = { page: page || 1, limit: limit || 20 };
    if (status !== undefined && status !== '') data.status = status;
    return getAppRef().request({ url: '/scanwork/getReports', method: 'GET', data });
  },
  getActiveReports(page, limit) {
    return getAppRef().request({ url: '/scanwork/getActiveReports', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getReportDetail(reportId) {
    return getAppRef().request({ url: '/scanwork/getReportDetail', method: 'GET', data: { report_id: reportId } });
  },
  auditReport(reportId, status, auditReason, auditNotes, qualityStatus, auditImages, auditVideos) {
    const data = { report_id: reportId, status, audit_reason: auditReason || '' };
    if (auditNotes != null) data.audit_notes = auditNotes;
    if (qualityStatus != null) data.quality_status = qualityStatus;
    if (Array.isArray(auditImages) && auditImages.length) data.audit_images = auditImages;
    if (Array.isArray(auditVideos) && auditVideos.length) data.audit_videos = auditVideos;
    return getAppRef().request({ url: '/scanwork/auditReport', method: 'POST', data });
  },
  uploadAuditImage(filePath) {
    const { BASE_URL } = require('./config.js');
    const token = wx.getStorageSync('adminToken') || '';
    return new Promise((resolve, reject) => {
      wx.uploadFile({
        url: BASE_URL + '/scanwork/uploadAuditImage',
        filePath,
        name: 'file',
        header: { Authorization: token ? 'Bearer ' + token : '' },
        success(res) {
          try {
            const data = JSON.parse(res.data);
            if (data.code === 1 && data.data && data.data.url) resolve(data.data.url);
            else reject(data);
          } catch (e) { reject(e); }
        },
        fail: reject,
      });
    });
  },
  uploadAuditVideo(filePath) {
    const { BASE_URL } = require('./config.js');
    const token = wx.getStorageSync('adminToken') || '';
    return new Promise((resolve, reject) => {
      wx.uploadFile({
        url: BASE_URL + '/scanwork/uploadAuditVideo',
        filePath,
        name: 'file',
        header: { Authorization: token ? 'Bearer ' + token : '' },
        success(res) {
          try {
            const data = JSON.parse(res.data);
            if (data.code === 1 && data.data && data.data.url) resolve(data.data.url);
            else reject(data);
          } catch (e) { reject(e); }
        },
        fail: reject,
      });
    });
  },
  deleteReport(idOrIds) {
    const data = Array.isArray(idOrIds) ? { ids: idOrIds.join(',') } : { ids: String(idOrIds) };
    return getAppRef().request({ url: '/scanwork/deleteReport', method: 'POST', data });
  },
  getReportStatistics(params) {
    return getAppRef().request({ url: '/scanwork/getReportStatistics', method: 'GET', data: params || {} });
  },
  getProducts(page, limit) {
    return getAppRef().request({ url: '/scanwork/getProducts', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  createProduct(data) {
    return getAppRef().request({ url: '/scanwork/createProduct', method: 'POST', data });
  },
  updateProduct(data) {
    return getAppRef().request({ url: '/scanwork/updateProduct', method: 'POST', data });
  },
  deleteProduct(id) {
    return getAppRef().request({ url: '/scanwork/deleteProduct', method: 'POST', data: { id } });
  },
  getModels(page, limit, productId) {
    const data = { page: page || 1, limit: limit || 20 };
    if (productId) data.product_id = productId;
    return getAppRef().request({ url: '/scanwork/getModels', method: 'GET', data });
  },
  createProductModel(data) {
    return getAppRef().request({ url: '/scanwork/createProductModel', method: 'POST', data });
  },
  updateProductModel(data) {
    return getAppRef().request({ url: '/scanwork/updateProductModel', method: 'POST', data });
  },
  deleteProductModel(id) {
    return getAppRef().request({ url: '/scanwork/deleteProductModel', method: 'POST', data: { id } });
  },
  /** 批量添加型号及工序工价，重复型号跳过。data: { product_id, models: [ { name, model_code?, description?, prices: [ { process_id, price, time_price? } ] } ] } */
  batchAddProductModels(data) {
    return getAppRef().request({ url: '/scanwork/batchAddProductModels', method: 'POST', data });
  },
  getProcesses(page, limit) {
    return getAppRef().request({ url: '/scanwork/getProcesses', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  createProcess(data) {
    return getAppRef().request({ url: '/scanwork/createProcess', method: 'POST', data });
  },
  updateProcess(data) {
    return getAppRef().request({ url: '/scanwork/updateProcess', method: 'POST', data });
  },
  deleteProcess(id) {
    return getAppRef().request({ url: '/scanwork/deleteProcess', method: 'POST', data: { id } });
  },
  getProcessPriceList(page, limit, modelId) {
    const data = { page: page || 1, limit: limit || 20 };
    if (modelId) data.model_id = modelId;
    return getAppRef().request({ url: '/scanwork/getProcessPriceList', method: 'GET', data });
  },
  createProcessPrice(data) {
    return getAppRef().request({ url: '/scanwork/createProcessPrice', method: 'POST', data });
  },
  updateProcessPrice(data) {
    return getAppRef().request({ url: '/scanwork/updateProcessPrice', method: 'POST', data });
  },
  deleteProcessPrice(id) {
    return getAppRef().request({ url: '/scanwork/deleteProcessPrice', method: 'POST', data: { id } });
  },
  batchProcessPrice(data) {
    return getAppRef().request({ url: '/scanwork/batchProcessPrice', method: 'POST', data });
  },
  getMaterialList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getMaterialList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getMaterialDetail(id) {
    return getAppRef().request({ url: '/scanwork/getMaterialDetail', method: 'GET', data: { id } });
  },
  createMaterial(data) {
    return getAppRef().request({ url: '/scanwork/createMaterial', method: 'POST', data });
  },
  updateMaterial(data) {
    return getAppRef().request({ url: '/scanwork/updateMaterial', method: 'POST', data });
  },
  deleteMaterial(id) {
    return getAppRef().request({ url: '/scanwork/deleteMaterial', method: 'POST', data: { id } });
  },
  getWarehouseList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getWarehouseList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getWarehouseDetail(id) {
    return getAppRef().request({ url: '/scanwork/getWarehouseDetail', method: 'GET', data: { id } });
  },
  createWarehouse(data) {
    return getAppRef().request({ url: '/scanwork/createWarehouse', method: 'POST', data });
  },
  updateWarehouse(data) {
    return getAppRef().request({ url: '/scanwork/updateWarehouse', method: 'POST', data });
  },
  deleteWarehouse(id) {
    return getAppRef().request({ url: '/scanwork/deleteWarehouse', method: 'POST', data: { id } });
  },
  getStockList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getStockList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getStockLog(page, limit, warehouseId) {
    const data = { page: page || 1, limit: limit || 20 };
    if (warehouseId) data.warehouse_id = warehouseId;
    return getAppRef().request({ url: '/scanwork/getStockLog', method: 'GET', data });
  },
  stockIn(data) {
    return getAppRef().request({ url: '/scanwork/stockIn', method: 'POST', data });
  },
  stockOut(data) {
    return getAppRef().request({ url: '/scanwork/stockOut', method: 'POST', data });
  },
  stockCheck(data) {
    return getAppRef().request({ url: '/scanwork/stockCheck', method: 'POST', data });
  },
  getBomList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getBomList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getBomDetail(id) {
    return getAppRef().request({ url: '/scanwork/getBomDetail', method: 'GET', data: { id } });
  },
  getBomItems(bomId) {
    return getAppRef().request({ url: '/scanwork/getBomItems', method: 'GET', data: { bom_id: bomId } });
  },
  createBom(data) {
    return getAppRef().request({ url: '/scanwork/createBom', method: 'POST', data });
  },
  updateBom(data) {
    return getAppRef().request({ url: '/scanwork/updateBom', method: 'POST', data });
  },
  deleteBom(id) {
    return getAppRef().request({ url: '/scanwork/deleteBom', method: 'POST', data: { id } });
  },
  addBomItem(data) {
    return getAppRef().request({ url: '/scanwork/addBomItem', method: 'POST', data });
  },
  updateBomItem(data) {
    return getAppRef().request({ url: '/scanwork/updateBomItem', method: 'POST', data });
  },
  deleteBomItem(id) {
    return getAppRef().request({ url: '/scanwork/deleteBomItem', method: 'POST', data: { id } });
  },
  approveBom(id, approve) {
    return getAppRef().request({ url: '/scanwork/approveBom', method: 'POST', data: { ids: String(id), approve: approve !== undefined ? approve : 1 } });
  },
  getProductionPlanList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getProductionPlanList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getProductionPlanDetail(id) {
    return getAppRef().request({ url: '/scanwork/getProductionPlanDetail', method: 'GET', data: { id } });
  },
  getProductionPlanAllocations(planId) {
    return getAppRef().request({ url: '/scanwork/getProductionPlanAllocations', method: 'GET', data: { plan_id: planId } });
  },
  getProductionPlanProgress(planId) {
    return getAppRef().request({ url: '/scanwork/getProductionPlanProgress', method: 'GET', data: { plan_id: planId } });
  },
  getProductionPlanProgressStats(planId) {
    return getAppRef().request({ url: '/scanwork/getProductionPlanProgressStats', method: 'GET', data: planId ? { plan_id: planId } : {} });
  },
  createProductionPlan(data) {
    return getAppRef().request({ url: '/scanwork/createProductionPlan', method: 'POST', data });
  },
  updateProductionPlan(data) {
    return getAppRef().request({ url: '/scanwork/updateProductionPlan', method: 'POST', data });
  },
  deleteProductionPlan(id) {
    return getAppRef().request({ url: '/scanwork/deleteProductionPlan', method: 'POST', data: { id } });
  },
  getPurchaseList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getPurchaseList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getPurchaseDetail(id) {
    return getAppRef().request({ url: '/scanwork/getPurchaseDetail', method: 'GET', data: { id } });
  },
  createPurchase(data) {
    return getAppRef().request({ url: '/scanwork/createPurchase', method: 'POST', data });
  },
  updatePurchase(data) {
    return getAppRef().request({ url: '/scanwork/updatePurchase', method: 'POST', data });
  },
  deletePurchase(id) {
    return getAppRef().request({ url: '/scanwork/deletePurchase', method: 'POST', data: { id } });
  },
  purchaseInbound(data) {
    return getAppRef().request({ url: '/scanwork/purchaseInbound', method: 'POST', data });
  },
  getShipmentList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getShipmentList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getShipmentDetail(id) {
    return getAppRef().request({ url: '/scanwork/getShipmentDetail', method: 'GET', data: { id } });
  },
  createShipment(data) {
    return getAppRef().request({ url: '/scanwork/createShipment', method: 'POST', data });
  },
  updateShipment(data) {
    return getAppRef().request({ url: '/scanwork/updateShipment', method: 'POST', data });
  },
  deleteShipment(id) {
    return getAppRef().request({ url: '/scanwork/deleteShipment', method: 'POST', data: { id } });
  },
  getQualityStandards(page, limit) {
    return getAppRef().request({ url: '/scanwork/getQualityStandards', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getQualityChecks(page, limit) {
    return getAppRef().request({ url: '/scanwork/getQualityChecks', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getWageList(page, limit, params) {
    const data = { page: page || 1, limit: limit || 20 };
    if (params && params.user_id !== undefined && params.user_id !== '') data.user_id = params.user_id;
    if (params && params.work_date) data.work_date = params.work_date;
    return getAppRef().request({ url: '/scanwork/getWageList', method: 'GET', data });
  },
  getWageStatistics(params) {
    return getAppRef().request({ url: '/scanwork/getWageStatistics', method: 'GET', data: params || {} });
  },
  getTraceCodeList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getTraceCodeList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  generateTraceCode(data) {
    return getAppRef().request({ url: '/scanwork/generateTraceCode', method: 'POST', data });
  },
  queryTraceCode(code) {
    return getAppRef().request({ url: '/scanwork/queryTraceCode', method: 'GET', data: { code } });
  },
  getAfterSalesList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getAfterSalesList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getAfterSalesDetail(id) {
    return getAppRef().request({ url: '/scanwork/getAfterSalesDetail', method: 'GET', data: { id } });
  },
  createAfterSales(data) {
    return getAppRef().request({ url: '/scanwork/createAfterSales', method: 'POST', data });
  },
  updateAfterSales(data) {
    return getAppRef().request({ url: '/scanwork/updateAfterSales', method: 'POST', data });
  },
  deleteAfterSales(id) {
    return getAppRef().request({ url: '/scanwork/deleteAfterSales', method: 'POST', data: { id } });
  },
  getUsers(page, limit) {
    return getAppRef().request({ url: '/scanwork/getUsers', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getCustomerList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getCustomerList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getCustomerDetail(id) {
    return getAppRef().request({ url: '/scanwork/getCustomerDetail', method: 'GET', data: { id } });
  },
  createCustomer(data) {
    return getAppRef().request({ url: '/scanwork/createCustomer', method: 'POST', data });
  },
  updateCustomer(data) {
    return getAppRef().request({ url: '/scanwork/updateCustomer', method: 'POST', data });
  },
  deleteCustomer(id) {
    return getAppRef().request({ url: '/scanwork/deleteCustomer', method: 'POST', data: { ids: String(id) } });
  },
  getSupplierList(page, limit) {
    return getAppRef().request({ url: '/scanwork/getSupplierList', method: 'GET', data: { page: page || 1, limit: limit || 20 } });
  },
  getSupplierDetail(id) {
    return getAppRef().request({ url: '/scanwork/getSupplierDetail', method: 'GET', data: { id } });
  },
  createSupplier(data) {
    return getAppRef().request({ url: '/scanwork/createSupplier', method: 'POST', data });
  },
  updateSupplier(data) {
    return getAppRef().request({ url: '/scanwork/updateSupplier', method: 'POST', data });
  },
  deleteSupplier(id) {
    return getAppRef().request({ url: '/scanwork/deleteSupplier', method: 'POST', data: { ids: String(id) } });
  },
  getBiProductionEfficiency(params) {
    return getAppRef().request({ url: '/scanwork/getBiProductionEfficiency', method: 'GET', data: params || {} });
  },
  getBiQualityAnalysis(params) {
    return getAppRef().request({ url: '/scanwork/getBiQualityAnalysis', method: 'GET', data: params || {} });
  },
  getBiCostAnalysis(params) {
    return getAppRef().request({ url: '/scanwork/getBiCostAnalysis', method: 'GET', data: params || {} });
  },
};

module.exports = {
  userApi,
  adminApi,
};
