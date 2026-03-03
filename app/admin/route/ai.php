<?php
/**
 * 工厂 AI 模块 - 独立路由
 */
use think\facade\Route;

Route::group('ai', function () {
    Route::get('config/index', 'ai.Config/index');
    Route::get('config/add', 'ai.Config/add');
    Route::get('config/edit', 'ai.Config/edit');
    Route::get('config', 'ai.Config/index');
    Route::post('config/add', 'ai.Config/add');
    Route::post('config/edit', 'ai.Config/edit');
    Route::post('config/del', 'ai.Config/del');
    Route::post('config/test', 'ai.Config/test');
    Route::get('config/testAudio', 'ai.Config/testAudio');
    Route::post('config/testSpeech', 'ai.Config/testSpeech');

    Route::get('voice_report/index', 'ai.VoiceReport/index');
    Route::get('voice_report', 'ai.VoiceReport/index');
    Route::post('voice_report/transcribe', 'ai.VoiceReport/transcribe');
    Route::post('voice_report/parse', 'ai.VoiceReport/parse');

    Route::get('anomaly/index', 'ai.Anomaly/index');
    Route::get('anomaly', 'ai.Anomaly/index');
    Route::post('anomaly/scan', 'ai.Anomaly/scan');

    Route::get('qa/index', 'ai.Qa/index');
    Route::get('qa', 'ai.Qa/index');
    Route::post('qa/ask', 'ai.Qa/ask');

    Route::get('daily_report/index', 'ai.DailyReport/index');
    Route::get('daily_report', 'ai.DailyReport/index');
    Route::post('daily_report/generate', 'ai.DailyReport/generate');

    Route::get('crm_follow/index', 'ai.CrmFollow/index');
    Route::get('crm_follow', 'ai.CrmFollow/index');
    Route::post('crm_follow/suggest', 'ai.CrmFollow/suggest');
});
