<?php
declare(strict_types=1);

namespace app\index\controller\wemedia;

use app\admin\model\WemediaConfigModel;
use app\common\lib\AiService;
use app\common\model\WemediaCopyModel;
use app\common\model\WemediaTopicModel;
use think\facade\View;
use think\Response;

/**
 * 文案创作
 */
class Copy extends BaseWemedia
{
    public function index(): string
    {
        View::assign('title', '文案创作');
        return $this->fetchWithLayout('wemedia/copy/index');
    }

    public function add(): string
    {
        View::assign('title', '添加文案');
        View::assign('item', null);
        View::assign('itemJson', '{}');
        View::assign('topics', $this->getTopicOptions());
        return $this->fetchWithLayout('wemedia/copy/edit');
    }

    public function edit(): string|Response
    {
        $id = (int) request()->get('id', 0);
        if ($id <= 0) {
            return redirect('/index/wemedia/copy/index');
        }
        $item = WemediaCopyModel::where('id', $id)
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find();
        if (!$item) {
            return redirect('/index/wemedia/copy/index');
        }
        View::assign('title', '编辑文案');
        View::assign('item', $item);
        View::assign('itemJson', json_encode($item->toArray()));
        View::assign('topics', $this->getTopicOptions());
        return $this->fetchWithLayout('wemedia/copy/edit');
    }

    private function getTopicOptions(): array
    {
        return WemediaTopicModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('id', 'desc')
            ->limit(200)
            ->select()
            ->toArray();
    }

    public function list(): Response
    {
        $keyword = trim((string) request()->get('keyword', ''));
        $status = request()->get('status', '');
        $page = max(1, (int) request()->get('page', 1));
        $limit = min(100, max(10, (int) request()->get('limit', 20)));
        $query = WemediaCopyModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('title|content|tags', '%' . $keyword . '%');
        }
        if ($status !== '') {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['status_text'] = WemediaCopyModel::statusText((int) ($row['status'] ?? 0));
            $row['content_preview'] = $row['content'] ? mb_substr(strip_tags((string) $row['content']), 0, 80) . '...' : '';
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
        $content = request()->post('content', '');
        $tags = trim((string) request()->post('tags', ''));
        $status = (int) request()->post('status', 0);
        if ($title === '') {
            return $this->jsonError('请输入标题');
        }
        $now = time();
        $data = [
            'title'       => $title,
            'platform'    => $platform,
            'topic_id'    => $topic_id,
            'content'     => $content,
            'tags'        => $tags,
            'status'      => $status,
            'update_time' => $now,
        ];
        if ($id > 0) {
            $row = WemediaCopyModel::where('id', $id)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$row) {
                return $this->jsonError('记录不存在');
            }
            $row->save($data);
            return $this->jsonSuccess('保存成功', ['id' => $id]);
        }
        $data['tenant_id']   = $this->tenantId;
        $data['user_id']    = $this->userId;
        $data['create_time'] = $now;
        $m = new WemediaCopyModel();
        $m->save($data);
        return $this->jsonSuccess('添加成功', ['id' => (int) $m->id]);
    }

    public function del(): Response
    {
        $id = (int) (request()->post('id', request()->get('id', 0)));
        if ($id <= 0) {
            return $this->jsonError('参数错误');
        }
        $row = WemediaCopyModel::where('id', $id)
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find();
        if (!$row) {
            return $this->jsonError('记录不存在');
        }
        $row->delete();
        return $this->jsonSuccess('删除成功');
    }

    /** AI 根据选题生成文案标题（使用后台「自媒体配置」中的文案生成 AI） */
    public function generateTitle(): Response
    {
        $topicId = (int) request()->post('topic_id', 0);
        $platform = trim((string) request()->post('platform', ''));
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
            return $this->jsonError('请先在后台「自媒体配置」中配置文案生成用的 AI 模型与 API Key');
        }

        $topicTitle = trim((string) $topic->title);
        $highlight = trim((string) ($topic->highlight ?? ''));
        $platformLabel = $platform ? $this->platformLabel($platform) : '不限';
        $prompt = "根据以下选题生成5个文案标题，要求吸引点击、符合「{$platformLabel}」的文案风格与调性。重要：标题中不要出现平台名称（如抖音、小红书、公众号等），只输出标题本身，每行一个，不要序号、不要解释。\n"
            . "选题：{$topicTitle}\n"
            . ($highlight !== '' ? "亮点：{$highlight}\n" : '')
            . "目标平台（仅作风格参考，勿写入标题）：{$platformLabel}";

        $svc = new AiService(0, 0);
        $reply = $svc->chatWithConfig($config, [
            ['role' => 'user', 'content' => $prompt],
        ], ['temperature' => 0.7, 'max_tokens' => 500]);

        if ($reply === null || $reply === '') {
            $err = $svc->getLastError();
            return $this->jsonError($err ?: 'AI 调用失败，请检查后台自媒体配置');
        }

        $titles = $this->parseTitleLines(trim($reply));
        $modelUsed = $config['model_display'] ?? $config['model'] ?? '';
        if (empty($titles)) {
            $titles = [trim($reply)];
        }
        return $this->jsonSuccess('', ['list' => $titles, 'model_used' => $modelUsed]);
    }

    /** AI 根据选题（和标题）生成文案正文，支持字数、语气、情绪、心情 */
    public function generateContent(): Response
    {
        $topicId = (int) request()->post('topic_id', 0);
        $title = trim((string) request()->post('title', ''));
        $platform = trim((string) request()->post('platform', ''));
        $wordCount = trim((string) request()->post('word_count', ''));
        $tone = trim((string) request()->post('tone', ''));
        $emotion = trim((string) request()->post('emotion', ''));
        $mood = trim((string) request()->post('mood', ''));
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
            return $this->jsonError('请先在后台「自媒体配置」中配置文案生成用的 AI 模型与 API Key');
        }

        $topicTitle = trim((string) $topic->title);
        $highlight = trim((string) ($topic->highlight ?? ''));
        $platformLabel = $platform ? $this->platformLabel($platform) : '不限';
        $prompt = "根据以下选题写一篇自媒体文案正文。要求：符合平台调性、可分段、有感染力。直接输出正文内容，不要输出标题，不要加「正文：」等前缀。\n"
            . "选题：{$topicTitle}\n"
            . ($highlight !== '' ? "亮点：{$highlight}\n" : '')
            . "平台：{$platformLabel}\n";
        if ($title !== '') {
            $prompt .= "文案标题（正文需与标题呼应）：{$title}\n";
        }
        $constraints = [];
        if ($wordCount !== '') {
            $constraints[] = '字数控制在约' . $wordCount . '字';
        }
        if ($tone !== '') {
            $constraints[] = '语气：' . $tone;
        }
        if ($emotion !== '') {
            $constraints[] = '情绪：' . $emotion;
        }
        if ($mood !== '') {
            $constraints[] = '心情/氛围：' . $mood;
        }
        if ($constraints !== []) {
            $prompt .= '写作要求：' . implode('，', $constraints) . "。\n";
        }
        $prompt .= "请直接输出正文：";

        $svc = new AiService(0, 0);
        $reply = $svc->chatWithConfig($config, [
            ['role' => 'user', 'content' => $prompt],
        ], ['temperature' => 0.7, 'max_tokens' => 2500]);

        if ($reply === null || $reply === '') {
            $err = $svc->getLastError();
            return $this->jsonError($err ?: 'AI 调用失败，请检查后台自媒体配置');
        }

        $content = trim($reply);
        $modelUsed = $config['model_display'] ?? $config['model'] ?? '';
        return $this->jsonSuccess('', ['content' => $content, 'model_used' => $modelUsed]);
    }

    /** AI 推荐热门流量标签（抖音/公众号/小红书） */
    public function generateTags(): Response
    {
        $topicId = (int) request()->post('topic_id', 0);
        $title = trim((string) request()->post('title', ''));
        $platform = trim((string) request()->post('platform', ''));
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
        if ($platform === '') {
            return $this->jsonError('请先选择平台（抖音/公众号/小红书等）');
        }

        $config = $this->getWemediaAiTextConfig();
        if ($config === null) {
            return $this->jsonError('请先在后台「自媒体配置」中配置文案生成用的 AI 模型与 API Key');
        }

        $topicTitle = trim((string) $topic->title);
        $highlight = trim((string) ($topic->highlight ?? ''));
        $platformLabel = $this->platformLabel($platform);
        $prompt = "根据以下文案信息，为该平台推荐适合的「热门流量标签」（关键词），便于提高曝光和推荐。要求：符合{$platformLabel}当前常见热门标签风格，易被搜索和推荐，数量10-15个。只输出标签，用中文逗号分隔，不要序号、不要解释、不要引号。\n"
            . "选题：{$topicTitle}\n"
            . ($highlight !== '' ? "亮点：{$highlight}\n" : '')
            . ($title !== '' ? "文案标题：{$title}\n" : '')
            . "平台：{$platformLabel}";

        $svc = new AiService(0, 0);
        $reply = $svc->chatWithConfig($config, [
            ['role' => 'user', 'content' => $prompt],
        ], ['temperature' => 0.6, 'max_tokens' => 400]);

        if ($reply === null || $reply === '') {
            $err = $svc->getLastError();
            return $this->jsonError($err ?: 'AI 调用失败，请检查后台自媒体配置');
        }

        $reply = trim($reply);
        $reply = preg_replace('/^\d+[\.\、]\s*/um', '', $reply);
        $tagsList = array_values(array_filter(array_map('trim', preg_split('/[,，、\s]+/u', $reply))));
        $tagsStr = implode(',', array_map('trim', $tagsList));
        $modelUsed = $config['model_display'] ?? $config['model'] ?? '';
        return $this->jsonSuccess('', ['list' => $tagsList, 'tags' => $tagsStr, 'model_used' => $modelUsed]);
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
                'zhipu'   => 'https://open.bigmodel.cn/api/paas/v4',
                'openai'  => 'https://api.openai.com/v1',
                'aliyun'  => 'https://dashscope.aliyuncs.com/compatible-mode/v1',
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

    private function parseTitleLines(string $text): array
    {
        $list = [];
        $lines = preg_split('/\r\n|\r|\n/', $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $line = preg_replace('/^\d+[\.\、]\s*/u', '', $line);
            if ($line !== '') {
                $list[] = $line;
            }
        }
        return $list;
    }
}
