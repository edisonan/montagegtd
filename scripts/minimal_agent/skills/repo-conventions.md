# 仓库约定（MontageGTD / Laravel 5.5）

处理本仓库任务时遵守以下约定：

- 这是 Laravel 5.5 / PHP 7 的老项目，**保持 PHP 7 兼容**，不要引入 PHP 8 专属语法。
- 改动 PHP 文件后用 `php -l <file>` 验证语法。
- 涉及数据库改动时新增 migration，不要改历史 migration。
- API 改动要同时更新 `routes/`、控制器和 `docs/` 下对应文档。
- 改 `resources/assets` 下的前端资源后跑 `npm run dev` 验证构建。
- 不要覆盖工作区里别人的未提交改动；部署前先 `git status` 检查。
- 本地联调地址是 `http://testtask.congcong.us/`，远程生产是 `https://task.congcong.us`。
- 需要部署时用 `bash scripts/deploy_task_rsync.sh`，但部署前先确认没有夹带无关改动。
- 会话开始时调用 `bash scripts/bark_notify.sh` 一次；完成时再调用一次并附上完成摘要。