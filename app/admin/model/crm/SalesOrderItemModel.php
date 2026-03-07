<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

/**
 * CRM 销售订单明细模型
 */
class SalesOrderItemModel extends Model
{
    protected $name = 'crm_sales_order_item';

    protected $type = [
        'tenant_id'       => 'integer',
        'sales_order_id'  => 'integer',
        'product_id'      => 'integer',
        'quantity'        => 'integer',
        'price'           => 'float',
        'amount'          => 'float',
        'create_time'     => 'integer',
        'update_time'     => 'integer',
    ];
}
