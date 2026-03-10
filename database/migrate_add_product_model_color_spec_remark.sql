-- 产品型号表增加：颜色、规格、备注（仅用于已有旧表时增量执行）
-- 新安装请使用 migrate_add_mes_tables.sql，其 CREATE TABLE 已含本三列
-- 若表前缀不是 fa_，请将 fa_mes_product_model 改为 你的前缀+mes_product_model

ALTER TABLE `fa_mes_product_model`
  ADD COLUMN `color` varchar(100) NOT NULL DEFAULT '' COMMENT '颜色' AFTER `model_code`,
  ADD COLUMN `specification` varchar(200) NOT NULL DEFAULT '' COMMENT '规格' AFTER `color`,
  ADD COLUMN `remark` text COMMENT '备注' AFTER `specification`;
