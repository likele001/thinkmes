<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class ProductModelModel extends Model
{
    protected $name = 'mes_product_model';
    
    protected $type = [
        'tenant_id'   => 'integer',
        'product_id'  => 'integer',
        'status'      => 'integer',
        'stock'       => 'decimal',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    // 关联产品
    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'id');
    }

    // 关联工序工价
    public function processPrices()
    {
        return $this->hasMany(ProcessPriceModel::class, 'model_id', 'id');
    }

    // 获取完整显示名称
    public function getFullNameAttr($value, $data)
    {
        $productName = '';
        if (isset($data['product']) && $data['product']) {
            $productName = $data['product']['name'] ?? '';
        }
        $modelName = $data['name'] ?? '';
        $modelCode = $data['model_code'] ?? '';
        
        $fullName = $productName ? ($productName . ' - ') : '';
        $fullName .= $modelName;
        if ($modelCode) {
            $fullName .= ' (' . $modelCode . ')';
        }
        return $fullName;
    }
}
