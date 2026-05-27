@echo off
REM Montage GTD Desktop Windows 启动脚本

echo 🚀 启动 Montage GTD Desktop 应用...

REM 检查是否安装了 Node.js
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ 未检测到 Node.js，请先安装 Node.js
    pause
    exit /b 1
)

REM 检查是否安装了 npm
where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ 未检测到 npm，请先安装 npm
    pause
    exit /b 1
)

REM 进入桌面应用目录
cd /d "%~dp0"

REM 检查依赖是否已安装
if not exist "node_modules\" (
    echo 📦 首次运行，正在安装依赖...
    call npm install
    if %errorlevel% neq 0 (
        echo ❌ 依赖安装失败
        pause
        exit /b 1
    )
)

REM 启动应用
echo ✅ 启动应用...
call npm start

pause
