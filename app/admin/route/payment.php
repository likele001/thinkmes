<?php
use think\facade\Route;

Route::group('payment', function () {
    Route::get('config/index', 'payment.Config/index');
    Route::get('config/add', 'payment.Config/add');
    Route::get('config/edit', 'payment.Config/edit');
    Route::post('config/add', 'payment.Config/addPost');
    Route::post('config/edit', 'payment.Config/editPost');
    Route::post('config/del', 'payment.Config/del');
    Route::get('config/configFields', 'payment.Config/configFields');
    // 各渠道菜单直入（跳转到网关列表并带 code 筛选）
    Route::get('config_alipay', 'payment.Config/configAlipay');
    Route::get('config_wechat', 'payment.Config/configWechat');
    Route::get('config_xunhupay', 'payment.Config/configXunhupay');
    Route::get('config_epay', 'payment.Config/configEpay');
    Route::post('config_alipay', 'payment.Config/configAlipay');
    Route::post('config_wechat', 'payment.Config/configWechat');
    Route::post('config_xunhupay', 'payment.Config/configXunhupay');
    Route::post('config_epay', 'payment.Config/configEpay');

    Route::get('config_alipay/index', 'payment.Config/configAlipay');
    Route::get('config_wechat/index', 'payment.Config/configWechat');
    Route::get('config_xunhupay/index', 'payment.Config/configXunhupay');
    Route::get('config_epay/index', 'payment.Config/configEpay');
    Route::post('config_alipay/index', 'payment.Config/configAlipay');
    Route::post('config_wechat/index', 'payment.Config/configWechat');
    Route::post('config_xunhupay/index', 'payment.Config/configXunhupay');
    Route::post('config_epay/index', 'payment.Config/configEpay');

    Route::get('order/index', 'payment.Order/index');

    Route::get('callback_log/index', 'payment.CallbackLog/index');
    Route::get('stats/index', 'payment.Stats/index');
});
