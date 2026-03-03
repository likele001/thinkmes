<?php
declare(strict_types=1);

namespace app\admin\controller\ai;

use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * AI 报工异常检测：造假、不合理、重复、超时
 */
class Anomaly extends Base
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            $tenantId = $this->getTenantId();
            $list = Db::name('ai_anomaly')
                ->where('tenant_id', $tenantId)
                ->order('id', 'desc')
                ->limit(100)
                ->select()
                ->toArray();
            return $this->success('', ['total' => count($list), 'list' => $list]);
        }
        View::assign('title', '报工异常检测');
        return $this->fetchWithLayout('ai/anomaly/index');
    }

    public function scan(): Response
    {
        return $this->safeAiCall(function () {
            $tenantId = $this->getTenantId();
            $days = max(1, min(30, (int) $this->request->post('days', 7)));
            $since = strtotime("-{$days} days");
            try {
                $reports = Db::name('mes_report')
                    ->alias('r')
                    ->join('mes_allocation a', 'r.allocation_id = a.id')
                    ->join('mes_order o', 'a.order_id = o.id')
                    ->leftJoin('mes_product_model pm', 'a.model_id = pm.id')
                    ->leftJoin('mes_process p', 'a.process_id = p.id')
                    ->where('r.tenant_id', $tenantId)
                    ->where('r.create_time', '>=', $since)
                    ->field('r.id,r.user_id,r.work_type,r.quantity,r.work_hours,r.item_nos,r.status,r.create_time,o.order_no,pm.name as model_name,p.name as process_name,a.quantity as alloc_qty')
                    ->order('r.create_time', 'asc')
                    ->limit(200)
                    ->select()
                    ->toArray();
            } catch (\Throwable $e) {
                return $this->error('请先安装 MES 模块');
            }
            if (empty($reports)) {
                return $this->success('', ['count' => 0, 'anomalies' => []]);
            }
            $summary = $this->buildReportSummary($reports);
            $svc = $this->getAiService()->setModule('anomaly', 'scan');
            $messages = [
                ['role' => 'system', 'content' => '你是报工异常检测助手。分析以下报工数据，找出可能的异常：1)重复报工(同一人同一产品编号多次) 2)造假(数量异常、时间不合理) 3)超时(报工时间明显滞后) 4)不合理(数量远超分配、工时异常)。只返回JSON数组，每项格式：{"report_id":报工ID,"anomaly_type":"duplicate|fraud|timeout|unreasonable","score":0-100,"reason":"简短原因"}，无异常返回空数组[]。'],
                ['role' => 'user', 'content' => $summary],
            ];
            $result = $svc->chat($messages, ['temperature' => 0.3, 'max_tokens' => 2000]);
            if (!$result) {
                return $this->error('AI 分析失败');
            }
            $parsed = json_decode($result, true);
            return $this->saveAnomalies($tenantId, $parsed, $reports);
        });
    }

    protected function buildReportSummary(array $reports): string
    {
        $lines = [];
        foreach ($reports as $r) {
            $itemNos = $r['item_nos'] ?? '';
            if (is_string($itemNos)) {
                $dec = json_decode($itemNos, true);
                $itemNos = is_array($dec) ? implode(',', $dec) : $itemNos;
            }
            $lines[] = sprintf(
                'id=%d user=%d order=%s model=%s process=%s type=%s qty=%s hours=%s item_nos=%s status=%d time=%s',
                $r['id'],
                $r['user_id'],
                $r['order_no'] ?? '',
                $r['model_name'] ?? '',
                $r['process_name'] ?? '',
                $r['work_type'] ?? '',
                $r['quantity'] ?? 0,
                $r['work_hours'] ?? 0,
                $itemNos,
                $r['status'] ?? 0,
                date('Y-m-d H:i', $r['create_time'] ?? 0)
            );
        }
        return implode("\n", $lines);
    }

    protected function saveAnomalies(int $tenantId, $parsed, array $reports): Response
    {
        $reportIds = array_column($reports, 'id');
        $inserted = 0;
        if (is_array($parsed)) {
            foreach ($parsed as $item) {
                $reportId = (int) ($item['report_id'] ?? 0);
                if ($reportId <= 0 || !in_array($reportId, $reportIds, true)) {
                    continue;
                }
                $type = trim((string) ($item['anomaly_type'] ?? ''));
                if (!in_array($type, ['duplicate', 'fraud', 'timeout', 'unreasonable'], true)) {
                    $type = 'unreasonable';
                }
                $score = min(100, max(0, (float) ($item['score'] ?? 50)));
                $reason = trim((string) ($item['reason'] ?? ''));
                Db::name('ai_anomaly')->insert([
                    'tenant_id' => $tenantId,
                    'report_id' => $reportId,
                    'anomaly_type' => $type,
                    'score' => $score,
                    'detail' => json_encode($item, JSON_UNESCAPED_UNICODE),
                    'ai_reason' => $reason,
                    'status' => 0,
                    'create_time' => time(),
                ]);
                $inserted++;
            }
        }
        $list = Db::name('ai_anomaly')
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc')
            ->limit(50)
            ->select()
            ->toArray();
        return $this->success('扫描完成', ['count' => $inserted, 'anomalies' => $list]);
    }
}
