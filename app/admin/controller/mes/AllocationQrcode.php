<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\AllocationQrcodeModel;
use app\common\lib\QrCodeService;
use think\facade\Db;
use think\facade\Log;
use think\facade\View;
use think\Response;

/**
 * 分工二维码管理：查看、重新生成
 */
class AllocationQrcode extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '分工二维码管理');
            return $this->fetchWithLayout('mes/allocation_qrcode/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();

        // 用 JOIN 直接取订单号、产品、型号；订单优先用 a.order_id，为 0 时用计划表 plan.order_id
        $baseQuery = function () use ($tenantId) {
            return Db::name('mes_allocation_qrcode')
                ->alias('q')
                ->leftJoin('mes_allocation a', 'q.allocation_id = a.id AND a.tenant_id = q.tenant_id')
                ->leftJoin('mes_production_plan pp', 'a.plan_id = pp.id AND pp.tenant_id = q.tenant_id')
                ->leftJoin('mes_order o', 'o.tenant_id = q.tenant_id AND (o.id = a.order_id OR (a.order_id = 0 AND o.id = pp.order_id))')
                ->leftJoin('mes_product_model pm', 'pm.tenant_id = q.tenant_id AND (pm.id = a.model_id OR (a.model_id = 0 AND pm.id = pp.model_id))')
                ->leftJoin('mes_product p', 'pm.product_id = p.id AND p.tenant_id = q.tenant_id')
                ->leftJoin('mes_process pr', 'a.process_id = pr.id AND pr.tenant_id = q.tenant_id')
                ->where('q.tenant_id', $tenantId);
        };
        $total = $baseQuery()->count();
        $rows = $baseQuery()
            ->order('q.id', 'desc')
            ->field('q.id, q.allocation_id, q.qrcode_content, q.qrcode_url, q.scan_count, q.last_scan_time, q.status, q.create_time, q.update_time,
                o.order_no, p.name as product_name, pm.name as model_name, pr.name as process_name, a.quantity')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'id' => $r['id'] ?? 0,
                'allocation_id' => $r['allocation_id'] ?? 0,
                'order_no' => isset($r['order_no']) && (string) $r['order_no'] !== '' ? $r['order_no'] : '-',
                'product_name' => isset($r['product_name']) && (string) $r['product_name'] !== '' ? $r['product_name'] : '-',
                'model_name' => isset($r['model_name']) && (string) $r['model_name'] !== '' ? $r['model_name'] : '-',
                'process_name' => isset($r['process_name']) && (string) $r['process_name'] !== '' ? $r['process_name'] : '-',
                'quantity' => (int) ($r['quantity'] ?? 0),
                'qrcode_content' => $r['qrcode_content'] ?? '',
                'qrcode_url' => $r['qrcode_url'] ?? '',
                'scan_count' => (int) ($r['scan_count'] ?? 0),
                'last_scan_time' => $r['last_scan_time'] ?? null,
                'status' => (int) ($r['status'] ?? 0),
                'create_time' => $r['create_time'] ?? 0,
                'update_time' => $r['update_time'] ?? 0,
            ];
        }

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 获取一条二维码信息（用于弹窗展示）；若尚未生成则先自动生成再返回
     */
    public function getInfo(): Response
    {
        $id = (int) $this->request->get('id');
        $tenantId = $this->getTenantId();
        $logCtx = 'qrcode_getInfo id=' . $id . ' tenant_id=' . $tenantId;

        Log::info('[' . $logCtx . '] request');

        if ($id <= 0) {
            Log::warning('[' . $logCtx . '] invalid id, return 参数错误');
            return $this->error('参数错误');
        }
        $qr = AllocationQrcodeModel::where('tenant_id', $tenantId)->where('id', $id)->find();
        if (!$qr) {
            Log::warning('[' . $logCtx . '] qr not found');
            return $this->error('记录不存在');
        }
        $url = trim((string) ($qr->qrcode_url ?: $qr->qrcode_content ?: ''));
        Log::info('[' . $logCtx . '] qr found allocation_id=' . ($qr->allocation_id ?? 0) . ' url_empty=' . ($url === '' ? 1 : 0));

        if ($url === '') {
            $allocationId = (int) $qr->allocation_id;
            if ($allocationId <= 0) {
                Log::warning('[' . $logCtx . '] allocation_id empty');
                return $this->error('该分工未关联分配记录，无法生成二维码');
            }
            try {
                Log::info('[' . $logCtx . '] doGenerateQrcode allocation_id=' . $allocationId);
                $allocationCtrl = $this->app->make(Allocation::class);
                $allocationCtrl->doGenerateQrcode($allocationId, $tenantId);
            } catch (\Throwable $e) {
                Log::error('[' . $logCtx . '] doGenerateQrcode exception: ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
                return $this->error('生成二维码失败：' . $e->getMessage());
            }
            $qr->refresh();
            $url = trim((string) ($qr->qrcode_url ?: $qr->qrcode_content ?: ''));
            Log::info('[' . $logCtx . '] after generate url_empty=' . ($url === '' ? 1 : 0));
            if ($url === '') {
                Log::warning('[' . $logCtx . '] url still empty after generate');
                return $this->error('生成后仍无链接，请稍后重试');
            }
        }
        if ($url === '') {
            Log::warning('[' . $logCtx . '] url empty before return, return error');
            return $this->error('二维码链接为空，请点击重新生成');
        }
        $imagePath = trim((string) ($qr->qrcode_image ?? ''));
        // 有链接但无本地图片时（旧数据或此前未安装 qr-code），触发生成一次以便弹窗用本地图
        if ($imagePath === '' && (int) $qr->allocation_id > 0) {
            try {
                $allocationCtrl = $this->app->make(Allocation::class);
                $allocationCtrl->doGenerateQrcode((int) $qr->allocation_id, $tenantId);
                $qr->refresh();
                $imagePath = trim((string) ($qr->qrcode_image ?? ''));
            } catch (\Throwable $e) {
                Log::warning('[' . $logCtx . '] sync local qr image failed: ' . $e->getMessage());
            }
        }
        $imageUrl = $imagePath !== '' ? QrCodeService::pathToUrl($imagePath) : '';
        Log::info('[' . $logCtx . '] success url_len=' . strlen($url) . ' has_image=' . ($imageUrl !== '' ? 1 : 0));
        return $this->success('', [
            'url' => $url,
            'image' => $imageUrl,
            'allocation_id' => $qr->allocation_id,
            'id' => $qr->id,
        ]);
    }

    /**
     * 重新生成二维码（按二维码表 id 或 allocation_id）
     */
    public function regenerate(): Response
    {
        $id = (int) $this->request->post('id');
        $allocationId = (int) $this->request->post('allocation_id');
        $tenantId = $this->getTenantId();
        $logCtx = 'qrcode_regenerate id=' . $id . ' allocation_id=' . $allocationId . ' tenant_id=' . $tenantId;
        Log::info('[' . $logCtx . '] request');

        if ($allocationId <= 0 && $id > 0) {
            $qr = AllocationQrcodeModel::where('tenant_id', $tenantId)->where('id', $id)->find();
            if ($qr) {
                $allocationId = (int) $qr->allocation_id;
                Log::info('[' . $logCtx . '] resolved allocation_id=' . $allocationId);
            }
        }
        if ($allocationId <= 0) {
            Log::warning('[' . $logCtx . '] no allocation_id');
            return $this->error('请指定分工');
        }
        try {
            $allocationCtrl = $this->app->make(Allocation::class);
            $allocationCtrl->doGenerateQrcode($allocationId, $tenantId);
            Log::info('[' . $logCtx . '] success');
        } catch (\Throwable $e) {
            Log::error('[' . $logCtx . '] exception: ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
            return $this->error('生成失败：' . $e->getMessage());
        }
        return $this->success('二维码已重新生成');
    }
}
