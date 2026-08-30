-- 新工作流审批引擎（线性节点）- 数据库结构

CREATE TABLE IF NOT EXISTS `fa_wf_definition` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '流程名称（同租户唯一）',
  `module_code` varchar(60) NOT NULL DEFAULT '' COMMENT '业务模块标识',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_name` (`tenant_id`,`name`),
  KEY `idx_tenant_module` (`tenant_id`,`module_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流-流程定义';

CREATE TABLE IF NOT EXISTS `fa_wf_node` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `definition_id` int unsigned NOT NULL DEFAULT 0 COMMENT '流程定义ID',
  `sort` int unsigned NOT NULL DEFAULT 1 COMMENT '节点顺序（从1开始）',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '节点名称',
  `approver_type` varchar(30) NOT NULL DEFAULT 'admin' COMMENT '审批人类型：admin|role|dept_manager|initiator_select',
  `approver_ids` text NULL COMMENT '审批人ID列表（JSON数组）',
  `approval_mode` varchar(20) NOT NULL DEFAULT 'any_sign' COMMENT '审批方式：countersign|any_sign',
  `condition_logic` varchar(10) NOT NULL DEFAULT 'AND' COMMENT '条件逻辑：AND|OR',
  `condition_items` text NULL COMMENT '条件项（JSON数组）',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_def_sort` (`definition_id`,`sort`),
  KEY `idx_tenant_def` (`tenant_id`,`definition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流-审批节点';

CREATE TABLE IF NOT EXISTS `fa_wf_module` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `module_code` varchar(60) NOT NULL DEFAULT '' COMMENT '业务模块标识',
  `enabled` tinyint NOT NULL DEFAULT 0 COMMENT '是否启用：1启用 0禁用',
  `definition_id` int unsigned NOT NULL DEFAULT 0 COMMENT '默认流程定义ID',
  `table_name` varchar(120) NOT NULL DEFAULT '' COMMENT '业务表名（Db::name）',
  `title_field` varchar(80) NOT NULL DEFAULT '' COMMENT '标题字段名',
  `status_field` varchar(80) NOT NULL DEFAULT '' COMMENT '状态字段名',
  `approved_value` varchar(80) NOT NULL DEFAULT '' COMMENT '审批通过状态值',
  `rejected_value` varchar(80) NOT NULL DEFAULT '' COMMENT '审批拒绝状态值',
  `in_progress_value` varchar(80) NOT NULL DEFAULT '' COMMENT '审批中状态值（可选）',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_module` (`tenant_id`,`module_code`),
  KEY `idx_tenant_enabled` (`tenant_id`,`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流-业务模块接入';

CREATE TABLE IF NOT EXISTS `fa_wf_instance` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `instance_no` varchar(40) NOT NULL DEFAULT '' COMMENT '实例编号',
  `definition_id` int unsigned NOT NULL DEFAULT 0 COMMENT '流程定义ID',
  `module_code` varchar(60) NOT NULL DEFAULT '' COMMENT '业务模块标识',
  `business_id` int unsigned NOT NULL DEFAULT 0 COMMENT '业务ID',
  `business_title` varchar(255) NOT NULL DEFAULT '' COMMENT '业务标题',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0审批中 1已通过 2已拒绝 3已撤回 4回写异常',
  `current_node_id` int unsigned NOT NULL DEFAULT 0 COMMENT '当前节点ID',
  `current_sort` int unsigned NOT NULL DEFAULT 0 COMMENT '当前节点顺序',
  `initiator_id` int unsigned NOT NULL DEFAULT 0 COMMENT '发起人管理员ID',
  `initiator_name` varchar(100) NOT NULL DEFAULT '' COMMENT '发起人名称',
  `start_time` int unsigned NOT NULL DEFAULT 0 COMMENT '发起时间',
  `end_time` int unsigned NOT NULL DEFAULT 0 COMMENT '结束时间',
  `extra` longtext NULL COMMENT '扩展数据（JSON）',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_instance_no` (`instance_no`),
  KEY `idx_tenant_status` (`tenant_id`,`status`),
  KEY `idx_tenant_module_biz` (`tenant_id`,`module_code`,`business_id`),
  KEY `idx_current_node` (`current_node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流-流程实例';

CREATE TABLE IF NOT EXISTS `fa_wf_task` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `instance_id` int unsigned NOT NULL DEFAULT 0 COMMENT '实例ID',
  `node_id` int unsigned NOT NULL DEFAULT 0 COMMENT '节点ID',
  `node_sort` int unsigned NOT NULL DEFAULT 0 COMMENT '节点顺序',
  `approval_mode` varchar(20) NOT NULL DEFAULT 'any_sign' COMMENT '审批方式：countersign|any_sign',
  `approver_type` varchar(30) NOT NULL DEFAULT 'admin' COMMENT '审批人类型',
  `approver_id` int unsigned NOT NULL DEFAULT 0 COMMENT '审批人管理员ID',
  `approver_name` varchar(100) NOT NULL DEFAULT '' COMMENT '审批人名称',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待审批 1已通过 2已拒绝 3已取消 4已转交',
  `comment` varchar(500) NOT NULL DEFAULT '' COMMENT '审批意见/备注',
  `action_time` int unsigned NOT NULL DEFAULT 0 COMMENT '操作时间',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant_approver_status` (`tenant_id`,`approver_id`,`status`),
  KEY `idx_instance_node` (`instance_id`,`node_id`),
  KEY `idx_instance_status` (`instance_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流-审批任务';

CREATE TABLE IF NOT EXISTS `fa_wf_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `instance_id` int unsigned NOT NULL DEFAULT 0 COMMENT '实例ID',
  `node_id` int unsigned NOT NULL DEFAULT 0 COMMENT '节点ID',
  `task_id` int unsigned NOT NULL DEFAULT 0 COMMENT '任务ID',
  `action` varchar(30) NOT NULL DEFAULT '' COMMENT '动作：start|approve|reject|transfer|withdraw|skip|callback_ok|callback_fail',
  `actor_id` int unsigned NOT NULL DEFAULT 0 COMMENT '操作人ID',
  `actor_name` varchar(100) NOT NULL DEFAULT '' COMMENT '操作人名称',
  `to_approver_id` int unsigned NOT NULL DEFAULT 0 COMMENT '转交目标审批人ID',
  `to_approver_name` varchar(100) NOT NULL DEFAULT '' COMMENT '转交目标审批人名称',
  `comment` varchar(500) NOT NULL DEFAULT '' COMMENT '备注/意见',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_instance` (`instance_id`),
  KEY `idx_tenant_time` (`tenant_id`,`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作流-操作日志';
