# 权限规则列表为空 - 快速修复指南

## 问题描述

权限规则列表页面显示为空，没有任何数据。

## 原因

数据库中没有初始化权限规则数据。

## 解决方法

### 方法1：执行初始化SQL（推荐）

执行以下SQL文件来初始化权限规则数据：

```bash
mysql -uroot -p thinkmes < database/init_auth_rules_complete.sql
```

或者直接在数据库中执行：

```sql
-- 执行 database/init_auth_rules_complete.sql 文件中的SQL
```

### 方法2：执行种子数据文件

如果之前已经执行过 `init.sql`，可以执行：

```bash
mysql -uroot -p thinkmes < database/seed_auth_rule.sql
```

### 方法3：手动添加基础规则

如果上述方法都不行，可以手动执行以下SQL：

```sql
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(1, 'admin/index/index', '首页', 1, 1, 1, 0, 'fas fa-tachometer-alt', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 'admin/admin/index', '管理员管理', 1, 1, 1, 0, 'fas fa-user-shield', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, 'admin/role/index', '角色管理', 1, 1, 1, 0, 'fas fa-users-cog', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, 'admin/auth_rule/index', '权限规则', 1, 1, 1, 0, 'fas fa-sitemap', 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, 'admin/config/index', '系统配置', 1, 1, 1, 0, 'fas fa-cog', 40, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(6, 'admin/log/index', '操作日志', 1, 1, 1, 0, 'fas fa-history', 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 'admin/tenant/index', '租户管理', 1, 1, 1, 0, 'fas fa-building', 15, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 'admin/member/index', '用户管理', 1, 1, 1, 0, 'fas fa-user', 35, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(9, 'admin/attachment/index', '文件管理', 1, 1, 1, 0, 'fas fa-file-alt', 38, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(10, 'admin/tenant_package/index', '套餐管理', 1, 1, 1, 0, 'fas fa-box', 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(11, 'admin/tenant_order/index', '订单管理', 1, 1, 1, 0, 'fas fa-shopping-cart', 17, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`), 
    `icon` = VALUES(`icon`), 
    `sort` = VALUES(`sort`), 
    `ismenu` = VALUES(`ismenu`);
```

## 验证

执行SQL后，刷新权限规则页面，应该能看到数据了。

如果仍然为空，请检查：
1. 数据库连接是否正确
2. 表 `fa_auth_rule` 是否存在
3. 浏览器控制台是否有错误
4. 网络请求是否成功（F12 -> Network -> 查看 `/admin/auth_rule/tree` 请求）

## 注意事项

- 执行SQL前请备份数据库
- 如果表中有数据但页面仍为空，可能是前端JS问题，请检查浏览器控制台错误
