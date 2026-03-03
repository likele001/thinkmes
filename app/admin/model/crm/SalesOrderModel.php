<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

/**
 * CRM 销售订单模型
 */
class SalesOrderModel extends Model
{
    protected $name = 'crm_sales_order';

    protected $type = [
        'tenant_id'     => 'integer',
        'customer_id'   => 'integer',
        'contract_id'   => 'integer',
        'total_amount'  => 'float',
        'mes_order_id'  => 'integer',
        'delivery_date' => 'integer',
        'create_time'   => 'integer',
        'update_time'   => 'integer',
    ];
}
