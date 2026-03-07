<?php
declare(strict_types=1);

namespace app\admin\model\finance;

use app\common\model\BaseModel as Model;

class FinancePayableModel extends Model
{
    protected $name = 'finance_payable';

    protected $type = [
        'tenant_id'   => 'integer',
        'supplier_id' => 'integer',
        'amount'      => 'float',
        'paid'        => 'float',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function supplier()
    {
        return $this->belongsTo(\app\admin\model\mes\SupplierModel::class, 'supplier_id', 'id');
    }

    public static function getStatusList(): array
    {
        return [0 => '未结清', 1 => '已结清'];
    }
}
