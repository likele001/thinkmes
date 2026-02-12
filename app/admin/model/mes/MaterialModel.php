<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 物料模型
 */
class MaterialModel extends Model
{
    protected $name = 'mes_material';

    protected $type = [
        'tenant_id'          => 'integer',
        'category_id'        => 'integer',
        'default_supplier_id'=> 'integer',
        'current_price'      => 'decimal',
        'stock'              => 'decimal',
        'min_stock'          => 'decimal',
        'create_time'        => 'integer',
        'update_time'        => 'integer',
    ];
}
