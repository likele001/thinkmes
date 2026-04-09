-- MES 制造执行系统菜单SQL
-- 先插入主菜单，再插入子菜单，使用固定ID避免子查询问题

-- 1. 插入MES主菜单（如果不存在）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes', 'MES制造执行', 1, 1, 1, 0, 'fa fa-industry', 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

-- 2. 获取MES主菜单ID（用于后续插入）
SET @mes_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes' LIMIT 1);

-- 2.1 插入MES首页权限（如果不存在）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/index', 'MES首页', 2, 0, 1, @mes_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @mes_pid;

-- 3. 插入订单管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/order', '订单管理', 1, 1, 1, @mes_pid, 'fa fa-shopping-cart', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @order_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/order' LIMIT 1);

-- 订单管理的操作权限
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/order/index', '订单列表', 2, 0, 1, @order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order/add', '添加订单', 2, 0, 1, @order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order/edit', '编辑订单', 2, 0, 1, @order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order/del', '删除订单', 2, 0, 1, @order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order/materialList', '物料清单', 2, 0, 1, @order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @order_pid;

-- 4. 插入产品管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/product', '产品管理', 1, 1, 1, @mes_pid, 'fa fa-cube', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @product_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/product' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/product/index', '产品列表', 2, 0, 1, @product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product/add', '添加产品', 2, 0, 1, @product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product/edit', '编辑产品', 2, 0, 1, @product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product/del', '删除产品', 2, 0, 1, @product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @product_pid;

-- 5. 插入BOM管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/bom', 'BOM管理', 1, 1, 1, @mes_pid, 'fa fa-sitemap', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @bom_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/bom' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/bom/index', 'BOM列表', 2, 0, 1, @bom_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/add', '添加BOM', 2, 0, 1, @bom_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/edit', '编辑BOM', 2, 0, 1, @bom_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/del', '删除BOM', 2, 0, 1, @bom_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/items', 'BOM明细', 2, 0, 1, @bom_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom/approve', '审核BOM', 2, 0, 1, @bom_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @bom_pid;

-- 6. 插入报工管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/report', '报工管理', 1, 1, 1, @mes_pid, 'fa fa-clipboard', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @report_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/report' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/report/index', '报工列表', 2, 0, 1, @report_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/add', '添加报工', 2, 0, 1, @report_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/edit', '编辑报工', 2, 0, 1, @report_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/del', '删除报工', 2, 0, 1, @report_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/audit_page', '审核页面', 2, 0, 1, @report_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report/audit', '审核报工', 2, 0, 1, @report_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @report_pid;

-- 7. 插入客户管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/customer', '客户管理', 1, 1, 1, @mes_pid, 'fa fa-users', 9, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @customer_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/customer' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/customer/index', '客户列表', 2, 0, 1, @customer_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer/add', '添加客户', 2, 0, 1, @customer_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer/edit', '编辑客户', 2, 0, 1, @customer_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer/del', '删除客户', 2, 0, 1, @customer_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @customer_pid;

-- 客户产品配置管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/customer_product', '客户产品配置', 1, 1, 1, @mes_pid, 'fa fa-list', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @customer_product_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/customer_product' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/customer_product/index', '配置列表', 2, 0, 1, @customer_product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer_product/add', '添加配置', 2, 0, 1, @customer_product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer_product/edit', '编辑配置', 2, 0, 1, @customer_product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer_product/del', '删除配置', 2, 0, 1, @customer_product_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @customer_product_pid;

-- 8. 插入工序管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/process', '工序管理', 1, 1, 1, @mes_pid, 'fa fa-cogs', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @process_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/process' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/process/index', '工序列表', 2, 0, 1, @process_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process/add', '添加工序', 2, 0, 1, @process_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process/edit', '编辑工序', 2, 0, 1, @process_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process/del', '删除工序', 2, 0, 1, @process_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @process_pid;

-- 9. 插入物料管理菜单（pid 用子查询，便于单独执行本段）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
SELECT 'mes/material', '物料管理', 1, 1, 1, id, 'fa fa-cubes', 11, UNIX_TIMESTAMP(), UNIX_TIMESTAMP() FROM (SELECT id FROM fa_auth_rule WHERE name = 'mes' LIMIT 1) t
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = VALUES(`pid`);

SET @material_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/material' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/material/index', '物料列表', 2, 0, 1, @material_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/add', '添加物料', 2, 0, 1, @material_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/edit', '编辑物料', 2, 0, 1, @material_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/del', '删除物料', 2, 0, 1, @material_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @material_pid;

-- 9.1 插入物料分类菜单（pid 用子查询，便于单独执行本段）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
SELECT 'mes/material_category', '物料分类', 1, 1, 1, id, 'fa fa-folder', 12, UNIX_TIMESTAMP(), UNIX_TIMESTAMP() FROM (SELECT id FROM fa_auth_rule WHERE name = 'mes' LIMIT 1) t
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = VALUES(`pid`);

SET @material_category_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/material_category' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/material_category/index', '物料分类列表', 2, 0, 1, @material_category_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material_category/add', '添加物料分类', 2, 0, 1, @material_category_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material_category/edit', '编辑物料分类', 2, 0, 1, @material_category_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material_category/del', '删除物料分类', 2, 0, 1, @material_category_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = VALUES(`pid`);

-- 10. 插入供应商管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/supplier', '供应商管理', 1, 1, 1, @mes_pid, 'fa fa-truck', 13, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @supplier_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/supplier' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/supplier/index', '供应商列表', 2, 0, 1, @supplier_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/supplier/add', '添加供应商', 2, 0, 1, @supplier_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/supplier/edit', '编辑供应商', 2, 0, 1, @supplier_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/supplier/del', '删除供应商', 2, 0, 1, @supplier_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @supplier_pid;

-- 产品型号管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/product_model', '产品型号', 1, 1, 1, @mes_pid, 'fa fa-tags', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @product_model_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/product_model' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/product_model/index', '型号列表', 2, 0, 1, @product_model_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product_model/add', '添加型号', 2, 0, 1, @product_model_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product_model/edit', '编辑型号', 2, 0, 1, @product_model_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product_model/del', '删除型号', 2, 0, 1, @product_model_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @product_model_pid;

-- 工序工价管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/process_price', '工序工价', 1, 1, 1, @mes_pid, 'fa fa-money', 14, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @process_price_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/process_price' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/process_price/index', '工价列表', 2, 0, 1, @process_price_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process_price/add', '添加工价', 2, 0, 1, @process_price_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process_price/edit', '编辑工价', 2, 0, 1, @process_price_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process_price/del', '删除工价', 2, 0, 1, @process_price_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process_price/batch', '批量设置', 2, 0, 1, @process_price_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @process_price_pid;

-- 生产计划管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/production_plan', '生产计划', 1, 1, 1, @mes_pid, 'fa fa-calendar', 15, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @production_plan_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/production_plan' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/production_plan/index', '计划列表', 2, 0, 1, @production_plan_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/production_plan/add', '添加计划', 2, 0, 1, @production_plan_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/production_plan/edit', '编辑计划', 2, 0, 1, @production_plan_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/production_plan/del', '删除计划', 2, 0, 1, @production_plan_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/production_plan/start', '开始计划', 2, 0, 1, @production_plan_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/production_plan/pause', '暂停计划', 2, 0, 1, @production_plan_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/production_plan/resume', '恢复计划', 2, 0, 1, @production_plan_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/production_plan/finish', '完成计划', 2, 0, 1, @production_plan_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/production_plan/getOrderModels', '获取订单型号', 2, 0, 1, @production_plan_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @production_plan_pid;

-- 分工分配管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/allocation', '分工分配', 1, 1, 1, @mes_pid, 'fa fa-tasks', 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @allocation_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/allocation' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/allocation/index', '分配列表', 2, 0, 1, @allocation_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/allocation/add', '添加分配', 2, 0, 1, @allocation_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/allocation/edit', '编辑分配', 2, 0, 1, @allocation_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/allocation/del', '删除分配', 2, 0, 1, @allocation_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/allocation/batch', '批量分配', 2, 0, 1, @allocation_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @allocation_pid;

-- 分工二维码管理菜单（直接子查询更新，可单独执行）
UPDATE `fa_auth_rule` a
INNER JOIN (SELECT id FROM `fa_auth_rule` WHERE name = 'mes' LIMIT 1) m ON 1=1
SET a.`pid` = m.id, a.`title` = '分工二维码', a.`icon` = 'fa fa-qrcode', a.`sort` = 17, a.`update_time` = UNIX_TIMESTAMP()
WHERE a.name = 'mes/allocation_qrcode';

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
SELECT 'mes/allocation_qrcode', '分工二维码', 1, 1, 1, m.id, 'fa fa-qrcode', 17, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (SELECT id FROM `fa_auth_rule` WHERE name = 'mes' LIMIT 1) m
WHERE NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE name = 'mes/allocation_qrcode' LIMIT 1);

UPDATE `fa_auth_rule` a
INNER JOIN (SELECT id FROM `fa_auth_rule` WHERE name = 'mes/allocation_qrcode' LIMIT 1) p ON 1=1
SET a.`pid` = p.id, a.`update_time` = UNIX_TIMESTAMP()
WHERE a.name IN ('mes/allocation_qrcode/index', 'mes/allocation_qrcode/getInfo', 'mes/allocation_qrcode/regenerate');

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
SELECT r.name, r.title, 2, 0, 1, p.id, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (SELECT 'mes/allocation_qrcode/index' AS name, '二维码列表' AS title
      UNION ALL SELECT 'mes/allocation_qrcode/getInfo', '查看二维码'
      UNION ALL SELECT 'mes/allocation_qrcode/regenerate', '重新生成') r
CROSS JOIN (SELECT id FROM `fa_auth_rule` WHERE name = 'mes/allocation_qrcode' LIMIT 1) p
WHERE NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE name = r.name LIMIT 1);

-- 工艺路线菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/process_route', '工艺路线', 1, 1, 1, @mes_pid, 'fa fa-road', 8, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @process_route_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/process_route' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/process_route/index', '路线列表', 2, 0, 1, @process_route_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process_route/add', '添加路线', 2, 0, 1, @process_route_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process_route/edit', '编辑路线', 2, 0, 1, @process_route_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process_route/del', '删除路线', 2, 0, 1, @process_route_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @process_route_pid;

-- 工资管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/wage', '工资管理', 1, 1, 1, @mes_pid, 'fa fa-yen-sign', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

-- 智能排产菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/schedule', '智能排产', 1, 1, 1, @mes_pid, 'fa fa-calendar-check', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @schedule_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/schedule' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/schedule/index', '排产列表', 2, 0, 1, @schedule_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/schedule/ganttData', '排产视图数据', 2, 0, 1, @schedule_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/schedule/generate', '生成排产', 2, 0, 1, @schedule_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/schedule/publish', '下发分工', 2, 0, 1, @schedule_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/schedule/del', '删除排产', 2, 0, 1, @schedule_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @schedule_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/user_process_capacity', '员工产能', 1, 1, 1, @mes_pid, 'fa fa-user-cog', 61, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @capacity_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/user_process_capacity' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/user_process_capacity/index', '列表', 2, 0, 1, @capacity_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/user_process_capacity/add', '添加', 2, 0, 1, @capacity_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/user_process_capacity/edit', '编辑', 2, 0, 1, @capacity_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/user_process_capacity/del', '删除', 2, 0, 1, @capacity_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @capacity_pid;

SET @wage_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/wage' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/wage/statistics', '工资统计', 2, 1, 1, @wage_pid, 'fa fa-chart-bar', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/wage/index', '工资明细', 2, 1, 1, @wage_pid, 'fa fa-list', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/wage/export', '导出工资', 2, 0, 1, @wage_pid, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @wage_pid;

-- 追溯码管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/trace_code', '追溯码管理', 1, 1, 1, @mes_pid, 'fa fa-qrcode', 18, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @trace_code_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/trace_code' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/trace_code/index', '追溯码列表', 2, 0, 1, @trace_code_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/trace_code/generate', '生成追溯码', 2, 0, 1, @trace_code_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/trace_code/batchGenerate', '批量生成', 2, 0, 1, @trace_code_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/trace_code/query', '追溯查询', 2, 0, 1, @trace_code_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/trace_code/del', '删除追溯码', 2, 0, 1, @trace_code_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @trace_code_pid;

-- BI报表和数据大屏菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/bi', '数据报表', 1, 1, 1, @mes_pid, 'fa fa-chart-bar', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @bi_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/bi' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/bi/dashboard', 'MES数据监管大屏', 1, 1, 1, @bi_pid, 'fa fa-desktop', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bi/productionEfficiency', '生产效率', 2, 0, 1, @bi_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bi/qualityAnalysis', '质量分析', 2, 0, 1, @bi_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bi/costAnalysis', '成本分析', 2, 0, 1, @bi_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @bi_pid,
    `type` = VALUES(`type`),
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/mrp', '缺料计算(MRP)', 1, 1, 1, @mes_pid, 'fa fa-calculator', 21, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/allocation_qrcode', '分工二维码', 1, 1, 1, @mes_pid, 'fa fa-qrcode', 13, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @wage_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/wage' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/wage/statistics', '工资统计', 2, 1, 1, @wage_pid, 'fa fa-chart-bar', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/wage/index', '工资明细', 2, 1, 1, @wage_pid, 'fa fa-list', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @wage_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/shipment', '发货管理', 1, 1, 1, @mes_pid, 'fa fa-truck', 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @shipment_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/shipment' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/shipment/index', '发货单列表', 1, 1, 1, @shipment_pid, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/shipment/add', '添加发货单', 2, 0, 1, @shipment_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/shipment/track', '物流追踪', 2, 0, 1, @shipment_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @shipment_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/quality', '质检管理', 1, 1, 1, @mes_pid, 'fa fa-check-circle', 17, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @quality_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/quality' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/quality/standard', '质检标准', 1, 1, 1, @quality_pid, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/quality/check', '质检记录', 1, 1, 1, @quality_pid, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/quality/statistics', '质检统计', 1, 1, 1, @quality_pid, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @quality_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/purchase', '采购管理', 1, 1, 1, @mes_pid, 'fa fa-shopping-cart', 15, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @purchase_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/purchase' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/purchase/request', '采购单列表', 1, 1, 1, @purchase_pid, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/purchase/inbound', '采购入库', 1, 1, 1, @purchase_pid, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @purchase_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/warehouse', '仓库管理', 1, 1, 1, @mes_pid, 'fa fa-home', 14, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @warehouse_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/warehouse' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/warehouse/index', '仓库列表', 1, 1, 1, @warehouse_pid, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/warehouse/add', '添加仓库', 1, 1, 1, @warehouse_pid, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @warehouse_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/stock', '库存管理', 1, 1, 1, @mes_pid, 'fa fa-warehouse', 12, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @stock_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/stock' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/stock/index', '库存查询', 1, 1, 1, @stock_pid, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/stock/outbound', '生产领料', 1, 1, 1, @stock_pid, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/stock/log', '库存流水', 1, 1, 1, @stock_pid, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/stock/product_log', '产品流水', 1, 1, 1, @stock_pid, '', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/stock/check', '库存盘点', 1, 1, 1, @stock_pid, '', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/stock/alert', '库存预警', 1, 1, 1, @stock_pid, 'fa fa-exclamation-triangle', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @stock_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`);

SET @mes_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/group_production', '生产执行', 1, 1, 1, @mes_pid, 'fa fa-industry', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/group_base', '基础资料', 1, 1, 1, @mes_pid, 'fa fa-database', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/group_warehouse', '仓储管理', 1, 1, 1, @mes_pid, 'fa fa-warehouse', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/group_purchase', '采购供应', 1, 1, 1, @mes_pid, 'fa fa-shopping-cart', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/group_shipping', '发货管理', 1, 1, 1, @mes_pid, 'fa fa-truck', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/group_quality', '质量管理', 1, 1, 1, @mes_pid, 'fa fa-check-circle', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/group_report', '报表分析', 1, 1, 1, @mes_pid, 'fa fa-chart-bar', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/group_customer', '客户管理', 1, 1, 1, @mes_pid, 'fa fa-users', 8, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `type` = 1,
    `ismenu` = 1,
    `status` = 1,
    `pid` = VALUES(`pid`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `update_time` = UNIX_TIMESTAMP();

SET @g_production = (SELECT id FROM fa_auth_rule WHERE name = 'mes/group_production' LIMIT 1);
SET @g_base = (SELECT id FROM fa_auth_rule WHERE name = 'mes/group_base' LIMIT 1);
SET @g_warehouse = (SELECT id FROM fa_auth_rule WHERE name = 'mes/group_warehouse' LIMIT 1);
SET @g_purchase = (SELECT id FROM fa_auth_rule WHERE name = 'mes/group_purchase' LIMIT 1);
SET @g_shipping = (SELECT id FROM fa_auth_rule WHERE name = 'mes/group_shipping' LIMIT 1);
SET @g_quality = (SELECT id FROM fa_auth_rule WHERE name = 'mes/group_quality' LIMIT 1);
SET @g_report = (SELECT id FROM fa_auth_rule WHERE name = 'mes/group_report' LIMIT 1);
SET @g_customer = (SELECT id FROM fa_auth_rule WHERE name = 'mes/group_customer' LIMIT 1);

UPDATE `fa_auth_rule` SET `pid` = @g_production, `sort` = 1, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/order';
UPDATE `fa_auth_rule` SET `pid` = @g_production, `sort` = 2, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/production_plan';
UPDATE `fa_auth_rule` SET `pid` = @g_production, `sort` = 3, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/schedule';
UPDATE `fa_auth_rule` SET `pid` = @g_production, `sort` = 4, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/allocation';
UPDATE `fa_auth_rule` SET `pid` = @g_production, `sort` = 5, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/allocation_qrcode';
UPDATE `fa_auth_rule` SET `pid` = @g_production, `sort` = 6, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/report';
UPDATE `fa_auth_rule` SET `pid` = @g_production, `sort` = 7, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/wage';
UPDATE `fa_auth_rule` SET `pid` = @g_production, `sort` = 8, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/trace_code';

UPDATE `fa_auth_rule` SET `pid` = @g_base, `sort` = 1, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/product';
UPDATE `fa_auth_rule` SET `pid` = @g_base, `sort` = 2, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/product_model';
UPDATE `fa_auth_rule` SET `pid` = @g_base, `sort` = 3, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/bom';
UPDATE `fa_auth_rule` SET `pid` = @g_base, `sort` = 4, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/process';
UPDATE `fa_auth_rule` SET `pid` = @g_base, `sort` = 5, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/process_price';
UPDATE `fa_auth_rule` SET `pid` = @g_base, `sort` = 6, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/process_route';
UPDATE `fa_auth_rule` SET `pid` = @g_base, `sort` = 7, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/user_process_capacity';

UPDATE `fa_auth_rule` SET `pid` = @g_warehouse, `sort` = 1, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/warehouse';
UPDATE `fa_auth_rule` SET `pid` = @g_warehouse, `sort` = 2, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/stock';
UPDATE `fa_auth_rule` SET `pid` = @g_warehouse, `sort` = 3, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/material_category';
UPDATE `fa_auth_rule` SET `pid` = @g_warehouse, `sort` = 4, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/material';

UPDATE `fa_auth_rule` SET `pid` = @g_purchase, `sort` = 1, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/purchase';
UPDATE `fa_auth_rule` SET `pid` = @g_purchase, `sort` = 2, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/supplier';

UPDATE `fa_auth_rule` SET `pid` = @g_shipping, `sort` = 1, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/shipment';

UPDATE `fa_auth_rule` SET `pid` = @g_quality, `sort` = 1, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/quality';

UPDATE `fa_auth_rule` SET `pid` = @g_report, `sort` = 1, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/bi';
UPDATE `fa_auth_rule` SET `pid` = @g_report, `sort` = 2, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/mrp';

UPDATE `fa_auth_rule` SET `pid` = @g_customer, `sort` = 1, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/customer';
UPDATE `fa_auth_rule` SET `pid` = @g_customer, `sort` = 2, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'mes/customer_product';
