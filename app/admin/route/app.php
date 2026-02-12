<?php
use think\facade\Route;

// 后台登录、登出、验证码、无权限页、首页
Route::get('index/login', 'Index/login');
Route::post('index/login', 'Index/login');
Route::get('index/logout', 'Index/logout');
Route::post('index/logout', 'Index/logout');
Route::get('index/captcha', 'Index/captcha');
Route::get('index/error', 'Index/errorPage');
Route::get('index/index', 'Index/index');
Route::get('index/menu', 'Index/menu');

// 管理员
Route::get('admin/index', 'Admin/index');
Route::get('admin/add', 'Admin/add');
Route::get('admin/edit', 'Admin/edit');
Route::post('admin/add', 'Admin/addPost');
Route::post('admin/edit', 'Admin/editPost');
Route::post('admin/del', 'Admin/del');
Route::post('admin/resetPwd', 'Admin/resetPwd');

// 租户管理（仅平台超管）
Route::get('tenant/index', 'Tenant/index');
Route::get('tenant/add', 'Tenant/add');
Route::get('tenant/edit', 'Tenant/edit');
Route::post('tenant/add', 'Tenant/addPost');
Route::post('tenant/edit', 'Tenant/editPost');
Route::post('tenant/del', 'Tenant/del');

// 租户套餐管理（仅平台超管）
Route::get('tenant_package/index', 'TenantPackage/index');
Route::get('tenant_package/add', 'TenantPackage/add');
Route::get('tenant_package/edit', 'TenantPackage/edit');
Route::post('tenant_package/add', 'TenantPackage/addPost');
Route::post('tenant_package/edit', 'TenantPackage/editPost');
Route::post('tenant_package/del', 'TenantPackage/del');

// 套餐功能管理（仅平台超管）
Route::get('tenant_package_feature/index', 'TenantPackageFeature/index');
Route::get('tenant_package_feature/add', 'TenantPackageFeature/add');
Route::post('tenant_package_feature/add', 'TenantPackageFeature/addPost');
Route::post('tenant_package_feature/del', 'TenantPackageFeature/del');

// 租户订单管理（仅平台超管）
Route::get('tenant_order/index', 'TenantOrder/index');
Route::get('tenant_order/add', 'TenantOrder/add');
Route::post('tenant_order/add', 'TenantOrder/addPost');
Route::post('tenant_order/pay', 'TenantOrder/pay');
Route::post('tenant_order/cancel', 'TenantOrder/cancel');

// 用户管理（C端）
Route::get('member/index', 'Member/index');
Route::get('member/add', 'Member/add');
Route::get('member/edit', 'Member/edit');
Route::post('member/add', 'Member/addPost');
Route::post('member/edit', 'Member/editPost');
Route::post('member/del', 'Member/del');
Route::post('member/resetPwd', 'Member/resetPwd');

// 文件管理
Route::get('attachment/index', 'Attachment/index');
Route::post('attachment/del', 'Attachment/del');

// 角色
Route::get('role/index', 'Role/index');
Route::get('role/add', 'Role/add');
Route::get('role/edit', 'Role/edit');
Route::post('role/add', 'Role/addPost');
Route::post('role/edit', 'Role/editPost');
Route::post('role/del', 'Role/del');

// 权限规则
Route::get('auth_rule/index', 'AuthRule/index');
Route::get('auth_rule/add', 'AuthRule/add');
Route::get('auth_rule/edit', 'AuthRule/edit');
Route::post('auth_rule/add', 'AuthRule/addPost');
Route::post('auth_rule/edit', 'AuthRule/editPost');
Route::post('auth_rule/del', 'AuthRule/del');
Route::get('auth_rule/tree', 'AuthRule/tree');

// 系统配置
Route::get('config/index', 'Config/index');
Route::get('config/group', 'Config/group');
Route::post('config/save', 'Config/save');

// 操作日志
Route::get('log/index', 'Log/index');
Route::get('log/export', 'Log/export');

// 上传
Route::post('common/upload', 'Common/upload');
Route::post('common/uploadChunk', 'Common/uploadChunk');
Route::post('common/mergeChunks', 'Common/mergeChunks');

// 缓存清理（后台入口）
Route::post('index/clearCache', 'Index/clearCache');

// MES 制造执行系统
// 使用路由分组，更符合 FastAdmin 风格，所有 mes/* 路由都在分组内
Route::group('mes', function () {
    // 产品型号管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('product_model/add', 'mes.ProductModel/add');
    Route::get('product_model/edit', 'mes.ProductModel/edit');
    Route::get('product_model/index', 'mes.ProductModel/index');
    Route::get('product_model', 'mes.ProductModel/index');
    Route::post('product_model/add', 'mes.ProductModel/add');
    Route::post('product_model/edit', 'mes.ProductModel/edit');
    Route::post('product_model/del', 'mes.ProductModel/del');

    // 工序工价管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('process_price/add', 'mes.ProcessPrice/add');
    Route::get('process_price/edit', 'mes.ProcessPrice/edit');
    Route::get('process_price/batch', 'mes.ProcessPrice/batch');
    Route::get('process_price/index', 'mes.ProcessPrice/index');
    Route::get('process_price', 'mes.ProcessPrice/index');
    Route::post('process_price/add', 'mes.ProcessPrice/add');
    Route::post('process_price/edit', 'mes.ProcessPrice/edit');
    Route::post('process_price/del', 'mes.ProcessPrice/del');
    Route::post('process_price/batch', 'mes.ProcessPrice/batch');

    // 生产计划管理（先定义具体路由，避免被通用路由匹配）
    Route::get('production_plan/add', 'mes.ProductionPlan/add');
    Route::get('production_plan/edit', 'mes.ProductionPlan/edit');
    Route::get('production_plan/getOrderModels', 'mes.ProductionPlan/getOrderModels');
    Route::get('production_plan/index', 'mes.ProductionPlan/index');
    Route::get('production_plan', 'mes.ProductionPlan/index');
    Route::post('production_plan/add', 'mes.ProductionPlan/add');
    Route::post('production_plan/edit', 'mes.ProductionPlan/edit');
    Route::post('production_plan/del', 'mes.ProductionPlan/del');

    // 分工分配管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('allocation/add', 'mes.Allocation/add');
    Route::get('allocation/edit', 'mes.Allocation/edit');
    Route::get('allocation/batch', 'mes.Allocation/batch');
    Route::get('allocation/getOrderModels', 'mes.Allocation/getOrderModels');
    Route::get('allocation/index', 'mes.Allocation/index');
    Route::get('allocation', 'mes.Allocation/index');
    Route::post('allocation/add', 'mes.Allocation/add');
    Route::post('allocation/edit', 'mes.Allocation/edit');
    Route::post('allocation/del', 'mes.Allocation/del');
    Route::post('allocation/batch', 'mes.Allocation/batch');
    Route::post('allocation/generateQrcode', 'mes.Allocation/generateQrcode');

    // 订单管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('order/add', 'mes.Order/add');
    Route::get('order/edit', 'mes.Order/edit');
    Route::get('order/materialList', 'mes.Order/materialList');
    Route::get('order/index', 'mes.Order/index');
    Route::get('order', 'mes.Order/index');
    Route::post('order/add', 'mes.Order/add');
    Route::post('order/edit', 'mes.Order/edit');
    Route::post('order/del', 'mes.Order/del');

    // 产品管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('product/add', 'mes.Product/add');
    Route::get('product/edit', 'mes.Product/edit');
    Route::get('product/index', 'mes.Product/index');
    Route::get('product', 'mes.Product/index');
    Route::post('product/add', 'mes.Product/add');
    Route::post('product/edit', 'mes.Product/edit');
    Route::post('product/del', 'mes.Product/del');

    // BOM管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('bom/add', 'mes.Bom/add');
    Route::get('bom/edit', 'mes.Bom/edit');
    Route::get('bom/items', 'mes.Bom/items');
    Route::get('bom/index', 'mes.Bom/index');
    Route::get('bom', 'mes.Bom/index');
    Route::post('bom/add', 'mes.Bom/add');
    Route::post('bom/edit', 'mes.Bom/edit');
    Route::post('bom/del', 'mes.Bom/del');
    Route::post('bom/addItem', 'mes.Bom/addItem');
    Route::post('bom/updateItem', 'mes.Bom/updateItem');
    Route::post('bom/deleteItem', 'mes.Bom/deleteItem');
    Route::post('bom/approve', 'mes.Bom/approve');

    // 报工管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('report/add', 'mes.Report/add');
    Route::get('report/edit', 'mes.Report/edit');
    Route::get('report/audit_page', 'mes.Report/audit_page');
    Route::get('report/index', 'mes.Report/index');
    Route::get('report', 'mes.Report/index');
    Route::post('report/add', 'mes.Report/add');
    Route::post('report/edit', 'mes.Report/edit');
    Route::post('report/del', 'mes.Report/del');
    Route::post('report/audit', 'mes.Report/audit');

    // 客户管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('customer/add', 'mes.Customer/add');
    Route::get('customer/edit', 'mes.Customer/edit');
    Route::get('customer/index', 'mes.Customer/index');
    Route::get('customer', 'mes.Customer/index');
    Route::post('customer/add', 'mes.Customer/add');
    Route::post('customer/edit', 'mes.Customer/edit');
    Route::post('customer/del', 'mes.Customer/del');

    // 工序管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('process/add', 'mes.Process/add');
    Route::get('process/edit', 'mes.Process/edit');
    Route::get('process/index', 'mes.Process/index');
    Route::get('process', 'mes.Process/index');
    Route::post('process/add', 'mes.Process/add');
    Route::post('process/edit', 'mes.Process/edit');
    Route::post('process/del', 'mes.Process/del');

    // 物料管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('material/add', 'mes.Material/add');
    Route::get('material/edit', 'mes.Material/edit');
    Route::get('material/index', 'mes.Material/index');
    Route::get('material', 'mes.Material/index');
    Route::post('material/add', 'mes.Material/add');
    Route::post('material/edit', 'mes.Material/edit');
    Route::post('material/del', 'mes.Material/del');

    // 供应商管理（具体路由放在前面，避免被通用路由匹配）
    Route::get('supplier/add', 'mes.Supplier/add');
    Route::get('supplier/edit', 'mes.Supplier/edit');
    Route::get('supplier/index', 'mes.Supplier/index');
    Route::get('supplier', 'mes.Supplier/index');
    Route::post('supplier/add', 'mes.Supplier/add');
    Route::post('supplier/edit', 'mes.Supplier/edit');
    Route::post('supplier/del', 'mes.Supplier/del');

    // 库存管理
    Route::get('stock/index', 'mes.Stock/index');
    Route::get('stock', 'mes.Stock/index');
    Route::get('stock/check', 'mes.Stock/check');
    Route::get('stock/log', 'mes.Stock/log');
    Route::post('stock/in', 'mes.Stock/in');
    Route::post('stock/out', 'mes.Stock/out');
    Route::post('stock/del', 'mes.Stock/del');

    // 仓库管理
    Route::get('warehouse/add', 'mes.Warehouse/add');
    Route::get('warehouse/edit', 'mes.Warehouse/edit');
    Route::get('warehouse/index', 'mes.Warehouse/index');
    Route::get('warehouse', 'mes.Warehouse/index');
    Route::post('warehouse/add', 'mes.Warehouse/add');
    Route::post('warehouse/edit', 'mes.Warehouse/edit');
    Route::post('warehouse/del', 'mes.Warehouse/del');

    // 采购管理
    Route::get('purchase/index', 'mes.Purchase/index');
    Route::get('purchase/inbound', 'mes.Purchase/inbound');
    Route::get('purchase/request', 'mes.Purchase/requestList');
    Route::get('purchase', 'mes.Purchase/index');
    Route::post('purchase/add', 'mes.Purchase/add');
    Route::post('purchase/edit', 'mes.Purchase/edit');
    Route::post('purchase/del', 'mes.Purchase/del');

    // 质检管理
    Route::get('quality/index', 'mes.Quality/index');
    Route::get('quality/statistics', 'mes.Quality/statistics');
    Route::get('quality/check', 'mes.Quality/check');
    Route::get('quality/standard', 'mes.Quality/standard');
    Route::get('quality', 'mes.Quality/index');
    Route::post('quality/add', 'mes.Quality/add');
    Route::post('quality/edit', 'mes.Quality/edit');
    Route::post('quality/del', 'mes.Quality/del');

    // 工资管理
    Route::get('wage', 'mes.Wage/index');
    Route::get('wage/index', 'mes.Wage/index');
    Route::get('wage/statistics', 'mes.Wage/statistics');
    Route::get('wage/export', 'mes.Wage/export');

    // 追溯码管理
    Route::get('trace_code', 'mes.TraceCode/index');
    Route::get('trace_code/index', 'mes.TraceCode/index');
    Route::post('trace_code/generate', 'mes.TraceCode/generate');
    Route::post('trace_code/batchGenerate', 'mes.TraceCode/batchGenerate');
    Route::get('trace_code/query', 'mes.TraceCode/query');
    Route::post('trace_code/del', 'mes.TraceCode/del');

    // BI报表和数据大屏
    Route::get('bi/dashboard', 'mes.Bi/dashboard');
    Route::get('bi/getDashboardData', 'mes.Bi/getDashboardData');
    Route::get('bi/productionEfficiency', 'mes.Bi/productionEfficiency');
    Route::get('bi/qualityAnalysis', 'mes.Bi/qualityAnalysis');
    Route::get('bi/costAnalysis', 'mes.Bi/costAnalysis');

    // 发货管理
    Route::get('shipment/add', 'mes.Shipment/add');
    Route::get('shipment/edit', 'mes.Shipment/edit');
    Route::get('shipment/track', 'mes.Shipment/track');
    Route::get('shipment/index', 'mes.Shipment/index');
    Route::get('shipment', 'mes.Shipment/index');
    Route::post('shipment/add', 'mes.Shipment/add');
    Route::post('shipment/edit', 'mes.Shipment/edit');
    Route::post('shipment/del', 'mes.Shipment/del');

    // MES 首页（放在最后，避免匹配其他mes路由）
    Route::get('index', 'mes.Mes/index');
});

// MES 根路径
Route::get('mes', 'mes.Mes/index');
