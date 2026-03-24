<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class WorkflowInstance extends Model
{
    protected $name = 'workflow_instance';
    protected $autoWriteTimestamp = true;

    protected $type = [
        'workflow_id'    => 'integer',
        'record_id'      => 'integer',
        'current_state_id' => 'integer',
        'initiator_id'   => 'integer',
        'is_completed'   => 'integer',
        'completed_time' => 'integer',
        'tenant_id'      => 'integer',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function currentState()
    {
        return $this->belongsTo(WorkflowState::class, 'current_state_id');
    }

    public function approvals()
    {
        return $this->hasMany(WorkflowApproval::class, 'instance_id')->order('create_time', 'desc');
    }

    public function pendingApprovals()
    {
        return $this->approvals()->where('status', 'pending');
    }

    public function getInstanceByRecord(string $tableName, int $recordId): ?self
    {
        return $this->where('table_name', $tableName)
            ->where('record_id', $recordId)
            ->find();
    }
}
