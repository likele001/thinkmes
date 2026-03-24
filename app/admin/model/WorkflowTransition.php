<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class WorkflowTransition extends Model
{
    protected $name = 'workflow_transition';
    protected $autoWriteTimestamp = true;

    protected $type = [
        'workflow_id'      => 'integer',
        'from_state_id'    => 'integer',
        'to_state_id'      => 'integer',
        'require_approval' => 'integer',
        'sort'             => 'integer',
    ];

    protected $json = ['condition_expression'];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function fromState()
    {
        return $this->belongsTo(WorkflowState::class, 'from_state_id');
    }

    public function toState()
    {
        return $this->belongsTo(WorkflowState::class, 'to_state_id');
    }

    public function approvers()
    {
        return $this->hasMany(WorkflowApprover::class, 'transition_id')->order('sort', 'asc');
    }
}
