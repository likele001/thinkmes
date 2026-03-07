<?php
declare(strict_types=1);

namespace app\admin\model;

use app\common\model\BaseModel as Model;

class PrintTemplateModel extends Model
{
    protected $name = 'print_template';

    protected $type = [
        'tenant_id'   => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public static function getTypeList(): array
    {
        return [
            'order'        => '生产订单',
            'sales_order'  => '销售订单',
            'shipment'     => '发货单',
            'contract'     => '合同',
            'other'        => '其他',
        ];
    }
}
