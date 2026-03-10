<?php
declare(strict_types=1);

namespace app\index\controller\wemedia;

use app\admin\model\WemediaConfigModel;
use app\common\lib\AiService;
use app\common\lib\AiVideoService;
use app\common\lib\DigitalHumanService;
use app\common\lib\TtsService;
use app\common\lib\WemediaImageService;
use app\common\lib\WemediaVideoMaker;
use app\common\model\WemediaCopyModel;
use app\common\model\WemediaTopicModel;
use app\common\model\WemediaVideoScriptModel;
use think\facade\View;
use think\Response;

/**
 * 短视频辅助（脚本）
 */
class Video extends BaseWemedia
{
    public function index(): string
    {
        View::assign('title', '短视频辅助');
        return $this->fetchWithLayout('wemedia/video/index');
    }

    public function add(): string
    {
        $fromCopy = (int) request()->get('from_copy', 0);
        View::assign('title', '添加脚本');
        View::assign('item', null);
        View::assign('itemJson', '{}');
        View::assign('topics', $this->getTopicOptions());
        View::assign('copies', $this->getCopyOptions());
        View::assign('from_copy_id', $fromCopy);
        return $this->fetchWithLayout('wemedia/video/edit');
    }

    public function edit(): string|Response
    {
        $id = (int) request()->get('id', 0);
        if ($id <= 0) return redirect('/index/wemedia/video/index');
        $item = WemediaVideoScriptModel::where('id', $id)
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find();
        if (!$item) return redirect('/index/wemedia/video/index');
        View::assign('title', '编辑脚本');
        View::assign('item', $item);
        View::assign('itemJson', json_encode($item->toArray()));
        View::assign('topics', $this->getTopicOptions());
        View::assign('copies', $this->getCopyOptions());
        View::assign('from_copy_id', 0);
        return $this->fetchWithLayout('wemedia/video/edit');
    }

    private function getTopicOptions(): array
    {
        return WemediaTopicModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('id', 'desc')->limit(200)->select()->toArray();
    }

    private function getCopyOptions(): array
    {
        return WemediaCopyModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('id', 'desc')->limit(100)->select()->toArray();
    }

    public function list(): Response
    {
        $keyword = trim((string) request()->get('keyword', ''));
        $page = max(1, (int) request()->get('page', 1));
        $limit = min(100, max(10, (int) request()->get('limit', 20)));
        $query = WemediaVideoScriptModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('title|script_content', '%' . $keyword . '%');
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['status_text'] = WemediaVideoScriptModel::statusText((int) ($row['status'] ?? 0));
        }
        unset($row);
        return $this->jsonSuccess('', ['total' => $total, 'list' => $list]);
    }

    public function save(): Response
    {
        $id = (int) request()->post('id', 0);
        $title = trim((string) request()->post('title', ''));
        $platform = trim((string) request()->post('platform', ''));
        $topic_id = (int) request()->post('topic_id', 0);
        $duration = (int) request()->post('duration', 0);
        $script_content = request()->post('script_content', '');
        $cover_path = trim((string) request()->post('cover_path', ''));
        $audio_path = trim((string) request()->post('audio_path', ''));
        $video_path = trim((string) request()->post('video_path', ''));
        $ai_video_path = trim((string) request()->post('ai_video_path', ''));
        $digital_human_path = trim((string) request()->post('digital_human_path', ''));
        $status = (int) request()->post('status', 0);
        if ($title === '') return $this->jsonError('请输入标题');
        $now = time();
        $data = [
            'title' => $title, 'platform' => $platform, 'topic_id' => $topic_id,
            'duration' => $duration, 'script_content' => $script_content, 'cover_path' => $cover_path,
            'audio_path' => $audio_path, 'video_path' => $video_path, 'ai_video_path' => $ai_video_path,
            'digital_human_path' => $digital_human_path,
            'status' => $status, 'update_time' => $now,
        ];
        if ($id > 0) {
            $row = WemediaVideoScriptModel::where('id', $id)->where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->find();
            if (!$row) return $this->jsonError('记录不存在');
            $row->save($data);
            return $this->jsonSuccess('保存成功', ['id' => $id]);
        }
        $data['tenant_id'] = $this->tenantId;
        $data['user_id'] = $this->userId;
        $data['create_time'] = $now;
        $m = new WemediaVideoScriptModel();
        $m->save($data);
        return $this->jsonSuccess('添加成功', ['id' => (int) $m->id]);
    }

    public function del(): Response
    {
        $id = (int) (request()->post('id', request()->get('id', 0)));
        if ($id <= 0) return $this->jsonError('参数错误');
        $row = WemediaVideoScriptModel::where('id', $id)->where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->find();
        if (!$row) return $this->jsonError('记录不存在');
        $row->delete();
        return $this->jsonSuccess('删除成功');
    }

    /** AI 生成分镜/口播脚本（按选题+时长+平台） */
    public function generateScript(): Response
    {
        $topicId = (int) request()->post('topic_id', 0);
        $duration = (int) request()->post('duration', 60);
        $platform = trim((string) request()->post('platform', ''));
        $title = trim((string) request()->post('title', ''));
        if ($topicId <= 0) {
            return $this->jsonError('请先选择关联选题');
        }
        $topic = WemediaTopicModel::where('id', $topicId)
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find();
        if (!$topic) {
            return $this->jsonError('选题不存在');
        }
        $config = $this->getWemediaAiTextConfig();
        if ($config === null) {
            return $this->jsonError('请先在后台「自媒体配置」中配置文案生成 AI');
        }
        $topicTitle = trim((string) $topic->title);
        $highlight = trim((string) ($topic->highlight ?? ''));
        $platformLabel = $platform ? $this->platformLabel($platform) : '不限';
        if ($duration <= 0) {
            $duration = 60;
        }
        $prompt = "根据以下选题生成一条短视频「口播/分镜脚本」，适合真人出镜或配音朗读。要求：总时长约{$duration}秒（按正常语速约" . round($duration / 2.5) . "字），分段标注【画面/镜头】与【旁白】，节奏清晰。直接输出脚本内容，不要标题。\n"
            . "选题：{$topicTitle}\n"
            . ($highlight !== '' ? "亮点：{$highlight}\n" : '')
            . "平台：{$platformLabel}\n"
            . ($title !== '' ? "视频标题（可作开头钩子）：{$title}\n" : '');
        $svc = new AiService(0, 0);
        $reply = $svc->chatWithConfig($config, [
            ['role' => 'user', 'content' => $prompt],
        ], ['temperature' => 0.6, 'max_tokens' => 1500]);
        if ($reply === null || $reply === '') {
            $err = $svc->getLastError();
            return $this->jsonError($err ?: 'AI 调用失败');
        }
        $modelUsed = $config['model_display'] ?? $config['model'] ?? '';
        return $this->jsonSuccess('', ['script_content' => trim($reply), 'model_used' => $modelUsed]);
    }

    /** 从文案生成口播脚本（文案正文 → 口播分镜） */
    public function generateFromCopy(): Response
    {
        $copyId = (int) request()->post('copy_id', 0);
        $content = trim((string) request()->post('content', ''));
        if ($copyId > 0) {
            $copy = WemediaCopyModel::where('id', $copyId)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$copy) {
                return $this->jsonError('文案不存在');
            }
            $content = trim((string) ($copy->content ?? ''));
            $title = trim((string) ($copy->title ?? ''));
        } else {
            $title = trim((string) request()->post('title', ''));
        }
        if ($content === '') {
            return $this->jsonError('请选择一篇文案或粘贴正文内容');
        }
        $config = $this->getWemediaAiTextConfig();
        if ($config === null) {
            return $this->jsonError('请先在后台「自媒体配置」中配置文案生成 AI');
        }
        $prompt = "将以下文案改写成「短视频口播/分镜脚本」，适合配音或真人朗读。要求：分段标注【画面/镜头】与【旁白】，节奏适合 1 分钟左右的短视频，可直接用于剪映/必剪等配音。直接输出脚本，不要解释。\n";
        if ($title !== '') {
            $prompt .= "标题：{$title}\n";
        }
        $prompt .= "正文：\n" . mb_substr($content, 0, 4000);
        $svc = new AiService(0, 0);
        $reply = $svc->chatWithConfig($config, [
            ['role' => 'user', 'content' => $prompt],
        ], ['temperature' => 0.5, 'max_tokens' => 2000]);
        if ($reply === null || $reply === '') {
            $err = $svc->getLastError();
            return $this->jsonError($err ?: 'AI 调用失败');
        }
        $modelUsed = $config['model_display'] ?? $config['model'] ?? '';
        return $this->jsonSuccess('', ['script_content' => trim($reply), 'model_used' => $modelUsed]);
    }

    /** 生成配音：按脚本内容 TTS 生成音频，写入 script 的 audio_path */
    public function generateAudio(): Response
    {
        $scriptId = (int) request()->post('script_id', 0);
        $scriptContent = trim((string) request()->post('script_content', ''));
        if ($scriptId > 0) {
            $row = WemediaVideoScriptModel::where('id', $scriptId)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$row) {
                return $this->jsonError('脚本不存在');
            }
            $scriptContent = trim((string) ($row->script_content ?? ''));
        }
        if ($scriptContent === '') {
            return $this->jsonError('请先填写或保存脚本内容');
        }
        $tts = new TtsService();
        $path = $tts->textToAudio($scriptContent);
        if ($path === null) {
            return $this->jsonError($tts->getLastError());
        }
        if ($scriptId > 0) {
            $row->save(['audio_path' => $path, 'update_time' => time()]);
        }
        return $this->jsonSuccess('配音生成成功', ['audio_path' => $path]);
    }

    /** 合成口播视频：音频 + 封面图 → MP4，写入 script 的 video_path */
    public function generateVideo(): Response
    {
        $scriptId = (int) request()->post('script_id', 0);
        $audioPath = trim((string) request()->post('audio_path', ''));
        $coverPath = trim((string) request()->post('cover_path', ''));
        if ($scriptId > 0) {
            $row = WemediaVideoScriptModel::where('id', $scriptId)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$row) {
                return $this->jsonError('脚本不存在');
            }
            if ($audioPath === '') {
                $audioPath = trim((string) ($row->audio_path ?? ''));
            }
            if ($coverPath === '') {
                $coverPath = trim((string) ($row->cover_path ?? ''));
            }
        }
        if ($audioPath === '') {
            return $this->jsonError('请先生成配音或填写音频路径');
        }
        if ($coverPath === '') {
            return $this->jsonError('请先设置封面图路径');
        }
        $maker = new WemediaVideoMaker();
        $path = $maker->makeSlideVideo($audioPath, $coverPath);
        if ($path === null) {
            return $this->jsonError($maker->getLastError());
        }
        if ($scriptId > 0) {
            $row->save(['video_path' => $path, 'update_time' => time()]);
        }
        return $this->jsonSuccess('口播视频合成成功', ['video_path' => $path]);
    }

    /** 文生图生成封面：根据提示词或脚本摘要调用图片生成接口，写入封面图路径 */
    public function generateCoverImage(): Response
    {
        $scriptId = (int) request()->post('script_id', 0);
        $prompt = trim((string) request()->post('prompt', ''));
        if ($scriptId > 0) {
            $row = WemediaVideoScriptModel::where('id', $scriptId)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$row) {
                return $this->jsonError('脚本不存在');
            }
            if ($prompt === '') {
                $prompt = mb_substr(strip_tags((string) ($row->script_content ?? '')), 0, 500);
            }
            if ($prompt === '') {
                $prompt = trim((string) ($row->title ?? ''));
            }
        }
        if ($prompt === '') {
            return $this->jsonError('请填写画面描述/提示词，或先保存脚本内容');
        }
        $svc = new WemediaImageService();
        $path = $svc->textToImage($prompt);
        if ($path === null) {
            return $this->jsonError($svc->getLastError());
        }
        if ($scriptId > 0) {
            $row->save(['cover_path' => $path, 'update_time' => time()]);
        }
        return $this->jsonSuccess('封面图已生成', ['cover_path' => $path]);
    }

    /** AI 图生视频：封面图 + 提示词（脚本摘要）→ 万相/可灵等生成视频，写入 script 的 ai_video_path */
    public function generateAiVideo(): Response
    {
        $scriptId = (int) request()->post('script_id', 0);
        $coverPath = trim((string) request()->post('cover_path', ''));
        $prompt = trim((string) request()->post('prompt', ''));
        $baseUrl = trim((string) request()->post('base_url', ''));
        if ($scriptId > 0) {
            $row = WemediaVideoScriptModel::where('id', $scriptId)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$row) {
                return $this->jsonError('脚本不存在');
            }
            if ($coverPath === '') {
                $coverPath = trim((string) ($row->cover_path ?? ''));
            }
            if ($prompt === '') {
                $prompt = mb_substr(strip_tags((string) ($row->script_content ?? '')), 0, 500);
            }
        }
        if ($coverPath === '') {
            return $this->jsonError('请先设置封面图路径');
        }
        if ($prompt === '') {
            return $this->jsonError('请填写画面描述/提示词，或先保存脚本内容');
        }
        if ($baseUrl === '') {
            $baseUrl = $this->request->domain();
        }
        @set_time_limit(360);
        $svc = new AiVideoService();
        $path = $svc->generateFromImage($coverPath, $prompt, $baseUrl);
        if ($path === null) {
            return $this->jsonError($svc->getLastError());
        }
        if ($scriptId > 0) {
            $row->save(['ai_video_path' => $path, 'update_time' => time()]);
        }
        return $this->jsonSuccess('AI 视频生成成功', ['ai_video_path' => $path]);
    }

    /** 文生视频：仅脚本/提示词 → 视频（无需封面图），约 1～5 分钟，若 502 请调大 nginx proxy_read_timeout */
    public function generateAiVideoFromText(): Response
    {
        $scriptId = (int) request()->post('script_id', 0);
        $prompt = trim((string) request()->post('prompt', ''));
        if ($scriptId > 0) {
            $row = WemediaVideoScriptModel::where('id', $scriptId)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$row) {
                return $this->jsonError('脚本不存在');
            }
            if ($prompt === '') {
                $prompt = mb_substr(strip_tags((string) ($row->script_content ?? '')), 0, 1500);
            }
        }
        if ($prompt === '') {
            return $this->jsonError('请填写画面描述/提示词，或先保存脚本内容');
        }
        @set_time_limit(360);
        $svc = new AiVideoService();
        $path = $svc->generateFromTextAuto($prompt);
        if ($path === null) {
            return $this->jsonError($svc->getLastError());
        }
        if ($scriptId > 0) {
            $row->save(['ai_video_path' => $path, 'update_time' => time()]);
        }
        return $this->jsonSuccess('文生视频已生成', ['ai_video_path' => $path]);
    }

    /** 数字人口播：脚本内容 → 数字人播报视频，写入 script 的 digital_human_path */
    public function generateDigitalHuman(): Response
    {
        $scriptId = (int) request()->post('script_id', 0);
        $text = trim((string) request()->post('script_content', ''));
        if ($scriptId > 0) {
            $row = WemediaVideoScriptModel::where('id', $scriptId)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$row) {
                return $this->jsonError('脚本不存在');
            }
            $text = trim((string) ($row->script_content ?? ''));
        }
        if ($text === '') {
            return $this->jsonError('请先填写或保存脚本内容');
        }
        @set_time_limit(420);
        $svc = new DigitalHumanService();
        $path = $svc->textToVideo($text);
        if ($path === null) {
            return $this->jsonError($svc->getLastError());
        }
        if ($scriptId > 0) {
            $row->save(['digital_human_path' => $path, 'update_time' => time()]);
        }
        return $this->jsonSuccess('数字人视频生成成功', ['digital_human_path' => $path]);
    }

    /** 图/文生成视频、口播成片说明 */
    public function videoGenerateTip(): Response
    {
        $tip = '当前支持：① AI 生成口播/分镜脚本；② 从文案生成口播脚本；'
            . '③ TTS 配音 + 封面图合成口播成片；④ AI 图生视频（封面图+提示词→万相等）；⑤ 数字人播报（文本→阿里云2D数字人视频）。';
        return $this->jsonSuccess('', ['tip' => $tip]);
    }

    private function platformLabel(string $value): string
    {
        $map = ['xiaohongshu' => '小红书', 'douyin' => '抖音', 'wechat' => '公众号', 'shipinhao' => '视频号'];
        return $map[$value] ?? $value;
    }

    private function getWemediaAiTextConfig(): ?array
    {
        $rows = WemediaConfigModel::where('tenant_id', 0)->select();
        $cfg = [];
        foreach ($rows as $r) {
            $cfg[$r->config_key] = $r->config_value ?? '';
        }
        $apiKey = trim((string) ($cfg[WemediaConfigModel::KEY_AI_TEXT_API_KEY] ?? ''));
        if ($apiKey === '' || str_starts_with($apiKey, '***')) {
            return null;
        }
        $apiBase = trim((string) ($cfg[WemediaConfigModel::KEY_AI_TEXT_API_BASE] ?? ''));
        if ($apiBase === '') {
            $provider = strtolower(trim((string) ($cfg[WemediaConfigModel::KEY_AI_TEXT_PROVIDER] ?? '')));
            $defaults = [
                'zhipu' => 'https://open.bigmodel.cn/api/paas/v4',
                'openai' => 'https://api.openai.com/v1',
                'aliyun' => 'https://dashscope.aliyuncs.com/compatible-mode/v1',
            ];
            $apiBase = $defaults[$provider] ?? 'https://api.openai.com/v1';
        }
        $model = trim((string) ($cfg[WemediaConfigModel::KEY_AI_TEXT_MODEL] ?? ''));
        if ($model === '') {
            $model = 'gpt-3.5-turbo';
        }
        $provider = strtolower(trim((string) ($cfg[WemediaConfigModel::KEY_AI_TEXT_PROVIDER] ?? '')));
        $providerLabels = [
            'doubao' => '豆包', 'zhipu' => '智谱', 'openai' => 'OpenAI', 'aliyun' => '阿里云',
            'xunfei_spark' => '讯飞星火', 'baidu' => '百度', 'azure' => 'Azure', 'other' => '其他',
        ];
        $modelDisplay = ($providerLabels[$provider] ?? $provider ?: '未知') . ' / ' . $model;
        return [
            'api_key' => $apiKey,
            'api_base' => $apiBase,
            'model' => $model,
            'model_display' => $modelDisplay,
        ];
    }
}
