<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use think\facade\View;

/**
 * MES制造执行系统首页
 */
class Mes extends Backend
{
    /**
     * MES首页
     */
    public function index(): string
    {
        View::assign('title', 'MES制造执行系统');
        return $this->fetchWithLayout('mes/index');
    }
}
