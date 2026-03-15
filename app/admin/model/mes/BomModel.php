<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * BOM模型
 */
class BomModel extends Model
{
    protected $name = 'mes_bom';

    protected $type = [
        'tenant_id'     => 'integer',
        'product_id'    => 'integer',
        'model_id'      => 'integer',
        'bom_type'      => 'integer',
        'base_quantity' => 'integer',
        'status'        => 'integer',
        'creator_id'    => 'integer',
        'approver_id'   => 'integer',
        'approve_time'  => 'integer',
        'publish_time'  => 'integer',
        'create_time'   => 'integer',
        'update_time'   => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            0 => '草稿',
            1 => '审核中',
            2 => '已发布',
            3 => '已废弃'
        ];
    }

    public function getBomTypeList(): array
    {
        return [
            0 => '产品BOM',
            1 => '通用模板',
        ];
    }

    /**
     * 生成BOM编号
     */
    public static function generateBomNo(): string
    {
        $prefix = 'BOM';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $date . $random;
    }

    /**
     * 关联BOM明细
     */
    public function items()
    {
        return $this->hasMany(BomItemModel::class, 'bom_id', 'id');
    }

    /**
     * 关联产品
     */
    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'id');
    }

    /**
     * 关联产品型号
     */
    public function model()
    {
        return $this->belongsTo(ProductModelModel::class, 'model_id', 'id');
    }

    /**
     * 根据产品+型号获取默认 BOM ID（供订单/排产等使用）
     * 优先级：1) 型号的 default_bom_id  2) 产品的 default_bom_id  3) 该型号已发布 BOM  4) 该产品通用 BOM(model_id=0)
     */
    public static function getDefaultBomId(int $tenantId, int $productId, int $modelId = 0): int
    {
        if ($modelId > 0) {
            $modelRow = ProductModelModel::where('tenant_id', $tenantId)->find($modelId);
            if ($modelRow && $modelRow->default_bom_id > 0) {
                $bom = self::where('tenant_id', $tenantId)->where('id', $modelRow->default_bom_id)->where('status', 2)->find();
                if ($bom) {
                    return (int) $bom->id;
                }
            }
        }
        $productRow = \app\admin\model\mes\ProductModel::where('tenant_id', $tenantId)->find($productId);
        if ($productRow && $productRow->default_bom_id > 0) {
            $bom = self::where('tenant_id', $tenantId)->where('id', $productRow->default_bom_id)->where('status', 2)->find();
            if ($bom) {
                return (int) $bom->id;
            }
        }
        if ($modelId > 0) {
            $bom = self::where('tenant_id', $tenantId)->where('product_id', $productId)->where('model_id', $modelId)->where('status', 2)->order('id', 'desc')->find();
            if ($bom) {
                return (int) $bom->id;
            }
        }
        // 若产品没有专用 BOM，再退化到产品通用 BOM(model_id=0)
        $bom = self::where('tenant_id', $tenantId)->where('product_id', $productId)->where('model_id', 0)->where('status', 2)->order('id', 'desc')->find();
        return $bom ? (int) $bom->id : 0;
    }
}
