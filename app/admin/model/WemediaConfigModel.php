<?php
declare(strict_types=1);

namespace app\admin\model;

use app\common\model\BaseModel as Model;

/**
 * 自媒体配置（后台）
 */
class WemediaConfigModel extends Model
{
    protected $name = 'wemedia_config';

    protected $type = [
        'tenant_id'   => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    const KEY_ENABLE = 'enable';
    const KEY_PLATFORMS = 'platforms';

    /** 自媒体独立 AI 配置（与工厂 AI 无关），每种用途：provider / model / api_key / api_base */
    const KEY_AI_TEXT_PROVIDER = 'ai_text_provider';
    const KEY_AI_TEXT_MODEL = 'ai_text_model';
    const KEY_AI_TEXT_API_KEY = 'ai_text_api_key';
    const KEY_AI_TEXT_API_BASE = 'ai_text_api_base';
    const KEY_AI_SAFE_PROVIDER = 'ai_safe_provider';
    const KEY_AI_SAFE_MODEL = 'ai_safe_model';
    const KEY_AI_SAFE_API_KEY = 'ai_safe_api_key';
    const KEY_AI_SAFE_API_BASE = 'ai_safe_api_base';
    const KEY_AI_IMAGE_PROVIDER = 'ai_image_provider';
    const KEY_AI_IMAGE_MODEL = 'ai_image_model';
    const KEY_AI_IMAGE_API_KEY = 'ai_image_api_key';
    const KEY_AI_IMAGE_API_BASE = 'ai_image_api_base';
    const KEY_AI_VIDEO_PROVIDER = 'ai_video_provider';
    const KEY_AI_VIDEO_MODEL = 'ai_video_model';
    const KEY_AI_VIDEO_API_KEY = 'ai_video_api_key';
    const KEY_AI_VIDEO_API_BASE = 'ai_video_api_base';

    /** TTS 语音合成（口播配音），与工厂语音识别无关 */
    const KEY_TTS_PROVIDER = 'tts_provider';
    const KEY_TTS_API_KEY = 'tts_api_key';
    const KEY_TTS_API_BASE = 'tts_api_base';
    const KEY_TTS_VOICE = 'tts_voice';

    /** AI 图/文生成视频（万相、可灵等），首帧图需公网 URL */
    const KEY_AI_VIDEO_BASE_URL = 'ai_video_base_url';
    /** 生成视频时长（秒），万相 API 支持 5 或 10 */
    const KEY_AI_VIDEO_DURATION = 'ai_video_duration';
    /** 文生视频分段字数，0=不拼接；>0 时按句拆分后多段生成再拼接（如 280≈每段10秒） */
    const KEY_AI_VIDEO_SEGMENT_CHARS = 'ai_video_segment_chars';

    /** 数字人：口播/数字人视频。阿里云需 TenantId、AppId；API Key 格式：阿里云 access_key_id|access_key_secret，腾讯云 secret_id|secret_key */
    const KEY_DIGITAL_HUMAN_PROVIDER = 'digital_human_provider';
    const KEY_DIGITAL_HUMAN_API_KEY = 'digital_human_api_key';
    const KEY_DIGITAL_HUMAN_API_BASE = 'digital_human_api_base';
    const KEY_DIGITAL_HUMAN_TENANT_ID = 'digital_human_tenant_id';
    const KEY_DIGITAL_HUMAN_APP_ID = 'digital_human_app_id';

    /** 供应商选项（自媒体场景：免费优先 + 低价备选，多用户/轻量/低成本） */
    public static function getAiProviderOptions(): array
    {
        return [
            '' => '— 不启用 —',
            // 文本：免费/低价
            'doubao' => '豆包 API（免费额度足，中文文案推荐）',
            'zhipu' => '智谱 ChatGLM（免费/本地）',
            'xunfei_spark' => '讯飞星火（免费额度多）',
            'openai' => 'OpenAI GPT-3.5（按量低价）',
            'baidu' => '百度文心一言（低价套餐）',
            'aliyun' => '阿里云通义（文本）',
            'azure' => 'Azure OpenAI',
            // 图片
            'tongyi_wanxiang' => '通义万相（免费额度/按量）',
            'stable_diffusion' => 'Stable Diffusion（本地免费）',
            'jianying' => '剪映 AI 配图',
            'midjourney' => 'MidJourney（订阅）',
            // 视频
            'videocrafter' => 'VideoCrafter（本地免费）',
            'runway' => 'Runway ML（免费额度）',
            'xunfei_hear' => '讯飞听见视频（免费额度）',
            'pika' => 'Pika Labs（按量）',
            'aliyun_video' => '阿里云视频生成（按量）',
            'other' => '其他（自定义）',
        ];
    }

    public static function getDefaultPlatforms(): string
    {
        return json_encode([
            ['value' => 'xiaohongshu', 'label' => '小红书'],
            ['value' => 'douyin', 'label' => '抖音'],
            ['value' => 'wechat', 'label' => '公众号'],
            ['value' => 'shipinhao', 'label' => '视频号'],
        ], JSON_UNESCAPED_UNICODE);
    }

    /** TTS 供应商选项（口播配音） */
    public static function getTtsProviderOptions(): array
    {
        return [
            '' => '— 不启用 —',
            'openai' => 'OpenAI TTS（tts-1/tts-1-hd，需 API Key）',
            'aliyun' => '阿里云 语音合成（需 AccessKey + AppKey）',
            'xunfei' => '讯飞 语音合成（需 APPID + APIKey + APISecret）',
            'tencent' => '腾讯云 语音合成',
            'other' => '其他（自定义）',
        ];
    }

    /** OpenAI TTS 音色 */
    public static function getTtsVoiceOptions(): array
    {
        return [
            'alloy' => 'alloy（中性）',
            'echo' => 'echo（男）',
            'fable' => 'fable（英）',
            'onyx' => 'onyx（男）',
            'nova' => 'nova（女）',
            'shimmer' => 'shimmer（女）',
        ];
    }

    /** AI 视频生成供应商（图生视频/文生视频） */
    public static function getAiVideoProviderOptions(): array
    {
        return [
            '' => '— 不启用 —',
            'aliyun_video' => '阿里云万相（图生视频，DashScope）',
            'kling' => '可灵 Kling（图/文生视频）',
            'runway' => 'Runway ML',
            'minimax' => 'MiniMax 海螺（图/文生视频）',
            'baidu' => '百度 文生视频',
            'tencent_video' => '腾讯云 智影/视频生成',
            'other' => '其他（自定义）',
        ];
    }

    /** AI 视频生成模型选项（按供应商常用模型，可复选或手动输入） */
    public static function getAiVideoModelOptions(): array
    {
        return [
            '' => '— 自动/默认 —',
            // 阿里云万相
            'wan2.2-i2v-plus' => '万相 wan2.2-i2v-plus（5秒）',
            'wan2.2-i2v-flash' => '万相 wan2.2-i2v-flash（5秒）',
            'wan2.6-i2v-flash' => '万相 wan2.6-i2v-flash（2-15秒）',
            'wan2.6-i2v' => '万相 wan2.6-i2v',
            'wan2.5-i2v-preview' => '万相 wan2.5-i2v-preview（5/10秒）',
            'wanx2.1-i2v-turbo' => '万相 wanx2.1-i2v-turbo（3-5秒）',
            'wanx2.1-i2v-plus' => '万相 wanx2.1-i2v-plus（5秒）',
            // 文生视频（T2V，无需首帧图）
            'wan2.2-t2v' => '万相 wan2.2-t2v（文生视频）',
            'wan2.2-t2v-plus' => '万相 wan2.2-t2v-plus（文生视频）',
            'wan2.6-t2v' => '万相 wan2.6-t2v（文生视频）',
            'wan2.5-t2v-preview' => '万相 wan2.5-t2v-preview（文生视频）',
            // 可灵/其他占位
            'kling-1.5' => '可灵 kling-1.5',
            'kling-1.6' => '可灵 kling-1.6',
            'runway-gen3' => 'Runway Gen3',
            'minimax-video' => 'MiniMax 海螺',
        ];
    }

    /** 数字人供应商 */
    public static function getDigitalHumanProviderOptions(): array
    {
        return [
            '' => '— 不启用 —',
            'aliyun' => '阿里云 2D 数字人（文本→播报视频）',
            'tencent' => '腾讯云 智能数智人（照片免训练）',
            'xunfei' => '讯飞 数字人',
            'other' => '其他（自定义）',
        ];
    }
}
