<?php

namespace App\Agent\Core;

use Dotenv\Dotenv;
use InvalidArgumentException;

/**
 * 配置管理类
 * 
 * 负责加载和管理框架的所有配置项，支持环境变量、.env 文件和数组配置。
 * 配置项优先级：数组参数 > 环境变量 > .env 文件 > 默认值
 */
class Config
{
    /**
     * 配置数据
     *
     * @var array
     */
    private array $config = [];

    /**
     * 默认配置
     *
     * @var array
     */
    private array $defaults = [
        // 项目元数据
        'PROJECT_NAME' => 'PHP Agent Framework',
        'VERSION' => '0.1.0',
        'DESCRIPTION' => 'AI Agent with tool execution capabilities',
        'DEBUG' => false,

        // LLM 配置
        'LLM_API_KEY' => '',
        'LLM_API_BASE' => '',
        'LLM_MODEL' => 'openai/gpt-4',

        // Agent 配置
        'AGENT_MAX_STEPS' => 50,
        'AGENT_WORKSPACE_DIR' => './workspace',

        // 技能配置
        'ENABLE_SKILLS' => true,
        'SKILLS_DIR' => './skills',

        // 会话配置
        'ENABLE_SESSION' => true,
        'SESSION_STORAGE_PATH' => '~/.php-agent/sessions.json',
        'SESSION_MAX_AGE_DAYS' => 7,

        // 记忆配置
        'ENABLE_MEMORY' => true,
        'MEMORY_BASE_DIR' => './.agent_memories',

        // 日志配置
        'LOG_LEVEL' => 'info',
        'LOG_FILE' => './logs/agent.log',
    ];

    /**
     * 构造函数
     *
     * @param array $config 配置数组
     * @param string|null $envPath .env 文件路径
     */
    public function __construct(array $config = [], ?string $envPath = null)
    {
        // 加载 .env 文件
        $this->loadEnv($envPath ?? dirname(__DIR__, 2));

        // 合并配置：数组参数 > 环境变量 > 默认值
        $this->config = array_merge(
            $this->defaults,
            $this->loadFromEnvironment(),
            $config
        );

        // 验证必要配置
        $this->validateRequired();
        
        // 标准化配置
        $this->normalizeConfig();
    }

    /**
     * 加载 .env 文件
     *
     * @param string $path 路径
     * @return void
     */
    private function loadEnv(string $path): void
    {
        if (file_exists($path . '/.env')) {
            $dotenv = Dotenv::createImmutable($path);
            $dotenv->load();
        }
    }

    /**
     * 从环境变量加载配置
     *
     * @return array
     */
    private function loadFromEnvironment(): array
    {
        $config = [];
        $keys = array_keys($this->defaults);

        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $config[$key] = $this->parseValue($value, $key);
            }
        }

        return $config;
    }

    /**
     * 解析配置值类型
     *
     * @param string $value 原始值
     * @param string $key 配置键
     * @return mixed 解析后的值
     */
    private function parseValue(string $value, string $key)
    {
        // 布尔值
        if (in_array(strtolower($value), ['true', 'false'])) {
            return strtolower($value) === 'true';
        }

        // 数字
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }

        // 数组 (逗号分隔)
        if (str_ends_with($key, '_ORIGINS') || str_ends_with($key, '_DIRS')) {
            return array_map('trim', explode(',', $value));
        }

        return $value;
    }

    /**
     * 验证必要配置项
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateRequired(): void
    {
        $required = ['PROJECT_NAME', 'VERSION'];
        
        foreach ($required as $key) {
            if (empty($this->config[$key])) {
                throw new InvalidArgumentException("Required configuration '{$key}' is missing");
            }
        }
    }

    /**
     * 标准化配置值
     *
     * @return void
     */
    private function normalizeConfig(): void
    {
        // 标准化模型名称格式
        if (!empty($this->config['LLM_MODEL'])) {
            $this->config['LLM_MODEL'] = $this->standardizeModelName($this->config['LLM_MODEL']);
        }

        // 确保工作目录存在
        if (!empty($this->config['AGENT_WORKSPACE_DIR'])) {
            $workspaceDir = $this->config['AGENT_WORKSPACE_DIR'];
            if (!is_dir($workspaceDir)) {
                mkdir($workspaceDir, 0755, true);
            }
            $this->config['AGENT_WORKSPACE_DIR'] = realpath($workspaceDir);
        }

        // 展开家目录路径
        if (!empty($this->config['SESSION_STORAGE_PATH'])) {
            $this->config['SESSION_STORAGE_PATH'] = $this->expandHomeDir($this->config['SESSION_STORAGE_PATH']);
        }

        if (!empty($this->config['MEMORY_BASE_DIR'])) {
            $this->config['MEMORY_BASE_DIR'] = $this->expandHomeDir($this->config['MEMORY_BASE_DIR']);
        }
    }

    /**
     * 标准化模型名称格式为 provider/model
     *
     * @param string $modelName 模型名称
     * @return string 标准化后的模型名称
     */
    private function standardizeModelName(string $modelName): string
    {
        $modelName = trim($modelName);

        // 转换冒号格式为斜杠格式
        if (strpos($modelName, ':') !== false && strpos($modelName, '/') === false) {
            $modelName = str_replace(':', '/', $modelName);
        }

        // 如果已经有提供商标识，直接返回
        if (strpos($modelName, '/') !== false) {
            return $modelName;
        }

        // 自动检测提供商
        $lowerModel = strtolower($modelName);

        if (strpos($lowerModel, 'claude') !== false) {
            return "anthropic/{$modelName}";
        } elseif (strpos($lowerModel, 'gpt') !== false || str_starts_with($lowerModel, 'o1') || str_starts_with($lowerModel, 'o3')) {
            return "openai/{$modelName}";
        } elseif (strpos($lowerModel, 'gemini') !== false) {
            return "google/{$modelName}";
        } elseif (strpos($lowerModel, 'mistral') !== false) {
            return "mistral/{$modelName}";
        } elseif (strpos($lowerModel, 'llama') !== false) {
            return "together_ai/{$modelName}";
        } elseif (strpos($lowerModel, 'qwen') !== false || strpos($lowerModel, 'deepseek') !== false) {
            return "openai/{$modelName}";
        } else {
            // 默认使用 openai (适用于自定义端点)
            return "openai/{$modelName}";
        }
    }

    /**
     * 展开家目录路径 (~ -> /home/user)
     *
     * @param string $path 路径
     * @return string 展开后的路径
     */
    private function expandHomeDir(string $path): string
    {
        if (str_starts_with($path, '~/')) {
            $homeDir = getenv('HOME') ?: getenv('USERPROFILE');
            if ($homeDir) {
                return $homeDir . substr($path, 1);
            }
        }
        return $path;
    }

    /**
     * 获取配置项
     *
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed 配置值
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * 设置配置项
     *
     * @param string $key 配置键
     * @param mixed $value 配置值
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * 检查配置项是否存在
     *
     * @param string $key 配置键
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }

    /**
     * 获取所有配置
     *
     * @return array
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * 获取 LLM 相关配置
     *
     * @return array
     */
    public function getLlmConfig(): array
    {
        return [
            'api_key' => $this->get('LLM_API_KEY'),
            'api_base' => $this->get('LLM_API_BASE'),
            'model' => $this->get('LLM_MODEL'),
        ];
    }

    /**
     * 获取 Agent 相关配置
     *
     * @return array
     */
    public function getAgentConfig(): array
    {
        return [
            'max_steps' => $this->get('AGENT_MAX_STEPS'),
            'workspace_dir' => $this->get('AGENT_WORKSPACE_DIR'),
        ];
    }

    /**
     * 获取会话相关配置
     *
     * @return array
     */
    public function getSessionConfig(): array
    {
        return [
            'enabled' => $this->get('ENABLE_SESSION'),
            'storage_path' => $this->get('SESSION_STORAGE_PATH'),
            'max_age_days' => $this->get('SESSION_MAX_AGE_DAYS'),
        ];
    }

    /**
     * 魔术方法：允许通过属性访问配置
     *
     * @param string $key 配置键
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * 魔术方法：检查属性是否存在
     *
     * @param string $key 配置键
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }
}