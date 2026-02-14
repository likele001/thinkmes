<?php
// 添加质检管理权限规则的PHP脚本

require __DIR__ . '/vendor/autoload.php';

use thinkacadeb;
use app\admin\model\AuthRuleModel;

// 初始化应用
$app = require __DIR__ . '/app/config.php';

// 连接数据库
try {
    // 查找质检管理菜单的ID
    $qualityMenu = AuthRuleModel::where('name', 'IN', ['mes/quality/index', 'quality/index'])->find();
    $qualityMenuId = $qualityMenu ? $qualityMenu->id : 0;
    
    // 如果找不到质检管理菜单，先创建它
    if (!$qualityMenuId) {
        $qualityMenu = AuthRuleModel::create([
            'name' => 'mes/quality/index',
            'title' => '质检管理',
            'type' => 1,
            'ismenu' => 1,
            'status' => 1,
            'pid' => 0,
            'icon' => 'fas fa-check-circle',
            'sort' => 50,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        $qualityMenuId = $qualityMenu->id;
        echo "创建质检管理菜单成功，ID: {$qualityMenuId}\n";
    } else {
        echo "质检管理菜单已存在，ID: {$qualityMenuId}\n";
    }
    
    // 添加质检标准管理子菜单
    $qualityStandardMenu = AuthRuleModel::where('name', 'mes/quality/standard')->find();
    $qualityStandardMenuId = $qualityStandardMenu ? $qualityStandardMenu->id : 0;
    
    if (!$qualityStandardMenuId) {
        $qualityStandardMenu = AuthRuleModel::create([
            'name' => 'mes/quality/standard',
            'title' => '质检标准管理',
            'type' => 1,
            'ismenu' => 1,
            'status' => 1,
            'pid' => $qualityMenuId,
            'icon' => 'fas fa-list',
            'sort' => 10,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        $qualityStandardMenuId = $qualityStandardMenu->id;
        echo "创建质检标准管理子菜单成功，ID: {$qualityStandardMenuId}\n";
    } else {
        echo "质检标准管理子菜单已存在，ID: {$qualityStandardMenuId}\n";
    }
    
    // 添加质检记录管理子菜单
    $qualityCheckMenu = AuthRuleModel::where('name', 'mes/quality/check')->find();
    $qualityCheckMenuId = $qualityCheckMenu ? $qualityCheckMenu->id : 0;
    
    if (!$qualityCheckMenuId) {
        $qualityCheckMenu = AuthRuleModel::create([
            'name' => 'mes/quality/check',
            'title' => '质检记录管理',
            'type' => 1,
            'ismenu' => 1,
            'status' => 1,
            'pid' => $qualityMenuId,
            'icon' => 'fas fa-clipboard-check',
            'sort' => 20,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        $qualityCheckMenuId = $qualityCheckMenu->id;
        echo "创建质检记录管理子菜单成功，ID: {$qualityCheckMenuId}\n";
    } else {
        echo "质检记录管理子菜单已存在，ID: {$qualityCheckMenuId}\n";
    }
    
    // 添加质检统计子菜单
    $qualityStatisticsMenu = AuthRuleModel::where('name', 'mes/quality/statistics')->find();
    $qualityStatisticsMenuId = $qualityStatisticsMenu ? $qualityStatisticsMenu->id : 0;
    
    if (!$qualityStatisticsMenuId) {
        $qualityStatisticsMenu = AuthRuleModel::create([
            'name' => 'mes/quality/statistics',
            'title' => '质检统计',
            'type' => 1,
            'ismenu' => 1,
            'status' => 1,
            'pid' => $qualityMenuId,
            'icon' => 'fas fa-chart-bar',
            'sort' => 30,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        $qualityStatisticsMenuId = $qualityStatisticsMenu->id;
        echo "创建质检统计子菜单成功，ID: {$qualityStatisticsMenuId}\n";
    } else {
        echo "质检统计子菜单已存在，ID: {$qualityStatisticsMenuId}\n";
    }
    
    // 添加添加质检标准权限
    $addStandardRule = AuthRuleModel::where('name', 'mes/quality/addStandard')->find();
    
    if (!$addStandardRule) {
        $addStandardRule = AuthRuleModel::create([
            'name' => 'mes/quality/addStandard',
            'title' => '添加质检标准',
            'type' => 2,
            'ismenu' => 0,
            'status' => 1,
            'pid' => $qualityStandardMenuId,
            'icon' => '',
            'sort' => 10,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        echo "创建添加质检标准权限成功，ID: {$addStandardRule->id}\n";
    } else {
        echo "添加质检标准权限已存在，ID: {$addStandardRule->id}\n";
    }
    
    // 添加添加质检记录权限
    $addCheckRule = AuthRuleModel::where('name', 'mes/quality/addCheck')->find();
    
    if (!$addCheckRule) {
        $addCheckRule = AuthRuleModel::create([
            'name' => 'mes/quality/addCheck',
            'title' => '添加质检记录',
            'type' => 2,
            'ismenu' => 0,
            'status' => 1,
            'pid' => $qualityCheckMenuId,
            'icon' => '',
            'sort' => 10,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        echo "创建添加质检记录权限成功，ID: {$addCheckRule->id}\n";
    } else {
        echo "添加质检记录权限已存在，ID: {$addCheckRule->id}\n";
    }
    
    echo "\n质检管理权限规则添加完成！\n";
    
} catch (Exception $e) {
    echo "错误: {$e->getMessage()}\n";
    echo "错误堆栈: {$e->getTraceAsString()}\n";
}
