<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\Request;
use think\facade\View;
use think\Response;

class AiPackage extends Backend
{
    /**
     * AI 套餐管理首页
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', 'AI 套餐管理');
            return $this->fetchWithLayout('ai/package/index');
        }
        // AJAX 请求返回套餐列表
        return $this->packages();
    }

    /** 全局开关与四个子功能开关配置页（平台/租户均可打开） */
    public function globalSwitchPage(): string|Response
    {
        View::assign('title', 'AI 全局开关与子功能');
        return $this->fetchWithLayout('ai/global_switch');
    }
    // 获取全局/租户生效的开关（含四个子功能：报工、异常检测、智能问答、CRM跟单）
    public function globalSwitch(): Response
    {
        $default = [
            'id' => 1, 'enabled' => 0, 'require_purchase' => 1, 'notice' => '',
            'switch_voice_report' => 1, 'switch_anomaly' => 1, 'switch_qa' => 1, 'switch_crm_follow' => 1,
        ];
        try {
            $row = Db::name('ai_global_switch')->where('id', 1)->find();
            $data = $row ? (is_array($row) ? $row : $row->toArray()) : [];
            $data = array_merge($default, $data);
            foreach (['switch_voice_report', 'switch_anomaly', 'switch_qa', 'switch_crm_follow'] as $k) {
                if (!array_key_exists($k, $data)) {
                    $data[$k] = 1;
                }
            }
            $tenantId = $this->getTenantId();
            if ($tenantId > 0) {
                $overrides = Db::name('tenant_ai_module_switch')->where('tenant_id', $tenantId)->column('enabled', 'module');
                $map = ['voice_report' => 'switch_voice_report', 'anomaly' => 'switch_anomaly', 'qa' => 'switch_qa', 'crm_follow' => 'switch_crm_follow'];
                foreach ($map as $module => $col) {
                    if (array_key_exists($module, $overrides)) {
                        $data[$col] = (int) $overrides[$module];
                    }
                }
            }
        } catch (\Throwable $e) {
            $data = $default;
        }
        return $this->success('', $data);
    }

    // 更新全局开关（平台改全局，租户改本租户覆盖）
    public function updateGlobal(): Response
    {
        $tenantId = $this->getTenantId();
        $data = Request::only(['enabled', 'require_purchase', 'notice', 'switch_voice_report', 'switch_anomaly', 'switch_qa', 'switch_crm_follow']);
        $data['update_time'] = time();

        try {
            if ($tenantId === 0) {
                $update = array_intersect_key($data, array_flip(['enabled', 'require_purchase', 'notice', 'update_time', 'switch_voice_report', 'switch_anomaly', 'switch_qa', 'switch_crm_follow']));
                $exists = Db::name('ai_global_switch')->where('id', 1)->find();
                try {
                    if ($exists) {
                        Db::name('ai_global_switch')->where('id', 1)->update($update);
                    } else {
                        $update['id'] = 1;
                        Db::name('ai_global_switch')->insert($update);
                    }
                } catch (\Throwable $e) {
                    if (stripos($e->getMessage(), 'Unknown column') !== false) {
                        $update = array_intersect_key($data, array_flip(['enabled', 'require_purchase', 'notice', 'update_time']));
                        if ($exists) {
                            Db::name('ai_global_switch')->where('id', 1)->update($update);
                        } else {
                            $update['id'] = 1;
                            Db::name('ai_global_switch')->insert($update);
                        }
                    } else {
                        throw $e;
                    }
                }
            } else {
                $modules = [
                    'voice_report' => 'switch_voice_report',
                    'anomaly'      => 'switch_anomaly',
                    'qa'           => 'switch_qa',
                    'crm_follow'   => 'switch_crm_follow',
                ];
                Db::name('tenant_ai_module_switch')->where('tenant_id', $tenantId)->delete();
                $now = (int) $data['update_time'];
                foreach ($modules as $module => $col) {
                    $enabled = isset($data[$col]) ? (int) $data[$col] : 1;
                    Db::name('tenant_ai_module_switch')->insert([
                        'tenant_id'   => $tenantId,
                        'module'      => $module,
                        'enabled'     => $enabled,
                        'update_time' => $now,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
        return $this->success('更新成功');
    }

    // 列表套餐
    public function packages(): Response
    {
        $list = Db::name('ai_package')->order('id','asc')->select()->toArray();
        return $this->success('', $list);
    }

    // 新增套餐
    public function createPackage(): Response
    {
        $data = $this->request->post();
        if (empty($data)) {
            $raw = $this->request->getContent();
            if ($raw !== '' && $raw !== null) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }
        $data = array_intersect_key($data ?: [], array_flip(['name', 'price_month', 'price_quarter', 'price_year', 'description', 'enabled']));
        $data['name'] = isset($data['name']) ? trim((string) $data['name']) : '';
        if ($data['name'] === '') {
            return $this->error('套餐名称不能为空');
        }
        $data['create_time'] = $data['update_time'] = time();
        $id = Db::name('ai_package')->insertGetId($data);
        return $this->success('创建成功', ['id' => $id]);
    }

    // 管理端为租户下单（标记购买记录）
    public function purchaseForTenant(): Response
    {
        $data = Request::only(['tenant_id','package_id','period','order_no','amount','payment_method']);
        $tenantId = intval($data['tenant_id'] ?? 0);
        $packageId = intval($data['package_id'] ?? 0);
        if (!$tenantId || !$packageId) {
            return $this->error('参数错误');
        }
        $period = $data['period'] ?? 'month';
        $now = time();
        $start = $now;
        switch ($period) {
            case 'quarter': $end = $now + 90*24*3600; break;
            case 'year': $end = $now + 365*24*3600; break;
            default: $end = $now + 30*24*3600; break;
        }
        $insert = [
            'tenant_id'=>$tenantId,
            'package_id'=>$packageId,
            'period'=>$period,
            'start_time'=>$start,
            'end_time'=>$end,
            'status'=>1,
            'order_no'=>$data['order_no'] ?? '',
            'amount'=>floatval($data['amount'] ?? 0),
            'payment_method'=>$data['payment_method'] ?? '',
            'create_time'=>$now,
        ];
        Db::name('tenant_ai_purchase')->insert($insert);
        // 更新租户表的 package_id 与 expire_time（可选）
        Db::name('tenant')->where('id',$tenantId)->update(['package_id'=>$packageId,'expire_time'=>$end,'update_time'=>$now]);
        return $this->success('购买记录已保存');
    }
}
