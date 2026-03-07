<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel as Model;

class CustomerTagModel extends Model
{
    protected $name = 'crm_customer_tag';

    protected $type = [
        'tenant_id'   => 'integer',
        'sort'        => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
