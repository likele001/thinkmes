<?php
use think\facade\Route;

Route::group('restaurant/openclaw', function () {
    Route::post('webhook', 'restaurant.OpenClaw/webhook');
});

