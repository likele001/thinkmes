<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\common\lib\restaurant\OpenClawClient;
use think\facade\Db;
use think\facade\View;
use think\Response;

class OpenClawSetting extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isPost()) {
            $action = trim((string) $this->request->post('action', 'save'));
            $tenantId = $this->resolveTenantId();
            if ($action === 'save') {
                $enabled = (int) $this->request->post('enabled', 0) ? 1 : 0;
                $apiBase = trim((string) $this->request->post('api_base', ''));
                $apiKey = trim((string) $this->request->post('api_key', ''));
                $workspace = trim((string) $this->request->post('workspace', ''));
                $webhookSecret = trim((string) $this->request->post('webhook_secret', ''));
                $badThreshold = (int) $this->request->post('bad_threshold', 3);
                $alertEnabled = (int) $this->request->post('alert_enabled', 1) ? 1 : 0;
                $alertPush = (int) $this->request->post('alert_push_openclaw', 1) ? 1 : 0;
                $now = time();
                $this->upsert('restaurant_openclaw_enabled', '餐饮 OpenClaw 启用', (string) $enabled, $now);
                $this->upsert('restaurant_openclaw_api_base', '餐饮 OpenClaw API Base', $apiBase, $now);
                $this->upsert('restaurant_openclaw_api_key', '餐饮 OpenClaw API Key', $apiKey, $now);
                $this->upsert('restaurant_openclaw_workspace', '餐饮 OpenClaw 工作区', $workspace, $now);
                $this->upsert('restaurant_openclaw_webhook_secret', '餐饮 OpenClaw Webhook Secret', $webhookSecret, $now);
                $this->upsert('restaurant_review_bad_threshold', '餐饮差评阈值(<=为差评)', (string) max(1, min(5, $badThreshold)), $now);
                $this->upsert('restaurant_review_alert_enabled', '餐饮差评告警启用', (string) $alertEnabled, $now);
                $this->upsert('restaurant_review_alert_push_openclaw', '餐饮差评告警推送OpenClaw', (string) $alertPush, $now);
                return $this->success('已保存');
            }
            if ($action === 'ping') {
                $cli = new OpenClawClient($tenantId > 0 ? $tenantId : 0);
                $r = $cli->ping();
                return $r['ok'] ? $this->success('连接正常') : $this->error($r['error'] ?? '连接失败');
            }
            if ($action === 'install') {
                if ($tenantId <= 0) return $this->error('tenant_id required');
                $cli = new OpenClawClient($tenantId);
                $r = $cli->install();
                return $r['ok'] ? $this->success('已申请安装/初始化') : $this->error($r['error'] ?? '安装失败');
            }
            if ($action === 'sync') {
                $date = trim((string) $this->request->post('date', date('Y-m-d')));
                if ($tenantId <= 0) return $this->error('tenant_id required');
                $cli = new OpenClawClient($tenantId);
                $r = $cli->pushSummary($date);
                return $r['ok'] ? $this->success('已同步') : $this->error($r['error'] ?? '同步失败');
            }
            return $this->error('未知动作');
        }
        $get = function (string $k, string $d = '') {
            $v = Db::name('config')->where('name', $k)->value('value');
            return $v === null ? $d : (string) $v;
        };
        View::assign('enabled', $get('restaurant_openclaw_enabled', '0') === '1' ? 1 : 0);
        View::assign('api_base', $get('restaurant_openclaw_api_base', ''));
        View::assign('api_key', $get('restaurant_openclaw_api_key', ''));
        View::assign('workspace', $get('restaurant_openclaw_workspace', ''));
        View::assign('webhook_secret', $get('restaurant_openclaw_webhook_secret', ''));
        View::assign('bad_threshold', (int) $get('restaurant_review_bad_threshold', '3'));
        View::assign('alert_enabled', $get('restaurant_review_alert_enabled', '1') === '1' ? 1 : 0);
        View::assign('alert_push_openclaw', $get('restaurant_review_alert_push_openclaw', '1') === '1' ? 1 : 0);
        $isPlatform = $this->isPlatformAdmin() && $this->getTenantId() === 0;
        View::assign('is_platform', $isPlatform ? 1 : 0);
        $tenantId = $this->resolveTenantId();
        View::assign('tenant_id', $tenantId);
        if ($isPlatform) {
            $tenants = Db::name('tenant')->where('status', 1)->order('id', 'asc')->field('id,name')->select()->toArray();
            View::assign('tenants', $tenants);
        } else {
            View::assign('tenants', []);
        }
        View::assign('title', 'OpenClaw 设置');
        return $this->fetchWithLayout('restaurant/openclaw_setting/index');
    }

    protected function resolveTenantId(): int
    {
        $tenantId = $this->getTenantId();
        if ($tenantId > 0) return $tenantId;
        $p = $this->request->param('tenant_id');
        if ($p !== null && $p !== '') return (int) $p;
        $g = $this->request->get('tenant_id');
        if ($g !== null && $g !== '') return (int) $g;
        $post = $this->request->post('tenant_id');
        if ($post !== null && $post !== '') return (int) $post;
        return 0;
    }

    private function upsert(string $name, string $title, string $value, int $now): void
    {
        $row = Db::name('config')->where('name', $name)->find();
        if ($row) {
            Db::name('config')->where('name', $name)->update(['value' => $value, 'update_time' => $now]);
            return;
        }
        Db::name('config')->insert([
            'name' => $name,
            'title' => $title,
            'value' => $value,
            'group' => 'base',
            'sort' => 0,
            'create_time' => $now,
            'update_time' => $now,
        ]);
    }
}
