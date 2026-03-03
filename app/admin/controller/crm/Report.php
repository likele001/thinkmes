<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\Backend;
use app\admin\model\crm\CustomerModel;
use app\admin\model\crm\OpportunityModel;
use app\admin\model\crm\ContractModel;
use app\admin\model\crm\PaymentModel;
use app\admin\model\crm\SalesOrderModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * CRM 报表
 */
class Report extends Backend
{
    public function index(): string|Response
    {
        $tenantId = $this->getTenantId();
        $prefix = (string) (Db::connect()->getConfig()['prefix'] ?? 'fa_');

        $stats = [
            'customer_count' => CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->count(),
            'opportunity_count' => OpportunityModel::where('tenant_id', $tenantId)->where('stage', 'in', ['lead', 'quote', 'negotiate'])->count(),
            'opportunity_amount' => OpportunityModel::where('tenant_id', $tenantId)->where('stage', 'in', ['lead', 'quote', 'negotiate'])->sum('amount'),
            'contract_count' => ContractModel::where('tenant_id', $tenantId)->where('status', 'in', ['performing', 'completed'])->count(),
            'contract_amount' => ContractModel::where('tenant_id', $tenantId)->where('status', 'in', ['performing', 'completed'])->sum('amount'),
            'payment_amount' => PaymentModel::where('tenant_id', $tenantId)->sum('amount'),
            'sales_order_count' => SalesOrderModel::where('tenant_id', $tenantId)->where('status', '<>', 'cancelled')->count(),
            'sales_order_amount' => SalesOrderModel::where('tenant_id', $tenantId)->where('status', 'in', ['confirmed', 'producing', 'completed'])->sum('total_amount'),
        ];

        View::assign('stats', $stats);
        View::assign('title', 'CRM报表');
        return $this->fetchWithLayout('crm/report/index');
    }
}
