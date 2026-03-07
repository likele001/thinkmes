<?php
/**
 * 设备管理 - 独立路由
 */
use think\facade\Route;

Route::group('equipment', function () {
    // 设备档案
    Route::get('equipment/add', 'equipment.Equipment/add');
    Route::get('equipment/edit', 'equipment.Equipment/edit');
    Route::get('equipment/index', 'equipment.Equipment/index');
    Route::get('equipment/stat', 'equipment.Equipment/stat');
    Route::get('equipment', 'equipment.Equipment/index');
    Route::post('equipment/add', 'equipment.Equipment/add');
    Route::post('equipment/edit', 'equipment.Equipment/edit');
    Route::post('equipment/del', 'equipment.Equipment/del');
    // 保养计划
    Route::get('maintenance/index', 'equipment.Maintenance/index');
    Route::get('maintenance/add', 'equipment.Maintenance/add');
    Route::get('maintenance/edit', 'equipment.Maintenance/edit');
    Route::post('maintenance/add', 'equipment.Maintenance/add');
    Route::post('maintenance/edit', 'equipment.Maintenance/edit');
    Route::post('maintenance/del', 'equipment.Maintenance/del');
    // 点检记录
    Route::get('check/index', 'equipment.Check/index');
    Route::get('check/add', 'equipment.Check/add');
    Route::get('check/edit', 'equipment.Check/edit');
    Route::post('check/add', 'equipment.Check/add');
    Route::post('check/edit', 'equipment.Check/edit');
    Route::post('check/del', 'equipment.Check/del');
    // 维修记录
    Route::get('repair/index', 'equipment.Repair/index');
    Route::get('repair/add', 'equipment.Repair/add');
    Route::get('repair/edit', 'equipment.Repair/edit');
    Route::post('repair/add', 'equipment.Repair/add');
    Route::post('repair/edit', 'equipment.Repair/edit');
    Route::post('repair/del', 'equipment.Repair/del');
    // 运行记录
    Route::get('runtime/index', 'equipment.Runtime/index');
    Route::get('runtime/add', 'equipment.Runtime/add');
    Route::get('runtime/edit', 'equipment.Runtime/edit');
    Route::post('runtime/add', 'equipment.Runtime/add');
    Route::post('runtime/edit', 'equipment.Runtime/edit');
    Route::post('runtime/del', 'equipment.Runtime/del');
});
Route::get('equipment', 'equipment.Equipment/index');
