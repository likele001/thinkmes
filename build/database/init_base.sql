-- thinkmes 仿 FastAdmin 初始化 SQL（基础版：不预置租户套餐，由部署方在后台自行添加）
-- 字符集 utf8mb4，排序 utf8mb4_unicode_ci，引擎 InnoDB
-- 数据库名/用户: thinkmes

-- 管理员表（含租户、父级、数据权限）
DROP TABLE IF EXISTS `fa_admin`;
CREATE TABLE `fa_admin` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID，0=平台超管',
  `pid` int unsigned NOT NULL DEFAULT 0 COMMENT '父级管理员ID',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '登录账号',
  `password` varchar(100) NOT NULL DEFAULT '' COMMENT '密码(BCrypt加密)',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `role_ids` varchar(255) NOT NULL DEFAULT '' COMMENT '角色ID，逗号分隔',
  `data_scope` tinyint NOT NULL DEFAULT 1 COMMENT '数据权限：1个人 2子级 3全部',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `login_time` int DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tenant_username` (`tenant_id`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- 角色表
DROP TABLE IF EXISTS `fa_role`;
CREATE TABLE `fa_role` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '角色名称',
  `rules` text COMMENT '权限规则ID，逗号分隔',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色表';

-- 权限规则表（菜单+按钮+接口）
DROP TABLE IF EXISTS `fa_auth_rule`;
CREATE TABLE `fa_auth_rule` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '规则ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则标识：控制器/方法',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
  `type` tinyint NOT NULL DEFAULT 1 COMMENT '类型：1菜单 2按钮 3接口',
  `ismenu` tinyint NOT NULL DEFAULT 1 COMMENT '是否菜单：1显示 0隐藏',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `pid` int NOT NULL DEFAULT 0 COMMENT '父级ID',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '菜单图标',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序值',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限规则表';

-- 系统配置表
DROP TABLE IF EXISTS `fa_config`;
CREATE TABLE `fa_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '配置键名',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '配置项标题（中文）',
  `value` text COMMENT '配置值',
  `group` varchar(30) NOT NULL DEFAULT 'base' COMMENT '配置分组：base/upload/safe',
  `sort` int DEFAULT 0 COMMENT '排序',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';

-- 验证码等安全配置（后台登录验证码、前端登录/注册验证码方式，基础版即含）
INSERT INTO `fa_config` (`name`, `title`, `value`, `group`, `sort`, `create_time`, `update_time`) VALUES
('login_captcha', '登录验证码', '1', 'safe', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('front_captcha_mode', '前端验证码方式', 'image', 'safe', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `value` = VALUES(`value`), `sort` = VALUES(`sort`);

-- 文件上传表
DROP TABLE IF EXISTS `fa_upload`;
CREATE TABLE `fa_upload` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `admin_id` int NOT NULL DEFAULT 0 COMMENT '上传管理员ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件访问地址',
  `size` int NOT NULL DEFAULT 0 COMMENT '文件大小(字节)',
  `mime_type` varchar(128) DEFAULT '' COMMENT '文件MIME类型',
  `storage` varchar(20) DEFAULT 'local' COMMENT '存储方式：local/oss',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '上传时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文件上传表';

-- 操作日志表
DROP TABLE IF EXISTS `fa_log`;
CREATE TABLE `fa_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `admin_id` int NOT NULL DEFAULT 0 COMMENT '操作管理员ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '操作类型：login/add/edit/del',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '操作内容',
  `url` varchar(255) DEFAULT '' COMMENT '请求地址',
  `ip` varchar(50) DEFAULT '' COMMENT '操作IP',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant_time` (`tenant_id`,`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';

-- 租户表（基础版也建表，避免 Auth/TenantResolve 等查表报错；无多租户时可为空）
DROP TABLE IF EXISTS `fa_tenant`;
CREATE TABLE `fa_tenant` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '租户ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '租户名称',
  `domain` varchar(255) NOT NULL DEFAULT '' COMMENT '绑定域名，多个逗号分隔',
  `package_id` int unsigned NOT NULL DEFAULT 0 COMMENT '套餐ID',
  `expire_time` int DEFAULT NULL COMMENT '到期时间 NULL=永久',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_domain` (`domain`(64)),
  KEY `idx_status_expire` (`status`,`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户表';

-- C端用户表（基础版也建表，避免首页统计等代码查表报错；无会员功能时可为空）
DROP TABLE IF EXISTS `fa_user`;
CREATE TABLE `fa_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '登录名',
  `password` varchar(100) NOT NULL DEFAULT '' COMMENT '密码',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `level` int NOT NULL DEFAULT 0 COMMENT '等级',
  `score` int NOT NULL DEFAULT 0 COMMENT '积分',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `login_time` int DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant_username` (`tenant_id`,`username`),
  KEY `idx_mobile` (`tenant_id`,`mobile`),
  KEY `idx_email` (`tenant_id`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='C端用户表';

-- 用户小程序绑定表（基础版也建表，与 fa_user 配套）
DROP TABLE IF EXISTS `fa_user_miniapp`;
CREATE TABLE `fa_user_miniapp` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL COMMENT '租户ID',
  `user_id` int unsigned NOT NULL COMMENT '用户ID',
  `type` varchar(20) NOT NULL DEFAULT 'wechat' COMMENT '小程序类型：wechat 等',
  `app_id` varchar(64) NOT NULL DEFAULT '' COMMENT 'AppID',
  `openid` varchar(64) NOT NULL DEFAULT '' COMMENT 'OpenID',
  `unionid` varchar(64) NOT NULL DEFAULT '' COMMENT 'UnionID',
  `session_key` varchar(128) NOT NULL DEFAULT '' COMMENT '最近一次 code2session 的 session_key',
  `last_login_time` int NOT NULL DEFAULT 0 COMMENT '最近登录时间',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tenant_type_openid` (`tenant_id`,`type`,`openid`),
  KEY `idx_tenant_user_type` (`tenant_id`,`user_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户小程序绑定表';

-- 初始化超级管理员（tenant_id=0 平台超管，账号 admin 密码 123456，BCrypt）
INSERT INTO `fa_admin` (`id`, `tenant_id`, `pid`, `username`, `password`, `salt`, `nickname`, `role_ids`, `data_scope`, `status`, `create_time`, `update_time`)
VALUES (1, 0, 0, 'admin', '$2y$10$FgTjiHSfat5J4izn09x4u.nZ0d/aiDm0dWXN7YEZBteofm7D6M2Ma', 'fast', '超级管理员', '1', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 初始化默认角色
INSERT INTO `fa_role` (`id`, `name`, `rules`, `status`, `create_time`, `update_time`)
VALUES (1, '超级管理员', '*', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 默认菜单/规则（供侧栏显示，带层级：首页 + 系统管理/扩展功能/租户与用户 三大分组）
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(1, 'admin/index/index', '首页', 1, 1, 1, 0, 'fas fa-tachometer-alt', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(9, 'admin/_sys', '系统管理', 1, 1, 1, 0, 'fas fa-cog', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(10, 'admin/_ext', '扩展功能', 1, 1, 1, 0, 'fas fa-puzzle-piece', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(11, 'admin/_tenant_user', '租户与用户', 1, 1, 1, 0, 'fas fa-users', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 'admin/admin/index', '管理员管理', 1, 1, 1, 9, 'fas fa-user-shield', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, 'admin/role/index', '角色管理', 1, 1, 1, 9, 'fas fa-users-cog', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, 'admin/auth_rule/index', '权限规则', 1, 1, 1, 9, 'fas fa-sitemap', 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, 'admin/config/index', '系统配置', 1, 1, 1, 9, 'fas fa-cog', 40, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(6, 'admin/log/index', '操作日志', 1, 1, 1, 9, 'fas fa-history', 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 'admin/addon/index', '插件管理', 1, 1, 1, 10, 'fas fa-plug', 60, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 'admin/app_center/index', '应用中心', 1, 1, 1, 10, 'fas fa-th-large', 65, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(12, 'admin/tenant/index', '租户管理', 1, 1, 1, 11, 'fas fa-building', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(13, 'admin/tenant/miniapp', '租户小程序', 1, 1, 1, 11, 'fas fa-mobile-alt', 15, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(14, 'admin/tenant_package/index', '租户套餐', 1, 1, 1, 11, 'fas fa-box', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(15, 'admin/tenant_order/index', '租户订单', 1, 1, 1, 11, 'fas fa-receipt', 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(16, 'admin/member/index', '会员管理', 1, 1, 1, 11, 'fas fa-user', 40, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 租户套餐表（安装向导一步到位，无需再执行 migrate）
DROP TABLE IF EXISTS `fa_tenant_package`;
CREATE TABLE `fa_tenant_package` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '套餐ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '套餐名称',
  `max_admin` int NOT NULL DEFAULT 10 COMMENT '最大管理员数',
  `max_user` int NOT NULL DEFAULT 1000 COMMENT '最大C端用户数',
  `expire_days` int DEFAULT NULL COMMENT '默认有效天数 NULL=永久',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户套餐表';

-- 租户小程序配置表
DROP TABLE IF EXISTS `fa_tenant_miniapp`;
CREATE TABLE `fa_tenant_miniapp` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL COMMENT '租户ID',
  `type` varchar(20) NOT NULL DEFAULT 'wechat' COMMENT '小程序类型：wechat 等',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '小程序名称',
  `app_id` varchar(64) NOT NULL DEFAULT '' COMMENT 'AppID',
  `app_secret` varchar(100) NOT NULL DEFAULT '' COMMENT 'AppSecret',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tenant_type` (`tenant_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户小程序配置表';

-- 套餐功能配置表
DROP TABLE IF EXISTS `fa_tenant_package_feature`;
CREATE TABLE `fa_tenant_package_feature` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `package_id` int unsigned NOT NULL COMMENT '套餐ID',
  `feature_code` varchar(50) NOT NULL COMMENT '功能代码',
  `feature_name` varchar(50) NOT NULL COMMENT '功能名称',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_package_feature` (`package_id`,`feature_code`),
  KEY `idx_package` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套餐功能配置表';

-- 租户订单表
DROP TABLE IF EXISTS `fa_tenant_order`;
CREATE TABLE `fa_tenant_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `tenant_id` int unsigned NOT NULL COMMENT '租户ID',
  `order_no` varchar(32) NOT NULL COMMENT '订单号',
  `package_id` int unsigned NOT NULL COMMENT '套餐ID',
  `type` tinyint NOT NULL COMMENT '类型：1购买 2续费 3升级',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待支付 1已支付 2已取消 3已退款',
  `pay_method` varchar(20) DEFAULT '' COMMENT '支付方式：alipay/wechat/bank',
  `pay_time` int DEFAULT NULL COMMENT '支付时间',
  `expire_days` int DEFAULT NULL COMMENT '购买/续费天数',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_no` (`order_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户订单表';

-- 打印模板表（底座可选功能）
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='打印模板';

-- 租户套餐：基础版不预置数据，由部署方在后台「租户套餐」中自行添加

-- 插件表
DROP TABLE IF EXISTS `fa_addon`;
CREATE TABLE `fa_addon` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '插件ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '插件名称',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '插件标题',
  `intro` text COMMENT '插件介绍',
  `author` varchar(50) DEFAULT '' COMMENT '插件作者',
  `version` varchar(20) DEFAULT '' COMMENT '插件版本',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0禁用 1启用',
  `config` text COMMENT '插件配置JSON',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件表';

-- 自定义字段表
DROP TABLE IF EXISTS `fa_custom_field`;
CREATE TABLE `fa_custom_field` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '字段ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `table_name` varchar(50) NOT NULL DEFAULT '' COMMENT '关联表名',
  `field_name` varchar(50) NOT NULL DEFAULT '' COMMENT '字段名',
  `field_label` varchar(100) NOT NULL DEFAULT '' COMMENT '字段标签',
  `field_type` varchar(20) NOT NULL DEFAULT 'text' COMMENT '字段类型：text/number/date/select等',
  `field_options` text COMMENT '选项配置JSON（select/radio/checkbox）',
  `is_required` tinyint NOT NULL DEFAULT 0 COMMENT '是否必填：0否 1是',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_table` (`table_name`),
  KEY `idx_tenant_table` (`tenant_id`,`table_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自定义字段表';

-- 通知表
DROP TABLE IF EXISTS `fa_notification`;
CREATE TABLE `fa_notification` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '通知ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `admin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '接收管理员ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '通知标题',
  `content` text COMMENT '通知内容',
  `type` varchar(20) NOT NULL DEFAULT 'system' COMMENT '通知类型：system/task/order等',
  `is_read` tinyint NOT NULL DEFAULT 0 COMMENT '是否已读：0未读 1已读',
  `read_time` int DEFAULT NULL COMMENT '阅读时间',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_admin_read` (`admin_id`,`is_read`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通知表';

-- 短信配置表
DROP TABLE IF EXISTS `fa_sms_config`;
CREATE TABLE `fa_sms_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `provider` varchar(20) NOT NULL DEFAULT 'aliyun' COMMENT '服务商：aliyun/tencent等',
  `access_key` varchar(100) DEFAULT '' COMMENT 'AccessKey',
  `access_secret` varchar(100) DEFAULT '' COMMENT 'AccessSecret',
  `sign_name` varchar(50) DEFAULT '' COMMENT '短信签名',
  `template_code` varchar(50) DEFAULT '' COMMENT '模板代码',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='短信配置表';

-- 新工作流审批引擎（线性节点）
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

-- 底座菜单：文件管理→扩展功能(10)；租户/套餐/订单/用户→租户与用户(11)
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/attachment/index', '文件管理', 1, 1, 1, 10, 'fas fa-folder-open', 55, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/addon/index', '插件管理', 1, 1, 1, 10, 'fas fa-plug', 60, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/app_center/index', '应用中心', 1, 1, 1, 10, 'fas fa-th-large', 65, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/tenant/index', '租户管理', 1, 1, 1, 11, 'fas fa-building', 66, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/tenant_package/index', '租户套餐', 1, 1, 1, 11, 'fas fa-box', 67, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/tenant_package_feature/index', '套餐功能', 1, 1, 1, 11, 'fas fa-list', 68, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/tenant_order/index', '租户订单', 1, 1, 1, 11, 'fas fa-receipt', 69, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/member/index', '用户管理', 1, 1, 1, 11, 'fas fa-users', 70, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/crud_gen/index', 'CRUD生成', 1, 1, 1, 9, 'fas fa-code', 45, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/custom_field/index', '自定义字段', 1, 1, 1, 10, 'fas fa-edit', 70, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/notification/index', '消息通知', 1, 1, 1, 10, 'fas fa-bell', 75, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/sms_config/index', '短信配置', 1, 1, 1, 10, 'fas fa-sms', 80, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/definition/index', '工作流定义', 1, 1, 1, 10, 'fas fa-project-diagram', 85, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/instance/index', '工作流实例', 1, 1, 1, 10, 'fas fa-tasks', 86, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/module/index', '业务模块', 1, 1, 1, 10, 'fas fa-cubes', 87, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `pid` = VALUES(`pid`);
