-- 打印模板表、短信配置使用 fa_config 的 group=sms
-- 表前缀 fa_

CREATE TABLE IF NOT EXISTS `fa_print_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(64) NOT NULL COMMENT '模板名称',
  `type` varchar(32) NOT NULL DEFAULT 'order' COMMENT '类型：order/shipment/contract等',
  `content` text COMMENT 'HTML内容，支持变量如 {order_no}',
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='打印模板';

-- 短信配置：使用 fa_config 的 group=sms，由后台保存时自动创建
