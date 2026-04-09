-- Prompt 应用 - 文生图/文生视频
-- 为 prompt_generation 表添加图片/视频任务字段（旧库增量执行）
-- 若报 Duplicate column name / Duplicate key name 说明已存在，可忽略

ALTER TABLE `fa_prompt_generation` ADD COLUMN `image_task_id` varchar(100) NOT NULL DEFAULT '' COMMENT '图片生成任务ID(智谱AI)' AFTER `output_text`;
ALTER TABLE `fa_prompt_generation` ADD COLUMN `image_status` varchar(20) NOT NULL DEFAULT '' COMMENT '图片任务状态：PROCESSING/SUCCESS/FAIL/EMPTY' AFTER `image_task_id`;
ALTER TABLE `fa_prompt_generation` ADD COLUMN `image_url` text COMMENT '图片URL' AFTER `image_status`;
ALTER TABLE `fa_prompt_generation` ADD COLUMN `image_size` varchar(30) NOT NULL DEFAULT '' COMMENT '图片尺寸' AFTER `image_url`;
ALTER TABLE `fa_prompt_generation` ADD COLUMN `image_error_msg` varchar(500) NOT NULL DEFAULT '' COMMENT '图片生成失败原因' AFTER `image_size`;

ALTER TABLE `fa_prompt_generation` ADD COLUMN `video_task_id` varchar(100) NOT NULL DEFAULT '' COMMENT '视频生成任务ID(智谱AI)' AFTER `image_error_msg`;
ALTER TABLE `fa_prompt_generation` ADD COLUMN `video_status` varchar(20) NOT NULL DEFAULT '' COMMENT '视频任务状态：PROCESSING/SUCCESS/FAIL/EMPTY' AFTER `video_task_id`;
ALTER TABLE `fa_prompt_generation` ADD COLUMN `video_url` text COMMENT '视频URL' AFTER `video_status`;
ALTER TABLE `fa_prompt_generation` ADD COLUMN `video_duration` int NOT NULL DEFAULT 0 COMMENT '视频时长(秒)' AFTER `video_url`;
ALTER TABLE `fa_prompt_generation` ADD COLUMN `video_error_msg` varchar(500) NOT NULL DEFAULT '' COMMENT '视频生成失败原因' AFTER `video_duration`;

ALTER TABLE `fa_prompt_generation` ADD INDEX `idx_image_task` (`image_task_id`);
ALTER TABLE `fa_prompt_generation` ADD INDEX `idx_video_task` (`video_task_id`);
