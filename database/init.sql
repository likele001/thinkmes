-- thinkmes 仿 FastAdmin 初始化 SQL
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

-- 默认菜单/规则（供侧栏显示）
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(1, 'admin/index/index', '首页', 1, 1, 1, 0, 'fas fa-tachometer-alt', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 'admin/admin/index', '管理员管理', 1, 1, 1, 0, 'fas fa-user-shield', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, 'admin/role/index', '角色管理', 1, 1, 1, 0, 'fas fa-users-cog', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, 'admin/auth_rule/index', '权限规则', 1, 1, 1, 0, 'fas fa-sitemap', 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, 'admin/config/index', '系统配置', 1, 1, 1, 0, 'fas fa-cog', 40, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(6, 'admin/log/index', '操作日志', 1, 1, 1, 0, 'fas fa-history', 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, 'admin/addon/index', '插件管理', 1, 1, 1, 0, 'fas fa-plug', 60, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 'admin/app_center/index', '应用中心', 1, 1, 1, 0, 'fas fa-th-large', 65, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
