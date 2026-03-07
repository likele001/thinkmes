<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\View;

/** 报表统计（套餐功能占位） */
class Report extends Backend
{
    public function index(): string
    {
        View::assign('title', '报表统计');
        return $this->fetchWithLayout('extend/coming_soon');
    }
}
