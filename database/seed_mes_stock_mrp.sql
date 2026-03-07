-- 库存预警与MRP菜单（挂到 MES 下）
SET @mes_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes' LIMIT 1);
SET @stock_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/stock' LIMIT 1);

-- 库存预警（挂在 stock 下，若 mes/stock 不存在则挂到 mes）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/stock/alert', '库存预警', 1, 1, 1, COALESCE(@stock_pid, @mes_pid), 'fa fa-exclamation-triangle', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`);

-- MRP 缺料计算（一级挂在 mes 下）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/mrp', '缺料计算(MRP)', 1, 1, 1, @mes_pid, 'fa fa-calculator', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`);

SET @mrp_pid = (SELECT id FROM fa_auth_rule WHERE name = 'mes/mrp' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('mes/mrp/index', '查看', 2, 0, 1, @mrp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @mrp_pid;
