<?php
use think\facade\Route;

Route::get('index/index', 'Index/index');
Route::get('doc', 'Doc/index');
Route::get('doc/index', 'Doc/index');
Route::get('doc/spec', 'Doc/spec');

// 应用商店：公开浏览 + 登录后下单/发布
Route::get('store/plugins', 'Store/plugins');
Route::get('store/detail', 'Store/detail');
Route::get('store/payment_methods', 'Store/paymentMethods');
Route::post('store/publish', 'Store/publish')->middleware(\app\api\middleware\DeveloperAuth::class);
Route::post('store/upload', 'Store/upload')->middleware(\app\api\middleware\DeveloperAuth::class);
Route::get('store/my_plugins', 'Store/myPlugins')->middleware(\app\api\middleware\DeveloperAuth::class);
Route::get('store/my_orders', 'Store/myOrders')->middleware(\app\api\middleware\UserAuth::class);
Route::post('store/order/create', 'Store/createOrder')->middleware(\app\api\middleware\UserAuth::class);
Route::post('store/pay', 'Store/pay')->middleware(\app\api\middleware\UserAuth::class);
Route::get('store/order/status', 'Store/orderStatus')->middleware(\app\api\middleware\UserAuth::class);
Route::get('store/download', 'Store/download')->middleware(\app\api\middleware\UserAuth::class);

// 开发者中心（独立账号体系）
Route::post('developer/register', 'Developer/register');
Route::post('developer/login', 'Developer/login');
Route::get('developer/profile', 'Developer/profile')->middleware(\app\api\middleware\DeveloperAuth::class);
Route::post('developer/logout', 'Developer/logout')->middleware(\app\api\middleware\DeveloperAuth::class);

// C端用户：无需登录
Route::post('user/register', 'User/register');
Route::post('user/login', 'User/login');
Route::post('user/forgot', 'User/forgot');
Route::post('user/resetPassword', 'User/resetPassword');
Route::get('user/captcha', 'User/captcha');
Route::get('user/captchaMode', 'User/captchaMode');

// 小程序：根据 AppID 获取租户配置（无需登录）
Route::get('miniapp/getConfig', 'Miniapp/getConfig');
Route::post('miniapp/getConfig', 'Miniapp/getConfig');
// 小程序登录（无需登录，按租户隔离）
Route::post('miniapp/login', 'Miniapp/login');
// 小程序绑定已有员工（无需登录）
Route::post('miniapp/bindWithEmployee', 'Miniapp/bindWithEmployee');

// C端用户：需登录
Route::get('user/profile', 'User/profile')->middleware(\app\api\middleware\UserAuth::class);
Route::post('user/profile', 'User/profile')->middleware(\app\api\middleware\UserAuth::class);
Route::post('user/updateProfile', 'User/updateProfile')->middleware(\app\api\middleware\UserAuth::class);
Route::post('user/changePassword', 'User/changePassword')->middleware(\app\api\middleware\UserAuth::class);
Route::get('user/logout', 'User/logout')->middleware(\app\api\middleware\UserAuth::class);
Route::post('user/logout', 'User/logout')->middleware(\app\api\middleware\UserAuth::class);

// 支付：异步通知（第三方回调，无需登录）
Route::post('payment/notify/:gateway_id', 'Payment/notify');
Route::any('payment/notify/:gateway_id', 'Payment/notify');
// 支付：创建订单（内部或后台调用）
Route::post('payment/create', 'Payment/create');

// 客户门户：无需登录
Route::post('customer/login', 'Customer/login');

// 客户门户：需登录
Route::get('customer/profile', 'Customer/profile')->middleware(\app\api\middleware\CustomerAuth::class);
Route::get('customer/products', 'Customer/products')->middleware(\app\api\middleware\CustomerAuth::class);
Route::post('customer/createOrder', 'Customer/createOrder')->middleware(\app\api\middleware\CustomerAuth::class);
Route::post('customer/confirmOrder', 'Customer/confirmOrder')->middleware(\app\api\middleware\CustomerAuth::class);
Route::post('customer/updateOrder', 'Customer/updateOrder')->middleware(\app\api\middleware\CustomerAuth::class);
Route::get('customer/orders', 'Customer/orders')->middleware(\app\api\middleware\CustomerAuth::class);

// 小程序绑定（需登录）
Route::post('miniapp/bind', 'Miniapp/bind')->middleware(\app\api\middleware\UserAuth::class);

require __DIR__ . '/restaurant_wxa.php';
require __DIR__ . '/restaurant_openclaw.php';
require __DIR__ . '/prompt.php';

// C端用户：文件上传（需登录）
Route::post('common/upload', 'Common/upload')->middleware(\app\api\middleware\UserAuth::class);

// 员工报工相关接口（需登录，按租户隔离）- 前端报工小程序
Route::get('mesuser/dashboard', 'Mesuser/dashboard')->middleware(\app\api\middleware\UserAuth::class);
Route::get('mesuser/taskInfo', 'Mesuser/taskInfo')->middleware(\app\api\middleware\UserAuth::class);
Route::post('mesuser/report', 'Mesuser/report')->middleware(\app\api\middleware\UserAuth::class);
Route::get('mesuser/reports', 'Mesuser/reports')->middleware(\app\api\middleware\UserAuth::class);
Route::get('mesuser/reportDetail', 'Mesuser/reportDetail')->middleware(\app\api\middleware\UserAuth::class);
Route::get('mesuser/wages', 'Mesuser/wages')->middleware(\app\api\middleware\UserAuth::class);
Route::post('mesuser/uploadImage', 'Mesuser/uploadImage')->middleware(\app\api\middleware\UserAuth::class);
Route::get('mesuser/notifications', 'Mesuser/notifications')->middleware(\app\api\middleware\UserAuth::class);
Route::post('mesuser/readNotifications', 'Mesuser/readNotifications')->middleware(\app\api\middleware\UserAuth::class);

// MES 大屏（需登录，按租户隔离）
Route::get('mesdashboard/data', 'Mesdashboard/data')->middleware(\app\api\middleware\UserAuth::class);
Route::get('mesdashboard/gantt', 'Mesdashboard/gantt')->middleware(\app\api\middleware\UserAuth::class);

Route::get('cockpit/getData', 'Cockpit/getData')->middleware(\app\api\middleware\UserAuth::class);

// AI 接口（员工端）
Route::post('ai/transcribe', 'Ai/transcribe')->middleware(\app\api\middleware\UserAuth::class)->middleware(\app\common\middleware\AICheck::class)->middleware(\app\common\middleware\AIBilling::class);
Route::post('ai/parse', 'Ai/parse')->middleware(\app\api\middleware\UserAuth::class)->middleware(\app\common\middleware\AICheck::class)->middleware(\app\common\middleware\AIBilling::class);
Route::post('ai/ask', 'Ai/ask')->middleware(\app\api\middleware\UserAuth::class)->middleware(\app\common\middleware\AICheck::class)->middleware(\app\common\middleware\AIBilling::class);

// 后端管理小程序 API（需管理员 Token）
Route::post('mesadmin/adminLogin', 'Mesadmin/adminLogin');
Route::get('mesadmin/checkToken', 'Mesadmin/checkToken')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getScanworkMenu', 'Mesadmin/getScanworkMenu')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getOrders', 'Mesadmin/getOrders')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getOrderDetail', 'Mesadmin/getOrderDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getOrderModels', 'Mesadmin/getOrderModels')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getAllocationRemain', 'Mesadmin/getAllocationRemain')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getAllocations', 'Mesadmin/getAllocations')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getAllocationDetail', 'Mesadmin/getAllocationDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getTaskByScan', 'Mesadmin/getTaskByScan')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createAllocation', 'Mesadmin/createAllocation')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getReports', 'Mesadmin/getReports')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getActiveReports', 'Mesadmin/getActiveReports')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getReportDetail', 'Mesadmin/getReportDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getReportStatistics', 'Mesadmin/getReportStatistics')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/auditReport', 'Mesadmin/auditReport')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/uploadAuditImage', 'Mesadmin/uploadAuditImage')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/uploadAuditVideo', 'Mesadmin/uploadAuditVideo')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/uploadReportImage', 'Mesadmin/uploadReportImage')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProducts', 'Mesadmin/getProducts')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProductDetail', 'Mesadmin/getProductDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getModels', 'Mesadmin/getModels')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProcesses', 'Mesadmin/getProcesses')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProcessPriceList', 'Mesadmin/getProcessPriceList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getUsers', 'Mesadmin/getUsers')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);

// 订单
Route::get('mesadmin/getOrderMaterialList', 'Mesadmin/getOrderMaterialList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createOrder', 'Mesadmin/createOrder')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateOrder', 'Mesadmin/updateOrder')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteOrder', 'Mesadmin/deleteOrder')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 客户
Route::get('mesadmin/getCustomerList', 'Mesadmin/getCustomerList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getCustomerDetail', 'Mesadmin/getCustomerDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createCustomer', 'Mesadmin/createCustomer')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateCustomer', 'Mesadmin/updateCustomer')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteCustomer', 'Mesadmin/deleteCustomer')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 供应商
Route::get('mesadmin/getSupplierList', 'Mesadmin/getSupplierList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getSupplierDetail', 'Mesadmin/getSupplierDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createSupplier', 'Mesadmin/createSupplier')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateSupplier', 'Mesadmin/updateSupplier')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteSupplier', 'Mesadmin/deleteSupplier')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 工序
Route::post('mesadmin/createProcess', 'Mesadmin/createProcess')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateProcess', 'Mesadmin/updateProcess')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteProcess', 'Mesadmin/deleteProcess')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 产品/型号/工序工价
Route::post('mesadmin/createProduct', 'Mesadmin/createProduct')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateProduct', 'Mesadmin/updateProduct')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteProduct', 'Mesadmin/deleteProduct')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createProductModel', 'Mesadmin/createProductModel')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateProductModel', 'Mesadmin/updateProductModel')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteProductModel', 'Mesadmin/deleteProductModel')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/batchAddProductModels', 'Mesadmin/batchAddProductModels')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createProcessPrice', 'Mesadmin/createProcessPrice')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateProcessPrice', 'Mesadmin/updateProcessPrice')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteProcessPrice', 'Mesadmin/deleteProcessPrice')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/batchProcessPrice', 'Mesadmin/batchProcessPrice')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 物料
Route::get('mesadmin/getMaterialList', 'Mesadmin/getMaterialList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getMaterialCategoryList', 'Mesadmin/getMaterialCategoryList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createMaterialCategory', 'Mesadmin/createMaterialCategory')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateMaterialCategory', 'Mesadmin/updateMaterialCategory')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteMaterialCategory', 'Mesadmin/deleteMaterialCategory')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);

// 员工产能
Route::get('mesadmin/getCapacityList', 'Mesadmin/getCapacityList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createCapacity', 'Mesadmin/createCapacity')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateCapacity', 'Mesadmin/updateCapacity')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteCapacity', 'Mesadmin/deleteCapacity')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);

// 工艺路线
Route::get('mesadmin/getProcessRouteList', 'Mesadmin/getProcessRouteList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProcessRouteDetail', 'Mesadmin/getProcessRouteDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createProcessRoute', 'Mesadmin/createProcessRoute')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateProcessRoute', 'Mesadmin/updateProcessRoute')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteProcessRoute', 'Mesadmin/deleteProcessRoute')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);

// 智能排产
Route::get('mesadmin/getScheduleList', 'Mesadmin/getScheduleList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/generateSchedule', 'Mesadmin/generateSchedule')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getScheduleGanttData', 'Mesadmin/getScheduleGanttData')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/publishSchedule', 'Mesadmin/publishSchedule')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteSchedule', 'Mesadmin/deleteSchedule')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);

Route::get('mesadmin/getMaterialDetail', 'Mesadmin/getMaterialDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createMaterial', 'Mesadmin/createMaterial')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateMaterial', 'Mesadmin/updateMaterial')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteMaterial', 'Mesadmin/deleteMaterial')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 仓库
Route::get('mesadmin/getWarehouseList', 'Mesadmin/getWarehouseList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getWarehouseDetail', 'Mesadmin/getWarehouseDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createWarehouse', 'Mesadmin/createWarehouse')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateWarehouse', 'Mesadmin/updateWarehouse')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteWarehouse', 'Mesadmin/deleteWarehouse')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 库存
Route::get('mesadmin/getStockList', 'Mesadmin/getStockList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getStockLog', 'Mesadmin/getStockLog')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getMaterialStockLog', 'Mesadmin/getMaterialStockLog')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProductStockLog', 'Mesadmin/getProductStockLog')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getStockAlertList', 'Mesadmin/getStockAlertList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getStockOutboundList', 'Mesadmin/getStockOutboundList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/stockIn', 'Mesadmin/stockIn')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/stockOut', 'Mesadmin/stockOut')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/stockCheck', 'Mesadmin/stockCheck')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// BOM
Route::get('mesadmin/getBomList', 'Mesadmin/getBomList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getBomDetail', 'Mesadmin/getBomDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getBomItems', 'Mesadmin/getBomItems')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createBom', 'Mesadmin/createBom')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateBom', 'Mesadmin/updateBom')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteBom', 'Mesadmin/deleteBom')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/addBomItem', 'Mesadmin/addBomItem')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateBomItem', 'Mesadmin/updateBomItem')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteBomItem', 'Mesadmin/deleteBomItem')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/approveBom', 'Mesadmin/approveBom')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 生产计划
Route::get('mesadmin/getProductionPlanList', 'Mesadmin/getProductionPlanList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProductionPlanDetail', 'Mesadmin/getProductionPlanDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProductionPlanAllocations', 'Mesadmin/getProductionPlanAllocations')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProductionPlanProgress', 'Mesadmin/getProductionPlanProgress')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProductionPlanProgressStats', 'Mesadmin/getProductionPlanProgressStats')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createProductionPlan', 'Mesadmin/createProductionPlan')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateProductionPlan', 'Mesadmin/updateProductionPlan')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteProductionPlan', 'Mesadmin/deleteProductionPlan')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/setProductionPlanStatus', 'Mesadmin/setProductionPlanStatus')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getProductionPlanProgressOverview', 'Mesadmin/getProductionPlanProgressOverview')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 分工分配
Route::post('mesadmin/updateAllocation', 'Mesadmin/updateAllocation')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteAllocation', 'Mesadmin/deleteAllocation')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/batchCreateAllocation', 'Mesadmin/batchCreateAllocation')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/generateQrcode', 'Mesadmin/generateQrcode')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 报工
Route::post('mesadmin/deleteReport', 'Mesadmin/deleteReport')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 采购
Route::get('mesadmin/getPurchaseRequestList', 'Mesadmin/getPurchaseRequestList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getPurchaseList', 'Mesadmin/getPurchaseList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getPurchaseDetail', 'Mesadmin/getPurchaseDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createPurchase', 'Mesadmin/createPurchase')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updatePurchase', 'Mesadmin/updatePurchase')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deletePurchase', 'Mesadmin/deletePurchase')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/purchaseInbound', 'Mesadmin/purchaseInbound')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 发货
Route::get('mesadmin/getShipmentList', 'Mesadmin/getShipmentList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getShipmentDetail', 'Mesadmin/getShipmentDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createShipment', 'Mesadmin/createShipment')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateShipment', 'Mesadmin/updateShipment')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteShipment', 'Mesadmin/deleteShipment')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 质检
Route::get('mesadmin/getQualityStandards', 'Mesadmin/getQualityStandards')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getQualityChecks', 'Mesadmin/getQualityChecks')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 工资
Route::get('mesadmin/getWageList', 'Mesadmin/getWageList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getWageStatistics', 'Mesadmin/getWageStatistics')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 追溯码
Route::get('mesadmin/getTraceCodeList', 'Mesadmin/getTraceCodeList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/generateTraceCode', 'Mesadmin/generateTraceCode')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/queryTraceCode', 'Mesadmin/queryTraceCode')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// 售后
Route::get('mesadmin/getAfterSalesList', 'Mesadmin/getAfterSalesList')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getAfterSalesDetail', 'Mesadmin/getAfterSalesDetail')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/createAfterSales', 'Mesadmin/createAfterSales')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/updateAfterSales', 'Mesadmin/updateAfterSales')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::post('mesadmin/deleteAfterSales', 'Mesadmin/deleteAfterSales')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
// BI
Route::get('mesadmin/getDashboardData', 'Mesadmin/getDashboardData')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getBiProductionEfficiency', 'Mesadmin/getBiProductionEfficiency')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getBiQualityAnalysis', 'Mesadmin/getBiQualityAnalysis')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);
Route::get('mesadmin/getBiCostAnalysis', 'Mesadmin/getBiCostAnalysis')->middleware([\app\api\middleware\AdminAuth::class, \app\api\middleware\MesadminPermission::class]);

if (is_file(__DIR__ . '/restaurant.php')) {
    require __DIR__ . '/restaurant.php';
}

// 租户自助购买API
Route::get('tenant_purchase/package_list', 'TenantPurchase/packageList');
Route::post('tenant_purchase/create_order', 'TenantPurchase/createOrder');
Route::get('tenant_purchase/payment_methods', 'TenantPurchase/paymentMethods');
Route::post('tenant_purchase/pay', 'TenantPurchase/pay');
Route::get('tenant_purchase/return', 'TenantPurchase/return');
Route::post('tenant_purchase/notify', 'TenantPurchase/notify');
Route::get('tenant_purchase/order_status', 'TenantPurchase/orderStatus');

if (is_file(__DIR__ . '/prompt.php')) {
    require __DIR__ . '/prompt.php';
}
