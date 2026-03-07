-- 菜单层级调整：将原有一级菜单归入「系统管理」「扩展功能」「租户与用户」
-- 基础版：打包时已用 init_base.sql，解压安装即为新层级，无需执行本文件。
-- 完整版：已安装环境执行一次即可应用新菜单层级：
--   mysql -u用户 -p 数据库名 < database/migrate_menu_hierarchy.sql
-- 表前缀请按实际修改（默认 fa_）
-- 注意：不强制占用 id 9/10/11，避免覆盖已有菜单导致 502；父级用 name 唯一插入后按 name 关联。

-- 1. 插入父级（不指定 id，用 name 防重复；若已存在则更新）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/_sys', '系统管理', 1, 1, 1, 0, 'fas fa-cog', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/_ext', '扩展功能', 1, 1, 1, 0, 'fas fa-puzzle-piece', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/_tenant_user', '租户与用户', 1, 1, 1, 0, 'fas fa-users', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = 0;

-- 2. 子级归入父级（按父级 name 查 id 再更新，不写死 id）
UPDATE `fa_auth_rule` a
INNER JOIN (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/_sys' LIMIT 1) b ON 1=1
SET a.`pid` = b.id
WHERE a.`name` IN ('admin/admin/index','admin/role/index','admin/auth_rule/index','admin/config/index','admin/log/index');

UPDATE `fa_auth_rule` a
INNER JOIN (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/_ext' LIMIT 1) b ON 1=1
SET a.`pid` = b.id
WHERE a.`name` IN ('admin/addon/index','admin/app_center/index','admin/attachment/index','report','export','api','custom_field','workflow','notification','backup');

UPDATE `fa_auth_rule` a
INNER JOIN (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/_tenant_user' LIMIT 1) b ON 1=1
SET a.`pid` = b.id
WHERE a.`name` IN ('admin/tenant/index','admin/tenant_package/index','admin/tenant_package_feature/index','admin/tenant_order/index','admin/member/index');
