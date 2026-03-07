<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

/**
 * CRM 回款模型
 */
class PaymentModel extends Model
{
    protected $name = 'crm_payment';

    protected $type = [
        'tenant_id'   => 'integer',
        'contract_id' => 'integer',
        'amount'      => 'float',
        'pay_date'    => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
