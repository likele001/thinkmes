-- MES 新增模块菜单配置
-- 执行时间: 2026-02-11

-- 首先获取MES根菜单的ID (假设已存在，通常是1194)
SET @mes_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes' LIMIT 1);

-- ----------------------------
-- 1. 采购管理模块菜单
-- ----------------------------
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `icon`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@mes_pid, 'mes/purchase', '采购管理', 'fa fa-shopping-cart', 1, 1, 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取刚插入的采购管理ID
SET @purchase_pid = LAST_INSERT_ID();

-- 采购管理子菜单
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@purchase_pid, 'mes/purchase/request', '采购申请', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@purchase_pid, 'mes/purchase/inbound', '采购入库', 1, 1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ----------------------------
-- 2. 库存管理模块菜单
-- ----------------------------
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `icon`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@mes_pid, 'mes/stock', '库存管理', 'fa fa-warehouse', 1, 1, 25, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取刚插入的库存管理ID
SET @stock_pid = LAST_INSERT_ID();

-- 库存管理子菜单
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@stock_pid, 'mes/stock/index', '库存查询', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@stock_pid, 'mes/stock/outbound', '生产领料', 1, 1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@stock_pid, 'mes/stock/log', '库存流水', 1, 1, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ----------------------------
-- 3. 质检管理模块菜单
-- ----------------------------
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `icon`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@mes_pid, 'mes/quality', '质检管理', 'fa fa-check-circle', 1, 1, 26, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取刚插入的质检管理ID
SET @quality_pid = LAST_INSERT_ID();

-- 质检管理子菜单
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@quality_pid, 'mes/quality/standard', '质检标准', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@quality_pid, 'mes/quality/check', '质检记录', 1, 1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@quality_pid, 'mes/quality/statistics', '质检统计', 1, 1, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ----------------------------
-- 4. 发货管理模块菜单
-- ----------------------------
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `icon`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@mes_pid, 'mes/shipment', '发货管理', 'fa fa-truck', 1, 1, 27, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取刚插入的发货管理ID
SET @shipment_pid = LAST_INSERT_ID();

-- 发货管理子菜单
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@shipment_pid, 'mes/shipment/index', '发货列表', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@shipment_pid, 'mes/shipment/add', '创建发货', 1, 1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ----------------------------
-- 5. 仓库管理模块菜单
-- ----------------------------
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `icon`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@mes_pid, 'mes/warehouse', '仓库管理', 'fa fa-home', 1, 1, 24, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取刚插入的仓库管理ID
SET @warehouse_pid = LAST_INSERT_ID();

-- 仓库管理子菜单
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@warehouse_pid, 'mes/warehouse/index', '仓库列表', 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@warehouse_pid, 'mes/warehouse/add', '添加仓库', 1, 1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ----------------------------
-- 6. 补充审核操作权限
-- ----------------------------
-- 采购审核
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@purchase_pid, 'mes/purchase/auditRequest', '审核采购申请', 1, 1, 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 确认入库
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@purchase_pid, 'mes/purchase/confirmInbound', '确认入库', 1, 1, 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 确认出库
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@stock_pid, 'mes/stock/confirmOutbound', '确认出库', 1, 1, 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 库存盘点
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@stock_pid, 'mes/stock/check', '库存盘点', 1, 1, 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 质检操作
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@quality_pid, 'mes/quality/addCheck', '创建质检', 1, 1, 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 发货操作
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@shipment_pid, 'mes/shipment/confirmSign', '确认签收', 1, 1, 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@shipment_pid, 'mes/shipment/track', '物流跟踪', 1, 1, 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ----------------------------
-- 7. 调整排序值（将新模块放在合适位置）
-- ----------------------------
-- 采购管理 sort=30 (在工资管理之后)
-- 库存管理 sort=25 (在物料管理之后)
-- 质检管理 sort=26 (在库存管理之后)
-- 发货管理 sort=27 (在质检管理之后)
-- 仓库管理 sort=24 (在生产计划之前)

-- 查看添加的菜单
SELECT id, pid, name, title, icon, sort
FROM fa_auth_rule
WHERE name LIKE 'mes/%' AND create_time >= UNIX_TIMESTAMP('2026-02-11 00:00:00')
ORDER BY sort ASC;
