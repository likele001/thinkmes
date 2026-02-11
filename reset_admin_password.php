<?php
/**
 * 临时密码重置脚本
 * 使用方法：访问 http://your-domain.com/reset_admin_password.php?username=admin&password=新密码
 * 注意：使用后请删除此文件！
 */

require __DIR__ . '/vendor/autoload.php';

$app = new think\App();
$app->initialize();

use think\facade\Db;

// 安全检查：只在开发环境或通过命令行运行
$username = $_GET['username'] ?? '';
$newPassword = $_GET['password'] ?? '';

if (empty($username) || empty($newPassword)) {
    die('Usage: ?username=admin&password=新密码');
}

if (strlen($newPassword) < 6 || strlen($newPassword) > 32) {
    die('密码长度必须在 6-32 位之间');
}

try {
    // 查找管理员
    $admin = Db::name('admin')->where('username', $username)->find();
    
    if (!$admin) {
        die('管理员不存在：' . $username);
    }
    
    // 加密密码（只加密一次）
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    // 更新密码
    Db::name('admin')->where('id', $admin['id'])->update([
        'password' => $hashedPassword,
        'update_time' => time()
    ]);
    
    echo "密码重置成功！<br>";
    echo "用户名：{$username}<br>";
    echo "新密码：{$newPassword}<br>";
    echo "<br><strong style='color:red;'>请立即删除此文件！</strong>";
    
} catch (\Exception $e) {
    die('错误：' . $e->getMessage());
}
