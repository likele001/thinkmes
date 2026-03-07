<?php
declare(strict_types=1);

namespace app\admin\model\crm;

use app\common\model\BaseModel;

/**
 * CRM 跟进记录模型
 */
class FollowModel extends BaseModel
{
    protected $name = 'crm_follow';

    protected $type = [
        'tenant_id'        => 'integer',
        'customer_id'      => 'integer',
        'opportunity_id'   => 'integer',
        'admin_id'         => 'integer',
        'next_follow_time' => 'integer',
        'create_time'      => 'integer',
        'update_time'      => 'integer',
    ];
}
