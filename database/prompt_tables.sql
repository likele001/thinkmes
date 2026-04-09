-- AI 提示词工坊 - 数据表
-- 安装时由 AppCenter runSqlFile 执行，fa_ 会被替换为实际前缀

CREATE TABLE IF NOT EXISTS `fa_prompt_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名称',
  `icon` varchar(50) NOT NULL DEFAULT 'fas fa-star' COMMENT 'FontAwesome图标',
  `sort` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '1启用 0禁用',
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='提示词分类';

CREATE TABLE IF NOT EXISTS `fa_prompt_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int unsigned NOT NULL DEFAULT 0,
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '模板标题',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '简介',
  `prompt_text` text NOT NULL COMMENT '提示词内容，含{变量}占位符',
  `variables` text COMMENT '变量定义JSON数组[{name,label,placeholder,required}]',
  `system_prompt` text COMMENT '系统提示词(可选)',
  `output_words` int NOT NULL DEFAULT 0 COMMENT '最大字数(约，0不限制)',
  `ext_prompt` text COMMENT '附加要求（会自动拼到最终提示词，可用{变量}）',
  `ext_variables` text COMMENT '扩展变量定义JSON数组（会追加到 variables）',
  `icon` varchar(50) NOT NULL DEFAULT '',
  `sort` int NOT NULL DEFAULT 0,
  `use_count` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status_sort` (`status`,`sort`),
  UNIQUE KEY `uk_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='提示词模板';

CREATE TABLE IF NOT EXISTS `fa_prompt_generation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `template_id` int unsigned NOT NULL DEFAULT 0,
  `template_title` varchar(100) NOT NULL DEFAULT '',
  `input_text` text COMMENT '填充变量后的完整提示词',
  `variables_input` text COMMENT '用户填写的变量JSON',
  `output_text` mediumtext COMMENT 'AI回复',
  `image_task_id` varchar(100) NOT NULL DEFAULT '' COMMENT '图片生成任务ID(智谱AI)',
  `image_status` varchar(20) NOT NULL DEFAULT '' COMMENT '图片任务状态：PROCESSING/SUCCESS/FAIL/EMPTY',
  `image_url` text,
  `image_size` varchar(30) NOT NULL DEFAULT '' COMMENT '图片尺寸',
  `image_error_msg` varchar(500) NOT NULL DEFAULT '' COMMENT '图片生成失败原因',
  `video_task_id` varchar(100) NOT NULL DEFAULT '' COMMENT '视频生成任务ID(智谱AI)',
  `video_status` varchar(20) NOT NULL DEFAULT '' COMMENT '视频任务状态：PROCESSING/SUCCESS/FAIL/EMPTY',
  `video_url` text,
  `video_duration` int NOT NULL DEFAULT 0 COMMENT '视频时长(秒)',
  `video_error_msg` varchar(500) NOT NULL DEFAULT '' COMMENT '视频生成失败原因',
  `tokens_used` int NOT NULL DEFAULT 0,
  `cost_ms` int NOT NULL DEFAULT 0,
  `is_favorite` tinyint NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '1成功 0失败',
  `error_msg` varchar(500) NOT NULL DEFAULT '',
  `create_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_create` (`create_time`),
  KEY `idx_image_task` (`image_task_id`),
  KEY `idx_video_task` (`video_task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI生成记录';

CREATE TABLE IF NOT EXISTS `fa_prompt_quota` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `free_quota` int NOT NULL DEFAULT 0 COMMENT '免费剩余次数',
  `paid_quota` int NOT NULL DEFAULT 0 COMMENT '购买剩余次数',
  `total_used` int NOT NULL DEFAULT 0 COMMENT '累计使用',
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户额度';

CREATE TABLE IF NOT EXISTS `fa_prompt_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `order_no` varchar(32) NOT NULL COMMENT '订单号',
  `product_name` varchar(100) NOT NULL DEFAULT '',
  `quota_count` int NOT NULL DEFAULT 0 COMMENT '购买次数',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0待支付 1已支付 2已取消',
  `pay_method` varchar(20) NOT NULL DEFAULT '',
  `pay_time` int DEFAULT NULL,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='购买订单';

CREATE TABLE IF NOT EXISTS `fa_prompt_ai_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(50) NOT NULL DEFAULT '' COMMENT '服务商(openai/zhipu/qwen等)',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '配置名称',
  `api_key` varchar(500) NOT NULL DEFAULT '',
  `api_base` varchar(255) NOT NULL DEFAULT '' COMMENT 'API地址',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '模型名称',
  `image_model` varchar(100) NOT NULL DEFAULT '' COMMENT '图片模型名称（如 cogview-3-flash）',
  `video_model` varchar(100) NOT NULL DEFAULT '' COMMENT '视频模型名称（如 cogvideox-flash）',
  `max_tokens` int NOT NULL DEFAULT 2048,
  `temperature` decimal(3,2) NOT NULL DEFAULT 0.70,
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '1启用 0禁用',
  `sort` int NOT NULL DEFAULT 0,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI配置（独立于全局）';

-- 应用计费配置
INSERT INTO `fa_config` (`name`,`title`,`value`,`group`,`sort`,`create_time`,`update_time`) VALUES
('prompt_free_quota',    '新用户免费次数',           '5',    'prompt', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt_price_s',       '体验包价格(元/10次)',        '9.9',  'prompt', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt_price_m',       '畅享包价格(元/50次)',        '39.9', 'prompt', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt_price_month',   '月度套餐价格(元/月/100次)', '59.9', 'prompt', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt_enable_pay',    '是否开启付费功能',           '1',    'prompt', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt_output_words',  '默认输出字数(约)',           '300',  'prompt', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `value`=VALUES(`value`);

-- 为已有表补加唯一键（ALTER IGNORE 避免重复数据报错）
ALTER TABLE `fa_prompt_category` ADD UNIQUE KEY `uk_name` (`name`);
ALTER TABLE `fa_prompt_template` ADD UNIQUE KEY `uk_title` (`title`);
