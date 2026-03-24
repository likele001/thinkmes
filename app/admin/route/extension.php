<?php
/**
 * 扩展模块路由配置 - 自定义字段、工作流、插件市场
 */
use think\facade\Route;

// 自定义字段模块路由
Route::group('custom_field', function () {
    Route::get('index', 'CustomField/index');
    Route::get('', 'CustomField/index');
    Route::get('add', 'CustomField/add');
    Route::post('add', 'CustomField/add');
    Route::get('edit', 'CustomField/edit');
    Route::post('edit', 'CustomField/edit');
    Route::post('del', 'CustomField/del');
    Route::post('multi', 'CustomField/multi');
    
    Route::get('groups', 'CustomField/groups');
    Route::get('groups/add', 'CustomField/addGroup');
    Route::post('groups/add', 'CustomField/addGroup');
    Route::get('groups/edit', 'CustomField/editGroup');
    Route::post('groups/edit', 'CustomField/editGroup');
    Route::post('groups/del', 'CustomField/delGroup');
    
    Route::get('render', 'CustomField/render');
    Route::post('save_value', 'CustomField/saveValue');
});

// 工作流模块路由
Route::group('workflow', function () {
    Route::get('index', 'Workflow/index');
    Route::get('', 'Workflow/index');
    Route::get('add', 'Workflow/add');
    Route::post('add', 'Workflow/add');
    Route::get('edit', 'Workflow/edit');
    Route::post('edit', 'Workflow/edit');
    Route::post('del', 'Workflow/del');
    Route::post('multi', 'Workflow/multi');
    
    Route::get('instances', 'Workflow/instances');
    Route::post('instances/start', 'Workflow/startInstance');
    Route::post('instances/approve', 'Workflow/approve');
    Route::post('instances/reject', 'Workflow/reject');
    Route::post('instances/withdraw', 'Workflow/withdraw');
    Route::get('instances/detail', 'Workflow/instanceDetail');
    
    Route::get('states', 'Workflow/getStates');
    Route::get('transitions', 'Workflow/getTransitions');
});

// 插件市场路由
Route::group('market', function () {
    Route::get('index', 'Market/index');
    Route::get('', 'Market/index');
    Route::get('detail', 'Market/detail');
    Route::group('my_plugins', function () {
        Route::get('index', 'Market/myPlugins');
        Route::get('', 'Market/myPlugins');
    });
    Route::post('install', 'Market/install');
    Route::post('uninstall', 'Market/uninstall');
    Route::post('update', 'Market/update');
    Route::get('versions', 'Market/getVersions');
    Route::post('review', 'Market/addReview');
    Route::get('reviews', 'Market/getReviews');
    Route::post('search', 'Market/search');
});
