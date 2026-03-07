<?php
declare(strict_types=1);

namespace app\admin\model\finance;

use app\common\model\BaseModel as Model;

class FinanceReceivableModel extends Model
{
    protected $name = 'finance_receivable';

    protected $type = [
        'tenant_id'   => 'integer',
        'customer_id' => 'integer',
        'order_id'    => 'integer',
        'amount'      => 'float',
        'received'    => 'float',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(\app\admin\model\crm\CustomerModel::class, 'customer_id', 'id');
    }

    public static function getStatusList(): array
    {
        return [0 => '未结清', 1 => '已结清'];
    }
}
