<?php
/**
 * 基础版后台路由（含租户、套餐、应用中心、用户管理；无 MES/CRM/AI/支付/设备/人事/财务）
 * 由 pack_base.php 复制为 app/admin/route/app.php
 */
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

// 个人中心
Route::get('profile/index', 'Profile/index');
Route::post('profile/updateProfile', 'Profile/updateProfile');

// 管理员
Route::group('admin', function () {
    Route::get('index', 'Admin/index');
    Route::get('add', 'Admin/add');
    Route::get('edit', 'Admin/edit');
    Route::post('add', 'Admin/addPost');
    Route::post('edit', 'Admin/editPost');
    Route::post('del', 'Admin/del');
    Route::post('resetPwd', 'Admin/resetPwd');
});

// 租户管理
Route::get('tenant/index', 'Tenant/index');
Route::get('tenant/add', 'Tenant/add');
Route::get('tenant/edit', 'Tenant/edit');
Route::post('tenant/add', 'Tenant/addPost');
Route::post('tenant/edit', 'Tenant/editPost');
Route::post('tenant/del', 'Tenant/del');
Route::get('tenant/miniapp', 'TenantMiniapp/index');
Route::post('tenant/miniapp', 'TenantMiniapp/index');
Route::get('tenant/miniapp/index', 'TenantMiniapp/index');
Route::post('tenant/miniapp/index', 'TenantMiniapp/index');

// 租户套餐、套餐功能、租户订单
Route::get('tenant_package/index', 'TenantPackage/index');
Route::get('tenant_package/add', 'TenantPackage/add');
Route::get('tenant_package/edit', 'TenantPackage/edit');
Route::post('tenant_package/add', 'TenantPackage/addPost');
Route::post('tenant_package/edit', 'TenantPackage/editPost');
Route::post('tenant_package/del', 'TenantPackage/del');
Route::get('tenant_package_feature/index', 'TenantPackageFeature/index');
Route::get('tenant_package_feature/add', 'TenantPackageFeature/add');
Route::post('tenant_package_feature/add', 'TenantPackageFeature/addPost');
Route::post('tenant_package_feature/del', 'TenantPackageFeature/del');
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

// 角色、权限规则、系统配置
Route::get('role/index', 'Role/index');
Route::get('role/add', 'Role/add');
Route::get('role/edit', 'Role/edit');
Route::post('role/add', 'Role/addPost');
Route::post('role/edit', 'Role/editPost');
Route::post('role/del', 'Role/del');
Route::get('auth_rule/index', 'AuthRule/index');
Route::get('auth_rule/add', 'AuthRule/add');
Route::get('auth_rule/edit', 'AuthRule/edit');
Route::post('auth_rule/add', 'AuthRule/addPost');
Route::post('auth_rule/edit', 'AuthRule/editPost');
Route::post('auth_rule/del', 'AuthRule/del');
Route::get('auth_rule/tree', 'AuthRule/tree');
Route::get('config/index', 'Config/index');
Route::get('config/group', 'Config/group');
Route::post('config/save', 'Config/save');

// 打印模板、短信配置
Route::get('print_template/index', 'PrintTemplate/index');
Route::get('print_template/add', 'PrintTemplate/add');
Route::get('print_template/edit', 'PrintTemplate/edit');
Route::get('print_template/preview', 'PrintTemplate/preview');
Route::post('print_template/add', 'PrintTemplate/add');
Route::post('print_template/edit', 'PrintTemplate/edit');
Route::post('print_template/del', 'PrintTemplate/del');
Route::get('sms_config/index', 'SmsConfig/index');
Route::post('sms_config/index', 'SmsConfig/index');

// 插件管理、云存储入口
Route::get('addon/index', 'Addon/index');
Route::get('addon/detail', 'Addon/detail');
Route::get('addon/config', 'Addon/config');
Route::post('addon/install', 'Addon/install');
Route::post('addon/uninstall', 'Addon/uninstall');
Route::post('addon/enable', 'Addon/enable');
Route::post('addon/disable', 'Addon/disable');
Route::get('cloudstorage/index', function () {
    return redirect((string) url('addon/config', ['name' => 'cloudstorage']));
});

// 操作日志、上传、缓存清理
Route::get('log/index', 'Log/index');
Route::get('log/export', 'Log/export');
Route::post('common/upload', 'Common/upload');
Route::post('common/uploadChunk', 'Common/uploadChunk');
Route::post('common/mergeChunks', 'Common/mergeChunks');
Route::post('index/clearCache', 'Index/clearCache');

// 应用中心
Route::get('app_center/index', 'AppCenter/index');
Route::post('app_center/install', 'AppCenter/install');
Route::post('app_center/uninstall', 'AppCenter/uninstall');
Route::post('app_center/upload', 'AppCenter/upload');

// 套餐功能占位（报表、导出、API、自定义字段、工作流、通知、备份）
Route::get('report/index', 'Report/index');
Route::get('export/index', 'Export/index');
Route::get('api/index', 'Api/index');
Route::get('custom_field/index', 'CustomField/index');
Route::get('workflow/index', 'Workflow/index');
Route::get('notification/index', 'Notification/index');
Route::get('backup/index', 'Backup/index');

// 按需加载应用路由（应用包安装时合并 crm.php / mes.php / ai.php）
if (is_file(__DIR__ . '/crm.php')) {
    require __DIR__ . '/crm.php';
}
if (is_file(__DIR__ . '/mes.php')) {
    require __DIR__ . '/mes.php';
}
if (is_file(__DIR__ . '/ai.php')) {
    require __DIR__ . '/ai.php';
}
