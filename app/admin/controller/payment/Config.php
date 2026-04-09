<?php
declare(strict_types=1);

namespace app\admin\controller\payment;

use app\admin\controller\Backend;
use app\common\lib\payment\PaymentGatewayFactory;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 支付网关配置（平台或租户）
 */
class Config extends Backend
{
    /** 官方支付宝菜单入口 */
    public function configAlipay(): Response
    {
        if ($this->request->isPost()) {
            return $this->saveSingleGateway('official_alipay');
        }
        return $this->renderSingleGateway('official_alipay');
    }

    /** 官方微信支付菜单入口 */
    public function configWechat(): Response
    {
        if ($this->request->isPost()) {
            return $this->saveSingleGateway('official_wechat');
        }
        return $this->renderSingleGateway('official_wechat');
    }

    /** 虎皮椒(讯虎)菜单入口 */
    public function configXunhupay(): Response
    {
        if ($this->request->isPost()) {
            return $this->saveXunhupay();
        }
        return $this->renderXunhupay();
    }

    /** 易支付(8-pay)菜单入口 */
    public function configEpay(): Response
    {
        if ($this->request->isPost()) {
            return $this->saveEpay();
        }
        return $this->renderEpay();
    }

    private function redirectToConfigIndex(string $code): Response
    {
        $prefix = $this->getAdminUrlPrefix();
        $url = rtrim($this->request->domain(), '/') . $prefix . '/payment/config/index?code=' . urlencode($code);
        return redirect($url);
    }

    private function redirectToVisual(string $code): Response
    {
        $prefix = $this->getAdminUrlPrefix();
        $map = [
            'official_alipay' => '/payment/config_alipay',
            'official_wechat' => '/payment/config_wechat',
            'xunhupay' => '/payment/config_xunhupay',
            'epay' => '/payment/config_epay',
        ];
        $path = $map[$code] ?? '/payment/config/index?code=' . urlencode($code);
        $url = rtrim($this->request->domain(), '/') . $prefix . $path;
        return redirect($url);
    }

    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            $tenantId = $this->getTenantId();
            $query = Db::name('payment_gateway')->order('sort', 'asc')->order('id', 'asc');
            if ($tenantId > 0) {
                $query->where('tenant_id', $tenantId);
            } else {
                $tid = (int) $this->request->get('tenant_id', 0);
                if ($tid > 0) {
                    $query->where('tenant_id', $tid);
                }
            }
            $codeFilter = trim((string) $this->request->get('code', ''));
            if ($codeFilter !== '') {
                if ($codeFilter === 'xunhupay') {
                    $query->whereIn('code', ['xunhupay', 'xunhupay_alipay', 'xunhupay_wechat']);
                } else {
                    $query->where('code', $codeFilter);
                }
            }
            $list = $query->select()->toArray();
            $names = PaymentGatewayFactory::allNames();
            foreach ($list as &$row) {
                $row['config'] = $row['config'] ? json_decode($row['config'], true) : [];
                $row['code_name'] = $names[$row['code']] ?? $row['code'];
            }
            return $this->success('', ['total' => count($list), 'list' => $list]);
        }
        $codeFilter = trim((string) $this->request->get('code', ''));
        $legacy = (int) $this->request->get('legacy', 0);
        if ($legacy !== 1 && in_array($codeFilter, ['official_alipay', 'official_wechat', 'xunhupay', 'epay'], true)) {
            return $this->redirectToVisual($codeFilter);
        }
        $titleMap = [
            'official_alipay' => '官方支付宝',
            'official_wechat' => '官方微信支付',
            'xunhupay'        => '虎皮椒(讯虎)',
            'epay'            => '易支付(8-pay)',
        ];
        View::assign('title', $codeFilter !== '' ? ($titleMap[$codeFilter] ?? '支付网关') : '支付网关');
        View::assign('code_filter', $codeFilter);
        return $this->fetchWithLayout('payment/config/index');
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        $codePreselect = trim((string) $this->request->get('code', ''));
        if ($codePreselect === 'xunhupay') {
            $codePreselect = 'xunhupay_alipay';
        }
        View::assign('gatewayNames', PaymentGatewayFactory::allNames());
        View::assign('code_preselect', $codePreselect);
        View::assign('title', '添加支付网关');
        return $this->fetchWithLayout('payment/config/add');
    }

    public function addPost(): Response
    {
        $data = $this->request->post();
        $code = trim((string) ($data['code'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));
        if ($code === '' || $name === '') {
            return $this->error('请选择网关类型并填写名称');
        }
        $config = $data['config'] ?? [];
        if (is_string($config)) {
            $config = $config !== '' ? json_decode($config, true) : [];
        }
        $config = is_array($config) ? $config : [];
        $tenantId = $this->getTenantId();
        $insert = [
            'tenant_id'   => $tenantId,
            'code'        => $code,
            'name'        => $name,
            'config'      => json_encode($config, JSON_UNESCAPED_UNICODE),
            'enabled'     => isset($data['enabled']) ? (int) $data['enabled'] : 1,
            'sort'        => (int) ($data['sort'] ?? 0),
            'create_time' => time(),
            'update_time' => time(),
        ];
        Db::name('payment_gateway')->insert($insert);
        return $this->success('添加成功');
    }

    public function edit(): string|Response
    {
        $id = (int) $this->request->param('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = Db::name('payment_gateway')->where('id', $id)->find();
        if (!$row) {
            return $this->error('记录不存在');
        }
        $tenantId = $this->getTenantId();
        if ($tenantId > 0 && (int) $row['tenant_id'] !== $tenantId) {
            return $this->error('无权限');
        }
        if ($this->request->isPost()) {
            return $this->editPost($id);
        }
        $row['config'] = $row['config'] ? json_decode($row['config'], true) : [];
        $row['config_json'] = json_encode($row['config'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        View::assign('row', $row);
        View::assign('gatewayNames', PaymentGatewayFactory::allNames());
        View::assign('configFields', PaymentGatewayFactory::configFields($row['code']));
        View::assign('title', '编辑支付网关');
        return $this->fetchWithLayout('payment/config/edit');
    }

    public function editPost(int $id): Response
    {
        $row = Db::name('payment_gateway')->where('id', $id)->find();
        if (!$row) {
            return $this->error('记录不存在');
        }
        $tenantId = $this->getTenantId();
        if ($tenantId > 0 && (int) $row['tenant_id'] !== $tenantId) {
            return $this->error('无权限');
        }
        $data = $this->request->post();
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            return $this->error('请填写名称');
        }
        $config = $data['config'] ?? [];
        if (is_string($config)) {
            $config = $config !== '' ? json_decode($config, true) : [];
        }
        $config = is_array($config) ? $config : [];
        $update = [
            'name'        => $name,
            'config'      => json_encode($config, JSON_UNESCAPED_UNICODE),
            'enabled'     => isset($data['enabled']) ? (int) $data['enabled'] : 1,
            'sort'        => (int) ($data['sort'] ?? 0),
            'update_time' => time(),
        ];
        Db::name('payment_gateway')->where('id', $id)->update($update);
        return $this->success('保存成功');
    }

    public function del(): Response
    {
        $id = (int) $this->request->post('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = Db::name('payment_gateway')->where('id', $id)->find();
        if (!$row) {
            return $this->error('记录不存在');
        }
        $tenantId = $this->getTenantId();
        if ($tenantId > 0 && (int) $row['tenant_id'] !== $tenantId) {
            return $this->error('无权限');
        }
        Db::name('payment_gateway')->where('id', $id)->delete();
        return $this->success('删除成功');
    }

    /** 根据 code 返回配置项（用于前端动态表单） */
    public function configFields(): Response
    {
        $code = trim((string) $this->request->get('code', ''));
        $fields = PaymentGatewayFactory::configFields($code);
        return $this->success('', $fields);
    }

    private function loadGatewayRow(int $tenantId, string $code): array
    {
        $row = Db::name('payment_gateway')->where('tenant_id', $tenantId)->where('code', $code)->find();
        if (!$row) {
            return [
                'id' => 0,
                'tenant_id' => $tenantId,
                'code' => $code,
                'name' => '',
                'config' => [],
                'enabled' => 0,
                'sort' => 0,
            ];
        }
        $cfg = $row['config'] ? json_decode((string) $row['config'], true) : [];
        $cfg = is_array($cfg) ? $cfg : [];
        $row['config'] = $cfg;
        return $row;
    }

    private function upsertGateway(int $tenantId, string $code, string $name, array $config, int $enabled, int $sort = 0): void
    {
        $now = time();
        $exists = Db::name('payment_gateway')->where('tenant_id', $tenantId)->where('code', $code)->find();
        $data = [
            'tenant_id' => $tenantId,
            'code' => $code,
            'name' => $name,
            'config' => json_encode($config, JSON_UNESCAPED_UNICODE),
            'enabled' => $enabled ? 1 : 0,
            'sort' => $sort,
            'update_time' => $now,
        ];
        if ($exists) {
            Db::name('payment_gateway')->where('id', (int) $exists['id'])->update($data);
        } else {
            $data['create_time'] = $now;
            Db::name('payment_gateway')->insert($data);
        }
    }

    private function normalizeCommonConfig(array $in): array
    {
        return [
            'notify_url' => trim((string) ($in['notify_url'] ?? '')),
            'return_url' => trim((string) ($in['return_url'] ?? '')),
            'wap_url' => trim((string) ($in['wap_url'] ?? '')),
            'wap_name' => trim((string) ($in['wap_name'] ?? '')),
        ];
    }

    private function renderXunhupay(): Response
    {
        $tenantId = $this->getTenantId();
        $wechat = $this->loadGatewayRow($tenantId, 'xunhupay_wechat');
        $alipay = $this->loadGatewayRow($tenantId, 'xunhupay_alipay');

        $common = $wechat['config'] ?? [];
        $common = is_array($common) ? $common : [];
        $common = $this->normalizeCommonConfig($common);

        $domain = rtrim((string) $this->request->domain(), '/');
        $wechatNotify = !empty($wechat['id']) ? ($domain . '/api/payment/notify/' . (int) $wechat['id']) : ($domain . '/api/payment/notify/{网关ID}');
        $alipayNotify = !empty($alipay['id']) ? ($domain . '/api/payment/notify/' . (int) $alipay['id']) : ($domain . '/api/payment/notify/{网关ID}');
        View::assign('wechat_notify_url', $wechatNotify);
        View::assign('alipay_notify_url', $alipayNotify);

        View::assign('title', '虎皮椒(讯虎)支付配置');
        View::assign('tenant_id', $tenantId);
        View::assign('wechat', $wechat);
        View::assign('alipay', $alipay);
        View::assign('common', $common);
        View::assign('legacy_url', $this->request->url(true) . '?legacy=1');
        return Response::create($this->fetchWithLayout('payment/config/xunhupay'), 'html');
    }

    private function saveXunhupay(): Response
    {
        $tenantId = $this->getTenantId();
        $wechatEnabled = (int) $this->request->post('wechat_enabled', 0) === 1 ? 1 : 0;
        $alipayEnabled = (int) $this->request->post('alipay_enabled', 0) === 1 ? 1 : 0;
        $common = $this->normalizeCommonConfig((array) $this->request->post('common', []));

        $wechatCfg = [
            'appid' => trim((string) $this->request->post('wechat_appid', '')),
            'appsecret' => trim((string) $this->request->post('wechat_appsecret', '')),
            'type' => 'wechat',
            'api_url' => trim((string) $this->request->post('wechat_api_url', '')),
        ];
        $alipayCfg = [
            'appid' => trim((string) $this->request->post('alipay_appid', '')),
            'appsecret' => trim((string) $this->request->post('alipay_appsecret', '')),
            'type' => 'alipay',
            'api_url' => trim((string) $this->request->post('alipay_api_url', '')),
        ];

        foreach ($common as $k => $v) {
            $wechatCfg[$k] = $v;
            $alipayCfg[$k] = $v;
        }

        $this->upsertGateway($tenantId, 'xunhupay_wechat', '虎皮椒-微信', $wechatCfg, $wechatEnabled, 10);
        $this->upsertGateway($tenantId, 'xunhupay_alipay', '虎皮椒-支付宝', $alipayCfg, $alipayEnabled, 11);
        return $this->success('保存成功');
    }

    private function renderEpay(): Response
    {
        $tenantId = $this->getTenantId();
        $row = $this->loadGatewayRow($tenantId, 'epay');
        $common = $row['config'] ?? [];
        $common = is_array($common) ? $common : [];
        $common = $this->normalizeCommonConfig($common);
        if (!isset($row['config']) || !is_array($row['config'])) {
            $row['config'] = [];
        }
        $row['config']['pid'] = (string) ($row['config']['pid'] ?? '');
        $row['config']['key'] = (string) ($row['config']['key'] ?? '');
        $row['config']['submit_url'] = (string) ($row['config']['submit_url'] ?? '');
        $row['config']['type'] = (string) ($row['config']['type'] ?? '');
        $domain = rtrim((string) $this->request->domain(), '/');
        $notify = !empty($row['id']) ? ($domain . '/api/payment/notify/' . (int) $row['id']) : ($domain . '/api/payment/notify/{网关ID}');
        View::assign('notify_url', $notify);
        View::assign('title', '易支付(8-pay)配置');
        View::assign('tenant_id', $tenantId);
        View::assign('row', $row);
        View::assign('common', $common);
        View::assign('legacy_url', $this->request->url(true) . '?legacy=1');
        return Response::create($this->fetchWithLayout('payment/config/epay'), 'html');
    }

    private function saveEpay(): Response
    {
        $tenantId = $this->getTenantId();
        $enabled = (int) $this->request->post('enabled', 0) === 1 ? 1 : 0;
        $common = $this->normalizeCommonConfig((array) $this->request->post('common', []));
        $cfg = [
            'pid' => trim((string) $this->request->post('pid', '')),
            'key' => trim((string) $this->request->post('key', '')),
            'submit_url' => trim((string) $this->request->post('submit_url', '')),
            'type' => trim((string) $this->request->post('type', '')),
        ];
        foreach ($common as $k => $v) $cfg[$k] = $v;
        $this->upsertGateway($tenantId, 'epay', '易支付(8-pay)', $cfg, $enabled, 20);
        return $this->success('保存成功');
    }

    private function renderSingleGateway(string $code): Response
    {
        $tenantId = $this->getTenantId();
        $row = $this->loadGatewayRow($tenantId, $code);
        $common = $row['config'] ?? [];
        $common = is_array($common) ? $common : [];
        $common = $this->normalizeCommonConfig($common);
        View::assign('title', ($code === 'official_alipay' ? '官方支付宝配置' : '官方微信支付配置'));
        View::assign('tenant_id', $tenantId);
        View::assign('row', $row);
        View::assign('common', $common);
        View::assign('schema', PaymentGatewayFactory::configFields($code));
        View::assign('legacy_url', $this->request->url(true) . '?legacy=1');
        $tpl = $code === 'official_alipay' ? 'payment/config/official_alipay' : 'payment/config/official_wechat';
        return Response::create($this->fetchWithLayout($tpl), 'html');
    }

    private function saveSingleGateway(string $code): Response
    {
        $tenantId = $this->getTenantId();
        $enabled = (int) $this->request->post('enabled', 0) === 1 ? 1 : 0;
        $common = $this->normalizeCommonConfig((array) $this->request->post('common', []));
        $cfg = (array) $this->request->post('config', []);
        foreach ($cfg as $k => $v) $cfg[$k] = is_string($v) ? trim($v) : $v;
        foreach ($common as $k => $v) $cfg[$k] = $v;
        $name = $code === 'official_alipay' ? '官方支付宝' : '官方微信支付';
        $this->upsertGateway($tenantId, $code, $name, $cfg, $enabled, 1);
        return $this->success('保存成功');
    }
}
