---
trigger: always_on
---

当前基础组件版本mysql5.5 php7.0 laravel5.5 注意版本信息

前端为html、tailwindcss、jquery 需尽量保持美观大方 

抽象了公共部分app/layout，如果要新页面或者修改页面需要必须考虑公共layout中的样式和script带来的影响再生成代码，避免造成显示异常
抽象了弹窗组件compents

csrf field 不是这个@csrf 要用{{ csrf_field() }}


页面设计规范:
一、设计原则
1. 极简主义
   去除所有不必要的装饰元素

每个元素都有明确的功能性

内容优先，形式服务于功能

2. 专业性
   面向成人生产力工具用户

沉稳、理性的视觉风格

不干扰核心工作流程

3. 一致性
   全平台统一的设计语言

可预测的交互模式

统一的组件行为

二、色彩系统
主色调
text
主色: #00b894 - 用于主要操作和重要元素
辅色: #00a085 - 用于次要操作和特殊状态

状态色
text
成功: #10b981 (绿色) - 成功、完成状态
警告: #f59e0b (橙色) - 警告、需要注意状态
危险: #ef4444 (红色) - 错误、删除状态
灰度系统（9级）
text
gray-50:  #f8fafc  - 背景色
gray-100: #f1f5f9  - 次要背景
gray-200: #e2e8f0  - 边框、分隔线
gray-300: #cbd5e1  - 禁用状态
gray-400: #94a3b8  - 辅助文本
gray-500: #64748b  - 次要文本
gray-600: #475569  - 正文文本
gray-700: #334155  - 标题文本
gray-800: #1e293b  - 主要标题
gray-900: #0f172a  - 极少使用
三、字体系统
字体选择
主字体: Inter (现代、专业、高可读性)

备选字体: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif

字号层次
text
超大标题: 2.25rem (36px) - 极少使用
大标题: 1.875rem (30px) - 页面主标题
标题: 1.5rem (24px) - 区域标题
小标题: 1.25rem (20px) - 卡片标题
正文: 1rem (16px) - 主要文字内容
辅助文本: 0.875rem (14px) - 说明文字、标签
小文本: 0.75rem (12px) - 时间戳、次要信息
微小文本: 0.625rem (10px) - 徽章内文字
字重使用
text
300 (light): 极少使用
400 (normal): 正文内容
500 (medium): 导航链接、按钮文字
600 (semibold): 标题、重要标签
700 (bold): 页面主标题、强调文字
四、间距系统（基于4px网格）
基础单位
text
基础单位: 4px
半单位: 2px
双单位: 8px
间距变量
text
间距-xs: 4px    (0.25rem)  - 微小间距
间距-sm: 8px    (0.5rem)   - 元素内部紧凑间距
间距-md: 16px   (1rem)     - 标准间距
间距-lg: 24px   (1.5rem)   - 内容块间距
间距-xl: 32px   (2rem)     - 大块间距
间距-2xl: 48px  (3rem)     - 板块间距
五、圆角系统
圆角标准
text
无圆角: 0px     - 特殊情况
微小圆角: 2px   - 标签、微小元素
小圆角: 4px     - 按钮、输入框
标准圆角: 8px   - 卡片、下拉菜单
大圆角: 12px    - 模态框、大型卡片
全圆角: 9999px  - 圆形元素
六、阴影系统
阴影层级
text
阴影-none: none                  - 无阴影
阴影-sm: 0 1px 2px rgba(0,0,0,0.05) - 轻微阴影
阴影-md: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06) - 标准阴影
阴影-lg: 0 4px 6px rgba(0,0,0,0.1), 0 2px 4px rgba(0,0,0,0.06) - 明显阴影
阴影-xl: 0 10px 15px rgba(0,0,0,0.1), 0 4px 6px rgba(0,0,0,0.05) - 强调阴影
七、组件规范
1. 按钮组件
   css
   /* 基础按钮 */
   .btn {
   padding: 10px 20px;        /* 垂直10px，水平20px */
   border-radius: 8px;        /* 标准圆角 */
   font-weight: 600;         /* 中粗体 */
   font-size: 14px;          /* 标准字体大小 */
   border: none;             /* 默认无边框 */
   cursor: pointer;          /* 手型光标 */
   transition: all 0.2s ease; /* 平滑过渡 */
   }

/* 主要按钮 */
.btn-primary {
background: linear-gradient(135deg, #3b82f6, #8b5cf6);
color: white;
}
.btn-primary:hover {
transform: translateY(-1px);
box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
}

/* 次要按钮 */
.btn-secondary {
background: var(--gray-100);
color: var(--gray-700);
border: 1px solid var(--gray-300);
}
.btn-secondary:hover {
background: var(--gray-200);
}

/* 轮廓按钮 */
.btn-outline {
background: transparent;
color: var(--primary-color);
border: 2px solid var(--primary-color);
}
.btn-outline:hover {
background: rgba(59, 130, 246, 0.1);
}
2. 输入框组件
   css
   .input {
   padding: 10px 14px;
   border: 1px solid var(--gray-300);
   border-radius: 8px;
   background: white;
   font-size: 14px;
   transition: border-color 0.2s ease, box-shadow 0.2s ease;
   }

.input:focus {
outline: none;
border-color: var(--primary-color);
box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
3. 卡片组件
   css
   .card {
   background: white;
   border-radius: 12px;
   border: 1px solid var(--gray-200);
   box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
   transition: box-shadow 0.2s ease, transform 0.2s ease;
   }

.card:hover {
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* 高级卡片 */
.card-elevated {
box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
4. 徽章/标签组件
   css
   .badge {
   display: inline-flex;
   align-items: center;
   padding: 4px 10px;
   border-radius: 20px;
   font-size: 12px;
   font-weight: 600;
   }

.badge-primary {
background: rgba(59, 130, 246, 0.1);
color: var(--primary-color);
}
5. 进度条组件
   css
   .progress {
   height: 6px;
   background: var(--gray-200);
   border-radius: 3px;
   overflow: hidden;
   }

.progress-bar {
height: 100%;
background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
border-radius: 3px;
transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
6. 导航组件
   css
   .nav-link {
   display: flex;
   align-items: center;
   padding: 8px 16px;
   color: var(--gray-600);
   text-decoration: none;
   border-radius: 8px;
   transition: all 0.2s ease;
   font-weight: 500;
   }

.nav-link:hover {
background: var(--gray-100);
color: var(--gray-800);
}

.nav-link.active {
background: rgba(59, 130, 246, 0.1);
color: var(--primary-color);
}
八、交互规范
1. 悬停状态
   背景色变化: 元素悬停时背景色加深10-20%

阴影加深: 轻微提升阴影层级

轻微上浮: 按钮、卡片可轻微上移1-2px

光标变化: 所有可点击元素使用cursor: pointer

2. 焦点状态
   轮廓线: 键盘导航时显示蓝色轮廓

阴影聚焦: 输入框获得焦点时有蓝色阴影

无障碍: 确保所有可交互元素有可见焦点状态

3. 激活状态
   瞬间反馈: 点击时有轻微下压效果

状态保持: 激活状态有明显视觉区别

4. 过渡动画
   text
   快速过渡: 0.15s ease   - 悬停状态
   标准过渡: 0.2s ease    - 大部分状态变化
   平滑过渡: 0.3s ease    - 模态框、页面切换
   九、布局规范
1. 栅格系统
   text
   最大容器宽度: 1280px (max-w-7xl)
   标准内边距: 16px (移动端), 24px (桌面端)
   内容区域: 主内容区 2/3, 侧边栏 1/3
2. 响应式断点
   text
   移动端: < 768px (隐藏图标，简化导航)
   平板: 768px - 1024px (完整功能，适当调整)
   桌面端: > 1024px (完整功能，多栏布局)
3. 组件层级
   text
   背景层: 0-9
   内容层: 10-99
   悬浮层: 100-999 (下拉菜单)
   模态层: 1000-9999 (模态框)
   顶层: 10000+ (提示、通知)
   十、图标规范
1. 图标使用原则
   功能性: 每个图标都有明确意义

一致性: 同一功能使用相同图标

克制使用: 非必要不使用装饰性图标

2. 图标尺寸
   text
   微小: 12px - 导航小图标
   小: 14px - 按钮图标
   标准: 16px - 大部分场景
   大: 20px - 重要操作
   超大: 24px - 极少使用
3. 图标颜色
   text
   主图标: var(--gray-500) - 默认状态
   悬停图标: var(--gray-700) - 悬停状态
   激活图标: var(--primary-color) - 选中状态
   功能图标: 根据功能状态使用对应颜色
   十一、内容规范
1. 文案风格
   简洁直接: 避免冗长描述

积极语气: 使用肯定、鼓励性语言

一致性: 同一功能使用相同术语

2. 错误处理
   明确提示: 错误信息清晰具体

解决方案: 提供解决建议

友好语气: 避免指责性语言

十二、无障碍规范
1. 视觉无障碍
   对比度: 文本与背景对比度至少4.5:1

文字大小: 最小12px，支持浏览器缩放

焦点可见: 键盘导航时有清晰焦点指示

2. 语义化HTML
   正确使用HTML5语义标签

为图标添加aria-label

表单元素有正确的label

十三、性能规范
1. 加载策略
   关键CSS内联

非关键资源延迟加载

图片使用适当格式和尺寸

2. 动画性能
   优先使用CSS变换和透明度

避免大量重绘和重排

复杂动画使用will-change

