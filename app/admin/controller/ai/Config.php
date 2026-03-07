<?php
declare(strict_types=1);

namespace app\admin\controller\ai;

use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * AI 配置管理
 * 管理 API Key、供应商、限流等
 */
class Config extends Base
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            $tenantId = $this->getTenantId();
            $list = Db::name('ai_config')
                ->where('tenant_id', $tenantId)
                ->order('id', 'desc')
                ->select()
                ->toArray();
            foreach ($list as &$row) {
                $row['api_key'] = $row['api_key'] ? '***' . substr($row['api_key'], -4) : '';
            }
            return $this->success('', ['total' => count($list), 'list' => $list]);
        }
        View::assign('title', 'AI 配置');
        return $this->fetchWithLayout('ai/config/index');
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $data = [
                'tenant_id' => $this->getTenantId(),
                'provider' => trim((string) $this->request->post('provider', '')),
                'api_key' => trim((string) $this->request->post('api_key', '')),
                'api_base' => trim((string) $this->request->post('api_base', '')),
                'model' => trim((string) $this->request->post('model', 'gpt-3.5-turbo')),
                'speech_provider' => trim((string) $this->request->post('speech_provider', '')),
                'speech_api_key' => trim((string) $this->request->post('speech_api_key', '')),
                'rate_limit_per_day' => max(0, (int) $this->request->post('rate_limit_per_day', 1000)),
                'status' => (int) $this->request->post('status', 1),
                'create_time' => time(),
                'update_time' => time(),
            ];
            if (empty($data['provider'])) {
                return $this->error('请选择供应商');
            }
            $exist = Db::name('ai_config')->where('tenant_id', $data['tenant_id'])->where('provider', $data['provider'])->find();
            if ($exist) {
                return $this->error('该供应商已配置');
            }
            $id = Db::name('ai_config')->insertGetId($data);
            return $this->success('添加成功', ['id' => $id]);
        }
        View::assign('title', '添加 AI 配置');
        return $this->fetchWithLayout('ai/config/add');
    }

    public function edit(): string|Response
    {
        $id = (int) $this->request->param('id');
        $tenantId = $this->getTenantId();
        $row = Db::name('ai_config')->where('id', $id)->where('tenant_id', $tenantId)->find();
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $data = [
                'api_base' => trim((string) $this->request->post('api_base', '')),
                'model' => trim((string) $this->request->post('model', 'gpt-3.5-turbo')),
                'speech_provider' => trim((string) $this->request->post('speech_provider', '')),
                'rate_limit_per_day' => max(0, (int) $this->request->post('rate_limit_per_day', 1000)),
                'status' => (int) $this->request->post('status', 1),
                'update_time' => time(),
            ];
            $apiKey = trim((string) $this->request->post('api_key', ''));
            if ($apiKey !== '' && $apiKey !== '***') {
                $data['api_key'] = $apiKey;
            }
            $speechKey = trim((string) $this->request->post('speech_api_key', ''));
            if ($speechKey !== '' && $speechKey !== '***') {
                $data['speech_api_key'] = $speechKey;
            }
            Db::name('ai_config')->where('id', $id)->where('tenant_id', $tenantId)->update($data);
            return $this->success('保存成功');
        }
        $row['api_key'] = $row['api_key'] ? '***' : '';
        $row['speech_api_key'] = $row['speech_api_key'] ? '***' : '';
        View::assign('data', $row);
        View::assign('title', '编辑 AI 配置');
        return $this->fetchWithLayout('ai/config/edit');
    }

    /**
     * 测试大模型连接（不校验限流）
     * 支持传入 use_form=1 时使用当前表单值（编辑页保存前测试）
     */
    public function test(): Response
    {
        $id = (int) $this->request->post('id', 0);
        $tenantId = $this->getTenantId();
        $useForm = (bool) $this->request->post('use_form', false);
        if ($id <= 0) {
            return $this->error('请指定配置');
        }
        $row = Db::name('ai_config')->where('id', $id)->where('tenant_id', $tenantId)->find();
        if (!$row) {
            return $this->error('配置不存在');
        }
        $apiKey = $row['api_key'];
        $apiBase = $row['api_base'] ?? '';
        $model = $row['model'] ?? 'gpt-3.5-turbo';
        if ($useForm) {
            $formKey = trim((string) $this->request->post('api_key', ''));
            if ($formKey !== '' && $formKey !== '***') {
                $apiKey = $formKey;
            }
            $formBase = trim((string) $this->request->post('api_base', ''));
            if ($formBase !== '') {
                $apiBase = $formBase;
            }
            $formModel = trim((string) $this->request->post('model', ''));
            if ($formModel !== '') {
                $model = $formModel;
            }
        }
        if (empty($apiKey) || $apiKey === '***') {
            return $this->error('请填写有效的 API Key 后再测试');
        }
        if (empty($apiBase)) {
            $providerDefaults = [
                'zhipu' => 'https://open.bigmodel.cn/api/paas/v4',
                'aliyun' => 'https://dashscope.aliyuncs.com/compatible-mode/v1',
                'tencent' => 'https://api.hunyuan.cloud.tencent.com/v1',
                'xfyun' => 'https://spark-api.xf-yun.com/v1',
            ];
            $provider = strtolower(trim((string) ($row['provider'] ?? '')));
            $apiBase = $providerDefaults[$provider] ?? 'https://api.openai.com/v1';
        }
        $config = [
            'api_key' => $apiKey,
            'api_base' => $apiBase,
            'model' => $model,
        ];
        $svc = $this->getAiService()->setModule('ai_config', 'test');
        $result = $svc->chatWithConfig($config, [
            ['role' => 'user', 'content' => '你好，请回复：连接成功'],
        ]);
        if ($result !== null && $result !== '') {
            return $this->success('大模型连接正常', ['reply' => mb_substr(trim($result), 0, 200)]);
        }
        $lastError = $svc->getLastError();
        return $this->error($lastError ?: '连接失败，请检查 API Key 和 API 地址');
    }

    /**
     * 返回测试用音频（0.5 秒静音 WAV，16kHz 单声道）
     * 用于语音识别连接测试，阿里云需公网可访问
     */
    public function testAudio(): Response
    {
        $header = "RIFF" . pack('V', 16036) . "WAVEfmt \x10\x00\x00\x00\x01\x00\x01\x00\x80\x3e\x00\x00\x00\x7d\x00\x00\x02\x00\x10\x00data" . pack('V', 16000);
        $samples = str_repeat("\x00\x00", 8000);
        $wav = $header . $samples;
        return response($wav, 200, [
            'Content-Type'   => 'audio/wav',
            'Content-Length' => (string) strlen($wav),
        ]);
    }

    /**
     * 测试语音识别连接
     */
    public function testSpeech(): Response
    {
        $id = (int) $this->request->post('id', 0);
        $tenantId = $this->getTenantId();
        $useForm = (bool) $this->request->post('use_form', false);
        if ($id <= 0) {
            return $this->error('请指定配置');
        }
        $row = Db::name('ai_config')->where('id', $id)->where('tenant_id', $tenantId)->find();
        if (!$row) {
            return $this->error('配置不存在');
        }
        $speechProvider = trim((string) ($row['speech_provider'] ?? ''));
        $speechKey = trim((string) ($row['speech_api_key'] ?? ''));
        if ($useForm) {
            $formProvider = trim((string) $this->request->post('speech_provider', ''));
            if ($formProvider !== '') {
                $speechProvider = $formProvider;
            }
            $formKey = trim((string) $this->request->post('speech_api_key', ''));
            if ($formKey !== '' && $formKey !== '***') {
                $speechKey = $formKey;
            }
        }
        if (empty($speechProvider)) {
            return $this->error('请先选择语音识别供应商');
        }
        if (empty($speechKey) || $speechKey === '***') {
            return $this->error('请填写语音识别 Key 后再测试');
        }
        $speechProvider = strtolower($speechProvider);
        if ($speechProvider === 'aliyun') {
            $testUrl = 'https://dashscope.oss-cn-beijing.aliyuncs.com/samples/audio/paraformer/hello_world_male2.wav';
        } else {
            $domain = rtrim((string) \think\facade\Request::domain(), '/');
            $root = trim((string) \think\facade\Request::root(), '/');
            $testUrl = $domain . '/' . ($root ? trim($root, '/') . '/' : '') . 'ai/config/testAudio';
        }
        $config = array_merge($row, [
            'speech_provider' => $speechProvider,
            'speech_api_key' => $speechKey,
        ]);
        $svc = $this->getAiService()->setModule('ai_config', 'test_speech');
        $result = $svc->speechToText($testUrl, $config);
        if ($result !== null) {
            return $this->success('语音识别连接正常', ['text' => $result ?: '(静音或空识别)']);
        }
        $lastError = $svc->getLastError();
        return $this->error($lastError ?: '连接失败，请检查语音 Key 格式（阿里云：API Key；腾讯/百度/讯飞：竖线分隔）');
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids', '');
        if (empty($ids)) {
            return $this->error('请选择要删除的记录');
        }
        $tenantId = $this->getTenantId();
        $idArr = array_map('intval', explode(',', $ids));
        Db::name('ai_config')->whereIn('id', $idArr)->where('tenant_id', $tenantId)->delete();
        return $this->success('删除成功');
    }
}
