<?php

namespace Agent\Skills\Examples;

use Agent\Skills\Skill;
use Agent\Core\AgentContext;

/**
 * 天气查询技能示例
 * 演示如何创建一个简单的技能
 */
class WeatherSkill extends Skill
{
    public function __construct()
    {
        parent::__construct(
            'weather_query',
            '查询天气信息的技能',
            array(
                'api_key' => '',
                'default_city' => '北京'
            )
        );
        
        $this->setVersion('1.0.0');
        $this->setAuthor('Agent Framework Team');
        $this->setRequiredTools(array('web_search'));
    }

    public function execute($context, $parameters = array())
    {
        $this->log("开始执行天气查询技能", 'info');
        
        // 获取城市参数
        $city = isset($parameters['city']) ? $parameters['city'] : $this->getConfig('default_city');
        
        if (empty($city)) {
            throw new \Exception("未指定城市参数");
        }
        
        // 使用 Web 搜索工具查询天气
        $searchQuery = "{$city} 天气预报";
        $toolExecutor = $context->getToolExecutor();
        
        try {
            $result = $toolExecutor->execute('web_search', array(
                'query' => $searchQuery,
                'max_results' => 3
            ));
            
            // 处理搜索结果
            $weatherInfo = $this->parseWeatherInfo($result, $city);
            
            $this->log("天气查询完成", 'info');
            
            return array(
                'city' => $city,
                'weather' => $weatherInfo,
                'source' => 'web_search',
                'timestamp' => time()
            );
            
        } catch (\Exception $e) {
            $this->log("天气查询失败: " . $e->getMessage(), 'error');
            throw $e;
        }
    }

    private function parseWeatherInfo($searchResults, $city)
    {
        // 简单的天气信息提取
        $info = array(
            'temperature' => '未知',
            'condition' => '未知',
            'humidity' => '未知'
        );
        
        if (isset($searchResults['results']) && is_array($searchResults['results'])) {
            foreach ($searchResults['results'] as $result) {
                $content = isset($result['content']) ? $result['content'] : '';
                
                // 简单的温度提取（示例）
                if (preg_match('/(\d+)°C/', $content, $matches)) {
                    $info['temperature'] = $matches[1] . '°C';
                }
                
                // 简单的天气状况提取
                $conditions = array('晴', '阴', '雨', '雪', '多云');
                foreach ($conditions as $condition) {
                    if (strpos($content, $condition) !== false) {
                        $info['condition'] = $condition;
                        break;
                    }
                }
            }
        }
        
        return $info;
    }

    public function validateParameters($parameters)
    {
        if (!is_array($parameters)) {
            return false;
        }
        
        // 城市参数可选，但如果提供必须是字符串
        if (isset($parameters['city']) && !is_string($parameters['city'])) {
            return false;
        }
        
        return true;
    }

    public function getUsageInstructions()
    {
        return "使用方法: \n" .
               "参数: city (可选) - 要查询的城市名称\n" .
               "示例: {'city': '上海'}";
    }
}