<?php
declare(strict_types=1);

namespace app\index\controller\wemedia;

use app\admin\model\WemediaConfigModel;
use app\common\lib\AiService;
use app\common\model\WemediaTopicModel;
use think\facade\View;
use think\Response;

/**
 * 选题策划
 */
class Topic extends BaseWemedia
{
    public function index(): string
    {
        View::assign('title', '选题策划');
        return $this->fetchWithLayout('wemedia/topic/index');
    }

    public function add(): string
    {
        View::assign('title', '添加选题');
        View::assign('item', null);
        View::assign('itemJson', '{}');
        return $this->fetchWithLayout('wemedia/topic/edit');
    }

    public function edit(): string|Response
    {
        $id = (int) (request()->get('id', 0));
        if ($id <= 0) {
            return $this->jsonError('参数错误');
        }
        $item = WemediaTopicModel::where('id', $id)
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find();
        if (!$item) {
            return redirect('/index/wemedia/topic/index');
        }
        View::assign('title', '编辑选题');
        View::assign('item', $item);
        View::assign('itemJson', json_encode($item->toArray()));
        return $this->fetchWithLayout('wemedia/topic/edit');
    }

    /** 列表数据（Ajax） */
    public function list(): Response
    {
        $keyword = trim((string) request()->get('keyword', ''));
        $status = request()->get('status', '');
        $page = max(1, (int) request()->get('page', 1));
        $limit = min(100, max(10, (int) request()->get('limit', 20)));
        $query = WemediaTopicModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('title|highlight|field_keyword', '%' . $keyword . '%');
        }
        if ($status !== '') {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['status_text'] = WemediaTopicModel::statusText((int) ($row['status'] ?? 0));
        }
        unset($row);
        return $this->jsonSuccess('', ['total' => $total, 'list' => $list]);
    }

    /** 保存（新增/编辑） */
    public function save(): Response
    {
        $id = (int) (request()->post('id', 0));
        $title = trim((string) request()->post('title', ''));
        $platform = trim((string) request()->post('platform', ''));
        $field_keyword = trim((string) request()->post('field_keyword', ''));
        $highlight = trim((string) request()->post('highlight', ''));
        $remark = trim((string) request()->post('remark', ''));
        $status = (int) request()->post('status', 0);
        if ($title === '') {
            return $this->jsonError('请输入选题标题');
        }
        $now = time();
        if ($id > 0) {
            $row = WemediaTopicModel::where('id', $id)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$row) {
                return $this->jsonError('记录不存在');
            }
            $row->save([
                'title'        => $title,
                'platform'     => $platform,
                'field_keyword' => $field_keyword,
                'highlight'    => $highlight,
                'remark'       => $remark,
                'status'       => $status,
                'update_time'  => $now,
            ]);
            return $this->jsonSuccess('保存成功', ['id' => $id]);
        }
        $m = new WemediaTopicModel();
        $m->save([
            'tenant_id'     => $this->tenantId,
            'user_id'       => $this->userId,
            'title'         => $title,
            'platform'      => $platform,
            'field_keyword' => $field_keyword,
            'highlight'     => $highlight,
            'remark'        => $remark,
            'status'        => $status,
            'create_time'   => $now,
            'update_time'   => $now,
        ]);
        return $this->jsonSuccess('添加成功', ['id' => (int) $m->id]);
    }

    /** 删除 */
    public function del(): Response
    {
        $id = (int) (request()->post('id', request()->get('id', 0)));
        if ($id <= 0) {
            return $this->jsonError('参数错误');
        }
        $row = WemediaTopicModel::where('id', $id)
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find();
        if (!$row) {
            return $this->jsonError('记录不存在');
        }
        $row->delete();
        return $this->jsonSuccess('删除成功');
    }

    /** AI 生成选题（使用后台「自媒体配置」中的文案生成 AI） */
    public function generate(): Response
    {
        $platform = trim((string) request()->post('platform', ''));
        $field_keyword = trim((string) request()->post('field_keyword', ''));
        if ($field_keyword === '') {
            return $this->jsonError('请输入领域关键词');
        }

        $config = $this->getWemediaAiTextConfig();
        if ($config === null) {
            return $this->jsonError('请先在后台「自媒体配置」中配置文案生成用的 AI 模型与 API Key');
        }

        $platformLabel = $platform ? $this->platformLabel($platform) : '不限';
        $prompt = "你是一位自媒体选题助手。根据以下条件生成5个可落地的选题。\n"
            . "请严格按以下格式输出，每行一个选题，行内用制表符Tab分隔两列：第一列是选题标题（简短有力），第二列是该选题的亮点或卖点（一句话）。不要输出序号、不要其他解释，只输出这5行。\n"
            . "领域关键词：{$field_keyword}\n"
            . "平台：{$platformLabel}";

        $svc = new AiService(0, 0);
        $reply = $svc->chatWithConfig($config, [
            ['role' => 'user', 'content' => $prompt],
        ], ['temperature' => 0.7, 'max_tokens' => 800]);

        if ($reply === null || $reply === '') {
            $err = $svc->getLastError();
            return $this->jsonError($err ?: 'AI 调用失败，请检查后台自媒体配置中的 API Key 与地址');
        }

        $list = $this->parseTopicLines(trim($reply));
        $modelUsed = $config['model_display'] ?? $config['model'] ?? '';
        if (empty($list)) {
            return $this->jsonSuccess('', [
                'list' => [['title' => trim($reply), 'highlight' => '']],
                'tip' => '未能解析为多行选题，已按原文展示',
                'model_used' => $modelUsed,
            ]);
        }
        return $this->jsonSuccess('', ['list' => $list, 'model_used' => $modelUsed]);
    }

    /** 读取自媒体配置中的「文案生成」AI（tenant_id=0 框架级） */
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

    private function platformLabel(string $value): string
    {
        $map = ['xiaohongshu' => '小红书', 'douyin' => '抖音', 'wechat' => '公众号', 'shipinhao' => '视频号'];
        return $map[$value] ?? $value;
    }

    /** 解析 AI 返回的多行「标题\t亮点」为 list */
    private function parseTopicLines(string $text): array
    {
        $list = [];
        $lines = preg_split('/\r\n|\r|\n/', $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $line = preg_replace('/^\d+[\.\、]\s*/u', '', $line);
            $parts = preg_split('/\t+/', $line, 2);
            $title = trim($parts[0] ?? '');
            $highlight = trim($parts[1] ?? '');
            if ($title !== '') {
                $list[] = ['title' => $title, 'highlight' => $highlight];
            }
        }
        return $list;
    }
}
