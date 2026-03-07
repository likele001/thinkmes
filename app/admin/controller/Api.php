<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\View;

/** API接口访问（套餐功能占位） */
class Api extends Backend
{
    public function index(): string
    {
        View::assign('title', 'API接口访问');
        return $this->fetchWithLayout('extend/coming_soon');
    }
}
