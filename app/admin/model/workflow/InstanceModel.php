<?php
declare(strict_types=1);

namespace app\admin\model\workflow;

use think\Model;

class InstanceModel extends Model
{
    protected $name = 'wf_instance';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $autoWriteTimestamp = 'int';
    protected $dateFormat = false;
}

