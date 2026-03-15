<?php
declare(strict_types=1);

namespace app\admin\controller\ai;

use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * AI 老板问答：自然语言查询生产/订单/员工数据（基于实时数据摘要回答）
 */
class Qa extends Base
{
    public function index(): string|Response
    {
        $err = $this->checkModule('qa');
        if ($err !== null) {
            return $err;
        }
        if ($this->request->isAjax()) {
            $tenantId = $this->getTenantId();
            $list = Db::name('ai_qa_history')
                ->where('tenant_id', $tenantId)
                ->order('id', 'desc')
                ->limit(50)
                ->select()
                ->toArray();
            return $this->success('', ['total' => count($list), 'list' => $list]);
        }
        View::assign('title', 'AI 老板问答');
        return $this->fetchWithLayout('ai/qa/index');
    }

    public function ask(): Response
    {
        $err = $this->checkModule('qa');
        if ($err !== null) {
            return $err;
        }
        return $this->safeAiCall(function () {
            $question = trim((string) $this->request->post('question', ''));
            if (empty($question)) {
                return $this->error('请输入问题');
            }
            $tenantId = $this->getTenantId();
            $admin = \think\facade\Session::get('admin_info');
            $adminId = (int) ($admin['id'] ?? 0);

            $dataContext = $this->buildQaDataContext($tenantId);
            $systemPrompt = '你是生产数据助手。下面【数据摘要】是当前系统的实时统计，请严格根据这些数据用自然语言回答用户问题。'
                . '用户可能问：今天报工情况、某员工今天的工作量、某工序或某产品的完成情况、生产计划完成情况、还有几个计划没完成等；请根据摘要中的「按员工」「按工序」「按产品」「工序+产品」「生产计划」等列表回答。'
                . '若摘要中无相关数据，可回答「当前数据中暂无相关记录」。不要回答「无法访问数据库」等。\n\n【数据摘要】\n' . $dataContext;

            $svc = $this->getAiService()->setModule('qa', 'ask');
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $question],
            ];
            $answer = $svc->chat($messages);
            if (!$answer) {
                return $this->error('AI 暂不可用');
            }
            Db::name('ai_qa_history')->insert([
                'tenant_id' => $tenantId,
                'admin_id' => $adminId,
                'question' => $question,
                'answer' => $answer,
                'create_time' => time(),
            ]);
            return $this->success('', ['answer' => $answer]);
        });
    }

    /**
     * 拉取当前租户的报工、订单等摘要，供 AI 依据数据回答
     */
    protected function buildQaDataContext(int $tenantId): string
    {
        $today = date('Y-m-d');
        $todayStart = strtotime($today . ' 00:00:00');
        $todayEnd = strtotime($today . ' 23:59:59');
        $lines = ["统计日期：{$today}（今日）"];

        try {
            // 今日报工：条数、计件总数、工时总数
            $reports = Db::name('mes_report')
                ->where('tenant_id', $tenantId)
                ->where('create_time', '>=', $todayStart)
                ->where('create_time', '<=', $todayEnd)
                ->field('user_id,quantity,work_hours')
                ->select()
                ->toArray();
            $reportCount = count($reports);
            $totalQty = 0;
            $totalHours = 0.0;
            $byUser = [];
            foreach ($reports as $r) {
                $q = (int) ($r['quantity'] ?? 0);
                $h = (float) ($r['work_hours'] ?? 0);
                $totalQty += $q;
                $totalHours += $h;
                $uid = (int) ($r['user_id'] ?? 0);
                if ($uid > 0) {
                    if (!isset($byUser[$uid])) {
                        $byUser[$uid] = ['qty' => 0, 'hours' => 0.0];
                    }
                    $byUser[$uid]['qty'] += $q;
                    $byUser[$uid]['hours'] += $h;
                }
            }
            $lines[] = "今日报工：共{$reportCount}条，计件总数={$totalQty}，工时总数=" . round($totalHours, 2);

            // 今日报工按员工（便于问「某员工今天工作量」）
            if (!empty($byUser)) {
                $userIds = array_keys($byUser);
                $userRows = Db::name('user')
                    ->where('tenant_id', $tenantId)
                    ->whereIn('id', $userIds)
                    ->field('id,nickname,username')
                    ->select()
                    ->toArray();
                $userNames = [];
                foreach ($userRows as $u) {
                    $n = trim((string) ($u['nickname'] ?? ''));
                    $userNames[(int) $u['id']] = $n !== '' ? $n : (trim((string) ($u['username'] ?? '')) ?: '员工' . $u['id']);
                }
                $byUserLines = [];
                foreach ($byUser as $uid => $v) {
                    $name = $userNames[$uid] ?? '员工' . $uid;
                    $byUserLines[] = $name . ' 计件' . $v['qty'] . ' 工时' . round($v['hours'], 2);
                }
                $lines[] = '今日报工按员工：' . implode('； ', $byUserLines);
            } else {
                $lines[] = '今日报工按员工：无';
            }

            // 今日报工按工序、按产品、工序+产品（需 join allocation/process/product_model）
            $reportDetail = Db::name('mes_report')
                ->alias('r')
                ->join('mes_allocation a', 'r.allocation_id = a.id')
                ->leftJoin('mes_process p', 'a.process_id = p.id')
                ->leftJoin('mes_product_model pm', 'a.model_id = pm.id')
                ->leftJoin('mes_product prod', 'pm.product_id = prod.id')
                ->where('r.tenant_id', $tenantId)
                ->where('r.create_time', '>=', $todayStart)
                ->where('r.create_time', '<=', $todayEnd)
                ->field('a.process_id,a.model_id,p.name as process_name,pm.name as model_name,prod.name as product_name,r.quantity')
                ->select()
                ->toArray();
            $byProcess = [];
            $byModel = [];
            $byProcessModel = [];
            foreach ($reportDetail as $rd) {
                $q = (int) ($rd['quantity'] ?? 0);
                $pid = (int) ($rd['process_id'] ?? 0);
                $mid = (int) ($rd['model_id'] ?? 0);
                $pName = trim((string) ($rd['process_name'] ?? '')) ?: ('工序' . $pid);
                $mName = trim((string) ($rd['model_name'] ?? '')) ?: ('型号' . $mid);
                $prodName = trim((string) ($rd['product_name'] ?? ''));
                $label = $mName . ($prodName ? '(' . $prodName . ')' : '');
                if ($pid > 0) {
                    $byProcess[$pName] = ($byProcess[$pName] ?? 0) + $q;
                }
                if ($mid > 0) {
                    $byModel[$label] = ($byModel[$label] ?? 0) + $q;
                }
                if ($pid > 0 && $mid > 0) {
                    $key = $pName . '|' . $label;
                    $byProcessModel[$key] = ($byProcessModel[$key] ?? 0) + $q;
                }
            }
            $processLines = [];
            foreach ($byProcess as $pName => $qty) {
                $processLines[] = $pName . ' 完成' . $qty;
            }
            $lines[] = '今日报工按工序：' . (empty($processLines) ? '无' : implode('； ', $processLines));
            $modelLines = [];
            foreach ($byModel as $label => $qty) {
                $modelLines[] = $label . ' 完成' . $qty;
            }
            $lines[] = '今日报工按产品（型号）：' . (empty($modelLines) ? '无' : implode('； ', $modelLines));
            $processModelLines = [];
            foreach ($byProcessModel as $k => $v) {
                $parts = explode('|', $k, 2);
                $processModelLines[] = ($parts[0] ?? '') . '+' . ($parts[1] ?? '') . ' 完成' . $v;
            }
            $lines[] = '今日报工按工序+产品：' . (empty($processModelLines) ? '无' : implode('； ', $processModelLines));

            // 订单概况：总数、按状态分布、今日新增
            $orderTotal = (int) Db::name('mes_order')->where('tenant_id', $tenantId)->count();
            $orderStatus = Db::name('mes_order')
                ->where('tenant_id', $tenantId)
                ->field('status')
                ->select()
                ->toArray();
            $statusCount = [];
            foreach ($orderStatus as $o) {
                $s = (int) ($o['status'] ?? 0);
                $statusCount[$s] = ($statusCount[$s] ?? 0) + 1;
            }
            $statusText = [0 => '待生产', 1 => '生产中', 2 => '已完成', 3 => '已关闭'];
            $statusDesc = [];
            foreach ($statusCount as $s => $cnt) {
                $statusDesc[] = ($statusText[$s] ?? "状态{$s}") . "{$cnt}个";
            }
            $lines[] = '订单总数：' . $orderTotal . '；按状态：' . implode('、', $statusDesc);

            $newToday = (int) Db::name('mes_order')
                ->where('tenant_id', $tenantId)
                ->where('create_time', '>=', $todayStart)
                ->where('create_time', '<=', $todayEnd)
                ->count();
            $lines[] = "今日新增订单：{$newToday}个";

            // 最近若干条订单（订单号、状态、总数量），便于回答「订单完成情况」
            $recentOrders = Db::name('mes_order')
                ->where('tenant_id', $tenantId)
                ->order('id', 'desc')
                ->limit(15)
                ->field('order_no,order_name,total_quantity,status')
                ->select()
                ->toArray();
            $orderLines = [];
            foreach ($recentOrders as $o) {
                $st = $statusText[$o['status'] ?? 0] ?? '未知';
                $orderLines[] = ($o['order_no'] ?? '') . ' 数量' . (int)($o['total_quantity'] ?? 0) . ' ' . $st;
            }
            $lines[] = '最近订单：' . (count($orderLines) ? implode('； ', $orderLines) : '无');

            // 生产计划：总数、按状态、未完成数量、最近计划（便于问「计划完成情况」「还有几个没完成」）
            try {
                $planStatusText = [0 => '待开始', 1 => '进行中', 2 => '已完成', 3 => '已暂停'];
                $planTotal = (int) Db::name('mes_production_plan')->where('tenant_id', $tenantId)->count();
                $planStatusRows = Db::name('mes_production_plan')
                    ->where('tenant_id', $tenantId)
                    ->field('status')
                    ->select()
                    ->toArray();
                $planStatusCount = [];
                foreach ($planStatusRows as $ps) {
                    $s = (int) ($ps['status'] ?? 0);
                    $planStatusCount[$s] = ($planStatusCount[$s] ?? 0) + 1;
                }
                $planStatusDesc = [];
                foreach ($planStatusCount as $s => $cnt) {
                    $planStatusDesc[] = ($planStatusText[$s] ?? "状态{$s}") . "{$cnt}个";
                }
                $planNotDone = $planTotal - (int) ($planStatusCount[2] ?? 0);
                $lines[] = '生产计划总数：' . $planTotal . '；按状态：' . (empty($planStatusDesc) ? '无' : implode('、', $planStatusDesc)) . '；未完成：' . $planNotDone . '个';
                $recentPlans = Db::name('mes_production_plan')
                    ->where('tenant_id', $tenantId)
                    ->order('id', 'desc')
                    ->limit(12)
                    ->field('plan_code,plan_name,total_quantity,completed_quantity,status')
                    ->select()
                    ->toArray();
                $planLines = [];
                foreach ($recentPlans as $pl) {
                    $st = $planStatusText[$pl['status'] ?? 0] ?? '未知';
                    $total = (int) ($pl['total_quantity'] ?? 0);
                    $done = (int) ($pl['completed_quantity'] ?? 0);
                    $pct = $total > 0 ? round($done / $total * 100, 0) : 0;
                    $planLines[] = ($pl['plan_name'] ?? $pl['plan_code'] ?? '') . ' 进度' . $pct . '% ' . $st;
                }
                $lines[] = '最近生产计划：' . (empty($planLines) ? '无' : implode('； ', $planLines));
            } catch (\Throwable $e) {
                $lines[] = '生产计划：暂无数据或表未安装';
            }
        } catch (\Throwable $e) {
            $lines[] = '（当前无 MES 报工/订单数据或表未安装，无法提供数据摘要）';
        }

        return implode("\n", $lines);
    }
}
