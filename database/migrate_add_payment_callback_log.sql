-- 支付回调日志（单用户版，便于排查第三方通知）
DROP TABLE IF EXISTS `fa_payment_callback_log`;
CREATE TABLE `fa_payment_callback_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `gateway_id` int unsigned NOT NULL DEFAULT 0,
  `order_no` varchar(64) NOT NULL DEFAULT '',
  `raw` text COMMENT '原始 POST 数据 JSON',
  `result` varchar(32) NOT NULL DEFAULT '' COMMENT 'success/fail',
  `create_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_gateway` (`gateway_id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_create` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付回调日志';
