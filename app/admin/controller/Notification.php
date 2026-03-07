<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\View;

/** 消息通知（套餐功能占位） */
class Notification extends Backend
{
    public function index(): string
    {
        View::assign('title', '消息通知');
        return $this->fetchWithLayout('extend/coming_soon');
    }
}
