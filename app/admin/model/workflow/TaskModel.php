<?php
declare(strict_types=1);

namespace app\admin\model\workflow;

use think\Model;

class TaskModel extends Model
{
    protected $name = 'wf_task';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $autoWriteTimestamp = 'int';
    protected $dateFormat = false;
}

