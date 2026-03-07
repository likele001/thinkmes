<?php
/**
 * CRM 客户关系管理 - 独立路由（应用包安装时合并到此文件）
 */
use think\facade\Route;

Route::group('crm', function () {
    Route::get('customer/index', 'crm.Customer/index');
    Route::get('customer/add', 'crm.Customer/add');
    Route::get('customer/edit', 'crm.Customer/edit');
    Route::get('customer', 'crm.Customer/index');
    Route::post('customer/add', 'crm.Customer/add');
    Route::post('customer/edit', 'crm.Customer/edit');
    Route::post('customer/del', 'crm.Customer/del');
    Route::get('customer_tag/index', 'crm.CustomerTag/index');
    Route::get('customer_tag/add', 'crm.CustomerTag/add');
    Route::get('customer_tag/edit', 'crm.CustomerTag/edit');
    Route::post('customer_tag/add', 'crm.CustomerTag/add');
    Route::post('customer_tag/edit', 'crm.CustomerTag/edit');
    Route::post('customer_tag/del', 'crm.CustomerTag/del');
    Route::post('quote/aiGenerate', 'crm.Quote/aiGenerate')
        ->middleware(\app\common\middleware\AICheck::class)
        ->middleware(\app\common\middleware\AIBilling::class);
    Route::get('contact/index', 'crm.Contact/index');
    Route::get('contact/add', 'crm.Contact/add');
    Route::get('contact/edit', 'crm.Contact/edit');
    Route::get('contact/getByCustomer', 'crm.Contact/getByCustomer');
    Route::get('contact', 'crm.Contact/index');
    Route::post('contact/add', 'crm.Contact/add');
    Route::post('contact/edit', 'crm.Contact/edit');
    Route::post('contact/del', 'crm.Contact/del');
    Route::get('opportunity/index', 'crm.Opportunity/index');
    Route::get('opportunity/add', 'crm.Opportunity/add');
    Route::get('opportunity/edit', 'crm.Opportunity/edit');
    Route::get('opportunity/getByCustomer', 'crm.Opportunity/getByCustomer');
    Route::get('opportunity', 'crm.Opportunity/index');
    Route::post('opportunity/add', 'crm.Opportunity/add');
    Route::post('opportunity/edit', 'crm.Opportunity/edit');
    Route::post('opportunity/del', 'crm.Opportunity/del');
    Route::get('contract/index', 'crm.Contract/index');
    Route::get('contract/add', 'crm.Contract/add');
    Route::get('contract/edit', 'crm.Contract/edit');
    Route::get('contract', 'crm.Contract/index');
    Route::post('contract/add', 'crm.Contract/add');
    Route::post('contract/edit', 'crm.Contract/edit');
    Route::post('contract/del', 'crm.Contract/del');
    Route::get('follow/index', 'crm.Follow/index');
    Route::get('follow/add', 'crm.Follow/add');
    Route::get('follow/edit', 'crm.Follow/edit');
    Route::get('follow', 'crm.Follow/index');
    Route::post('follow/add', 'crm.Follow/add');
    Route::post('follow/edit', 'crm.Follow/edit');
    Route::post('follow/del', 'crm.Follow/del');
    // CRM 下的 AI 路由：智能跟单建议（受租户购买与全局开关限制）
    Route::post('follow/suggest', 'ai.CrmFollow/suggest')
        ->middleware(\app\common\middleware\AICheck::class)
        ->middleware(\app\common\middleware\AIBilling::class);
    Route::get('payment/index', 'crm.Payment/index');
    Route::get('payment/add', 'crm.Payment/add');
    Route::get('payment/edit', 'crm.Payment/edit');
    Route::get('payment', 'crm.Payment/index');
    Route::post('payment/add', 'crm.Payment/add');
    Route::post('payment/edit', 'crm.Payment/edit');
    Route::post('payment/del', 'crm.Payment/del');
    Route::get('product/index', 'crm.Product/index');
    Route::get('product/add', 'crm.Product/add');
    Route::get('product/edit', 'crm.Product/edit');
    Route::get('product', 'crm.Product/index');
    Route::post('product/add', 'crm.Product/add');
    Route::post('product/edit', 'crm.Product/edit');
    Route::post('product/del', 'crm.Product/del');
    Route::get('sales_order/index', 'crm.SalesOrder/index');
    Route::get('sales_order/add', 'crm.SalesOrder/add');
    Route::get('sales_order/edit', 'crm.SalesOrder/edit');
    Route::get('sales_order', 'crm.SalesOrder/index');
    Route::post('sales_order/add', 'crm.SalesOrder/add');
    Route::post('sales_order/edit', 'crm.SalesOrder/edit');
    Route::post('sales_order/del', 'crm.SalesOrder/del');
    Route::post('sales_order/toMes', 'crm.SalesOrder/toMes');
    Route::get('report/index', 'crm.Report/index');
    Route::get('report', 'crm.Report/index');
});
