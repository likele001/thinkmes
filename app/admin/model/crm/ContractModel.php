<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

/**
 * CRM 合同模型
 */
class ContractModel extends Model
{
    protected $name = 'crm_contract';

    protected $type = [
        'tenant_id'     => 'integer',
        'customer_id'   => 'integer',
        'opportunity_id'=> 'integer',
        'amount'        => 'float',
        'sign_date'     => 'integer',
        'create_time'   => 'integer',
        'update_time'   => 'integer',
    ];
}
