#!/bin/bash

# Montage GTD Desktop 启动脚本

echo "🚀 启动 Montage GTD Desktop 应用..."

# 检查是否安装了 Node.js
if ! command -v node &> /dev/null; then
    echo "❌ 未检测到 Node.js，请先安装 Node.js"
    exit 1
fi

# 检查是否安装了 npm
if ! command -v npm &> /dev/null; then
    echo "❌ 未检测到 npm，请先安装 npm"
    exit 1
fi

# 进入桌面应用目录
cd "$(dirname "$0")"

# 检查依赖是否已安装
if [ ! -d "node_modules" ]; then
    echo "📦 首次运行，正在安装依赖..."
    npm install
    if [ $? -ne 0 ]; then
        echo "❌ 依赖安装失败"
        exit 1
    fi
fi

# 启动应用
echo "✅ 启动应用..."
npm start
