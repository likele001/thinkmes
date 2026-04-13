<?php
use think\facade\Route;

// AI智能问答
Route::post('chat/ask', 'Chat/ask');
Route::get('chat/history', 'Chat/history');
Route::post('chat/clear', 'Chat/clearHistory');
Route::get('chat/faq', 'Chat/faq');
Route::get('chat/categories', 'Chat/categories');
Route::get('chat/search', 'Chat/search');
Route::get('chat/article', 'Chat/article');
Route::post('chat/helpful', 'Chat/helpful');

// 工单系统
Route::post('ticket/create', 'Ticket/create');
Route::get('ticket/my', 'Ticket/myTickets');
Route::get('ticket/detail', 'Ticket/detail');
Route::post('ticket/reply', 'Ticket/reply');
Route::post('ticket/rate', 'Ticket/rate');
Route::post('ticket/close', 'Ticket/close');

// 客服会话（待实现WebSocket）
Route::get('session/create', 'Session/create');
Route::get('session/info', 'Session/info');
Route::post('session/message', 'Session/message');
Route::get('session/messages', 'Session/messages');
