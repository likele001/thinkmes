-- 采购入库改为 report 流程：入库单主表 + 明细表，先「生成入库单」再「确认入库」
-- 若表已存在可跳过；表前缀非 fa_ 请替换后执行

-- 入库单主表（按供应商/批次，状态 1=待入库 2=已入库）
CREATE TABLE IF NOT EXISTS `fa_mes_purchase_inbound` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `inbound_no` varchar(50) NOT NULL DEFAULT '' COMMENT '入库单号',
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
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购入库单主表';

-- 入库单明细表（一单多物料）
CREATE TABLE IF NOT EXISTS `fa_mes_purchase_inbound_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `inbound_id` int unsigned NOT NULL DEFAULT 0 COMMENT '入库单ID',
  `purchase_request_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联采购申请ID',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `request_quantity` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT '申请数量',
  `actual_quantity` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT '实际入库数量',
  `unit_price` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT '单价',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '总金额',
  `quality_status` tinyint NOT NULL DEFAULT 1 COMMENT '质检：1待检 2合格 3不合格',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_inbound` (`inbound_id`),
  KEY `idx_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购入库单明细表';
