<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class CustomFieldGroup extends Model
{
    protected $name = 'custom_field_group';
    protected $autoWriteTimestamp = true;

    protected $type = [
        'status' => 'integer',
        'sort'   => 'integer',
    ];

    public function fields()
    {
        return $this->hasMany(CustomField::class, 'group_id');
    }
}
