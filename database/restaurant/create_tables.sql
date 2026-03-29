CREATE TABLE IF NOT EXISTS `fa_restaurant_store` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `contact_phone` varchar(30) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_area` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `sort` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_table` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `area_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `code` varchar(50) NOT NULL DEFAULT '',
  `seats` int NOT NULL DEFAULT 0,
  `state` tinyint NOT NULL DEFAULT 0,
  `qr_token` varchar(64) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`qr_token`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_area` (`area_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `sort` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `category_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(120) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort` int NOT NULL DEFAULT 0,
  `sold_out` tinyint NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_cart` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `table_id` int unsigned NOT NULL DEFAULT 0,
  `item_id` int unsigned NOT NULL DEFAULT 0,
  `product_type` varchar(20) NOT NULL DEFAULT 'item',
  `combo_id` int unsigned NOT NULL DEFAULT 0,
  `option_key` varchar(64) NOT NULL DEFAULT '',
  `option_snapshot` text,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_table_product_option` (`tenant_id`,`table_id`,`product_type`,`item_id`,`combo_id`,`option_key`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `table_id` int unsigned NOT NULL DEFAULT 0,
  `order_no` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 0,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remark` varchar(255) NOT NULL DEFAULT '',
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_table` (`table_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_order_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `order_id` int unsigned NOT NULL DEFAULT 0,
  `item_id` int unsigned NOT NULL DEFAULT 0,
  `product_type` varchar(20) NOT NULL DEFAULT 'item',
  `combo_id` int unsigned NOT NULL DEFAULT 0,
  `option_key` varchar(64) NOT NULL DEFAULT '',
  `name_snapshot` varchar(255) NOT NULL DEFAULT '',
  `option_snapshot` text,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_item_option_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `item_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(80) NOT NULL DEFAULT '',
  `required` tinyint NOT NULL DEFAULT 0,
  `min_select` int NOT NULL DEFAULT 0,
  `max_select` int NOT NULL DEFAULT 1,
  `sort` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_item_option` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `group_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(80) NOT NULL DEFAULT '',
  `price_delta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_group` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_combo` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `category_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(120) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort` int NOT NULL DEFAULT 0,
  `sold_out` tinyint NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_combo_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `combo_id` int unsigned NOT NULL DEFAULT 0,
  `item_id` int unsigned NOT NULL DEFAULT 0,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_combo_item` (`tenant_id`,`combo_id`,`item_id`),
  KEY `idx_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_kds_event` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `order_id` int unsigned NOT NULL DEFAULT 0,
  `event_type` varchar(40) NOT NULL DEFAULT '',
  `payload` text,
  `create_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_ai_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `provider` varchar(50) NOT NULL DEFAULT '',
  `api_key` varchar(255) NOT NULL DEFAULT '',
  `api_base` varchar(255) NOT NULL DEFAULT '',
  `model` varchar(100) NOT NULL DEFAULT 'gpt-3.5-turbo',
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_provider` (`tenant_id`,`provider`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_ai_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `module` varchar(100) NOT NULL DEFAULT '',
  `action` varchar(100) NOT NULL DEFAULT '',
  `request_text` text,
  `response_text` text,
  `tokens_used` int NOT NULL DEFAULT 0,
  `cost_ms` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `error_msg` varchar(500) NOT NULL DEFAULT '',
  `create_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_module` (`module`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_ai_daily_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `report_date` date NOT NULL,
  `content` text,
  `summary` varchar(500) NOT NULL DEFAULT '',
  `create_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_date` (`tenant_id`,`report_date`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_review` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `platform` varchar(30) NOT NULL DEFAULT '',
  `external_id` varchar(80) NOT NULL DEFAULT '',
  `rating` tinyint NOT NULL DEFAULT 0,
  `content` text,
  `images` text,
  `review_time` int NOT NULL DEFAULT 0,
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `sentiment` tinyint NOT NULL DEFAULT 0,
  `suggest_reply` text,
  `reply_content` text,
  `reply_status` tinyint NOT NULL DEFAULT 0,
  `raw` text,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_platform_external` (`tenant_id`,`platform`,`external_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_time` (`review_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_review_reply_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `platform` varchar(30) NOT NULL DEFAULT '',
  `scene` varchar(20) NOT NULL DEFAULT 'good',
  `rating_min` tinyint NOT NULL DEFAULT 0,
  `rating_max` tinyint NOT NULL DEFAULT 5,
  `template` text,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_platform_scene` (`platform`,`scene`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_review_keyword` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `keyword` varchar(50) NOT NULL DEFAULT '',
  `category` varchar(30) NOT NULL DEFAULT '',
  `weight` int NOT NULL DEFAULT 1,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_keyword` (`tenant_id`,`keyword`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fa_restaurant_review_alert` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `store_id` int unsigned NOT NULL DEFAULT 0,
  `platform` varchar(30) NOT NULL DEFAULT '',
  `external_id` varchar(80) NOT NULL DEFAULT '',
  `alert_type` varchar(30) NOT NULL DEFAULT 'bad_review',
  `rating` tinyint NOT NULL DEFAULT 0,
  `content` varchar(500) NOT NULL DEFAULT '',
  `review_time` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 0,
  `create_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_type_external` (`tenant_id`,`alert_type`,`platform`,`external_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_time` (`review_time`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
