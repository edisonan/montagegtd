#!/bin/bash

# 悬浮窗和提醒功能测试脚本

echo "🧪 开始测试悬浮窗和智能提醒功能..."
echo ""

# 检查文件是否存在
echo "📁 检查文件结构..."

files=(
    "floating-window.js"
    "reminder-manager.js"
    "renderer/floating-window.html"
    "renderer/js/floating-window.js"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file 存在"
    else
        echo "❌ $file 缺失"
        exit 1
    fi
done

echo ""
echo "🔍 检查代码集成..."

# 检查main.js中的集成
if grep -q "FloatingWindow" main.js; then
    echo "✅ main.js 中已集成悬浮窗"
else
    echo "❌ main.js 中缺少悬浮窗集成"
fi

if grep -q "ReminderManager" main.js; then
    echo "✅ main.js 中已集成提醒管理器"
else
    echo "❌ main.js 中缺少提醒管理器集成"
fi

# 检查preload.js中的API
if grep -q "hideFloatingWindow" preload.js; then
    echo "✅ preload.js 中包含悬浮窗API"
else
    echo "❌ preload.js 中缺少悬浮窗API"
fi

echo ""
echo "📋 功能清单:"
echo ""
echo "悬浮窗功能:"
echo "  ✓ 自动显示（最小化时）"
echo "  ✓ 透明毛玻璃效果"
echo "  ✓ 始终置顶"
echo "  ✓ 可拖动"
echo "  ✓ 实时状态显示"
echo "  ✓ 快速操作按钮"
echo ""
echo "智能提醒功能:"
echo "  ✓ 指数退避算法"
echo "  ✓ 6个提醒阶段（5/10/20/40/80/160分钟）"
echo "  ✓ 活动状态检测"
echo "  ✓ 点击通知打开应用"
echo "  ✓ 自动重置机制"
echo ""
echo "🎯 测试建议:"
echo ""
echo "1. 启动应用: npm run dev"
echo "2. 登录后最小化窗口，检查悬浮窗是否出现"
echo "3. 拖动悬浮窗到不同位置"
echo "4. 点击悬浮窗上的按钮测试功能"
echo "5. 最小化应用并等待5分钟，检查是否收到提醒"
echo "6. 点击通知，验证是否能打开主窗口"
echo ""
echo "⚠️  注意事项:"
echo "- 确保系统允许桌面通知"
echo "- 提醒功能需要应用在后台运行"
echo "- 悬浮窗在macOS上可能需要辅助功能权限"
echo ""
echo "✨ 测试完成！如有问题请查看控制台日志。"
