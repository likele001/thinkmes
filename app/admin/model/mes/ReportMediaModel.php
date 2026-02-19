<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class ReportMediaModel extends Model
{
    protected $name = 'mes_report_media';

    protected $type = [
        'tenant_id'  => 'integer',
        'report_id'  => 'integer',
        'create_time'=> 'integer',
    ];
}

