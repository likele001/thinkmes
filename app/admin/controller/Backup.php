<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\View;

/** 数据备份（套餐功能占位） */
class Backup extends Backend
{
    public function index(): string
    {
        View::assign('title', '数据备份');
        return $this->fetchWithLayout('extend/coming_soon');
    }
}
