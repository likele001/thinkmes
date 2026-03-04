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
        return $this->redirectToConfigIndex('official_alipay');
    }

    /** 官方微信支付菜单入口 */
    public function configWechat(): Response
    {
        return $this->redirectToConfigIndex('official_wechat');
    }

    /** 虎皮椒(讯虎)菜单入口 */
    public function configXunhupay(): Response
    {
        return $this->redirectToConfigIndex('xunhupay');
    }

    /** 易支付(8-pay)菜单入口 */
    public function configEpay(): Response
    {
        return $this->redirectToConfigIndex('epay');
    }

    private function redirectToConfigIndex(string $code): Response
    {
        $prefix = $this->getAdminUrlPrefix();
        $url = rtrim($this->request->domain(), '/') . $prefix . '/payment/config/index?code=' . urlencode($code);
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
}
