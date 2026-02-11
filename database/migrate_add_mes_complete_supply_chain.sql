-- MES 完整供应链扩展表结构
-- 补充采购、库存、入库、出库、质检、成品、发货等完整功能表
-- 所有表都包含 tenant_id 字段以实现租户隔离

-- =============================================
-- 1. 采购管理模块
-- =============================================

-- 采购订单表
DROP TABLE IF EXISTS `fa_mes_purchase_order`;
CREATE TABLE `fa_mes_purchase_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '采购订单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '采购订单号',
  `request_id` int unsigned NOT NULL DEFAULT 0 COMMENT '采购申请ID',
  `supplier_id` int unsigned NOT NULL DEFAULT 0 COMMENT '供应商ID',
  `purchase_date` date DEFAULT NULL COMMENT '采购日期',
  `expected_date` date DEFAULT NULL COMMENT '预计到货日期',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '采购总金额',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '已付金额',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待审核 1已审核 2已采购 3部分入库 4已入库 5已取消',
  `audit_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审核人ID',
  `audit_time` int DEFAULT NULL COMMENT '审核时间',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_no` (`order_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_supplier` (`supplier_id`),
  KEY `idx_request` (`request_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购订单表';

-- 采购订单明细表
DROP TABLE IF EXISTS `fa_mes_purchase_order_item`;
CREATE TABLE `fa_mes_purchase_order_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `purchase_order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '采购订单ID',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '采购数量',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '小计',
  `received_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '已入库数量',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`purchase_order_id`),
  KEY `idx_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购订单明细表';

-- =============================================
-- 2. 库存管理模块
-- =============================================

-- 库存出入库单表
DROP TABLE IF EXISTS `fa_mes_stock_order`;
CREATE TABLE `fa_mes_stock_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '出入库单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '出入库单号',
  `order_type` tinyint NOT NULL DEFAULT 0 COMMENT '单据类型：1采购入库 2生产入库 3销售出库 4领料出库 5调拨 6盘点',
  `source_type` varchar(50) NOT NULL DEFAULT '' COMMENT '来源类型：purchase采购/production生产/sales销售等',
  `source_id` int unsigned NOT NULL DEFAULT 0 COMMENT '来源单据ID',
  `warehouse_id` int unsigned NOT NULL DEFAULT 0 COMMENT '仓库ID',
  `order_date` date DEFAULT NULL COMMENT '出入库日期',
  `total_quantity` int NOT NULL DEFAULT 0 COMMENT '总数量',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待审核 1已审核 2已执行 3已取消',
  `audit_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审核人ID',
  `audit_time` int DEFAULT NULL COMMENT '审核时间',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_no` (`order_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_type` (`order_type`),
  KEY `idx_source` (`source_type`,`source_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='库存出入库单表';

-- 出入库单明细表
DROP TABLE IF EXISTS `fa_mes_stock_order_item`;
CREATE TABLE `fa_mes_stock_order_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `stock_order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '出入库单ID',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `quantity` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '数量（正数入库，负数出库）',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '总金额',
  `batch_no` varchar(50) NOT NULL DEFAULT '' COMMENT '批次号',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`stock_order_id`),
  KEY `idx_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='出入库单明细表';

-- 物料库存流水表（用于追溯库存变化）
DROP TABLE IF EXISTS `fa_mes_stock_log`;
CREATE TABLE `fa_mes_stock_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `order_type` tinyint NOT NULL DEFAULT 0 COMMENT '单据类型',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '出入库单号',
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '变动数量（正数入库，负数出库）',
  `before_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '变动前库存',
  `after_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '变动后库存',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `warehouse_id` int unsigned NOT NULL DEFAULT 0 COMMENT '仓库ID',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_material` (`material_id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物料库存流水表';

-- 库存盘点单表
DROP TABLE IF EXISTS `fa_mes_stock_check`;
CREATE TABLE `fa_mes_stock_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '盘点单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `check_no` varchar(50) NOT NULL DEFAULT '' COMMENT '盘点单号',
  `warehouse_id` int unsigned NOT NULL DEFAULT 0 COMMENT '仓库ID',
  `check_date` date DEFAULT NULL COMMENT '盘点日期',
  `check_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '盘点人ID',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待审核 1已审核 2已完成',
  `audit_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审核人ID',
  `audit_time` int DEFAULT NULL COMMENT '审核时间',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_check_no` (`check_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_warehouse` (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='库存盘点单表';

-- 盘点单明细表
DROP TABLE IF EXISTS `fa_mes_stock_check_item`;
CREATE TABLE `fa_mes_stock_check_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `check_id` int unsigned NOT NULL DEFAULT 0 COMMENT '盘点单ID',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `book_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '账面数量',
  `actual_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '实际数量',
  `diff_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '差异数量',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `diff_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '差异金额',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_check` (`check_id`),
  KEY `idx_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='盘点单明细表';

-- 仓库表
DROP TABLE IF EXISTS `fa_mes_warehouse`;
CREATE TABLE `fa_mes_warehouse` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '仓库ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '仓库名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '仓库编码',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '仓库地址',
  `manager_id` int unsigned NOT NULL DEFAULT 0 COMMENT '负责人ID',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='仓库表';

-- =============================================
-- 3. 质检管理模块
-- =============================================

-- 质检单表
DROP TABLE IF EXISTS `fa_mes_quality_check`;
CREATE TABLE `fa_mes_quality_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '质检单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `check_no` varchar(50) NOT NULL DEFAULT '' COMMENT '质检单号',
  `check_type` tinyint NOT NULL DEFAULT 0 COMMENT '质检类型：1来料检验 2过程检验 3成品检验',
  `source_type` varchar(50) NOT NULL DEFAULT '' COMMENT '来源类型：purchase采购/production生产等',
  `source_id` int unsigned NOT NULL DEFAULT 0 COMMENT '来源单据ID',
  `check_date` date DEFAULT NULL COMMENT '检验日期',
  `check_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '检验人ID',
  `sample_quantity` int NOT NULL DEFAULT 0 COMMENT '抽样数量',
  `qualified_quantity` int NOT NULL DEFAULT 0 COMMENT '合格数量',
  `unqualified_quantity` int NOT NULL DEFAULT 0 COMMENT '不合格数量',
  `qualified_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '合格率(%)',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待审核 1已审核 2已取消',
  `audit_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审核人ID',
  `audit_time` int DEFAULT NULL COMMENT '审核时间',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_check_no` (`check_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_type` (`check_type`),
  KEY `idx_source` (`source_type`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质检单表';

-- 质检单明细表
DROP TABLE IF EXISTS `fa_mes_quality_check_item`;
CREATE TABLE `fa_mes_quality_check_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `check_id` int unsigned NOT NULL DEFAULT 0 COMMENT '质检单ID',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `batch_no` varchar(50) NOT NULL DEFAULT '' COMMENT '批次号',
  `check_quantity` int NOT NULL DEFAULT 0 COMMENT '检验数量',
  `qualified_quantity` int NOT NULL DEFAULT 0 COMMENT '合格数量',
  `unqualified_quantity` int NOT NULL DEFAULT 0 COMMENT '不合格数量',
  `defect_type` varchar(100) NOT NULL DEFAULT '' COMMENT '缺陷类型',
  `defect_desc` text COMMENT '缺陷描述',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_check` (`check_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质检单明细表';

-- 不良品处理表
DROP TABLE IF EXISTS `fa_mes_defect_handle`;
CREATE TABLE `fa_mes_defect_handle` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '处理单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `handle_no` varchar(50) NOT NULL DEFAULT '' COMMENT '处理单号',
  `quality_check_id` int unsigned NOT NULL DEFAULT 0 COMMENT '质检单ID',
  `handle_type` tinyint NOT NULL DEFAULT 0 COMMENT '处理方式：1返工 2报废 3退货 4让步接收',
  `handle_quantity` int NOT NULL DEFAULT 0 COMMENT '处理数量',
  `handle_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '处理人ID',
  `handle_date` date DEFAULT NULL COMMENT '处理日期',
  `loss_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '损失金额',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待审核 1已审核 2已完成',
  `audit_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审核人ID',
  `audit_time` int DEFAULT NULL COMMENT '审核时间',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_handle_no` (`handle_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_quality` (`quality_check_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='不良品处理表';

-- =============================================
-- 4. 成品与发货模块
-- =============================================

-- 成品表
DROP TABLE IF EXISTS `fa_mes_product_stock`;
CREATE TABLE `fa_mes_product_stock` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '产品ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `warehouse_id` int unsigned NOT NULL DEFAULT 0 COMMENT '仓库ID',
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '库存数量',
  `lock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '锁定数量',
  `available_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '可用数量',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_product_model` (`tenant_id`,`product_id`,`model_id`,`warehouse_id`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='成品库存表';

-- 成品入库单表
DROP TABLE IF EXISTS `fa_mes_product_inbound`;
CREATE TABLE `fa_mes_product_inbound` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '入库单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `inbound_no` varchar(50) NOT NULL DEFAULT '' COMMENT '入库单号',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '生产订单ID',
  `warehouse_id` int unsigned NOT NULL DEFAULT 0 COMMENT '仓库ID',
  `inbound_date` date DEFAULT NULL COMMENT '入库日期',
  `total_quantity` int NOT NULL DEFAULT 0 COMMENT '入库总数',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待审核 1已审核 2已入库',
  `audit_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审核人ID',
  `audit_time` int DEFAULT NULL COMMENT '审核时间',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_inbound_no` (`inbound_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='成品入库单表';

-- 成品入库明细表
DROP TABLE IF EXISTS `fa_mes_product_inbound_item`;
CREATE TABLE `fa_mes_product_inbound_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `inbound_id` int unsigned NOT NULL DEFAULT 0 COMMENT '入库单ID',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '产品ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '入库数量',
  `batch_no` varchar(50) NOT NULL DEFAULT '' COMMENT '批次号',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_inbound` (`inbound_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='成品入库明细表';

-- 发货单表
DROP TABLE IF EXISTS `fa_mes_delivery`;
CREATE TABLE `fa_mes_delivery` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '发货单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `delivery_no` varchar(50) NOT NULL DEFAULT '' COMMENT '发货单号',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `warehouse_id` int unsigned NOT NULL DEFAULT 0 COMMENT '仓库ID',
  `delivery_date` date DEFAULT NULL COMMENT '发货日期',
  `receiver_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收货人',
  `receiver_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '收货电话',
  `receiver_address` varchar(500) NOT NULL DEFAULT '' COMMENT '收货地址',
  `total_quantity` int NOT NULL DEFAULT 0 COMMENT '发货总数',
  `logistics_no` varchar(100) NOT NULL DEFAULT '' COMMENT '物流单号',
  `logistics_company` varchar(100) NOT NULL DEFAULT '' COMMENT '物流公司',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待发货 1已发货 2已签收 3已退回',
  `delivery_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '发货人ID',
  `delivery_time` int DEFAULT NULL COMMENT '发货时间',
  `sign_time` int DEFAULT NULL COMMENT '签收时间',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_delivery_no` (`delivery_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='发货单表';

-- 发货单明细表
DROP TABLE IF EXISTS `fa_mes_delivery_item`;
CREATE TABLE `fa_mes_delivery_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `delivery_id` int unsigned NOT NULL DEFAULT 0 COMMENT '发货单ID',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '产品ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '发货数量',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '小计',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_delivery` (`delivery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='发货单明细表';
