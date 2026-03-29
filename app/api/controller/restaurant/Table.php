<?php
declare(strict_types=1);

namespace app\api\controller\restaurant;

use app\common\controller\BaseController;
use app\admin\model\restaurant\TableModel;
use think\Response;

class Table extends BaseController
{
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    public function info(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            $tenantId = (int) $this->request->get('tenant_id', 0);
        }
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }

        $token = trim((string) $this->request->get('token', ''));
        if ($token === '') {
            return $this->error('缺少 token');
        }

        $row = TableModel::with(['store', 'area'])
            ->where('tenant_id', $tenantId)
            ->where('qr_token', $token)
            ->where('status', 1)
            ->find();
        if (!$row) {
            return $this->error('桌台不存在或已禁用');
        }

        return $this->success('', [
            'table' => [
                'id' => (int) $row->id,
                'store_id' => (int) $row->store_id,
                'area_id' => (int) $row->area_id,
                'name' => (string) $row->name,
                'code' => (string) $row->code,
                'seats' => (int) $row->seats,
                'state' => (int) $row->state,
                'token' => (string) $row->qr_token,
            ],
            'store' => [
                'id' => (int) ($row->store ? $row->store->id : 0),
                'name' => (string) ($row->store ? $row->store->name : ''),
            ],
            'area' => [
                'id' => (int) ($row->area ? $row->area->id : 0),
                'name' => (string) ($row->area ? $row->area->name : ''),
            ],
        ]);
    }
}

