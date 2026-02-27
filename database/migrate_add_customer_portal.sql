-- 客户门户相关表结构与字段扩展
-- 说明：
-- 1. 在现有客户表 fa_mes_customer 上增加登录账号、密码和默认语言等字段
-- 2. 新增客户可下单产品表 fa_mes_customer_product
-- 3. 新增客户订单表 fa_mes_customer_order 及其明细表 fa_mes_customer_order_item

-- 1. 扩展客户表：增加登录与多语言相关字段
ALTER TABLE `fa_mes_customer`
    ADD COLUMN `login_account` varchar(100) NOT NULL DEFAULT '' COMMENT '登录账号' AFTER `customer_name`,
    ADD COLUMN `login_password` varchar(255) NOT NULL DEFAULT '' COMMENT '登录密码哈希' AFTER `login_account`,
    ADD COLUMN `default_lang` varchar(20) NOT NULL DEFAULT 'zh-cn' COMMENT '默认语言：zh-cn/en-us 等' AFTER `status`;

-- 唯一约束：同一租户下客户登录账号唯一
ALTER TABLE `fa_mes_customer`
    ADD UNIQUE KEY `uniq_tenant_login_account` (`tenant_id`, `login_account`);

-- 2. 客户可下单产品表
DROP TABLE IF EXISTS `fa_mes_customer_product`;
CREATE TABLE `fa_mes_customer_product` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '产品ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '销售单价',
  `currency` varchar(10) NOT NULL DEFAULT 'CNY' COMMENT '币种：CNY/USD 等',
  `min_qty` int unsigned NOT NULL DEFAULT 1 COMMENT '最小起订量',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_product_model` (`product_id`, `model_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户可下单产品表';

-- 3. 客户订单表
DROP TABLE IF EXISTS `fa_mes_customer_order`;
CREATE TABLE `fa_mes_customer_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '客户订单ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `customer_order_no` varchar(50) NOT NULL DEFAULT '' COMMENT '客户订单号',
  `internal_order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '内部订单ID（关联 fa_mes_order.id）',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待确认 1已确认 2生产中 3已发货 4已完成 5已取消',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '订单总金额',
  `currency` varchar(10) NOT NULL DEFAULT 'CNY' COMMENT '币种',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_customer_order_no` (`tenant_id`, `customer_order_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_internal_order` (`internal_order_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户订单表';

-- 4. 客户订单明细表
DROP TABLE IF EXISTS `fa_mes_customer_order_item`;
CREATE TABLE `fa_mes_customer_order_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `customer_order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户订单ID',
  `customer_product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户可下单产品ID',
  `product_id` int unsigned NOT NULL DEFAULT 0 COMMENT '产品ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `quantity` int unsigned NOT NULL DEFAULT 0 COMMENT '数量',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '销售单价',
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '小计金额',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer_order` (`customer_order_id`),
  KEY `idx_customer_product` (`customer_product_id`),
  KEY `idx_product_model` (`product_id`, `model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户订单明细表';

