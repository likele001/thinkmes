<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class ProcessRouteModel extends Model
{
    protected $name = 'mes_process_route';

    protected $type = [
        'tenant_id'   => 'integer',
        'product_id'  => 'integer',
        'model_id'    => 'integer',
        'route_type'  => 'integer',
        'status'      => 'integer',
        'is_default'  => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'id');
    }

    public function model()
    {
        return $this->belongsTo(ProductModelModel::class, 'model_id', 'id');
    }

    public static function getRouteTypeList(): array
    {
        return [
            1 => '标准路线',
            2 => '备选路线',
            3 => '临时路线',
        ];
    }

    public static function getStatusList(): array
    {
        return [
            0 => '草稿',
            1 => '审核中',
            2 => '已发布',
            3 => '已归档',
        ];
    }

    public static function getRouteByModel(int $tenantId, int $modelId): ?self
    {
        $query = self::where('tenant_id', $tenantId)
            ->where('model_id', $modelId)
            ->where('status', 2)
            ->order('is_default', 'desc')
            ->order('id', 'desc');
        return $query->find();
    }
}

