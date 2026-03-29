<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class ReviewKeywordModel extends Model
{
    protected $name = 'restaurant_review_keyword';

    protected $type = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'weight' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}

