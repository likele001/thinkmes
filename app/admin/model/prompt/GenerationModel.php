<?php
declare(strict_types=1);
namespace app\admin\model\prompt;
use think\Model;

class GenerationModel extends Model {
    protected $name = 'prompt_generation';
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $autoWriteTimestamp = 'int';
    protected $dateFormat = false;
}
