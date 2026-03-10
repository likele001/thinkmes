<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\WemediaConfigModel;
use think\facade\View;
use think\Response;

/**
 * 自媒体工作流 - 后台配置（框架级独立应用，与租户无关，全站共用一套）
 */
class WemediaConfig extends Backend
{
    /** 自媒体为框架独立应用，不使用租户，固定为 0 */
    private function getWemediaTenantId(): int
    {
        return 0;
    }

    public function index(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->save();
        }
        $tenantId = $this->getWemediaTenantId();
        $rows = WemediaConfigModel::where('tenant_id', $tenantId)->select();
        $config = [];
        foreach ($rows as $r) {
            $config[$r->config_key] = $r->config_value;
        }
        $defaults = [
            'enable' => '',
            'platforms' => WemediaConfigModel::getDefaultPlatforms(),
            'ai_text_provider' => '', 'ai_text_model' => '', 'ai_text_api_key' => '', 'ai_text_api_base' => '',
            'ai_safe_provider' => '', 'ai_safe_model' => '', 'ai_safe_api_key' => '', 'ai_safe_api_base' => '',
            'ai_image_provider' => '', 'ai_image_model' => '', 'ai_image_api_key' => '', 'ai_image_api_base' => '',
            'ai_video_provider' => '', 'ai_video_model' => '', 'ai_video_api_key' => '', 'ai_video_api_base' => '',
            'ai_video_base_url' => '',
            'ai_video_duration' => '10',
            'ai_video_segment_chars' => '0',
            'tts_provider' => '', 'tts_api_key' => '', 'tts_api_base' => '', 'tts_voice' => 'alloy',
            'digital_human_provider' => '', 'digital_human_api_key' => '', 'digital_human_api_base' => '',
            'digital_human_tenant_id' => '', 'digital_human_app_id' => '',
        ];
        $config = array_merge($defaults, $config);
        if ($config['platforms'] === '') {
            $config['platforms'] = WemediaConfigModel::getDefaultPlatforms();
        }
        $this->maskApiKeysForDisplay($config);
        View::assign('title', '自媒体配置');
        View::assign('config', $config);
        View::assign('aiProviderOptions', WemediaConfigModel::getAiProviderOptions());
        View::assign('ttsProviderOptions', WemediaConfigModel::getTtsProviderOptions());
        View::assign('ttsVoiceOptions', WemediaConfigModel::getTtsVoiceOptions());
        View::assign('aiVideoProviderOptions', WemediaConfigModel::getAiVideoProviderOptions());
        View::assign('aiVideoModelOptions', WemediaConfigModel::getAiVideoModelOptions());
        View::assign('digitalHumanProviderOptions', WemediaConfigModel::getDigitalHumanProviderOptions());
        return $this->fetchWithLayout('wemedia/config');
    }

    /** 展示时 API Key 脱敏，避免在页面明文回显 */
    private function maskApiKeysForDisplay(array &$config): void
    {
        $keyFields = [
            'ai_text_api_key', 'ai_safe_api_key', 'ai_image_api_key', 'ai_video_api_key', 'tts_api_key', 'digital_human_api_key',
        ];
        foreach ($keyFields as $k) {
            if (!empty($config[$k])) {
                $config[$k] = '***' . substr($config[$k], -4);
            }
        }
    }

    private function save(): Response
    {
        $tenantId = $this->getWemediaTenantId();
        $now = time();
        $existing = [];
        foreach (WemediaConfigModel::where('tenant_id', $tenantId)->select() as $r) {
            $existing[$r->config_key] = $r->config_value;
        }
        $keys = [
            WemediaConfigModel::KEY_ENABLE => $this->request->post('enable', '0'),
            WemediaConfigModel::KEY_PLATFORMS => $this->request->post('platforms', '') ?: WemediaConfigModel::getDefaultPlatforms(),
        ];
        $aiKeyFields = [
            WemediaConfigModel::KEY_AI_TEXT_PROVIDER, WemediaConfigModel::KEY_AI_TEXT_MODEL,
            WemediaConfigModel::KEY_AI_TEXT_API_KEY, WemediaConfigModel::KEY_AI_TEXT_API_BASE,
            WemediaConfigModel::KEY_AI_SAFE_PROVIDER, WemediaConfigModel::KEY_AI_SAFE_MODEL,
            WemediaConfigModel::KEY_AI_SAFE_API_KEY, WemediaConfigModel::KEY_AI_SAFE_API_BASE,
            WemediaConfigModel::KEY_AI_IMAGE_PROVIDER, WemediaConfigModel::KEY_AI_IMAGE_MODEL,
            WemediaConfigModel::KEY_AI_IMAGE_API_KEY, WemediaConfigModel::KEY_AI_IMAGE_API_BASE,
            WemediaConfigModel::KEY_AI_VIDEO_PROVIDER, WemediaConfigModel::KEY_AI_VIDEO_MODEL,
            WemediaConfigModel::KEY_AI_VIDEO_API_KEY, WemediaConfigModel::KEY_AI_VIDEO_API_BASE,
            WemediaConfigModel::KEY_AI_VIDEO_BASE_URL,
            WemediaConfigModel::KEY_AI_VIDEO_DURATION,
            WemediaConfigModel::KEY_AI_VIDEO_SEGMENT_CHARS,
            WemediaConfigModel::KEY_TTS_PROVIDER, WemediaConfigModel::KEY_TTS_API_KEY,
            WemediaConfigModel::KEY_TTS_API_BASE, WemediaConfigModel::KEY_TTS_VOICE,
            WemediaConfigModel::KEY_DIGITAL_HUMAN_PROVIDER, WemediaConfigModel::KEY_DIGITAL_HUMAN_API_KEY,
            WemediaConfigModel::KEY_DIGITAL_HUMAN_API_BASE,
            WemediaConfigModel::KEY_DIGITAL_HUMAN_TENANT_ID, WemediaConfigModel::KEY_DIGITAL_HUMAN_APP_ID,
        ];
        foreach ($aiKeyFields as $k) {
            $v = trim((string) $this->request->post($k, ''));
            if (in_array($k, ['ai_text_api_key', 'ai_safe_api_key', 'ai_image_api_key', 'ai_video_api_key', 'tts_api_key', 'digital_human_api_key'], true)) {
                if ($v === '' || str_starts_with($v, '***')) {
                    $v = $existing[$k] ?? '';
                }
            }
            $keys[$k] = $v;
        }
        foreach ($keys as $k => $v) {
            $row = WemediaConfigModel::where('tenant_id', $tenantId)->where('config_key', $k)->find();
            if ($row) {
                $row->save(['config_value' => $v, 'update_time' => $now]);
            } else {
                WemediaConfigModel::create([
                    'tenant_id' => $tenantId,
                    'config_key' => $k,
                    'config_value' => $v,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
            }
        }
        return $this->success('保存成功');
    }
}
