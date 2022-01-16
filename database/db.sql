-- --------------------------------------------------------
-- 主机:                           198.52.100.249
-- 服务器版本:                        5.5.68-MariaDB - MariaDB Server
-- 服务器OS:                        Linux
-- HeidiSQL 版本:                  10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for tasks
CREATE DATABASE IF NOT EXISTS `tasks` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `tasks`;

-- Dumping structure for table tasks.admin_menu
CREATE TABLE IF NOT EXISTS `admin_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `uri` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table tasks.admin_operation_log
CREATE TABLE IF NOT EXISTS `admin_operation_log` (
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
) ENGINE=InnoDB AUTO_INCREMENT=893 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table tasks.admin_permissions
CREATE TABLE IF NOT EXISTS `admin_permissions` (
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

-- Data exporting was unselected.

-- Dumping structure for table tasks.admin_roles
CREATE TABLE IF NOT EXISTS `admin_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table tasks.admin_role_menu
CREATE TABLE IF NOT EXISTS `admin_role_menu` (
  `role_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_menu_role_id_menu_id_index` (`role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table tasks.admin_role_permissions
CREATE TABLE IF NOT EXISTS `admin_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_permissions_role_id_permission_id_index` (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table tasks.admin_role_users
CREATE TABLE IF NOT EXISTS `admin_role_users` (
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_role_users_role_id_user_id_index` (`role_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table tasks.admin_users
CREATE TABLE IF NOT EXISTS `admin_users` (
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

-- Data exporting was unselected.

-- Dumping structure for table tasks.admin_user_permissions
CREATE TABLE IF NOT EXISTS `admin_user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `admin_user_permissions_user_id_permission_id_index` (`user_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table tasks.articles
CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(10) unsigned NOT NULL COMMENT '订阅id',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '状态（unread/read/star/later）',
  `star_ind` int(11) NOT NULL DEFAULT '0' COMMENT '收藏数量',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章地址',
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文章首图',
  `subject` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章标题',
  `content` mediumtext COLLATE utf8mb4_unicode_ci COMMENT '文章内容',
  `published` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '文章发布时间',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `articles_feed_id_index` (`feed_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10399 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.article_marks
CREATE TABLE IF NOT EXISTS `article_marks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `article_id` int(10) unsigned NOT NULL COMMENT '文章id',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标注内容',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='文章标注表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.article_subs
CREATE TABLE IF NOT EXISTS `article_subs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(10) unsigned NOT NULL COMMENT '订阅id',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `status` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT 'unread' COMMENT '状态（unread/read/later/star）',
  `star_ind` int(11) NOT NULL DEFAULT '0' COMMENT '收藏数量',
  `article_id` int(10) unsigned NOT NULL COMMENT '文章id',
  `published` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发布时间',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `articles_feed_id_index` (`feed_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23978 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章订阅关系表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.cals
CREATE TABLE IF NOT EXISTS `cals` (
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

-- Data exporting was unselected.

-- Dumping structure for table tasks.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分类名称',
  `category_order` tinyint(4) DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分类表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.daily_summarys
CREATE TABLE IF NOT EXISTS `daily_summarys` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='日报/日总结表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.feedbacks
CREATE TABLE IF NOT EXISTS `feedbacks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT '用户id',
  `from` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '问题来源',
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='反馈表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.feeds
CREATE TABLE IF NOT EXISTS `feeds` (
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
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `feeds_category_id_index` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订阅源表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.feed_subs
CREATE TABLE IF NOT EXISTS `feed_subs` (
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
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户订阅关系表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.goals
CREATE TABLE IF NOT EXISTS `goals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '目标名称',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1进行中 2已完成 3已折叠） ',
  `deadline` timestamp NULL DEFAULT NULL COMMENT '截止时间',
  `remindtime` timestamp NULL DEFAULT NULL COMMENT '提醒时间',
  `priority` tinyint(4) NOT NULL DEFAULT '1' COMMENT '优先级（1不重要不紧急/2不重要紧急/3重要不紧急/4重要紧急）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `tasks_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='目标表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.kindle_logs
CREATE TABLE IF NOT EXISTS `kindle_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(10) unsigned NOT NULL COMMENT '类型（1测试 2定时任务）',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态（1初始化 2生成成功 3发送成功）',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件路径',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18726 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='kindle推送记录表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `migration` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table tasks.minds
CREATE TABLE IF NOT EXISTS `minds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '描述',
  `image_url` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片地址',
  `orders` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '排序',
  `parent_mind_id` int(11) DEFAULT NULL COMMENT '父类导图id',
  `copy_mind_id` int(11) DEFAULT NULL COMMENT '拷贝导图id',
  `is_root` tinyint(4) DEFAULT NULL COMMENT '是否首层',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态（1 启用 2删除）',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2021 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='思维导图表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.notes
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1私有 2公共）',
  `name` text COLLATE utf8mb4_unicode_ci COMMENT '内容',
  `record_path` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '语音地址',
  `image_path` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片地址',
  `task_id` int(11) DEFAULT NULL COMMENT '任务id',
  `pomo_id` int(11) DEFAULT NULL COMMENT '番茄id',
  `article_id` int(11) DEFAULT NULL COMMENT '文章id',
  `audit_status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `pomos_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=862 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='笔记表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.note_tag_maps
CREATE TABLE IF NOT EXISTS `note_tag_maps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL COMMENT '标签id',
  `note_id` int(11) NOT NULL COMMENT '笔记id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1启用 2删除）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=805 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='笔记标签关系表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.oauth_infos
CREATE TABLE IF NOT EXISTS `oauth_infos` (
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

-- Data exporting was unselected.

-- Dumping structure for table tasks.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `password_resets_email_index` (`email`(191)),
  KEY `password_resets_token_index` (`token`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='密码重置表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.pomos
CREATE TABLE IF NOT EXISTS `pomos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `status` tinyint(4) DEFAULT '1' COMMENT '番茄状态（1初始 2完成 3放弃）',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '番茄内容',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '番茄开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '番茄结束时间',
  `rest_status` tinyint(4) DEFAULT '1' COMMENT '休息状态（1初始 2完成 3放弃）',
  `rest_start_time` timestamp NULL DEFAULT NULL COMMENT '休息开始时间',
  `rest_end_time` timestamp NULL DEFAULT NULL COMMENT '休息结束时间',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `pomos_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=578 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='番茄表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `day_pomo_goal` tinyint(4) DEFAULT '8' COMMENT '日番茄数',
  `week_pomo_goal` int(11) DEFAULT '40' COMMENT '周番茄数',
  `month_pomo_goal` int(11) DEFAULT '160' COMMENT '月番茄数',
  `pomo_time` int(11) NOT NULL DEFAULT '25' COMMENT '番茄时间',
  `pomo_rest_time` int(11) NOT NULL DEFAULT '5' COMMENT '番茄休息时间',
  `is_start_kindle` tinyint(4) DEFAULT '0' COMMENT '是否开启kindle推送（0 未开启 1开启）',
  `kindle_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'kindle推送地址',
  `with_image_push` tinyint(4) DEFAULT '0' COMMENT 'kindle是否带图推送',
  `cal_token` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '任务日历token',
  `ifttt_notify` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ifttt通知token',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=373 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='设置表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.statistics
CREATE TABLE IF NOT EXISTS `statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `date_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日期类型（day 按日）',
  `data_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据类型（article/task/note/mind/pomo）',
  `total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数量',
  `statistic_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '统计日期',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=741 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='统计表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.tags
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标签内容',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1启用 2禁用）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='标签表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.tasks
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '待办内容',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1 进行中 2已完成 3已折叠）',
  `deadline` timestamp NULL DEFAULT NULL COMMENT '截止时间',
  `remindtime` timestamp NULL DEFAULT NULL COMMENT '提醒时间',
  `priority` tinyint(4) NOT NULL DEFAULT '1' COMMENT '优先级（1不重要不紧急/2不重要紧急/3重要不紧急/4重要紧急）',
  `is_top` tinyint(4) DEFAULT '0' COMMENT '是否置顶',
  `mode` tinyint(4) DEFAULT '1' COMMENT '类型（1工作 2生活）',
  `parent_task_id` int(11) DEFAULT NULL COMMENT '父类任务id',
  `goal_id` int(11) DEFAULT NULL COMMENT '目标id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `tasks_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=429 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='待办任务表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.task_tag_maps
CREATE TABLE IF NOT EXISTS `task_tag_maps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL COMMENT '标签id',
  `task_id` int(11) NOT NULL COMMENT '任务id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1 启用 2禁用）',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='任务标签关系表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.things
CREATE TABLE IF NOT EXISTS `things` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `start_time` datetime DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '事情内容',
  `type` smallint(6) DEFAULT '1' COMMENT '类型（1 主动记事 2待办任务 3番茄记录） ',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=554 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='事情记录表';

-- Data exporting was unselected.

-- Dumping structure for table tasks.thirds
CREATE TABLE IF NOT EXISTS `thirds` (
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

-- Data exporting was unselected.

-- Dumping structure for table tasks.users
CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB AUTO_INCREMENT=372 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- Data exporting was unselected.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

-- Dumping data for table tasks.admin_menu: ~26 rows (大约)
/*!40000 ALTER TABLE `admin_menu` DISABLE KEYS */;
INSERT INTO `admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `created_at`, `updated_at`) VALUES
  (1, 0, 1, 'Index', 'fa-bar-chart', '/', NULL, NULL),
  (2, 0, 2, 'Admin', 'fa-tasks', '', NULL, NULL),
  (3, 2, 3, 'Users', 'fa-users', 'auth/users', NULL, NULL),
  (4, 2, 4, 'Roles', 'fa-user', 'auth/roles', NULL, NULL),
  (5, 2, 5, 'Permission', 'fa-ban', 'auth/permissions', NULL, NULL),
  (6, 2, 6, 'Menu', 'fa-bars', 'auth/menu', NULL, NULL),
  (7, 2, 7, 'Operation log', 'fa-history', 'auth/logs', NULL, NULL),
  (8, 0, 8, 'User Manager', 'fa-user-md', 'demo/users', '2018-01-06 07:21:22', '2018-01-07 00:09:29'),
  (9, 0, 10, 'Feed Manager', 'fa-feed', 'demo/feeds', '2018-01-06 07:35:53', '2018-01-07 00:09:29'),
  (10, 8, 9, 'User Info', 'fa-bars', 'users', '2018-01-06 23:53:40', '2018-01-07 00:09:29'),
  (11, 9, 12, 'Feed Info', 'fa-bars', 'feeds', '2018-01-06 23:54:05', '2018-01-07 00:09:29'),
  (12, 9, 11, 'FeedSub Info', 'fa-bars', 'feedsubs', '2018-01-06 23:54:34', '2018-01-07 00:09:29'),
  (13, 9, 14, 'Article Info', 'fa-bars', 'articles', '2018-01-07 01:11:11', '2018-01-07 01:12:03'),
  (14, 9, 13, 'ArticleMark Info', 'fa-bars', 'articlemarks', '2018-01-07 01:11:54', '2018-01-07 01:12:03'),
  (15, 26, 25, 'Feed Back', 'fa-bars', 'feedbacks', '2018-01-07 01:12:28', '2018-01-07 01:18:43'),
  (16, 25, 21, 'Goal Info', 'fa-bars', 'goals', '2018-01-07 01:12:44', '2018-01-07 01:18:43'),
  (17, 25, 22, 'Category Info', 'fa-bars', 'categorys', '2018-01-07 01:12:58', '2018-01-07 01:18:54'),
  (18, 26, 26, 'Kindle Log', 'fa-bars', 'kindlelogs', '2018-01-07 01:13:16', '2018-01-07 01:18:43'),
  (19, 25, 19, 'Mind Info', 'fa-bars', 'minds', '2018-01-07 01:13:35', '2018-01-07 01:18:43'),
  (20, 25, 20, 'Note Info', 'fa-bars', 'notes', '2018-01-07 01:13:48', '2018-01-07 01:18:43'),
  (21, 25, 17, 'Pomo Info', 'fa-bars', 'pomos', '2018-01-07 01:14:03', '2018-01-07 01:18:43'),
  (22, 26, 24, 'Setting Info', 'fa-bars', 'settings', '2018-01-07 01:14:19', '2018-01-07 01:18:43'),
  (23, 25, 18, 'Task Info', 'fa-bars', 'tasks', '2018-01-07 01:14:40', '2018-01-07 01:18:43'),
  (24, 25, 16, 'Things Info', 'fa-bars', 'things', '2018-01-07 01:14:58', '2018-01-07 01:18:43'),
  (25, 0, 15, 'GTD Manager', 'fa-tasks', NULL, '2018-01-07 01:15:12', '2018-01-07 01:18:43'),
  (26, 0, 23, 'System Manager', 'fa-comments-o', NULL, '2018-01-07 01:16:40', '2018-01-07 01:18:43');
/*!40000 ALTER TABLE `admin_menu` ENABLE KEYS */;

-- Dumping data for table tasks.admin_permissions: ~5 rows (大约)
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` (`id`, `name`, `slug`, `http_method`, `http_path`, `created_at`, `updated_at`) VALUES
  (1, 'All permission', '*', '', '*', NULL, NULL),
  (2, 'Dashboard', 'dashboard', 'GET', '/', NULL, NULL),
  (3, 'Login', 'auth.login', '', '/auth/login\r\n/auth/logout', NULL, NULL),
  (4, 'User setting', 'auth.setting', 'GET,PUT', '/auth/setting', NULL, NULL),
  (5, 'Auth management', 'auth.management', '', '/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs', NULL, NULL);
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;

-- Dumping data for table tasks.admin_roles: ~1 rows (大约)
/*!40000 ALTER TABLE `admin_roles` DISABLE KEYS */;
INSERT INTO `admin_roles` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
  (1, 'Administrator', 'administrator', '2018-01-06 00:15:18', '2018-01-06 00:15:18');
/*!40000 ALTER TABLE `admin_roles` ENABLE KEYS */;

-- Dumping data for table tasks.admin_role_menu: ~20 rows (大约)
/*!40000 ALTER TABLE `admin_role_menu` DISABLE KEYS */;
INSERT INTO `admin_role_menu` (`role_id`, `menu_id`, `created_at`, `updated_at`) VALUES
  (1, 2, NULL, NULL),
  (1, 8, NULL, NULL),
  (1, 9, NULL, NULL),
  (1, 10, NULL, NULL),
  (1, 11, NULL, NULL),
  (1, 12, NULL, NULL),
  (1, 13, NULL, NULL),
  (1, 14, NULL, NULL),
  (1, 15, NULL, NULL),
  (1, 16, NULL, NULL),
  (1, 17, NULL, NULL),
  (1, 18, NULL, NULL),
  (1, 19, NULL, NULL),
  (1, 20, NULL, NULL),
  (1, 21, NULL, NULL),
  (1, 22, NULL, NULL),
  (1, 23, NULL, NULL),
  (1, 24, NULL, NULL),
  (1, 25, NULL, NULL),
  (1, 26, NULL, NULL);
/*!40000 ALTER TABLE `admin_role_menu` ENABLE KEYS */;

-- Dumping data for table tasks.admin_role_permissions: ~1 rows (大约)
/*!40000 ALTER TABLE `admin_role_permissions` DISABLE KEYS */;
INSERT INTO `admin_role_permissions` (`role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
  (1, 1, NULL, NULL);
/*!40000 ALTER TABLE `admin_role_permissions` ENABLE KEYS */;

-- Dumping data for table tasks.admin_role_users: ~1 rows (大约)
/*!40000 ALTER TABLE `admin_role_users` DISABLE KEYS */;
INSERT INTO `admin_role_users` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES
  (1, 1, NULL, NULL);
/*!40000 ALTER TABLE `admin_role_users` ENABLE KEYS */;

-- Dumping data for table tasks.admin_users: ~1 rows (大约)
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` (`id`, `username`, `password`, `name`, `avatar`, `remember_token`, `created_at`, `updated_at`, `last_login`) VALUES
  (1, 'admin', 'execute cmd genarate password:** php artisan admin:install **', 'Administrator', 'images/qrcode20170921114041.png', '', '2018-01-06 00:15:18', '2018-01-06 00:15:18', '2018-01-06 00:15:18');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;

