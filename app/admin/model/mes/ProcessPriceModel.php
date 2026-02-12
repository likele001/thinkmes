<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class ProcessPriceModel extends Model
{
    protected $name = 'mes_process_price';
    
    protected $type = [
        'tenant_id'   => 'integer',
        'model_id'    => 'integer',
        'process_id'  => 'integer',
        'price'       => 'float',
        'time_price'  => 'float',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    // 关联产品型号
    public function model()
    {
        return $this->belongsTo(ProductModelModel::class, 'model_id', 'id');
    }

    // 关联工序
    public function process()
    {
        return $this->belongsTo(ProcessModel::class, 'process_id', 'id');
    }
}
