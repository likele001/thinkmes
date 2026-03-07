-- 财务简易模块（与 CRM 订单/回款、采购/供应商 闭环）
-- 执行前请确认 .env 中 DB_PREFIX=fa_

-- 应收账款（可关联 crm 客户、mes 订单或 crm 销售订单）
CREATE TABLE IF NOT EXISTS `fa_finance_receivable` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `title` varchar(128) NOT NULL COMMENT '摘要',
  `customer_id` int unsigned DEFAULT NULL COMMENT '关联CRM客户',
  `order_id` int unsigned DEFAULT NULL COMMENT '关联订单( mes_order / crm_sales_order )',
  `order_type` varchar(32) DEFAULT 'mes' COMMENT 'mes/crm',
  `amount` decimal(12,2) NOT NULL COMMENT '应收金额',
  `received` decimal(12,2) NOT NULL DEFAULT 0 COMMENT '已收',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0未结清 1已结清',
  `due_date` date DEFAULT NULL,
  `remark` varchar(500) DEFAULT NULL,
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `customer_id` (`customer_id`),
  KEY `order_id` (`order_id`,`order_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='应收账款';

-- 应付账款（可关联供应商）
CREATE TABLE IF NOT EXISTS `fa_finance_payable` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `title` varchar(128) NOT NULL,
  `supplier_id` int unsigned DEFAULT NULL COMMENT '关联供应商 fa_mes_supplier',
  `amount` decimal(12,2) NOT NULL,
  `paid` decimal(12,2) NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0未结清 1已结清',
  `due_date` date DEFAULT NULL,
  `remark` varchar(500) DEFAULT NULL,
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='应付账款';

-- 收款登记
CREATE TABLE IF NOT EXISTS `fa_finance_receive` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `receivable_id` int unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `pay_time` datetime DEFAULT NULL,
  `remark` varchar(500) DEFAULT NULL,
  `create_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `receivable_id` (`receivable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收款登记';

-- 付款登记
CREATE TABLE IF NOT EXISTS `fa_finance_pay` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `payable_id` int unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `pay_time` datetime DEFAULT NULL,
  `remark` varchar(500) DEFAULT NULL,
  `create_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `payable_id` (`payable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='付款登记';
