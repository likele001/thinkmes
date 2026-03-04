<?php
declare(strict_types=1);

namespace app\admin\controller\ai;

use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * AI 老板问答：自然语言查询生产/订单/员工数据
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

            $svc = $this->getAiService()->setModule('qa', 'ask');
            $messages = [
                ['role' => 'system', 'content' => '你是生产数据助手。根据用户问题，用自然语言回答。若无法获取实时数据，说明需对接数据库。'],
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
}
