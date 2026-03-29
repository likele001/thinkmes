INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant', '餐饮SaaS', 1, 1, 1, 0, 'fa fa-utensils', 160, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @restaurant_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/store', '门店管理', 1, 1, 1, @restaurant_pid, 'fa fa-store', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @store_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/store' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/store/index', '门店列表', 2, 0, 1, @store_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/store/add', '添加门店', 2, 0, 1, @store_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/store/edit', '编辑门店', 2, 0, 1, @store_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/store/del', '删除门店', 2, 0, 1, @store_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @store_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/area', '区域管理', 1, 1, 1, @restaurant_pid, 'fa fa-layer-group', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/review', '评价管理', 1, 1, 1, @restaurant_pid, 'fa fa-star', 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @review_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/review' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/review/index', '评价列表', 2, 0, 1, @review_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review/sync', '同步评价', 2, 0, 1, @review_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review/autoReply', '自动回复回写', 2, 0, 1, @review_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review/stats', '差评统计', 2, 0, 1, @review_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @review_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/review_template', '回复模板', 1, 1, 1, @restaurant_pid, 'fa fa-reply', 17, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @reviewtpl_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/review_template' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/review_template/index', '模板列表', 2, 0, 1, @reviewtpl_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review_template/add', '添加模板', 2, 0, 1, @reviewtpl_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review_template/edit', '编辑模板', 2, 0, 1, @reviewtpl_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review_template/del', '删除模板', 2, 0, 1, @reviewtpl_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @reviewtpl_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/review_keyword', '关键词库', 1, 1, 1, @restaurant_pid, 'fa fa-tags', 18, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @reviewkw_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/review_keyword' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/review_keyword/index', '列表', 2, 0, 1, @reviewkw_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review_keyword/add', '添加', 2, 0, 1, @reviewkw_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review_keyword/edit', '编辑', 2, 0, 1, @reviewkw_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review_keyword/del', '删除', 2, 0, 1, @reviewkw_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @reviewkw_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/review_alert', '差评告警', 1, 1, 1, @restaurant_pid, 'fa fa-exclamation-triangle', 19, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @reviewalert_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/review_alert' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/review_alert/index', '列表', 2, 0, 1, @reviewalert_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/review_alert/markDone', '处理', 2, 0, 1, @reviewalert_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @reviewalert_pid;

SET @area_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/area' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/area/index', '区域列表', 2, 0, 1, @area_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/area/add', '添加区域', 2, 0, 1, @area_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/area/edit', '编辑区域', 2, 0, 1, @area_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/area/del', '删除区域', 2, 0, 1, @area_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @area_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/table', '桌台管理', 1, 1, 1, @restaurant_pid, 'fa fa-chair', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @table_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/table' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/table/index', '桌台列表', 2, 0, 1, @table_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/table/add', '添加桌台', 2, 0, 1, @table_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/table/edit', '编辑桌台', 2, 0, 1, @table_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/table/del', '删除桌台', 2, 0, 1, @table_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/table/resetToken', '重置桌码Token', 2, 0, 1, @table_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @table_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/table/wxacode', '生成小程序码', 2, 0, 1, @table_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @table_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/category', '菜品分类', 1, 1, 1, @restaurant_pid, 'fa fa-list', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @category_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/category' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/category/index', '分类列表', 2, 0, 1, @category_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/category/add', '添加分类', 2, 0, 1, @category_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/category/edit', '编辑分类', 2, 0, 1, @category_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/category/del', '删除分类', 2, 0, 1, @category_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @category_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/item', '菜品管理', 1, 1, 1, @restaurant_pid, 'fa fa-hamburger', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @item_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/item' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/item/index', '菜品列表', 2, 0, 1, @item_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/item/add', '添加菜品', 2, 0, 1, @item_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/item/edit', '编辑菜品', 2, 0, 1, @item_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/item/del', '删除菜品', 2, 0, 1, @item_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @item_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/order', '餐饮订单', 1, 1, 1, @restaurant_pid, 'fa fa-receipt', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @order_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/order' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/order/index', '订单列表', 2, 0, 1, @order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/order/detail', '订单详情', 2, 0, 1, @order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/order/updateStatus', '更新订单状态', 2, 0, 1, @order_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @order_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/kds', '后厨KDS', 1, 1, 1, @restaurant_pid, 'fa fa-bell', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @kds_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/kds' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/kds/index', 'KDS看板', 2, 0, 1, @kds_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/kds/items', '订单明细', 2, 0, 1, @kds_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/kds/call', '叫号', 2, 0, 1, @kds_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/kds/setSoldOut', '售罄联动', 2, 0, 1, @kds_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @kds_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/option_group', '规格/口味分组', 1, 1, 1, @restaurant_pid, 'fa fa-sliders-h', 8, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @og_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/option_group' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/option_group/index', '分组列表', 2, 0, 1, @og_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/option_group/add', '添加分组', 2, 0, 1, @og_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/option_group/edit', '编辑分组', 2, 0, 1, @og_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/option_group/del', '删除分组', 2, 0, 1, @og_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @og_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/option', '规格/口味选项', 1, 1, 1, @restaurant_pid, 'fa fa-ellipsis-h', 9, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @opt_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/option' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/option/index', '选项列表', 2, 0, 1, @opt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/option/add', '添加选项', 2, 0, 1, @opt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/option/edit', '编辑选项', 2, 0, 1, @opt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/option/del', '删除选项', 2, 0, 1, @opt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @opt_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/combo', '套餐管理', 1, 1, 1, @restaurant_pid, 'fa fa-box', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @combo_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/combo' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/combo/index', '套餐列表', 2, 0, 1, @combo_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/combo/add', '添加套餐', 2, 0, 1, @combo_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/combo/edit', '编辑套餐', 2, 0, 1, @combo_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/combo/del', '删除套餐', 2, 0, 1, @combo_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @combo_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/ai', '餐饮AI', 1, 1, 1, @restaurant_pid, 'fa fa-robot', 11, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @ai_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/ai' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/ai/index', 'AI面板', 2, 0, 1, @ai_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @ai_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/ai_config', 'AI配置', 1, 1, 1, @restaurant_pid, 'fa fa-cog', 13, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @aicfg_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/ai_config' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/ai_config/index', '配置列表', 2, 0, 1, @aicfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/ai_config/add', '添加配置', 2, 0, 1, @aicfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/ai_config/edit', '编辑配置', 2, 0, 1, @aicfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/ai_config/del', '删除配置', 2, 0, 1, @aicfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/ai_config/test', '测试连接', 2, 0, 1, @aicfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @aicfg_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/ai_report', 'AI经营日报', 1, 1, 1, @restaurant_pid, 'fa fa-file-alt', 14, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @aireport_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/ai_report' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/ai_report/index', '日报列表', 2, 0, 1, @aireport_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/ai_report/generate', '生成日报', 2, 0, 1, @aireport_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @aireport_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/wxa_setting', '小程序配置', 1, 1, 1, @restaurant_pid, 'fa fa-mobile-alt', 13, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @wxa_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/wxa_setting' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/wxa_setting/index', '配置保存', 2, 0, 1, @wxa_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @wxa_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/openclaw_setting', 'OpenClaw设置', 1, 1, 1, @restaurant_pid, 'fa fa-plug', 15, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @oc_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/openclaw_setting' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/openclaw_setting/index', '保存配置', 2, 0, 1, @oc_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @oc_pid;

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('restaurant/report', '报表统计', 1, 1, 1, @restaurant_pid, 'fa fa-chart-line', 12, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `pid` = @restaurant_pid,
    `ismenu` = VALUES(`ismenu`),
    `status` = VALUES(`status`);

SET @report_pid = (SELECT id FROM fa_auth_rule WHERE name = 'restaurant/report' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('restaurant/report/index', '报表页面', 2, 0, 1, @report_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restaurant/report/overview', '报表数据', 2, 0, 1, @report_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `pid` = @report_pid;
