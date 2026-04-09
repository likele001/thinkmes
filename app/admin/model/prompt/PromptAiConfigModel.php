<?php
declare(strict_types=1);
namespace app\admin\model\prompt;
use think\Model;

class PromptAiConfigModel extends Model {
    protected $name = 'prompt_ai_config';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = false;
}
