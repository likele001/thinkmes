<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\View;

/** 工作流（套餐功能占位） */
class Workflow extends Backend
{
    public function index(): string
    {
        View::assign('title', '工作流');
        return $this->fetchWithLayout('extend/coming_soon');
    }
}
