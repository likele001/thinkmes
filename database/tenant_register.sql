-- 租户注册和审核补充表
-- 执行时间: 2026-02-11

-- 1. 租户注册申请表
DROP TABLE IF EXISTS \`fa_tenant_register\`;
CREATE TABLE \`fa_tenant_register\` (
  \`id\` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请ID',
  \`register_no\` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '注册单号',
  \`company_name\` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业名称',
  \`contact_name\` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人',
  \`contact_phone\` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  \`contact_email\` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系邮箱',
  \`domain\` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '期望绑定域名',
  \`package_id\` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '选择套餐ID',
  \`remark\` text COLLATE utf8mb4_unicode_ci COMMENT '申请说明',
  \`status\` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审核状态：0待审核 1已通过 2已拒绝',
  \`audit_user_id\` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核人ID',
  \`audit_time\` int(11) DEFAULT NULL COMMENT '审核时间',
  \`audit_remark\` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审核备注',
  \`create_time\` int(11) NOT NULL DEFAULT '0' COMMENT '申请时间',
  PRIMARY KEY (\`id\`),
  UNIQUE KEY \`idx_register_no\` (\`register_no\`),
  KEY \`idx_status\` (\`status\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户注册申请表';

-- 2. 重建套餐功能表
DROP TABLE IF EXISTS \`fa_tenant_package_feature\`;
CREATE TABLE \`fa_tenant_package_feature\` (
  \`id\` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  \`package_id\` int(10) unsigned NOT NULL COMMENT '套餐ID',
  \`feature_code\` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '功能代码',
  \`feature_name\` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '功能名称',
  \`is_enabled\` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否启用：1启用 0禁用',
  \`max_count\` int(11) DEFAULT NULL COMMENT '最大数量限制 NULL=不限制',
  \`sort\` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  \`create_time\` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (\`id\`),
  UNIQUE KEY \`idx_package_feature\` (\`package_id\`,\`feature_code\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套餐功能配置表';

-- 3. 插入套餐功能数据（分为4个套餐：基础版、标准版、专业版、企业版）
INSERT INTO \`fa_tenant_package_feature\` (\`package_id\`, \`feature_code\`, \`feature_name\`, \`sort\`, \`create_time\`) VALUES
(1, 'mes.order', '订单管理', 1, UNIX_TIMESTAMP()),
(1, 'mes.product', '产品管理', 2, UNIX_TIMESTAMP()),
(1, 'mes.bom', 'BOM管理', 3, UNIX_TIMESTAMP()),
(1, 'mes.material', '物料管理', 4, UNIX_TIMESTAMP()),
(1, 'mes.process', '工序列表', 5, UNIX_TIMESTAMP()),
(1, 'mes.customer', '客户管理', 6, UNIX_TIMESTAMP()),
(1, 'mes.supplier', '供应商管理', 7, UNIX_TIMESTAMP()),
(1, 'mes.allocation', '分工分配', 8, UNIX_TIMESTAMP()),
(1, 'mes.report', '报工管理', 9, UNIX_TIMESTAMP()),
(1, 'mes.wage', '工资管理', 10, UNIX_TIMESTAMP()),
(1, 'mes.trace_code', '追溯码管理', 11, UNIX_TIMESTAMP()),
(1, 'mes.production_plan', '生产计划', 12, UNIX_TIMESTAMP()),
(1, 'mes.process_price', '工序工价', 19, UNIX_TIMESTAMP()),
(2, 'mes.order', '订单管理', 1, UNIX_TIMESTAMP()),
(2, 'mes.product', '产品管理', 2, UNIX_TIMESTAMP()),
(2, 'mes.bom', 'BOM管理', 3, UNIX_TIMESTAMP()),
(2, 'mes.material', '物料管理', 4, UNIX_TIMESTAMP()),
(2, 'mes.process', '工序列表', 5, UNIX_TIMESTAMP()),
(2, 'mes.customer', '客户管理', 6, UNIX_TIMESTAMP()),
(2, 'mes.supplier', '供应商管理', 7, UNIX_TIMESTAMP()),
(2, 'mes.allocation', '分工分配', 8, UNIX_TIMESTAMP()),
(2, 'mes.report', '报工管理', 9, UNIX_TIMESTAMP()),
(2, 'mes.wage', '工资管理', 10, UNIX_TIMESTAMP()),
(2, 'mes.trace_code', '追溯码管理', 11, UNIX_TIMESTAMP()),
(2, 'mes.production_plan', '生产计划', 12, UNIX_TIMESTAMP()),
(2, 'mes.process_price', '工序工价', 19, UNIX_TIMESTAMP()),
(3, 'mes.order', '订单管理', 1, UNIX_TIMESTAMP()),
(3, 'mes.product', '产品管理', 2, UNIX_TIMESTAMP()),
(3, 'mes.bom', 'BOM管理', 3, UNIX_TIMESTAMP()),
(3, 'mes.material', '物料管理', 4, UNIX_TIMESTAMP()),
(3, 'mes.process', '工序列表', 5, UNIX_TIMESTAMP()),
(3, 'mes.customer', '客户管理', 6, UNIX_TIMESTAMP()),
(3, 'mes.supplier', '供应商管理', 7, UNIX_TIMESTAMP()),
(3, 'mes.allocation', '分工分配', 8, UNIX_TIMESTAMP()),
(3, 'mes.report', '报工管理', 9, UNIX_TIMESTAMP()),
(3, 'mes.wage', '工资管理', 10, UNIX_TIMESTAMP()),
(3, 'mes.trace_code', '追溯码管理', 11, UNIX_TIMESTAMP()),
(3, 'mes.production_plan', '生产计划', 12, UNIX_TIMESTAMP()),
(3, 'mes.purchase', '采购管理', 13, UNIX_TIMESTAMP()),
(3, 'mes.stock', '库存管理', 14, UNIX_TIMESTAMP()),
(3, 'mes.quality', '质检管理', 15, UNIX_TIMESTAMP()),
(3, 'mes.shipment', '发货管理', 16, UNIX_TIMESTAMP()),
(3, 'mes.warehouse', '仓库管理', 17, UNIX_TIMESTAMP()),
(3, 'mes.process_price', '工序工价', 19, UNIX_TIMESTAMP()),
(3, 'mes.product_model', '产品型号', 20, UNIX_TIMESTAMP()),
(4, 'mes.order', '订单管理', 1, UNIX_TIMESTAMP()),
(4, 'mes.product', '产品管理', 2, UNIX_TIMESTAMP()),
(4, 'mes.bom', 'BOM管理', 3, UNIX_TIMESTAMP()),
(4, 'mes.material', '物料管理', 4, UNIX_TIMESTAMP()),
(4, 'mes.process', '工序列表', 5, UNIX_TIMESTAMP()),
(4, 'mes.customer', '客户管理', 6, UNIX_TIMESTAMP()),
(4, 'mes.supplier', '供应商管理', 7, UNIX_TIMESTAMP()),
(4, 'mes.allocation', '分工分配', 8, UNIX_TIMESTAMP()),
(4, 'mes.report', '报工管理', 9, UNIX_TIMESTAMP()),
(4, 'mes.wage', '工资管理', 10, UNIX_TIMESTAMP()),
(4, 'mes.trace_code', '追溯码管理', 11, UNIX_TIMESTAMP()),
(4, 'mes.production_plan', '生产计划', 12, UNIX_TIMESTAMP()),
(4, 'mes.purchase', '采购管理', 13, UNIX_TIMESTAMP()),
(4, 'mes.stock', '库存管理', 14, UNIX_TIMESTAMP()),
(4, 'mes.quality', '质检管理', 15, UNIX_TIMESTAMP()),
(4, 'mes.shipment', '发货管理', 16, UNIX_TIMESTAMP()),
(4, 'mes.warehouse', '仓库管理', 17, UNIX_TIMESTAMP()),
(4, 'mes.process_price', '工序工价', 19, UNIX_TIMESTAMP()),
(4, 'mes.product_model', '产品型号', 20, UNIX_TIMESTAMP()),
(4, 'mes.bi', '数据报表', 21, UNIX_TIMESTAMP());

-- 4. 扩展租户表
ALTER TABLE \`fa_tenant\`
ADD COLUMN \`company_name\` varchar(100) NOT NULL DEFAULT '' COMMENT '企业名称' AFTER \`name\`,
ADD COLUMN \`contact_name\` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人' AFTER \`company_name\`,
ADD COLUMN \`contact_phone\` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话' AFTER \`contact_name\`,
ADD COLUMN \`contact_email\` varchar(100) NOT NULL DEFAULT '' COMMENT '联系邮箱' AFTER \`contact_phone\`,
ADD COLUMN \`admin_count\` int(11) NOT NULL DEFAULT '1' COMMENT '管理员数量' AFTER \`package_id\`,
ADD COLUMN \`user_count\` int(11) NOT NULL DEFAULT '0' COMMENT '用户数量' AFTER \`admin_count\`;

-- 5. 初始化套餐数据
TRUNCATE TABLE \`fa_tenant_package\`;
INSERT INTO \`fa_tenant_package\` (\`id\`, \`name\`, \`max_admin\`, \`max_user\`, \`expire_days\`, \`sort\`, \`create_time\`, \`update_time\`) VALUES
(1, '基础版', 3, 10, 365, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, '标准版', 5, 50, 365, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, '专业版', 10, 200, 365, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, '企业版', 20, 500, 365, 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 6. 更新现有租户套餐
UPDATE \`fa_tenant\` SET \`package_id\` = 1 WHERE \`package_id\` = 0 OR \`package_id\` IS NULL;
