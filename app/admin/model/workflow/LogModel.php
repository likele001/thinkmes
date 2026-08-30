<?php
declare(strict_types=1);

namespace app\admin\model\workflow;

use think\Model;

class LogModel extends Model
{
    protected $name = 'wf_log';
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $autoWriteTimestamp = 'int';
    protected $dateFormat = false;
}

