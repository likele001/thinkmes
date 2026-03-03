-- CRM 应用菜单与权限规则
-- 表前缀由应用中心执行时替换 fa_ 为实际前缀

-- 1. CRM 主菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm', 'CRM客户关系', 1, 1, 1, 0, 'fa fa-handshake', 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @crm_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm' LIMIT 1), 0);

-- 2. 客户管理
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm/customer', '客户管理', 1, 1, 1, @crm_pid, 'fa fa-users', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

SET @customer_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm/customer' LIMIT 1), 0);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/customer/index', '客户列表', 2, 0, 1, @customer_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/customer/add', '添加客户', 2, 0, 1, @customer_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/customer/edit', '编辑客户', 2, 0, 1, @customer_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/customer/del', '删除客户', 2, 0, 1, @customer_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @customer_pid;

-- 3. 联系人（挂在客户下，作为二级菜单）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm/contact', '联系人', 1, 1, 1, @crm_pid, 'fa fa-address-card', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

SET @contact_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm/contact' LIMIT 1), 0);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/contact/index', '联系人列表', 2, 0, 1, @contact_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/contact/add', '添加联系人', 2, 0, 1, @contact_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/contact/edit', '编辑联系人', 2, 0, 1, @contact_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/contact/del', '删除联系人', 2, 0, 1, @contact_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @contact_pid;

-- 4. 商机管理（Phase2 使用）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm/opportunity', '商机管理', 1, 1, 1, @crm_pid, 'fa fa-lightbulb', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

SET @opp_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm/opportunity' LIMIT 1), 0);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/opportunity/index', '商机列表', 2, 0, 1, @opp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/opportunity/add', '添加商机', 2, 0, 1, @opp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/opportunity/edit', '编辑商机', 2, 0, 1, @opp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/opportunity/del', '删除商机', 2, 0, 1, @opp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @opp_pid;

-- 5. 合同管理
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm/contract', '合同管理', 1, 1, 1, @crm_pid, 'fa fa-file-contract', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

SET @contract_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm/contract' LIMIT 1), 0);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/contract/index', '合同列表', 2, 0, 1, @contract_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/contract/add', '添加合同', 2, 0, 1, @contract_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/contract/edit', '编辑合同', 2, 0, 1, @contract_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/contract/del', '删除合同', 2, 0, 1, @contract_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @contract_pid;

-- 6. 跟进记录
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm/follow', '跟进记录', 1, 1, 1, @crm_pid, 'fa fa-tasks', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

SET @follow_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm/follow' LIMIT 1), 0);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/follow/index', '跟进列表', 2, 0, 1, @follow_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/follow/add', '添加跟进', 2, 0, 1, @follow_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/follow/edit', '编辑跟进', 2, 0, 1, @follow_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/follow/del', '删除跟进', 2, 0, 1, @follow_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @follow_pid;

-- 7. 回款管理
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm/payment', '回款管理', 1, 1, 1, @crm_pid, 'fa fa-money-bill', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

SET @payment_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm/payment' LIMIT 1), 0);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/payment/index', '回款列表', 2, 0, 1, @payment_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/payment/add', '添加回款', 2, 0, 1, @payment_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/payment/edit', '编辑回款', 2, 0, 1, @payment_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/payment/del', '删除回款', 2, 0, 1, @payment_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @payment_pid;

-- 8. 产品管理（CRM 独立产品）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm/product', '产品管理', 1, 1, 1, @crm_pid, 'fa fa-cube', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

SET @product_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm/product' LIMIT 1), 0);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/product/index', '产品列表', 2, 0, 1, @product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/product/add', '添加产品', 2, 0, 1, @product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/product/edit', '编辑产品', 2, 0, 1, @product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/product/del', '删除产品', 2, 0, 1, @product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @product_pid;

-- 9. 销售订单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm/sales_order', '销售订单', 1, 1, 1, @crm_pid, 'fa fa-shopping-cart', 8, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

SET @sales_order_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm/sales_order' LIMIT 1), 0);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/sales_order/index', '订单列表', 2, 0, 1, @sales_order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/sales_order/add', '添加订单', 2, 0, 1, @sales_order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/sales_order/edit', '编辑订单', 2, 0, 1, @sales_order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/sales_order/del', '删除订单', 2, 0, 1, @sales_order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/sales_order/toMes', '转生产订单', 2, 0, 1, @sales_order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @sales_order_pid;

-- 10. CRM 报表
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm/report', 'CRM报表', 1, 1, 1, @crm_pid, 'fa fa-chart-bar', 9, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

SET @report_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'crm/report' LIMIT 1), 0);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/report/index', '报表首页', 2, 0, 1, @report_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @report_pid;
