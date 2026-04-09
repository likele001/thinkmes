-- Prompt 应用 - AI 服务配置补丁
-- 为 prompt_ai_config 增加图像/视频模型字段（旧库增量执行）
-- 若报 Duplicate column name 说明已存在，可忽略

ALTER TABLE `fa_prompt_ai_config` ADD COLUMN `image_model` varchar(100) NOT NULL DEFAULT '' COMMENT '图片模型名称（如 cogview-3-flash）' AFTER `model`;
ALTER TABLE `fa_prompt_ai_config` ADD COLUMN `video_model` varchar(100) NOT NULL DEFAULT '' COMMENT '视频模型名称（如 cogvideox-flash）' AFTER `image_model`;
