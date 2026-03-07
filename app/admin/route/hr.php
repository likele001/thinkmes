<?php
/**
 * 人事考勤 - 独立路由
 */
use think\facade\Route;

Route::group('hr', function () {
    Route::get('department/index', 'hr.Department/index');
    Route::get('department/add', 'hr.Department/add');
    Route::get('department/edit', 'hr.Department/edit');
    Route::post('department/add', 'hr.Department/add');
    Route::post('department/edit', 'hr.Department/edit');
    Route::post('department/del', 'hr.Department/del');

    Route::get('position/index', 'hr.Position/index');
    Route::get('position/add', 'hr.Position/add');
    Route::get('position/edit', 'hr.Position/edit');
    Route::post('position/add', 'hr.Position/add');
    Route::post('position/edit', 'hr.Position/edit');
    Route::post('position/del', 'hr.Position/del');

    Route::get('employee/index', 'hr.Employee/index');
    Route::get('employee/add', 'hr.Employee/add');
    Route::get('employee/edit', 'hr.Employee/edit');
    Route::post('employee/add', 'hr.Employee/add');
    Route::post('employee/edit', 'hr.Employee/edit');
    Route::post('employee/del', 'hr.Employee/del');

    Route::get('attendance/index', 'hr.Attendance/index');

    Route::get('leave/index', 'hr.Leave/index');
    Route::get('leave/add', 'hr.Leave/add');
    Route::get('leave/edit', 'hr.Leave/edit');
    Route::post('leave/add', 'hr.Leave/add');
    Route::post('leave/edit', 'hr.Leave/edit');
    Route::post('leave/del', 'hr.Leave/del');
    Route::post('leave/approve', 'hr.Leave/approve');

    Route::get('overtime/index', 'hr.Overtime/index');
    Route::get('overtime/add', 'hr.Overtime/add');
    Route::get('overtime/edit', 'hr.Overtime/edit');
    Route::post('overtime/add', 'hr.Overtime/add');
    Route::post('overtime/edit', 'hr.Overtime/edit');
    Route::post('overtime/del', 'hr.Overtime/del');
    Route::post('overtime/approve', 'hr.Overtime/approve');
});
