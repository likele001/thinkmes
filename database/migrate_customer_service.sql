-- 智能客服和帮助文档系统数据库表

-- 1. 客服会话表 (fa_cs_session)
CREATE TABLE IF NOT EXISTS `fa_cs_session` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '会话ID',
  `session_id` varchar(64) NOT NULL DEFAULT '' COMMENT '会话唯一标识',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '用户ID(0表示匿名)',
  `visitor_name` varchar(100) NOT NULL DEFAULT '' COMMENT '访客名称',
  `visitor_email` varchar(100) NOT NULL DEFAULT '' COMMENT '访客邮箱',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待接待 1进行中 2已关闭',
  `assigned_to` int unsigned NOT NULL DEFAULT 0 COMMENT '分配给的客服ID',
  `start_time` int unsigned NOT NULL DEFAULT 0 COMMENT '开始时间',
  `end_time` int unsigned NOT NULL DEFAULT 0 COMMENT '结束时间',
  `last_message_time` int unsigned NOT NULL DEFAULT 0 COMMENT '最后消息时间',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_session_id` (`session_id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_assigned` (`assigned_to`),
  KEY `idx_last_msg` (`last_message_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客服会话表';

-- 2. 聊天消息表 (fa_cs_message)
CREATE TABLE IF NOT EXISTS `fa_cs_message` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '消息ID',
  `session_id` varchar(64) NOT NULL DEFAULT '' COMMENT '会话ID',
  `sender_type` tinyint NOT NULL DEFAULT 0 COMMENT '发送者类型：0访客 1客服 2系统',
  `sender_id` int unsigned NOT NULL DEFAULT 0 COMMENT '发送者ID',
  `message_type` tinyint NOT NULL DEFAULT 0 COMMENT '消息类型：0文本 1图片 2文件 3系统提示',
  `content` text COMMENT '消息内容',
  `extra_data` text COMMENT '额外数据(JSON)',
  `is_read` tinyint NOT NULL DEFAULT 0 COMMENT '是否已读',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='聊天消息表';

-- 3. 工单表 (fa_cs_ticket)
CREATE TABLE IF NOT EXISTS `fa_cs_ticket` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '工单ID',
  `ticket_no` varchar(40) NOT NULL DEFAULT '' COMMENT '工单编号',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '提交用户ID',
  `category` varchar(50) NOT NULL DEFAULT '' COMMENT '工单分类',
  `priority` tinyint NOT NULL DEFAULT 1 COMMENT '优先级：1低 2中 3高 4紧急',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '工单标题',
  `description` text COMMENT '问题描述',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待处理 1处理中 2等待回复 3已解决 4已关闭',
  `assigned_to` int unsigned NOT NULL DEFAULT 0 COMMENT '分配给的客服ID',
  `resolved_at` int unsigned NOT NULL DEFAULT 0 COMMENT '解决时间',
  `closed_at` int unsigned NOT NULL DEFAULT 0 COMMENT '关闭时间',
  `satisfaction` tinyint NOT NULL DEFAULT 0 COMMENT '满意度评分：1-5分',
  `feedback` text COMMENT '用户反馈',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ticket_no` (`ticket_no`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category`),
  KEY `idx_assigned` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工单表';

-- 4. 工单回复表 (fa_cs_ticket_reply)
CREATE TABLE IF NOT EXISTS `fa_cs_ticket_reply` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '回复ID',
  `ticket_id` int unsigned NOT NULL DEFAULT 0 COMMENT '工单ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '回复用户ID',
  `user_type` tinyint NOT NULL DEFAULT 0 COMMENT '用户类型：0用户 1客服',
  `content` text COMMENT '回复内容',
  `attachments` text COMMENT '附件(JSON数组)',
  `is_internal` tinyint NOT NULL DEFAULT 0 COMMENT '是否为内部备注',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_ticket_id` (`ticket_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工单回复表';

-- 5. 知识库分类表 (fa_cs_kb_category)
CREATE TABLE IF NOT EXISTS `fa_cs_kb_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `parent_id` int unsigned NOT NULL DEFAULT 0 COMMENT '父分类ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID(0表示全局)',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '分类描述',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '分类图标',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='知识库分类表';

-- 6. 知识库文章表 (fa_cs_kb_article)
CREATE TABLE IF NOT EXISTS `fa_cs_kb_article` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `category_id` int unsigned NOT NULL DEFAULT 0 COMMENT '分类ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID(0表示全局)',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '文章标题',
  `summary` varchar(500) NOT NULL DEFAULT '' COMMENT '摘要',
  `content` longtext COMMENT '文章内容(Markdown)',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '标签(逗号分隔)',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO关键词',
  `views` int unsigned NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `likes` int unsigned NOT NULL DEFAULT 0 COMMENT '点赞次数',
  `helpful` int unsigned NOT NULL DEFAULT 0 COMMENT '有用次数',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0草稿 1发布 2下线',
  `created_by` int unsigned NOT NULL DEFAULT 0 COMMENT '创建人',
  `updated_by` int unsigned NOT NULL DEFAULT 0 COMMENT '更新人',
  `published_at` int unsigned NOT NULL DEFAULT 0 COMMENT '发布时间',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_status` (`status`),
  KEY `idx_views` (`views`),
  FULLTEXT KEY `ft_title_content` (`title`,`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='知识库文章表';

-- 7. 常见问题表 (fa_cs_faq)
CREATE TABLE IF NOT EXISTS `fa_cs_faq` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'FAQ ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID(0表示全局)',
  `category` varchar(50) NOT NULL DEFAULT '' COMMENT '分类',
  `question` varchar(255) NOT NULL DEFAULT '' COMMENT '问题',
  `answer` text COMMENT '答案',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `views` int unsigned NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `helpful` int unsigned NOT NULL DEFAULT 0 COMMENT '有用次数',
  `not_helpful` int unsigned NOT NULL DEFAULT 0 COMMENT '无用次数',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='常见问题表';

-- 8. AI对话历史表 (fa_cs_ai_history)
CREATE TABLE IF NOT EXISTS `fa_cs_ai_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `session_id` varchar(64) NOT NULL DEFAULT '' COMMENT '会话ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `user_message` text COMMENT '用户消息',
  `ai_response` text COMMENT 'AI回复',
  `context_data` text COMMENT '上下文数据(JSON)',
  `sources` text COMMENT '知识来源(JSON数组)',
  `confidence` decimal(3,2) NOT NULL DEFAULT 0.00 COMMENT '置信度',
  `model` varchar(50) NOT NULL DEFAULT '' COMMENT '使用的AI模型',
  `tokens_used` int unsigned NOT NULL DEFAULT 0 COMMENT '消耗的token数',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI对话历史表';

-- 9. 客服人员表 (fa_cs_agent)
CREATE TABLE IF NOT EXISTS `fa_cs_agent` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '客服ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '客服昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0离线 1在线 2忙碌 3离开',
  `max_concurrent` int unsigned NOT NULL DEFAULT 5 COMMENT '最大并发会话数',
  `current_sessions` int unsigned NOT NULL DEFAULT 0 COMMENT '当前会话数',
  `auto_accept` tinyint NOT NULL DEFAULT 0 COMMENT '是否自动接单',
  `skills` text COMMENT '技能标签(JSON数组)',
  `last_active_time` int unsigned NOT NULL DEFAULT 0 COMMENT '最后活跃时间',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客服人员表';

-- 10. 系统配置表 (fa_cs_config)
CREATE TABLE IF NOT EXISTS `fa_cs_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID(0表示全局默认)',
  `config_key` varchar(100) NOT NULL DEFAULT '' COMMENT '配置键',
  `config_value` text COMMENT '配置值(JSON)',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '配置说明',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_key` (`tenant_id`,`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客服系统配置表';

-- 插入默认配置
INSERT INTO `fa_cs_config` (`tenant_id`, `config_key`, `config_value`, `description`, `create_time`, `update_time`) VALUES
(0, 'ai_settings', '{"enabled":true,"model":"gpt-3.5-turbo","temperature":0.7,"max_tokens":1000,"system_prompt":"你是一个专业的客服助手，帮助用户解答关于ThinkMES系统的问题。"}', 'AI设置', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'chat_settings', '{"welcome_message":"您好，我是智能客服助手，有什么可以帮助您的吗？","auto_assign":true,"max_wait_time":60}', '聊天设置', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 'ticket_settings', '{"auto_close_days":7,"notification_enabled":true,"email_enabled":false}', '工单设置', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `update_time` = UNIX_TIMESTAMP();

-- 插入默认知识库分类
INSERT INTO `fa_cs_kb_category` (`parent_id`, `tenant_id`, `name`, `description`, `icon`, `sort`, `status`, `create_time`, `update_time`) VALUES
(0, 0, '快速入门', 'ThinkMES系统快速入门指南', 'fa-rocket', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 0, '功能说明', '系统各功能模块详细说明', 'fa-book', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 0, '常见问题', '用户常见问题解答', 'fa-question-circle', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 0, '视频教程', '视频操作教程', 'fa-video', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, 0, 'API文档', '开发者API接口文档', 'fa-code', 5, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `update_time` = UNIX_TIMESTAMP();

-- 插入示例FAQ
INSERT INTO `fa_cs_faq` (`tenant_id`, `category`, `question`, `answer`, `sort`, `status`, `create_time`, `update_time`) VALUES
(0, '快速入门', '如何开始使用ThinkMES？', 'ThinkMES是一款制造执行系统，您可以通过以下步骤开始使用：\n1. 完成系统初始化设置\n2. 创建企业组织和员工信息\n3. 配置基础数据（产品、客户、供应商等）\n4. 开始创建生产工单\n5. 进行生产报工和进度跟踪', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '常见问题', '忘记密码怎么办？', '如果您忘记了密码，可以：\n1. 在登录页面点击\"忘记密码\"\n2. 输入您的注册邮箱或手机号\n3. 按照提示重置密码\n如果您无法通过邮箱重置，请联系系统管理员。', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(0, '功能说明', 'ThinkMES支持哪些功能模块？', 'ThinkMES提供以下核心功能模块：\n• MES制造执行：工单管理、报工、工序、BOM、生产计划\n• CRM客户关系：客户、商机、合同、跟进、回款\n• AI智能助手：AI配置、语音报工、智能跟单\n• 支付管理：支付配置与订单支付\n• 设备管理：设备、点检、保养、维修\n• 人事考勤：组织、员工、考勤、排班\n• 财务管理：科目、凭证、账簿、报表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `update_time` = UNIX_TIMESTAMP();
