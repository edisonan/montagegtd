<div align="center">

English | [简体中文](./README.zh-CN.md)

# 🎬 MontageGTD

**More than GTD — a knowledge-management web app combining Pomodoro focus, tasks, notes, RSS reading, mind maps and an AI assistant**

[![PHP](https://img.shields.io/badge/PHP-%3E%3D%207.0-8892BF?logo=php&logoColor=white)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-5.5-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-%3E%3D%205.5-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![License](https://img.shields.io/badge/license-MIT-brightgreen)](LICENSE.txt)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen)](https://github.com/edisonan/montagegtd/pulls)

[🌐 Live Demo](https://task.congcong.us) · [📦 Gitee Repo](https://gitee.com/accacc/task) · [🐙 GitHub Repo](https://github.com/edisonan/montagegtd) · [📖 Wiki](https://gitee.com/accacc/task/wikis/Home?sort_id=42169)

</div>

---

> **Why "Montage"?** The name comes from the film-editing technique of assembling many shots in different ways to tell different stories. Even as many predicted cinema's demise, film remains one of the most resilient industries — a place where ordinary, fragile humans get to rule an imaginary world.
>
> This platform shares that spirit: it records the small moments of ordinary people in everyday life and remarkable times, like a **"Life in a Day"**. Here you are the **director** of the plot, the **screenwriter** of the story, and the **actor** living it. Every record and every bit of growth is meant to be cut together like a well-edited montage — into a grand film of your own life.

![Screenshot](public/img/index.jpg)

---

## ✨ Features

| Module | Highlights | Status |
| --- | --- | --- |
| 🍅 Pomodoro + Tasks | Guided onboarding · customizable timer (min. 10 minutes) · double-click a todo to attach a pomodoro record · log meaningful work done without a timer · todo reminders & eye-catching deadline alerts · **4-quadrant (Eisenhower) matrix** · group tasks under goals · daily AM/PM pomodoro reminders | ✅ |
| 📰 Reading / RSS | Feed management · drag to reorder · read-it-later · star / favorite · share to social networks · text-to-speech for articles · explorer & immersive reading modes · AI summaries & classification | ✅ |
| 🧠 Mind Maps | One-click creation · keyboard shortcuts to insert / edit nodes · descriptions · export as image | ✅ |
| 📝 Notes | Tags · public / private publishing · voice input (Chrome) · share web pages & images into notes · guided daily goals & reviews | ✅ |
| 📚 Kindle Push | Push subscriptions to your Kindle · image support · test push | ✅ |
| 📊 Statistics | Monthly pie & bar charts for reading, pomodoro, notes and more | ✅ |
| 📅 Daily Summary | Daily reminder · auto-collects the day's tasks, mind maps, notes and reading as writing aids | ✅ |
| 🎓 Study | Study check-ins · course management · chapter management · learning-progress aggregation | ✅ |
| ⭐ Points | Gamified point incentives across reading, notes, study and more | ✅ |
| 🤖 AI Assistant (LLM) | Agent-based multi-turn chat workspace · attach text / code / DOCX / PDF · quick scenes like "organize my day", "develop an idea", "condense content" · AI article digests & classification · provider / model / credential management (user-level & global) | ✅ |
| 🚧 Roadmap | Weibo & WeChat Official Account subscriptions · personalized article recommendations · daily-digest subscriptions · one-click favorite-article → HTML · custom RSS push · finer pomodoro statistics | 🚧 |

## 🧱 Tech Stack

- **Backend**: [Laravel 5.5](https://laravel.com) · **Language**: PHP >= 7.0
- **Database**: MySQL >= 5.5 (Redis / Predis optional for cache & queue)
- **Frontend**: Blade + Vue 2 + jQuery + Bootstrap Sass, built with Laravel Mix
- **Admin**: [encore/laravel-admin](https://github.com/z-song/laravel-admin)
- **Deployment**: built-in `Dockerfile` & `docker-compose.yml` (Nginx + PHP-FPM + MySQL)

## 🚀 Getting Started

### Live Demo

Try the full feature set at [https://task.congcong.us](https://task.congcong.us).

### Docker (recommended)

```bash
docker-compose up -d
# open http://localhost:8080
```

### Manual Setup

```bash
# 1. Clone the repository
git clone https://github.com/edisonan/montagegtd.git
cd montagegtd

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate
# edit .env to set up MySQL and other connections

# 4. Initialize the database
php artisan migrate --seed

# 5. Build frontend assets
npm install
npm run dev

# 6. Start the server
php artisan serve
```

> 💡 The AI assistant needs an extra LLM table initialization — see [LLM_FEATURES_INSTALL.md](./LLM_FEATURES_INSTALL.md).

## 🔌 Browser Integrations

**Quick RSS subscribe** — install [RSS Subscription Extension](https://chrome.google.com/webstore/detail/rss-subscription-extensio/nlbjncdgjeocebhnmkbbbdekmmmcbfjd) for Chrome (or a compatible browser), add a subscription entry in its options and hit "Subscribe":

```
Name:  Subscribe to Montage GTD
URL:   http://task.congcong.us/feeds?url=%s
```

**Quick sharing into Notes** — install [Right-click Search (右键搜)](https://chrome.google.com/webstore/detail/context-menus/phlfmkfpmphogkomddckmggcfpmfchpn) and configure the custom menus:

```
Page menu:    https://task.congcong.us/notes?add_content=%s
Text menu:    https://task.congcong.us/notes?add_content=%s
Image menu:   https://task.congcong.us/notes?type=image&add_content=%s
Link menu:    https://task.congcong.us/notes?add_content=%s
```

<details>
<summary>Or import the full config on the extension settings page (click to expand)</summary>

```json
{"mcGroup":"[]","linBack":"[]","txtSelect":"[\"montage\"]","lb1":"\"2\"","rt2":"\"2\"","shorten":"\"googl\"","txtBack":"[]","zh_TW":"false","menBack":"[]","txtIncognito":"[]","analytics":"true","menSelect":"[\"montage\"]","picIncognito":"[]","picCustom":"[[\"montage\",\"https://task.congcong.us/notes?type=image&add_content=%s\"]]","rb2":"\"2\"","linCustom":"[[\"montage\",\"https://task.congcong.us/notes?add_content=%s\"]]","picBack":"[]","lcGroup":"[]","pcGroup":"[]","qr_size":"250","txtCustom":"[[\"montage\",\"https://task.congcong.us/notes?add_content=%s\"]]","rt1":"\"1\"","isFlag":"true","ru":"false","lt2":"\"1\"","tcGroup":"[]","linSelect":"[\"montage\"]","back":"false","names":"{}","menCustom":"[[\"montage\",\"https://task.congcong.us/notes?add_content=%s\"]]","lt1":"\"1\"","newPage":"true","zh_CN":"false","phrase":"\"montagegtd\"","locale":"\"zh_CN\"","lb2":"\"1\"","picSelect":"[\"montage\"]","rb1":"\"2\"","isEdit":"true","linIncognito":"[]","en":"false","menIncognito":"[]"}
```

</details>

## 🔑 API

The project exposes **Personal Access Token (PAT)** based API routes (prefix `/api/v1`). Authenticate with:

```bash
Authorization: Bearer {personal_access_token}
```

Scope conventions:

| Scope | Purpose | Example |
| --- | --- | --- |
| `read` | Read-only queries | `GET /api/v1/auth/me`, session / model queries |
| `write` | Write operations | create session, chat, agent CRUD |
| `admin` | High-privilege config | provider / model / credential management |

```bash
curl -H "Authorization: Bearer <TOKEN>" \
  https://your-domain/api/v1/auth/me
```

> The modern `/api/v2` surface uses hybrid token middleware (`hybrid.token:read` / `hybrid.token:write`). OpenAPI docs: `docs/openapi-v2.md` and `docs/openapi-v2.yaml`.

## 📚 Documentation

- [Wiki (deployment & technical docs)](https://gitee.com/accacc/task/wikis/Home?sort_id=42169)
- [LLM Features Install Guide](./LLM_FEATURES_INSTALL.md)
- [`docs/`](./docs) — environments, OpenAPI v2, PAT, hybrid-auth client and more
- [`docs/environments.md`](./docs/environments.md) — local/production environment mapping & deployment notes

## 🤝 Contributing

Issues and pull requests are welcome — let's co-edit the "life montage" of everyone.

## 📄 License

[MIT](./LICENSE.txt)

## 🙏 Acknowledgements

[![JetBrains](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://jb.gg/OpenSourceSupport)

Thanks to [JetBrains](https://jb.gg/OpenSourceSupport) for supporting open source.