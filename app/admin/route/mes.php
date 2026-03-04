<?php
/**
 * MES 制造执行系统 - 独立路由（应用包安装时合并到此文件）
 */
use think\facade\Route;

Route::group('mes', function () {
    Route::get('after_sales/add', 'mes.AfterSales/add');
    Route::get('after_sales/edit', 'mes.AfterSales/edit');
    Route::get('after_sales/index', 'mes.AfterSales/index');
    Route::get('after_sales', 'mes.AfterSales/index');
    Route::post('after_sales/add', 'mes.AfterSales/add');
    Route::post('after_sales/edit', 'mes.AfterSales/edit');
    Route::post('after_sales/del', 'mes.AfterSales/del');

    Route::get('product_model/add', 'mes.ProductModel/add');
    Route::get('product_model/edit', 'mes.ProductModel/edit');
    Route::get('product_model/index', 'mes.ProductModel/index');
    Route::get('product_model', 'mes.ProductModel/index');
    Route::post('product_model/add', 'mes.ProductModel/add');
    Route::post('product_model/edit', 'mes.ProductModel/edit');
    Route::post('product_model/del', 'mes.ProductModel/del');

    Route::get('process_price/add', 'mes.ProcessPrice/add');
    Route::get('process_price/edit', 'mes.ProcessPrice/edit');
    Route::get('process_price/batch', 'mes.ProcessPrice/batch');
    Route::get('process_price/index', 'mes.ProcessPrice/index');
    Route::get('process_price', 'mes.ProcessPrice/index');
    Route::post('process_price/add', 'mes.ProcessPrice/add');
    Route::post('process_price/edit', 'mes.ProcessPrice/edit');
    Route::post('process_price/del', 'mes.ProcessPrice/del');
    Route::post('process_price/batch', 'mes.ProcessPrice/batch');

    Route::get('process_route/add', 'mes.ProcessRoute/add');
    Route::get('process_route/edit', 'mes.ProcessRoute/edit');
    Route::get('process_route/index', 'mes.ProcessRoute/index');
    Route::get('process_route', 'mes.ProcessRoute/index');
    Route::post('process_route/add', 'mes.ProcessRoute/add');
    Route::post('process_route/edit', 'mes.ProcessRoute/edit');
    Route::post('process_route/del', 'mes.ProcessRoute/del');

    Route::get('production_plan/add', 'mes.ProductionPlan/add');
    Route::get('production_plan/edit', 'mes.ProductionPlan/edit');
    Route::get('production_plan/getOrderModels', 'mes.ProductionPlan/getOrderModels');
    Route::get('production_plan/allocations', 'mes.ProductionPlan/allocations');
    Route::get('production_plan/progressStats', 'mes.ProductionPlan/progressStats');
    Route::get('production_plan/progress', 'mes.ProductionPlan/progress');
    Route::get('production_plan/getOrderDetails', 'mes.ProductionPlan/getOrderDetails');
    Route::get('production_plan/getProductDetails', 'mes.ProductionPlan/getProductDetails');
    Route::get('production_plan/getProcessDetails', 'mes.ProductionPlan/getProcessDetails');
    Route::get('production_plan/getEmployeeDetails', 'mes.ProductionPlan/getEmployeeDetails');
    Route::get('production_plan/index', 'mes.ProductionPlan/index');
    Route::get('production_plan', 'mes.ProductionPlan/index');
    Route::post('production_plan/add', 'mes.ProductionPlan/add');
    Route::post('production_plan/edit', 'mes.ProductionPlan/edit');
    Route::post('production_plan/del', 'mes.ProductionPlan/del');

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

    Route::get('order/add', 'mes.Order/add');
    Route::get('order/edit', 'mes.Order/edit');
    Route::get('order/materialList', 'mes.Order/materialList');
    Route::get('order/index', 'mes.Order/index');
    Route::get('order', 'mes.Order/index');
    Route::post('order/add', 'mes.Order/add');
    Route::post('order/edit', 'mes.Order/edit');
    Route::post('order/del', 'mes.Order/del');

    Route::get('product/add', 'mes.Product/add');
    Route::get('product/edit', 'mes.Product/edit');
    Route::get('product/index', 'mes.Product/index');
    Route::get('product', 'mes.Product/index');
    Route::post('product/add', 'mes.Product/add');
    Route::post('product/edit', 'mes.Product/edit');
    Route::post('product/del', 'mes.Product/del');

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

    Route::get('report/add', 'mes.Report/add');
    Route::get('report/edit', 'mes.Report/edit');
    Route::get('report/audit_page', 'mes.Report/audit_page');
    Route::get('report/detail', 'mes.Report/detail');
    Route::get('report/index', 'mes.Report/index');
    Route::get('report', 'mes.Report/index');
    Route::post('report/add', 'mes.Report/add');
    Route::post('report/edit', 'mes.Report/edit');
    Route::post('report/del', 'mes.Report/del');
    Route::post('report/audit', 'mes.Report/audit');

    // MES 下的 AI 接口代理（受租户购买与全局开关限制）
    Route::post('report/ai/transcribe', 'ai.VoiceReport/transcribe')
        ->middleware(\app\common\middleware\AICheck::class)
        ->middleware(\app\common\middleware\AIBilling::class);
    Route::post('report/ai/parse', 'ai.VoiceReport/parse')
        ->middleware(\app\common\middleware\AICheck::class)
        ->middleware(\app\common\middleware\AIBilling::class);
    Route::post('report/ai/anomaly', 'ai.Anomaly/scan')
        ->middleware(\app\common\middleware\AICheck::class)
        ->middleware(\app\common\middleware\AIBilling::class);
    // 老板问答放到 mes/qa 下，便于 MES 菜单调用
    Route::post('qa/ask', 'ai.Qa/ask')
        ->middleware(\app\common\middleware\AICheck::class)
        ->middleware(\app\common\middleware\AIBilling::class);

    Route::get('customer/add', 'mes.Customer/add');
    Route::get('customer/edit', 'mes.Customer/edit');
    Route::get('customer/index', 'mes.Customer/index');
    Route::get('customer', 'mes.Customer/index');
    Route::post('customer/add', 'mes.Customer/add');
    Route::post('customer/edit', 'mes.Customer/edit');
    Route::post('customer/del', 'mes.Customer/del');

    Route::get('customer_product/add', 'mes.CustomerProduct/add');
    Route::get('customer_product/edit', 'mes.CustomerProduct/edit');
    Route::get('customer_product/index', 'mes.CustomerProduct/index');
    Route::get('customer_product', 'mes.CustomerProduct/index');
    Route::post('customer_product/add', 'mes.CustomerProduct/add');
    Route::post('customer_product/edit', 'mes.CustomerProduct/edit');
    Route::post('customer_product/del', 'mes.CustomerProduct/del');

    Route::get('process/add', 'mes.Process/add');
    Route::get('process/edit', 'mes.Process/edit');
    Route::get('process/index', 'mes.Process/index');
    Route::get('process', 'mes.Process/index');
    Route::post('process/add', 'mes.Process/add');
    Route::post('process/edit', 'mes.Process/edit');
    Route::post('process/del', 'mes.Process/del');

    Route::get('material/add', 'mes.Material/add');
    Route::get('material/edit', 'mes.Material/edit');
    Route::get('material/index', 'mes.Material/index');
    Route::get('material', 'mes.Material/index');
    Route::post('material/add', 'mes.Material/add');
    Route::post('material/edit', 'mes.Material/edit');
    Route::post('material/del', 'mes.Material/del');

    Route::get('supplier/add', 'mes.Supplier/add');
    Route::get('supplier/edit', 'mes.Supplier/edit');
    Route::get('supplier/index', 'mes.Supplier/index');
    Route::get('supplier', 'mes.Supplier/index');
    Route::post('supplier/add', 'mes.Supplier/add');
    Route::post('supplier/edit', 'mes.Supplier/edit');
    Route::post('supplier/del', 'mes.Supplier/del');

    Route::get('stock/index', 'mes.Stock/index');
    Route::get('stock/check', 'mes.Stock/check');
    Route::get('stock/log', 'mes.Stock/log');
    Route::get('stock/outbound', 'mes.Stock/outbound');
    Route::get('stock', 'mes.Stock/index');
    Route::post('stock/in', 'mes.Stock/in');
    Route::post('stock/out', 'mes.Stock/out');
    Route::post('stock/del', 'mes.Stock/del');
    Route::post('stock/check', 'mes.Stock/check');
    Route::post('stock/outbound', 'mes.Stock/outbound');

    Route::get('warehouse/add', 'mes.Warehouse/add');
    Route::get('warehouse/edit', 'mes.Warehouse/edit');
    Route::get('warehouse/index', 'mes.Warehouse/index');
    Route::get('warehouse', 'mes.Warehouse/index');
    Route::post('warehouse/add', 'mes.Warehouse/add');
    Route::post('warehouse/edit', 'mes.Warehouse/edit');
    Route::post('warehouse/del', 'mes.Warehouse/del');

    Route::get('purchase/index', 'mes.Purchase/index');
    Route::get('purchase/inbound', 'mes.Purchase/inbound');
    Route::get('purchase/request', 'mes.Purchase/requestList');
    Route::get('purchase', 'mes.Purchase/index');
    Route::post('purchase/add', 'mes.Purchase/add');
    Route::post('purchase/edit', 'mes.Purchase/edit');
    Route::post('purchase/del', 'mes.Purchase/del');

    Route::get('quality/index', 'mes.Quality/index');
    Route::get('quality/statistics', 'mes.Quality/statistics');
    Route::get('quality/check', 'mes.Quality/check');
    Route::get('quality/standard', 'mes.Quality/standard');
    Route::get('quality/addStandard', 'mes.Quality/addStandard');
    Route::get('quality/addCheck', 'mes.Quality/addCheck');
    Route::get('quality', 'mes.Quality/index');
    Route::post('quality/add', 'mes.Quality/add');
    Route::post('quality/edit', 'mes.Quality/edit');
    Route::post('quality/del', 'mes.Quality/del');
    Route::post('quality/addStandard', 'mes.Quality/addStandard');
    Route::post('quality/addCheck', 'mes.Quality/addCheck');

    Route::get('wage', 'mes.Wage/index');
    Route::get('wage/index', 'mes.Wage/index');
    Route::get('wage/statistics', 'mes.Wage/statistics');
    Route::get('wage/export', 'mes.Wage/export');

    Route::get('trace_code', 'mes.TraceCode/index');
    Route::get('trace_code/index', 'mes.TraceCode/index');
    Route::post('trace_code/generate', 'mes.TraceCode/generate');
    Route::post('trace_code/batchGenerate', 'mes.TraceCode/batchGenerate');
    Route::get('trace_code/query', 'mes.TraceCode/query');
    Route::post('trace_code/del', 'mes.TraceCode/del');

    Route::get('bi/dashboard', 'mes.Bi/dashboard');
    Route::get('bi/getDashboardData', 'mes.Bi/getDashboardData');
    Route::get('bi/productionEfficiency', 'mes.Bi/productionEfficiency');
    Route::get('bi/qualityAnalysis', 'mes.Bi/qualityAnalysis');
    Route::get('bi/costAnalysis', 'mes.Bi/costAnalysis');

    Route::get('shipment/add', 'mes.Shipment/add');
    Route::get('shipment/edit', 'mes.Shipment/edit');
    Route::get('shipment/track', 'mes.Shipment/track');
    Route::get('shipment/index', 'mes.Shipment/index');
    Route::get('shipment', 'mes.Shipment/index');
    Route::post('shipment/add', 'mes.Shipment/add');
    Route::post('shipment/edit', 'mes.Shipment/edit');
    Route::post('shipment/del', 'mes.Shipment/del');

    Route::get('index', 'mes.Mes/index');
});
Route::get('mes', 'mes.Mes/index');
