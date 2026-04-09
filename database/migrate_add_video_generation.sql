-- Prompt 应用 - 新增视频生成功能
-- 为 prompt_generation 表添加视频相关字段

ALTER TABLE `fa_prompt_generation`
ADD COLUMN `video_task_id` varchar(100) DEFAULT '' COMMENT '视频生成任务ID(智谱AI)' AFTER `output_text`,
ADD COLUMN `video_status` varchar(20) DEFAULT '' COMMENT '视频任务状态：PROCESSING/SUCCESS/FAIL/EMPTY',
ADD COLUMN `video_url` text COMMENT '视频URL' AFTER `video_status`,
ADD COLUMN `video_duration` int DEFAULT 0 COMMENT '视频时长(秒)' AFTER `video_url`,
ADD COLUMN `video_error_msg` varchar(500) DEFAULT '' COMMENT '视频生成失败原因' AFTER `video_duration`,
ADD INDEX `idx_video_task` (`video_task_id`);
