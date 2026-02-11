<?php
declare(strict_types=1);

namespace app\index\controller;

use app\common\controller\BaseController;
use think\response\Redirect;

class Index extends BaseController
{
    public function index(): Redirect
    {
        return redirect('/admin/index/index');
    }
}
