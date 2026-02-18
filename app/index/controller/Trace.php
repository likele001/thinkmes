<?php
declare(strict_types=1);

namespace app\index\controller;

use app\admin\model\mes\TraceCodeModel;
use think\response\Json;

class Trace
{
    public function query(): Json
    {
        $code = trim((string) request()->get('code'));
        if ($code === '') {
            return json([
                'code' => 0,
                'msg'  => '追溯码不能为空',
                'data' => [],
            ]);
        }

        $trace = TraceCodeModel::with(['order', 'model.product', 'process', 'report.allocation'])
            ->where('trace_code', $code)
            ->where('status', 1)
            ->find();

        if (!$trace) {
            return json([
                'code' => 0,
                'msg'  => '追溯码不存在或已失效',
                'data' => [],
            ]);
        }

        $trace->scan_count += 1;
        $trace->last_scan_time = time();
        $trace->save();

        return json([
            'code' => 1,
            'msg'  => '查询成功',
            'data' => $trace->toArray(),
        ]);
    }
}

