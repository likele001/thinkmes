-- 工厂 AI 模块表结构
-- 所有表带 tenant_id，严格多租户隔离
-- 不修改系统核心表，仅新增

-- AI 配置表（API Key、供应商、限流等）
DROP TABLE IF EXISTS `fa_ai_config`;
CREATE TABLE `fa_ai_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `provider` varchar(50) NOT NULL DEFAULT '' COMMENT '供应商：openai/azure/zhipu/baidu等',
  `api_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'API Key（加密存储）',
  `api_base` varchar(255) NOT NULL DEFAULT '' COMMENT 'API 基础URL',
  `model` varchar(100) NOT NULL DEFAULT 'gpt-3.5-turbo' COMMENT '模型名称',
  `speech_provider` varchar(50) NOT NULL DEFAULT '' COMMENT '语音识别供应商',
  `speech_api_key` varchar(255) NOT NULL DEFAULT '' COMMENT '语音API Key',
  `rate_limit_per_day` int NOT NULL DEFAULT 1000 COMMENT '每日限流次数',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tenant_provider` (`tenant_id`, `provider`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI配置表';

-- AI 调用日志表
DROP TABLE IF EXISTS `fa_ai_log`;
CREATE TABLE `fa_ai_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `admin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '调用管理员ID',
  `module` varchar(50) NOT NULL DEFAULT '' COMMENT '模块：voice_report/anomaly/qa/daily_report/crm_follow',
  `action` varchar(50) NOT NULL DEFAULT '' COMMENT '动作',
  `request_text` text COMMENT '请求内容',
  `response_text` text COMMENT '响应内容',
  `tokens_used` int NOT NULL DEFAULT 0 COMMENT '消耗token数',
  `cost_ms` int NOT NULL DEFAULT 0 COMMENT '耗时毫秒',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1成功 0失败',
  `error_msg` varchar(500) NOT NULL DEFAULT '' COMMENT '错误信息',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_module` (`module`),
  KEY `idx_create` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI调用日志';

-- 语音报工记录表（语音→文字→结构化结果）
DROP TABLE IF EXISTS `fa_ai_voice_report`;
CREATE TABLE `fa_ai_voice_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `admin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '报工员工ID',
  `voice_url` varchar(500) NOT NULL DEFAULT '' COMMENT '语音文件URL',
  `voice_text` text COMMENT '语音转文字结果',
  `parsed_data` text COMMENT '解析后的结构化数据JSON',
  `report_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联报工ID',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待确认 1已提交 2已忽略',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_admin` (`admin_id`),
  KEY `idx_report` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='语音报工记录';

-- AI 报工异常检测记录表
DROP TABLE IF EXISTS `fa_ai_anomaly`;
CREATE TABLE `fa_ai_anomaly` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `report_id` int unsigned NOT NULL DEFAULT 0 COMMENT '报工ID',
  `anomaly_type` varchar(50) NOT NULL DEFAULT '' COMMENT '异常类型：duplicate/fraud/timeout/unreasonable',
  `score` decimal(5,2) NOT NULL DEFAULT 0 COMMENT '异常分数0-100',
  `detail` text COMMENT '异常详情JSON',
  `ai_reason` text COMMENT 'AI分析原因',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待处理 1已确认 2已忽略',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_report` (`report_id`),
  KEY `idx_type` (`anomaly_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='报工异常检测';

-- AI 老板问答历史表
DROP TABLE IF EXISTS `fa_ai_qa_history`;
CREATE TABLE `fa_ai_qa_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `admin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '提问人ID',
  `question` text NOT NULL COMMENT '问题',
  `answer` text COMMENT 'AI回答',
  `data_sources` text COMMENT '数据来源说明JSON',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_admin` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI问答历史';

-- AI 生产日报/周报表
DROP TABLE IF EXISTS `fa_ai_daily_report`;
CREATE TABLE `fa_ai_daily_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `report_type` varchar(20) NOT NULL DEFAULT 'daily' COMMENT '类型：daily/weekly',
  `report_date` date NOT NULL COMMENT '报告日期',
  `content` text COMMENT 'AI生成内容',
  `summary` varchar(500) NOT NULL DEFAULT '' COMMENT '摘要',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tenant_type_date` (`tenant_id`, `report_type`, `report_date`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI生产日报周报';

-- AI CRM 跟单建议表
DROP TABLE IF EXISTS `fa_ai_crm_follow`;
CREATE TABLE `fa_ai_crm_follow` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `opportunity_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商机ID',
  `suggestion_type` varchar(50) NOT NULL DEFAULT '' COMMENT '类型：follow_advice/script/intent',
  `content` text COMMENT '建议内容',
  `intent_score` decimal(5,2) NOT NULL DEFAULT 0 COMMENT '意向分数0-100',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_opportunity` (`opportunity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI CRM跟单建议';
