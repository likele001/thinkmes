<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

/**
 * CRM 商机模型
 */
class OpportunityModel extends Model
{
    protected $name = 'crm_opportunity';

    protected $type = [
        'tenant_id'     => 'integer',
        'customer_id'   => 'integer',
        'contact_id'    => 'integer',
        'owner_id'      => 'integer',
        'amount'        => 'float',
        'expected_date' => 'integer',
        'create_time'   => 'integer',
        'update_time'   => 'integer',
    ];
}
