<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class CustomFieldValue extends Model
{
    protected $name = 'custom_field_value';
    protected $autoWriteTimestamp = true;

    protected $type = [
        'field_id'  => 'integer',
        'record_id' => 'integer',
        'tenant_id' => 'integer',
    ];

    public function field()
    {
        return $this->belongsTo(CustomField::class, 'field_id');
    }

    public function getFieldValue($tableName, $recordId, $fieldName)
    {
        return $this->alias('v')
            ->join('custom_field f', 'v.field_id = f.id')
            ->where('v.table_name', $tableName)
            ->where('v.record_id', $recordId)
            ->where('f.name', $fieldName)
            ->value('v.value');
    }

    public function getFieldValues($tableName, $recordId)
    {
        return $this->alias('v')
            ->join('custom_field f', 'v.field_id = f.id')
            ->where('v.table_name', $tableName)
            ->where('v.record_id', $recordId)
            ->where('f.status', 1)
            ->column('v.value', 'f.name');
    }

    public function saveFieldValues($tableName, $recordId, $fieldValues)
    {
        $fields = CustomField::where('group_id', function ($query) use ($tableName) {
            $query->name('custom_field_group')->where('table_name', $tableName)->field('id');
        })->where('status', 1)->column('id', 'name');

        foreach ($fieldValues as $name => $value) {
            if (isset($fields[$name])) {
                $fieldId = $fields[$name];
                $existing = $this->where('field_id', $fieldId)
                    ->where('record_id', $recordId)
                    ->where('table_name', $tableName)
                    ->find();

                if ($existing) {
                    $existing->value = $value;
                    $existing->save();
                } else {
                    $this->create([
                        'field_id'  => $fieldId,
                        'record_id' => $recordId,
                        'table_name' => $tableName,
                        'value'     => $value,
                    ]);
                }
            }
        }
    }
}
