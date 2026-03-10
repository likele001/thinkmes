<?php
declare(strict_types=1);

namespace app\common\model;

/**
 * 自媒体选题
 */
class WemediaTopicModel extends BaseModel
{
    protected $name = 'wemedia_topic';

    protected $type = [
        'tenant_id'   => 'integer',
        'user_id'     => 'integer',
        'status'      => 'integer',
        'is_shared'   => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public const STATUS_PENDING = 0;
    public const STATUS_DONE = 1;

    public static function statusText(int $v): string
    {
        return $v === self::STATUS_DONE ? '已完成' : '待创作';
    }
}
