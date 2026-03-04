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

// 应用中心（内置应用安装/卸载/上传）
Route::get('app_center/index', 'AppCenter/index');
Route::post('app_center/install', 'AppCenter/install');
Route::post('app_center/uninstall', 'AppCenter/uninstall');
Route::post('app_center/upload', 'AppCenter/upload');

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
