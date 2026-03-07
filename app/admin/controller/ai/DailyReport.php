<?php
declare(strict_types=1);

namespace app\admin\controller\ai;

use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * AI 生产日报/周报：自动生成文字总结
 */
class DailyReport extends Base
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            $tenantId = $this->getTenantId();
            $list = Db::name('ai_daily_report')
                ->where('tenant_id', $tenantId)
                ->order('report_date', 'desc')
                ->limit(30)
                ->select()
                ->toArray();
            return $this->success('', ['total' => count($list), 'list' => $list]);
        }
        View::assign('title', 'AI 生产日报');
        return $this->fetchWithLayout('ai/daily_report/index');
    }

    public function generate(): Response
    {
        return $this->safeAiCall(function () {
            $type = trim((string) $this->request->post('type', 'daily'));
            $type = in_array($type, ['daily', 'weekly'], true) ? $type : 'daily';
            $date = trim((string) $this->request->post('date', date('Y-m-d')));
            $reportDate = date('Y-m-d', strtotime($date));
            $tenantId = $this->getTenantId();
            $since = $type === 'daily'
                ? strtotime($reportDate . ' 00:00:00')
                : (strtotime('monday this week', strtotime($reportDate)) ?: strtotime($reportDate));
            $until = $type === 'daily'
                ? strtotime($reportDate . ' 23:59:59')
                : $since + 7 * 86400 - 1;
            try {
                $reports = Db::name('mes_report')
                    ->alias('r')
                    ->join('mes_allocation a', 'r.allocation_id = a.id')
                    ->join('mes_order o', 'a.order_id = o.id')
                    ->leftJoin('mes_product_model pm', 'a.model_id = pm.id')
                    ->leftJoin('mes_process p', 'a.process_id = p.id')
                    ->where('r.tenant_id', $tenantId)
                    ->where('r.create_time', '>=', $since)
                    ->where('r.create_time', '<=', $until)
                    ->field('r.quantity,r.work_hours,r.work_type,o.order_no,pm.name as model_name,p.name as process_name')
                    ->select()
                    ->toArray();
                $orders = Db::name('mes_order')
                    ->where('tenant_id', $tenantId)
                    ->where('create_time', '>=', $since)
                    ->where('create_time', '<=', $until)
                    ->field('order_no,order_name,total_quantity,status')
                    ->select()
                    ->toArray();
            } catch (\Throwable $e) {
                return $this->error('请先安装 MES 模块');
            }
            $summary = $this->buildDailySummary($reports, $orders, $type, $reportDate);
            $svc = $this->getAiService()->setModule('daily_report', 'generate');
            $messages = [
                ['role' => 'system', 'content' => '你是生产日报撰写助手。根据以下数据生成简洁的生产' . ($type === 'daily' ? '日报' : '周报') . '，包含：1)总体完成情况 2)各订单/工序进度 3)存在的问题或建议。200-500字，条理清晰。'],
                ['role' => 'user', 'content' => $summary],
            ];
            $content = $svc->chat($messages, ['temperature' => 0.5, 'max_tokens' => 1500]);
            if (!$content) {
                return $this->error('AI 生成失败');
            }
            $summaryShort = mb_substr(preg_replace('/\s+/', ' ', $content), 0, 200);
            $exist = Db::name('ai_daily_report')
                ->where('tenant_id', $tenantId)
                ->where('report_type', $type)
                ->where('report_date', $reportDate)
                ->find();
            if ($exist) {
                Db::name('ai_daily_report')
                    ->where('id', $exist['id'])
                    ->update(['content' => $content, 'summary' => $summaryShort]);
            } else {
                Db::name('ai_daily_report')->insert([
                    'tenant_id' => $tenantId,
                    'report_type' => $type,
                    'report_date' => $reportDate,
                    'content' => $content,
                    'summary' => $summaryShort,
                    'create_time' => time(),
                ]);
            }
            return $this->success('生成成功', ['content' => $content]);
        });
    }

    protected function buildDailySummary(array $reports, array $orders, string $type, string $date): string
    {
        $totalQty = 0;
        $totalHours = 0.0;
        $byOrder = [];
        foreach ($reports as $r) {
            $totalQty += (int) ($r['quantity'] ?? 0);
            $totalHours += (float) ($r['work_hours'] ?? 0);
            $no = $r['order_no'] ?? '';
            if ($no !== '') {
                $byOrder[$no] = ($byOrder[$no] ?? 0) + (int) ($r['quantity'] ?? 0);
            }
        }
        $lines = [
            ($type === 'daily' ? '日报' : '周报') . '日期：' . $date,
            '报工汇总：计件总数=' . $totalQty . '，工时总数=' . round($totalHours, 2),
            '按订单：' . json_encode($byOrder, JSON_UNESCAPED_UNICODE),
            '新订单：' . json_encode(array_map(function ($o) {
                return ['order_no' => $o['order_no'] ?? '', 'name' => $o['order_name'] ?? '', 'total' => $o['total_quantity'] ?? 0, 'status' => $o['status'] ?? 0];
            }, $orders), JSON_UNESCAPED_UNICODE),
        ];
        return implode("\n", $lines);
    }
}
