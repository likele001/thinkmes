<?php
declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\CustomField;
use app\admin\model\CustomFieldValue;
use app\admin\model\CustomFieldGroup;

class CustomFieldService
{
    protected static array $fieldTypeMap = [
        'text'     => 'text',
        'number'   => 'number',
        'select'   => 'select',
        'radio'    => 'radio',
        'checkbox' => 'checkbox',
        'date'     => 'date',
        'datetime' => 'datetime',
        'textarea' => 'textarea',
        'editor'   => 'editor',
        'image'    => 'image',
        'file'     => 'file',
        'switch'   => 'switch',
    ];

    public static function getFieldsByTable(string $tableName, int $tenantId = 0): array
    {
        $groupId = CustomFieldGroup::where('table_name', $tableName)
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->value('id');

        if (!$groupId) {
            return [];
        }

        return CustomField::where('group_id', $groupId)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
    }

    public static function renderFormFields(string $tableName, int $recordId = 0, int $tenantId = 0): array
    {
        $fields = self::getFieldsByTable($tableName, $tenantId);
        $values = [];

        if ($recordId > 0) {
            $valueModel = new CustomFieldValue();
            $values = $valueModel->getFieldValues($tableName, $recordId);
        }

        $formFields = [];
        foreach ($fields as $field) {
            $formField = [
                'name'        => $field['name'],
                'title'       => $field['title'],
                'type'        => self::$fieldTypeMap[$field['type']] ?? 'text',
                'value'       => $values[$field['name']] ?? $field['default_value'],
                'placeholder' => $field['placeholder'],
                'required'    => (bool)$field['required'],
                'width'       => $field['width'] ?? 12,
                'tips'        => $field['tips'],
                'options'     => [],
                'validation'  => explode(',', $field['validation_rules']),
                'regex'       => $field['regex_pattern'],
            ];

            if (in_array($field['type'], ['select', 'radio', 'checkbox'])) {
                $options = json_decode($field['options'], true);
                $formField['options'] = is_array($options) ? $options : [];
            }

            $formFields[] = $formField;
        }

        return $formFields;
    }

    public static function renderListColumns(string $tableName, int $tenantId = 0): array
    {
        $fields = self::getFieldsByTable($tableName, $tenantId);

        $columns = [];
        foreach ($fields as $field) {
            if ($field['is_list']) {
                $columns[] = [
                    'field' => $field['name'],
                    'title' => $field['title'],
                    'searchable' => (bool)$field['is_search'],
                    'sortable' => (bool)$field['is_sort'],
                    'type' => self::$fieldTypeMap[$field['type']] ?? 'text',
                ];
            }
        }

        return $columns;
    }

    public static function validateFieldValues(array $fieldValues, string $tableName, int $tenantId = 0): array
    {
        $errors = [];
        $fields = self::getFieldsByTable($tableName, $tenantId);
        $fieldMap = array_column($fields, null, 'name');

        foreach ($fieldValues as $name => $value) {
            if (!isset($fieldMap[$name])) {
                continue;
            }

            $field = $fieldMap[$name];

            if ($field['required'] && empty($value)) {
                $errors[$name] = $field['title'] . '不能为空';
                continue;
            }

            if (!empty($value)) {
                $rules = array_filter(explode(',', $field['validation_rules']));
                foreach ($rules as $rule) {
                    $error = self::validateRule($value, $rule, $field);
                    if ($error) {
                        $errors[$name] = $error;
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    protected static function validateRule($value, string $rule, array $field): ?string
    {
        switch ($rule) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $field['title'] . '格式不正确';
                }
                break;
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return $field['title'] . '格式不正确';
                }
                break;
            case 'number':
                if (!is_numeric($value)) {
                    return $field['title'] . '必须是数字';
                }
                break;
            case 'integer':
                if (!preg_match('/^\d+$/', (string)$value)) {
                    return $field['title'] . '必须是整数';
                }
                break;
            case 'regex':
                $pattern = $field['regex_pattern'] ?? '';
                if ($pattern && !preg_match($pattern, (string)$value)) {
                    return $field['title'] . '格式不正确';
                }
                break;
        }

        return null;
    }

    public static function saveCustomFieldValues(string $tableName, int $recordId, array $fieldValues, int $tenantId = 0): bool
    {
        $valueModel = new CustomFieldValue();
        $valueModel->saveFieldValues($tableName, $recordId, $fieldValues);
        return true;
    }

    public static function deleteCustomFieldValues(string $tableName, int $recordId): bool
    {
        CustomFieldValue::where('table_name', $tableName)
            ->where('record_id', $recordId)
            ->delete();
        return true;
    }
}
