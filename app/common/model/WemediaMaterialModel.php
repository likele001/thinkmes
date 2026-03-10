<?php
declare(strict_types=1);

namespace app\common\model;

class WemediaMaterialModel extends BaseModel
{
    protected $name = 'wemedia_material';

    protected $type = [
        'tenant_id'   => 'integer',
        'user_id'     => 'integer',
        'size'        => 'integer',
        'is_shared'   => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_TEXT = 'text';

    public static function typeText(string $v): string
    {
        $map = ['image' => '图片', 'video' => '视频', 'audio' => '音频', 'text' => '文案'];
        return $map[$v] ?? $v;
    }
}
