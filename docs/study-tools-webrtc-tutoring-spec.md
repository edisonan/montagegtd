# 学习工具（Study Tools）与 WebRTC 远程辅导 — 技术规格

> 状态：✅ V1 已实施（2026-08，M1–M4 完成，testtask 环境验证通过）
> 目标版本：V1（可用的最小完整版本）
> 适用范围：MontageGTD（Laravel 5.5 / PHP 7 / MySQL / Vue2 / jQuery + 行内 JS）

---

## 1. 背景与目标

在"学习"模块下新增 **学习工具** 入口，进入后是一个 **卡片列表页**（后续可扩展更多工具）。
第一个学习工具为 **基于 WebRTC 的远程辅导（Tutoring）**：

- 支持通过 **URL 参数传递学习内容**（`?url=...`），V1 一期支持三类：**课程页 + 章节定位**（`/courses/{id}?item={章节}`）、**学习计划详情**（`/study?plan={计划ID}`）、**外部 URL**；
- 支持打开 **本地文件**：图片、docx、xls/xlsx、pptx、pdf、html 等；
- 页面 **左右两栏布局**：左侧主内容区，右侧视频区；
- 主内容区可切换为 **标注画笔模式**，双方画笔轨迹 **实时同步**；
- 双方通过 WebRTC 建立 **音视频** 连接，通过 DataChannel 同步内容与标注。

### 明确不做（V1 范围外）

- 不做屏幕共享（V2 可加，架构已预留）；
- 不做服务端音视频转码 / 录制 / 混流（纯 P2P）；
- 不做 TURN 服务器部署（V1 提供 STUN 公共节点 + NAT 穿透失败时的降级提示与白板服务端中继兜底）。
- **本地 Office 文件（docx/xlsx/pptx）采用纯前端 JS 渲染（V1 已评审确认，服务器零安装）**，见 4.3 与 6.4；LibreOffice 转 PDF 仅作为配置可选的"高保真"方案（默认关闭）。

### Office 渲染决策记录

- ✅ 已确认（2026-08，评审）：**纯前端 JS 渲染为主方案**，理由：文件字节本来就经 DataChannel 直达对端浏览器，前端渲染端到端不经过服务器，省去上传/签名 URL/临时文件清理/soffice 安装与部署风险，且数据不在服务端落盘。
- 可选高保真兜底：`study_tools.office_server_conversion=false`（默认关）。开启时装 LibreOffice，走 6.4 的 PDF 转换链路。

---

## 2. 功能总览

```
学习（顶部菜单）
└─ 学习工具  /study/tools          ← 新增子菜单与页面（卡片列表）
   └─ 远程辅导（WebRTC）  /study/tools/tutoring?url=...&room=...
      ├─ 左侧：主内容区（URL 内容 / 本地文件 / 白板标注画布）
      └─ 右侧：视频区（本地小窗 + 远端大窗 + 通话控制 + 房间管理）
```

---

## 3. 学习工具卡片页（/study/tools）

### 3.1 路由与控制器

| 方法 | 路由 | 控制器 |
|---|---|---|
| GET | `/study/tools` | `StudyController@tools` → 返回 `study.tools` 视图 |

在 `routes/web.php` 中与现有 `/study` 路由相邻注册（web + auth 中间件组内）。

### 3.2 页面结构

- 标题栏："学习工具"，副标题说明；返回学习页链接（沿用 `/study/checkins` 页风格）。
- **卡片网格**（数据驱动，便于以后加工具）：
  - 每张卡：图标、工具名、一句话介绍、标签（如"实时"）、"进入工具"按钮。
  - V1 卡片注册表：直接写在 Blade 的 PHP 数组或独立 `config/study_tools.php`（推荐后者，表单校验/展示共用）。
- 首张卡片：**远程辅导（WebRTC 实时批注辅导）** — 图标 `fas fa-chalkboard-teacher`，描述"音视频辅导 + URL/本地文件学习内容 + 双方同步画笔标注"，链接 `/study/tools/tutoring`。

### 3.3 页面 UI 约定

沿用 `study/index.blade.php` 的视觉语言（圆角卡片、浅色渐变、`#1e3a8a` 主色、`#ff5f9d` 强调色），布局居中 max-width 1080px。

---

## 4. 远程辅导工具（/study/tools/tutoring）— 页面与交互

### 4.1 页面总览

```
┌──────────────────────────────────────────────┬──────────────────┐
│ 工具栏（打开文件 | 打开URL | 标注模式 | 画笔设置 | 清空）        │ 视频区标题         │
├──────────────────────────────────────────────┤ ├──────────────────┤
│                                              │ │ 远端视频(大)      │
│  主内容舞台（image / html / pdf / text / office占位）│ ├──────────────────┤
│  ┌────────────────────────────────────────┐  │ │ 本地视频(小, PIP) │
│  │ 标注画布 overlay（标注模式时激活）            │  │ ├──────────────────┤
│  │ 尺寸与内容同步缩放                          │  │ │ 房间: 创建/加入    │
│  └────────────────────────────────────────┘  │ │ 麦克风/摄像头/挂断  │
│  （无内容时显示引导占位 + 状态提示）                  │ │ 连接状态指示        │
└──────────────────────────────────────────────┴──────────────────┘
```

- 左右两栏：左侧主内容 `flex-1`，右侧视频区固定宽约 320px（移动端自动上下堆叠）。
- 移动端：两栏垂直排列，视频区可折叠。

### 4.2 URL 传递学习内容

- 入口：工具栏"打开 URL"输入框；URL 参数 `?url=<urlencoded>`。
- 页面加载流程：
  1. 读 `url` 参数 → **URL 类型识别**（见下表）→ 加载到主内容舞台；
  2. 若已加入 WebRTC 房间且 DataChannel 可用 → 发送 `content_url` 同步消息 `{url}`，对端按相同识别逻辑加载同一 URL（宿主打开内容，双方看到同一内容）。

**URL 类型识别与渲染（V1 一期定位：课程章节 / 计划详情 / 外部 URL）**：

| URL 形态 | 识别 | 渲染 | 备注 |
|---|---|---|---|
| `/courses/{id}` 或 `/courses/{id}?item={chapterId}` | 同源应用页 · 课程 | **同源 iframe**（允许脚本 + 自带登录态） | 课程页已支持 `?item=` 定位章节（现有实现：读取并自动选中展开章节、滚动到该章节），**零改动** |
| `/study` 或 `/study?plan={planId}` | 同源应用页 · 学习计划 | 同源 iframe | `/study` 需**新增 `?plan=` 钩子**：加载后自动 `viewPlanDetail(planId)` 并高亮对应计划卡（见 7 前端清单），实现"计划的执行详情"直达 |
| 其他 http(s) 外部 URL | 外部资源 | 普通 iframe（目标站可能因 X-Frame-Options 拒绝嵌入 → 提供"在新标签打开"兜底按钮） | 等价于用户在浏览器打开该 URL |
| 本地文件 | — | 见 4.3 | |

- 安全：仅允许 http/https 协议；**同源应用页 iframe 不做 sandbox**（需要脚本与登录态，属可信白名单：`/courses/*`、`/study*`）；**外部 URL iframe 不代理、不注入**，等同用户自行打开；标注模式下内容区接管指针（只读批注），退出标注恢复 iframe 交互。

### 4.3 本地文件打开

文件选择器（`<input type="file" multiple>`，accept 覆盖：`image/*, .pdf, .html, .htm, .txt, .md, .doc, .docx, .xls, .xlsx, .ppt, .pptx`）。

**渲染策略（file → viewer pipeline）**：

| 类型 | 渲染方式 |
|---|---|
| png/jpg/gif/webp/svg/bmp | `<img>`（URL.createObjectURL） |
| pdf | `<object type="application/pdf">`（浏览器内置预览） |
| html/htm | iframe `srcdoc` + `sandbox` |
| txt/md | 文本/简易 markdown 渲染（textContent，不引入渲染库） |
| docx | `docx-preview` 纯前端渲染（HTML） |
| xls/xlsx | SheetJS CE 解析 → HTML 表格 |
| pptx | `pptx-preview` 纯前端按页渲染 |
| doc / ppt（2007 前二进制） | 无纯 JS 库可渲染 → 提示"请另存为 .docx/.pptx 后打开"（.xls 例外，SheetJS 支持） |
| 其他 | 统一降级提示 |

- 纯前端 Office 渲染库（docx-preview / SheetJS CE / pptx-preview）下载 dist 放 `public/vendor_local/`，本页面按需 `<script>` 引入；渲染目标为**沙箱 iframe 内的 DOM**（见 5.5 安全），避免恶意文档注入主页面。

- **同步到对端（V1 特性）**：宿主打开本地文件 → 读取为 `ArrayBuffer`（限制 ≤ 20MB，超限提示）→ DataChannel 分块发送（`file:meta` / `file:chunk` / `file:end`）→ 对端按相同 pipeline 渲染。对端未连接或 DC 不可用时仅本地渲染。
- **Office 文件（docx/xlsx/pptx）**：纯前端渲染 —— 宿主与本端字节直接喂给对应 JS 库；对端收到的字节同样本地渲染。**端到端不经过服务器**（各浏览器渲染细节见 4.3）。仅当 `study_tools.office_server_conversion=true` 时才走服务端 Pdf 转换链路（6.4 可选方案）。
- 端上副本不落库（内存 + blob URL 生命周期内有效）。

### 4.4 标注画笔模式（核心同步特性）

- 工具栏按钮"标注模式"切换：激活时在主内容舞台上叠加 **全尺寸透明 canvas**，尺寸跟随内容缩放（canvas 内坐标按当前缩放换算统一到"内容逻辑坐标系"，保证双方所见一致）。
- 工具栏：画笔颜色、笔宽、橡皮擦、撤销、清空、退出标注。
- 事件：pointerdown/move/up（兼容触摸），红点渲染。
- **同步协议（DataChannel JSON 消息）**：
  - `wb` 消息体为一段笔画（stroke）：`{ id, color, size, tool, points:[{x,y}...] }`；
  - `wb:clear`、`wb:undo`（撤销历史栈同步）；
  - 由宿主（房主）主导，客户端收到远端笔画后仅重放（不回传），防止回声循环；房主与学员都可在自己侧看到双方落笔（对端笔画用略透明/不同色表现，可辨识"谁在画"）。
- **降级中继**：DataChannel 未就绪时，标注消息改走服务端信令轮询 API（见 6），双方仍可同步（延迟稍高）。

### 4.5 视频区（WebRTC）

- 本地小窗（PIP 角标）、远端大窗；无视频时展示占位（图标 + 姓名）。
- 控件：麦克风开关、摄像头开关、挂断；连接状态指示（未连接/信令中/已连接 P2P/中继受限）。
- 媒体约束默认 `video: true, audio: true`；支持 `?video=0` / `?audio=0` 参数便于纯白板辅导。

---

## 5. 房间与信令（关键架构决策）

### 5.1 现状与决策

- **现状**：项目无 Pusher/Redis/WebSocket 广播基础设施（composer.json、config/broadcasting.php 均为默认空壳），部署为单机 Nginx + PHP。
- **决策 A（信令）**：采用 **HTTP 轮询中继 API**（房间码 + 消息表）。理由：零新增基础设施、兼容现有 LAMP 部署、可离线自托管、够用（信令 + 白板降级）。不引入第三方信令服务（如 PeerJS 云）以保持数据私密。
- **决策 B（媒体）**：音视频走 **WebRTC P2P**（ICE 默认含公共 Google STUN ``stun:stun.l.google.com:19302``；未来可配置环境变量加 TURN）。
- **决策 C（同步通道）**：白板/文件/内容同步优先走 **DataChannel（低延迟）**，失败时白板消息退化为服务端轮询中继（同一套消息表）。

### 5.2 房间生命周期

1. **创建房间**：房主点"创建房间" → `POST /api/v2/study/tools/rooms` → 后端生成房号（如 6 位大写字母数字）；房主复制"邀请链接"（`/study/tools/tutoring?room=CODE`）发给对方；**URL 参数 `room=CODE` 打开页面自动加入**。
2. **加入房间**：输入房号或点邀请链接加入 → 双方进入同一房间。
3. **WebRTC 连接流程**：标准 offer/answer + ICE trickle；信令消息经轮询 API 中转：
   - 房主 createOffer → 存消息(type=offer) → 学员轮询到 → setRemote + createAnswer → 存消息(type=answer) → 房主轮询到 → 双方交换 ICE。
4. **房间状态**：`waiting → active(双方已建立) → closed(房主关闭或超时)`。房间 **2 小时过期**（房主可续期）；消息随房间清理。
5. **Demo 兜底**：信令中继不可达时，提供 **"手动复制 SDP"** 折叠面板（offer/answer 粘贴互换），保证无后端通路也能连（如纯内网）。

### 5.3 轮询 API 设计

统一前缀 `/api/v2/study/tools`，`hybrid.token` 中间件，响应沿用 `{code:9999,msg,result}`。

| 方法 | 路径 | 说明 |
|---|---|---|
| POST | `/rooms` | 创建房间，body `{kind:'tutoring'}` → `{code, room_id, expires_at}` |
| GET | `/rooms/{code}` | 房间信息 + 成员数（用于展示/校验） |
| POST | `/rooms/{code}/join` | 加入：`{role:'student'}` → 返回房间数据 |
| POST | `/rooms/{code}/messages` | 发送信令/白板消息：`{type, payload}`，type ∈ offer/answer/ice/join/leave/wb/wb_clear/wb_undo/content_url/ping/file |
| GET | `/rooms/{code}/messages?since=<id>` | 增量拉取（轮询间隔 1.5s），返回 `{list:[{id,type,payload,sender,created_at}], has_more}` |
| POST | `/rooms/{code}/close` | 仅房主可关闭 |
| POST | `/rooms/{code}/heartbeat` | 周期心跳，清理失效成员（V1 可合并进 messages 轮询的返回） |

- 消息体大小限制（白板段落 ≤ 64KB；文件走 DataChannel 不经过此接口）。
- 成员标识：以 `user_id`（登录用户）+ `peer_id`（前端生成 UUID，用于同账号双开时区分）。

### 5.4 数据表（新增迁移）

**`study_tool_rooms`**

| 列 | 类型 | 说明 |
|---|---|---|
| id | increments | |
| code | string(8) unique | 房号 |
| kind | string(32) default 'tutoring' | 工具类型，预留扩展 |
| owner_user_id | unsignedInteger index | 房主 |
| state | tinyInteger default 0 | 0 waiting / 1 active / 2 closed |
| expires_at | dateTime nullable | 过期时间（创建 +2h） |
| meta | text nullable | 保留字段（如约定内容 URL） |
| timestamps | | |

**`study_tool_messages`**

| 列 | 类型 | 说明 |
|---|---|---|
| id | bigIncrements | 轮询游标 |
| room_id | unsignedInteger index | |
| sender_user_id | unsignedInteger default 0 | |
| peer_id | string(64) default '' | 前端会话标识 |
| type | string(32) | offer/answer/ice/join/leave/wb/... |
| payload | mediumText | JSON |
| created_at | datetime index | |
|（无 updated_at） | | |

- 过期房间与消息由 `Schedule` 命令（`StudyToolRoomCleanup`）每日清理，或懒清理（读写时顺带删过期数据）。

### 5.5 安全

- 创建/关闭房间需要登录；**加入房间只需房号**（房号即邀请凭证，内网辅导场景可接受；配置项 `study_tools.room_join_password` 可开启加入口令）。
- 消息 payload 全部视为不可信 JSON，前端渲染前转义；房间 message 查询严格限定 `room_id + code` 匹配。
- 房主关闭/两小时过期 → 双方收到 `leave`、回房间列表态，视频轨道关闭，画布清空会话数据。
- **URL 嵌入策略**：同源应用页（`/courses/*`、`/study*`）走可信白名单，直接 iframe（需要脚本与登录态）；外部 URL 等同用户自行打开（不代理、不注入、不解析其内容）；本地文件里 docx/pptx 渲染统一进沙箱 iframe（`sandbox="allow-scripts"` 且不放行同源），docx-preview/pptx-preview/SheetJS 的输出 DOM 与主页面隔离，防恶意文档注入。

---

## 6. 后端实现清单

| 文件 | 内容 |
|---|---|
| `database/migrations/2026_XX_XX_000001_create_study_tool_rooms_table.php` | rooms 表 |
| `database/migrations/2026_XX_XX_000002_create_study_tool_messages_table.php` | messages 表 |
| `app/Models/StudyToolRoom.php` | 模型 + 房号生成（唯一重试） |
| `app/Models/StudyToolMessage.php` | 模型 |
| `app/Services/StudyToolService.php` | 房间 CRUD、消息存取、过期清理、心跳 |
| `app/Http/Controllers/Api/V2/StudyToolController.php` | 上述 5.3 的 7 个方法（复用 `getAuthUserId` / `jsonResponse`） |
| `routes/api.php` | 新增 `/study/tools/*` 路由组（读/写分组，沿用现有 hybrid.token 模式） |
| `routes/web.php` | `GET /study/tools`、`GET /study/tools/tutoring` |
| `app/Http/Controllers/StudyController.php` | 新增 `tools()`、`tutoring()` 两方法（`auth` 中间件已覆盖） |
| `config/study_tools.php` | 工具卡片注册表 + 房间过期时长 + STUN/TURN 配置 + 加入口令开关 + `office_server_conversion`（默认 false） |
| `app/Console/Kernel.php` | 注册每日清理命令（`study_tools:cleanup`） |

### 6.4 Office 文件渲染：纯前端 JS（V1 主方案，已评审确认）

**渲染矩阵（客户端，无需服务器）**：

| 格式 | 库（vendored 到 `public/vendor_local/`） | 渲染方式 |
|---|---|---|
| docx | `docx-preview.min.js`（MIT） | 渲染为 HTML，放入沙箱 iframe |
| xls / xlsx | `xlsx.full.min.js`（SheetJS CE，Apache-2.0） | 解析 → HTML 表格，放入沙箱 iframe |
| pptx | `pptx-preview.min.js`（MIT） | 按页渲染 DOM，放入沙箱 iframe |
| doc / ppt | — | 提示另存为 .docx/.pptx |

- 三个库均为 ZIP+XML 解析渲染，**依赖浏览器端 bytes**，天然适配"宿主本地文件 + DataChannel 分块传输给对端"的链路，数据不落服务器。
- **安全**：Office 渲染统一放进 **sandbox iframe**（`sandbox="allow-scripts"` 亦不放行同源），渲染产物与主页面隔离；仅在用户显式打开自己或辅导对端传来的文档时产生，属低信任输入。
- 字体：使用浏览器本机字体（中文排版走系统字体即可，无额外安装）。

**可选高保真链路（默认关闭，`study_tools.office_server_conversion`）**：

若将来需要统一高保真渲染：上传 → `soffice --headless --convert-to pdf`（Symfony Process 调用）→ HMAC 签名 PDF URL（1h 有效）→ 按 PDF 渲染 + `content_pdf` 消息同步对端。此链路仅作配置选项，V1 不实现也不部署依赖。

---

## 7. 前端实现清单

| 文件 | 内容 |
|---|---|
| `resources/views/study/tools.blade.php` | 学习工具卡片列表页（读 `config/study_tools.php` 渲染卡片） |
| `resources/views/study/tools/tutoring.blade.php` | 远程辅导主页面：布局、工具栏、视频区、白板画布、房间面板（HTML/CSS + 行内 JS，沿用 study 模块风格）；Viewer 含**同源应用页识别**（课程 /study?plan= / 外部 URL 三种渲染分支） |
| `resources/views/study/index.blade.php` | **新增 `?plan=` 钩子**：加载读参 → `viewPlanDetail(planId)` + 计划卡高亮 + 滚动到详情区（实现"计划执行详情"直达） |
| `public/vendor_local/docx-preview.min.js` | .docx 纯前端渲染（MIT，随 JSZip bundle） |
| `public/vendor_local/xlsx.full.min.js` | .xls/.xlsx 解析渲染（SheetJS CE，Apache-2.0） |
| `public/vendor_local/pptx-preview.min.js` | .pptx 纯前端渲染（MIT，随 JSZip bundle） |
| `resources/views/layouts/app.blade.php` | "学习"子菜单新增 `['url'=>'/study/tools','label'=>'学习工具','icon'=>'fas fa-tools']` |

**tutoring.blade.php 内 JS 模块划分（单个 IIFE 按小节组织，便于无构建部署）**：

1. `Viewer`：URL/本地文件 → 各类型渲染 pipeline；
2. `Whiteboard`：画布、缩放坐标系、笔画/清空/撤销、本地历史栈；
3. `SignalingApi`：fetch 封装轮询 API（用 `taskApiFetch` 统一鉴权）；
4. `RtcManager`：getUserMedia、RTCPeerConnection、DataChannel、ICE、状态机；
5. `Sync`：选择通道（DC 优先 / 轮询中继兜底）发送 wb/file/content 消息；
6. `RoomUi`：房间面板、视频区控件、提示 toast。

> 说明：V1 将 JS 内联在 Blade 视图中，与 `study/index.blade.php` 等现有页面保持一致（该站大量页面即此模式），**无需改 webpack 构建**。若后续工具增多，再把公共部分抽到 `public/js/study-tools-core.js`。

---

## 8. 关键边界与异常处理

| 场景 | 处理 |
|---|---|
| 双方同一账号分别开两个浏览器 | 用 `peer_id`（前端 UUID）区分，避免自己收到自己的消息 |
| 本地文件 > 20MB | 拒绝发送并提示，仅本地渲染 |
| NAT 穿透失败（无 TURN） | 提示"视频可能无法直连"，白板自动降级轮询中继，可继续辅导 |
| 页面用 http 非 https 访问（非 localhost） | getUserMedia 会被浏览器拒绝 → 明确提示需 https 或 localhost |
| 标注画布与内容缩放不一致 | 统一逻辑坐标系：canvas 内坐标 = 内容坐标 × 当前 scale，对端只收逻辑坐标 |
| 房主先打开本地文件、学员后加入 | 加入成功后宿主重发 `content:file`（内存 blob）同步当前内容 |
| 轮询接口 401 | 走 `taskApiFetch` 的自动刷新 token 链路；仍失败则提示登录失效 |
| 关闭页面/挂断 | 发送 `leave`、释放 MediaStream tracks、清理轮询定时器，避免幽灵轮询 |
| 房间过期 | 轮询返回 `room_expired` → UI 提示并复位为未加入状态 |

---

## 9. 验证方案

1. **后端**：`php -l` 触碰文件；`php artisan migrate`；`php artisan route:list` 检查新路由；`vendor/bin/phpunit` 全量（若新增测试需另建 `tests/Feature/StudyToolTest.php`，V1 至少包含：创建房间、加入、消息往返、过期清理单测）。
2. **API 冒烟**：`curl http://testtask.congcong.us/api/v2/study/tools/...`（带 token）验证 create/join/messages 往返。
3. **Office 渲染（无服务器依赖）**：上传样例 docx/xlsx/pptx 验证三个 vendored 库渲染成功且进入沙箱 iframe；老 .doc/.ppt 验证提示另存为。
4. **前端构建**：本特性不涉及 webpack 变更；若改动了 `resources/assets` 才跑 `npm run dev`。
5. **端到端手测**（两台浏览器/同一局域网两台设备）：
   - A 创建房间 → B 通过邀请链接加入 → 视频互通；
   - A 打开课程 URL `/courses/{id}?item={章节}` → 双方同源 iframe 显示课程并**自动定位到该章节**；
   - A 打开 `/study?plan={计划ID}` → 双方显示该计划执行详情（计划卡高亮、任务列表定位）；
   - A 打开外部 URL（能嵌入的站点）→ 双方显示一致；被 X-Frame-Options 拒绝的站点 → 出现"在新标签打开"兜底按钮；
   - A 打开本地图像 → B 自动同步显示；A 打开本地 docx → 双方各自前端渲染一致显示；
   - 双方开启标注模式笔画实时同步；清空/撤销同步；
   - 断网直连（模拟无 P2P）验证白板走中继仍同步。
6. **CLI 通知**：按 AGENTS.md 用 `scripts/bark_notify.sh` 上报开始/完成；部署用 `scripts/deploy_task_rsync.sh`（部署前检查工作区，避免夹带无关改动；无新增服务器依赖，部署机无需 soffice）。

---

## 10. 实施步骤（里程碑）

1. **M1 后端基础**：迁移 + 模型 + Service + 房间/消息 API 控制器 + 路由 + 清理命令；curl 冒烟通过。
2. **M2 页面骨架**：菜单项、`study/tools` 卡片页、`study/tools/tutoring` 布局与房间面板（含轮询信令打通 SDP，双浏览器视频互通）。
3. **M3 内容与白板**：URL/本地文件渲染 pipeline（含**同源应用页**：课程 `?item=` 章节定位、`/study?plan=` 钩子、外部 URL 三类）、白板画布、DC 同步 + 中继兜底、文件分块传输、`?url=` 参数。
4. **M3.5 Office 前端渲染**：vendor 三个库（docx-preview / SheetJS CE / pptx-preview）到 `public/vendor_local/`，接入沙箱 iframe 渲染；.doc/.ppt 提示另存为。
5. **M4 收尾**：边界处理、手测清单全跑（含 office 前端渲染用例）、文档更新（本 spec → `docs/` 归档 + README 功能列表）、bark 通报、按需部署（无新增服务器依赖）。

---

## 11. 待确认问题（评审点）

1. **房号即凭证**是否可接受？如需口令可开 `study_tools.room_join_password`。
2. ~~本地 **doc/xls/ppt** 处理~~ ✅ 已确认：V1 纯前端 JS 渲染（docx-preview / SheetJS / pptx-preview），服务器零安装；LibreOffice 转 PDF 保留为默认关闭的配置开关。老二进制 .doc/.ppt 提示另存为 .docx/.pptx。
3. 是否需要 **录制/回放** 功能（V2 可做房间内的白板操作日志与视频本地录制）。
4. 标注角色：V1 采用"所有人可画 + 房主主导"，是否需要**只允许房主画**的模式开关？