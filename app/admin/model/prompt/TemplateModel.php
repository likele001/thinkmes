<?php
declare(strict_types=1);
namespace app\admin\model\prompt;
use think\Model;

class TemplateModel extends Model {
    protected $name = 'prompt_template';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $dateFormat = false;

    public function category() {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function getVariablesAttr($value) {
        if (empty($value)) return [];
        $arr = json_decode($value, true);
        return is_array($arr) ? $arr : [];
    }
    public function setVariablesAttr($value) {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?: '[]');
    }

    public function getExtVariablesAttr($value) {
        if (empty($value)) return [];
        $arr = json_decode($value, true);
        return is_array($arr) ? $arr : [];
    }
    public function setExtVariablesAttr($value) {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?: '[]');
    }
}
