-- Merge study_plans into plans (idempotent)
-- Target: MySQL / MariaDB

SET @db := DATABASE();

DROP PROCEDURE IF EXISTS sp_add_column_if_missing;
DELIMITER $$
CREATE PROCEDURE sp_add_column_if_missing(
    IN p_table VARCHAR(64),
    IN p_column VARCHAR(64),
    IN p_ddl TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = @db
          AND TABLE_NAME = p_table
          AND COLUMN_NAME = p_column
    ) THEN
        SET @sql := CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN ', p_ddl);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END $$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_add_index_if_missing;
DELIMITER $$
CREATE PROCEDURE sp_add_index_if_missing(
    IN p_table VARCHAR(64),
    IN p_index_name VARCHAR(64),
    IN p_index_ddl TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = @db
          AND TABLE_NAME = p_table
          AND INDEX_NAME = p_index_name
    ) THEN
        SET @sql := CONCAT('ALTER TABLE `', p_table, '` ADD ', p_index_ddl);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END $$
DELIMITER ;

-- 1) Ensure merged columns exist on plans
CALL sp_add_column_if_missing('plans', 'plan_type', "`plan_type` varchar(32) NOT NULL DEFAULT 'general'");
CALL sp_add_column_if_missing('plans', 'content', "`content` text NULL");
CALL sp_add_column_if_missing('plans', 'start_time', "`start_time` datetime NULL");
CALL sp_add_column_if_missing('plans', 'repeat_type', "`repeat_type` varchar(32) NOT NULL DEFAULT 'none'");
CALL sp_add_column_if_missing('plans', 'repeat_days', "`repeat_days` varchar(64) NULL");
CALL sp_add_column_if_missing('plans', 'repeat_meta', "`repeat_meta` text NULL");
CALL sp_add_column_if_missing('plans', 'sp_points', "`sp_points` int NOT NULL DEFAULT 0");
CALL sp_add_column_if_missing('plans', 'last_generated_date', "`last_generated_date` date NULL");

-- 2) Ensure indexes for merged fields
CALL sp_add_index_if_missing('plans', 'plans_plan_type_index', 'INDEX `plans_plan_type_index` (`plan_type`)');
CALL sp_add_index_if_missing('plans', 'plans_start_time_index', 'INDEX `plans_start_time_index` (`start_time`)');
CALL sp_add_index_if_missing('plans', 'plans_repeat_type_index', 'INDEX `plans_repeat_type_index` (`repeat_type`)');
CALL sp_add_index_if_missing('plans', 'plans_last_generated_date_index', 'INDEX `plans_last_generated_date_index` (`last_generated_date`)');

-- 3) Copy data from study_plans if it exists
SET @has_study_plans := (
    SELECT COUNT(1)
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'study_plans'
);

SET @copy_sql := IF(
    @has_study_plans > 0,
    "INSERT INTO `plans` (`user_id`, `name`, `status`, `plan_type`, `content`, `start_time`, `repeat_type`, `repeat_days`, `repeat_meta`, `sp_points`, `last_generated_date`, `created_at`, `updated_at`)\nSELECT sp.`user_id`, sp.`name`, sp.`status`, 'study', COALESCE(sp.`content`, ''), sp.`start_time`, COALESCE(NULLIF(sp.`repeat_type`, ''), 'none'), COALESCE(sp.`repeat_days`, ''), COALESCE(sp.`repeat_meta`, ''), COALESCE(sp.`sp_points`, 0), sp.`last_generated_date`, COALESCE(sp.`created_at`, NOW()), COALESCE(sp.`updated_at`, NOW())\nFROM `study_plans` sp\nLEFT JOIN `plans` p\n  ON p.`user_id` = sp.`user_id`\n AND p.`plan_type` = 'study'\n AND p.`name` = sp.`name`\n AND p.`start_time` = sp.`start_time`\nWHERE p.`id` IS NULL",
    "SELECT 'study_plans not exists, skip copy'"
);
PREPARE stmt FROM @copy_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4) Drop old table after merge
SET @drop_sql := IF(
    @has_study_plans > 0,
    "DROP TABLE `study_plans`",
    "SELECT 'study_plans already dropped'"
);
PREPARE stmt FROM @drop_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DROP PROCEDURE IF EXISTS sp_add_column_if_missing;
DROP PROCEDURE IF EXISTS sp_add_index_if_missing;
