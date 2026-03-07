-- CRM 销售订单与产品表（Phase3）
-- 表前缀由应用中心执行时替换

-- CRM 产品表（独立运行时使用，与 MES 整合时可关联 mes_product）
DROP TABLE IF EXISTS `fa_crm_product`;
CREATE TABLE `fa_crm_product` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '产品ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '产品名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '产品编码',
  `unit` varchar(20) NOT NULL DEFAULT '个' COMMENT '单位',
  `price` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT '参考单价',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0停用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRM产品表';

-- 销售订单表
DROP TABLE IF EXISTS `fa_crm_sales_order`;
CREATE TABLE `fa_crm_sales_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '订单编号',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `contract_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联合同ID',
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT '订单总金额',
  `status` varchar(20) NOT NULL DEFAULT 'draft' COMMENT '状态：draft草稿 confirmed已确认 producing生产中 completed已完成 cancelled已取消',
  `mes_order_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'MES订单ID（转生产后）',
  `delivery_date` int DEFAULT NULL COMMENT '交货日期',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_no` (`order_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_mes_order` (`mes_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRM销售订单表';

-- 销售订单明细表
DROP TABLE IF EXISTS `fa_crm_sales_order_item`;
CREATE TABLE `fa_crm_sales_order_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `sales_order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '销售订单ID',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '产品ID',
  `product_name` varchar(100) NOT NULL DEFAULT '' COMMENT '产品名称',
  `product_code` varchar(50) NOT NULL DEFAULT '' COMMENT '产品编码',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '数量',
  `unit` varchar(20) NOT NULL DEFAULT '个' COMMENT '单位',
  `price` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_sales_order` (`sales_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRM销售订单明细表';
