-- 客服管理后台菜单和权限（最终修正版）

-- 插入顶级菜单
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`)
VALUES
(0, 'customer_service', '客服管理', 'fa fa-headset', 1, 1, 1, 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取刚插入的菜单ID
SET @customer_service_id = LAST_INSERT_ID();

-- 插入子菜单
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`)
VALUES
(@customer_service_id, 'customer_service/index', '客服首页', 'fa fa-dashboard', 1, 1, 1, 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@customer_service_id, 'customer_service/sessions', '会话管理', 'fa fa-comments', 1, 1, 1, 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@customer_service_id, 'customer_service/tickets', '工单管理', 'fa fa-ticket', 1, 1, 1, 80, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@customer_service_id, 'customer_service/knowledge', '知识库管理', 'fa fa-book', 1, 1, 1, 70, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@customer_service_id, 'customer_service/faq', 'FAQ管理', 'fa fa-question-circle', 1, 1, 1, 60, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@customer_service_id, 'customer_service/categories', '分类管理', 'fa fa-folder', 1, 1, 1, 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@customer_service_id, 'customer_service/ai_history', 'AI对话历史', 'fa fa-robot', 1, 1, 1, 40, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@customer_service_id, 'customer_service/config', '系统配置', 'fa fa-cog', 1, 1, 1, 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入操作权限（不显示在菜单中，ismenu=0）
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`)
VALUES
-- 工单相关权限
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/tickets' LIMIT 1), 'customer_service/get_ticket_list', '查看工单列表', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/tickets' LIMIT 1), 'customer_service/ticket_detail', '查看工单详情', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/tickets' LIMIT 1), 'customer_service/reply_ticket', '回复工单', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/tickets' LIMIT 1), 'customer_service/update_ticket_status', '更新工单状态', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 知识库相关权限
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/knowledge' LIMIT 1), 'customer_service/get_article_list', '查看文章列表', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/knowledge' LIMIT 1), 'customer_service/article_edit', '编辑文章', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/knowledge' LIMIT 1), 'customer_service/save_article', '保存文章', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/knowledge' LIMIT 1), 'customer_service/delete_article', '删除文章', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- FAQ相关权限
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/faq' LIMIT 1), 'customer_service/get_faq_list', '查看FAQ列表', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/faq' LIMIT 1), 'customer_service/save_faq', '保存FAQ', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/faq' LIMIT 1), 'customer_service/delete_faq', '删除FAQ', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 分类相关权限
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/categories' LIMIT 1), 'customer_service/get_category_list', '查看分类列表', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/categories' LIMIT 1), 'customer_service/save_category', '保存分类', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/categories' LIMIT 1), 'customer_service/delete_category', '删除分类', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 会话相关权限
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/sessions' LIMIT 1), 'customer_service/get_session_list', '查看会话列表', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/sessions' LIMIT 1), 'customer_service/session_detail', '查看会话详情', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/sessions' LIMIT 1), 'customer_service/get_session_messages', '获取会话消息', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- AI对话历史相关权限
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/ai_history' LIMIT 1), 'customer_service/get_ai_history_list', '查看AI对话历史', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- 系统配置相关权限
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/config' LIMIT 1), 'customer_service/get_config', '查看配置', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
((SELECT id FROM `fa_auth_rule` WHERE `name`='customer_service/config' LIMIT 1), 'customer_service/save_config', '保存配置', '', 1, 0, 1, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
