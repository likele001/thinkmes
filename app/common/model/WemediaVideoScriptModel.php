<?php
declare(strict_types=1);

namespace app\common\model;

class WemediaVideoScriptModel extends BaseModel
{
    protected $name = 'wemedia_video_script';

    protected $type = [
        'tenant_id'   => 'integer',
        'user_id'     => 'integer',
        'topic_id'    => 'integer',
        'duration'    => 'integer',
        'status'      => 'integer',
        'is_shared'   => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    const STATUS_DRAFT = 0;
    const STATUS_DONE = 1;

    public static function statusText(int $v): string
    {
        return $v === self::STATUS_DONE ? '已完成' : '草稿';
    }
}
