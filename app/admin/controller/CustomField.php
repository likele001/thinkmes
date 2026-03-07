<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\View;

/** 自定义字段（套餐功能占位） */
class CustomField extends Backend
{
    public function index(): string
    {
        View::assign('title', '自定义字段');
        return $this->fetchWithLayout('extend/coming_soon');
    }
}
