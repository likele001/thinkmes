<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 库存流水模型
 */
class StockLogModel extends Model
{
    protected $name = 'mes_stock_log';

    protected $type = [
        'tenant_id'       => 'integer',
        'material_id'      => 'integer',
        'warehouse_id'     => 'integer',
        'before_quantity'  => 'float',
        'change_quantity'   => 'float',
        'after_quantity'    => 'float',
        'operator_id'      => 'integer',
        'create_time'      => 'integer',
    ];

    /**
     * 获取业务类型列表
     */
    public function getBusinessTypeList(): array
    {
        return [
            'purchase_in'  => '采购入库',
            'production_out' => '生产出库',
            'check_in'      => '盘点入库',
            'check_out'     => '盘点出库',
            'return_in'     => '退货入库',
            'adjust_in'     => '调整入库',
            'adjust_out'    => '调整出库',
        ];
    }

    /**
     * 关联物料
     */
    public function material()
    {
        return $this->belongsTo(MaterialModel::class, 'material_id', 'id');
    }

    /**
     * 关联仓库
     */
    public function warehouse()
    {
        return $this->belongsTo(WarehouseModel::class, 'warehouse_id', 'id');
    }

    /**
     * 记录库存变动
     */
    public static function log(int $tenantId, int $materialId, float $changeQty, string $businessType, int $businessId, int $operatorId, string $remark = ''): void
    {
        $material = MaterialModel::where('tenant_id', $tenantId)->find($materialId);
        if (!$material) {
            return;
        }

        $beforeQty = $material->stock;
        $afterQty = $material->stock + $changeQty;

        // 更新物料库存
        $material->stock = $afterQty;
        $material->save();

        // 记录流水
        self::create([
            'tenant_id'       => $tenantId,
            'material_id'      => $materialId,
            'warehouse_id'     => 0,
            'before_quantity'  => $beforeQty,
            'change_quantity'   => $changeQty,
            'after_quantity'    => $afterQty,
            'business_type'    => $businessType,
            'business_id'      => $businessId,
            'operator_id'      => $operatorId,
            'remark'           => $remark,
            'create_time'      => time(),
        ]);
    }
}
