-- 产品表、产品型号表增加默认 BOM 字段，便于针对不同产品/型号选择默认 BOM
-- 执行前请确认 fa_mes_product、fa_mes_product_model 表已存在

-- 产品表：该产品的默认 BOM（通用或未指定型号时使用）
ALTER TABLE `fa_mes_product`
ADD COLUMN `default_bom_id` int unsigned NOT NULL DEFAULT 0 COMMENT '默认BOM ID（0表示未设置）' AFTER `status`;
ALTER TABLE `fa_mes_product` ADD KEY `idx_default_bom` (`default_bom_id`);

-- 产品型号表：该型号的默认 BOM（优先于产品的默认 BOM）
ALTER TABLE `fa_mes_product_model`
ADD COLUMN `default_bom_id` int unsigned NOT NULL DEFAULT 0 COMMENT '默认BOM ID（0表示未设置）' AFTER `status`;
ALTER TABLE `fa_mes_product_model` ADD KEY `idx_default_bom` (`default_bom_id`);
