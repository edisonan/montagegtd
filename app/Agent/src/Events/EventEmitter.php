<?php

namespace App\Agent\Events;

/**
 * 事件发射器类
 * 
 * 负责事件的注册、分发和管理。
 */
class EventEmitter
{
    /**
     * 事件处理器映射
     *
     * @var array
     */
    private array $handlers = [];

    /**
     * 全局事件处理器
     *
     * @var array
     */
    private array $globalHandlers = [];

    /**
     * 注册事件处理器
     *
     * @param string $event 事件名称
     * @param callable $handler 处理器函数
     * @return void
     */
    public function on(string $event, callable $handler): void
    {
        if (!isset($this->handlers[$event])) {
            $this->handlers[$event] = [];
        }
        $this->handlers[$event][] = $handler;
    }

    /**
     * 注销事件处理器
     *
     * @param string $event 事件名称
     * @param callable $handler 处理器函数
     * @return void
     */
    public function off(string $event, callable $handler): void
    {
        if (isset($this->handlers[$event])) {
            $key = array_search($handler, $this->handlers[$event], true);
            if ($key !== false) {
                unset($this->handlers[$event][$key]);
            }
        }
    }

    /**
     * 注册全局事件处理器（接收所有事件）
     *
     * @param callable $handler 处理器函数
     * @return void
     */
    public function onAll(callable $handler): void
    {
        $this->globalHandlers[] = $handler;
    }

    /**
     * 注销全局事件处理器
     *
     * @param callable $handler 处理器函数
     * @return void
     */
    public function offAll(callable $handler): void
    {
        $key = array_search($handler, $this->globalHandlers, true);
        if ($key !== false) {
            unset($this->globalHandlers[$key]);
        }
    }

    /**
     * 发射事件
     *
     * @param string $event 事件名称
     * @param array $data 事件数据
     * @return void
     */
    public function emit(string $event, array $data = []): void
    {
        // 执行全局处理器
        foreach ($this->globalHandlers as $handler) {
            $this->callHandler($handler, $event, $data);
        }

        // 执行特定事件处理器
        if (isset($this->handlers[$event])) {
            foreach ($this->handlers[$event] as $handler) {
                $this->callHandler($handler, $event, $data);
            }
        }
    }

    /**
     * 调用处理器函数
     *
     * @param callable $handler 处理器
     * @param string $event 事件名称
     * @param array $data 事件数据
     * @return void
     */
    private function callHandler(callable $handler, string $event, array $data): void
    {
        try {
            $handler($event, $data);
        } catch (\Exception $e) {
            // 记录错误但不中断执行
            error_log("Event handler error for event '{$event}': " . $e->getMessage());
        }
    }

    /**
     * 检查是否有特定事件的处理器
     *
     * @param string $event 事件名称
     * @return bool
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->handlers[$event]) && !empty($this->handlers[$event]);
    }

    /**
     * 获取特定事件的处理器数量
     *
     * @param string $event 事件名称
     * @return int
     */
    public function listenerCount(string $event): int
    {
        return isset($this->handlers[$event]) ? count($this->handlers[$event]) : 0;
    }

    /**
     * 获取所有事件名称
     *
     * @return array
     */
    public function eventNames(): array
    {
        return array_keys($this->handlers);
    }

    /**
     * 清空所有事件处理器
     *
     * @return void
     */
    public function removeAllListeners(): void
    {
        $this->handlers = [];
        $this->globalHandlers = [];
    }

    /**
     * 清空特定事件的处理器
     *
     * @param string $event 事件名称
     * @return void
     */
    public function removeAllListenersFor(string $event): void
    {
        unset($this->handlers[$event]);
    }

    /**
     * 一次性事件处理器（只执行一次）
     *
     * @param string $event 事件名称
     * @param callable $handler 处理器函数
     * @return void
     */
    public function once(string $event, callable $handler): void
    {
        $wrapper = function ($eventName, $data) use (&$wrapper, $handler, $event) {
            $this->off($event, $wrapper);
            $handler($eventName, $data);
        };
        
        $this->on($event, $wrapper);
    }

    /**
     * 获取事件处理器的详细信息（用于调试）
     *
     * @return array
     */
    public function getListenerInfo(): array
    {
        $info = [
            'global_handlers' => count($this->globalHandlers),
            'events' => []
        ];

        foreach ($this->handlers as $event => $handlers) {
            $info['events'][$event] = [
                'count' => count($handlers),
                'handlers' => array_map(function ($handler) {
                    if (is_array($handler)) {
                        if (is_object($handler[0])) {
                            return get_class($handler[0]) . '::' . $handler[1];
                        }
                        return $handler[0] . '::' . $handler[1];
                    } elseif (is_string($handler)) {
                        return $handler;
                    } elseif ($handler instanceof \Closure) {
                        return 'Closure';
                    }
                    return 'Unknown';
                }, $handlers)
            ];
        }

        return $info;
    }
}