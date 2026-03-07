<?php
/**
 * 财务模块 - 独立路由
 */
use think\facade\Route;

Route::group('finance', function () {
    Route::get('receivable/index', 'finance.Receivable/index');
    Route::get('receivable/add', 'finance.Receivable/add');
    Route::get('receivable/edit', 'finance.Receivable/edit');
    Route::post('receivable/add', 'finance.Receivable/add');
    Route::post('receivable/edit', 'finance.Receivable/edit');
    Route::post('receivable/del', 'finance.Receivable/del');

    Route::get('payable/index', 'finance.Payable/index');
    Route::get('payable/add', 'finance.Payable/add');
    Route::get('payable/edit', 'finance.Payable/edit');
    Route::post('payable/add', 'finance.Payable/add');
    Route::post('payable/edit', 'finance.Payable/edit');
    Route::post('payable/del', 'finance.Payable/del');

    Route::get('receive/index', 'finance.Receive/index');
    Route::get('receive/add', 'finance.Receive/add');
    Route::post('receive/add', 'finance.Receive/add');

    Route::get('pay/index', 'finance.Pay/index');
    Route::get('pay/add', 'finance.Pay/add');
    Route::post('pay/add', 'finance.Pay/add');

    Route::get('profit/index', 'finance.Profit/index');

    Route::get('statement/index', 'finance.Statement/index');
});
