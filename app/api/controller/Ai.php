<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\lib\AiService;
use think\facade\Db;
use think\Response;

class Ai extends BaseController
{
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    protected function getUserId(): int
    {
        return (int) ($this->request->userId ?? 0);
    }

    public function transcribe(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $audioUrl = (string) $this->request->post('audio_url', '');
        if ($audioUrl === '') {
            return $this->error('缺少音频 URL');
        }
        if (!function_exists('tenant_ai_available') || !tenant_ai_available($tenantId)) {
            return $this->error('AI 功能未启用或租户未开通');
        }
        $svc = new AiService($tenantId, $userId);
        $svc->setModule('voice_report', 'transcribe');
        $text = $svc->speechToText($audioUrl);
        if ($text === null) {
            return $this->error('语音识别失败');
        }
        return $this->success('ok', ['text' => $text]);
    }

    public function parse(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $text = (string) $this->request->post('text', '');
        if ($text === '') {
            return $this->error('缺少文字内容');
        }
        if (!function_exists('tenant_ai_available') || !tenant_ai_available($tenantId)) {
            return $this->error('AI 功能未启用或租户未开通');
        }
        $svc = new AiService($tenantId, $userId);
        $svc->setModule('voice_report', 'parse');
        $messages = [
            ['role' => 'system', 'content' => '将报工文字解析为JSON：{order_no,model_name,process_name,quantity,item_nos}'],
            ['role' => 'user', 'content' => $text],
        ];
        $result = $svc->chat($messages);
        if ($result === null) {
            return $this->error('解析失败');
        }
        $parsed = json_decode($result, true);
        return $this->success('ok', ['data' => $parsed ?: ['raw' => $result]]);
    }

    public function ask(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $question = (string) $this->request->post('question', '');
        if ($question === '') {
            return $this->error('请输入问题');
        }
        if (!function_exists('tenant_ai_available') || !tenant_ai_available($tenantId)) {
            return $this->error('AI 功能未启用或租户未开通');
        }
        $svc = new AiService($tenantId, $userId);
        $svc->setModule('qa', 'ask');
        $messages = [
            ['role' => 'system', 'content' => '你是生产数据助手。根据用户问题，用自然语言回答。若无法获取实时数据，说明需对接数据库。'],
            ['role' => 'user', 'content' => $question],
        ];
        $answer = $svc->chat($messages);
        if ($answer === null) {
            return $this->error('AI 暂不可用');
        }
        // 保存历史
        try {
            Db::name('ai_qa_history')->insert([
                'tenant_id' => $tenantId,
                'admin_id' => $userId,
                'question' => $question,
                'answer' => $answer,
                'create_time' => time(),
            ]);
        } catch (\Throwable $e) {
            \think\facade\Log::error('Ai API save history error: ' . $e->getMessage());
        }
        return $this->success('ok', ['answer' => $answer]);
    }
}
