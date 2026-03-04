# API v2 Migration Checklist

## Scope

This checklist tracks migration from legacy web/session routes to `/api/v2/*` token routes.
Auth target: `hybrid.token` (`pat_` + `uat_`).

## Auth (token)

| Capability | v2 route | Status |
|---|---|---|
| Login | `POST /api/v2/auth/login` | done |
| Register | `POST /api/v2/auth/register` | done |
| Refresh | `POST /api/v2/auth/refresh` | done |
| Password reset email | `POST /api/v2/auth/password/email` | done |
| Password reset submit | `POST /api/v2/auth/password/reset` | done |
| Logout | `POST /api/v2/auth/logout` | done |
| Verify/me | `GET /api/v2/auth/verify`, `GET /api/v2/auth/me` | done |
| Session bootstrap to token | `POST /api/v2/auth/bootstrap-session` | done |
| Login/Register page submit path | frontend default submit to `/api/v2/auth/login|register` (with legacy form fallback) | done |
| Password pages submit path | frontend default submit to `/api/v2/auth/password/email|reset` (with legacy form fallback) | done |

## Task

| Legacy capability | Legacy route | v2 route | Status |
|---|---|---|---|
| Task list (paged) | `GET /tasks` | `GET /api/v2/tasks` | done |
| Index dashboard summary | `GET /index` | `GET /api/v2/index` | done |
| Task all list | `GET /tasksall` | `GET /api/v2/tasks/all` | done |
| Task priority list | `GET /taskpriority` | `GET /api/v2/tasks/priority` | done |
| Parent tasks | `GET /taskparenttasks` | `GET /api/v2/tasks/parent-tasks` | done |
| Task detail | `GET /tasks/{task}` | `GET /api/v2/tasks/{task}` | done |
| Task create | `POST /task` | `POST /api/v2/tasks` | done |
| Task update | `POST /task/{task}` | `PUT /api/v2/tasks/{task}` | done |
| Task delete/finish | `DELETE /task/{task}` | `DELETE /api/v2/tasks/{task}` | done |

## Pomo

| Legacy capability | Legacy route | v2 route | Status |
|---|---|---|---|
| Pomo list | `GET /pomos` | `GET /api/v2/pomos` | done |
| Today pomos | `GET /pomostoday` | `GET /api/v2/pomos/today` | done |
| Pomo status | `GET /pomos/pomostatus` | `GET /api/v2/pomos/status` | done |
| Start pomo | `GET /pomos/start` | `POST /api/v2/pomos/start` | done |
| Discard pomo | `GET /pomos/discard/{pomo}` | `POST /api/v2/pomos/discard/{pomo}` | done |
| Discard (empty) | `GET /pomos/discard/` | `POST /api/v2/pomos/discard` | done |
| Complete/store pomo | `POST /pomo/{pomo}` | `POST /api/v2/pomos/{pomo}` | done |
| Update pomo name | `POST /pomoupdate/{pomo}` | `PUT /api/v2/pomos/{pomo}` | done |
| Delete pomo | `DELETE /pomo/{pomo}` | `DELETE /api/v2/pomos/{pomo}` | done |

## Note

| Legacy capability | Legacy route | v2 route | Status |
|---|---|---|---|
| Note index/filter | `GET /notes` | `GET /api/v2/notes` | done |
| Note create | `POST /note` | `POST /api/v2/notes` | done |
| Note detail (edit load) | `GET /noteupdate/{note}` | `GET /api/v2/notes/{note}` | done |
| Note update | `POST /noteupdate/{note}` | `PUT /api/v2/notes/{note}` | done |
| Note delete | `DELETE /note/{note}` | `DELETE /api/v2/notes/{note}` | done |
| Audio upload | `POST /notes/upload` | `POST /api/v2/notes/upload` | done |
| Audio stream | `GET /note/getRecord/{note}` | `GET /api/v2/notes/{note}/record` | done |

## Calendar

| Legacy capability | Legacy route | v2 route | Status |
|---|---|---|---|
| Calendar index info | `GET /cals` | `GET /api/v2/calendar` | done |
| Theme ICS | `GET /calics/{theme}` | `GET /api/v2/calendar/ics/{theme}` | done |
| Personal ICS by token | `GET /taskics/{cal_token}` | `GET /api/v2/calendar/taskics/{calToken}` | done |

## Article (already migrated)

| Legacy capability | v2 route | Status |
|---|---|---|
| Article list by status/filter | `GET /api/v2/articles` | done |
| Article list by feed | `GET /api/v2/articles/list` | done |
| Article detail | `GET /api/v2/articles/{article}` | done |
| Article delete (subscription relation) | `DELETE /api/v2/articles/{articleSub}` | done |
| Article audio record | `GET /api/v2/articles/{articleSub}/record` | done |
| Article proxy view | `GET /api/v2/articles/proxyview` | done |
| Nav info/count | `GET /api/v2/articles/navinfo`, `GET /api/v2/articles/navcountinfo` | done |
| Status update | `POST /api/v2/articles/status/{articleSub}`, `POST /api/v2/articles/allstatus` | done |
| Mark | `POST /api/v2/articles/mark` | done |

## Mind

| Legacy capability | Legacy route | v2 route | Status |
|---|---|---|---|
| Mind list | `GET /minds` | `GET /api/v2/minds` | done |
| Mind detail | `GET /mind/{mind}` | `GET /api/v2/minds/{mind}` | done |
| Mind jsmind data | `GET /mindajaxget/{mind}` | `GET /api/v2/minds/{mind}/jsmind` | done |
| Mind outline data | `GET /mindajaxoutlineget/{mind}` | `GET /api/v2/minds/{mind}/outline` | done |
| Mind create | `POST /mind` | `POST /api/v2/minds` | done |
| Mind update | `POST /mind/{mind}` | `PUT /api/v2/minds/{mind}` | done |
| Mind delete | `DELETE /mind/{mind}` | `DELETE /api/v2/minds/{mind}` | done |
| Mind add tag | `POST /mindaddtag/{mind}` | `POST /api/v2/minds/{mind}/tags` | done |

## Goal

| Legacy capability | Legacy route | v2 route | Status |
|---|---|---|---|
| Goal list | `GET /goals` | `GET /api/v2/goals` | done |
| Goal create | `POST /goal` | `POST /api/v2/goals` | done |
| Goal update | `POST /goal/{goal}` | `PUT /api/v2/goals/{goal}` | done |
| Goal delete/finish | `DELETE /goal/{goal}` | `DELETE /api/v2/goals/{goal}` | done |

## Setting + Kindle

| Legacy capability | Legacy route | v2 route | Status |
|---|---|---|---|
| Setting get | `GET /settings` | `GET /api/v2/settings` | done |
| Setting update (self) | `POST /setting` | `POST /api/v2/settings/current` | done |
| Setting update (id) | `POST /setting/{setting}` | `PUT /api/v2/settings/{setting}` | done |
| Setting test Kindle | `POST /settings/test-kindle` | `POST /api/v2/settings/test-kindle` | done |
| Setting test IFTTT | `POST /settings/test-ifttt` | `POST /api/v2/settings/test-ifttt` | done |
| Setting export | `GET /settings/export` | `GET /api/v2/settings/export` | done |
| Kindle setting page data | `GET /kindles` | `GET /api/v2/kindles` | done |
| Kindle test | `GET /kindle/test` | `POST /api/v2/kindles/test` | done |

## Course

| Legacy capability | Legacy route | v2 route | Status |
|---|---|---|---|
| Course my list | `GET /courses` | `GET /api/v2/courses` | done |
| Course management | `GET /course/management` | `GET /api/v2/courses/management` | done |
| User course enrollments | `GET /course-enrollments` | `GET /api/v2/course-enrollments` | done |
| Course detail | `GET /courses/{id}` | `GET /api/v2/courses/{id}` | done |
| Course create | `POST /courses` | `POST /api/v2/courses` | done |
| Course update | `PUT /courses/{id}` | `PUT /api/v2/courses/{id}` | done |
| Course delete | `DELETE /courses/{id}` | `DELETE /api/v2/courses/{id}` | done |
| Join course | `POST /courses/{id}/join` | `POST /api/v2/courses/{id}/join` | done |
| Course items list | `GET /courses/{courseId}/items` | `GET /api/v2/courses/{courseId}/items` | done |
| Course item detail | `GET /courses/{courseId}/items/{id}` | `GET /api/v2/courses/{courseId}/items/{id}` | done |
| Course structure | `GET /course-items/structure/{courseId}` | `GET /api/v2/course-items/structure/{courseId}` | done |
| Modal item detail | `GET /course-items/{id}` | `GET /api/v2/course-items/{id}` | done |
| Item create (nested) | `POST /courses/{courseId}/items` | `POST /api/v2/courses/{courseId}/items` | done |
| Item update (nested) | `PUT /courses/{courseId}/items/{id}` | `PUT /api/v2/courses/{courseId}/items/{id}` | done |
| Item delete (nested) | `DELETE /courses/{courseId}/items/{id}` | `DELETE /api/v2/courses/{courseId}/items/{id}` | done |
| Item create (modal) | `POST /course-items` | `POST /api/v2/course-items` | done |
| Item update (modal) | `POST /course-items/{id}` | `PUT /api/v2/course-items/{id}` | done |
| Item delete (modal) | `DELETE /course-items/{id}` | `DELETE /api/v2/course-items/{id}` | done |
| Discussion list | `GET /courses/{courseId}/discussions` | `GET /api/v2/courses/{courseId}/discussions` | done |
| Discussion create | `POST /courses/{courseId}/discussions` | `POST /api/v2/courses/{courseId}/discussions` | done |
| Discussion detail | `GET /courses/{courseId}/discussions/{id}` | `GET /api/v2/courses/{courseId}/discussions/{id}` | done |
| Discussion reply | `POST /courses/{courseId}/discussions/{id}/reply` | `POST /api/v2/courses/{courseId}/discussions/{id}/reply` | done |

## LLM (supplement)

| Legacy capability | Legacy route | v2 route | Status |
|---|---|---|---|
| Provider list/detail | mixed | `GET /api/v2/llm/providers`, `GET /api/v2/llm/providers/{id}` | done |
| Model detail | mixed | `GET /api/v2/llm/models/{id}` | done |
| Credential list/detail | mixed | `GET /api/v2/llm/credentials`, `GET /api/v2/llm/credentials/{id}` | done |
| Agent draft create/update/publish | `/api/llm-agents/*` | `POST /api/v2/llm/agents/create-draft`, `PUT /api/v2/llm/agents/{id}/draft`, `POST /api/v2/llm/agents/{id}/publish` | done |
| Session clear | web-only | `POST /api/v2/llm/sessions/{id}/clear` | done |
| Credential test | web-only | `POST /api/v2/llm/credentials/{id}/test` | done |
| Agent toggle status | web-only | `POST /api/v2/llm/agents/{id}/toggle-status` | done |
| Agent test chat | web-only | `POST /api/v2/llm/agents/{id}/test-chat` | done |
| Usage stats | `GET /llm/usage-stats` | `GET /api/v2/llm/usage-stats` | done |
| Ask AI alias | `POST /llm/ask-ai` | `POST /api/v2/llm/ask-ai` | done |

## Notes

- Legacy web pages (`/tasks`, `/pomos`, `/notes`, `/cals`) still exist for server-rendered UI compatibility.
- Frontend full switch to `/api/v2/*` should be done after login/session -> token bootstrap is in place.
- `goals/minds/settings/kindles` pages now use v2 API for core write actions (create/update/delete/test) via `TaskApiBridge`.
- `courses/create` and `courses/show|management` join/create actions now call v2 (`/api/v2/courses*`) via `TaskApiBridge`.
- `course-items/index` + `components/course-item-modal` now use v2 API for structure/query/create/update/delete via `TaskApiBridge`.
- `minds/outlineview` + `minds/outlineviewv2` data loading now uses v2 endpoints (`/api/v2/minds/{id}/outline|jsmind`).
- `llm/index` and `llm/llmmanagement` core API calls now target `/api/v2/llm/*`.
- `llm/agentmanagement`, `llm/agent-editor`, `components/llm-agent-create-modal`, `components/ai-ask-modal` requests are switched to `/api/v2/llm/*`.
- `articles/*`, `feeds/*`, `notes/*`, `pomos/*`, `cals/*` frontend calls are removed from legacy fallback mode and now use token API path only through `TaskApiBridge`.
- `feeds/setting` delete and sort operations are migrated from jQuery legacy routes to v2 routes (`DELETE /api/v2/feeds/{id}`, `POST /api/v2/categories/sort`, `POST /api/v2/feeds/sort`).
- `feeds/update` actions are migrated to v2: update (`PUT /api/v2/feeds/{id}`), check url (`GET /api/v2/feeds/check-feed-url`), refresh/toggle/clear (`POST /api/v2/feeds/{id}/refresh|toggle-status|clear-articles`).
- `notes` like action is now routed through v2 (`POST /api/v2/notes/{id}/like`) to avoid legacy `/note/{id}/like` dependency.
- Added v2 backend coverage for `ThingController`, `DailySummaryController`, `AchievementController`, `PointController`, `StatisticsController`:
  `GET/POST/PUT/DELETE /api/v2/things*`,
  `GET/POST/PUT/DELETE /api/v2/daily-summaries*` (+`/tips` `/by-date`),
  `GET /api/v2/achievements`, `POST /api/v2/achievements/claim`,
  `GET /api/v2/points`, `GET /api/v2/statistics`.
- Added v2 backend coverage for `AccountController`, `PersonalAccessTokenController`, `HelpController`, `ThirdController`, `ApplicationController`, `CodeController`:
  `GET /api/v2/accounts`,
  `GET/POST/DELETE /api/v2/personal-access-tokens*`,
  `GET /api/v2/help/about`, `POST /api/v2/help/feedback`,
  `GET /api/v2/thirds`, `POST /api/v2/thirds/fanfou/request`, `POST /api/v2/thirds/fanfou/test`,
  `GET /api/v2/applications/{appSlug}/{codePath}`, `ANY /api/v2/codes/{codeInfo}`.
- Frontend pages completed for this batch:
  `dailysummarys/index` delete action -> `DELETE /api/v2/daily-summaries/{id}`,
  `help/feedback` submit -> `POST /api/v2/help/feedback`,
  `personal_access_tokens/index` create/delete -> `POST|DELETE /api/v2/personal-access-tokens`,
  `personal_access_tokens/create` submit -> `POST /api/v2/personal-access-tokens`.
- Added `Api/V2` wrappers for `LlmController`, `LlmAgentController`, `LlmSessionController`, and switched all `/api/v2/llm/*` routes to these wrappers so token auth context is injected before legacy LLM logic executes.
- Added complete v2 category CRUD backend:
  `GET /api/v2/categories`, `GET /api/v2/categories/{id}`,
  `POST /api/v2/categories`, `PUT /api/v2/categories/{id}`, `DELETE /api/v2/categories/{id}`.
- Added v2 feed create endpoint:
  `POST /api/v2/feeds` (paired with existing check/update/delete/sort operations),
  and `POST /api/v2/feeds/import-opml` for OPML batch import.
- Frontend pages switched in this round:
  `categorys/index` create/delete -> `/api/v2/categories*`,
  `categorys/update` update/delete -> `/api/v2/categories/{id}`,
  `feeds/index` add new feed submit -> `POST /api/v2/feeds`.
- Unified remaining major form `action` attributes to v2 API endpoints (goal/mind/note/course/daily-summary/achievement/thing/category/feed) to avoid accidental fallback to legacy web routes.
- `feeds/weiborss`, `feeds/weixinrss`, `feeds/opml`, `tasks/update`, `goals/update`, `notes/update`, `things/update`, `feeds/update`, `dailysummarys/update`, `settings/index`, `kindles/index`, `courses/show|management` form actions are aligned to `/api/v2/*`.
- `public/js/jsmind.js` keyboard edit/create/delete operations are switched from legacy `/mind*` to `/api/v2/minds*` and now attach Bearer token from `TaskApiClient`.
- Added migration guard scripts:
  `scripts/v2_token_smoke.sh` for broad PAT/uat token endpoint smoke checks,
  `scripts/audit_legacy_endpoints.sh` for static scan of legacy non-v2 form/js submit paths.
- Added `scripts/audit_v2_route_parity.sh` to assert key business capabilities all have `/api/v2/*` route coverage.
- Added `scripts/audit_route_handler_integrity.sh` to detect route->controller missing-method risks and avoid latent 500 errors.
- Added `scripts/audit_v2_action_coverage.sh` to compare legacy `web.php` controller actions vs `/api/v2` actions (with alias normalization + web-only allowlist) and surface real unmigrated items.
- Wechat mini legacy `/api/wechat/*` routes are migrated to `/api/v2/wechat/*` with hybrid token auth:
  `POST /api/v2/wechat/login`,
  `GET /api/v2/wechat/explorer|articles|articleview|notes`,
  `POST /api/v2/wechat/notes|addNote|articles/status`.
- PAT smoke test for newly added routes is blocked in current environment because `http://testtask.congcong.us` is unreachable from this sandbox (`curl: (7) Couldn't connect to server`).
