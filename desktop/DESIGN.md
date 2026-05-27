# Montage GTD 桌面应用设计文档

## 项目概述

Montage GTD Desktop 是一个基于 Electron 构建的跨平台桌面应用程序，旨在提供比 Web 版本更流畅、更易用的 GTD（Getting Things Done）体验。

## 设计理念

### 1. 桌面优先体验
- **系统级集成**：托盘图标、桌面通知、全局快捷键
- **离线能力**：本地数据存储，网络恢复后同步
- **性能优化**：原生应用般的响应速度
- **资源效率**：合理的内存和CPU使用

### 2. GTD方法论实现
- **收集**：快速捕获任务和想法
- **澄清**：四象限优先级分类
- **组织**：智能任务管理和时间规划
- **回顾**：统计分析和进度跟踪
- **执行**：番茄钟专注模式

### 3. 用户友好界面
- **直观导航**：清晰的侧边栏和视图切换
- **视觉反馈**：实时状态更新和动画效果
- **无障碍设计**：键盘操作和屏幕阅读器支持
- **响应式布局**：适配不同屏幕尺寸

## 技术架构

### 整体架构
```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   渲染进程       │◄──►│   主进程          │◄──►│   后端API        │
│   (UI/UX)       │    │  (系统集成)       │    │  (pretask)      │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   本地存储       │    │   系统服务        │    │   云服务        │
│  (electron-store)│   │ (通知/托盘等)     │    │  (数据同步)      │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

### 模块划分

#### 主进程 (main.js)
- **窗口管理**：创建和管理应用窗口
- **系统集成**：托盘图标、系统菜单、文件操作
- **IPC通信**：与渲染进程的安全通信
- **生命周期**：应用启动、退出、异常处理

#### 预加载脚本 (preload.js)
- **安全桥接**：暴露有限的Node.js API给渲染进程
- **权限控制**：限制渲染进程的访问权限
- **类型定义**：提供TypeScript类型支持

#### 渲染进程模块

##### 1. API服务层 (api.js)
```javascript
class ApiService {
  - 认证管理 (login/logout/register)
  - 任务CRUD (create/read/update/delete)
  - 番茄钟记录 (start/update/complete)
  - 数据统计 (overview/analytics)
}
```

##### 2. 认证管理 (auth.js)
```javascript
class AuthManager {
  - Token管理 (save/load/verify)
  - 会话控制 (login/logout/auto-login)
  - 用户信息 (profile/preferences)
}
```

##### 3. 导航系统 (navigation.js)
```javascript
class NavigationManager {
  - 视图切换 (dashboard/tasks/pomodoro/etc)
  - 路由管理 (history/bookmarks)
  - 模态框控制 (quick-add/settings/search)
}
```

##### 4. 任务管理 (tasks.js)
```javascript
class TaskManager {
  - 任务列表 (filter/sort/pagination)
  - 四象限视图 (priority-based grouping)
  - 批量操作 (complete/delete/move)
  - 实时更新 (websocket/long-polling)
}
```

##### 5. 番茄钟 (pomodoro.js)
```javascript
class PomodoroManager {
  - 计时器控制 (start/pause/reset)
  - 会话管理 (work/rest cycles)
  - 统计追踪 (daily/weekly/monthly)
  - 声音提醒 (completion notifications)
}
```

##### 6. 仪表盘 (dashboard.js)
```javascript
class DashboardManager {
  - 概览统计 (task counts/focus time)
  - 四象限预览 (priority distribution)
  - 最近活动 (recent pomodoros/tasks)
  - 即将到期 (upcoming deadlines)
```

## 核心功能设计

### 1. 任务管理系统

#### 数据结构
```javascript
Task {
  id: number
  name: string
  content: string
  priority: 1|2|3|4  // 高|中|低|无
  status: 1|2|3      // 进行中|已完成|已折叠
  mode: 1|2          // GTD|学习
  deadline: string|null
  remindtime: string|null
  parent_task_id: number|null
  created_at: string
  updated_at: string
}
```

#### 交互流程
1. **创建任务**：快速添加模态框 → 验证输入 → API调用 → 本地更新
2. **编辑任务**：点击编辑按钮 → 填充表单 → 保存更改 → 同步更新
3. **完成任务**：勾选复选框 → 状态变更 → 动画反馈 → 统计更新
4. **删除任务**：确认对话框 → API删除 → 移除DOM → 刷新列表

### 2. 番茄钟系统

#### 工作流程
```
准备阶段 → 工作计时(25min) → 完成提醒 → 休息计时(5min) → 循环或结束
```

#### 状态管理
```javascript
PomodoroState {
  isRunning: boolean
  isWorkMode: boolean
  timeLeft: number
  totalTime: number
  sessionCount: number
  settings: {
    workDuration: number
    restDuration: number
  }
}
```

### 3. 四象限任务分布

#### 象限定义
- **Q1 (重要且紧急)**：立即处理的任务
- **Q2 (重要不紧急)**：计划安排的任务  
- **Q3 (紧急不重要)**：可以委托的任务
- **Q4 (不重要不紧急)**：可以删除的任务

#### 可视化展示
- 仪表盘中的四格布局
- 每个象限显示任务数量和前3个任务
- 颜色编码区分重要程度
- 点击任务可快速跳转

## UI/UX 设计

### 色彩系统
```css
--primary-color: #00b894     /* 主色调 - 成功/进行 */
--primary-hover: #00a085     /* 主色悬停 */
--danger-color: #ef4444      /* 高优先级/错误 */
--warning-color: #f59e0b     /* 中优先级/警告 */
--info-color: #3b82f6       /* 低优先级/信息 */
--gray-400: #94a3b8         /* 无优先级/禁用 */
```

### 布局结构
```
┌─────────────────────────────────────────┐
│              顶部工具栏                   │
├──────────────┬──────────────────────────┤
│              │                          │
│   侧边栏     │       主内容区域          │
│  (250px)     │      (自适应宽度)         │
│              │                          │
└──────────────┴──────────────────────────┘
```

### 响应式断点
- **Desktop**: > 1024px - 完整功能布局
- **Tablet**: 768px - 1024px - 简化侧边栏
- **Mobile**: < 768px - 汉堡菜单，单列布局

## 性能优化

### 1. 渲染优化
- **虚拟滚动**：大量任务列表的性能优化
- **懒加载**：按需加载视图组件
- **防抖节流**：搜索和过滤操作的优化
- **CSS动画**：硬件加速的过渡效果

### 2. 内存管理
- **及时清理**：组件销毁时清理事件监听器
- **数据缓存**：合理的数据缓存策略
- **垃圾回收**：避免内存泄漏的最佳实践

### 3. 网络优化
- **请求合并**：批量API调用减少网络往返
- **离线队列**：网络异常时的操作队列
- **增量同步**：只同步变更的数据

## 安全考虑

### 1. 数据安全
- **Token存储**：使用electron-store加密存储
- **HTTPS通信**：所有API调用使用加密传输
- **输入验证**：前后端双重验证机制

### 2. 进程隔离
- **Context Isolation**：渲染进程与Node.js隔离
- **Preload脚本**：最小化暴露的API表面
- **CSP策略**：严格的内容安全策略

## 测试策略

### 1. 单元测试
- API服务层的 mocking 测试
- 业务逻辑的纯函数测试
- 工具函数的边界条件测试

### 2. 集成测试
- 主进程与渲染进程的IPC通信
- 数据库操作的完整性测试
- 第三方服务的集成测试

### 3. E2E测试
- 用户登录流程
- 任务创建和管理
- 番茄钟完整会话

## 部署发布

### 1. 构建流程
```bash
# 开发环境
npm run dev

# 生产构建
npm run build

# 平台特定构建
npm run build:win    # Windows
npm run build:mac    # macOS
npm run build:linux  # Linux
```

### 2. 自动更新
- **更新服务器**：配置GitHub Releases或自建服务器
- **版本检查**：启动时检查最新版本
- **增量更新**：只下载变更的文件

### 3. 分发渠道
- **官方网站**：直接下载安装包
- **应用商店**：Microsoft Store, Mac App Store
- **包管理器**：Homebrew (macOS), Snap (Linux)

## 未来扩展

### 1. 功能增强
- **AI助手**：智能任务建议和优先级排序
- **协作功能**：团队任务共享和协同编辑
- **插件系统**：第三方扩展和自定义集成
- **语音输入**：语音识别和自然语言处理

### 2. 平台扩展
- **移动端**：iOS和Android原生应用
- **浏览器扩展**：Chrome/Firefox快捷添加
- **智能手表**：Apple Watch/ Wear OS通知
- **桌面小部件**：macOS Widgets / Windows Widgets

### 3. 生态集成
- **日历同步**：Google Calendar, Outlook
- **项目管理**：Jira, Trello, Asana集成
- **通讯工具**：Slack, Teams通知集成
- **云存储**：Dropbox, OneDrive备份

---

**Montage GTD Desktop** - 为现代知识工作者打造的效率工具！
