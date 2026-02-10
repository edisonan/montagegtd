<?php

namespace App\Agent\Storage\Adapters;

use App\Agent\Storage\StorageAdapterInterface;
use PDO;
use PDOException;

/**
 * 数据库存储适配器
 * 使用 PDO 支持多种数据库
 */
class DatabaseStorageAdapter implements StorageAdapterInterface
{
    private $pdo;
    private $tableName;
    private $initialized = false;

    public function __construct($dsn, $username = null, $password = null, $tableName = 'agent_storage')
    {
        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $this->tableName = $tableName;
            $this->initializeTable();
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * 初始化数据表
     */
    private function initializeTable()
    {
        if ($this->initialized) {
            return;
        }

        $sql = "
            CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `storage_key` VARCHAR(255) NOT NULL UNIQUE,
                `data` LONGTEXT NOT NULL,
                `created_at` INT NOT NULL,
                `expires_at` INT DEFAULT NULL,
                `updated_at` INT NOT NULL,
                INDEX `idx_storage_key` (`storage_key`),
                INDEX `idx_expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        try {
            $this->pdo->exec($sql);
            $this->initialized = true;
        } catch (PDOException $e) {
            throw new \Exception("Failed to create storage table: " . $e->getMessage());
        }
    }

    public function save($key, $data, $ttl = null)
    {
        try {
            $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
            $expiresAt = $ttl ? time() + $ttl : null;
            $now = time();

            $sql = "
                INSERT INTO `{$this->tableName}` 
                (`storage_key`, `data`, `created_at`, `expires_at`, `updated_at`) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                `data` = VALUES(`data`), 
                `expires_at` = VALUES(`expires_at`), 
                `updated_at` = VALUES(`updated_at`)
            ";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$key, $jsonData, $now, $expiresAt, $now]);

        } catch (PDOException $e) {
            error_log("DatabaseStorage save error: " . $e->getMessage());
            return false;
        }
    }

    public function load($key)
    {
        try {
            $sql = "SELECT `data`, `expires_at` FROM `{$this->tableName}` WHERE `storage_key` = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$key]);
            $result = $stmt->fetch();

            if (!$result) {
                return null;
            }

            // 检查过期时间
            if ($result['expires_at'] !== null && time() > $result['expires_at']) {
                $this->delete($key); // 自动清理过期数据
                return null;
            }

            $data = json_decode($result['data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $data;

        } catch (PDOException $e) {
            error_log("DatabaseStorage load error: " . $e->getMessage());
            return null;
        }
    }

    public function delete($key)
    {
        try {
            $sql = "DELETE FROM `{$this->tableName}` WHERE `storage_key` = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$key]);
        } catch (PDOException $e) {
            error_log("DatabaseStorage delete error: " . $e->getMessage());
            return false;
        }
    }

    public function exists($key)
    {
        return $this->load($key) !== null;
    }

    public function keys()
    {
        try {
            $sql = "SELECT `storage_key` FROM `{$this->tableName}` WHERE `expires_at` IS NULL OR `expires_at` > ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([time()]);
            $results = $stmt->fetchAll();
            
            return array_column($results, 'storage_key');
        } catch (PDOException $e) {
            error_log("DatabaseStorage keys error: " . $e->getMessage());
            return [];
        }
    }

    public function clear()
    {
        try {
            $sql = "TRUNCATE TABLE `{$this->tableName}`";
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("DatabaseStorage clear error: " . $e->getMessage());
            return false;
        }
    }

    public function getStats()
    {
        try {
            // 获取总记录数
            $sql = "SELECT COUNT(*) as count FROM `{$this->tableName}`";
            $stmt = $this->pdo->query($sql);
            $totalCount = $stmt->fetch()['count'];

            // 获取未过期记录数
            $sql = "SELECT COUNT(*) as count FROM `{$this->tableName}` WHERE `expires_at` IS NULL OR `expires_at` > ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([time()]);
            $validCount = $stmt->fetch()['count'];

            // 获取表大小（MySQL特有）
            $tableSize = 0;
            if (strpos($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME), 'mysql') !== false) {
                $sql = "SELECT 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb' 
                    FROM information_schema.TABLES 
                    WHERE table_schema = DATABASE() AND table_name = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$this->tableName]);
                $result = $stmt->fetch();
                $tableSize = $result ? $result['size_mb'] : 0;
            }

            return [
                'adapter' => $this->getName(),
                'total_records' => $totalCount,
                'valid_records' => $validCount,
                'expired_records' => $totalCount - $validCount,
                'table_size_mb' => $tableSize,
                'table_name' => $this->tableName
            ];

        } catch (PDOException $e) {
            error_log("DatabaseStorage stats error: " . $e->getMessage());
            return [
                'adapter' => $this->getName(),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getName()
    {
        return 'database';
    }
}