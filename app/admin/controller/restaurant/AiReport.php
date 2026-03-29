<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\common\lib\restaurant\RestaurantAiService;
use think\facade\Db;
use think\facade\View;
use think\Response;

class AiReport extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            $tenantId = $this->resolveTenantId();
            if ($tenantId <= 0) {
                return $this->success('', ['total' => 0, 'list' => []]);
            }
            $list = Db::name('restaurant_ai_daily_report')->where('tenant_id', $tenantId)->order('report_date', 'desc')->limit(30)->select()->toArray();
            return $this->success('', ['total' => count($list), 'list' => $list]);
        }
        $isPlatform = $this->isPlatformAdmin() && $this->getTenantId() === 0;
        View::assign('is_platform', $isPlatform ? 1 : 0);
        $tenantId = $this->resolveTenantId();
        View::assign('tenant_id', $tenantId);
        if ($isPlatform) {
            $tenants = Db::name('tenant')->where('status', 1)->order('id', 'asc')->field('id,name')->select()->toArray();
            View::assign('tenants', $tenants);
        } else {
            View::assign('tenants', []);
        }
        View::assign('title', 'AI 经营日报');
        return $this->fetchWithLayout('restaurant/ai_report/index');
    }

    public function generate(): Response
    {
        $tenantId = $this->resolveTenantId();
        if ($tenantId <= 0) {
            return $this->error('tenant_id required');
        }
        $date = trim((string) $this->request->post('date', date('Y-m-d')));
        $reportDate = date('Y-m-d', strtotime($date));
        $since = strtotime($reportDate . ' 00:00:00');
        $until = strtotime($reportDate . ' 23:59:59');
        $orders = Db::name('restaurant_order')
            ->where('tenant_id', $tenantId)
            ->whereBetweenTime('create_time', $since, $until)
            ->field('id,total_amount,status,create_time')
            ->select()
            ->toArray();
        $items = Db::name('restaurant_order_item')
            ->alias('oi')
            ->leftJoin('restaurant_item i', 'i.id = oi.item_id AND i.tenant_id = oi.tenant_id')
            ->where('oi.tenant_id', $tenantId)
            ->whereBetweenTime('oi.create_time', $since, $until)
            ->field('oi.item_id, IFNULL(i.name, \'\') as name, SUM(oi.quantity) as qty, SUM(oi.amount) as amount')
            ->group('oi.item_id')
            ->order('amount desc')
            ->limit(50)
            ->select()
            ->toArray();
        $summary = $this->buildSummary($orders, $items, $reportDate);
        $content = null;
        try {
            $svc = (new RestaurantAiService($tenantId))->setModule('restaurant_daily_report', 'generate');
            $messages = [
                ['role' => 'system', 'content' => '你是餐饮经营分析助手。根据【数据摘要】写一段300-500字的经营日报，总结今日营业额、订单数、客单价、热销菜品、存在问题与建议。适度条理化，不要重复数据原文。'],
                ['role' => 'user', 'content' => $summary],
            ];
            $content = $svc->chat($messages, ['temperature' => 0.6, 'max_tokens' => 1200]);
        } catch (\Throwable $e) {
            $content = null;
        }
        if (!$content || trim((string) $content) === '') {
            $content = "【数据摘要】\n\n" . $summary . "\n\n（未配置餐饮AI或调用失败时显示以上数据摘要。配置后可自动生成文字总结。）";
        }
        $row = Db::name('restaurant_ai_daily_report')->where('tenant_id', $tenantId)->where('report_date', $reportDate)->find();
        $data = [
            'tenant_id' => $tenantId,
            'report_date' => $reportDate,
            'content' => $content,
            'summary' => mb_substr(strip_tags($content), 0, 200),
            'create_time' => time(),
        ];
        if ($row) {
            Db::name('restaurant_ai_daily_report')->where('id', (int) $row['id'])->update($data);
        } else {
            Db::name('restaurant_ai_daily_report')->insert($data);
        }
        return $this->success('已生成');
    }

    private function resolveTenantId(): int
    {
        $tenantId = $this->getTenantId();
        if ($tenantId > 0) return $tenantId;
        $p = $this->request->param('tenant_id');
        if ($p !== null && $p !== '') return (int) $p;
        $g = $this->request->get('tenant_id');
        if ($g !== null && $g !== '') return (int) $g;
        $post = $this->request->post('tenant_id');
        if ($post !== null && $post !== '') return (int) $post;
        return 0;
    }

    private function buildSummary(array $orders, array $items, string $date): string
    {
        $orderCount = count($orders);
        $revenue = 0.0;
        foreach ($orders as $o) {
            $revenue += (float) ($o['total_amount'] ?? 0);
        }
        $avg = $orderCount > 0 ? $revenue / $orderCount : 0.0;
        $top = array_slice($items, 0, 10);
        $lines = [];
        $lines[] = "日期：{$date}";
        $lines[] = "订单数：{$orderCount}";
        $lines[] = "营业额：¥" . number_format($revenue, 2);
        $lines[] = "客单价：¥" . number_format($avg, 2);
        $lines[] = "热销TOP：";
        foreach ($top as $t) {
            $nm = (string) ($t['name'] ?? ('#' . ($t['item_id'] ?? 0)));
            $qty = (float) ($t['qty'] ?? 0);
            $amt = (float) ($t['amount'] ?? 0);
            $lines[] = "- {$nm} 数量：" . rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.') . " 金额：¥" . number_format($amt, 2);
        }
        return implode("\n", $lines);
    }
}
