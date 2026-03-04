<?php
declare(strict_types=1);

namespace app\admin\controller\ai;

use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * AI CRM 智能跟单：跟进建议、话术、客户意向判断
 */
class CrmFollow extends Base
{
    public function index(): string|Response
    {
        $err = $this->checkModule('crm_follow');
        if ($err !== null) {
            return $err;
        }
        if ($this->request->isAjax()) {
            $tenantId = $this->getTenantId();
            $list = Db::name('ai_crm_follow')
                ->where('tenant_id', $tenantId)
                ->order('id', 'desc')
                ->limit(50)
                ->select()
                ->toArray();
            return $this->success('', ['total' => count($list), 'list' => $list]);
        }
        View::assign('title', 'AI 智能跟单');
        return $this->fetchWithLayout('ai/crm_follow/index');
    }

    public function suggest(): Response
    {
        $err = $this->checkModule('crm_follow');
        if ($err !== null) {
            return $err;
        }
        return $this->safeAiCall(function () {
            $customerId = (int) $this->request->post('customer_id', 0);
            $opportunityId = (int) $this->request->post('opportunity_id', 0);
            if ($customerId <= 0 && $opportunityId <= 0) {
                return $this->error('请指定客户或商机');
            }
            if (Db::name('config')->where('name', 'app_crm_installed')->value('value') !== '1') {
                return $this->error('请先安装 CRM 模块');
            }
            $tenantId = $this->getTenantId();
            $context = $this->buildCrmContext($tenantId, $customerId, $opportunityId);
            if (empty($context)) {
                return $this->error('未找到客户或商机信息');
            }
            $svc = $this->getAiService()->setModule('crm_follow', 'suggest');
            $messages = [
                ['role' => 'system', 'content' => '你是CRM跟单助手。根据客户和商机信息，给出：1)跟进建议(下一步该做什么) 2)话术建议(沟通时可说的话) 3)客户意向判断(0-100分及理由)。只返回JSON：{"follow_advice":"...","script":"...","intent_score":数字,"intent_reason":"..."}'],
                ['role' => 'user', 'content' => $context],
            ];
            $result = $svc->chat($messages, ['temperature' => 0.5, 'max_tokens' => 800]);
            if (!$result) {
                return $this->error('AI 生成失败');
            }
            $parsed = json_decode($result, true);
            if (!is_array($parsed)) {
                $parsed = ['follow_advice' => $result, 'script' => '', 'intent_score' => 0, 'intent_reason' => ''];
            }
            $advice = trim((string) ($parsed['follow_advice'] ?? ''));
            $script = trim((string) ($parsed['script'] ?? ''));
            $intentScore = min(100, max(0, (float) ($parsed['intent_score'] ?? 0)));
            $intentReason = trim((string) ($parsed['intent_reason'] ?? ''));
            if ($advice !== '') {
                Db::name('ai_crm_follow')->insert([
                    'tenant_id' => $tenantId,
                    'customer_id' => $customerId,
                    'opportunity_id' => $opportunityId,
                    'suggestion_type' => 'follow_advice',
                    'content' => $advice,
                    'intent_score' => 0,
                    'create_time' => time(),
                ]);
            }
            if ($script !== '') {
                Db::name('ai_crm_follow')->insert([
                    'tenant_id' => $tenantId,
                    'customer_id' => $customerId,
                    'opportunity_id' => $opportunityId,
                    'suggestion_type' => 'script',
                    'content' => $script,
                    'intent_score' => 0,
                    'create_time' => time(),
                ]);
            }
            Db::name('ai_crm_follow')->insert([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'opportunity_id' => $opportunityId,
                'suggestion_type' => 'intent',
                'content' => $intentReason,
                'intent_score' => $intentScore,
                'create_time' => time(),
            ]);
            return $this->success('', [
                'follow_advice' => $advice,
                'script' => $script,
                'intent_score' => $intentScore,
                'intent_reason' => $intentReason,
            ]);
        });
    }

    protected function buildCrmContext(int $tenantId, int $customerId, int $opportunityId): string
    {
        $lines = [];
        if ($customerId > 0) {
            $customer = Db::name('crm_customer')->where('tenant_id', $tenantId)->where('id', $customerId)->find();
            if ($customer) {
                $lines[] = '客户：' . ($customer['name'] ?? '') . ' 级别=' . ($customer['level'] ?? 0) . ' 行业=' . ($customer['industry'] ?? '') . ' 来源=' . ($customer['source'] ?? '');
            }
        }
        if ($opportunityId > 0) {
            $opp = Db::name('crm_opportunity')->where('tenant_id', $tenantId)->where('id', $opportunityId)->find();
            if ($opp) {
                $lines[] = '商机：' . ($opp['name'] ?? '') . ' 阶段=' . ($opp['stage'] ?? '') . ' 金额=' . ($opp['amount'] ?? 0) . ' 预期日期=' . ($opp['expected_date'] ? date('Y-m-d', $opp['expected_date']) : '');
            }
        }
        $follows = Db::name('crm_follow')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($customerId, $opportunityId) {
                if ($customerId > 0 && $opportunityId > 0) {
                    $q->where('customer_id', $customerId)->whereOr('opportunity_id', $opportunityId);
                } elseif ($customerId > 0) {
                    $q->where('customer_id', $customerId);
                } elseif ($opportunityId > 0) {
                    $q->where('opportunity_id', $opportunityId);
                }
            })
            ->order('create_time', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        foreach ($follows as $f) {
            $lines[] = '跟进' . date('Y-m-d', $f['create_time'] ?? 0) . ' ' . ($f['type'] ?? '') . '：' . mb_substr((string) ($f['content'] ?? ''), 0, 200);
        }
        return implode("\n", $lines);
    }
}
