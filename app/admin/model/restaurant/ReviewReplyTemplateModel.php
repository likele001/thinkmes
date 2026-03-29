<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class ReviewReplyTemplateModel extends Model
{
    protected $name = 'restaurant_review_reply_template';

    protected $type = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'rating_min' => 'integer',
        'rating_max' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}

