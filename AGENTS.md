# AGENTS.md

This file gives Codex project-specific context for working in this repository.

Claude Code compatibility: if Claude Code is used, it should also follow this file. `CLAUDE.md` is only a compatibility pointer.

## Project

MontageGTD is a Laravel 5.5 / PHP 7 web application for GTD tasks, pomodoro focus, notes, RSS/articles, mind maps, plans, daily summaries, points, study modules, and LLM-related features.

Primary local integration URL:

- `http://testtask.congcong.us/`

Production/demo URL referenced by the README:

- `https://task.congcong.us`

## Tech Stack

- PHP `>=7.0`
- Laravel `5.5.*`
- MySQL
- Composer
- Laravel Mix 1 / Webpack
- Vue 2, jQuery, Bootstrap Sass
- PHPUnit 6

## Important Paths

- `app/Http/Controllers` - web controllers
- `app/Http/Controllers/Api/V2` - v2 API controllers
- `app/Models` - Eloquent models
- `app/Repositories` - data access and query helpers
- `app/Services` - business logic and integrations
- `app/Http/Middleware` - auth/token middleware
- `routes/web.php` - web routes and some web-session API bridge routes
- `routes/api.php` - API routes, mainly `/api/v2`
- `resources/views` - Blade templates
- `resources/assets/js` - frontend JS source
- `resources/assets/sass` - frontend styles
- `database/migrations` - schema migrations
- `database/seeds` - seeders
- `docs` - API and feature documentation
- `scripts` - audit, smoke-test, and utility scripts

## Common Commands

Install/update PHP autoload:

```bash
composer dump-autoload
```

Run migrations:

```bash
php artisan migrate
```

Show routes:

```bash
php artisan route:list
```

Run PHP tests:

```bash
vendor/bin/phpunit
```

Build frontend assets:

```bash
npm run dev
```

Watch frontend assets:

```bash
npm run watch
```

Production frontend build:

```bash
npm run prod
```

## Session And Deployment Workflow

- For each Codex session in this project, call the Bark notification script once to signal activity:

```bash
bash scripts/bark_notify.sh
```

- After making changes, run the narrowest useful checks from the Verification section. If the checks pass and deployment is requested or expected for the completed change, deploy with:

```bash
bash scripts/deploy_task_rsync.sh
```

- Before deploying, check the working tree and avoid deploying unrelated local changes unintentionally, because the deploy script syncs the project directory to the remote host.

## API Notes

The modern API surface is under `/api/v2`.

Authentication commonly uses hybrid token middleware:

- `hybrid.token:read` for read endpoints
- `hybrid.token:write` for write endpoints
- `hybrid.token` for general authenticated token endpoints

Personal Access Token requests use:

```http
Authorization: Bearer {personal_access_token}
```

API responses generally follow this shape:

```json
{
  "code": 9999,
  "msg": "ok",
  "result": {}
}
```

Relevant docs:

- `docs/openapi-v2.md`
- `docs/openapi-v2.yaml`
- `docs/personal-access-token.md`
- `docs/hybrid-auth-client.md`
- `docs/v2-migration-checklist.md`

## Development Guidelines

- Prefer existing Laravel 5.5 patterns in the repository over newer Laravel conventions.
- Keep compatibility with PHP 7 syntax. Do not use PHP 8-only features.
- Put shared business rules in `app/Services` or `app/Repositories` when existing code already follows that split.
- Keep controllers thin when practical, but match surrounding controller style for narrow fixes.
- For API changes, update routes, controller behavior, and docs together when the public contract changes.
- For database changes, add migrations instead of editing existing historical migrations unless explicitly asked.
- Do not overwrite unrelated local changes. The working tree may contain active user work.
- Use the local integration URL `http://testtask.congcong.us/` when checking web/API behavior manually.

## Verification

Choose the narrowest useful verification for the change:

- PHP syntax check for touched PHP files: `php -l path/to/file.php`
- Route visibility: `php artisan route:list`
- Focused API smoke checks with `curl` against `http://testtask.congcong.us/`
- PHPUnit for backend behavior: `vendor/bin/phpunit`
- Frontend asset build when changing files under `resources/assets`: `npm run dev`

## Caution

This is an older Laravel app with a large active worktree. Before editing, inspect the nearby code and preserve local conventions. Avoid broad refactors unless the task explicitly calls for them.
