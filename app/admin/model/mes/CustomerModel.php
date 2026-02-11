<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 客户模型
 */
class CustomerModel extends Model
{
    protected $name = 'mes_customer';

    protected $type = [
        'tenant_id'   => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
