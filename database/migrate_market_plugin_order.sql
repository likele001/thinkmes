-- 插件市场：购买订单表（前端应用商店使用）

CREATE TABLE IF NOT EXISTS `fa_market_plugin_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_no` varchar(40) NOT NULL DEFAULT '' COMMENT '订单号',
  `plugin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '市场插件ID',
  `plugin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '插件标识',
  `plugin_title` varchar(100) NOT NULL DEFAULT '' COMMENT '插件标题',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '订单金额',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待支付 1已支付',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'C端用户ID',
  `pay_time` int unsigned NOT NULL DEFAULT 0 COMMENT '支付时间',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_plugin_id` (`plugin_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件市场购买订单';
