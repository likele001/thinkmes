<?php
declare(strict_types=1);
namespace app\admin\model\prompt;
use think\Model;

class CategoryModel extends Model {
    protected $name = 'prompt_category';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = false;
}
