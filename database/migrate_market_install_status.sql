-- 添加状态字段到插件安装记录表
ALTER TABLE `fa_market_plugin_install` ADD COLUMN `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用' AFTER `tenant_id`;
