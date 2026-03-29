<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class ReviewAlertModel extends Model
{
    protected $name = 'restaurant_review_alert';

    protected $type = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'store_id' => 'integer',
        'rating' => 'integer',
        'status' => 'integer',
        'review_time' => 'integer',
        'create_time' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(StoreModel::class, 'store_id', 'id');
    }
}

