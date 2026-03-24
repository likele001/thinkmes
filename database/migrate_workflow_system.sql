-- 工作流系统数据表
-- 执行：mysql -u用户 -p 数据库名 < database/migrate_workflow_system.sql

-- 工作流定义表
CREATE TABLE IF NOT EXISTS `fa_workflow` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '工作流名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '工作流代码（唯一标识）',
  `table_name` varchar(100) NOT NULL DEFAULT '' COMMENT '关联表名',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '工作流描述',
  `is_active` tinyint NOT NULL DEFAULT 1 COMMENT '是否激活：0禁用 1启用',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流定义';

-- 工作流状态表
CREATE TABLE IF NOT EXISTS `fa_workflow_state` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `workflow_id` int unsigned NOT NULL DEFAULT 0 COMMENT '工作流ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '状态名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '状态代码',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '状态描述',
  `is_initial` tinyint NOT NULL DEFAULT 0 COMMENT '是否初始状态：0否 1是',
  `is_final` tinyint NOT NULL DEFAULT 0 COMMENT '是否最终状态：0否 1是',
  `color` varchar(20) NOT NULL DEFAULT '#1890ff' COMMENT '状态颜色',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_workflow_code` (`workflow_id`, `code`),
  KEY `idx_workflow_id` (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流状态';

-- 工作流转换表（状态流转规则）
CREATE TABLE IF NOT EXISTS `fa_workflow_transition` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `workflow_id` int unsigned NOT NULL DEFAULT 0 COMMENT '工作流ID',
  `from_state_id` int unsigned NOT NULL DEFAULT 0 COMMENT '源状态ID',
  `to_state_id` int unsigned NOT NULL DEFAULT 0 COMMENT '目标状态ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '转换名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '转换代码',
  `condition_expression` text COMMENT '流转条件表达式（JSON）',
  `require_approval` tinyint NOT NULL DEFAULT 0 COMMENT '是否需要审批：0否 1是',
  `approval_type` varchar(20) NOT NULL DEFAULT 'all' COMMENT '审批类型：all-全部通过，any-任意一人通过',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_workflow_from` (`workflow_id`, `from_state_id`),
  KEY `idx_to_state` (`to_state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流转换';

-- 工作流审批人表
CREATE TABLE IF NOT EXISTS `fa_workflow_approver` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `transition_id` int unsigned NOT NULL DEFAULT 0 COMMENT '转换ID',
  `approver_type` varchar(20) NOT NULL DEFAULT 'user' COMMENT '审批人类型：user-指定用户，role-角色，dept-部门',
  `approver_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审批人ID（用户ID/角色ID/部门ID）',
  `approver_name` varchar(100) NOT NULL DEFAULT '' COMMENT '审批人名称',
  `sort` int NOT NULL DEFAULT 0 COMMENT '审批顺序',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_transition_id` (`transition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流审批人';

-- 工作流实例表
CREATE TABLE IF NOT EXISTS `fa_workflow_instance` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `workflow_id` int unsigned NOT NULL DEFAULT 0 COMMENT '工作流ID',
  `table_name` varchar(100) NOT NULL DEFAULT '' COMMENT '关联表名',
  `record_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联记录ID',
  `current_state_id` int unsigned NOT NULL DEFAULT 0 COMMENT '当前状态ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '实例标题',
  `initiator_id` int unsigned NOT NULL DEFAULT 0 COMMENT '发起人ID',
  `initiator_name` varchar(100) NOT NULL DEFAULT '' COMMENT '发起人名称',
  `is_completed` tinyint NOT NULL DEFAULT 0 COMMENT '是否完成：0否 1是',
  `completed_time` int unsigned NOT NULL DEFAULT 0 COMMENT '完成时间',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_record` (`table_name`, `record_id`),
  KEY `idx_workflow_id` (`workflow_id`),
  KEY `idx_current_state` (`current_state_id`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流实例';

-- 工作流审批记录表
CREATE TABLE IF NOT EXISTS `fa_workflow_approval` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `instance_id` int unsigned NOT NULL DEFAULT 0 COMMENT '实例ID',
  `transition_id` int unsigned NOT NULL DEFAULT 0 COMMENT '转换ID',
  `from_state_id` int unsigned NOT NULL DEFAULT 0 COMMENT '源状态ID',
  `to_state_id` int unsigned NOT NULL DEFAULT 0 COMMENT '目标状态ID',
  `approver_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审批人ID',
  `approver_name` varchar(100) NOT NULL DEFAULT '' COMMENT '审批人名称',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '审批状态：pending-待审批，approved-已通过，rejected-已拒绝，cancelled-已取消',
  `comment` text COMMENT '审批意见',
  `approval_time` int unsigned NOT NULL DEFAULT 0 COMMENT '审批时间',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_instance_id` (`instance_id`),
  KEY `idx_approver_status` (`approver_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流审批记录';

-- 初始化示例数据：订单审批工作流
INSERT INTO `fa_workflow` (`name`, `code`, `table_name`, `description`, `is_active`, `tenant_id`, `create_time`, `update_time`) VALUES
('订单审批流程', 'order_approval', 'mes_order', '订单审批工作流', 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取工作流ID（假设为1）
SET @workflow_id = LAST_INSERT_ID();

-- 插入状态
INSERT INTO `fa_workflow_state` (`workflow_id`, `name`, `code`, `description`, `is_initial`, `is_final`, `color`, `sort`, `create_time`, `update_time`) VALUES
(@workflow_id, '草稿', 'draft', '订单草稿状态', 1, 0, '#faad14', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@workflow_id, '待审核', 'pending', '等待审核', 0, 0, '#1890ff', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@workflow_id, '已通过', 'approved', '审核通过', 0, 1, '#52c41a', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@workflow_id, '已拒绝', 'rejected', '审核拒绝', 0, 1, '#ff4d4f', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取状态ID
SET @state_draft = 1;
SET @state_pending = 2;
SET @state_approved = 3;
SET @state_rejected = 4;

-- 插入转换规则
INSERT INTO `fa_workflow_transition` (`workflow_id`, `from_state_id`, `to_state_id`, `name`, `code`, `require_approval`, `approval_type`, `sort`, `create_time`, `update_time`) VALUES
(@workflow_id, @state_draft, @state_pending, '提交审核', 'submit', 1, 'all', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@workflow_id, @state_pending, @state_approved, '通过', 'approve', 0, '', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@workflow_id, @state_pending, @state_rejected, '拒绝', 'reject', 0, '', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@workflow_id, @state_rejected, @state_draft, '重新编辑', 'edit', 0, '', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
