-- 方案 B：物料流水 / 产品流水 两个入口
-- 1. 将原「库存流水」菜单改为「物料流水」
UPDATE `fa_auth_rule` SET `title` = '物料流水' WHERE `name` = 'mes/stock/log';

-- 2. 新增「产品流水」菜单（挂在库存管理下，sort=4）
INSERT INTO `fa_auth_rule` (`pid`, `name`, `title`, `type`, `status`, `sort`, `create_time`, `update_time`)
SELECT `id`, 'mes/stock/product_log', '产品流水', 1, 1, 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM `fa_auth_rule` WHERE `name` = 'mes/stock' LIMIT 1;
