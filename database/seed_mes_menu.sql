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

-- 9. 插入物料管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/material', '物料管理', 1, 1, 1, @mes_pid, 'fa fa-cubes', 11, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @material_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/material' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/material/index', '物料列表', 2, 0, 1, @material_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/add', '添加物料', 2, 0, 1, @material_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/edit', '编辑物料', 2, 0, 1, @material_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material/del', '删除物料', 2, 0, 1, @material_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @material_pid;

-- 10. 插入供应商管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/supplier', '供应商管理', 1, 1, 1, @mes_pid, 'fa fa-truck', 12, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
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

-- 工资管理菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) 
VALUES ('mes/wage', '工资管理', 1, 1, 1, @mes_pid, 'fa fa-yen-sign', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @mes_pid;

SET @wage_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/wage' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/wage/index', '工资明细', 2, 0, 1, @wage_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/wage/statistics', '工资统计', 2, 0, 1, @wage_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/wage/export', '导出工资', 2, 0, 1, @wage_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
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
('mes/bi/dashboard', '数据大屏', 2, 0, 1, @bi_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bi/productionEfficiency', '生产效率', 2, 0, 1, @bi_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bi/qualityAnalysis', '质量分析', 2, 0, 1, @bi_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bi/costAnalysis', '成本分析', 2, 0, 1, @bi_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE 
    `title` = VALUES(`title`),
    `pid` = @bi_pid;
