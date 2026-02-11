-- 添加租户注册和审核相关菜单
-- 执行前请先查询当前最大菜单ID，替换下面的 @MAX_ID

-- 租户菜单的父ID
SET @tenant_pid = (SELECT id FROM fa_auth_rule WHERE name = 'tenant' LIMIT 1);

-- 插入租户注册审核菜单（仅超管可见）
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `icon`, `type`, `status`, `sort`, `remark`, `create_time`, `update_time`) VALUES
(@tenant_pid, 'tenant_audit', '租户审核', 'fa fa-user-check', 1, 1, 8, '租户注册审核管理', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取刚插入的菜单ID
SET @tenant_audit_pid = LAST_INSERT_ID();

-- 租户审核子菜单
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `remark`, `create_time`, `update_time`) VALUES
(@tenant_audit_pid, 'tenant_audit/index', '注册列表', 1, 1, 1, '查看待审核的注册申请', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@tenant_audit_pid, 'tenant_audit/approve', '审核通过', 1, 1, 2, '审核通过自动创建租户和管理员', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@tenant_audit_pid, 'tenant_audit/reject', '审核拒绝', 1, 1, 3, '拒绝注册申请', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@tenant_audit_pid, 'tenant_audit/del', '删除记录', 1, 1, 4, '删除注册申请记录', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 验证插入结果
SELECT
    ar.id,
    ar.pid,
    ar.name,
    ar.title,
    ar.icon,
    ar.sort
FROM fa_auth_rule ar
WHERE ar.name IN ('tenant_audit', 'tenant_audit/index', 'tenant_audit/approve', 'tenant_audit/reject', 'tenant_audit/del')
ORDER BY ar.id;
