-- MES 制造执行系统权限规则
-- 使用 ON DUPLICATE KEY UPDATE 确保可重复执行

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
-- MES 主菜单
('mes', 'MES制造执行', 1, 1, 1, 0, 'fa fa-industry', 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 订单管理
('mes/order', '订单管理', 1, 1, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes') AS t), 'fa fa-shopping-cart', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order/index', '订单列表', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/order') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order/add', '添加订单', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/order') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order/edit', '编辑订单', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/order') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order/del', '删除订单', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/order') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order/materialList', '物料清单', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/order') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 产品管理
('mes/product', '产品管理', 1, 1, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes') AS t), 'fa fa-cube', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product/index', '产品列表', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/product') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product/add', '添加产品', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/product') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product/edit', '编辑产品', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/product') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product/del', '删除产品', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/product') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- BOM管理
('mes/bom', 'BOM管理', 1, 1, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes') AS t), 'fa fa-sitemap', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/index', 'BOM列表', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/bom') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/add', '添加BOM', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/bom') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/edit', '编辑BOM', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/bom') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/del', '删除BOM', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/bom') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/items', 'BOM明细', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/bom') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/approve', '审核BOM', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/bom') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 报工管理
('mes/report', '报工管理', 1, 1, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes') AS t), 'fa fa-clipboard', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/index', '报工列表', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/report') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/add', '添加报工', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/report') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/edit', '编辑报工', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/report') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/del', '删除报工', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/report') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/audit', '审核报工', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/report') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 客户管理
('mes/customer', '客户管理', 1, 1, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes') AS t), 'fa fa-users', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer/index', '客户列表', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/customer') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer/add', '添加客户', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/customer') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer/edit', '编辑客户', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/customer') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer/del', '删除客户', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/customer') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 工序管理
('mes/process', '工序管理', 1, 1, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes') AS t), 'fa fa-cogs', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process/index', '工序列表', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/process') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process/add', '添加工序', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/process') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process/edit', '编辑工序', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/process') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process/del', '删除工序', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/process') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 物料管理
('mes/material', '物料管理', 1, 1, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes') AS t), 'fa fa-cubes', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/index', '物料列表', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/material') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/add', '添加物料', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/material') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/edit', '编辑物料', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/material') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/del', '删除物料', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/material') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 供应商管理
('mes/supplier', '供应商管理', 1, 1, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes') AS t), 'fa fa-truck', 8, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/supplier/index', '供应商列表', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/supplier') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/supplier/add', '添加供应商', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/supplier') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/supplier/edit', '编辑供应商', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/supplier') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/supplier/del', '删除供应商', 2, 0, 1, (SELECT id FROM (SELECT id FROM fa_auth_rule WHERE name='mes/supplier') AS t), '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `type` = VALUES(`type`),
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`),
    `pid` = VALUES(`pid`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `update_time` = UNIX_TIMESTAMP();
