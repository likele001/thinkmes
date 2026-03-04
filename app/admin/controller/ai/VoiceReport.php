<?php
declare(strict_types=1);

namespace app\admin\controller\ai;

use think\facade\View;
use think\Response;

/**
 * 语音报工：语音→文字→自动填充报工
 */
class VoiceReport extends Base
{
    public function index(): string|Response
    {
        $err = $this->checkModule('voice_report');
        if ($err !== null) {
            return $err;
        }
        View::assign('title', '语音报工');
        return $this->fetchWithLayout('ai/voice_report/index');
    }

    public function transcribe(): Response
    {
        $err = $this->checkModule('voice_report');
        if ($err !== null) {
            return $err;
        }
        return $this->safeAiCall(function () {
            $audioUrl = trim((string) $this->request->post('audio_url', ''));
            if (empty($audioUrl)) {
                return $this->error('请提供语音文件');
            }
            $svc = $this->getAiService()->setModule('voice_report', 'transcribe');
            $text = $svc->speechToText($audioUrl);
            if ($text === null) {
                return $this->error('语音识别暂未接入，请先配置语音 API');
            }
            return $this->success('', ['text' => $text]);
        });
    }

    public function parse(): Response
    {
        $err = $this->checkModule('voice_report');
        if ($err !== null) {
            return $err;
        }
        return $this->safeAiCall(function () {
            $text = trim((string) $this->request->post('text', ''));
            if (empty($text)) {
                return $this->error('请提供文字内容');
            }
            $svc = $this->getAiService()->setModule('voice_report', 'parse');
            $messages = [
                ['role' => 'system', 'content' => '将报工文字解析为JSON：{order_no,model_name,process_name,quantity,item_nos}'],
                ['role' => 'user', 'content' => $text],
            ];
            $result = $svc->chat($messages);
            if (!$result) {
                return $this->error('解析失败');
            }
            $parsed = json_decode($result, true);
            return $this->success('', ['data' => $parsed ?: ['raw' => $result]]);
        });
    }
}
