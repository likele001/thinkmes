<?php
declare(strict_types=1);

namespace app\index\controller\wemedia;

use think\facade\View;

/**
 * 自媒体工作台 - 入口与首页
 */
class Index extends BaseWemedia
{
    public function index(): string
    {
        View::assign('title', '自媒体工作台');
        return $this->fetchWithLayout('wemedia/index');
    }
}
