-- 创建LLM供应商表
CREATE TABLE IF NOT EXISTS `llm_providers` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL COMMENT '供应商名称，如OpenAI、Anthropic等',
    `slug` varchar(50) NOT NULL COMMENT '唯一标识符，如openai、anthropic',
    `description` text COMMENT '供应商描述',
    `base_url` varchar(255) DEFAULT NULL COMMENT 'API基础URL',
    `api_type` varchar(50) NOT NULL COMMENT 'API类型：openai、anthropic、custom',
    `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
    `priority` int DEFAULT '0' COMMENT '优先级，数字越大优先级越高',
    `config_schema` json DEFAULT NULL COMMENT '配置项JSON Schema',
    `rate_limit_per_minute` int DEFAULT NULL COMMENT '每分钟请求限制',
    `concurrent_limit` int DEFAULT '10' COMMENT '并发限制',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `llm_providers_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建模型表
CREATE TABLE IF NOT EXISTS `llm_models` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `provider_id` bigint unsigned NOT NULL COMMENT '供应商ID',
    `name` varchar(100) NOT NULL COMMENT '模型名称，如gpt-4-turbo',
    `display_name` varchar(100) DEFAULT NULL COMMENT '显示名称',
    `model_type` varchar(50) NOT NULL DEFAULT 'chat' COMMENT '模型类型：chat/completion/embedding/image',
    `context_length` int DEFAULT NULL COMMENT '上下文长度',
    `max_tokens` int DEFAULT NULL COMMENT '最大输出tokens',
    `input_price_per_1k` decimal(10,6) DEFAULT NULL COMMENT '输入价格/1K tokens',
    `output_price_per_1k` decimal(10,6) DEFAULT NULL COMMENT '输出价格/1K tokens',
    `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
    `capabilities` json DEFAULT NULL COMMENT '能力配置：vision, json_mode等',
    `sort_order` int DEFAULT '0' COMMENT '排序',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `llm_models_provider_name_unique` (`provider_id`,`name`),
    KEY `llm_models_model_type_index` (`model_type`),
    KEY `llm_models_is_active_index` (`is_active`),
    CONSTRAINT `llm_models_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `llm_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建供应商凭据表（支持多账号）
CREATE TABLE IF NOT EXISTS `llm_provider_credentials` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `provider_id` bigint unsigned NOT NULL,
    `name` varchar(100) NOT NULL COMMENT '凭据名称，用于区分不同账号',
    `api_key` text NOT NULL COMMENT '加密存储的API Key',
    `config` json DEFAULT NULL COMMENT '额外配置，如organization_id等',
    `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为默认凭据',
    `usage_count` int NOT NULL DEFAULT '0' COMMENT '使用次数',
    `last_used_at` timestamp NULL DEFAULT NULL,
    `quota_limit` int DEFAULT NULL COMMENT '配额限制',
    `quota_used` int DEFAULT '0' COMMENT '已使用配额',
    `quota_reset_at` timestamp NULL DEFAULT NULL COMMENT '配额重置时间',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `llm_provider_credentials_provider_id_index` (`provider_id`),
    KEY `llm_provider_credentials_is_active_index` (`is_active`),
    CONSTRAINT `llm_provider_credentials_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `llm_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建使用记录表
CREATE TABLE IF NOT EXISTS `llm_usage_logs` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `provider_id` bigint unsigned NOT NULL,
    `model_id` bigint unsigned NOT NULL,
    `credential_id` bigint unsigned NOT NULL,
    `user_id` bigint unsigned DEFAULT NULL COMMENT '关联用户（如果项目需要）',
    `input_tokens` int DEFAULT NULL,
    `output_tokens` int DEFAULT NULL,
    `total_tokens` int DEFAULT NULL,
    `cost` decimal(10,6) DEFAULT NULL COMMENT '本次调用成本',
    `request_time` decimal(8,3) DEFAULT NULL COMMENT '请求耗时（秒）',
    `status` varchar(20) DEFAULT 'success' COMMENT 'success/failed/rate_limited',
    `error_message` text,
    `request_data` json DEFAULT NULL COMMENT '请求数据（可脱敏存储）',
    `response_data` json DEFAULT NULL COMMENT '响应数据（可脱敏存储）',
    `created_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `llm_usage_logs_provider_id_index` (`provider_id`),
    KEY `llm_usage_logs_model_id_index` (`model_id`),
    KEY `llm_usage_logs_credential_id_index` (`credential_id`),
    KEY `llm_usage_logs_user_id_index` (`user_id`),
    KEY `llm_usage_logs_created_at_index` (`created_at`),
    CONSTRAINT `llm_usage_logs_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `llm_providers` (`id`),
    CONSTRAINT `llm_usage_logs_model_id_foreign` FOREIGN KEY (`model_id`) REFERENCES `llm_models` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;