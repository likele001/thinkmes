<?php
declare(strict_types=1);

namespace app\common\service\workflow;

use think\facade\Db;

class WorkflowEngine
{
    public static function start(int $tenantId, string $moduleCode, int $businessId, int $initiatorId, array $options = []): array
    {
        $tenantId = max(0, $tenantId);
        $moduleCode = trim($moduleCode);
        if ($tenantId <= 0 || $moduleCode === '' || $businessId <= 0 || $initiatorId <= 0) {
            throw new \RuntimeException('参数错误');
        }

        $module = Db::name('wf_module')
            ->where('tenant_id', $tenantId)
            ->where('module_code', $moduleCode)
            ->find();
        if (!$module || (int) ($module['enabled'] ?? 0) !== 1) {
            throw new \RuntimeException('业务模块未启用工作流');
        }
        $definitionId = (int) ($module['definition_id'] ?? 0);
        if ($definitionId <= 0) {
            throw new \RuntimeException('业务模块未绑定流程定义');
        }

        $def = Db::name('wf_definition')
            ->where('id', $definitionId)
            ->where('tenant_id', $tenantId)
            ->find();
        if (!$def || (int) ($def['status'] ?? 0) !== 1) {
            throw new \RuntimeException('流程定义不存在或已禁用');
        }

        $nodes = Db::name('wf_node')
            ->where('tenant_id', $tenantId)
            ->where('definition_id', $definitionId)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        if (!$nodes) {
            throw new \RuntimeException('流程未配置审批节点');
        }

        $initiator = Db::name('admin')
            ->where('tenant_id', $tenantId)
            ->where('id', $initiatorId)
            ->where('status', 1)
            ->field('id,nickname,username')
            ->find();
        if (!$initiator) {
            throw new \RuntimeException('发起人不存在或已禁用');
        }
        $initiatorName = (string) ($initiator['nickname'] ?: $initiator['username'] ?: ('admin' . $initiatorId));

        $bizTitle = trim((string) ($options['business_title'] ?? ''));
        $bizData = [];
        $tableName = trim((string) ($module['table_name'] ?? ''));
        if ($tableName !== '') {
            $bizData = Db::name($tableName)->where('id', $businessId)->find() ?: [];
            if ($bizTitle === '') {
                $tf = trim((string) ($module['title_field'] ?? ''));
                if ($tf !== '' && isset($bizData[$tf])) {
                    $bizTitle = (string) $bizData[$tf];
                }
            }
        }
        if ($bizTitle === '') {
            $bizTitle = $moduleCode . '#' . $businessId;
        }

        $chosen = $options['initiator_select'] ?? [];
        if (!is_array($chosen)) {
            $chosen = [];
        }

        $first = self::pickNextNode($nodes, 0, $bizData);
        if (!$first) {
            throw new \RuntimeException('没有可触发的审批节点');
        }
        $firstSort = (int) ($first['sort'] ?? 0);
        $approvers = self::resolveApprovers($tenantId, $first, $initiatorId, $chosen[$firstSort] ?? null);
        if (!$approvers) {
            throw new \RuntimeException('未解析到审批人');
        }

        $now = time();
        return Db::transaction(function () use ($tenantId, $moduleCode, $businessId, $bizTitle, $definitionId, $first, $firstSort, $initiatorId, $initiatorName, $approvers, $options, $now, $tableName, $module) {
            $instanceNo = self::nextInstanceNo();

            $extra = json_encode([
                'options' => $options,
            ], JSON_UNESCAPED_UNICODE);
            if (!is_string($extra) || $extra === '') {
                $extra = '{}';
            }

            $instanceId = (int) Db::name('wf_instance')->insertGetId([
                'tenant_id' => $tenantId,
                'instance_no' => $instanceNo,
                'definition_id' => $definitionId,
                'module_code' => $moduleCode,
                'business_id' => $businessId,
                'business_title' => $bizTitle,
                'status' => 0,
                'current_node_id' => (int) ($first['id'] ?? 0),
                'current_sort' => $firstSort,
                'initiator_id' => $initiatorId,
                'initiator_name' => $initiatorName,
                'start_time' => $now,
                'end_time' => 0,
                'extra' => $extra,
                'create_time' => $now,
                'update_time' => $now,
            ]);

            foreach ($approvers as $a) {
                Db::name('wf_task')->insert([
                    'tenant_id' => $tenantId,
                    'instance_id' => $instanceId,
                    'node_id' => (int) ($first['id'] ?? 0),
                    'node_sort' => $firstSort,
                    'approval_mode' => (string) ($first['approval_mode'] ?? 'any_sign'),
                    'approver_type' => (string) ($first['approver_type'] ?? 'admin'),
                    'approver_id' => (int) ($a['id'] ?? 0),
                    'approver_name' => (string) ($a['name'] ?? ''),
                    'status' => 0,
                    'comment' => '',
                    'action_time' => 0,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
            }

            Db::name('wf_log')->insert([
                'tenant_id' => $tenantId,
                'instance_id' => $instanceId,
                'node_id' => (int) ($first['id'] ?? 0),
                'task_id' => 0,
                'action' => 'start',
                'actor_id' => $initiatorId,
                'actor_name' => $initiatorName,
                'to_approver_id' => 0,
                'to_approver_name' => '',
                'comment' => '',
                'create_time' => $now,
            ]);

            $inProgressValue = trim((string) ($module['in_progress_value'] ?? ''));
            $statusField = trim((string) ($module['status_field'] ?? ''));
            if ($tableName !== '' && $statusField !== '' && $inProgressValue !== '') {
                Db::name($tableName)->where('id', $businessId)->update([
                    $statusField => $inProgressValue,
                ]);
            }

            return [
                'instance_id' => $instanceId,
                'instance_no' => $instanceNo,
            ];
        });
    }

    public static function approve(int $tenantId, int $taskId, int $adminId, string $comment = ''): array
    {
        return self::handleDecision($tenantId, $taskId, $adminId, 'approve', $comment, 0);
    }

    public static function reject(int $tenantId, int $taskId, int $adminId, string $comment): array
    {
        $comment = trim($comment);
        if ($comment === '') {
            throw new \RuntimeException('拒绝意见不能为空');
        }
        return self::handleDecision($tenantId, $taskId, $adminId, 'reject', $comment, 0);
    }

    public static function transfer(int $tenantId, int $taskId, int $adminId, int $toAdminId, string $comment = ''): array
    {
        $tenantId = max(0, $tenantId);
        if ($tenantId <= 0 || $taskId <= 0 || $adminId <= 0 || $toAdminId <= 0) {
            throw new \RuntimeException('参数错误');
        }

        return Db::transaction(function () use ($tenantId, $taskId, $adminId, $toAdminId, $comment) {
            $task = Db::name('wf_task')
                ->where('tenant_id', $tenantId)
                ->where('id', $taskId)
                ->lock(true)
                ->find();
            if (!$task || (int) ($task['status'] ?? 0) !== 0) {
                throw new \RuntimeException('任务不存在或已处理');
            }
            if ((int) ($task['approver_id'] ?? 0) !== $adminId) {
                throw new \RuntimeException('无权操作');
            }

            $inst = Db::name('wf_instance')
                ->where('tenant_id', $tenantId)
                ->where('id', (int) ($task['instance_id'] ?? 0))
                ->lock(true)
                ->find();
            if (!$inst || (int) ($inst['status'] ?? 0) !== 0) {
                throw new \RuntimeException('实例已结束');
            }

            $to = Db::name('admin')
                ->where('tenant_id', $tenantId)
                ->where('id', $toAdminId)
                ->where('status', 1)
                ->field('id,nickname,username')
                ->find();
            if (!$to) {
                throw new \RuntimeException('转交目标不存在或已禁用');
            }
            $toName = (string) ($to['nickname'] ?: $to['username'] ?: ('admin' . $toAdminId));

            $now = time();
            Db::name('wf_task')->where('id', $taskId)->update([
                'status' => 4,
                'comment' => trim($comment),
                'action_time' => $now,
                'update_time' => $now,
            ]);

            $newTaskId = (int) Db::name('wf_task')->insertGetId([
                'tenant_id' => $tenantId,
                'instance_id' => (int) ($task['instance_id'] ?? 0),
                'node_id' => (int) ($task['node_id'] ?? 0),
                'node_sort' => (int) ($task['node_sort'] ?? 0),
                'approval_mode' => (string) ($task['approval_mode'] ?? 'any_sign'),
                'approver_type' => (string) ($task['approver_type'] ?? 'admin'),
                'approver_id' => $toAdminId,
                'approver_name' => $toName,
                'status' => 0,
                'comment' => '',
                'action_time' => 0,
                'create_time' => $now,
                'update_time' => $now,
            ]);

            $actorName = self::adminName($tenantId, $adminId);
            Db::name('wf_log')->insert([
                'tenant_id' => $tenantId,
                'instance_id' => (int) ($task['instance_id'] ?? 0),
                'node_id' => (int) ($task['node_id'] ?? 0),
                'task_id' => $taskId,
                'action' => 'transfer',
                'actor_id' => $adminId,
                'actor_name' => $actorName,
                'to_approver_id' => $toAdminId,
                'to_approver_name' => $toName,
                'comment' => trim($comment),
                'create_time' => $now,
            ]);

            return [
                'task_id' => $newTaskId,
                'instance_id' => (int) ($task['instance_id'] ?? 0),
            ];
        });
    }

    public static function withdraw(int $tenantId, int $instanceId, int $initiatorId, string $comment = ''): array
    {
        $tenantId = max(0, $tenantId);
        if ($tenantId <= 0 || $instanceId <= 0 || $initiatorId <= 0) {
            throw new \RuntimeException('参数错误');
        }
        $comment = trim($comment);

        return Db::transaction(function () use ($tenantId, $instanceId, $initiatorId, $comment) {
            $inst = Db::name('wf_instance')
                ->where('tenant_id', $tenantId)
                ->where('id', $instanceId)
                ->lock(true)
                ->find();
            if (!$inst || (int) ($inst['status'] ?? 0) !== 0) {
                throw new \RuntimeException('实例不存在或已结束');
            }
            if ((int) ($inst['initiator_id'] ?? 0) !== $initiatorId) {
                throw new \RuntimeException('无权撤回');
            }
            if ((int) ($inst['current_sort'] ?? 0) !== 1) {
                throw new \RuntimeException('当前阶段不可撤回');
            }

            $hasDone = Db::name('wf_task')
                ->where('tenant_id', $tenantId)
                ->where('instance_id', $instanceId)
                ->whereIn('status', [1, 2])
                ->count();
            if ($hasDone > 0) {
                throw new \RuntimeException('当前阶段不可撤回');
            }

            $now = time();
            Db::name('wf_instance')->where('id', $instanceId)->update([
                'status' => 3,
                'end_time' => $now,
                'update_time' => $now,
            ]);
            Db::name('wf_task')
                ->where('tenant_id', $tenantId)
                ->where('instance_id', $instanceId)
                ->where('status', 0)
                ->update([
                    'status' => 3,
                    'action_time' => $now,
                    'update_time' => $now,
                ]);

            $actorName = self::adminName($tenantId, $initiatorId);
            Db::name('wf_log')->insert([
                'tenant_id' => $tenantId,
                'instance_id' => $instanceId,
                'node_id' => (int) ($inst['current_node_id'] ?? 0),
                'task_id' => 0,
                'action' => 'withdraw',
                'actor_id' => $initiatorId,
                'actor_name' => $actorName,
                'to_approver_id' => 0,
                'to_approver_name' => '',
                'comment' => $comment,
                'create_time' => $now,
            ]);

            return ['instance_id' => $instanceId];
        });
    }

    private static function handleDecision(int $tenantId, int $taskId, int $adminId, string $action, string $comment, int $toAdminId): array
    {
        $tenantId = max(0, $tenantId);
        if ($tenantId <= 0 || $taskId <= 0 || $adminId <= 0) {
            throw new \RuntimeException('参数错误');
        }

        return Db::transaction(function () use ($tenantId, $taskId, $adminId, $action, $comment) {
            $task = Db::name('wf_task')
                ->where('tenant_id', $tenantId)
                ->where('id', $taskId)
                ->lock(true)
                ->find();
            if (!$task || (int) ($task['status'] ?? 0) !== 0) {
                throw new \RuntimeException('任务不存在或已处理');
            }
            if ((int) ($task['approver_id'] ?? 0) !== $adminId) {
                throw new \RuntimeException('无权操作');
            }

            $instanceId = (int) ($task['instance_id'] ?? 0);
            $inst = Db::name('wf_instance')
                ->where('tenant_id', $tenantId)
                ->where('id', $instanceId)
                ->lock(true)
                ->find();
            if (!$inst || (int) ($inst['status'] ?? 0) !== 0) {
                throw new \RuntimeException('实例已结束');
            }

            $now = time();
            $newStatus = $action === 'approve' ? 1 : 2;
            Db::name('wf_task')->where('id', $taskId)->update([
                'status' => $newStatus,
                'comment' => trim($comment),
                'action_time' => $now,
                'update_time' => $now,
            ]);

            $actorName = self::adminName($tenantId, $adminId);
            Db::name('wf_log')->insert([
                'tenant_id' => $tenantId,
                'instance_id' => $instanceId,
                'node_id' => (int) ($task['node_id'] ?? 0),
                'task_id' => $taskId,
                'action' => $action,
                'actor_id' => $adminId,
                'actor_name' => $actorName,
                'to_approver_id' => 0,
                'to_approver_name' => '',
                'comment' => trim($comment),
                'create_time' => $now,
            ]);

            if ($action === 'reject') {
                Db::name('wf_task')
                    ->where('tenant_id', $tenantId)
                    ->where('instance_id', $instanceId)
                    ->where('node_id', (int) ($task['node_id'] ?? 0))
                    ->where('status', 0)
                    ->update([
                        'status' => 3,
                        'action_time' => $now,
                        'update_time' => $now,
                    ]);

                Db::name('wf_instance')->where('id', $instanceId)->update([
                    'status' => 2,
                    'end_time' => $now,
                    'update_time' => $now,
                ]);
                self::callbackBusinessStatus($tenantId, $inst, 2);
                return ['instance_id' => $instanceId, 'status' => 2];
            }

            $mode = (string) ($task['approval_mode'] ?? 'any_sign');
            if ($mode === 'any_sign') {
                Db::name('wf_task')
                    ->where('tenant_id', $tenantId)
                    ->where('instance_id', $instanceId)
                    ->where('node_id', (int) ($task['node_id'] ?? 0))
                    ->where('status', 0)
                    ->update([
                        'status' => 3,
                        'action_time' => $now,
                        'update_time' => $now,
                    ]);
            } else {
                $pending = Db::name('wf_task')
                    ->where('tenant_id', $tenantId)
                    ->where('instance_id', $instanceId)
                    ->where('node_id', (int) ($task['node_id'] ?? 0))
                    ->where('status', 0)
                    ->count();
                if ($pending > 0) {
                    return ['instance_id' => $instanceId, 'status' => 0];
                }
            }

            $definitionId = (int) ($inst['definition_id'] ?? 0);
            $nodes = Db::name('wf_node')
                ->where('tenant_id', $tenantId)
                ->where('definition_id', $definitionId)
                ->order('sort', 'asc')
                ->select()
                ->toArray();
            $module = Db::name('wf_module')
                ->where('tenant_id', $tenantId)
                ->where('module_code', (string) ($inst['module_code'] ?? ''))
                ->find() ?: [];

            $bizData = [];
            $tableName = trim((string) ($module['table_name'] ?? ''));
            if ($tableName !== '') {
                $bizData = Db::name($tableName)->where('id', (int) ($inst['business_id'] ?? 0))->find() ?: [];
            }

            $next = self::pickNextNode($nodes, (int) ($inst['current_sort'] ?? 0), $bizData);
            if (!$next) {
                Db::name('wf_instance')->where('id', $instanceId)->update([
                    'status' => 1,
                    'end_time' => $now,
                    'current_node_id' => 0,
                    'current_sort' => 0,
                    'update_time' => $now,
                ]);
                self::callbackBusinessStatus($tenantId, $inst, 1);
                return ['instance_id' => $instanceId, 'status' => 1];
            }

            $extra = [];
            $extraRaw = (string) ($inst['extra'] ?? '');
            if ($extraRaw !== '') {
                $decoded = json_decode($extraRaw, true);
                if (is_array($decoded)) $extra = $decoded;
            }
            $chosen = $extra['options']['initiator_select'] ?? [];
            if (!is_array($chosen)) $chosen = [];

            $nextSort = (int) ($next['sort'] ?? 0);
            $approvers = self::resolveApprovers($tenantId, $next, (int) ($inst['initiator_id'] ?? 0), $chosen[$nextSort] ?? null);
            if (!$approvers) {
                throw new \RuntimeException('未解析到下一节点审批人');
            }

            Db::name('wf_instance')->where('id', $instanceId)->update([
                'current_node_id' => (int) ($next['id'] ?? 0),
                'current_sort' => $nextSort,
                'update_time' => $now,
            ]);

            foreach ($approvers as $a) {
                Db::name('wf_task')->insert([
                    'tenant_id' => $tenantId,
                    'instance_id' => $instanceId,
                    'node_id' => (int) ($next['id'] ?? 0),
                    'node_sort' => $nextSort,
                    'approval_mode' => (string) ($next['approval_mode'] ?? 'any_sign'),
                    'approver_type' => (string) ($next['approver_type'] ?? 'admin'),
                    'approver_id' => (int) ($a['id'] ?? 0),
                    'approver_name' => (string) ($a['name'] ?? ''),
                    'status' => 0,
                    'comment' => '',
                    'action_time' => 0,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
            }

            return ['instance_id' => $instanceId, 'status' => 0];
        });
    }

    private static function callbackBusinessStatus(int $tenantId, array $inst, int $finalStatus): void
    {
        $tenantId = max(0, $tenantId);
        $moduleCode = (string) ($inst['module_code'] ?? '');
        $module = Db::name('wf_module')->where('tenant_id', $tenantId)->where('module_code', $moduleCode)->find();
        if (!$module) {
            return;
        }
        $tableName = trim((string) ($module['table_name'] ?? ''));
        $statusField = trim((string) ($module['status_field'] ?? ''));
        if ($tableName === '' || $statusField === '') {
            return;
        }
        $bizId = (int) ($inst['business_id'] ?? 0);
        if ($bizId <= 0) {
            return;
        }

        $val = '';
        if ($finalStatus === 1) {
            $val = (string) ($module['approved_value'] ?? '');
        } elseif ($finalStatus === 2) {
            $val = (string) ($module['rejected_value'] ?? '');
        } else {
            return;
        }
        $val = trim($val);
        if ($val === '') {
            return;
        }

        $now = time();
        try {
            Db::name($tableName)->where('id', $bizId)->update([$statusField => $val]);
            Db::name('wf_log')->insert([
                'tenant_id' => $tenantId,
                'instance_id' => (int) ($inst['id'] ?? 0),
                'node_id' => 0,
                'task_id' => 0,
                'action' => 'callback_ok',
                'actor_id' => 0,
                'actor_name' => '',
                'to_approver_id' => 0,
                'to_approver_name' => '',
                'comment' => '',
                'create_time' => $now,
            ]);
        } catch (\Throwable $e) {
            Db::name('wf_instance')->where('id', (int) ($inst['id'] ?? 0))->update([
                'status' => 4,
                'update_time' => $now,
            ]);
            Db::name('wf_log')->insert([
                'tenant_id' => $tenantId,
                'instance_id' => (int) ($inst['id'] ?? 0),
                'node_id' => 0,
                'task_id' => 0,
                'action' => 'callback_fail',
                'actor_id' => 0,
                'actor_name' => '',
                'to_approver_id' => 0,
                'to_approver_name' => '',
                'comment' => $e->getMessage(),
                'create_time' => $now,
            ]);
        }
    }

    private static function pickNextNode(array $nodes, int $currentSort, array $bizData): ?array
    {
        foreach ($nodes as $n) {
            $sort = (int) ($n['sort'] ?? 0);
            if ($sort <= $currentSort) {
                continue;
            }
            if (self::matchNodeCondition($n, $bizData)) {
                return $n;
            }
        }
        return null;
    }

    private static function matchNodeCondition(array $node, array $bizData): bool
    {
        $itemsRaw = $node['condition_items'] ?? '';
        if ($itemsRaw === null || $itemsRaw === '') {
            return true;
        }
        $items = [];
        if (is_string($itemsRaw)) {
            $decoded = json_decode($itemsRaw, true);
            if (is_array($decoded)) $items = $decoded;
        } elseif (is_array($itemsRaw)) {
            $items = $itemsRaw;
        }
        if (!$items) {
            return true;
        }
        $logic = strtoupper(trim((string) ($node['condition_logic'] ?? 'AND')));
        if ($logic !== 'OR') $logic = 'AND';

        $results = [];
        foreach ($items as $it) {
            if (!is_array($it)) continue;
            $field = trim((string) ($it['field'] ?? ''));
            $op = trim((string) ($it['op'] ?? ''));
            $val = $it['value'] ?? null;
            if ($field === '' || $op === '') {
                continue;
            }
            if (!array_key_exists($field, $bizData)) {
                $results[] = false;
                continue;
            }
            $left = $bizData[$field];
            $results[] = self::compare($left, $op, $val);
        }
        if (!$results) {
            return true;
        }
        if ($logic === 'OR') {
            foreach ($results as $r) if ($r) return true;
            return false;
        }
        foreach ($results as $r) if (!$r) return false;
        return true;
    }

    private static function compare($left, string $op, $right): bool
    {
        $op = strtolower($op);
        if (is_string($left)) $leftStr = $left; else $leftStr = (string) $left;
        if (is_string($right)) $rightStr = $right; else $rightStr = (string) $right;

        if (in_array($op, ['gt', 'lt', 'eq', 'neq'], true)) {
            $ln = is_numeric($leftStr) ? (float) $leftStr : null;
            $rn = is_numeric($rightStr) ? (float) $rightStr : null;
            if ($ln !== null && $rn !== null) {
                if ($op === 'gt') return $ln > $rn;
                if ($op === 'lt') return $ln < $rn;
                if ($op === 'eq') return $ln == $rn;
                return $ln != $rn;
            }
            if ($op === 'eq') return $leftStr === $rightStr;
            if ($op === 'neq') return $leftStr !== $rightStr;
            return false;
        }

        if ($op === 'contains') {
            return $rightStr !== '' && strpos($leftStr, $rightStr) !== false;
        }
        if ($op === 'not_contains') {
            return $rightStr === '' || strpos($leftStr, $rightStr) === false;
        }
        return false;
    }

    private static function resolveApprovers(int $tenantId, array $node, int $initiatorId, $initiatorSelected): array
    {
        $type = trim((string) ($node['approver_type'] ?? 'admin'));
        $type = $type !== '' ? $type : 'admin';
        $ids = [];

        if ($type === 'admin') {
            $ids = self::decodeIds($node['approver_ids'] ?? '');
        } elseif ($type === 'role') {
            $roleIds = self::decodeIds($node['approver_ids'] ?? '');
            if ($roleIds) {
                $q = Db::name('admin')
                    ->where('tenant_id', $tenantId)
                    ->where('status', 1);
                $q->where(function ($sub) use ($roleIds) {
                    foreach ($roleIds as $rid) {
                        $sub->whereOrRaw('FIND_IN_SET(?, role_ids)', [$rid]);
                    }
                });
                $rows = $q->field('id,nickname,username')->select()->toArray();
                return array_values(array_filter(array_map(function ($r) {
                    $id = (int) ($r['id'] ?? 0);
                    if ($id <= 0) return null;
                    $name = (string) (($r['nickname'] ?? '') ?: ($r['username'] ?? '') ?: ('admin' . $id));
                    return ['id' => $id, 'name' => $name];
                }, $rows)));
            }
        } elseif ($type === 'dept_manager') {
            $mgrId = self::resolveDeptManagerId($tenantId, $initiatorId);
            if ($mgrId > 0) {
                $ids = [$mgrId];
            }
        } elseif ($type === 'initiator_select') {
            if (!is_array($initiatorSelected)) {
                $initiatorSelected = self::decodeIds($initiatorSelected);
            }
            $ids = array_values(array_filter(array_map('intval', (array) $initiatorSelected)));
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($v) {
            return $v > 0;
        })));
        if (!$ids) {
            return [];
        }

        $rows = Db::name('admin')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->whereIn('id', $ids)
            ->field('id,nickname,username')
            ->select()
            ->toArray();
        $map = [];
        foreach ($rows as $r) {
            $id = (int) ($r['id'] ?? 0);
            if ($id <= 0) continue;
            $map[$id] = (string) (($r['nickname'] ?? '') ?: ($r['username'] ?? '') ?: ('admin' . $id));
        }
        $out = [];
        foreach ($ids as $id) {
            if (!isset($map[$id])) continue;
            $out[] = ['id' => $id, 'name' => $map[$id]];
        }
        return $out;
    }

    private static function resolveDeptManagerId(int $tenantId, int $initiatorId): int
    {
        $hasAdminId = Db::query("SELECT COUNT(*) as c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fa_hr_employee' AND COLUMN_NAME = 'admin_id'");
        $hasMgr = Db::query("SELECT COUNT(*) as c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fa_hr_department' AND COLUMN_NAME = 'manager_id'");
        $c1 = (int) (($hasAdminId[0]['c'] ?? 0));
        $c2 = (int) (($hasMgr[0]['c'] ?? 0));
        if ($c1 <= 0 || $c2 <= 0) {
            return 0;
        }

        $emp = Db::name('hr_employee')
            ->where('tenant_id', $tenantId)
            ->where('admin_id', $initiatorId)
            ->find();
        if (!$emp) return 0;
        $deptId = (int) ($emp['department_id'] ?? 0);
        if ($deptId <= 0) return 0;
        $dept = Db::name('hr_department')
            ->where('tenant_id', $tenantId)
            ->where('id', $deptId)
            ->find();
        return (int) ($dept['manager_id'] ?? 0);
    }

    private static function decodeIds($raw): array
    {
        if (is_array($raw)) {
            return array_values(array_unique(array_filter(array_map('intval', $raw), function ($v) {
                return $v > 0;
            })));
        }
        $s = trim((string) $raw);
        if ($s === '') {
            return [];
        }
        $decoded = json_decode($s, true);
        if (is_array($decoded)) {
            return array_values(array_unique(array_filter(array_map('intval', $decoded), function ($v) {
                return $v > 0;
            })));
        }
        $parts = array_filter(array_map('trim', explode(',', $s)));
        return array_values(array_unique(array_filter(array_map('intval', $parts), function ($v) {
            return $v > 0;
        })));
    }

    private static function adminName(int $tenantId, int $adminId): string
    {
        $r = Db::name('admin')
            ->where('tenant_id', $tenantId)
            ->where('id', $adminId)
            ->field('nickname,username')
            ->find();
        if (!$r) {
            return 'admin' . $adminId;
        }
        $name = (string) (($r['nickname'] ?? '') ?: ($r['username'] ?? '') ?: ('admin' . $adminId));
        return $name !== '' ? $name : ('admin' . $adminId);
    }

    private static function nextInstanceNo(): string
    {
        $ymd = date('Ymd');
        $prefix = 'WF' . $ymd;
        $row = Db::name('wf_instance')
            ->where('instance_no', 'like', $prefix . '%')
            ->order('id', 'desc')
            ->field('instance_no')
            ->find();
        $seq = 0;
        if ($row && isset($row['instance_no'])) {
            $no = (string) $row['instance_no'];
            if (strlen($no) >= strlen($prefix) + 6) {
                $tail = substr($no, -6);
                if (ctype_digit($tail)) {
                    $seq = (int) $tail;
                }
            }
        }
        $seq++;
        if ($seq > 999999) $seq = 1;
        return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}

