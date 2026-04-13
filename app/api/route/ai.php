<?php
use think\facade\Route;

Route::post('ai/transcribe', 'Ai/transcribe')->middleware(\app\api\middleware\UserAuth::class)->middleware(\app\common\middleware\AICheck::class)->middleware(\app\common\middleware\AIBilling::class);
Route::post('ai/parse', 'Ai/parse')->middleware(\app\api\middleware\UserAuth::class)->middleware(\app\common\middleware\AICheck::class)->middleware(\app\common\middleware\AIBilling::class);
Route::post('ai/ask', 'Ai/ask')->middleware(\app\api\middleware\UserAuth::class)->middleware(\app\common\middleware\AICheck::class)->middleware(\app\common\middleware\AIBilling::class);
