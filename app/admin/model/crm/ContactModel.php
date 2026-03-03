<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

/**
 * CRM 联系人模型
 */
class ContactModel extends Model
{
    protected $name = 'crm_contact';

    protected $type = [
        'tenant_id'   => 'integer',
        'customer_id' => 'integer',
        'is_main'     => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
