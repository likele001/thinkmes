<?php
declare(strict_types=1);

namespace app\admin\model;

use app\common\model\BaseModel as Model;

/**
 * 在线命令执行记录（CRUD 生成等）
 * 无 tenant_id，仅平台使用
 */
class CommandModel extends Model
{
    protected $name = 'command';

    protected $type = [
        'status'      => 'integer',
        'executetime' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public const TYPE_CRUD = 'crud';
    public const STATUS_FAIL = 0;
    public const STATUS_SUCCESS = 1;

    /** 类型文案 */
    public static function typeText(string $type): string
    {
        $map = [
            'crud' => '一键生成CRUD',
            'menu' => '一键生成菜单',
        ];
        return $map[$type] ?? $type;
    }

    /** 状态文案 */
    public static function statusText(int $status): string
    {
        return $status === self::STATUS_SUCCESS ? '成功' : '失败';
    }
}
