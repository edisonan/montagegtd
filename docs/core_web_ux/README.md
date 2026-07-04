# Core Web UX 文档总览

本文档目录基于 `resources/views/index`、`articles`、`notes`、`focus`、`journals`、`tasks` 的现有 Blade 模板整理，目标是为其他端复用现有 Web UX 提供结构化参考。

## 文档组织

- `index_current_ux.md` / `index_pc_recommendations.md`
- `articles_current_ux.md` / `articles_pc_recommendations.md`
- `notes_current_ux.md` / `notes_pc_recommendations.md`
- `focus_current_ux.md` / `focus_pc_recommendations.md`
- `journals_current_ux.md` / `journals_pc_recommendations.md`
- `tasks_current_ux.md` / `tasks_pc_recommendations.md`
- `shared_patterns.md`: 跨模块共用的交互模式、视觉语言、弹窗习惯

## 约束说明

- 本轮整理以当前模板代码为准，不推断未落地能力。
- `focus` 目录当前仅有 `index.blade.php`，因此该类的现状文档只覆盖“专注历史页”。
- `index` 类当前主要是首页双栏工作台，以及它依赖的任务编辑、手账创建、任务排期、评分弹层。
- 某些体验能力由页面内联脚本和组件弹窗共同组成，文档中已一并纳入。

## 推荐阅读顺序

1. 先读 `shared_patterns.md`，建立全局设计语言认知。
2. 再按业务域阅读对应 `current_ux`。
3. 最后看对应 `pc_recommendations`，评估桌面端扩展空间。
