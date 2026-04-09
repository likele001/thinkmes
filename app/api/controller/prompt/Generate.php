<?php
declare(strict_types=1);
namespace app\api\controller\prompt;

use app\common\controller\BaseController;
use app\admin\model\prompt\TemplateModel;
use app\admin\model\prompt\GenerationModel;
use app\admin\model\prompt\QuotaModel;
use app\common\lib\prompt\PromptAiService;
use app\common\lib\prompt\PromptMediaService;
use think\facade\Db;
use think\Response;

/**
 * C端 - AI生成接口（需 UserAuth）
 */
class Generate extends BaseController
{
    private function getUserId(): int
    {
        return (int)($this->request->userId ?? 0);
    }

    /** 查询我的额度 */
    public function quota(): Response
    {
        $userId = $this->getUserId();
        $quota  = QuotaModel::getOrCreate($userId);
        return $this->success('', [
            'free_quota'  => (int)$quota->free_quota,
            'paid_quota'  => (int)$quota->paid_quota,
            'total'       => $quota->getTotalAvailable(),
            'total_used'  => (int)$quota->total_used,
        ]);
    }

    /**
     * 执行生成
     * POST template_id, variables(JSON字符串或对象)
     */
    public function run(): Response
    {
        $userId     = $this->getUserId();
        $templateId = (int)$this->request->post('template_id', 0);
        $varsInput  = $this->request->post('variables', '{}');
        $promptTextInput = $this->request->post('prompt_text', null);
        $systemPromptInput = $this->request->post('system_prompt', null);

        if ($templateId <= 0) return $this->error('请选择模板');

        // 检查额度
        $quota = QuotaModel::getOrCreate($userId);
        if ($quota->getTotalAvailable() <= 0) {
            return $this->error('额度不足，请购买后继续使用', -1);
        }

        // 获取模板
        $tpl = TemplateModel::where('id', $templateId)->where('status', 1)->find();
        if (!$tpl) return $this->error('模板不存在或已禁用');

        // 解析变量
        if (is_string($varsInput)) {
            $vars = json_decode($varsInput, true) ?: [];
        } else {
            $vars = (array)$varsInput;
        }

        // 填充变量到提示词
        $promptTemplate = $promptTextInput !== null ? (string) $promptTextInput : (string) $tpl->prompt_text;
        if (trim($promptTemplate) === '') return $this->error('提示词内容不能为空');

        $promptText = $promptTemplate;
        foreach ($vars as $k => $v) {
            $promptText = str_replace('{' . $k . '}', (string)$v, $promptText);
        }
        $systemPrompt = $systemPromptInput !== null ? (string) $systemPromptInput : (string) ($tpl->system_prompt ?: '');
        if ($systemPrompt !== '') {
            foreach ($vars as $k => $v) {
                $systemPrompt = str_replace('{' . $k . '}', (string) $v, $systemPrompt);
            }
        }
        $maxWords = (int) ($tpl->output_words ?? 0);
        $userWords = (int) ($vars['word_count'] ?? 0);
        if ($userWords <= 0) {
            $userWords = (int) (Db::name('config')->where('name', 'prompt_output_words')->value('value') ?: 0);
        }
        if ($maxWords > 0 && $userWords > $maxWords) {
            $userWords = $maxWords;
        }
        if ($userWords > 0) {
            $promptText .= "\n\n请用中文输出，字数约{$userWords}字（可上下浮动20%），内容尽量具体可落地，不要只给要点。";
        }
        $extPrompt = trim((string) ($tpl->ext_prompt ?? ''));
        if ($extPrompt !== '') {
            foreach ($vars as $k => $v) {
                $extPrompt = str_replace('{' . $k . '}', (string) $v, $extPrompt);
            }
            $promptText .= "\n\n" . $extPrompt;
        } else {
            $aud = trim((string) ($vars['audience'] ?? ''));
            $mood = trim((string) ($vars['mood'] ?? ''));
            $emo = trim((string) ($vars['emotion'] ?? ''));
            $tone = trim((string) ($vars['tone'] ?? ''));
            if ($aud !== '' || $mood !== '' || $emo !== '' || $tone !== '') {
                $promptText .= "\n\n写作设定：\n"
                    . ($aud !== '' ? "- 受众群体：{$aud}\n" : '')
                    . ($mood !== '' ? "- 心情：{$mood}\n" : '')
                    . ($emo !== '' ? "- 情绪：{$emo}\n" : '')
                    . ($tone !== '' ? "- 语气风格：{$tone}\n" : '')
                    . "请确保整体表达符合上述设定。";
            }
        }

        // 消耗额度
        if (!$quota->consume()) {
            return $this->error('额度不足');
        }

        // 调用 AI
        $genRecord = [
            'user_id'        => $userId,
            'template_id'    => $templateId,
            'template_title' => $tpl->title,
            'input_text'     => $promptText,
            'variables_input' => is_array($varsInput) ? json_encode($varsInput, JSON_UNESCAPED_UNICODE) : $varsInput,
            'status'         => 0,
            'error_msg'      => '',
            'create_time'    => time(),
        ];

        try {
            $ai = new PromptAiService();
            $result = $ai->chat($promptText, $systemPrompt);

            $genRecord['output_text'] = $result['content'];
            $genRecord['tokens_used'] = $result['tokens'];
            $genRecord['cost_ms']     = $result['cost_ms'];
            $genRecord['status']      = 1;

            // 更新使用次数
            TemplateModel::where('id', $templateId)->inc('use_count')->update();

        } catch (\Throwable $e) {
            // AI 失败时退回额度
            if ($quota->free_quota > 0 || $quota->paid_quota > 0) {
                // 已消耗，但记录失败，不退回（可按业务调整）
            }
            $genRecord['error_msg'] = $e->getMessage();
            $genRecord['status']    = 0;
        }

        // 保存记录
        $gen = GenerationModel::create($genRecord);

        if ($genRecord['status'] === 0) {
            return $this->error('AI生成失败：' . $genRecord['error_msg']);
        }

        // 刷新额度
        $quota->refresh();
        return $this->success('生成成功', [
            'generation_id' => $gen->id,
            'content'       => $genRecord['output_text'],
            'tokens_used'   => $genRecord['tokens_used'],
            'quota'         => [
                'free_quota' => (int)$quota->free_quota,
                'paid_quota' => (int)$quota->paid_quota,
                'total'      => $quota->getTotalAvailable(),
            ],
        ]);
    }

    /** 我的生成历史 */
    public function history(): Response
    {
        $userId = $this->getUserId();
        $page   = max(1, (int)$this->request->get('page', 1));
        $limit  = min(50, max(1, (int)$this->request->get('limit', 20)));
        $fav    = (int)$this->request->get('favorite', 0);

        $query = GenerationModel::where('user_id', $userId)
            ->field('id, template_id, template_title, input_text, output_text, tokens_used, is_favorite, status, error_msg, create_time, image_status, image_url, image_error_msg, video_status, video_url, video_error_msg')
            ->order('id desc');
        if ($fav === 1) $query->where('is_favorite', 1);

        $total = $query->count();
        $list  = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /** 收藏/取消收藏 */
    public function favorite(): Response
    {
        $userId = $this->getUserId();
        $id     = (int)$this->request->post('id', 0);
        $row = GenerationModel::where('id', $id)->where('user_id', $userId)->find();
        if (!$row) return $this->error('记录不存在');
        $row->is_favorite = $row->is_favorite ? 0 : 1;
        $row->save();
        return $this->success($row->is_favorite ? '已收藏' : '已取消收藏', ['is_favorite' => $row->is_favorite]);
    }

    public function generateVideo(): Response
    {
        $userId = $this->getUserId();
        $id = (int) $this->request->post('generation_id', 0);
        if ($id <= 0) return $this->error('参数错误');

        $row = GenerationModel::where('id', $id)->where('user_id', $userId)->find();
        if (!$row) return $this->error('记录不存在');

        $duration = (int) $this->request->post('duration', 5);
        if ($duration !== 5 && $duration !== 10) $duration = 5;
        $quality = (string) $this->request->post('quality', 'speed');
        if ($quality !== 'speed' && $quality !== 'quality') $quality = 'speed';
        $withAudio = (bool) $this->request->post('with_audio', false);

        $promptInput = trim((string) $this->request->post('prompt', ''));
        if ($promptInput !== '') {
            $prompt = mb_substr(preg_replace("/\\s+/", " ", $promptInput), 0, 512);
        } else {
            $text = trim((string) ($row->output_text ?? ''));
            if ($text === '') return $this->error('生成内容为空');
            $prompt = mb_substr(preg_replace("/\\s+/", " ", $text), 0, 512);
        }

        try {
            $svc = new PromptMediaService();
            $task = $svc->createVideoTask($prompt, [
                'duration' => $duration,
                'quality' => $quality,
                'with_audio' => $withAudio,
            ]);

            $taskId = (string) ($task['task_id'] ?? '');
            if ($taskId === '') return $this->error('创建任务失败');

            GenerationModel::where('id', $id)->update([
                'video_task_id' => $taskId,
                'video_status' => (string) ($task['task_status'] ?: 'PROCESSING'),
                'video_duration' => $duration,
                'video_error_msg' => '',
            ]);

            return $this->success('任务已提交', [
                'video_task_id' => $taskId,
                'video_status' => (string) ($task['task_status'] ?: 'PROCESSING'),
            ]);
        } catch (\Throwable $e) {
            GenerationModel::where('id', $id)->update([
                'video_status' => 'FAIL',
                'video_error_msg' => mb_substr($e->getMessage(), 0, 500),
            ]);
            return $this->error('提交失败：' . $e->getMessage());
        }
    }

    public function queryVideoTask(): Response
    {
        $userId = $this->getUserId();
        $id = (int) $this->request->get('generation_id', 0);
        if ($id <= 0) return $this->error('参数错误');

        $row = GenerationModel::where('id', $id)->where('user_id', $userId)->find();
        if (!$row) return $this->error('记录不存在');

        $taskId = (string) ($row->video_task_id ?? '');
        if ($taskId === '') {
            return $this->success('', ['video_status' => 'EMPTY', 'video_url' => '']);
        }

        $status = (string) ($row->video_status ?? '');
        $url = (string) ($row->video_url ?? '');
        if ($status === 'SUCCESS' && $url !== '') {
            return $this->success('', ['video_status' => $status, 'video_url' => $url]);
        }

        try {
            $svc = new PromptMediaService();
            $res = $svc->queryTask($taskId);
            $remoteStatus = (string) ($res['task_status'] ?? '');
            $remoteUrl = (string) ($res['video_url'] ?? '');
            $err = (string) ($res['error_msg'] ?? '');

            $update = ['video_status' => $remoteStatus];
            if ($remoteUrl !== '') $update['video_url'] = $remoteUrl;
            if ($remoteStatus === 'FAIL' && $err !== '') $update['video_error_msg'] = mb_substr($err, 0, 500);
            GenerationModel::where('id', $id)->update($update);

            return $this->success('', [
                'video_status' => $remoteStatus,
                'video_url' => $remoteUrl,
            ]);
        } catch (\Throwable $e) {
            return $this->success('', [
                'video_status' => $status ?: 'PROCESSING',
                'video_url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function generateImage(): Response
    {
        $userId = $this->getUserId();
        $id = (int) $this->request->post('generation_id', 0);
        if ($id <= 0) return $this->error('参数错误');

        $row = GenerationModel::where('id', $id)->where('user_id', $userId)->find();
        if (!$row) return $this->error('记录不存在');

        $size = (string) $this->request->post('size', '1280x1280');
        $quality = (string) $this->request->post('quality', 'hd');

        $promptInput = trim((string) $this->request->post('prompt', ''));
        if ($promptInput !== '') {
            $prompt = mb_substr(preg_replace("/\\s+/", " ", $promptInput), 0, 512);
        } else {
            $text = trim((string) ($row->output_text ?? ''));
            if ($text === '') return $this->error('生成内容为空');
            $prompt = mb_substr(preg_replace("/\\s+/", " ", $text), 0, 512);
        }

        try {
            $svc = new PromptMediaService();
            $task = $svc->createImageTask($prompt, [
                'size' => $size,
                'quality' => $quality,
            ]);

            $taskId = (string) ($task['task_id'] ?? '');
            if ($taskId === '') return $this->error('创建任务失败');

            GenerationModel::where('id', $id)->update([
                'image_task_id' => $taskId,
                'image_status' => (string) ($task['task_status'] ?: 'PROCESSING'),
                'image_size' => $size,
                'image_error_msg' => '',
            ]);

            return $this->success('任务已提交', [
                'image_task_id' => $taskId,
                'image_status' => (string) ($task['task_status'] ?: 'PROCESSING'),
            ]);
        } catch (\Throwable $e) {
            GenerationModel::where('id', $id)->update([
                'image_status' => 'FAIL',
                'image_error_msg' => mb_substr($e->getMessage(), 0, 500),
            ]);
            return $this->error('提交失败：' . $e->getMessage());
        }
    }

    public function queryImageTask(): Response
    {
        $userId = $this->getUserId();
        $id = (int) $this->request->get('generation_id', 0);
        if ($id <= 0) return $this->error('参数错误');

        $row = GenerationModel::where('id', $id)->where('user_id', $userId)->find();
        if (!$row) return $this->error('记录不存在');

        $taskId = (string) ($row->image_task_id ?? '');
        if ($taskId === '') {
            return $this->success('', ['image_status' => 'EMPTY', 'image_url' => '']);
        }

        $status = (string) ($row->image_status ?? '');
        $url = (string) ($row->image_url ?? '');
        if ($status === 'SUCCESS' && $url !== '') {
            return $this->success('', ['image_status' => $status, 'image_url' => $url]);
        }

        try {
            $svc = new PromptMediaService();
            $res = $svc->queryTask($taskId);
            $remoteStatus = (string) ($res['task_status'] ?? '');
            $remoteUrl = (string) ($res['image_url'] ?? '');
            $err = (string) ($res['error_msg'] ?? '');

            $update = ['image_status' => $remoteStatus];
            if ($remoteUrl !== '') $update['image_url'] = $remoteUrl;
            if ($remoteStatus === 'FAIL' && $err !== '') $update['image_error_msg'] = mb_substr($err, 0, 500);
            GenerationModel::where('id', $id)->update($update);

            return $this->success('', [
                'image_status' => $remoteStatus,
                'image_url' => $remoteUrl,
            ]);
        } catch (\Throwable $e) {
            return $this->success('', [
                'image_status' => $status ?: 'PROCESSING',
                'image_url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
