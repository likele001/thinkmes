<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class WorkflowApproval extends Model
{
    protected $name = 'workflow_approval';
    protected $autoWriteTimestamp = true;

    protected $type = [
        'instance_id'   => 'integer',
        'transition_id' => 'integer',
        'from_state_id' => 'integer',
        'to_state_id'   => 'integer',
        'approver_id'   => 'integer',
        'approval_time' => 'integer',
    ];

    public function instance()
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    public function transition()
    {
        return $this->belongsTo(WorkflowTransition::class, 'transition_id');
    }

    public function fromState()
    {
        return $this->belongsTo(WorkflowState::class, 'from_state_id');
    }

    public function toState()
    {
        return $this->belongsTo(WorkflowState::class, 'to_state_id');
    }
}
