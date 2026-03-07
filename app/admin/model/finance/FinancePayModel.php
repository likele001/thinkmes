<?php
declare(strict_types=1);

namespace app\admin\model\finance;

use app\common\model\BaseModel as Model;

class FinancePayModel extends Model
{
    protected $name = 'finance_pay';

    protected $type = [
        'tenant_id'   => 'integer',
        'payable_id'  => 'integer',
        'amount'      => 'float',
        'create_time' => 'integer',
    ];

    public function payable()
    {
        return $this->belongsTo(FinancePayableModel::class, 'payable_id', 'id');
    }
}
