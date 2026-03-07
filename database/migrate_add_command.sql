-- 在线命令/CRUD 生成记录表（仿 FastAdmin 在线命令列表）
-- 执行 php think crud_gen 或后台「立即执行」时写入，用于列表展示、再次执行、详情
CREATE TABLE IF NOT EXISTS `fa_command` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(20) NOT NULL DEFAULT 'crud' COMMENT '类型：crud=一键生成CRUD,menu=一键生成菜单',
  `command` varchar(500) NOT NULL DEFAULT '' COMMENT '生成命令行/概要描述',
  `params` text COMMENT '请求参数 JSON',
  `content` longtext COMMENT '执行输出/返回结果',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：1成功 0失败',
  `executetime` int unsigned NOT NULL DEFAULT 0 COMMENT '执行时间戳',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_executetime` (`executetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='在线命令执行记录';
