<?php
// setup_llm_tables.php - 简单的数据库表创建脚本

// 检查是否在命令行环境中运行
if (php_sapi_name() !== 'cli') {
    die('此脚本只能在命令行环境中运行');
}

// 读取 .env 文件以获取数据库配置
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("错误: 未找到 .env 文件。请确保项目根目录下有 .env 文件\n");
}

$envContent = file_get_contents($envFile);
$envLines = explode("\n", $envContent);

$dbConfig = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'forge',
    'username' => 'forge',
    'password' => '',
];

foreach ($envLines as $line) {
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "\"'\n\r");

        switch ($key) {
            case 'DB_HOST':
                $dbConfig['host'] = $value;
                break;
            case 'DB_PORT':
                $dbConfig['port'] = $value;
                break;
            case 'DB_DATABASE':
                $dbConfig['database'] = $value;
                break;
            case 'DB_USERNAME':
                $dbConfig['username'] = $value;
                break;
            case 'DB_PASSWORD':
                $dbConfig['password'] = $value;
                break;
        }
    }
}

echo "正在连接到数据库...\n";
echo "主机: {$dbConfig['host']}:{$dbConfig['port']}\n";
echo "数据库: {$dbConfig['database']}\n";

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "数据库连接成功！\n";
    
    // 读取SQL文件
    $sqlFile = __DIR__ . '/create_llm_tables.sql';
    if (!file_exists($sqlFile)) {
        die("错误: 未找到 SQL 文件 {$sqlFile}\n");
    }
    
    $sqlContent = file_get_contents($sqlFile);
    
    // 分割SQL语句并执行
    $statements = array_filter(array_map('trim', preg_split('/;(?=\n)/', $sqlContent)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            echo "执行: " . substr($statement, 0, 50) . "...\n";
            $pdo->exec($statement);
        }
    }
    
    echo "\nLLM相关表创建成功！\n";
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
    exit(1);
}

// 检查是否需要创建菜单项
echo "\n是否需要添加菜单项到后台管理系统？(y/n): ";
$input = trim(fgets(STDIN));

if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
    echo "菜单项将通过 artisan 命令添加，请确保应用已正确配置并运行了 composer install\n";
    echo "执行命令: php artisan llm:add-menu-items\n";
}