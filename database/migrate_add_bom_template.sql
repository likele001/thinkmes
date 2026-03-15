-- BOM 增加“通用物料模板”能力
-- bom_type: 0=产品BOM（按产品/型号） 1=通用BOM模板（可跨产品复用）
-- 说明：模板 BOM 建议 product_id=0 且 model_id=0

ALTER TABLE `fa_mes_bom`
ADD COLUMN `bom_type` tinyint NOT NULL DEFAULT 0 COMMENT 'BOM类型：0产品BOM 1通用模板' AFTER `model_id`;

ALTER TABLE `fa_mes_bom` ADD KEY `idx_bom_type` (`tenant_id`,`bom_type`);

