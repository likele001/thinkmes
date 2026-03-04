-- 支付独立应用：网关配置与支付订单
-- 支持：官方支付宝/微信、虎皮椒(讯虎)、易支付(8-pay)

-- 支付网关配置表（平台或租户可配置多通道）
DROP TABLE IF EXISTS `fa_payment_gateway`;
CREATE TABLE `fa_payment_gateway` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '0=平台',
  `code` varchar(32) NOT NULL DEFAULT '' COMMENT 'official_alipay/official_wechat/xunhupay_alipay/xunhupay_wechat/epay',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '显示名称',
  `config` text COMMENT 'JSON: appid,secret,key,pid等',
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sort` int NOT NULL DEFAULT 0,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付网关配置';

-- 支付订单表（与业务订单号 order_no 关联，如租户订单）
DROP TABLE IF EXISTS `fa_payment_order`;
CREATE TABLE `fa_payment_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(64) NOT NULL COMMENT '业务订单号',
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '金额(元)',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '订单标题',
  `gateway_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'payment_gateway.id',
  `gateway_code` varchar(32) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0待支付 1已支付 2已关闭 3已退款',
  `third_order_id` varchar(128) DEFAULT '' COMMENT '第三方订单号',
  `pay_time` int DEFAULT NULL,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  `extra` text COMMENT 'JSON: return_url,notify_url等',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_status` (`status`),
  KEY `idx_gateway` (`gateway_id`),
  KEY `idx_create` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付订单';
