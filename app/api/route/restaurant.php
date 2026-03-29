<?php
use think\facade\Route;

Route::group('restaurant', function () {
    Route::get('table/info', 'restaurant.Table/info');
    Route::get('menu', 'restaurant.Menu/index');

    Route::get('cart/get', 'restaurant.Cart/get');
    Route::post('cart/add', 'restaurant.Cart/add');
    Route::post('cart/update', 'restaurant.Cart/update');
    Route::post('cart/remove', 'restaurant.Cart/remove');
    Route::post('cart/clear', 'restaurant.Cart/clear');

    Route::post('order/create', 'restaurant.Order/create');
    Route::get('order/list', 'restaurant.Order/list');
    Route::get('order/detail', 'restaurant.Order/detail');

    Route::get('payment/gateways', 'restaurant.Payment/gateways');
    Route::post('payment/pay', 'restaurant.Payment/pay');
});
