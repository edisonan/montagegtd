# Montage GTD Desktop Application

基于 Electron 构建的 GTD 桌面应用程序，提供高效的任务管理和番茄钟功能。

## 功能特性

### 🎯 核心GTD功能
- **任务四象限管理**：按重要性和紧急性分类任务
- **智能任务列表**：支持过滤、排序和批量操作
- **番茄钟专注**：25分钟工作+5分钟休息的经典模式
- **日历集成**：时间管理和日程安排
- **数据统计**：工作效率分析和趋势展示

### 💻 桌面应用特色
- **系统托盘**：最小化到托盘，后台运行
- **悬浮窗**：最小化时显示浮动窗口，实时查看状态
- **智能提醒**：指数退避算法，防止遗忘任务
- **桌面通知**：任务提醒和番茄钟完成通知
- **离线支持**：本地数据存储，网络恢复后同步
- **快捷键**：高效的键盘操作支持
- **跨平台**：支持 Windows、macOS、Linux

## 安装和运行

### 前置要求
- Node.js >= 16.0
- npm >= 7.0

### 开发环境运行

```bash
# 进入桌面应用目录
cd desktop

# 安装依赖
npm install

# 启动开发模式
npm run dev
```

### 生产构建

```bash
# 构建所有平台
npm run build

# 构建特定平台
npm run build:win    # Windows
npm run build:mac    # macOS  
npm run build:linux  # Linux
```

## 项目结构

```
desktop/
├── main.js              # Electron 主进程
├── preload.js           # 预加载脚本
├── package.json         # 项目配置
├── renderer/            # 渲染进程（前端界面）
│   ├── index.html       # 主页面
│   ├── styles/          # 样式文件
│   │   └── main.css
│   └── js/             # JavaScript 模块
│       ├── api.js      # API 服务
│       ├── auth.js     # 认证管理
│       ├── navigation.js # 导航管理
│       ├── tasks.js    # 任务管理
│       ├── pomodoro.js # 番茄钟
│       ├── dashboard.js # 仪表盘
│       └── app.js      # 应用入口
└── assets/             # 静态资源
    └── icons/          # 图标文件
```

## 使用说明

### 首次使用
1. 启动应用后，使用您的账号登录
2. 默认连接到 `https://pretask.congcong.us`
3. 登录后自动同步您的任务数据

### 快捷键
- `Ctrl/Cmd + N`: 快速添加任务
- `Ctrl/Cmd + T`: 切换到任务视图
- `Ctrl/Cmd + P`: 切换到番茄钟视图
- `Ctrl/Cmd + D`: 切换到仪表盘
- `Escape`: 关闭模态框
- `F5`: 刷新当前视图

### 任务管理
- **创建任务**：点击"快速添加"按钮或使用快捷键
- **优先级设置**：高(红色)、中(橙色)、低(蓝色)、无(灰色)
- **状态切换**：点击复选框标记任务完成
- **四象限视图**：在仪表盘查看任务分布

### 番茄钟使用
1. 切换到番茄钟视图
2. 设置工作和休息时长
3. 点击"开始"开始专注
4. 完成后自动记录到统计

## API 集成

应用通过 REST API 与后端服务通信：

- **基础URL**: `https://pretask.congcong.us/api/v2`
- **认证方式**: Bearer Token
- **数据格式**: JSON

主要API端点：
- `/auth/login` - 用户登录
- `/tasks` - 任务管理
- `/focuss` - 番茄钟记录
- `/statistics` - 统计数据

## 配置选项

在 `main.js` 中可以配置：

```javascript
const store = new Store({
  defaults: {
    apiBaseUrl: 'https://pretask.congcong.us/api/v2',
    settings: {
      autoStart: false,        // 开机自启动
      minimizeToTray: true,    // 最小化到托盘
      notifications: true,     // 启用通知
      pomodoroSound: true,     // 番茄钟声音
      theme: 'light'          // 主题
    }
  }
});
```

## 开发指南

### 添加新功能
1. 在 `renderer/js/` 中创建新的模块文件
2. 在 `index.html` 中添加对应的视图
3. 在 `navigation.js` 中注册新的导航项
4. 更新样式文件 `styles/main.css`

### 调试技巧
- 使用 `--dev` 参数启动开发模式
- 开发者工具会自动打开
- 使用 `console.log` 输出调试信息

## 打包发布

### Windows
```bash
npm run build:win
```
生成 `.exe` 安装文件和便携版

### macOS
```bash
npm run build:mac
```
生成 `.dmg` 安装包

### Linux
```bash
npm run build:linux
```
生成 `.AppImage` 和 `.deb` 包

## 故障排除

### 常见问题

**无法连接服务器**
- 检查网络连接
- 确认后端服务正常运行
- 检查防火墙设置

**通知不显示**
- 检查系统通知权限
- 确认应用设置中启用了通知

**数据不同步**
- 检查网络连接
- 尝试手动刷新（F5）
- 重新登录账户

### 日志位置
- Windows: `%APPDATA%/montage-gtd-desktop/logs/`
- macOS: `~/Library/Logs/montage-gtd-desktop/`
- Linux: `~/.config/montage-gtd-desktop/logs/`

## 贡献指南

1. Fork 项目
2. 创建功能分支
3. 提交更改
4. 推送到分支
5. 创建 Pull Request

## 许可证

MIT License - 详见 LICENSE 文件

## 联系方式

- 项目主页: https://pretask.congcong.us
- 问题反馈: 通过应用内反馈功能或项目issues

---

**Montage GTD Desktop** - 让效率触手可及！
