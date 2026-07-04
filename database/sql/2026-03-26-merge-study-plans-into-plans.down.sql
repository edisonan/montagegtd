-- Rollback for merge-study-plans-into-plans
-- This rollback restores study_plans and copies study plans back from plans.
-- It does NOT drop merged columns from plans by default (safe rollback).

SET @db := DATABASE();

CREATE TABLE IF NOT EXISTS `study_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NULL,
  `start_time` datetime NOT NULL,
  `repeat_type` varchar(32) NOT NULL DEFAULT 'none',
  `repeat_days` varchar(64) NULL,
  `repeat_meta` text NULL,
  `sp_points` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `last_generated_date` date NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `study_plans_user_id_index` (`user_id`),
  KEY `study_plans_repeat_type_index` (`repeat_type`),
  KEY `study_plans_status_index` (`status`),
  KEY `study_plans_last_generated_date_index` (`last_generated_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `study_plans` (
    `user_id`, `name`, `content`, `start_time`, `repeat_type`, `repeat_days`, `repeat_meta`,
    `sp_points`, `status`, `last_generated_date`, `created_at`, `updated_at`
)
SELECT
    p.`user_id`,
    p.`name`,
    COALESCE(p.`content`, ''),
    p.`start_time`,
    COALESCE(NULLIF(p.`repeat_type`, ''), 'none'),
    COALESCE(p.`repeat_days`, ''),
    COALESCE(p.`repeat_meta`, ''),
    COALESCE(p.`sp_points`, 0),
    COALESCE(p.`status`, 1),
    p.`last_generated_date`,
    COALESCE(p.`created_at`, NOW()),
    COALESCE(p.`updated_at`, NOW())
FROM `plans` p
LEFT JOIN `study_plans` sp
  ON sp.`user_id` = p.`user_id`
 AND sp.`name` = p.`name`
 AND sp.`start_time` = p.`start_time`
WHERE p.`plan_type` = 'study'
  AND p.`start_time` IS NOT NULL
  AND sp.`id` IS NULL;

-- Optional destructive rollback (disabled by default):
-- If you need full schema rollback on plans, manually execute below after confirming no业务依赖。
-- ALTER TABLE `plans`
--   DROP INDEX `plans_plan_type_index`,
--   DROP INDEX `plans_start_time_index`,
--   DROP INDEX `plans_repeat_type_index`,
--   DROP INDEX `plans_last_generated_date_index`,
--   DROP COLUMN `plan_type`,
--   DROP COLUMN `content`,
--   DROP COLUMN `start_time`,
--   DROP COLUMN `repeat_type`,
--   DROP COLUMN `repeat_days`,
--   DROP COLUMN `repeat_meta`,
--   DROP COLUMN `sp_points`,
--   DROP COLUMN `last_generated_date`;
