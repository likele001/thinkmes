<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class TenantPackageFeatureModel extends Model
{
    protected $name = 'tenant_package_feature';

    protected $type = [
        'package_id' => 'integer',
        'create_time' => 'integer',
    ];
}
