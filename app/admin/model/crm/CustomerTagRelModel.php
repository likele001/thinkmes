<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

class CustomerTagRelModel extends Model
{
    protected $name = 'crm_customer_tag_rel';

    protected $type = [
        'customer_id' => 'integer',
        'tag_id'      => 'integer',
    ];

    public function tag()
    {
        return $this->belongsTo(CustomerTagModel::class, 'tag_id', 'id');
    }
}
