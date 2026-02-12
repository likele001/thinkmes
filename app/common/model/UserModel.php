<?php
declare(strict_types=1);

namespace app\common\model;

use app\common\model\BaseModel as Model;

/**
 * C端用户模型 fa_user
 */
class UserModel extends Model
{
    protected $name = 'user';

    protected $type = [
        'tenant_id'   => 'integer',
        'level'       => 'integer',
        'score'       => 'integer',
        'status'      => 'integer',
        'login_time'  => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    protected $hidden = ['password'];

    public function setPasswordAttr($value): string
    {
        return password_hash((string) $value, PASSWORD_BCRYPT);
    }
}
