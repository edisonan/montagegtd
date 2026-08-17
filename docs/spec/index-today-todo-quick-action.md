# 首页待办「设为今日待办」快捷操作 规格说明

状态：Draft
版本：0.2
日期：2026-07-23
范围：首页（`/index`）待办面板（仅首页，不影响 `/tasks` 列表页与四象限页）

## 1. 背景与目标

首页右侧「待办事项」面板是高频操作台。目前每条任务的悬浮操作栏（`.task-actions`）已提供添子任务、置顶、设为正在做、编辑、时间设置、评分备注、删除、折叠、记想法等操作，但缺少一个"快速把任务排到今天"的入口。

目标：在待办项操作栏中新增一个快捷操作按钮，点击后弹出「设为今日待办」弹窗，快速把该任务的截止时间（`deadline`）设为今天（默认当天 18:00，支持 ±10 分钟微调，已有 deadline 时回填原值），并在**首页待办面板**的列表排序中让"越接近今天的 deadline 越靠前"。

## 2. 现状梳理

### 2.1 涉及页面与文件

- 页面模板：`resources/views/index/index.blade.php`（右栏待办面板）。
- 任务列表渲染：
  - `createTaskListItem(data, listType)` 生成任务 `<li>`，底部为 `task-actions` 操作栏（默认隐藏、hover 显示）。
  - `showtasks()` 调用 `GET /api/v2/tasks/all?status=1&mode=模式` 拉取列表，取非"正在做"任务后直接渲染；目前**不做二次排序**，顺序由后端返回决定。
- 接口：
  - `GET /api/v2/tasks/{id}` → `App\Http\Controllers\Api\V2\TaskController@show`，返回完整任务（含 `deadline`）。
  - `PUT /api/v2/tasks/{id}` → `TaskController@update`，已支持 `deadline`（校验 `nullable|date_format:Y-m-d H:i:s`，无过去时间限制）。
  - 列表数据链路：`Api\V2\TaskController@getAllList` → `TaskService::getAllList` → `TaskRepository::getUserAllListByStatusMode`。

### 2.2 数据现状

- `tasks` 表已有 `deadline`（timestamp，注释"截止时间"），模型 `app/Models/Task.php` 的 `$fillable` 已包含 `deadline`。**无需新增迁移/字段。**
- `deadline` 与 `planned_start_time` / `planned_end_time` / `remindtime` 相互独立；首页现有「任务时间设置」弹窗只维护后三者，未暴露 `deadline`。

### 2.3 排序现状（后端，保持不变）

`TaskRepository::getUserAllListByStatusMode()`（L103）：

```php
$tasks->orderBy('is_top', 'desc')
     ->orderBy('priority', 'desc')
     ->orderBy('updated_at', 'desc');
```

随后 `TaskService::getAllList()` 把子任务按父任务顺序插到父任务之后（父子相邻，顺序不破坏）。**本次不改动后端查询**，排序在首页前端完成（见第 6 节）。

## 3. 需求拆解

1. 待办项操作栏新增一个快捷操作按钮。
2. 点击弹出「设为今日待办」弹窗。
3. 弹窗内展示截止时间输入：
   - 若任务已有 `deadline`：回填展示之前的 deadline 时间；
   - 否则默认当天 18:00（若当前已过 18:00，顺延 3 小时，见 4.3）。
4. 提供「+10 分钟 / -10 分钟」快捷微调。
5. 保存后任务获得当天 deadline；**首页待办面板**排序让"有 deadline 且越接近当前时间（今天）的越靠前"。
6. **排序约束（已确认）**：置顶（`is_top`）仍为第一优先级，始终排最前；排序仅在首页待办面板生效，不影响 `/tasks` 列表页与四象限页。

## 4. 交互规格

### 4.1 操作栏按钮

位置：`.task-actions` 内，紧跟「时间设置」（`far fa-clock`）按钮之后。

```html
<button class="action-button text-gray-400 hover:text-sky-500"
        onclick="openTodayTaskModal(${data.id})"
        title="设为今日待办">
    <i class="fas fa-calendar-check"></i>
</button>
```

- 图标建议 `fa-calendar-check`，hover 用 sky/cyan 系色（区别于现有蓝/绿/黄/红/紫）。
- 所有任务（含子任务）都渲染该按钮；行为见 9.5。

### 4.2 弹窗结构与状态

新增 `#todayTaskModal`，结构与现有 `#taskScheduleModal` 保持一致（居中卡片、遮罩点击关闭）：

- 标题：**设为今日待办**；右上角 × 关闭。
- 任务名展示（只读）。
- 截止时间：`datetime-local` 输入框（id 建议 `todayTaskDeadlineInput`）。
- 快捷操作行：
  - `-10 分钟`（`far fa-minus`）
  - `+10 分钟`（`far fa-plus`）
  - （可选项）`今天 18:00` 重置、`清除`（清除时后端传 `deadline: null`）
- 底部：`取消` / `设为今日待办`（主按钮）。

### 4.3 打开与默认值逻辑

`openTodayTaskModal(taskId)`：

1. `GET /api/v2/tasks/{id}` 拉取任务。
2. `task.deadline` 非空 → 用 `toLocalDatetimeValue(deadline)` 回填输入框（**展示之前 deadline 时间**）。
3. 为空 → 按以下规则取默认值：
   - 当前时刻 ≤ 当天 18:00：默认当天 18:00（`YYYY-MM-DDT18:00`）；
   - 当前时刻 > 当天 18:00：默认值顺延为**当前时刻 + 3 小时**（分钟取整，跨天自然顺延）。
4. 打开弹窗并聚焦输入框。

### 4.4 ±10 分钟微调

`adjustTodayTaskDeadline(deltaMin)`：

- 读取当前输入值（`datetime-local`），±10 分钟写入回输入框（复用 `formatLocalDatetimeInput`）。
- 支持连续点击叠加。
- 输入为空时点击：基于默认值（4.3 规则）计算。

### 4.5 保存

`saveTodayTaskModal()`：

- `PUT /api/v2/tasks/{id}`，body：`{ "deadline": toApiDatetimeValue(inputValue) }`（转为 `Y-m-d H:i:s`）。
- 成功：关闭弹窗 → `showtasks()` 刷新 → 新排序立即生效，提示"已设为今日待办"。
- 失败：沿用 `showNotification('error', …)` 提示，不关闭弹窗。

## 5. 数据与接口

- 读：`GET /api/v2/tasks/{id}`（已返回 `deadline`，无需改动）。
- 写：`PUT /api/v2/tasks/{id}` 已支持 `deadline` 字段，**无需后端改动**。
- 数据库：`tasks.deadline` 已存在，无迁移。
- 文档：`docs/openapi-v2.md` 中 `PUT /tasks/{task}` 参数表若未列 `deadline`，本次实现时一并补齐（该字段实际已被 v2 校验规则支持）。

## 6. 排序规格（核心，仅首页待办面板）

在 `index.blade.php` 的 `showtasks()` 中，对取出的待渲染任务列表（`normalTasks`，已排除"正在做"任务）做**客户端稳定排序**。考虑到后端返回的列表是"父任务后紧跟其子任务"的扁平结构，排序前先按层级分组，避免排序拆散父子。

分组与排序算法：

1. **分组**：遍历后端返回的任务列表，`parent_task_id == null` 的任务开启新组，其余（子任务、孙任务）追加到当前组尾部——保持父子相邻。
2. **组级排序键**（从高到低）：
   - ① `is_top` 降序 —— 置顶任务始终最前（第一优先级，已确认不变）；
   - ② 是否为"有 deadline"分组 —— 有 `deadline` 的组排在无 `deadline` 的组之前；
   - ③ "有 deadline"组之间按 `|deadline 时间戳 - 当前时间戳|` 升序 —— **越接近当前时间（今天）的 deadline 越靠前**：今天 18:00 排明天之前，刚过期的紧随其后；
   - ④ 兜底：`priority` 降序 → `updated_at` 降序（保持与现状一致）；
   - ⑤ 稳定性兜底：以上键完全相同时保持后端返回顺序（显式记录原始下标作为末级比较键，避免依赖引擎排序稳定性）。
3. **扁平化**：按排序后的组顺序，组内保持原有父子顺序展平，得到最终渲染列表。

规则语义要点：

- 无 `deadline` 的任务之间、以及与排序键相同的情形，均保持现有后端顺序，**行为与现状完全一致**。
- 子任务不单独参与上浮：子任务的 `deadline` 仅决定其所在组（父任务）的位置，组内顺序不变。
- 该排序只作用于首页待办面板的 `showtasks()` 渲染路径；`/tasks` 列表页、`/taskpriority` 四象限页维持后端原排序，不受影响。

## 7. 实现要点（文件清单）

| 文件 | 改动 |
| --- | --- |
| `resources/views/index/index.blade.php` | ① `createTaskListItem` 的 `.task-actions` 增加快捷按钮；② 新增 `#todayTaskModal` 弹窗 HTML（参考 `#taskScheduleModal`）；③ 新增 `openTodayTaskModal` / `adjustTodayTaskDeadline` / `saveTodayTaskModal` / `closeTodayTaskModal`（复用 `toLocalDatetimeValue` / `toApiDatetimeValue` / `showNotification` / `apiRequest`）；④ `showtasks()` 内增加第 6 节的"分组 → 稳定排序 → 扁平化"逻辑 |
| `docs/openapi-v2.md`（若缺） | `PUT /tasks/{task}` 参数表补充 `deadline` |

**后端（`TaskRepository` / `TaskService` / 控制器）不改动。**

## 8. 验收标准

1. 首页任一待办项 hover 出现操作栏，含新"设为今日待办"按钮。
2. 点击后弹出弹窗，标题为"设为今日待办"。
3. 无 deadline 的任务：输入框默认当天 18:00（已过 18:00 时默认当前时刻 + 3 小时）；已有 deadline 的任务：回填原时间。
4. 点 ±10 分钟，输入值相应增减，可叠加；点保存，任务 deadline 更新成功并提示。
5. 保存后首页列表刷新，排序满足：置顶始终最前；有 deadline 的任务排在没有的之前；deadline 越接近当前时间越靠前；其余顺序与现状一致，父子任务保持相邻。
6. `/tasks` 列表页与 `/taskpriority` 排序与现状完全一致（未受影响）。
7. 全部改动后 `npm run dev` 编译通过，首页无 JS 报错。

## 9. 边界情况

1. **空输入保存**：弹窗内用户清空输入后保存 → `deadline` 传 `null`，任务进入无 deadline 分组（排序靠后）。`update()` 的校验允许 `null`。
2. **过期时间**：`PUT update()` 不校验过去时间（仅 `store` 校验），回填历史 deadline 或微调后仍可保存；已过期任务按距当前时刻的接近程度参与排序（刚过期的排在很久以前过期的前面）。
3. **默认值"顺延 3 小时"的边界**：当前时刻 = 当天 18:00 整时，按"≤ 18:00"处理，默认当天 18:00；跨天场景（如 23:30 打开，默认次日 02:30）自然顺延，`datetime-local` 自动切换到次日日期。
4. **时区**：`datetime-local` 为浏览器本地时间，转换复用现有 `toLocalDatetimeValue` / `toApiDatetimeValue`，与现有「任务时间设置」弹窗约定一致，无需新逻辑。
5. **子任务**：子任务同样显示该按钮并可单独设 deadline；排序上子任务跟随父任务位置，不单独上浮（见第 6 节）。
6. **性能**：排序为纯前端操作，任务量级为个人待办，无性能问题。
7. **并发**：沿用现有 `apiRequest` 流程，无新增并发风险。

## 10. 已确认决策

1. **排序口径**：按 deadline 距"当前时刻"的绝对值升序（今天 18:00 排明天之前，刚过期紧随其后）。
2. **默认值已过 18:00 时**：顺延为当前时刻 + 3 小时。
3. **排序生效范围**：仅首页待办面板（前端排序），不影响 `/tasks` 列表页与四象限页。
4. **置顶第一优先级**：`is_top` 始终排最前，保持不变。

## 11. 遗留待确认项（小项）

1. "顺延 3 小时"的精确口径：本规格按"当前时刻 + 3 小时"理解；若意图是固定"18:00 + 3 小时 = 21:00"，请指出。
2. 按钮位置与图标：默认"时间设置"之后 + `fa-calendar-check`；如需调整可改。
3. 是否提供「清除」（把任务移出今日待办分组）按钮：默认提供，可去掉。
4. 子任务是否允许"设为今日待办"：默认允许（与现有操作栏一致）。