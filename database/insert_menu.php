<?php
// 添加租户审核菜单
$pdo = new PDO('mysql:host=127.0.0.1;dbname=thinkmes', 'thinkmes', '123456');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo->beginTransaction();

    // 获取租户菜单父ID
    $stmt = $pdo->query("SELECT id FROM fa_auth_rule WHERE name = 'tenant' LIMIT 1");
    $result = $stmt->fetch();
    $tenantPid = $result ? $result['id'] : 0;

    echo "租户父菜单ID: $tenantPid\n";

    // 插入租户审核父菜单
    $sql = "INSERT INTO fa_auth_rule (pid, name, title, icon, type, ismenu, status, sort, create_time, update_time)
             VALUES (:pid, :name, :title, :icon, :type, :ismenu, :status, :sort, :create_time, :update_time)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':pid' => $tenantPid,
        ':name' => 'tenant_audit',
        ':title' => '租户审核',
        ':icon' => 'fa fa-user-check',
        ':type' => 1,
        ':ismenu' => 1,
        ':status' => 1,
        ':sort' => 8,
        ':create_time' => time(),
        ':update_time' => time()
    ]);

    echo "租户审核父菜单插入成功，ID: " . $pdo->lastInsertId() . "\n";

    // 获取刚插入的ID
    $auditPid = $pdo->lastInsertId();

    // 插入子菜单
    $menus = [
        ['tenant_audit/index', '注册列表', 1],
        ['tenant_audit/approve', '审核通过', 2],
        ['tenant_audit/reject', '审核拒绝', 3],
        ['tenant_audit/del', '删除记录', 4],
    ];

    $sort = 1;
    foreach ($menus as $menu) {
        $sql = "INSERT INTO fa_auth_rule (pid, name, title, icon, type, ismenu, status, sort, create_time, update_time)
                 VALUES (:pid, :name, :title, :icon, :type, :ismenu, :status, :sort, :create_time, :update_time)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':pid' => $auditPid,
            ':name' => $menu[0],
            ':title' => $menu[1],
            ':icon' => '',
            ':type' => 1,
            ':ismenu' => 1,
            ':status' => 1,
            ':sort' => $sort++,
            ':create_time' => time(),
            ':update_time' => time()
        ]);
        echo "子菜单插入成功: {$menu[0]} - {$menu[1]}\n";
    }

    $pdo->commit();
    echo "\n=== 菜单添加成功 ===\n";

    // 验证结果
    $sql = "SELECT id, pid, name, title, icon FROM fa_auth_rule WHERE pid = :pid ORDER BY sort";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => $auditPid]);
    echo "\n新添加的菜单：\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "[ID: {$row['id']}] [PID: {$row['pid']}] [Name: {$row['name']}] [Title: {$row['title']}] [Icon: {$row['icon']}] [Sort: {$row['sort']}]\n";
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "错误: " . $e->getMessage() . "\n";
}
