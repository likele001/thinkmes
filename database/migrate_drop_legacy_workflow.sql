-- 旧工作流链路下线脚本（先备份后删除）
-- 说明：
-- 1) 本脚本仅处理 legacy fa_workflow* 表，不处理新版 fa_wf_* 表
-- 2) 备份表命名：<原表名>_legacy_backup
-- 3) 已兼容“部分旧表不存在”的环境，可重复执行

SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

DROP TEMPORARY TABLE IF EXISTS tmp_legacy_workflow_tables;
CREATE TEMPORARY TABLE tmp_legacy_workflow_tables (
    table_name VARCHAR(128) PRIMARY KEY
);

INSERT INTO tmp_legacy_workflow_tables (table_name) VALUES
('fa_workflow'),
('fa_workflow_module'),
('fa_workflow_definition'),
('fa_workflow_node'),
('fa_workflow_node_approver'),
('fa_workflow_edge'),
('fa_workflow_state'),
('fa_workflow_transition'),
('fa_workflow_instance'),
('fa_workflow_approver'),
('fa_workflow_approval'),
('fa_workflow_approval_record');

DROP PROCEDURE IF EXISTS migrate_drop_legacy_workflow_tables;
DELIMITER $$
CREATE PROCEDURE migrate_drop_legacy_workflow_tables()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE src_table VARCHAR(128);
    DECLARE bak_table VARCHAR(160);
    DECLARE exists_count INT DEFAULT 0;
    DECLARE cur CURSOR FOR
        SELECT table_name FROM tmp_legacy_workflow_tables;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO src_table;
        IF done = 1 THEN
            LEAVE read_loop;
        END IF;

        SELECT COUNT(*) INTO exists_count
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = src_table;

        IF exists_count > 0 THEN
            SET bak_table = CONCAT(src_table, '_legacy_backup');

            SET @sql_create = CONCAT('CREATE TABLE IF NOT EXISTS `', bak_table, '` LIKE `', src_table, '`');
            PREPARE stmt_create FROM @sql_create;
            EXECUTE stmt_create;
            DEALLOCATE PREPARE stmt_create;

            SET @sql_truncate = CONCAT('TRUNCATE TABLE `', bak_table, '`');
            PREPARE stmt_truncate FROM @sql_truncate;
            EXECUTE stmt_truncate;
            DEALLOCATE PREPARE stmt_truncate;

            SET @sql_copy = CONCAT('INSERT INTO `', bak_table, '` SELECT * FROM `', src_table, '`');
            PREPARE stmt_copy FROM @sql_copy;
            EXECUTE stmt_copy;
            DEALLOCATE PREPARE stmt_copy;

            SET @sql_drop = CONCAT('DROP TABLE IF EXISTS `', src_table, '`');
            PREPARE stmt_drop FROM @sql_drop;
            EXECUTE stmt_drop;
            DEALLOCATE PREPARE stmt_drop;
        END IF;
    END LOOP;
    CLOSE cur;
END $$
DELIMITER ;

CALL migrate_drop_legacy_workflow_tables();
DROP PROCEDURE IF EXISTS migrate_drop_legacy_workflow_tables;
DROP TEMPORARY TABLE IF EXISTS tmp_legacy_workflow_tables;

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;

SELECT 'legacy workflow tables backup + drop done' AS result;
