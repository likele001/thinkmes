-- 客服管理后台菜单与权限（一级菜单 customer_service）
-- 表前缀 fa_

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('customer_service', '客服管理', 1, 1, 1, 0, 'fa fa-headset', 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`);

SET @cs_pid = (SELECT id FROM fa_auth_rule WHERE name = 'customer_service' LIMIT 1);

-- 插入子菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('customer_service/index', '客服首页', 1, 1, 1, @cs_pid, 'fa fa-dashboard', 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/sessions', '会话管理', 1, 1, 1, @cs_pid, 'fa fa-comments', 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/tickets', '工单管理', 1, 1, 1, @cs_pid, 'fa fa-ticket', 80, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/knowledge', '知识库管理', 1, 1, 1, @cs_pid, 'fa fa-book', 70, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/faq', 'FAQ管理', 1, 1, 1, @cs_pid, 'fa fa-question-circle', 60, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/categories', '分类管理', 1, 1, 1, @cs_pid, 'fa fa-folder', 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/ai_history', 'AI对话历史', 1, 1, 1, @cs_pid, 'fa fa-robot', 40, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/config', '系统配置', 1, 1, 1, @cs_pid, 'fa fa-cog', 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @cs_pid;

-- 工单管理子权限
SET @tickets_pid = (SELECT id FROM fa_auth_rule WHERE name = 'customer_service/tickets' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('customer_service/get_ticket_list', '查看列表', 2, 0, 1, @tickets_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/ticket_detail', '查看详情', 2, 0, 1, @tickets_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/reply_ticket', '回复工单', 2, 0, 1, @tickets_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/update_ticket_status', '更新状态', 2, 0, 1, @tickets_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @tickets_pid;

-- 知识库管理子权限
SET @knowledge_pid = (SELECT id FROM fa_auth_rule WHERE name = 'customer_service/knowledge' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('customer_service/get_article_list', '查看列表', 2, 0, 1, @knowledge_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/article_edit', '编辑文章', 2, 0, 1, @knowledge_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/save_article', '保存文章', 2, 0, 1, @knowledge_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/delete_article', '删除文章', 2, 0, 1, @knowledge_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @knowledge_pid;

-- FAQ管理子权限
SET @faq_pid = (SELECT id FROM fa_auth_rule WHERE name = 'customer_service/faq' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('customer_service/get_faq_list', '查看列表', 2, 0, 1, @faq_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/save_faq', '保存FAQ', 2, 0, 1, @faq_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/delete_faq', '删除FAQ', 2, 0, 1, @faq_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @faq_pid;

-- 分类管理子权限
SET @categories_pid = (SELECT id FROM fa_auth_rule WHERE name = 'customer_service/categories' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('customer_service/get_category_list', '查看列表', 2, 0, 1, @categories_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/save_category', '保存分类', 2, 0, 1, @categories_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/delete_category', '删除分类', 2, 0, 1, @categories_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @categories_pid;

-- 会话管理子权限
SET @sessions_pid = (SELECT id FROM fa_auth_rule WHERE name = 'customer_service/sessions' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('customer_service/get_session_list', '查看列表', 2, 0, 1, @sessions_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/session_detail', '查看详情', 2, 0, 1, @sessions_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/get_session_messages', '获取消息', 2, 0, 1, @sessions_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @sessions_pid;

-- AI对话历史子权限
SET @ai_history_pid = (SELECT id FROM fa_auth_rule WHERE name = 'customer_service/ai_history' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('customer_service/get_ai_history_list', '查看列表', 2, 0, 1, @ai_history_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @ai_history_pid;

-- 系统配置子权限
SET @config_pid = (SELECT id FROM fa_auth_rule WHERE name = 'customer_service/config' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('customer_service/get_config', '查看配置', 2, 0, 1, @config_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('customer_service/save_config', '保存配置', 2, 0, 1, @config_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @config_pid;
