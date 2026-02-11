-- MES 制造执行系统核心表结构
-- 所有表都包含 tenant_id 字段以实现租户隔离
-- 字符集 utf8mb4，排序 utf8mb4_unicode_ci，引擎 InnoDB

-- 产品表
DROP TABLE IF EXISTS `fa_mes_product`;
CREATE TABLE `fa_mes_product` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '产品ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '产品名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '产品编码',
  `description` text COMMENT '产品描述',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品表';

-- 产品型号表
DROP TABLE IF EXISTS `fa_mes_product_model`;
CREATE TABLE `fa_mes_product_model` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '型号ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '产品ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '型号名称',
  `model_code` varchar(50) NOT NULL DEFAULT '' COMMENT '型号编码',
  `description` text COMMENT '型号描述',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品型号表';

-- 客户表
DROP TABLE IF EXISTS `fa_mes_customer`;
CREATE TABLE `fa_mes_customer` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '客户ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `customer_name` varchar(100) NOT NULL DEFAULT '' COMMENT '客户名称',
  `contact_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
  `contact_person` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户表';

-- 订单表
DROP TABLE IF EXISTS `fa_mes_order`;
CREATE TABLE `fa_mes_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单号',
  `order_name` varchar(100) NOT NULL DEFAULT '' COMMENT '订单名称',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `customer_name` varchar(100) NOT NULL DEFAULT '' COMMENT '客户名称',
  `customer_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '客户电话',
  `total_quantity` int NOT NULL DEFAULT 0 COMMENT '总数量',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待生产 1生产中 2已完成 3已取消',
  `delivery_time` int DEFAULT NULL COMMENT '交货时间',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_no` (`order_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单表';

-- 订单型号表
DROP TABLE IF EXISTS `fa_mes_order_model`;
CREATE TABLE `fa_mes_order_model` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '数量',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_model` (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单型号表';

-- 工序表
DROP TABLE IF EXISTS `fa_mes_process`;
CREATE TABLE `fa_mes_process` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '工序ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '工序名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '工序编码',
  `description` text COMMENT '工序描述',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工序表';

-- 工序工价表
DROP TABLE IF EXISTS `fa_mes_process_price`;
CREATE TABLE `fa_mes_process_price` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `process_id` int unsigned NOT NULL DEFAULT 0 COMMENT '工序ID',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '计件工价',
  `time_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '计时工价',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_model` (`model_id`),
  KEY `idx_process` (`process_id`),
  UNIQUE KEY `idx_model_process` (`model_id`,`process_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工序工价表';

-- 物料表
DROP TABLE IF EXISTS `fa_mes_material`;
CREATE TABLE `fa_mes_material` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '物料ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '物料名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '物料编码',
  `category_id` int unsigned NOT NULL DEFAULT 0 COMMENT '分类ID',
  `unit` varchar(20) NOT NULL DEFAULT '' COMMENT '单位',
  `current_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '当前价格',
  `default_supplier_id` int unsigned NOT NULL DEFAULT 0 COMMENT '默认供应商ID',
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '库存数量',
  `min_stock` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '最低库存',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态：active/inactive',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物料表';

-- 供应商表
DROP TABLE IF EXISTS `fa_mes_supplier`;
CREATE TABLE `fa_mes_supplier` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '供应商ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '供应商名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '供应商编码',
  `contact_person` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人',
  `contact_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态：active/inactive',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='供应商表';

-- BOM表
DROP TABLE IF EXISTS `fa_mes_bom`;
CREATE TABLE `fa_mes_bom` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'BOM ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `bom_no` varchar(50) NOT NULL DEFAULT '' COMMENT 'BOM编号',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '产品ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `version` varchar(20) NOT NULL DEFAULT '1.0' COMMENT '版本号',
  `base_quantity` int NOT NULL DEFAULT 1 COMMENT '基准数量',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0草稿 1审核中 2已发布 3已废弃',
  `creator_id` int unsigned NOT NULL DEFAULT 0 COMMENT '创建人ID',
  `creator_name` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人名称',
  `approver_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审核人ID',
  `approver_name` varchar(50) NOT NULL DEFAULT '' COMMENT '审核人名称',
  `approve_time` int DEFAULT NULL COMMENT '审核时间',
  `publish_time` int DEFAULT NULL COMMENT '发布时间',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_bom_no` (`bom_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_model` (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='BOM表';

-- BOM明细表
DROP TABLE IF EXISTS `fa_mes_bom_item`;
CREATE TABLE `fa_mes_bom_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `bom_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'BOM ID',
  `parent_id` int unsigned NOT NULL DEFAULT 0 COMMENT '父级ID（支持多层级）',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `quantity` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT '用量',
  `loss_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '损耗率(%)',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `supplier_id` int unsigned NOT NULL DEFAULT 0 COMMENT '推荐供应商ID',
  `level` tinyint NOT NULL DEFAULT 1 COMMENT '层级：1第一层 2第二层...',
  `sequence` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_bom` (`bom_id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='BOM明细表';

-- 分工分配表
DROP TABLE IF EXISTS `fa_mes_allocation`;
CREATE TABLE `fa_mes_allocation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '分配ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `process_id` int unsigned NOT NULL DEFAULT 0 COMMENT '工序ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '员工ID（关联fa_user）',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '分配数量',
  `completed_quantity` int NOT NULL DEFAULT 0 COMMENT '已完成数量',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待开始 1进行中 2已完成',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分工分配表';

-- 报工表
DROP TABLE IF EXISTS `fa_mes_report`;
CREATE TABLE `fa_mes_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '报工ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `allocation_id` int unsigned NOT NULL DEFAULT 0 COMMENT '分配ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '员工ID',
  `work_type` varchar(20) NOT NULL DEFAULT 'piece' COMMENT '工作类型：piece计件 time计时',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '数量（计件）',
  `work_hours` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '工时（计时）',
  `item_nos` text COMMENT '产品编号列表（JSON）',
  `wage` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '工资',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待审核 1已通过 2已拒绝',
  `audit_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审核人ID',
  `audit_time` int DEFAULT NULL COMMENT '审核时间',
  `audit_reason` varchar(255) NOT NULL DEFAULT '' COMMENT '审核原因',
  `audit_notes` text COMMENT '审核备注',
  `quality_status` tinyint NOT NULL DEFAULT 1 COMMENT '质量状态：1合格 2不合格',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_allocation` (`allocation_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='报工表';

-- 订单物料需求表
DROP TABLE IF EXISTS `fa_mes_order_material`;
CREATE TABLE `fa_mes_order_material` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `required_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '需求数量',
  `estimated_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '预估单价',
  `estimated_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '预估总价',
  `supplier_id` int unsigned NOT NULL DEFAULT 0 COMMENT '供应商ID',
  `loss_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '损耗率(%)',
  `purchase_status` tinyint NOT NULL DEFAULT 0 COMMENT '采购状态：0待采购 1已采购 2部分采购',
  `stock_status` tinyint NOT NULL DEFAULT 0 COMMENT '库存状态：0已备料 1缺料',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单物料需求表';

-- 采购申请表
DROP TABLE IF EXISTS `fa_mes_purchase_request`;
CREATE TABLE `fa_mes_purchase_request` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `request_no` varchar(50) NOT NULL DEFAULT '' COMMENT '申请单号',
  `material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '物料ID',
  `supplier_id` int unsigned NOT NULL DEFAULT 0 COMMENT '供应商ID',
  `required_quantity` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '需求数量',
  `estimated_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '预估单价',
  `estimated_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '预估总价',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联订单ID',
  `order_material_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联订单物料ID',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待审核 1已审核 2已采购 3已取消',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_request_no` (`request_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_material` (`material_id`),
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购申请表';
