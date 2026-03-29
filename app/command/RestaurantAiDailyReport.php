<?php
namespace app\command;

use app\common\lib\restaurant\RestaurantAiService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

class RestaurantAiDailyReport extends Command
{
    protected function configure()
    {
        $this->setName('restaurant:ai-daily-report')
            ->setDescription('Generate restaurant AI daily report')
            ->addOption('date', null, \think\console\input\Option::VALUE_REQUIRED, 'Report date YYYY-mm-dd (default yesterday)')
            ->addOption('tenant', null, \think\console\input\Option::VALUE_REQUIRED, 'Tenant ID (optional)');
    }

    protected function execute(Input $input, Output $output)
    {
        $date = (string) ($input->getOption('date') ?: date('Y-m-d', strtotime('-1 day')));
        $reportDate = date('Y-m-d', strtotime($date));
        $tenantOnly = (int) ($input->getOption('tenant') ?: 0);

        $since = strtotime($reportDate . ' 00:00:00');
        $until = strtotime($reportDate . ' 23:59:59');

        $tenantIds = [];
        if ($tenantOnly > 0) {
            $tenantIds = [$tenantOnly];
        } else {
            $a = Db::name('restaurant_order')->whereBetweenTime('create_time', $since, $until)->distinct(true)->column('tenant_id');
            $b = Db::name('restaurant_ai_config')->where('status', 1)->distinct(true)->column('tenant_id');
            $tenantIds = array_values(array_filter(array_unique(array_merge($a ?: [], $b ?: []))));
        }

        if (!$tenantIds) {
            $output->writeln('no tenants');
            return 0;
        }

        foreach ($tenantIds as $tenantId) {
            $tenantId = (int) $tenantId;
            if ($tenantId <= 0) continue;
            try {
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
                $svc = (new RestaurantAiService($tenantId))->setModule('restaurant_daily_report', 'cron_generate');
                $messages = [
                    ['role' => 'system', 'content' => '你是餐饮经营分析助手。根据【数据摘要】写一段300-500字的经营日报，总结营业额、订单数、客单价、热销菜品、异常与建议。'],
                    ['role' => 'user', 'content' => $summary],
                ];
                $content = $svc->chat($messages, ['temperature' => 0.6, 'max_tokens' => 1200]);
                if (!$content || trim((string) $content) === '') {
                    $content = "【数据摘要】\n\n" . $summary . "\n\n（未配置餐饮AI或调用失败时显示以上数据摘要。）";
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
                $output->writeln("tenant {$tenantId}: ok");
            } catch (\Throwable $e) {
                $output->writeln("tenant {$tenantId}: fail " . $e->getMessage());
            }
        }
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

