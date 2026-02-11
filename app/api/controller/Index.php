<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use think\Response;

class Index extends BaseController
{
    public function index(): Response
    {
        return $this->success('API', ['version' => '1.0', 'app' => 'thinkmes']);
    }
}
