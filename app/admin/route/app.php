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
Route::get('index/setLang', 'Index/setLang');
Route::get('index/langDebug', 'Index/langDebug');
Route::get('index/menu', 'Index/menu');
Route::get('index/tenantList', 'Index/tenantList');
Route::post('index/switchTenantView', 'Index/switchTenantView');

// 个人中心
Route::get('profile/index', 'Profile/index');
Route::post('profile/updateProfile', 'Profile/updateProfile');

// 管理员（使用分组，路径为 /admin/admin/*）
Route::group('admin', function () {
    Route::get('index', 'Admin/index');
    Route::get('add', 'Admin/add');
    Route::get('edit', 'Admin/edit');
    Route::post('add', 'Admin/addPost');
    Route::post('edit', 'Admin/editPost');
    Route::post('del', 'Admin/del');
    Route::post('resetPwd', 'Admin/resetPwd');
});

// 租户管理（仅平台超管）
Route::get('tenant/index', 'Tenant/index');
Route::get('tenant/add', 'Tenant/add');
Route::get('tenant/edit', 'Tenant/edit');
Route::post('tenant/add', 'Tenant/addPost');
Route::post('tenant/edit', 'Tenant/editPost');
Route::post('tenant/del', 'Tenant/del');

// 租户小程序配置（租户管理员在后台配置自己的小程序）
Route::get('tenant/miniapp', 'TenantMiniapp/index');
Route::post('tenant/miniapp', 'TenantMiniapp/index');

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

// 打印模板
Route::get('print_template/index', 'PrintTemplate/index');
Route::get('print_template/add', 'PrintTemplate/add');
Route::get('print_template/edit', 'PrintTemplate/edit');
Route::get('print_template/preview', 'PrintTemplate/preview');
Route::post('print_template/add', 'PrintTemplate/add');
Route::post('print_template/edit', 'PrintTemplate/edit');
Route::post('print_template/del', 'PrintTemplate/del');

// 短信配置
Route::get('sms_config/index', 'SmsConfig/index');
Route::post('sms_config/index', 'SmsConfig/index');

// 插件管理
Route::get('addon/index', 'Addon/index');
Route::get('addon/detail', 'Addon/detail');
Route::get('addon/config', 'Addon/config');
Route::post('addon/install', 'Addon/install');
Route::post('addon/uninstall', 'Addon/uninstall');
Route::post('addon/enable', 'Addon/enable');
Route::post('addon/disable', 'Addon/disable');

// 云存储配置快捷入口
Route::get('cloudstorage/index', function () {
    return redirect((string) url('addon/config', ['name' => 'cloudstorage']));
});

// 操作日志
Route::get('log/index', 'Log/index');
Route::get('log/export', 'Log/export');

// 上传
Route::post('common/upload', 'Common/upload');
Route::post('common/uploadChunk', 'Common/uploadChunk');
Route::post('common/mergeChunks', 'Common/mergeChunks');

// 缓存清理（后台入口）
Route::post('index/clearCache', 'Index/clearCache');

// CRUD 一键生成 / 在线命令（仅平台超管）
Route::get('crud_gen/index', 'CrudGen/index');
Route::get('crud_gen/add', 'CrudGen/add');
Route::get('crud_gen/tables', 'CrudGen/tables');
Route::get('crud_gen/sqlFiles', 'CrudGen/sqlFiles');
Route::get('crud_gen/getFieldList', 'CrudGen/getFieldList');
Route::post('crud_gen/command', 'CrudGen/command');
Route::get('crud_gen/detail', 'CrudGen/detail');
Route::post('crud_gen/reExecute', 'CrudGen/reExecute');
Route::post('crud_gen/del', 'CrudGen/del');
Route::post('crud_gen/generate', 'CrudGen/generate');

// 应用中心（内置应用安装/卸载/上传）
Route::get('app_center/index', 'AppCenter/index');
Route::post('app_center/install', 'AppCenter/install');
Route::post('app_center/uninstall', 'AppCenter/uninstall');
Route::post('app_center/upload', 'AppCenter/upload');
Route::post('app_center/pack', 'AppCenter/pack');
Route::get('app_center/downloadPack', 'AppCenter/downloadPack');

// 自媒体工作流（配置 + 各模块管理）
Route::get('wemedia_config/index', 'WemediaConfig/index');
Route::post('wemedia_config/index', 'WemediaConfig/index');
Route::get('wemedia_topic/index', 'WemediaTopic/index');
Route::post('wemedia_topic/del', 'WemediaTopic/del');
Route::get('wemedia_copy/index', 'WemediaCopy/index');
Route::post('wemedia_copy/del', 'WemediaCopy/del');
Route::get('wemedia_material/index', 'WemediaMaterial/index');
Route::post('wemedia_material/del', 'WemediaMaterial/del');
Route::get('wemedia_video/index', 'WemediaVideo/index');
Route::post('wemedia_video/del', 'WemediaVideo/del');
Route::get('wemedia_schedule/index', 'WemediaSchedule/index');
Route::post('wemedia_schedule/del', 'WemediaSchedule/del');
Route::get('wemedia_report/index', 'WemediaReport/index');
Route::post('wemedia_report/del', 'WemediaReport/del');
Route::get('wemedia_compliance/index', 'WemediaCompliance/index');
Route::post('wemedia_compliance/del', 'WemediaCompliance/del');

// 套餐功能占位（报表统计、数据导出、API、自定义字段、工作流、消息通知、数据备份）
Route::get('report/index', 'Report/index');
Route::get('export/index', 'Export/index');
Route::get('api/index', 'Api/index');
Route::get('notification/index', 'Notification/index');
Route::get('backup/index', 'Backup/index');

// BI 报表与大屏：显式写死路径，避免被 mes 分组内 bi 兜底成 index
Route::get('mes/bi/dashboard', 'mes.Bi/dashboard');
Route::get('mes/bi/getDashboardData', 'mes.Bi/getDashboardData');
Route::get('mes/bi/productionEfficiency', 'mes.Bi/productionEfficiency');
Route::get('mes/bi/qualityAnalysis', 'mes.Bi/qualityAnalysis');
Route::get('mes/bi/costAnalysis', 'mes.Bi/costAnalysis');
Route::get('mes/bi/syncProgress', 'mes.Bi/syncProgress');
Route::post('mes/bi/syncProgress', 'mes.Bi/syncProgress');

// 按需加载应用路由（应用包安装时合并对应 crm.php / mes.php）

if (is_file(__DIR__ . '/crm.php')) {
    require __DIR__ . '/crm.php';
}
if (is_file(__DIR__ . '/mes.php')) {
    require __DIR__ . '/mes.php';
}
if (is_file(__DIR__ . '/ai.php')) {
    require __DIR__ . '/ai.php';
}
if (is_file(__DIR__ . '/payment.php')) {
    require __DIR__ . '/payment.php';
}
if (is_file(__DIR__ . '/equipment.php')) {
    require __DIR__ . '/equipment.php';
}
if (is_file(__DIR__ . '/hr.php')) {
    require __DIR__ . '/hr.php';
}
if (is_file(__DIR__ . '/finance.php')) {
    require __DIR__ . '/finance.php';
}
if (is_file(__DIR__ . '/restaurant.php')) {
    require __DIR__ . '/restaurant.php';
}
if (is_file(__DIR__ . '/extension.php')) {
    require __DIR__ . '/extension.php';
}


// 客服管理路由（注意：具体路由要放在通用路由前面）
Route::get('customer_service/sessions', 'CustomerService/sessions');
Route::get('customer_service/session_detail', 'CustomerService/sessionDetail');
Route::get('customer_service/get_session_list', 'CustomerService/getSessionList');
Route::get('customer_service/get_session_messages', 'CustomerService/getSessionMessages');
Route::get('customer_service/tickets', 'CustomerService/tickets');
Route::get('customer_service/ticket_detail', 'CustomerService/ticketDetail');
Route::get('customer_service/get_ticket_list', 'CustomerService/getTicketList');
Route::post('customer_service/reply_ticket', 'CustomerService/replyTicket');
Route::post('customer_service/update_ticket_status', 'CustomerService/updateTicketStatus');
Route::get('customer_service/knowledge', 'CustomerService/knowledge');
Route::get('customer_service/article_edit', 'CustomerService/articleEdit');
Route::post('customer_service/save_article', 'CustomerService/saveArticle');
Route::post('customer_service/delete_article', 'CustomerService/deleteArticle');
Route::get('customer_service/get_article_list', 'CustomerService/getArticleList');
Route::get('customer_service/faq', 'CustomerService/faq');
Route::get('customer_service/get_faq_list', 'CustomerService/getFaqList');
Route::post('customer_service/save_faq', 'CustomerService/saveFaq');
Route::post('customer_service/delete_faq', 'CustomerService/deleteFaq');
Route::get('customer_service/categories', 'CustomerService/categories');
Route::get('customer_service/get_category_list', 'CustomerService/getCategoryList');
Route::post('customer_service/save_category', 'CustomerService/saveCategory');
Route::post('customer_service/delete_category', 'CustomerService/deleteCategory');
Route::get('customer_service/ai_history', 'CustomerService/aiHistory');
Route::get('customer_service/get_ai_history_list', 'CustomerService/getAiHistoryList');
Route::get('customer_service/config', 'CustomerService/config');
Route::get('customer_service/get_config', 'CustomerService/getConfig');
Route::post('customer_service/save_config', 'CustomerService/saveConfig');
// 通用路由必须放在最后
Route::get('customer_service', 'CustomerService/index');
