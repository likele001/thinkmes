<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

/**
 * CRM 产品模型
 */
class ProductModel extends Model
{
    protected $name = 'crm_product';

    protected $type = [
        'tenant_id'   => 'integer',
        'price'       => 'float',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
