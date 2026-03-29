<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use think\facade\View;
use think\Response;

class Ai extends Backend
{
    public function index(): string|Response
    {
        View::assign('title', '餐饮AI');
        return $this->fetchWithLayout('restaurant/ai/index');
    }
}

