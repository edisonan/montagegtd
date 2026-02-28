<?php

return [
    // LLM 配置
    'llm' => [
        'default_provider' => env('LLM_DEFAULT_PROVIDER', 'openai'),
        'providers' => [
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
                'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-3.5-turbo'),
                'temperature' => env('OPENAI_TEMPERATURE', 0.7),
                'max_tokens' => env('OPENAI_MAX_TOKENS', 1500),
            ],
            'anthropic' => [
                'api_key' => env('ANTHROPIC_API_KEY'),
                'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
                'default_model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-3-haiku'),
                'temperature' => env('ANTHROPIC_TEMPERATURE', 0.7),
                'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 1500),
            ],
            'nvidia' => [
                'api_key' => env('NVIDIA_API_KEY'),
                'base_url' => env('NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
                'default_model' => env('NVIDIA_DEFAULT_MODEL', 'minimaxai/minimax-m2.1'),
                'temperature' => env('NVIDIA_TEMPERATURE', 0.7),
                'max_tokens' => env('NVIDIA_MAX_TOKENS', 1500),
            ],
        ],
    ],

    // 工具配置
    'tools' => [
        'enabled' => [
            'file_read',
            'file_write',
            'bash_executor',
            'user_input',
            'web_search',
        ],
        'timeout' => 30, // 工具执行超时时间（秒）
        'max_retries' => 3, // 最大重试次数
    ],

    // 会话配置
    'session' => [
        'storage_path' => sys_get_temp_dir() . '/agent_sessions',
        'max_sessions' => 1000,
        'cleanup_threshold' => 0.8,
        'default_ttl' => 3600, // 1小时
    ],

    // 记忆配置
    'memory' => [
        'storage_path' => sys_get_temp_dir() . '/agent_memories',
        'max_memories' => 1000,
        'cleanup_threshold' => 0.8,
        'default_importance' => 0.5,
    ],

    // 断点配置
    'checkpoint' => [
        'storage_path' => sys_get_temp_dir() . '/agent_checkpoints',
        'max_checkpoints_per_session' => 50,
        'cleanup_threshold' => 0.8,
        'auto_save_interval' => 10, // 自动保存间隔（步数）
    ],

    // Hook 配置
    'hooks' => [
        'enabled' => [
            'logging_hook',
            'performance_hook',
        ],
        'directories' => [
            __DIR__ . '/../src/Hooks/Examples',
        ],
    ],

    // 技能配置
    'skills' => [
        'paths' => [
            __DIR__ . '/../src/Skills/Examples',
            __DIR__ . '/../skills',
        ],
        'autoload' => true,
        'max_execution_time' => 30, // 技能最大执行时间（秒）
    ],

    // 日志配置
    'logging' => [
        'enabled' => true,
        'level' => 'info',
        'file_path' => sys_get_temp_dir() . '/agent.log',
    ],

    // 调试配置
    'debug' => [
        'enabled' => env('AGENT_DEBUG', false),
        'verbose' => env('AGENT_VERBOSE', false),
    ],

    // 存储配置
    'storage' => [
        'default' => env('AGENT_STORAGE_DEFAULT', 'file'),
        
        // 文件存储配置
        'file' => [
            'base_path' => env('AGENT_STORAGE_FILE_PATH', sys_get_temp_dir() . '/agent_storage'),
            'extension' => '.json'
        ],
        
        // 数据库存储配置
        'database' => [
            'dsn' => env('AGENT_STORAGE_DB_DSN'),
            'username' => env('AGENT_STORAGE_DB_USERNAME'),
            'password' => env('AGENT_STORAGE_DB_PASSWORD'),
            'table_name' => 'agent_storage'
        ]
    ],
];