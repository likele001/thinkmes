<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 采购入库单主表（与 report scanwork_purchase_inbound 对应）
 */
class PurchaseInboundModel extends Model
{
    protected $name = 'mes_purchase_inbound';

    protected $type = [
        'tenant_id'          => 'integer',
        'supplier_id'        => 'integer',
        'inbound_date'       => 'integer',
        'total_amount'       => 'float',
        'status'             => 'integer',
        'inbound_user_id'    => 'integer',
        'warehouse_id'       => 'integer',
        'create_time'        => 'integer',
        'update_time'        => 'integer',
    ];

    public function getStatusList(): array
    {
        return [1 => '待入库', 2 => '已入库', 3 => '已取消'];
    }

    public function supplier()
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInboundItemModel::class, 'inbound_id', 'id');
    }

    public static function generateInboundNo(): string
    {
        $prefix = 'IN';
        $date = date('Ymd');
        $rand = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
        return $prefix . $date . $rand;
    }
}
