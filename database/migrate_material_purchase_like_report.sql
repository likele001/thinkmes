-- 按 report 结构还原：物料分类、物料/订单物料字段、采购入库单（主表+明细）
-- 所有表/字段均带 tenant_id 或与现有 MES 表一致

-- ========== 1. 物料分类表（report: scanwork_material_category） ==========
DROP TABLE IF EXISTS `fa_mes_material_category`;
CREATE TABLE `fa_mes_material_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '分类编码',
  `description` varchar(255) DEFAULT NULL COMMENT '分类描述',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  UNIQUE KEY `idx_tenant_code` (`tenant_id`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物料分类表';

-- ========== 2. 物料表补充字段（与 report 对齐） ==========
ALTER TABLE `fa_mes_material`
  ADD COLUMN `spec` varchar(100) DEFAULT NULL COMMENT '规格型号' AFTER `unit`,
  ADD COLUMN `description` text COMMENT '描述' AFTER `spec`,
  ADD COLUMN `max_stock` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '最高库存' AFTER `min_stock`,
  ADD COLUMN `safety_stock` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '安全库存' AFTER `max_stock`,
  ADD COLUMN `lead_time` int NOT NULL DEFAULT 7 COMMENT '采购提前期(天)' AFTER `safety_stock`;
-- 若已存在则忽略错误（可分批执行）
-- 将 status 统一为 tinyint 0/1（若当前为 varchar 可后续改）
-- ALTER TABLE `fa_mes_material` MODIFY COLUMN `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0停用 1在用';

-- ========== 3. 订单物料表补充字段（与 report 对齐） ==========
-- 采购状态与 report 一致：0=待采购 1=已下单 2=已到货；库存状态：0=待准备 1=已备料
-- 若某列已存在可注释对应行后执行
ALTER TABLE `fa_mes_order_material`
  ADD COLUMN `allocated_quantity` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT '已分配数量' AFTER `required_quantity`,
  ADD COLUMN `actual_price` decimal(10,4) DEFAULT NULL COMMENT '实际采购单价' AFTER `estimated_amount`,
  ADD COLUMN `actual_amount` decimal(12,2) DEFAULT NULL COMMENT '实际采购金额' AFTER `actual_price`,
  ADD COLUMN `remark` varchar(500) DEFAULT NULL COMMENT '备注' AFTER `stock_status`,
  ADD COLUMN `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间' AFTER `create_time`;

-- ========== 4. 采购入库单主表（report: scanwork_purchase_inbound） ==========
DROP TABLE IF EXISTS `fa_mes_purchase_inbound`;
CREATE TABLE `fa_mes_purchase_inbound` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `inbound_no` varchar(50) NOT NULL DEFAULT '' COMMENT '入库单号',
  `purchase_record_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联采购记录ID',
  `supplier_id` int unsigned NOT NULL DEFAULT 0 COMMENT '供应商ID',
  `inbound_date` int NOT NULL DEFAULT 0 COMMENT '入库日期',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '入库总金额',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1=待入库 2=已入库 3=已取消',
  `inbound_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '入库操作员ID',
  `warehouse_id` int unsigned NOT NULL DEFAULT 1 COMMENT '仓库ID',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_inbound_no` (`inbound_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_supplier` (`supplier_id`),
  KEY `idx_status` (`status`),
  KEY `idx_inbound_date` (`inbound_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购入库单表';

-- ========== 5. 采购入库单明细表（report: scanwork_purchase_inbound_item） ==========
DROP TABLE IF EXISTS `fa_mes_purchase_inbound_item`;
CREATE TABLE `fa_mes_purchase_inbound_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `inbound_id` int unsigned NOT NULL DEFAULT 0 COMMENT '入库单ID',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `request_quantity` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT '申请入库数量',
  `actual_quantity` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT '实际入库数量',
  `unit_price` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT '单价',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '总金额',
  `batch_no` varchar(50) DEFAULT NULL COMMENT '批次号',
  `expiry_date` int DEFAULT NULL COMMENT '有效期',
  `quality_status` tinyint NOT NULL DEFAULT 1 COMMENT '质检状态：1=待检 2=合格 3=不合格',
  `location` varchar(100) DEFAULT NULL COMMENT '存放位置',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_inbound` (`inbound_id`),
  KEY `idx_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购入库明细表';
