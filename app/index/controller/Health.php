<?php
declare(strict_types=1);

namespace app\index\controller;

use think\response\Json;

class Health
{
    /**
     * 简单的健康检查接口，返回当前服务时间和状态
     * Route: GET /status
     */
    public function status(): Json
    {
        return json([
            'status' => 'ok',
            'time'   => time(),
        ]);
    }
}
