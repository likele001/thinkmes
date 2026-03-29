<?php
use think\facade\Route;

Route::group('restaurant/wxa', function () {
    Route::get('getConfig', 'restaurant.Wxa/getConfig');
    Route::post('genTableCode', 'restaurant.Wxa/genTableCode');
});

