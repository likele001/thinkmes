-- 插件市场系统数据表
-- 执行：mysql -u用户 -p 数据库名 < database/migrate_market_system.sql

-- 插件市场表
CREATE TABLE IF NOT EXISTS `fa_market_plugin` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '插件标识',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '插件标题',
  `description` text COMMENT '插件描述',
  `author` varchar(50) NOT NULL DEFAULT '' COMMENT '作者',
  `version` varchar(20) NOT NULL DEFAULT '1.0.0' COMMENT '版本号',
  `category` varchar(50) NOT NULL DEFAULT 'other' COMMENT '分类：tool,plugin,template,theme,other',
  `homepage` varchar(255) NOT NULL DEFAULT '' COMMENT '插件主页',
  `screenshot` varchar(255) NOT NULL DEFAULT '' COMMENT '截图URL',
  `download_url` varchar(255) NOT NULL DEFAULT '' COMMENT '下载地址',
  `file_size` int unsigned NOT NULL DEFAULT 0 COMMENT '文件大小（字节）',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '价格（0为免费）',
  `min_version` varchar(20) NOT NULL DEFAULT '' COMMENT '最低支持版本',
  `max_version` varchar(20) NOT NULL DEFAULT '' COMMENT '最高支持版本',
  `require_php` varchar(20) NOT NULL DEFAULT '7.4' COMMENT '最低PHP版本',
  `dependencies` text COMMENT '依赖插件（JSON数组）',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键词',
  `downloads` int unsigned NOT NULL DEFAULT 0 COMMENT '下载次数',
  `rating` decimal(3,2) NOT NULL DEFAULT 0.00 COMMENT '评分（0-5）',
  `rating_count` int unsigned NOT NULL DEFAULT 0 COMMENT '评分人数',
  `is_official` tinyint NOT NULL DEFAULT 0 COMMENT '是否官方：0否 1是',
  `is_featured` tinyint NOT NULL DEFAULT 0 COMMENT '是否推荐：0否 1是',
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT '状态：active-上架，draft-草稿，removed-下架',
  `released_at` int unsigned NOT NULL DEFAULT 0 COMMENT '发布时间',
  `updated_at` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件市场';

-- 插件版本表
CREATE TABLE IF NOT EXISTS `fa_market_plugin_version` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `plugin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '插件ID',
  `version` varchar(20) NOT NULL DEFAULT '' COMMENT '版本号',
  `changelog` text COMMENT '更新日志',
  `download_url` varchar(255) NOT NULL DEFAULT '' COMMENT '下载地址',
  `file_size` int unsigned NOT NULL DEFAULT 0 COMMENT '文件大小（字节）',
  `min_version` varchar(20) NOT NULL DEFAULT '' COMMENT '最低支持版本',
  `max_version` varchar(20) NOT NULL DEFAULT '' COMMENT '最高支持版本',
  `is_stable` tinyint NOT NULL DEFAULT 1 COMMENT '是否稳定版：0否 1是',
  `downloads` int unsigned NOT NULL DEFAULT 0 COMMENT '下载次数',
  `released_at` int unsigned NOT NULL DEFAULT 0 COMMENT '发布时间',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plugin_version` (`plugin_id`, `version`),
  KEY `idx_plugin_id` (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件版本';

-- 插件评价表
CREATE TABLE IF NOT EXISTS `fa_market_plugin_review` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `plugin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '插件ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `user_name` varchar(100) NOT NULL DEFAULT '' COMMENT '用户名',
  `rating` tinyint NOT NULL DEFAULT 5 COMMENT '评分（1-5）',
  `content` text COMMENT '评价内容',
  `is_verified` tinyint NOT NULL DEFAULT 0 COMMENT '是否已验证购买：0否 1是',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plugin_user` (`plugin_id`, `user_id`),
  KEY `idx_plugin_id` (`plugin_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件评价';

-- 插件安装记录表
CREATE TABLE IF NOT EXISTS `fa_market_plugin_install` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `plugin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '市场插件ID',
  `version` varchar(20) NOT NULL DEFAULT '' COMMENT '安装版本',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `install_time` int unsigned NOT NULL DEFAULT 0 COMMENT '安装时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_plugin_user` (`plugin_id`, `user_id`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件安装记录';

-- 初始化示例数据
INSERT INTO `fa_market_plugin` (`name`, `title`, `description`, `author`, `version`, `category`, `homepage`, `download_url`, `file_size`, `price`, `min_version`, `max_version`, `require_php`, `dependencies`, `keywords`, `downloads`, `rating`, `rating_count`, `is_official`, `is_featured`, `status`, `released_at`, `updated_at`, `create_time`, `update_time`) VALUES
('cloudstorage', '云存储插件', '支持阿里云OSS、腾讯云COS、七牛云、又拍云等多种云存储', 'keleadmin', '1.0.0', 'plugin', 'https://example.com/plugin/cloudstorage', 'https://example.com/download/cloudstorage.zip', 102400, 0.00, '1.0.0', '', '7.4', '[]', 'oss,cos,qiniu,upyun,云存储', 150, 4.80, 25, 1, 1, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('demo', '示例插件', '演示如何开发插件和使用钩子系统', 'keleadmin', '1.0.0', 'plugin', 'https://example.com/plugin/demo', 'https://example.com/download/demo.zip', 20480, 0.00, '1.0.0', '', '7.4', '[]', 'demo,示例,钩子', 80, 4.50, 10, 1, 0, 'active', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
