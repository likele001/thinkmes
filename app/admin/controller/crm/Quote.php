<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\ai\Base as AiBase;
use think\Response;

/**
 * CRM 报价：AI 智能报价
 */
class Quote extends AiBase
{
    /**
     * AI 生成报价说明或建议金额
     * POST: products = [{name, quantity, price?}, ...]
     */
    public function aiGenerate(): Response
    {
        return $this->safeAiCall(function () {
            $products = $this->request->post('products/a');
            if (empty($products) || !is_array($products)) {
                return $this->error('请传入产品列表 products');
            }
            $lines = [];
            foreach ($products as $i => $p) {
                $name = trim((string) ($p['name'] ?? ''));
                $qty = (float) ($p['quantity'] ?? 0);
                $price = isset($p['price']) ? (float) $p['price'] : null;
                if ($name === '') {
                    continue;
                }
                $line = $name . ' × ' . $qty;
                if ($price !== null && $price > 0) {
                    $line .= '，单价 ' . $price . ' 元';
                }
                $lines[] = $line;
            }
            if (empty($lines)) {
                return $this->error('请至少填写一项有效产品（名称+数量）');
            }
            $userContent = "请根据以下产品清单，生成一份简洁的报价说明（含总价建议与条款建议），并给出一个建议总金额（仅数字）。\n产品清单：\n" . implode("\n", $lines);
            $svc = $this->getAiService()->setModule('crm_quote', 'aiGenerate');
            $messages = [
                ['role' => 'system', 'content' => '你是销售报价助手。根据用户给的产品与数量（及可选单价），生成：1)一段简洁的报价说明（含总价与条款建议）2)建议总金额（仅一个数字，单位元）。若用户给了单价，请按数量与单价计算总价并纳入说明。回复格式：先是一段文字说明，最后一行单独写：建议总金额：数字'],
                ['role' => 'user', 'content' => $userContent],
            ];
            $result = $svc->chat($messages, ['temperature' => 0.4, 'max_tokens' => 800]);
            if ($result === null || $result === '') {
                return $this->error('AI 生成失败：' . $svc->getLastError());
            }
            $suggestedAmount = null;
            if (preg_match('/建议总金额[：:]\s*([\d.]+)/u', $result, $m)) {
                $suggestedAmount = (float) $m[1];
            }
            return $this->success('', [
                'content' => trim($result),
                'suggested_amount' => $suggestedAmount,
            ]);
        });
    }
}
