<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class CustomField extends Model
{
    protected $name = 'custom_field';
    protected $autoWriteTimestamp = true;

    protected $type = [
        'required' => 'integer',
        'is_list'  => 'integer',
        'is_search' => 'integer',
        'is_sort'  => 'integer',
        'width'    => 'integer',
        'sort'     => 'integer',
        'status'   => 'integer',
        'options'  => 'json',
    ];

    public function group()
    {
        return $this->belongsTo(CustomFieldGroup::class, 'group_id');
    }

    public function getOptionsTextAttr($value, $data)
    {
        if (empty($data['options'])) {
            return '';
        }
        $options = json_decode($data['options'], true);
        if (!is_array($options)) {
            return '';
        }
        $text = [];
        foreach ($options as $opt) {
            $text[] = ($opt['label'] ?? '') . ':' . ($opt['value'] ?? '');
        }
        return implode(', ', $text);
    }

    public function searchGroupNameAttr($query, $value)
    {
        $query->whereHas('group', function ($q) use ($value) {
            $q->where('name', 'like', '%' . $value . '%');
        });
    }
}
