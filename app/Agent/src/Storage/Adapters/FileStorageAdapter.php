<?php

namespace App\Agent\Storage\Adapters;

use App\Agent\Storage\StorageAdapterInterface;

/**
 * 文件系统存储适配器
 * 默认的存储方式，使用本地文件系统
 */
class FileStorageAdapter implements StorageAdapterInterface
{
    private $basePath;
    private $fileExtension;

    public function __construct($basePath = null, $fileExtension = '.json')
    {
        $this->basePath = $basePath ?? sys_get_temp_dir() . '/agent_storage';
        $this->fileExtension = $fileExtension;
        
        // 确保存储目录存在
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    public function save($key, $data, $ttl = null)
    {
        try {
            $filePath = $this->getKeyPath($key);
            $directory = dirname($filePath);
            
            // 确保目录存在
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // 准备存储数据
            $storageData = [
                'data' => $data,
                'created_at' => time(),
                'expires_at' => $ttl ? time() + $ttl : null
            ];
            
            // 原子写入
            $tempFile = $filePath . '.tmp';
            $jsonData = json_encode($storageData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            if (file_put_contents($tempFile, $jsonData) !== false) {
                rename($tempFile, $filePath);
                return true;
            }
            
            // 清理临时文件
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("FileStorage save error: " . $e->getMessage());
            return false;
        }
    }

    public function load($key)
    {
        try {
            $filePath = $this->getKeyPath($key);
            
            if (!file_exists($filePath)) {
                return null;
            }
            
            $content = file_get_contents($filePath);
            $storageData = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
            
            // 检查过期时间
            if (isset($storageData['expires_at']) && $storageData['expires_at'] !== null) {
                if (time() > $storageData['expires_at']) {
                    $this->delete($key); // 自动清理过期数据
                    return null;
                }
            }
            
            return $storageData['data'] ?? null;
            
        } catch (\Exception $e) {
            error_log("FileStorage load error: " . $e->getMessage());
            return null;
        }
    }

    public function delete($key)
    {
        try {
            $filePath = $this->getKeyPath($key);
            
            if (file_exists($filePath)) {
                return unlink($filePath);
            }
            
            return true; // 文件不存在也算删除成功
            
        } catch (\Exception $e) {
            error_log("FileStorage delete error: " . $e->getMessage());
            return false;
        }
    }

    public function exists($key)
    {
        $filePath = $this->getKeyPath($key);
        return file_exists($filePath) && $this->load($key) !== null;
    }

    public function keys()
    {
        $keys = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->basePath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === ltrim($this->fileExtension, '.')) {
                $relativePath = substr($file->getPathname(), strlen($this->basePath) + 1);
                $key = str_replace($this->fileExtension, '', $relativePath);
                $key = str_replace(DIRECTORY_SEPARATOR, '.', $key);
                
                // 检查是否过期
                if ($this->load($key) !== null) {
                    $keys[] = $key;
                }
            }
        }
        
        return $keys;
    }

    public function clear()
    {
        try {
            $this->removeDirectory($this->basePath);
            mkdir($this->basePath, 0755, true);
            return true;
        } catch (\Exception $e) {
            error_log("FileStorage clear error: " . $e->getMessage());
            return false;
        }
    }

    public function getStats()
    {
        $fileCount = 0;
        $totalSize = 0;
        
        if (is_dir($this->basePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->basePath)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $fileCount++;
                    $totalSize += $file->getSize();
                }
            }
        }
        
        return [
            'adapter' => $this->getName(),
            'file_count' => $fileCount,
            'total_size' => $totalSize,
            'base_path' => $this->basePath,
            'human_readable_size' => $this->formatBytes($totalSize)
        ];
    }

    public function getName()
    {
        return 'file';
    }

    /**
     * 获取键对应的文件路径
     *
     * @param string $key 键名
     * @return string 文件路径
     */
    private function getKeyPath($key)
    {
        // 将点号转换为目录分隔符，创建层级结构
        $pathParts = explode('.', $key);
        $fileName = array_pop($pathParts) . $this->fileExtension;
        
        $directoryPath = $this->basePath;
        if (!empty($pathParts)) {
            $directoryPath .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $pathParts);
        }
        
        return $directoryPath . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * 递归删除目录
     *
     * @param string $dir 目录路径
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    /**
     * 格式化字节数为人类可读格式
     *
     * @param int $bytes 字节数
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}