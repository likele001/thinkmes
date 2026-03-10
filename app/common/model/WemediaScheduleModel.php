<?php
declare(strict_types=1);

namespace app\common\model;

class WemediaScheduleModel extends BaseModel
{
    protected $name = 'wemedia_schedule';

    protected $type = [
        'tenant_id'   => 'integer',
        'user_id'     => 'integer',
        'relate_id'   => 'integer',
        'plan_time'   => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_OFFLINE = 2;

    public static function statusText(int $v): string
    {
        $map = [0 => '待发布', 1 => '已发布', 2 => '已下架'];
        return $map[$v] ?? '-';
    }

    public static function relateTypeText(string $v): string
    {
        return $v === 'video' ? '短视频' : '文案';
    }
}
