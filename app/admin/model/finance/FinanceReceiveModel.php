<?php
declare(strict_types=1);

namespace app\admin\model\finance;

use app\common\model\BaseModel as Model;

class FinanceReceiveModel extends Model
{
    protected $name = 'finance_receive';

    protected $type = [
        'tenant_id'      => 'integer',
        'receivable_id'  => 'integer',
        'amount'         => 'float',
        'create_time'    => 'integer',
    ];

    public function receivable()
    {
        return $this->belongsTo(FinanceReceivableModel::class, 'receivable_id', 'id');
    }
}
