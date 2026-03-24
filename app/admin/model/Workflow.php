<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class Workflow extends Model
{
    protected $name = 'workflow';
    protected $autoWriteTimestamp = true;

    protected $type = [
        'is_active' => 'integer',
        'tenant_id' => 'integer',
    ];

    public function states()
    {
        return $this->hasMany(WorkflowState::class, 'workflow_id')->order('sort', 'asc');
    }

    public function transitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'workflow_id')->order('sort', 'asc');
    }

    public function instances()
    {
        return $this->hasMany(WorkflowInstance::class, 'workflow_id');
    }

    public function getActiveWorkflowByTable(string $tableName, int $tenantId = 0): ?self
    {
        return $this->where('table_name', $tableName)
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->find();
    }
}
