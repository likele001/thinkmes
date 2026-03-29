<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class RestaurantAiConfigModel extends Model
{
    protected $name = 'restaurant_ai_config';

    protected $type = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}

