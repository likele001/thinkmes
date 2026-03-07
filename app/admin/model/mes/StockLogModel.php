<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 库存流水模型
 */
class StockLogModel extends Model
{
    protected $name = 'mes_stock_log';

    protected $type = [
        'tenant_id'        => 'integer',
        'material_id'      => 'integer',
        'product_model_id' => 'integer',
        'warehouse_id'     => 'integer',
        'before_quantity'  => 'float',
        'change_quantity'  => 'float',
        'after_quantity'   => 'float',
        'operator_id'      => 'integer',
        'create_time'      => 'integer',
    ];

    /**
     * 获取业务类型列表
     */
    public function getBusinessTypeList(): array
    {
        return [
            'purchase_in'    => '采购入库',
            'production_out' => '生产出库',
            'check_in'       => '盘点入库',
            'check_out'      => '盘点出库',
            'return_in'      => '退货入库',
            'adjust_in'      => '调整入库',
            'adjust_out'     => '调整出库',
            'shipment_out'   => '发货出库',
            'production_in'  => '完工入库',
        ];
    }

    /**
     * 关联产品型号
     */
    public function productModel()
    {
        return $this->belongsTo(ProductModelModel::class, 'product_model_id', 'id');
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
     * 记录库存变动（仅写流水，不修改库存；调用方需自行先更新库存）
     * @param float|null $beforeQty 变动前数量，与 $afterQty 同时传入时只记流水不更新库存
     * @param float|null $afterQty  变动后数量
     */
    public static function log(int $tenantId, int $materialId, float $changeQty, string $businessType, int $businessId, int $operatorId, string $remark = '', ?float $beforeQty = null, ?float $afterQty = null): void
    {
        if ($beforeQty !== null && $afterQty !== null) {
            // 调用方已更新库存，仅记录流水
            self::create([
                'tenant_id'        => $tenantId,
                'material_id'      => $materialId,
                'product_model_id' => 0,
                'warehouse_id'     => 0,
                'before_quantity'  => $beforeQty,
                'change_quantity'  => $changeQty,
                'after_quantity'   => $afterQty,
                'business_type'    => $businessType,
                'business_id'      => $businessId,
                'operator_id'      => $operatorId,
                'remark'           => $remark,
                'create_time'      => time(),
            ]);
            return;
        }

        $material = MaterialModel::where('tenant_id', $tenantId)->find($materialId);
        if (!$material) {
            return;
        }

        $beforeQty = (float)$material->stock;
        $afterQty = $beforeQty + $changeQty;

        // 更新物料库存（仅当调用方未传 before/after 时，由 log 负责更新，兼容 Scanwork 等）
        $material->stock = $afterQty;
        $material->save();

        self::create([
            'tenant_id'        => $tenantId,
            'material_id'      => $materialId,
            'product_model_id' => 0,
            'warehouse_id'     => 0,
            'before_quantity'  => $beforeQty,
            'change_quantity'  => $changeQty,
            'after_quantity'   => $afterQty,
            'business_type'    => $businessType,
            'business_id'      => $businessId,
            'operator_id'      => $operatorId,
            'remark'           => $remark,
            'create_time'      => time(),
        ]);
    }

    /**
     * 记录产品库存变动
     */
    public static function logProduct(int $tenantId, int $productModelId, float $changeQty, string $businessType, int $businessId, int $operatorId, string $remark = ''): void
    {
        $productModel = ProductModelModel::where('tenant_id', $tenantId)->find($productModelId);
        if (!$productModel) {
            return;
        }

        $beforeQty = (float)$productModel->stock;
        $afterQty = $beforeQty + $changeQty;

        // 更新产品库存
        $productModel->stock = $afterQty;
        $productModel->save();

        // 记录流水
        self::create([
            'tenant_id'        => $tenantId,
            'material_id'      => 0,
            'product_model_id' => $productModelId,
            'warehouse_id'     => 0,
            'before_quantity'  => $beforeQty,
            'change_quantity'  => $changeQty,
            'after_quantity'   => $afterQty,
            'business_type'    => $businessType,
            'business_id'      => $businessId,
            'operator_id'      => $operatorId,
            'remark'           => $remark,
            'create_time'      => time(),
        ]);
    }
}
