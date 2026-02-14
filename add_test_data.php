<?php
// 加载 ThinkPHP 引导文件
define('APP_PATH', __DIR__ . '/app/');
require __DIR__ . '/vendor/autoload.php';
$app = new \think\App();
$app->initialize();

use think\facade\Db;

$tenants = Db::name('admin')->column('tenant_id');
$tenants = array_unique($tenants);

foreach ($tenants as $tenantId) {
    echo "Processing tenant: $tenantId\n";
    
    // 检查是否存在
    $exists = Db::name('mes_customer')
        ->where('tenant_id', $tenantId)
        ->where('customer_name', '上海服饰零售商')
        ->find();
        
    if (!$exists) {
        Db::name('mes_customer')->insert([
            'tenant_id' => $tenantId,
            'customer_name' => '上海服饰零售商',
            'contact_person' => '王经理',
            'contact_phone' => '13812345678',
            'address' => '上海市黄浦区南京东路123号',
            'status' => 1,
            'create_time' => time(),
            'update_time' => time()
        ]);
        echo "  Added customer for tenant $tenantId\n";
    } else {
        echo "  Customer already exists for tenant $tenantId\n";
    }
}

echo "Done.\n";
