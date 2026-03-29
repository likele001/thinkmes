<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class KdsEventModel extends Model
{
    protected $name = 'restaurant_kds_event';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'store_id'    => 'integer',
        'order_id'    => 'integer',
        'create_time' => 'integer',
    ];
}

