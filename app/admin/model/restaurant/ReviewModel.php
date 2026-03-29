<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class ReviewModel extends Model
{
    protected $name = 'restaurant_review';

    protected $type = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'store_id' => 'integer',
        'rating' => 'integer',
        'sentiment' => 'integer',
        'reply_status' => 'integer',
        'review_time' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(StoreModel::class, 'store_id', 'id');
    }
}

