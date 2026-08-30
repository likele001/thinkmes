<?php
/**
 * 工作流应用路由
 */
use think\facade\Route;

Route::group('workflow', function () {
    // 模块接入
    Route::get('module/index', 'workflow.Module/index');
    Route::get('module/options', 'workflow.Module/options');
    Route::post('module/save', 'workflow.Module/save');

    // 流程定义
    Route::get('definition/index', 'workflow.Definition/index');
    Route::get('definition/add', 'workflow.Definition/add');
    Route::post('definition/add', 'workflow.Definition/add');
    Route::get('definition/edit', 'workflow.Definition/edit');
    Route::post('definition/edit', 'workflow.Definition/edit');
    Route::post('definition/del', 'workflow.Definition/del');
    Route::post('definition/toggle', 'workflow.Definition/toggle');
    Route::get('definition/designer', 'workflow.Definition/designer');
    Route::post('definition/saveNodes', 'workflow.Definition/saveNodes');

    // 审批中心
    Route::get('approval/index', 'workflow.Approval/index');
    Route::get('approval/pending', 'workflow.Approval/pending');
    Route::get('approval/done', 'workflow.Approval/done');
    Route::get('approval/mine', 'workflow.Approval/mine');
    Route::get('approval/detail', 'workflow.Approval/detail');
    Route::post('approval/doApprove', 'workflow.Approval/doApprove');
    Route::post('approval/doReject', 'workflow.Approval/doReject');
    Route::post('approval/doTransfer', 'workflow.Approval/doTransfer');
    Route::post('approval/doWithdraw', 'workflow.Approval/doWithdraw');
    Route::get('approval/adminOptions', 'workflow.Approval/adminOptions');
    Route::get('approval/stats', 'workflow.Approval/stats');

    // 流程实例（全量列表，与菜单 admin/workflow/instance/index 对应）
    Route::get('instance/index', 'workflow.Instance/index');
});