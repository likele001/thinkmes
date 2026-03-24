<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class WorkflowState extends Model
{
    protected $name = 'workflow_state';
    protected $autoWriteTimestamp = true;

    protected $type = [
        'workflow_id' => 'integer',
        'is_initial'  => 'integer',
        'is_final'    => 'integer',
        'sort'        => 'integer',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function fromTransitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'from_state_id');
    }

    public function toTransitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'to_state_id');
    }
}
