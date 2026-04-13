<?php
use think\facade\Route;

// 移除冗余路由，保留POST方法用于支付通知
Route::post('payment/notify/:gateway_id', 'Payment/notify');
Route::post('payment/create', 'Payment/create');
