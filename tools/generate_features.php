<?php
/**
 * 生成完整的套餐功能列表
 * 运行: php tools/generate_features.php
 */

require __DIR__ . '/../vendor/autoload.php';

// 初始化应用
$app = new think\App();
$app->initialize();

// 查询所有顶级权限节点
$topRules = \think\facade\Db::name('auth_rule')
    ->where('status', 1)
    ->where('pid', 0)
    ->where('ispublic', 0)
    ->order('weigh DESC, id ASC')
    ->column('title', 'name');

echo "=== 从数据库读取的顶级功能模块 ===\n";
echo str_pad("模块代码", 30) . str_pad("模块名称", 40) . "\n";
echo str_repeat("-", 80) . "\n";
foreach ($topRules as $name => $title) {
    echo str_pad($name, 30) . str_pad($title, 40) . "\n";
}

echo "\n=== 推荐的套餐功能配置代码 ===\n";
echo "protected function getAllFeatures(): array\n";
echo "{\n";
echo "    return [\n";
foreach ($topRules as $name => $title) {
    echo "        '{$name}' => '{$title}',\n";
}
echo "        // 附加功能\n";
echo "        'custom_field' => '自定义字段',\n";
echo "        'workflow' => '工作流',\n";
echo "        'ai' => 'AI智能助手（附加收费）',\n";
echo "        'api' => 'API接口访问',\n";
echo "        'export' => '数据导出',\n";
echo "        'import' => '数据导入',\n";
echo "        'backup' => '数据备份',\n";
echo "        'notification' => '消息通知',\n";
echo "    ];\n";
echo "}\n";

echo "\n完成！\n";
