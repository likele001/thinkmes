<?php
/**
 * 小程序 Scanwork 接口与 PC 端权限节点映射
 * 用于与 PC 角色/菜单权限一致：接口请求时校验管理员是否拥有对应节点
 * 节点格式与 fa_auth_rule.name 一致（如 mes/order、mes/report），Auth::check 支持父级匹配
 */
return [
    // 无需节点校验（仅登录即可）
    'checkToken' => null,
    'getScanworkMenu' => null,

    // 订单
    'getOrders' => 'mes/order',
    'getOrderDetail' => 'mes/order',
    'getOrderModels' => 'mes/order',
    'getOrderMaterialList' => 'mes/order',
    'createOrder' => 'mes/order',
    'updateOrder' => 'mes/order',
    'deleteOrder' => 'mes/order',

    // 分工/任务
    'getAllocations' => 'mes/allocation',
    'getAllocationDetail' => 'mes/allocation',
    'getTaskByScan' => 'mes/allocation',
    'createAllocation' => 'mes/allocation',
    'updateAllocation' => 'mes/allocation',
    'deleteAllocation' => 'mes/allocation',
    'batchCreateAllocation' => 'mes/allocation',
    'generateQrcode' => 'mes/allocation_qrcode',

    // 报工
    'getReports' => 'mes/report',
    'getActiveReports' => 'mes/report',
    'getReportDetail' => 'mes/report',
    'getReportStatistics' => 'mes/report',
    'auditReport' => 'mes/report',
    'uploadAuditImage' => 'mes/report',
    'uploadAuditVideo' => 'mes/report',
    'uploadReportImage' => 'mes/report',
    'deleteReport' => 'mes/report',

    // 产品/型号/工序
    'getProducts' => 'mes/product',
    'getModels' => 'mes/product',
    'createProduct' => 'mes/product',
    'updateProduct' => 'mes/product',
    'deleteProduct' => 'mes/product',
    'createProductModel' => 'mes/product',
    'updateProductModel' => 'mes/product',
    'deleteProductModel' => 'mes/product',
    'batchAddProductModels' => 'mes/product',
    'getProcesses' => 'mes/process',
    'getProcessPriceList' => 'mes/process',
    'createProcess' => 'mes/process',
    'updateProcess' => 'mes/process',
    'deleteProcess' => 'mes/process',
    'createProcessPrice' => 'mes/process_price',
    'updateProcessPrice' => 'mes/process_price',
    'deleteProcessPrice' => 'mes/process_price',
    'batchProcessPrice' => 'mes/process_price',

    // 员工（PC 端为 成员/用户 管理，对应 admin/member）
    'getUsers' => 'mes/allocation',

    // 客户
    'getCustomerList' => 'mes/customer',
    'getCustomerDetail' => 'mes/customer',
    'createCustomer' => 'mes/customer',
    'updateCustomer' => 'mes/customer',
    'deleteCustomer' => 'mes/customer',

    // 供应商
    'getSupplierList' => 'mes/supplier',
    'getSupplierDetail' => 'mes/supplier',
    'createSupplier' => 'mes/supplier',
    'updateSupplier' => 'mes/supplier',
    'deleteSupplier' => 'mes/supplier',

    // 物料
    'getMaterialList' => 'mes/material',
    'getMaterialDetail' => 'mes/material',
    'createMaterial' => 'mes/material',
    'updateMaterial' => 'mes/material',
    'deleteMaterial' => 'mes/material',

    // 仓库
    'getWarehouseList' => 'mes/warehouse',
    'getWarehouseDetail' => 'mes/warehouse',
    'createWarehouse' => 'mes/warehouse',
    'updateWarehouse' => 'mes/warehouse',
    'deleteWarehouse' => 'mes/warehouse',

    // 库存
    'getStockList' => 'mes/stock',
    'getStockLog' => 'mes/stock',
    'stockIn' => 'mes/stock',
    'stockOut' => 'mes/stock',
    'stockCheck' => 'mes/stock',

    // BOM
    'getBomList' => 'mes/bom',
    'getBomDetail' => 'mes/bom',
    'getBomItems' => 'mes/bom',
    'createBom' => 'mes/bom',
    'updateBom' => 'mes/bom',
    'deleteBom' => 'mes/bom',
    'addBomItem' => 'mes/bom',
    'updateBomItem' => 'mes/bom',
    'deleteBomItem' => 'mes/bom',
    'approveBom' => 'mes/bom',

    // 生产计划
    'getProductionPlanList' => 'mes/production_plan',
    'getProductionPlanDetail' => 'mes/production_plan',
    'getProductionPlanAllocations' => 'mes/production_plan',
    'getProductionPlanProgress' => 'mes/production_plan',
    'getProductionPlanProgressStats' => 'mes/production_plan',
    'createProductionPlan' => 'mes/production_plan',
    'updateProductionPlan' => 'mes/production_plan',
    'deleteProductionPlan' => 'mes/production_plan',

    // 采购
    'getPurchaseRequestList' => 'mes/purchase',
    'getPurchaseList' => 'mes/purchase',
    'getPurchaseDetail' => 'mes/purchase',
    'createPurchase' => 'mes/purchase',
    'updatePurchase' => 'mes/purchase',
    'deletePurchase' => 'mes/purchase',
    'purchaseInbound' => 'mes/purchase',

    // 发货
    'getShipmentList' => 'mes/shipment',
    'getShipmentDetail' => 'mes/shipment',
    'createShipment' => 'mes/shipment',
    'updateShipment' => 'mes/shipment',
    'deleteShipment' => 'mes/shipment',

    // 质检
    'getQualityStandards' => 'mes/quality',
    'getQualityChecks' => 'mes/quality',

    // 工资
    'getWageList' => 'mes/wage',
    'getWageStatistics' => 'mes/wage',

    // 追溯码
    'getTraceCodeList' => 'mes/trace_code',
    'generateTraceCode' => 'mes/trace_code',
    'queryTraceCode' => 'mes/trace_code',

    // 售后
    'getAfterSalesList' => 'mes/after_sales',
    'getAfterSalesDetail' => 'mes/after_sales',
    'createAfterSales' => 'mes/after_sales',
    'updateAfterSales' => 'mes/after_sales',
    'deleteAfterSales' => 'mes/after_sales',

    // 仪表盘 / BI
    'getDashboardData' => 'mes/bi',
    'getBiProductionEfficiency' => 'mes/bi',
    'getBiQualityAnalysis' => 'mes/bi',
    'getBiCostAnalysis' => 'mes/bi',
];
