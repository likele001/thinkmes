-- 完整的权限规则初始化SQL（包含所有菜单和功能）
-- 如果权限规则列表为空，执行此文件

-- 清空现有数据（可选，如果不想清空可以注释掉）
-- TRUNCATE TABLE `fa_auth_rule`;

-- 基础菜单规则
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
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`),
    `type` = VALUES(`type`);

-- 管理员管理的子规则
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(101, 'admin/admin/add', '添加管理员', 2, 0, 1, 2, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(102, 'admin/admin/edit', '编辑管理员', 2, 0, 1, 2, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(103, 'admin/admin/del', '删除管理员', 2, 0, 1, 2, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(104, 'admin/admin/resetPwd', '重置密码', 2, 0, 1, 2, '', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`), 
    `status` = VALUES(`status`),
    `type` = VALUES(`type`);

-- 角色管理的子规则
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(201, 'admin/role/add', '添加角色', 2, 0, 1, 3, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(202, 'admin/role/edit', '编辑角色', 2, 0, 1, 3, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(203, 'admin/role/del', '删除角色', 2, 0, 1, 3, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`), 
    `status` = VALUES(`status`),
    `type` = VALUES(`type`);

-- 权限规则的子规则
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(401, 'admin/auth_rule/add', '添加规则', 2, 0, 1, 4, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(402, 'admin/auth_rule/edit', '编辑规则', 2, 0, 1, 4, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(403, 'admin/auth_rule/del', '删除规则', 2, 0, 1, 4, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`), 
    `status` = VALUES(`status`),
    `type` = VALUES(`type`);

-- 租户管理的子规则
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(701, 'admin/tenant/add', '添加租户', 2, 0, 1, 7, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(702, 'admin/tenant/edit', '编辑租户', 2, 0, 1, 7, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(703, 'admin/tenant/del', '删除租户', 2, 0, 1, 7, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`), 
    `status` = VALUES(`status`),
    `type` = VALUES(`type`);

-- 套餐管理的子规则
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(1001, 'admin/tenant_package/add', '添加套餐', 2, 0, 1, 10, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1002, 'admin/tenant_package/edit', '编辑套餐', 2, 0, 1, 10, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1003, 'admin/tenant_package/del', '删除套餐', 2, 0, 1, 10, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`), 
    `status` = VALUES(`status`),
    `type` = VALUES(`type`);

-- 订单管理的子规则
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(1101, 'admin/tenant_order/add', '创建订单', 2, 0, 1, 11, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1102, 'admin/tenant_order/pay', '订单支付', 2, 0, 1, 11, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1103, 'admin/tenant_order/cancel', '取消订单', 2, 0, 1, 11, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`), 
    `status` = VALUES(`status`),
    `type` = VALUES(`type`);

-- 用户管理的子规则
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(801, 'admin/member/add', '添加用户', 2, 0, 1, 8, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(802, 'admin/member/edit', '编辑用户', 2, 0, 1, 8, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(803, 'admin/member/del', '删除用户', 2, 0, 1, 8, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(804, 'admin/member/resetPwd', '重置密码', 2, 0, 1, 8, '', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`), 
    `status` = VALUES(`status`),
    `type` = VALUES(`type`);

-- 文件管理的子规则
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(901, 'admin/attachment/del', '删除文件', 2, 0, 1, 9, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`), 
    `status` = VALUES(`status`),
    `type` = VALUES(`type`);
