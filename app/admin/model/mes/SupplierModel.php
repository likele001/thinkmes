<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 供应商模型
 */
class SupplierModel extends Model
{
    protected $name = 'mes_supplier';

    protected $type = [
        'tenant_id'   => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
