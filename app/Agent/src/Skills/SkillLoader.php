<?php

namespace Agent\Skills;

use Agent\Config\Config;
use Agent\Core\AgentContext;
use Exception;

/**
 * 技能加载器
 * 负责技能的发现、加载、管理和执行
 */
class SkillLoader
{
    private $config;
    private $skills;
    private $skillPaths;
    private $loadedSkills;
    private $skillRegistry;

    /**
     * 构造函数
     *
     * @param Config $config 配置对象
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->skills = array(); // 已注册的技能实例
        $this->loadedSkills = array(); // 已加载的技能信息
        $this->skillRegistry = array(); // 技能注册表
        
        $this->initializeSkillPaths();
        $this->discoverSkills();
    }

    /**
     * 初始化技能路径
     */
    private function initializeSkillPaths()
    {
        $this->skillPaths = $this->config->get('skills.paths', array());
        
        // 添加默认技能目录
        $defaultPath = __DIR__ . '/../../skills';
        if (!in_array($defaultPath, $this->skillPaths)) {
            $this->skillPaths[] = $defaultPath;
        }
        
        // 确保存储目录存在
        foreach ($this->skillPaths as $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
        }
    }

    /**
     * 发现可用技能
     */
    private function discoverSkills()
    {
        foreach ($this->skillPaths as $path) {
            if (is_dir($path)) {
                $this->scanSkillDirectory($path);
            }
        }
    }

    /**
     * 注册内置技能
     */
    private function registerBuiltInSkills()
    {
        // 注册示例技能
        try {
            $weatherSkill = new \Agent\Skills\Examples\WeatherSkill();
            $this->registerSkill('builtin_weather', $weatherSkill, 'built-in');
            error_log("Registered WeatherSkill successfully");
        } catch (Exception $e) {
            error_log("Failed to register WeatherSkill: " . $e->getMessage());
        }
        
        try {
            $fileSkill = new \Agent\Skills\Examples\FileProcessingSkill();
            $this->registerSkill('builtin_file', $fileSkill, 'built-in');
            error_log("Registered FileProcessingSkill successfully");
        } catch (Exception $e) {
            error_log("Failed to register FileProcessingSkill: " . $e->getMessage());
        }
    }

    /**
     * 扫描技能目录
     *
     * @param string $directory 目录路径
     */
    private function scanSkillDirectory($directory)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->loadSkillFromFile($file->getPathname());
            }
        }
    }

    /**
     * 从文件加载技能
     *
     * @param string $filePath 文件路径
     */
    private function loadSkillFromFile($filePath)
    {
        try {
            // 获取相对路径作为技能标识
            $relativePath = $this->getRelativePath($filePath);
            $skillId = str_replace(array('/', '.php'), array('_', ''), $relativePath);
            
            // 检查是否已加载
            if (isset($this->skillRegistry[$skillId])) {
                return;
            }
            
            // 临时包含文件以获取类名
            $className = $this->extractClassName($filePath);
            if (!$className || !class_exists($className)) {
                // 尝试包含文件
                require_once $filePath;
                if (!class_exists($className)) {
                    return;
                }
            }
            
            // 检查是否继承自 Skill 基类
            if (!is_subclass_of($className, 'Agent\\Skills\\Skill')) {
                return;
            }
            
            // 创建技能实例
            $skillInstance = new $className();
            
            // 注册技能
            $this->registerSkill($skillId, $skillInstance, $filePath);
            
        } catch (Exception $e) {
            error_log("Failed to load skill from {$filePath}: " . $e->getMessage());
        }
    }

    /**
     * 提取类名
     *
     * @param string $filePath 文件路径
     * @return string|null 类名
     */
    private function extractClassName($filePath)
    {
        $content = file_get_contents($filePath);
        
        // 简单的类名提取正则表达式
        if (preg_match('/class\s+(\w+)\s+extends\s+Skill/', $content, $matches)) {
            return 'Agent\\Skills\\' . $matches[1];
        }
        
        // 尝试命名空间方式
        if (preg_match('/namespace\s+([^\s;]+);/', $content, namespaceMatch)) {
            $namespace = $namespaceMatch[1];
            if (preg_match('/class\s+(\w+)\s+extends\s+\\\\?Agent\\\\Skills\\\\Skill/', $content, classMatch)) {
                return $namespace . '\\' . $classMatch[1];
            }
        }
        
        return null;
    }

    /**
     * 获取相对路径
     *
     * @param string $filePath 完整路径
     * @return string 相对路径
     */
    private function getRelativePath($filePath)
    {
        foreach ($this->skillPaths as $basePath) {
            if (strpos($filePath, $basePath) === 0) {
                return substr($filePath, strlen($basePath) + 1);
            }
        }
        return basename($filePath);
    }

    /**
     * 注册技能
     *
     * @param string $skillId 技能ID
     * @param Skill $skillInstance 技能实例
     * @param string $filePath 文件路径
     */
    private function registerSkill($skillId, $skillInstance, $filePath)
    {
        $this->skillRegistry[$skillId] = array(
            'instance' => $skillInstance,
            'file_path' => $filePath,
            'registered_at' => time(),
            'loaded' => false
        );
        
        $this->skills[$skillInstance->getName()] = $skillInstance;
    }

    /**
     * 加载指定技能
     *
     * @param string $skillName 技能名称
     * @param AgentContext $context 上下文
     * @param array $config 配置参数
     * @return bool 是否加载成功
     */
    public function loadSkill($skillName, $context, $config = array())
    {
        // 查找技能
        $skillInstance = $this->findSkillByName($skillName);
        if (!$skillInstance) {
            throw new Exception("Skill '{$skillName}' not found");
        }
        
        $skillId = $this->getSkillIdByName($skillName);
        
        // 检查依赖
        $availableTools = $context->getAvailableTools();
        $missingDeps = $skillInstance->checkDependencies($availableTools);
        if (!empty($missingDeps)) {
            throw new Exception("Missing required tools: " . implode(', ', $missingDeps));
        }
        
        // 初始化技能
        if (!$skillInstance->initialize($context)) {
            throw new Exception("Failed to initialize skill '{$skillName}'");
        }
        
        // 更新注册信息
        if (isset($this->skillRegistry[$skillId])) {
            $this->skillRegistry[$skillId]['loaded'] = true;
            $this->skillRegistry[$skillId]['config'] = $config;
            $this->skillRegistry[$skillId]['loaded_at'] = time();
        }
        
        $this->loadedSkills[$skillName] = array(
            'skill' => $skillInstance,
            'config' => $config,
            'loaded_at' => time()
        );
        
        return true;
    }

    /**
     * 卸载技能
     *
     * @param string $skillName 技能名称
     * @param AgentContext $context 上下文
     * @return bool 是否卸载成功
     */
    public function unloadSkill($skillName, $context)
    {
        if (!isset($this->loadedSkills[$skillName])) {
            return false;
        }
        
        $skillInfo = $this->loadedSkills[$skillName];
        $skillInstance = $skillInfo['skill'];
        
        // 执行清理
        $skillInstance->cleanup($context);
        
        // 从加载列表中移除
        unset($this->loadedSkills[$skillName]);
        
        // 更新注册信息
        $skillId = $this->getSkillIdByName($skillName);
        if (isset($this->skillRegistry[$skillId])) {
            $this->skillRegistry[$skillId]['loaded'] = false;
            unset($this->skillRegistry[$skillId]['config']);
            unset($this->skillRegistry[$skillId]['loaded_at']);
        }
        
        return true;
    }

    /**
     * 执行技能
     *
     * @param string $skillName 技能名称
     * @param AgentContext $context 上下文
     * @param array $parameters 执行参数
     * @return mixed 执行结果
     */
    public function executeSkill($skillName, $context, $parameters = array())
    {
        if (!isset($this->loadedSkills[$skillName])) {
            throw new Exception("Skill '{$skillName}' is not loaded");
        }
        
        $skillInfo = $this->loadedSkills[$skillName];
        $skillInstance = $skillInfo['skill'];
        
        // 验证参数
        if (!$skillInstance->validateParameters($parameters)) {
            throw new Exception("Invalid parameters for skill '{$skillName}'");
        }
        
        // 执行技能
        return $skillInstance->execute($context, $parameters);
    }

    /**
     * 根据名称查找技能
     *
     * @param string $skillName 技能名称
     * @return Skill|null 技能实例
     */
    private function findSkillByName($skillName)
    {
        return isset($this->skills[$skillName]) ? $this->skills[$skillName] : null;
    }

    /**
     * 根据名称获取技能ID
     *
     * @param string $skillName 技能名称
     * @return string|null 技能ID
     */
    private function getSkillIdByName($skillName)
    {
        foreach ($this->skillRegistry as $skillId => $info) {
            if ($info['instance']->getName() === $skillName) {
                return $skillId;
            }
        }
        return null;
    }

    /**
     * 获取所有可用技能
     *
     * @return array 技能列表
     */
    public function getAvailableSkills()
    {
        $available = array();
        
        foreach ($this->skillRegistry as $skillId => $info) {
            $skillInstance = $info['instance'];
            $available[] = array(
                'id' => $skillId,
                'name' => $skillInstance->getName(),
                'description' => $skillInstance->getDescription(),
                'version' => $skillInstance->getVersion(),
                'author' => $skillInstance->getAuthor(),
                'enabled' => $skillInstance->isEnabled(),
                'required_tools' => $skillInstance->getRequiredTools(),
                'loaded' => $info['loaded'],
                'file_path' => $info['file_path']
            );
        }
        
        return $available;
    }

    /**
     * 获取已加载技能
     *
     * @return array 已加载技能列表
     */
    public function getLoadedSkills()
    {
        $loaded = array();
        
        foreach ($this->loadedSkills as $skillName => $info) {
            $loaded[] = array(
                'name' => $skillName,
                'loaded_at' => $info['loaded_at'],
                'config_keys' => array_keys($info['config'])
            );
        }
        
        return $loaded;
    }

    /**
     * 启用技能
     *
     * @param string $skillName 技能名称
     * @return bool 是否成功
     */
    public function enableSkill($skillName)
    {
        $skillInstance = $this->findSkillByName($skillName);
        if ($skillInstance) {
            $skillInstance->enable();
            return true;
        }
        return false;
    }

    /**
     * 禁用技能
     *
     * @param string $skillName 技能名称
     * @return bool 是否成功
     */
    public function disableSkill($skillName)
    {
        $skillInstance = $this->findSkillByName($skillName);
        if ($skillInstance) {
            $skillInstance->disable();
            return true;
        }
        return false;
    }

    /**
     * 获取技能统计信息
     *
     * @return array 统计信息
     */
    public function getStatistics()
    {
        $totalSkills = count($this->skillRegistry);
        $loadedSkills = count($this->loadedSkills);
        $enabledSkills = 0;
        
        foreach ($this->skills as $skill) {
            if ($skill->isEnabled()) {
                $enabledSkills++;
            }
        }
        
        return array(
            'total_skills' => $totalSkills,
            'loaded_skills' => $loadedSkills,
            'enabled_skills' => $enabledSkills,
            'disabled_skills' => $totalSkills - $enabledSkills,
            'skill_paths' => $this->skillPaths
        );
    }

    /**
     * 重新发现技能
     */
    public function refresh()
    {
        $this->skillRegistry = array();
        $this->skills = array();
        $this->discoverSkills();
    }

    /**
     * 获取技能路径
     *
     * @return array
     */
    public function getSkillPaths()
    {
        return $this->skillPaths;
    }

    /**
     * 添加技能路径
     *
     * @param string $path 路径
     */
    public function addSkillPath($path)
    {
        if (!in_array($path, $this->skillPaths)) {
            $this->skillPaths[] = $path;
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
        }
    }
}