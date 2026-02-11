-- MES 完整闭环补充表结构
-- 执行时间: 2026-02-11

-- ----------------------------
-- 1. 采购入库单表
-- ----------------------------
DROP TABLE IF EXISTS `fa_mes_purchase_in`;
CREATE TABLE `fa_mes_purchase_in` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '入库单ID',
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '租户ID',
  `in_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '入库单号',
  `purchase_request_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '采购申请ID',
  `supplier_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '供应商ID',
  `material_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '物料ID',
  `in_quantity` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '入库数量',
  `actual_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际单价',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
  `in_time` int(11) DEFAULT NULL COMMENT '入库时间',
  `warehouse_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '仓库ID',
  `operator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态：0待入库 1已入库 2已退货',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_in_no` (`in_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_request` (`purchase_request_id`),
  KEY `idx_material` (`material_id`),
  KEY `idx_supplier` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='采购入库单表';

-- ----------------------------
-- 2. 生产领料单表
-- ----------------------------
DROP TABLE IF EXISTS `fa_mes_stock_out`;
CREATE TABLE `fa_mes_stock_out` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '出库单ID',
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '租户ID',
  `out_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '出库单号',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `material_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '物料ID',
  `out_quantity` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '出库数量',
  `out_time` int(11) DEFAULT NULL COMMENT '出库时间',
  `warehouse_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '仓库ID',
  `operator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `receiver_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '领料人ID',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态：0待出库 1已出库 2已退回',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_out_no` (`out_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_material` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='生产领料单表';

-- ----------------------------
-- 3. 库存流水表
-- ----------------------------
DROP TABLE IF EXISTS `fa_mes_stock_log`;
CREATE TABLE `fa_mes_stock_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '租户ID',
  `material_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '物料ID',
  `warehouse_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '仓库ID',
  `before_quantity` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动前数量',
  `change_quantity` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动数量（正入负出）',
  `after_quantity` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动后数量',
  `business_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '业务类型：purchase_in采购入库,production_out生产出库,check_in盘点入库,check_out盘点出库',
  `business_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '业务单据ID',
  `operator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_material` (`material_id`),
  KEY `idx_business` (`business_type`, `business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='库存流水表';

-- ----------------------------
-- 4. 质检标准表
-- ----------------------------
DROP TABLE IF EXISTS `fa_mes_quality_standard`;
CREATE TABLE `fa_mes_quality_standard` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '租户ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标准名称',
  `process_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工序ID（0表示通用）',
  `model_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '型号ID（0表示通用）',
  `check_items` text COLLATE utf8mb4_unicode_ci COMMENT '检查项（JSON）',
  `qualified_rate` decimal(5,2) NOT NULL DEFAULT '100.00' COMMENT '合格率要求(%)',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_process` (`process_id`),
  KEY `idx_model` (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质检标准表';

-- ----------------------------
-- 5. 质检记录表
-- ----------------------------
DROP TABLE IF EXISTS `fa_mes_quality_check`;
CREATE TABLE `fa_mes_quality_check` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '租户ID',
  `check_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '质检单号',
  `report_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '报工ID',
  `allocation_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分配ID',
  `standard_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '质检标准ID',
  `check_quantity` int(11) NOT NULL DEFAULT '0' COMMENT '检验数量',
  `qualified_quantity` int(11) NOT NULL DEFAULT '0' COMMENT '合格数量',
  `unqualified_quantity` int(11) NOT NULL DEFAULT '0' COMMENT '不合格数量',
  `qualified_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '合格率(%)',
  `check_items` text COLLATE utf8mb4_unicode_ci COMMENT '检查结果（JSON）',
  `check_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '质检人ID',
  `check_time` int(11) DEFAULT NULL COMMENT '质检时间',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态：0待质检 1已质检 2返工',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_check_no` (`check_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_report` (`report_id`),
  KEY `idx_allocation` (`allocation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='质检记录表';

-- ----------------------------
-- 6. 发货单表
-- ----------------------------
DROP TABLE IF EXISTS `fa_mes_shipment`;
CREATE TABLE `fa_mes_shipment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '发货单ID',
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '租户ID',
  `shipment_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发货单号',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `customer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户ID',
  `shipment_quantity` int(11) NOT NULL DEFAULT '0' COMMENT '发货数量',
  `shipment_time` int(11) DEFAULT NULL COMMENT '发货时间',
  `logistics_company` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物流公司',
  `logistics_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物流单号',
  `receiver_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收货人',
  `receiver_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收货电话',
  `receiver_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '收货地址',
  `sign_time` int(11) DEFAULT NULL COMMENT '签收时间',
  `sign_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '签收人',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态：0待发货 1已发货 2已签收 3已退回',
  `operator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_shipment_no` (`shipment_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='发货单表';

-- ----------------------------
-- 7. 发货明细表
-- ----------------------------
DROP TABLE IF EXISTS `fa_mes_shipment_item`;
CREATE TABLE `fa_mes_shipment_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '租户ID',
  `shipment_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发货单ID',
  `model_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '型号ID',
  `quantity` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `batch_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '批次号',
  `trace_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '追溯码',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_shipment` (`shipment_id`),
  KEY `idx_trace_code` (`trace_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='发货明细表';

-- ----------------------------
-- 8. 仓库表（扩展）
-- ----------------------------
DROP TABLE IF EXISTS `fa_mes_warehouse`;
CREATE TABLE `fa_mes_warehouse` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '仓库ID',
  `tenant_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '租户ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '仓库名称',
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '仓库编码',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '仓库地址',
  `manager_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '负责人ID',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认仓库',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='仓库表';

-- ----------------------------
-- 执行更新现有表（添加缺失字段）
-- ----------------------------

-- 为 fa_mes_order 添加发货相关字段
ALTER TABLE `fa_mes_order`
ADD COLUMN `shipment_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发货单ID' AFTER `status`,
ADD COLUMN `shipment_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发货状态：0未发货 1部分发货 2已发货' AFTER `shipment_id`,
ADD COLUMN `production_progress` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '生产进度(%)' AFTER `total_quantity`;

-- 为 fa_mes_allocation 添加二维码字段
ALTER TABLE `fa_mes_allocation`
ADD COLUMN `qr_content` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码内容' AFTER `status`,
ADD COLUMN `qr_image` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码图片' AFTER `qr_content`;

-- 为 fa_mes_material 添加仓库字段
ALTER TABLE `fa_mes_material`
ADD COLUMN `warehouse_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '默认仓库ID' AFTER `default_supplier_id`;
