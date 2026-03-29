<?php
use think\facade\Route;

Route::group('restaurant', function () {
    Route::get('store/index', 'restaurant.Store/index');
    Route::get('store/add', 'restaurant.Store/add');
    Route::get('store/edit', 'restaurant.Store/edit');
    Route::post('store/add', 'restaurant.Store/add');
    Route::post('store/edit', 'restaurant.Store/edit');
    Route::post('store/del', 'restaurant.Store/del');

    Route::get('area/index', 'restaurant.Area/index');
    Route::get('area/add', 'restaurant.Area/add');
    Route::get('area/edit', 'restaurant.Area/edit');
    Route::post('area/add', 'restaurant.Area/add');
    Route::post('area/edit', 'restaurant.Area/edit');
    Route::post('area/del', 'restaurant.Area/del');

    Route::get('table/index', 'restaurant.Table/index');
    Route::get('table/add', 'restaurant.Table/add');
    Route::get('table/edit', 'restaurant.Table/edit');
    Route::post('table/add', 'restaurant.Table/add');
    Route::post('table/edit', 'restaurant.Table/edit');
    Route::post('table/del', 'restaurant.Table/del');
    Route::post('table/resetToken', 'restaurant.Table/resetToken');
    Route::get('table/wxacode', 'restaurant.Table/wxacode');
    Route::get('table/qrcode', 'restaurant.Table/qrcode');

    Route::get('category/index', 'restaurant.Category/index');
    Route::get('category/add', 'restaurant.Category/add');
    Route::get('category/edit', 'restaurant.Category/edit');
    Route::post('category/add', 'restaurant.Category/add');
    Route::post('category/edit', 'restaurant.Category/edit');
    Route::post('category/del', 'restaurant.Category/del');

    Route::get('item/index', 'restaurant.Item/index');
    Route::get('item/add', 'restaurant.Item/add');
    Route::get('item/edit', 'restaurant.Item/edit');
    Route::post('item/add', 'restaurant.Item/add');
    Route::post('item/edit', 'restaurant.Item/edit');
    Route::post('item/del', 'restaurant.Item/del');

    Route::get('order/index', 'restaurant.Order/index');
    Route::get('order/detail', 'restaurant.Order/detail');
    Route::post('order/updateStatus', 'restaurant.Order/updateStatus');

    Route::get('kds/index', 'restaurant.Kds/index');
    Route::get('kds/items', 'restaurant.Kds/items');
    Route::post('kds/call', 'restaurant.Kds/call');
    Route::post('kds/setSoldOut', 'restaurant.Kds/setSoldOut');

    Route::get('combo/index', 'restaurant.Combo/index');
    Route::get('combo/add', 'restaurant.Combo/add');
    Route::get('combo/edit', 'restaurant.Combo/edit');
    Route::post('combo/add', 'restaurant.Combo/add');
    Route::post('combo/edit', 'restaurant.Combo/edit');
    Route::post('combo/del', 'restaurant.Combo/del');

    Route::get('option_group/index', 'restaurant.OptionGroup/index');
    Route::get('option_group/add', 'restaurant.OptionGroup/add');
    Route::get('option_group/edit', 'restaurant.OptionGroup/edit');
    Route::post('option_group/add', 'restaurant.OptionGroup/add');
    Route::post('option_group/edit', 'restaurant.OptionGroup/edit');
    Route::post('option_group/del', 'restaurant.OptionGroup/del');

    Route::get('option/index', 'restaurant.Option/index');
    Route::get('option/add', 'restaurant.Option/add');
    Route::get('option/edit', 'restaurant.Option/edit');
    Route::post('option/add', 'restaurant.Option/add');
    Route::post('option/edit', 'restaurant.Option/edit');
    Route::post('option/del', 'restaurant.Option/del');

    Route::get('ai/index', 'restaurant.Ai/index');

    Route::get('report/index', 'restaurant.Report/index');
    Route::get('report/overview', 'restaurant.Report/overview');

    Route::get('wxa_setting/index', 'restaurant.WxaSetting/index');
    Route::post('wxa_setting/index', 'restaurant.WxaSetting/index');

    Route::get('ai_report/index', 'restaurant.AiReport/index');
    Route::post('ai_report/generate', 'restaurant.AiReport/generate');

    Route::get('ai_config/index', 'restaurant.AiConfig/index');
    Route::get('ai_config/add', 'restaurant.AiConfig/add');
    Route::post('ai_config/add', 'restaurant.AiConfig/add');
    Route::get('ai_config/edit', 'restaurant.AiConfig/edit');
    Route::post('ai_config/edit', 'restaurant.AiConfig/edit');
    Route::post('ai_config/del', 'restaurant.AiConfig/del');
    Route::post('ai_config/test', 'restaurant.AiConfig/test');

    Route::get('openclaw_setting/index', 'restaurant.OpenClawSetting/index');
    Route::post('openclaw_setting/index', 'restaurant.OpenClawSetting/index');

    Route::get('review/index', 'restaurant.Review/index');
    Route::post('review/sync', 'restaurant.Review/sync');
    Route::post('review/autoReply', 'restaurant.Review/autoReply');

    Route::get('review_template/index', 'restaurant.ReviewTemplate/index');
    Route::get('review_template/add', 'restaurant.ReviewTemplate/add');
    Route::get('review_template/edit', 'restaurant.ReviewTemplate/edit');
    Route::post('review_template/add', 'restaurant.ReviewTemplate/add');
    Route::post('review_template/edit', 'restaurant.ReviewTemplate/edit');
    Route::post('review_template/del', 'restaurant.ReviewTemplate/del');

    Route::get('review/stats', 'restaurant.Review/stats');

    Route::get('review_keyword/index', 'restaurant.ReviewKeyword/index');
    Route::get('review_keyword/add', 'restaurant.ReviewKeyword/add');
    Route::get('review_keyword/edit', 'restaurant.ReviewKeyword/edit');
    Route::post('review_keyword/add', 'restaurant.ReviewKeyword/add');
    Route::post('review_keyword/edit', 'restaurant.ReviewKeyword/edit');
    Route::post('review_keyword/del', 'restaurant.ReviewKeyword/del');

    Route::get('review_alert/index', 'restaurant.ReviewAlert/index');
    Route::post('review_alert/markDone', 'restaurant.ReviewAlert/markDone');
});
