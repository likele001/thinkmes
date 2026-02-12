<?php
declare(strict_types=1);

namespace app\admin\model;

use app\common\model\BaseModel as Model;
use think\model\concern\SoftDelete;

class AdminModel extends Model
{
    use SoftDelete;

    protected $name = 'admin';
    protected $deleteTime = 'delete_time';
    /** 未删除为 NULL，查询条件为 delete_time IS NULL，与 init.sql 中未设置 delete_time 一致 */
    protected $defaultSoftDelete = null;

    protected $type = [
        'tenant_id'   => 'integer',
        'pid'         => 'integer',
        'data_scope'  => 'integer',
        'status'      => 'integer',
        'login_time'  => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
        'delete_time' => 'integer',
    ];

    public function setPasswordAttr($value): string
    {
        return password_hash((string) $value, PASSWORD_BCRYPT);
    }
}
