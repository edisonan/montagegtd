-- MySQL dump 10.13  Distrib 5.7.18, for osx10.12 (x86_64)
--
-- Host: 127.0.0.1    Database: tasks
-- ------------------------------------------------------
-- Server version	5.7.18

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `tasks`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `tasks` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `tasks`;

--
-- Table structure for table `achievement`
--

DROP TABLE IF EXISTS `achievement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '成就唯一标识，如 POMODORO_30_DAYS',
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '成就名称（展示用）',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '成就描述（解释达成意义）',
  `category` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'achievement' COMMENT 'achievement / badge / event',
  `point_value` int(11) NOT NULL DEFAULT '0' COMMENT '奖励的 GP 数量（可为 0）',
  `visible` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否对用户展示',
  `grant_start_at` datetime DEFAULT NULL COMMENT '成就可被授予的开始时间（限时活动）',
  `grant_end_at` datetime DEFAULT NULL COMMENT '成就可被授予的结束时间',
  `expire_at` datetime DEFAULT NULL COMMENT '成就展示或权限失效时间（不影响历史）',
  `icon` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '成就图标（可选，用于 UI）',
  `enabled` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否启用（紧急下线用）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_menu`
--

DROP TABLE IF EXISTS `admin_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `uri` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_operation_log`
--

DROP TABLE IF EXISTS `admin_operation_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_operation_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `input` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_operation_log_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1871 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_permissions`
--

DROP TABLE IF EXISTS `admin_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `http_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `http_path` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_role_menu`
--

DROP TABLE IF EXISTS `admin_role_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_role_menu` (
  `role_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_menu_role_id_menu_id_index` (`role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_role_permissions`
--

DROP TABLE IF EXISTS `admin_role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_permissions_role_id_permission_id_index` (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_role_users`
--

DROP TABLE IF EXISTS `admin_role_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_role_users` (
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_users_role_id_user_id_index` (`role_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_roles`
--

DROP TABLE IF EXISTS `admin_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_user_permissions`
--

DROP TABLE IF EXISTS `admin_user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_user_permissions_user_id_permission_id_index` (`user_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(190) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `app_virtual_table_fields`
--

DROP TABLE IF EXISTS `app_virtual_table_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_virtual_table_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `virtual_table_id` int(10) unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `physical_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `length` int(10) unsigned DEFAULT NULL,
  `nullable` tinyint(4) NOT NULL DEFAULT '1',
  `default_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `default_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `indexed` tinyint(4) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_virtual_table_fields_virtual_table_id_slug_unique` (`virtual_table_id`,`slug`),
  UNIQUE KEY `app_virtual_table_fields_virtual_table_id_physical_name_unique` (`virtual_table_id`,`physical_name`),
  KEY `app_virtual_table_fields_virtual_table_id_index` (`virtual_table_id`),
  KEY `app_virtual_table_fields_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `app_virtual_tables`
--

DROP TABLE IF EXISTS `app_virtual_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_virtual_tables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(10) unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `physical_table` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_virtual_tables_app_id_slug_unique` (`app_id`,`slug`),
  KEY `app_virtual_tables_physical_table_index` (`physical_table`),
  KEY `app_virtual_tables_app_id_index` (`app_id`),
  KEY `app_virtual_tables_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `application_allowed_users`
--

DROP TABLE IF EXISTS `application_allowed_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `application_allowed_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_allowed_users_application_id_user_id_unique` (`application_id`,`user_id`),
  KEY `application_allowed_users_application_id_index` (`application_id`),
  KEY `application_allowed_users_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '应用名称',
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '应用标识（唯一）',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '应用描述',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态: 1=开发中, 2=运行中, 3=已停止, 4=已删除',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `auth_mode` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  PRIMARY KEY (`id`),
  UNIQUE KEY `applications_slug_unique` (`slug`),
  KEY `applications_auth_mode_index` (`auth_mode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='应用管理表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_ai_profiles`
--

DROP TABLE IF EXISTS `article_ai_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_ai_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) unsigned NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `primary_category` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_category` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags_json` json DEFAULT NULL,
  `keywords_json` json DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `content_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audience` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quality_score` tinyint(3) unsigned DEFAULT NULL,
  `risk_flags_json` json DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_version` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `analyzed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_ai_profiles_article_id_unique` (`article_id`),
  KEY `article_ai_profiles_status_index` (`status`),
  KEY `article_ai_profiles_primary_category_index` (`primary_category`),
  KEY `article_ai_profiles_analyzed_at_index` (`analyzed_at`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_ai_renders`
--

DROP TABLE IF EXISTS `article_ai_renders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_ai_renders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) unsigned NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `render_mode` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reader_card',
  `template_style` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` mediumtext COLLATE utf8mb4_unicode_ci,
  `outline_json` longtext COLLATE utf8mb4_unicode_ci,
  `html_content` longtext COLLATE utf8mb4_unicode_ci,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_version` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `error_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_ai_renders_article_id_unique` (`article_id`),
  KEY `article_ai_renders_status_index` (`status`),
  KEY `article_ai_renders_render_mode_index` (`render_mode`),
  KEY `article_ai_renders_generated_at_index` (`generated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_ai_tasks`
--

DROP TABLE IF EXISTS `article_ai_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_ai_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) unsigned NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `retry_count` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `error_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_version` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `article_ai_tasks_status_scheduled_at_index` (`status`,`scheduled_at`),
  KEY `article_ai_tasks_article_id_index` (`article_id`),
  KEY `article_ai_tasks_status_index` (`status`),
  KEY `article_ai_tasks_scheduled_at_index` (`scheduled_at`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_marks`
--

DROP TABLE IF EXISTS `article_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_marks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `article_id` int(10) unsigned NOT NULL COMMENT '文章id',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标注内容',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='文章标注表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `article_subs`
--

DROP TABLE IF EXISTS `article_subs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_subs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(10) unsigned NOT NULL COMMENT '订阅id',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `status` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT 'unread' COMMENT '状态（unread/read/later/star）',
  `mark` tinyint(4) NOT NULL DEFAULT '0',
  `star_ind` int(11) NOT NULL DEFAULT '0' COMMENT '收藏数量',
  `article_id` int(10) unsigned NOT NULL COMMENT '文章id',
  `published` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发布时间',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `articles_feed_id_index` (`feed_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94544 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章订阅关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `star_ind` int(11) NOT NULL DEFAULT '0',
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` text COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `word_count` int(10) unsigned NOT NULL DEFAULT '0',
  `estimated_read_minutes` int(10) unsigned NOT NULL DEFAULT '1',
  `published` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `articles_feed_id_foreign` (`feed_id`),
  KEY `articles_word_count_index` (`word_count`),
  KEY `articles_estimated_read_minutes_index` (`estimated_read_minutes`)
) ENGINE=MyISAM AUTO_INCREMENT=116820 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `behavior_event`
--

DROP TABLE IF EXISTS `behavior_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `behavior_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `event_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_key` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_value` int(11) DEFAULT '1',
  `occurred_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_event` (`user_id`,`event_type`,`event_key`),
  KEY `idx_user_type_time` (`user_id`,`event_type`,`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bgm_tracks`
--

DROP TABLE IF EXISTS `bgm_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bgm_tracks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `artist` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `audio_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual_pixabay',
  `search_keyword` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `metadata_json` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bgm_tracks_source_type_index` (`source_type`),
  KEY `bgm_tracks_search_keyword_index` (`search_keyword`),
  KEY `bgm_tracks_sort_order_index` (`sort_order`),
  KEY `bgm_tracks_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cals`
--

DROP TABLE IF EXISTS `cals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cals` (
  `id` int(11) DEFAULT NULL,
  `theme` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '主题',
  `status` tinyint(4) DEFAULT NULL COMMENT '状态（1 待处理 2已完成 3已折叠）',
  `subject` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '标题',
  `url` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '链接地址',
  `image_url` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片地址',
  `content` tinytext COLLATE utf8mb4_unicode_ci COMMENT '内容',
  `published` datetime DEFAULT NULL COMMENT '发布时间',
  `desc` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `dtstart` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '开始时间',
  `dtend` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '结束时间',
  `location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '地点',
  `summary` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '摘要'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日历表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分类名称',
  `category_order` tinyint(4) DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分类表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `code_histories`
--

DROP TABLE IF EXISTS `code_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `code_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code_id` bigint(20) unsigned NOT NULL COMMENT '关联的代码ID',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '历史代码内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `code_histories_code_id_index` (`code_id`),
  KEY `code_histories_created_at_index` (`created_at`),
  CONSTRAINT `code_histories_code_id_foreign` FOREIGN KEY (`code_id`) REFERENCES `codes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='代码历史版本表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `codes`
--

DROP TABLE IF EXISTS `codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '代码名称',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '代码类型: 1=php, 2=html',
  `path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '代码文件路径',
  `app_id` bigint(20) unsigned DEFAULT NULL COMMENT '所属应用ID',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '代码内容',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态: 1=启用, 2=禁用',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `auth_mode` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `codes_type_index` (`type`),
  KEY `codes_status_index` (`status`),
  KEY `codes_created_at_index` (`created_at`),
  KEY `codes_auth_mode_index` (`auth_mode`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='代码管理表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_enrollments`
--

DROP TABLE IF EXISTS `course_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('planned','active','paused','completed','dropped') COLLATE utf8mb4_unicode_ci DEFAULT 'planned',
  `goal` mediumtext COLLATE utf8mb4_unicode_ci,
  `show_progress` tinyint(1) DEFAULT '1',
  `show_notes` tinyint(1) DEFAULT '0',
  `show_study_time` tinyint(1) DEFAULT '0',
  `progress_percent` decimal(5,2) DEFAULT '0.00',
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `order_index` int(11) DEFAULT '0',
  `start_date` date DEFAULT NULL,
  `target_end_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_course` (`user_id`,`course_id`),
  KEY `idx_user_status` (`user_id`,`status`),
  KEY `idx_course_progress` (`course_id`,`progress_percent`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_generation_runs`
--

DROP TABLE IF EXISTS `course_generation_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_generation_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'running',
  `source_url` text COLLATE utf8mb4_unicode_ci,
  `items_count` int(11) NOT NULL DEFAULT '0',
  `error` text COLLATE utf8mb4_unicode_ci,
  `metadata` text COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_generation_runs_course_id_status_created_at_index` (`course_id`,`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_items`
--

DROP TABLE IF EXISTS `course_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_type` enum('module','chapter','video','assignment','quiz','reading') COLLATE utf8mb4_unicode_ci DEFAULT 'chapter',
  `duration` int(11) DEFAULT NULL,
  `external_url` mediumtext COLLATE utf8mb4_unicode_ci,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `content` text COLLATE utf8mb4_unicode_ci,
  `source_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `source_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `content_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `order_index` float DEFAULT '0',
  `avg_rating` decimal(3,2) DEFAULT '0.00',
  `avg_study_time` int(11) DEFAULT '0',
  `completion_count` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_course_order` (`course_id`,`order_index`),
  KEY `course_items_course_id_source_key_index` (`course_id`,`source_key`),
  KEY `course_items_content_status_source_type_index` (`content_status`,`source_type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_quiz_attempts`
--

DROP TABLE IF EXISTS `course_quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_quiz_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_course_id` int(11) DEFAULT NULL,
  `score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `correct_count` int(11) NOT NULL DEFAULT '0',
  `total_count` int(11) NOT NULL DEFAULT '0',
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `answers` text COLLATE utf8mb4_unicode_ci,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_quiz_attempts_user_id_quiz_id_created_at_index` (`user_id`,`quiz_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_quiz_options`
--

DROP TABLE IF EXISTS `course_quiz_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_quiz_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `option_key` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `order_index` double(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_quiz_options_question_id_order_index_index` (`question_id`,`order_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_quiz_questions`
--

DROP TABLE IF EXISTS `course_quiz_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_quiz_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `question_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `points` decimal(6,2) NOT NULL DEFAULT '1.00',
  `order_index` double(8,2) NOT NULL DEFAULT '0.00',
  `source_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_quiz_questions_quiz_id_order_index_index` (`quiz_id`,`order_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_quizzes`
--

DROP TABLE IF EXISTS `course_quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_quizzes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_item_id` int(11) NOT NULL,
  `passing_score` decimal(5,2) NOT NULL DEFAULT '70.00',
  `attempts_allowed` int(11) DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_quizzes_course_item_id_unique` (`course_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_review_items`
--

DROP TABLE IF EXISTS `course_review_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_review_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_course_id` int(11) NOT NULL,
  `course_item_id` int(11) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'due',
  `review_count` int(11) NOT NULL DEFAULT '0',
  `interval_days` int(11) NOT NULL DEFAULT '1',
  `last_score` decimal(5,2) DEFAULT NULL,
  `last_reviewed_at` timestamp NULL DEFAULT NULL,
  `next_review_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_review_items_user_id_user_course_id_course_item_id_unique` (`user_id`,`user_course_id`,`course_item_id`),
  KEY `course_review_items_user_id_status_next_review_at_index` (`user_id`,`status`,`next_review_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_url` mediumtext COLLATE utf8mb4_unicode_ci,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `cover_image_url` mediumtext COLLATE utf8mb4_unicode_ci,
  `public_status` tinyint(1) DEFAULT '1',
  `created_by` int(11) DEFAULT NULL,
  `difficulty` enum('beginner','intermediate','advanced') COLLATE utf8mb4_unicode_ci DEFAULT 'beginner',
  `estimated_hours` int(11) DEFAULT NULL,
  `tags` mediumtext COLLATE utf8mb4_unicode_ci,
  `source_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `source_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `content_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `automation_config` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_public` (`public_status`),
  KEY `idx_created_by` (`created_by`),
  KEY `courses_created_by_source_key_index` (`created_by`,`source_key`),
  KEY `courses_content_status_source_type_index` (`content_status`,`source_type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `daily_summarys`
--

DROP TABLE IF EXISTS `daily_summarys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_summarys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `summary_date` date DEFAULT NULL COMMENT '总结时间',
  `work_content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '工作内容',
  `life_content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '生活内容',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1启用 2删除）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_summary_date_index` (`user_id`,`summary_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='日报/日总结表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `digest_pages`
--

DROP TABLE IF EXISTS `digest_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `digest_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `profile_id` bigint(20) unsigned NOT NULL,
  `task_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_time_start` timestamp NULL DEFAULT NULL,
  `cover_time_end` timestamp NULL DEFAULT NULL,
  `intro` text COLLATE utf8mb4_unicode_ci,
  `content_markdown` longtext COLLATE utf8mb4_unicode_ci,
  `source_article_ids_json` json DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `generated_at` timestamp NULL DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_version` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `digest_pages_user_id_index` (`user_id`),
  KEY `digest_pages_profile_id_index` (`profile_id`),
  KEY `digest_pages_task_id_index` (`task_id`),
  KEY `digest_pages_status_index` (`status`),
  KEY `digest_pages_generated_at_index` (`generated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `digest_tasks`
--

DROP TABLE IF EXISTS `digest_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `digest_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `profile_id` bigint(20) unsigned NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `retry_count` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `error_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_version` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `digest_tasks_status_scheduled_at_index` (`status`,`scheduled_at`),
  KEY `digest_tasks_user_id_index` (`user_id`),
  KEY `digest_tasks_profile_id_index` (`profile_id`),
  KEY `digest_tasks_status_index` (`status`),
  KEY `digest_tasks_scheduled_at_index` (`scheduled_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `digest_whitelist_users`
--

DROP TABLE IF EXISTS `digest_whitelist_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `digest_whitelist_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `expires_at` timestamp NULL DEFAULT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `digest_whitelist_users_user_id_unique` (`user_id`),
  KEY `digest_whitelist_users_enabled_index` (`enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `discussion_replies`
--

DROP TABLE IF EXISTS `discussion_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discussion_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discussion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_solution` tinyint(1) DEFAULT '0',
  `vote_count` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_discussion` (`discussion_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feed_subs`
--

DROP TABLE IF EXISTS `feed_subs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feed_subs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `status` tinyint(4) DEFAULT NULL COMMENT '状态（1 已订阅 2 已退订）',
  `category_id` int(10) unsigned NOT NULL COMMENT '分类id',
  `feed_id` int(10) unsigned NOT NULL COMMENT '订阅源id',
  `feed_name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '订阅源名称',
  `feed_order` tinyint(4) DEFAULT '0' COMMENT '订阅源排序',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `feeds_category_id_index` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户订阅关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedbacks`
--

DROP TABLE IF EXISTS `feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedbacks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT '用户id',
  `from` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '问题来源',
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='反馈表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feeds`
--

DROP TABLE IF EXISTS `feeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feeds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态（1 启用 2 禁用） ',
  `category_id` int(10) unsigned NOT NULL COMMENT '分类id',
  `feed_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订阅源名称',
  `feed_desc` text COLLATE utf8mb4_unicode_ci COMMENT '订阅源描述',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订阅源地址',
  `favicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '订阅源icon',
  `orders` int(11) DEFAULT '0' COMMENT '订阅源排序',
  `type` tinyint(4) DEFAULT '1' COMMENT '类型（1, 通用RSS 2，饭否 3，马蜂窝 4，正则提取）',
  `sub_count` int(11) DEFAULT '0' COMMENT '订阅数量',
  `recommend_name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '推荐名称',
  `is_recommend` tinyint(4) DEFAULT '0' COMMENT '是否推荐（1为推荐 0为未推荐）',
  `recommend_order` tinyint(4) DEFAULT '0' COMMENT '推荐排序',
  `recommend_category_id` tinyint(4) DEFAULT '0' COMMENT '推荐分类',
  `active_level` tinyint(4) DEFAULT '0' COMMENT '活跃等级（1/2/3/4 活跃性依次递减）',
  `last_published` timestamp NULL DEFAULT NULL COMMENT '上次发布时间',
  `audit_status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `feeds_category_id_index` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订阅源表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `focus`
--

DROP TABLE IF EXISTS `focus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `focus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `status` tinyint(4) DEFAULT '1' COMMENT '番茄状态（1初始 2完成 3放弃）',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '番茄内容',
  `rating` tinyint(4) DEFAULT NULL COMMENT '番茄评分 1-5',
  `review_note` text COLLATE utf8mb4_unicode_ci COMMENT '番茄备注',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '番茄开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '番茄结束时间',
  `rest_status` tinyint(4) DEFAULT '1' COMMENT '休息状态（1初始 2完成 3放弃）',
  `rest_start_time` timestamp NULL DEFAULT NULL COMMENT '休息开始时间',
  `rest_end_time` timestamp NULL DEFAULT NULL COMMENT '休息结束时间',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `pomos_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=933 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='番茄表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `journals`
--

DROP TABLE IF EXISTS `journals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `start_time` datetime DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '事情内容',
  `type` smallint(6) DEFAULT '1' COMMENT '类型（1 主动记事 2待办任务 3番茄记录） ',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=993 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='事情记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kindle_logs`
--

DROP TABLE IF EXISTS `kindle_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kindle_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(10) unsigned NOT NULL COMMENT '类型（1测试 2定时任务）',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态（1初始化 2生成成功 3发送成功）',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件路径',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18848 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='kindle推送记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llm_agent_versions`
--

DROP TABLE IF EXISTS `llm_agent_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `llm_agent_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint(20) unsigned NOT NULL COMMENT '所属Agent ID',
  `version_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'v1.0' COMMENT '版本名称',
  `version_number` int(11) NOT NULL DEFAULT '1' COMMENT '版本号（用于排序）',
  `model_id` bigint(20) unsigned NOT NULL COMMENT '模型ID',
  `system_prompt` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '系统提示词',
  `temperature` decimal(3,2) DEFAULT NULL COMMENT '温度参数，0-2范围',
  `top_p` decimal(4,3) DEFAULT NULL COMMENT 'top_p参数，0-1范围',
  `max_tokens` int(11) DEFAULT NULL COMMENT '最大输出tokens',
  `context_length` int(11) NOT NULL DEFAULT '4000' COMMENT '上下文长度',
  `tools_config` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON格式工具配置',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为默认版本',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `usage_count` int(11) NOT NULL DEFAULT '0' COMMENT '该版本使用次数',
  `created_by` int(11) NOT NULL COMMENT '创建者用户ID',
  `created_at` timestamp NOT NULL DEFAULT '1970-01-01 13:00:01' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '1970-01-01 13:00:01' COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  `change_log` text COLLATE utf8mb4_unicode_ci COMMENT '版本变更说明',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_agent_version_number` (`agent_id`,`version_number`) COMMENT '版本号唯一',
  KEY `idx_agent_versions` (`agent_id`,`version_number`,`deleted_at`) COMMENT 'Agent版本查询',
  KEY `idx_agent_default` (`agent_id`,`is_default`,`is_active`) COMMENT '默认版本查询',
  KEY `idx_model_active` (`model_id`,`is_active`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_version_usage` (`usage_count`) COMMENT '版本使用量统计',
  KEY `idx_created_at` (`created_at`) COMMENT '按创建时间查询版本'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='LLM智能体版本配置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llm_agents`
--

DROP TABLE IF EXISTS `llm_agents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `llm_agents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '创建用户ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Agent名称',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Agent描述',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像URL',
  `current_version_id` bigint(20) unsigned DEFAULT NULL COMMENT '当前使用版本ID',
  `is_public` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否公开：0-私有，1-公开',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用：0-禁用，1-启用',
  `usage_count` int(11) NOT NULL DEFAULT '0' COMMENT '使用次数统计',
  `favorite_count` int(11) NOT NULL DEFAULT '0' COMMENT '收藏次数（公开Agent用）',
  `last_used_at` timestamp NULL DEFAULT NULL COMMENT '最后使用时间',
  `created_at` timestamp NOT NULL DEFAULT '1970-01-01 13:00:01' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '1970-01-01 13:00:01' COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '软删除时间',
  `builtin_slug` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '内置Agent标识，如：general-assistant、coding-helper 等，NULL表示用户自定义',
  PRIMARY KEY (`id`),
  KEY `idx_user_status` (`user_id`,`is_active`,`deleted_at`) COMMENT '用户有效agent查询',
  KEY `idx_public_popular` (`is_public`,`is_active`,`favorite_count`) COMMENT '热门公开agent',
  KEY `idx_usage` (`usage_count`) COMMENT '按使用量排序',
  KEY `idx_created_at` (`created_at`) COMMENT '按创建时间查询',
  KEY `idx_last_used` (`last_used_at`) COMMENT '清理长时间未用agent',
  KEY `idx_public_recent` (`is_public`,`created_at`) COMMENT '最新公开agent',
  KEY `idx_user_public` (`user_id`,`is_public`,`deleted_at`),
  KEY `idx_current_version` (`current_version_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='LLM智能体基础信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llm_chat_attachments`
--

DROP TABLE IF EXISTS `llm_chat_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `llm_chat_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `session_id` bigint(20) unsigned DEFAULT NULL,
  `conversation_id` bigint(20) unsigned DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `storage_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extracted_text` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ready',
  `error_message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `llm_chat_attachments_uuid_unique` (`uuid`),
  KEY `idx_llm_attachment_user` (`user_id`,`created_at`),
  KEY `idx_llm_attachment_message` (`session_id`,`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llm_conversations`
--

DROP TABLE IF EXISTS `llm_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `llm_conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` bigint(20) unsigned DEFAULT NULL,
  `model_id` int(11) NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` longtext COLLATE utf8mb4_unicode_ci,
  `feedback` smallint(6) DEFAULT NULL,
  `request_data` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON格式数据，MySQL 5.6不支持JSON类型',
  `response_data` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON格式数据',
  `input_tokens` int(11) DEFAULT '0',
  `output_tokens` int(11) DEFAULT '0',
  `cost` decimal(10,6) DEFAULT '0.000000',
  `request_time` decimal(10,3) DEFAULT '0.000',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending/success/failed',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `prompt_tokens` int(11) NOT NULL DEFAULT '0',
  `completion_tokens` int(11) NOT NULL DEFAULT '0',
  `total_tokens` int(11) NOT NULL DEFAULT '0',
  `answered_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_created` (`user_id`,`created_at`) COMMENT '用户查询聊天历史',
  KEY `idx_model_status` (`model_id`,`status`) COMMENT '按模型和状态查询',
  KEY `idx_credential_created` (`created_at`) COMMENT '按凭证统计',
  KEY `idx_status_created` (`status`,`created_at`) COMMENT '按状态和时间查询',
  KEY `idx_created` (`created_at`) COMMENT '时间范围查询',
  KEY `idx_cost` (`cost`) COMMENT '成本分析',
  KEY `idx_llm_conversations_session` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=199 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='LLM AI 聊天记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llm_models`
--

DROP TABLE IF EXISTS `llm_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `llm_models` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联用户（如果项目需要）',
  `provider_id` bigint(20) unsigned NOT NULL COMMENT '供应商ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模型名称，如gpt-4-turbo',
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '显示名称',
  `model_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'chat' COMMENT '模型类型：chat/completion/embedding/image',
  `context_length` int(11) DEFAULT NULL COMMENT '上下文长度',
  `max_tokens` int(11) DEFAULT NULL COMMENT '最大输出tokens',
  `input_price_per_1k` decimal(10,6) DEFAULT NULL COMMENT '输入价格/1K tokens',
  `output_price_per_1k` decimal(10,6) DEFAULT NULL COMMENT '输出价格/1K tokens',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `capabilities` text COLLATE utf8mb4_unicode_ci COMMENT '能力配置：vision, json_mode等',
  `sort_order` int(11) DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `llm_models_provider_name_unique` (`provider_id`,`name`),
  KEY `llm_models_model_type_index` (`model_type`),
  KEY `llm_models_is_active_index` (`is_active`),
  CONSTRAINT `llm_models_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `llm_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llm_providers`
--

DROP TABLE IF EXISTS `llm_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `llm_providers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联用户（如果项目需要）',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '供应商名称，如OpenAI、Anthropic等',
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '唯一标识符，如openai、anthropic',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '供应商描述',
  `base_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'API基础URL',
  `api_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'API类型：openai、anthropic、custom',
  `api_key` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `priority` int(11) DEFAULT '0' COMMENT '优先级，数字越大优先级越高',
  `config_schema` text COLLATE utf8mb4_unicode_ci COMMENT '配置项JSON Schema',
  `rate_limit_per_minute` int(11) DEFAULT NULL COMMENT '每分钟请求限制',
  `concurrent_limit` int(11) DEFAULT '10' COMMENT '并发限制',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llm_sessions`
--

DROP TABLE IF EXISTS `llm_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `llm_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent_id` bigint(20) unsigned DEFAULT NULL,
  `parent_session_id` bigint(20) unsigned DEFAULT NULL,
  `branched_from_conversation_id` bigint(20) unsigned DEFAULT NULL,
  `branch_order` int(10) unsigned NOT NULL DEFAULT '0',
  `message_count` int(11) NOT NULL DEFAULT '0',
  `token_count` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `last_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_uuid` (`uuid`),
  KEY `idx_user_main` (`user_id`,`deleted_at`,`last_message_at`),
  KEY `idx_cleanup` (`is_active`,`last_message_at`),
  KEY `idx_agent` (`agent_id`),
  KEY `idx_llm_session_parent` (`parent_session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='LLM会话表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `llm_usage_logs`
--

DROP TABLE IF EXISTS `llm_usage_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `llm_usage_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` bigint(20) unsigned NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT '关联用户（如果项目需要）',
  `input_tokens` int(11) DEFAULT NULL,
  `output_tokens` int(11) DEFAULT NULL,
  `total_tokens` int(11) DEFAULT NULL,
  `cost` decimal(10,6) DEFAULT NULL COMMENT '本次调用成本',
  `request_time` decimal(8,3) DEFAULT NULL COMMENT '请求耗时（秒）',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'success' COMMENT 'success/failed/rate_limited',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `request_data` text COLLATE utf8mb4_unicode_ci COMMENT '请求数据（可脱敏存储）',
  `response_data` text COLLATE utf8mb4_unicode_ci COMMENT '响应数据（可脱敏存储）',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `llm_usage_logs_provider_id_index` (`provider_id`),
  KEY `llm_usage_logs_model_id_index` (`model_id`),
  KEY `llm_usage_logs_user_id_index` (`user_id`),
  KEY `llm_usage_logs_created_at_index` (`created_at`),
  CONSTRAINT `llm_usage_logs_model_id_foreign` FOREIGN KEY (`model_id`) REFERENCES `llm_models` (`id`),
  CONSTRAINT `llm_usage_logs_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `llm_providers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `migration` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mind_tag_maps`
--

DROP TABLE IF EXISTS `mind_tag_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mind_tag_maps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL COMMENT '标签id',
  `mind_id` int(11) NOT NULL COMMENT '导图id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1启用 2删除）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tag_id_mind_id` (`tag_id`,`mind_id`)
) ENGINE=InnoDB AUTO_INCREMENT=902 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='笔记标签关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `minds`
--

DROP TABLE IF EXISTS `minds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `minds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '描述',
  `image_url` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片地址',
  `orders` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '排序',
  `parent_mind_id` int(11) DEFAULT NULL COMMENT '父类导图id',
  `copy_mind_id` int(11) DEFAULT NULL COMMENT '拷贝导图id',
  `source_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `is_root` tinyint(4) DEFAULT NULL COMMENT '是否首层',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态（1 启用 2删除）',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2578 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='思维导图表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `note_tag_maps`
--

DROP TABLE IF EXISTS `note_tag_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note_tag_maps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL COMMENT '标签id',
  `note_id` int(11) NOT NULL COMMENT '笔记id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1启用 2删除）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=927 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='笔记标签关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1私有 2公共）',
  `name` text COLLATE utf8mb4_unicode_ci COMMENT '内容',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '正文内容',
  `record_path` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '语音地址',
  `image_path` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片地址',
  `task_id` int(11) DEFAULT NULL COMMENT '任务id',
  `pomo_id` int(11) DEFAULT NULL COMMENT '番茄id',
  `article_id` int(11) DEFAULT NULL COMMENT '文章id',
  `source_type` tinyint(4) DEFAULT NULL COMMENT '来源类型（1pomo 2article 3task 4thing）',
  `source_id` int(11) DEFAULT NULL COMMENT '来源id',
  `audit_status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `pomos_user_id_index` (`user_id`),
  KEY `idx_source` (`source_type`,`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1011 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='笔记表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_infos`
--

DROP TABLE IF EXISTS `oauth_infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_infos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `expire` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '过期时间',
  `access_token` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'access_token',
  `driver` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '驱动类型（github/weibo）',
  `third_uid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '第三方用户id',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Oauth登陆授权表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `password_resets_email_index` (`email`(191)),
  KEY `password_resets_token_index` (`token`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='密码重置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '令牌名称',
  `token` varchar(64) NOT NULL COMMENT '生成的令牌值',
  `token_hash` varchar(255) NOT NULL COMMENT '令牌的哈希值',
  `scopes` text NOT NULL COMMENT '权限范围',
  `revoked_at` timestamp NULL DEFAULT NULL COMMENT '撤销时间',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_token_hash` (`token_hash`(32)),
  KEY `personal_access_tokens_revoked_at_index` (`revoked_at`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '目标名称',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1进行中 2已完成 3已折叠） ',
  `deadline` timestamp NULL DEFAULT NULL COMMENT '截止时间',
  `remindtime` timestamp NULL DEFAULT NULL COMMENT '提醒时间',
  `priority` tinyint(4) NOT NULL DEFAULT '1' COMMENT '优先级（1不重要不紧急/2不重要紧急/3重要不紧急/4重要紧急）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `plan_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `repeat_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `repeat_days` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repeat_meta` text COLLATE utf8mb4_unicode_ci,
  `sp_points` int(11) NOT NULL DEFAULT '0',
  `last_generated_date` date DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `start_time` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_user_id_index` (`user_id`),
  KEY `plans_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='目标表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_account`
--

DROP TABLE IF EXISTS `point_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `gp_balance` int(11) NOT NULL DEFAULT '0',
  `ap_balance` int(11) NOT NULL DEFAULT '0',
  `ap_frozen` int(11) NOT NULL DEFAULT '0',
  `sp_balance` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_bus_lines`
--

DROP TABLE IF EXISTS `point_bus_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_bus_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#16a34a',
  `price_ap` int(11) NOT NULL DEFAULT '60',
  `path_payload` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_point_bus_lines_code` (`code`),
  KEY `idx_point_bus_lines_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_bus_run_logs`
--

DROP TABLE IF EXISTS `point_bus_run_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_bus_run_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `user_line_id` bigint(20) unsigned NOT NULL,
  `run_status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'running',
  `progress` int(11) NOT NULL DEFAULT '0',
  `meta_payload` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_point_bus_run_logs_user` (`user_id`),
  KEY `idx_point_bus_run_logs_user_line` (`user_line_id`),
  KEY `idx_point_bus_run_logs_status` (`run_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_event_log`
--

DROP TABLE IF EXISTS `point_event_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_event_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `rule_id` int(11) DEFAULT NULL,
  `event_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `point_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GP',
  `granted_points` int(11) NOT NULL DEFAULT '0',
  `balance_after` int(11) NOT NULL DEFAULT '0',
  `occurred_on` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_point_event_user_key` (`user_id`,`event_key`),
  KEY `point_event_log_user_id_index` (`user_id`),
  KEY `point_event_log_rule_id_index` (`rule_id`),
  KEY `point_event_log_event_type_index` (`event_type`),
  KEY `point_event_log_occurred_on_index` (`occurred_on`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_fish_feed_logs`
--

DROP TABLE IF EXISTS `point_fish_feed_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_fish_feed_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fish_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `feed_value` int(11) NOT NULL DEFAULT '0',
  `point_cost` int(11) NOT NULL DEFAULT '0',
  `feed_tier` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'basic',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `point_fish_feed_logs_fish_id_index` (`fish_id`),
  KEY `point_fish_feed_logs_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_fish_instances`
--

DROP TABLE IF EXISTS `point_fish_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_fish_instances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '我的鱼',
  `species` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'goldfish',
  `growth_value` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '1',
  `health` int(11) NOT NULL DEFAULT '100',
  `last_fed_at` datetime DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `point_fish_instances_user_id_index` (`user_id`),
  KEY `point_fish_instances_level_index` (`level`),
  KEY `point_fish_instances_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_lottery_draw_logs`
--

DROP TABLE IF EXISTS `point_lottery_draw_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_lottery_draw_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `pool_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned DEFAULT NULL,
  `cost_ap` int(11) NOT NULL DEFAULT '0',
  `result_payload` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_point_lottery_draw_logs_user` (`user_id`),
  KEY `idx_point_lottery_draw_logs_pool` (`pool_id`),
  KEY `idx_point_lottery_draw_logs_item` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_lottery_pool_items`
--

DROP TABLE IF EXISTS `point_lottery_pool_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_lottery_pool_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pool_id` bigint(20) unsigned NOT NULL,
  `reward_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'entitlement',
  `reward_name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reward_payload` text COLLATE utf8mb4_unicode_ci,
  `weight` int(11) NOT NULL DEFAULT '1',
  `stock` int(11) NOT NULL DEFAULT '-1',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_point_lottery_pool_items_pool` (`pool_id`),
  KEY `idx_point_lottery_pool_items_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_lottery_pools`
--

DROP TABLE IF EXISTS `point_lottery_pools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_lottery_pools` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scene` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `cost_ap` int(11) NOT NULL DEFAULT '10',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_point_lottery_pools_code` (`code`),
  KEY `idx_point_lottery_pools_scene` (`scene`),
  KEY `idx_point_lottery_pools_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_mall_delivery_logs`
--

DROP TABLE IF EXISTS `point_mall_delivery_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_mall_delivery_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `handler` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_payload` text COLLATE utf8mb4_unicode_ci,
  `response_payload` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_point_mall_delivery_logs_order` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_mall_entitlements`
--

DROP TABLE IF EXISTS `point_mall_entitlements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_mall_entitlements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `entitlement_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `meta_payload` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_point_mall_entitlements_order` (`order_id`),
  KEY `idx_point_mall_entitlements_user` (`user_id`),
  KEY `idx_point_mall_entitlements_type` (`entitlement_type`),
  KEY `idx_point_mall_entitlements_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_mall_goods`
--

DROP TABLE IF EXISTS `point_mall_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_mall_goods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scene` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `delivery_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `point_cost` int(11) NOT NULL DEFAULT '0',
  `stock` int(11) NOT NULL DEFAULT '-1',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `sort` int(11) NOT NULL DEFAULT '0',
  `payload` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_point_mall_goods_code` (`code`),
  KEY `idx_point_mall_goods_scene` (`scene`),
  KEY `idx_point_mall_goods_status` (`status`),
  KEY `idx_point_mall_goods_sort` (`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_mall_orders`
--

DROP TABLE IF EXISTS `point_mall_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_mall_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `goods_id` bigint(20) unsigned NOT NULL,
  `goods_snapshot` text COLLATE utf8mb4_unicode_ci,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `point_cost_each` int(11) NOT NULL DEFAULT '0',
  `point_cost_total` int(11) NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paid',
  `delivery_status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `delivery_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `delivery_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_payload` text COLLATE utf8mb4_unicode_ci,
  `paid_at` datetime DEFAULT NULL,
  `fulfilled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_point_mall_orders_no` (`order_no`),
  KEY `idx_point_mall_orders_user` (`user_id`),
  KEY `idx_point_mall_orders_goods` (`goods_id`),
  KEY `idx_point_mall_orders_status` (`status`),
  KEY `idx_point_mall_orders_delivery_status` (`delivery_status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_pet_feed_logs`
--

DROP TABLE IF EXISTS `point_pet_feed_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_pet_feed_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pet_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `feed_value` int(11) NOT NULL DEFAULT '0',
  `point_cost` int(11) NOT NULL DEFAULT '0',
  `feed_tier` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'basic',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `point_pet_feed_logs_pet_id_index` (`pet_id`),
  KEY `point_pet_feed_logs_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_pet_instances`
--

DROP TABLE IF EXISTS `point_pet_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_pet_instances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '我的宠物',
  `species` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cat',
  `growth_value` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '1',
  `health` int(11) NOT NULL DEFAULT '100',
  `last_fed_at` datetime DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `point_pet_instances_user_id_index` (`user_id`),
  KEY `point_pet_instances_level_index` (`level`),
  KEY `point_pet_instances_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_record`
--

DROP TABLE IF EXISTS `point_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `point_type` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `change_amount` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `source_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` int(11) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_time` (`user_id`,`created_at`),
  KEY `idx_user_type` (`user_id`,`point_type`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_rule`
--

DROP TABLE IF EXISTS `point_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `point_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GP',
  `point_value` int(11) NOT NULL DEFAULT '0',
  `daily_max_grants` int(11) NOT NULL DEFAULT '0',
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `point_rule_event_type_index` (`event_type`),
  KEY `point_rule_enabled_index` (`enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_tree_instances`
--

DROP TABLE IF EXISTS `point_tree_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_tree_instances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '我的树',
  `species` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'oak',
  `growth_value` int(11) NOT NULL DEFAULT '0',
  `stage` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sapling',
  `health` int(11) NOT NULL DEFAULT '100',
  `last_watered_at` datetime DEFAULT NULL,
  `decoration_payload` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'alive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_point_tree_instances_user` (`user_id`),
  KEY `idx_point_tree_instances_stage` (`stage`),
  KEY `idx_point_tree_instances_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_tree_water_logs`
--

DROP TABLE IF EXISTS `point_tree_water_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_tree_water_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tree_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `water_value` int(11) NOT NULL DEFAULT '0',
  `point_cost` int(11) NOT NULL DEFAULT '0',
  `water_tier` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'basic',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_point_tree_water_logs_tree` (`tree_id`),
  KEY `idx_point_tree_water_logs_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_user_bus_lines`
--

DROP TABLE IF EXISTS `point_user_bus_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `point_user_bus_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `line_id` bigint(20) unsigned NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `bought_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_point_user_bus_line` (`user_id`,`line_id`),
  KEY `idx_point_user_bus_lines_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `public_discussions`
--

DROP TABLE IF EXISTS `public_discussions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `public_discussions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `course_item_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `type` enum('question','note','resource','tip') COLLATE utf8mb4_unicode_ci DEFAULT 'note',
  `is_resolved` tinyint(1) DEFAULT '0',
  `is_pinned` tinyint(1) DEFAULT '0',
  `vote_count` int(11) DEFAULT '0',
  `view_count` int(11) DEFAULT '0',
  `reply_count` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_course_item` (`course_id`,`course_item_id`),
  KEY `idx_hot` (`course_id`,`vote_count`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `pomo_config` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON格式番茄目标及计时配置',
  `is_start_kindle` tinyint(4) DEFAULT '0' COMMENT '是否开启kindle推送（0 未开启 1开启）',
  `kindle_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'kindle推送地址',
  `with_image_push` tinyint(4) DEFAULT '0' COMMENT 'kindle是否带图推送',
  `cal_token` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '任务日历token',
  `notify_channels` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON格式通知渠道配置',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=402 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='设置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statistics`
--

DROP TABLE IF EXISTS `statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `date_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日期类型（day 按日）',
  `data_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据类型（article/task/note/mind/pomo）',
  `total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数量',
  `statistic_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '统计日期',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1013 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='统计表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `study_activities`
--

DROP TABLE IF EXISTS `study_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `course_item_id` int(11) DEFAULT NULL,
  `activity_type` enum('start_course','complete_item','add_note','rate_item','post_discussion','complete_course') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_course` (`user_id`,`course_id`,`created_at`),
  KEY `idx_course_recent` (`course_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `study_checkins`
--

DROP TABLE IF EXISTS `study_checkins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `study_checkins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `checkin_date` date NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `audio_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_study_checkin` (`user_id`,`task_id`,`checkin_date`),
  KEY `study_checkins_user_id_index` (`user_id`),
  KEY `study_checkins_task_id_index` (`task_id`),
  KEY `study_checkins_checkin_date_index` (`checkin_date`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标签内容',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1启用 2禁用）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='标签表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task_tag_maps`
--

DROP TABLE IF EXISTS `task_tag_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_tag_maps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL COMMENT '标签id',
  `task_id` int(11) NOT NULL COMMENT '任务id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1 启用 2禁用）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='任务标签关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '待办内容',
  `content` text COLLATE utf8mb4_unicode_ci,
  `planned_start_time` datetime DEFAULT NULL COMMENT '预计开始时间',
  `planned_end_time` datetime DEFAULT NULL COMMENT '预计结束时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1 进行中 2已完成 3已折叠）',
  `rating` tinyint(4) DEFAULT NULL COMMENT '任务评分 1-5',
  `review_note` text COLLATE utf8mb4_unicode_ci COMMENT '任务备注',
  `deadline` timestamp NULL DEFAULT NULL COMMENT '截止时间',
  `remindtime` timestamp NULL DEFAULT NULL COMMENT '提醒时间',
  `priority` tinyint(4) NOT NULL DEFAULT '1' COMMENT '优先级（1不重要不紧急/2不重要紧急/3重要不紧急/4重要紧急）',
  `is_top` tinyint(4) DEFAULT '0' COMMENT '是否置顶',
  `is_doing` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否正在做 0否 1是',
  `mode` tinyint(4) DEFAULT '1' COMMENT '类型（1工作 2生活）',
  `parent_task_id` int(11) DEFAULT NULL COMMENT '父类任务id',
  `plan_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `study_scheduled_date` date DEFAULT NULL,
  `study_repeat_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `study_repeat_days` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `study_repeat_meta` text COLLATE utf8mb4_unicode_ci,
  `study_sp_points` int(11) NOT NULL DEFAULT '0',
  `study_source_task_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_user_id_index` (`user_id`),
  KEY `tasks_study_scheduled_date_index` (`study_scheduled_date`),
  KEY `tasks_study_source_task_id_index` (`study_source_task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=638 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='待办任务表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thirds`
--

DROP TABLE IF EXISTS `thirds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thirds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `token` text COLLATE utf8mb4_unicode_ci COMMENT 'token',
  `token_value` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'token_value',
  `token_secret` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'token_secret',
  `third_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '第三方id',
  `third_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '第三方名称',
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '来源（fanfou、）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='第三方授权记录表（主要饭否）';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `token_usage_logs`
--

DROP TABLE IF EXISTS `token_usage_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `token_usage_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token_id` int(11) NOT NULL,
  `api_endpoint` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_access_tokens`
--

DROP TABLE IF EXISTS `user_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capabilities` json DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_access_tokens_token_hash_unique` (`token_hash`),
  KEY `user_access_tokens_user_id_index` (`user_id`),
  KEY `user_access_tokens_expires_at_index` (`expires_at`),
  KEY `user_access_tokens_revoked_at_index` (`revoked_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1669 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_achievement`
--

DROP TABLE IF EXISTS `user_achievement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_achievement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `achievement_code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '对应 point_achievement.code',
  `achieved_at` datetime NOT NULL COMMENT '首次达成时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_achievement` (`user_id`,`achievement_code`),
  KEY `idx_user_time` (`user_id`,`achieved_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_courses`
--

DROP TABLE IF EXISTS `user_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned',
  `goal` text COLLATE utf8mb4_unicode_ci,
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `order_index` int(11) NOT NULL DEFAULT '0',
  `start_date` date DEFAULT NULL,
  `target_end_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_courses_user_id_course_id_unique` (`user_id`,`course_id`),
  KEY `user_courses_user_id_status_index` (`user_id`,`status`),
  KEY `user_courses_course_id_progress_percent_index` (`course_id`,`progress_percent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_digest_profiles`
--

DROP TABLE IF EXISTS `user_digest_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_digest_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `topics_json` json DEFAULT NULL,
  `include_keywords_json` json DEFAULT NULL,
  `exclude_keywords_json` json DEFAULT NULL,
  `preferred_categories_json` json DEFAULT NULL,
  `time_window_days` tinyint(3) unsigned NOT NULL DEFAULT '7',
  `frequency` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'daily',
  `max_articles` smallint(5) unsigned NOT NULL DEFAULT '20',
  `output_style` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_digest_profiles_user_id_enabled_index` (`user_id`,`enabled`),
  KEY `user_digest_profiles_user_id_index` (`user_id`),
  KEY `user_digest_profiles_enabled_index` (`enabled`),
  KEY `user_digest_profiles_frequency_index` (`frequency`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_progress`
--

DROP TABLE IF EXISTS `user_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_course_id` int(11) NOT NULL,
  `course_item_id` int(11) NOT NULL,
  `status` enum('not_started','in_progress','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'not_started',
  `mastery_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started',
  `mastery_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `review_due_at` timestamp NULL DEFAULT NULL,
  `time_spent` int(11) DEFAULT '0',
  `rating` tinyint(4) DEFAULT NULL,
  `notes` mediumtext COLLATE utf8mb4_unicode_ci,
  `note_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_item` (`user_id`,`user_course_id`,`course_item_id`),
  KEY `idx_user_course_status` (`user_id`,`user_course_id`,`status`),
  KEY `idx_item_status` (`course_item_id`,`status`),
  KEY `user_progress_user_id_mastery_status_review_due_at_index` (`user_id`,`mastery_status`,`review_due_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_refresh_tokens`
--

DROP TABLE IF EXISTS `user_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_refresh_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `access_token_id` bigint(20) unsigned DEFAULT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_refresh_tokens_token_hash_unique` (`token_hash`),
  KEY `user_refresh_tokens_user_id_index` (`user_id`),
  KEY `user_refresh_tokens_access_token_id_index` (`access_token_id`),
  KEY `user_refresh_tokens_device_id_index` (`device_id`),
  KEY `user_refresh_tokens_client_type_index` (`client_type`),
  KEY `user_refresh_tokens_expires_at_index` (`expires_at`),
  KEY `user_refresh_tokens_revoked_at_index` (`revoked_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1669 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_pomo` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=402 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webpage_rss_sources`
--

DROP TABLE IF EXISTS `webpage_rss_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webpage_rss_sources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `feed_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `list_url` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rss_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_selector` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_selector` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_selector` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `published_selector` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_selector` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary_source` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'list',
  `summary_selector` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detail_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `detail_summary_selector` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_selector` text COLLATE utf8mb4_unicode_ci,
  `exclude_selector` text COLLATE utf8mb4_unicode_ci,
  `author_selector` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_content_length` int(11) NOT NULL DEFAULT '12000',
  `failure_strategy` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fallback',
  `refresh_interval` int(11) NOT NULL DEFAULT '60',
  `dedupe_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'url',
  `encoding` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'auto',
  `last_debug_result` mediumtext COLLATE utf8mb4_unicode_ci,
  `last_error` text COLLATE utf8mb4_unicode_ci,
  `last_checked_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `webpage_rss_sources_rss_token_unique` (`rss_token`),
  KEY `webpage_rss_sources_user_id_status_index` (`user_id`,`status`),
  KEY `webpage_rss_sources_feed_id_index` (`feed_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;



-- ------------------------------------------------------------
-- Migrations tracking (reconciled so `php artisan migrate` runs clean)
-- ------------------------------------------------------------
INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2014_10_12_000000_create_users_table', 1),
  ('2014_10_12_100000_create_password_resets_table', 1),
  ('2016_01_04_173148_create_admin_tables', 3),
  ('2016_02_04_041533_create_tasks_table', 1),
  ('2017_01_23_084959_create_pomos_table', 2),
  ('2025_01_01_000000_create_codes_table', 13),
  ('2025_01_01_000001_create_llm_providers_table', 13),
  ('2025_01_01_000002_create_llm_models_table', 13),
  ('2025_01_01_000003_create_llm_provider_credentials_table', 13),
  ('2025_01_01_000004_create_llm_usage_logs_table', 13),
  ('2025_01_02_000001_add_user_id_to_llm_tables', 13),
  ('2025_01_03_000001_create_courses_table', 13),
  ('2025_01_03_000002_create_user_courses_table', 13),
  ('2025_01_03_000003_create_course_items_table', 13),
  ('2025_01_03_000004_create_user_progress_table', 13),
  ('2025_01_03_000005_create_public_discussions_table', 13),
  ('2025_01_03_000006_create_discussion_replies_table', 13),
  ('2025_01_03_000007_create_study_activities_table', 13),
  ('2025_01_10_000001_create_llm_ai_chats_table', 13),
  ('2025_01_17_000001_create_llm_agents_table', 13),
  ('2025_12_29_231409_add_public_status_to_courses_table', 13),
  ('2025_12_29_233143_remove_is_public_from_courses_table', 13),
  ('2025_12_29_235530_create_personal_access_tokens_table', 13),
  ('2025_12_29_235533_create_token_usage_logs_table', 13),
  ('2025_12_29_235958_create_token_usage_logs_table', 13),
  ('2025_12_29_235959_create_personal_access_tokens_table', 13),
  ('2026_01_08_000000_update_codes_table_add_path_and_app_id', 13),
  ('2026_01_17_000001_create_llm_sessions_table', 13),
  ('2026_02_28_000001_add_revoked_at_to_personal_access_tokens_table', 13),
  ('2026_03_01_000001_create_user_access_tokens_table', 13),
  ('2026_03_01_000002_create_user_refresh_tokens_table', 13),
  ('2026_03_04_000001_create_point_rule_and_event_logs_tables', 3),
  ('2026_03_04_000002_create_user_notifications_table', 3),
  ('2026_03_04_000003_add_is_doing_to_tasks_table', 3),
  ('2026_03_06_000001_add_source_fields_to_minds_table', 13),
  ('2026_03_06_010000_create_point_mall_tables', 13),
  ('2026_03_06_020000_create_point_mall_game_tables', 13),
  ('2026_03_10_000004_add_schedule_review_fields_to_tasks_and_pomos_table', 4),
  ('2026_03_15_000001_extend_point_mall_eco_playgrounds', 13),
  ('2026_03_17_000001_add_study_fields_and_checkins', 13),
  ('2026_03_17_000002_create_study_plans_table', 13),
  ('2026_03_17_000003_merge_study_plans_into_plans', 13),
  ('2026_03_28_093500_add_missing_study_plan_columns_to_plans', 13),
  ('2026_03_29_080500_add_deleted_at_to_plans_for_soft_delete', 13),
  ('2026_03_29_090500_add_video_path_to_study_checkins', 13),
  ('2026_06_22_100000_create_article_ai_profiles_table', 5),
  ('2026_06_22_100100_create_article_ai_tasks_table', 5),
  ('2026_06_22_100200_create_digest_whitelist_users_table', 5),
  ('2026_06_22_100300_create_user_digest_profiles_table', 5),
  ('2026_06_22_100400_create_digest_tasks_table', 5),
  ('2026_06_22_100500_create_digest_pages_table', 5),
  ('2026_06_28_120000_create_article_ai_renders_table', 6),
  ('2026_06_28_130000_create_bgm_tracks_table', 13),
  ('2026_06_29_000001_create_webpage_rss_sources_table', 7),
  ('2026_07_04_000001_add_content_to_notes_table', 13),
  ('2026_07_05_000001_add_notify_channels_to_settings_table', 8),
  ('2026_07_06_000001_drop_ifttt_notify_from_settings_table', 9),
  ('2026_07_06_000002_merge_pomo_settings_into_pomo_config', 9),
  ('2026_07_09_000001_create_app_virtual_tables', 10),
  ('2026_07_12_000001_backfill_note_content_from_name', 11),
  ('2026_07_13_000001_add_code_access_policies', 13),
  ('2026_07_13_000002_add_reading_metrics_to_articles', 1),
  ('2026_07_13_000003_repair_study_schema', 13),
  ('2026_07_15_000001_add_status_to_categories_table', 12),
  ('2026_07_15_000002_add_mark_to_article_subs_table', 13),
  ('2026_07_17_000001_move_llm_api_keys_to_providers', 13),
  ('2026_07_17_000002_remove_llm_provider_credentials', 13),
  ('2026_07_17_010000_create_course_learning_features', 13),
  ('2026_07_17_020000_add_course_automation', 13),
  ('2026_07_17_030000_add_session_id_to_llm_conversations', 13),
  ('2026_07_17_040000_align_llm_conversation_runtime_columns', 13),
  ('2026_07_17_050000_create_llm_chat_attachments_table', 13),
  ('2026_07_17_060000_add_branch_fields_to_llm_sessions', 13),
  ('2026_07_17_070000_add_feedback_to_llm_conversations', 13);

-- ------------------------------------------------------------
-- Backend admin bootstrap
--   Login: admin  /  Password: admin123  (change immediately)
-- ------------------------------------------------------------
-- ---- admin bootstrap ----
INSERT INTO `admin_users` (`id`,`username`,`password`,`name`,`avatar`,`remember_token`,`created_at`,`updated_at`,`last_login`) VALUES
  (1,'admin','$2y$10$Sv/3mcE4bSeeXwQ3PiLOkuqjK8pTU5.aXQ3xELfI1rHreOk0cJjIW','Administrator',NULL,'',NOW(),NOW(),NOW());
INSERT INTO `admin_roles` (`id`,`name`,`slug`,`created_at`,`updated_at`) VALUES
  (1,'Administrator','administrator','2018-01-05 23:15:18','2018-01-05 23:15:18');
INSERT INTO `admin_role_users` (`role_id`,`user_id`,`created_at`,`updated_at`) VALUES (1,1,NOW(),NOW());
INSERT INTO `admin_permissions` (`id`,`name`,`slug`,`http_method`,`http_path`,`created_at`,`updated_at`) VALUES
  (1,'All permission','*','','*',NULL,NULL),
  (2,'Dashboard','dashboard','GET','/',NULL,NULL),
  (3,'Login','auth.login','','/auth/login
/auth/logout',NULL,NULL),
  (4,'User setting','auth.setting','GET,PUT','/auth/setting',NULL,NULL),
  (5,'Auth management','auth.management','','/auth/roles
/auth/permissions
/auth/menu
/auth/logs',NULL,NULL);
INSERT INTO `admin_menu` (`id`,`parent_id`,`order`,`title`,`icon`,`uri`,`created_at`,`updated_at`) VALUES
  (1,0,1,'首页','fa-bar-chart','/',NULL,'2025-12-18 14:16:43'),
  (2,0,21,'控制台管理','fa-tasks',NULL,NULL,'2025-12-29 03:56:42'),
  (3,2,22,'管理员管理','fa-users','auth/users',NULL,'2025-12-29 03:56:42'),
  (4,2,23,'角色管理','fa-user','auth/roles',NULL,'2025-12-29 03:56:42'),
  (5,2,24,'权限管理','fa-ban','auth/permissions',NULL,'2025-12-29 03:56:42'),
  (6,2,25,'菜单管理','fa-bars','auth/menu',NULL,'2025-12-29 03:56:42'),
  (7,2,26,'操作日志','fa-history','auth/logs',NULL,'2025-12-29 03:56:42'),
  (9,0,2,'订阅管理','fa-feed','demo/feeds','2018-01-06 20:35:53','2025-12-18 22:19:07'),
  (11,9,3,'订阅源管理','fa-bars','feeds','2018-01-07 12:54:05','2025-12-18 22:19:07'),
  (12,9,4,'订阅记录','fa-bars','feedsubs','2018-01-07 12:54:34','2025-12-18 22:19:07'),
  (13,9,5,'文章管理','fa-bars','articles','2018-01-07 14:11:11','2025-12-18 22:19:07'),
  (14,9,6,'文章收藏','fa-bars','articlemarks','2018-01-07 14:11:54','2025-12-18 22:19:07'),
  (15,26,18,'用户反馈','fa-bars','feedbacks','2018-01-07 14:12:28','2025-12-29 03:53:28'),
  (16,25,12,'目标管理','fa-bars','goals','2018-01-07 14:12:44','2025-12-18 22:19:07'),
  (17,25,14,'分类管理','fa-bars','categorys','2018-01-07 14:12:58','2025-12-18 22:19:07'),
  (18,26,20,'Kindle日志','fa-bars','kindlelogs','2018-01-07 14:13:16','2025-12-29 03:56:59'),
  (19,25,13,'思维导图','fa-bars','minds','2018-01-07 14:13:35','2025-12-18 22:19:07'),
  (20,25,10,'笔记管理','fa-bars','notes','2018-01-07 14:13:48','2025-12-18 22:19:07'),
  (21,25,11,'番茄钟','fa-bars','pomos','2018-01-07 14:14:03','2025-12-18 22:19:07'),
  (22,26,19,'系统设置','fa-bars','settings','2018-01-07 14:14:19','2025-12-29 03:56:59'),
  (23,25,8,'任务管理','fa-bars','tasks','2018-01-07 14:14:40','2025-12-18 22:19:07'),
  (24,25,9,'事项管理','fa-bars','things','2018-01-07 14:14:58','2025-12-18 22:19:07'),
  (25,0,7,'GTD管理','fa-tasks',NULL,'2018-01-07 14:15:12','2025-12-18 22:19:07'),
  (26,0,16,'系统管理','fa-comments-o',NULL,'2018-01-07 14:16:40','2025-12-29 03:53:28'),
  (27,0,15,'自建应用','fa-bars','codes','2025-12-18 14:01:01','2026-01-09 05:34:33'),
  (28,26,17,'用户管理','fa-bars','/users','2025-12-29 03:55:51','2025-12-29 03:56:59'),
  (29,26,0,'模型供应商管理','fa-bars','/llm-providers','2025-12-29 06:26:10','2025-12-29 06:26:10'),
  (30,26,0,'模型管理','fa-bars','/llm-models','2025-12-29 06:26:57','2025-12-29 06:26:57'),
  (31,26,0,'供应商凭证管理','fa-bars','/llm-provider-credentials','2025-12-29 06:27:23','2025-12-29 06:27:23'),
  (32,26,0,'模型使用记录管理','fa-bars','/llm-usage-logs','2025-12-29 06:27:50','2025-12-29 06:27:50'),
  (33,27,0,'应用管理','fa-bars','/applications','2026-01-09 05:33:40','2026-01-09 05:33:40'),
  (34,27,0,'代码管理','fa-bars','/codes','2026-01-09 05:34:04','2026-01-09 05:34:04'),
  (35,26,27,'日志查询','fa-file-text-o','system-logs','2026-06-29 00:10:20','2026-06-29 00:10:20');
INSERT INTO `admin_role_menu` (`role_id`,`menu_id`,`created_at`,`updated_at`) VALUES
  (1,2,NULL,NULL),
  (1,9,NULL,NULL),
  (1,10,NULL,NULL),
  (1,11,NULL,NULL),
  (1,12,NULL,NULL),
  (1,13,NULL,NULL),
  (1,14,NULL,NULL),
  (1,15,NULL,NULL),
  (1,16,NULL,NULL),
  (1,17,NULL,NULL),
  (1,18,NULL,NULL),
  (1,19,NULL,NULL),
  (1,20,NULL,NULL),
  (1,21,NULL,NULL),
  (1,22,NULL,NULL),
  (1,23,NULL,NULL),
  (1,24,NULL,NULL),
  (1,25,NULL,NULL),
  (1,26,NULL,NULL),
  (1,27,NULL,NULL),
  (1,28,NULL,NULL),
  (1,29,NULL,NULL),
  (1,30,NULL,NULL),
  (1,31,NULL,NULL),
  (1,32,NULL,NULL),
  (1,33,NULL,NULL),
  (1,34,NULL,NULL);
INSERT INTO `admin_role_permissions` (`role_id`,`permission_id`,`created_at`,`updated_at`) VALUES
  (1,1,NULL,NULL);

SET FOREIGN_KEY_CHECKS=1;
