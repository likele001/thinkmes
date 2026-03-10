<?php
declare(strict_types=1);

namespace app\common\model;

class WemediaReportModel extends BaseModel
{
    protected $name = 'wemedia_report';

    protected $type = [
        'tenant_id'   => 'integer',
        'user_id'     => 'integer',
        'metric_value'=> 'float',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public static function metricTypeText(string $v): string
    {
        $map = ['view' => '播放/阅读', 'like' => '点赞', 'comment' => '评论', 'share' => '分享', 'fan' => '涨粉'];
        return $map[$v] ?? $v;
    }
}
