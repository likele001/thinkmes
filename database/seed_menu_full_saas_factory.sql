-- ============================================================
-- ThinkPHP 多租户 SaaS 单工厂版 - 完整后台菜单结构
-- 适用：中小型加工厂，可直接导入 fa_auth_rule 表
-- 执行前请确认表前缀（本文件使用 fa_），与现有 seed_*.sql 可合并执行
-- ============================================================

-- 一、系统底座
-- 1. 多租户（已有：admin/tenant, admin/tenant_package, admin/tenant_order）
-- 2. 系统设置：角色权限、菜单、操作日志、附件
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/index/index', '首页', 1, 1, 1, 0, 'fas fa-tachometer-alt', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/tenant/index', '租户管理', 1, 1, 1, 0, 'fas fa-building', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/tenant_package/index', '套餐管理', 1, 1, 1, 0, 'fas fa-box', 11, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/tenant_order/index', '租户订单', 1, 1, 1, 0, 'fas fa-shopping-cart', 12, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/role/index', '角色管理', 1, 1, 1, 0, 'fas fa-users-cog', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/auth_rule/index', '权限规则', 1, 1, 1, 0, 'fas fa-sitemap', 21, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/log/index', '操作日志', 1, 1, 1, 0, 'fas fa-history', 22, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/attachment/index', '附件管理', 1, 1, 1, 0, 'fas fa-file-alt', 23, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/config/index', '系统配置', 1, 1, 1, 0, 'fas fa-cog', 24, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`);

-- 二、统一接口中心 / 工厂AI（语音、OCR、大模型、智能提醒）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('ai', '工厂AI', 1, 1, 1, 0, 'fa fa-robot', 95, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`);

SET @ai_pid = (SELECT id FROM fa_auth_rule WHERE name = 'ai' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('ai/config', 'AI配置', 1, 1, 1, @ai_pid, 'fa fa-cog', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('ai/qa', '老板问答', 1, 1, 1, @ai_pid, 'fa fa-comments', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('ai/voice_report', '语音报工', 1, 1, 1, @ai_pid, 'fa fa-microphone', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('ai/anomaly', '报工异常检测', 1, 1, 1, @ai_pid, 'fa fa-exclamation-triangle', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('ai/daily_report', 'AI日报周报', 1, 1, 1, @ai_pid, 'fa fa-file-alt', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('ai/crm_follow', 'AI智能跟单', 1, 1, 1, @ai_pid, 'fa fa-hand-holding-heart', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('ai/package', 'AI套餐管理', 1, 1, 1, @ai_pid, 'fa fa-cubes', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @ai_pid;

-- 三、CRM 客户销售
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('crm', 'CRM客户关系', 1, 1, 1, 0, 'fa fa-handshake', 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`);

SET @crm_pid = (SELECT id FROM fa_auth_rule WHERE name = 'crm' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('crm/customer', '客户管理', 1, 1, 1, @crm_pid, 'fa fa-users', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/contact', '联系人', 1, 1, 1, @crm_pid, 'fa fa-address-card', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/opportunity', '商机管理', 1, 1, 1, @crm_pid, 'fa fa-lightbulb', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/contract', '合同管理', 1, 1, 1, @crm_pid, 'fa fa-file-contract', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/follow', '跟进记录', 1, 1, 1, @crm_pid, 'fa fa-tasks', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/payment', '回款管理', 1, 1, 1, @crm_pid, 'fa fa-money-bill', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/product', '产品管理', 1, 1, 1, @crm_pid, 'fa fa-cube', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/sales_order', '销售订单', 1, 1, 1, @crm_pid, 'fa fa-shopping-cart', 8, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('crm/report', 'CRM数据概览', 1, 1, 1, @crm_pid, 'fa fa-chart-bar', 9, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @crm_pid;

-- 四、生产核心 MES
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes', 'MES制造执行', 1, 1, 1, 0, 'fa fa-industry', 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`);

SET @mes_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/index', 'MES首页', 2, 0, 1, @mes_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/order', '工单/订单', 1, 1, 1, @mes_pid, 'fa fa-shopping-cart', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/report', '报工管理', 1, 1, 1, @mes_pid, 'fa fa-clipboard', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process', '工序管理', 1, 1, 1, @mes_pid, 'fa fa-cogs', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/process_route', '工艺路线', 1, 1, 1, @mes_pid, 'fa fa-route', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/production_plan', '生产计划', 1, 1, 1, @mes_pid, 'fa fa-calendar-alt', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/product', '产品管理', 1, 1, 1, @mes_pid, 'fa fa-cube', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bom', 'BOM管理', 1, 1, 1, @mes_pid, 'fa fa-sitemap', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/customer', '客户管理', 1, 1, 1, @mes_pid, 'fa fa-users', 8, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/material', '物料管理', 1, 1, 1, @mes_pid, 'fa fa-cubes', 9, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/stock', '库存/出入库', 1, 1, 1, @mes_pid, 'fa fa-warehouse', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/purchase', '采购管理', 1, 1, 1, @mes_pid, 'fa fa-truck', 11, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/supplier', '供应商', 1, 1, 1, @mes_pid, 'fa fa-truck-loading', 12, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/warehouse', '仓库管理', 1, 1, 1, @mes_pid, 'fa fa-warehouse', 13, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/quality', '质检管理', 1, 1, 1, @mes_pid, 'fa fa-check-square', 14, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/wage', '计件工资', 1, 1, 1, @mes_pid, 'fa fa-yen-sign', 15, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/shipment', '发货管理', 1, 1, 1, @mes_pid, 'fa fa-shipping-fast', 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/bi', '生产看板/BI', 1, 1, 1, @mes_pid, 'fa fa-chart-line', 17, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mes/after_sales', '售后', 1, 1, 1, @mes_pid, 'fa fa-headset', 18, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @mes_pid;

-- 五、设备管理（预留，后续可补子节点）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/equipment', '设备管理', 1, 1, 1, @mes_pid, 'fa fa-tools', 19, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @mes_pid;

-- 六、应用中心（套餐开通、安装应用）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('app_center', '应用中心', 1, 1, 1, 0, 'fa fa-th-large', 85, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`);

-- 说明：各模块的 index/add/edit/del 等操作权限请沿用现有 seed_mes_menu.sql、seed_crm_menu.sql、seed_ai_menu.sql 中的详细规则；
-- 本文件仅保证一级、二级菜单名称与“方案书/官网”一致，便于老板与实施对照。
