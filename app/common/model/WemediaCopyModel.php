<?php
declare(strict_types=1);

namespace app\common\model;

class WemediaCopyModel extends BaseModel
{
    protected $name = 'wemedia_copy';

    protected $type = [
        'tenant_id'   => 'integer',
        'user_id'     => 'integer',
        'topic_id'    => 'integer',
        'status'      => 'integer',
        'is_shared'   => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;

    public static function statusText(int $v): string
    {
        return $v === self::STATUS_PUBLISHED ? '已发布' : '草稿';
    }
}
