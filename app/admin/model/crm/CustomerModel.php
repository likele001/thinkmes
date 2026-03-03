<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

/**
 * CRM 客户模型
 */
class CustomerModel extends Model
{
    protected $name = 'crm_customer';

    protected $type = [
        'tenant_id'   => 'integer',
        'level'        => 'integer',
        'status'       => 'integer',
        'create_time'  => 'integer',
        'update_time'  => 'integer',
    ];
}
