#!/usr/bin/env python3
"""Montage GTD domain CLI for /api/v2 workbench operations."""

import argparse
import json
import os
import sys
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path


DEFAULT_BASE_URL = "https://task.congcong.us/api/v2"
SUCCESS_CODE = 9999


def env_value(primary, fallback=None, default=None):
    return os.environ.get(primary) or (os.environ.get(fallback) if fallback else None) or default


def load_json(raw):
    if not raw:
        return None
    if raw == "-":
        raw = sys.stdin.read()
    raw = raw.strip()
    if raw.startswith("@"):
        raw = Path(raw[1:]).read_text()
    if not raw:
        return None
    try:
        return json.loads(raw)
    except json.JSONDecodeError as exc:
        raise SystemExit("Invalid JSON: %s" % exc)


def add_if_present(body, key, value):
    if value is not None:
        body[key] = value


def text_value(value, file_path=None):
    if file_path:
        return Path(file_path).read_text()
    if value == "-":
        return sys.stdin.read()
    return value


def build_url(base_url, path, query_items):
    url = (base_url or DEFAULT_BASE_URL).rstrip("/") + (path if path.startswith("/") else "/" + path)
    query = []
    for item in query_items or []:
        if "=" not in item:
            raise SystemExit("--query must use key=value: %s" % item)
        key, value = item.split("=", 1)
        query.append((key, value))
    if query:
        url += "?" + urllib.parse.urlencode(query)
    return url


def api_request(args, method, path, data=None, query=None, token=None):
    token = token if token is not None else getattr(args, "token", None)
    base_url = getattr(args, "base_url", None) or DEFAULT_BASE_URL
    headers = {"Accept": "application/json", "User-Agent": "montagegtd-cli/1.0"}
    body = None
    if data is not None:
        body = json.dumps(data, ensure_ascii=False).encode("utf-8")
        headers["Content-Type"] = "application/json"
    if token:
        headers["Authorization"] = "Bearer " + token

    request = urllib.request.Request(
        build_url(base_url, path, query or []),
        data=body,
        headers=headers,
        method=method.upper(),
    )
    try:
        with urllib.request.urlopen(request, timeout=getattr(args, "timeout", 30)) as response:
            raw = response.read().decode("utf-8")
            return response.status, parse_json(raw), raw
    except urllib.error.HTTPError as exc:
        raw = exc.read().decode("utf-8", errors="replace")
        return exc.code, parse_json(raw), raw
    except urllib.error.URLError as exc:
        raise SystemExit("Request failed: %s" % exc)


def parse_json(raw):
    if not raw:
        return None
    try:
        return json.loads(raw)
    except json.JSONDecodeError:
        return None


def nested_value(data, path, default=None):
    current = data
    for key in path:
        if not isinstance(current, dict) or key not in current:
            return default
        current = current[key]
    return current


def clean_text(value):
    if value is None:
        return ""
    return " ".join(str(value).replace("<br />", " ").replace("<br>", " ").split())


def render_table(headers, rows):
    string_rows = [[clean_text(value) for value in row] for row in rows]
    widths = [len(header) for header in headers]
    for row in string_rows:
        for index, value in enumerate(row):
            widths[index] = max(widths[index], len(value))
    print("  ".join(header.ljust(widths[index]) for index, header in enumerate(headers)))
    print("  ".join("-" * width for width in widths))
    for row in string_rows:
        print("  ".join(value.ljust(widths[index]) for index, value in enumerate(row)))


def render_task_list(parsed):
    tasks = nested_value(parsed, ("result", "tasks"), []) or []
    rows = []
    for task in tasks:
        rows.append((
            task.get("id"),
            task.get("status"),
            task.get("is_doing"),
            task.get("priority"),
            task.get("mode"),
            task.get("deadline") or "",
            task.get("name") or "",
        ))
    render_table(("ID", "STATUS", "DOING", "PRI", "MODE", "DEADLINE", "NAME"), rows)


def render_note_list(parsed):
    notes = nested_value(parsed, ("result", "notes"), {}) or {}
    if isinstance(notes, dict):
        notes = notes.get("data", [])
    rows = []
    for note in notes or []:
        summary = note.get("name") or note.get("content") or ""
        rows.append((
            note.get("id"),
            note.get("status"),
            note.get("source_type"),
            note.get("source_id"),
            note.get("updated_at") or "",
            clean_text(summary)[:80],
        ))
    render_table(("ID", "STATUS", "SOURCE", "SOURCE_ID", "UPDATED", "NOTE"), rows)


def render_article_list(parsed):
    articles = nested_value(parsed, ("result", "articles"), []) or []
    rows = []
    for article_sub in articles:
        article = article_sub.get("article") or {}
        feed = article.get("feed") or {}
        rows.append((
            article_sub.get("id"),
            article_sub.get("article_id"),
            article_sub.get("status"),
            article_sub.get("personalized_score"),
            feed.get("feed_name") or "",
            article.get("published") or "",
            clean_text(article.get("subject") or "")[:90],
        ))
    render_table(("SUB_ID", "ARTICLE_ID", "STATUS", "SCORE", "FEED", "PUBLISHED", "SUBJECT"), rows)


def render_record(parsed):
    result = parsed.get("result") if isinstance(parsed, dict) else parsed
    if not isinstance(result, dict):
        print(json.dumps(result, ensure_ascii=False, indent=2))
        return
    render_table(("FIELD", "VALUE"), [(key, value) for key, value in sorted(result.items())])


def emit(status, parsed, raw, raw_output=False, check_code=True, output="json", renderer=None):
    if raw_output or output == "raw":
        print(raw)
    elif output == "table" and renderer is not None and parsed is not None:
        renderer(parsed)
    elif parsed is not None:
        print(json.dumps(parsed, ensure_ascii=False, indent=2))
    elif raw:
        print(raw)
    else:
        print(json.dumps({"http_status": status, "body": ""}, ensure_ascii=False, indent=2))

    if status < 200 or status >= 300:
        raise SystemExit(1)
    if check_code and isinstance(parsed, dict) and parsed.get("code") not in (None, SUCCESS_CODE):
        raise SystemExit(1)


def call(args, method, path, data=None, query=None, token=None, check_code=True, renderer=None):
    status, parsed, raw = api_request(args, method, path, data=data, query=query, token=token)
    emit(
        status,
        parsed,
        raw,
        getattr(args, "raw", False),
        check_code=check_code,
        output=getattr(args, "output", "json"),
        renderer=renderer,
    )


def find_openapi(explicit):
    candidates = []
    if explicit:
        candidates.append(Path(explicit))
    openapi_env = env_value("MONTAGE_GTD_OPENAPI", "TASK_GITEE_OPENAPI")
    if openapi_env:
        candidates.append(Path(openapi_env))
    cwd = Path.cwd()
    candidates.extend([cwd / "docs/openapi-v2.yaml", cwd / "openapi-v2.yaml"])
    for candidate in candidates:
        if candidate.exists():
            return candidate
    return None


def parse_openapi(path):
    rows = []
    current_path = current_method = capability = tag = None
    in_tags = False

    def flush():
        if current_path and current_method:
            rows.append((current_method.upper(), current_path, capability or "", tag or ""))

    for raw in Path(path).read_text().splitlines():
        line = raw.rstrip()
        stripped = line.strip()
        if line.startswith("  \"") and stripped.endswith(":"):
            flush()
            current_path = stripped.strip(":").strip("\"")
            current_method = capability = tag = None
            in_tags = False
            continue
        if current_path and line.startswith("    ") and not line.startswith("      "):
            method = stripped.rstrip(":")
            if method in ("get", "post", "put", "delete", "patch"):
                flush()
                current_method = method
                capability = tag = None
                in_tags = False
            continue
        if current_path and current_method:
            if stripped == "tags:":
                in_tags = True
                continue
            if in_tags and stripped.startswith("- "):
                tag = stripped[2:].strip()
                in_tags = False
                continue
            if stripped.startswith("x-token-capability:"):
                capability = stripped.split(":", 1)[1].strip().strip("\"'")
    flush()
    return rows


def cmd_health(args):
    call(args, "GET", "/health", token="", check_code=False)


def cmd_me(args):
    call(args, "GET", "/auth/me")


def cmd_request(args):
    call(args, args.method, args.path, data=load_json(args.data), query=args.query)


def cmd_endpoints(args):
    path = find_openapi(args.openapi)
    if not path:
        raise SystemExit("OpenAPI file not found. Run from repo root or set MONTAGE_GTD_OPENAPI.")
    rows = parse_openapi(path)
    if args.capability:
        rows = [row for row in rows if row[2] == args.capability]
    if args.tag:
        rows = [row for row in rows if row[3] == args.tag]
    for method, endpoint, capability, tag in rows:
        print("%-6s %-55s %-8s %s" % (method, endpoint, capability, tag))


def cmd_pat_create(args):
    call(args, "POST", "/personal-access-tokens", data={
        "name": args.name,
        "scopes": [s.strip() for s in args.scopes.split(",") if s.strip()],
        "expires_at": args.expires_at,
    })


def cmd_pat_revoke(args):
    query = ["force_delete=1"] if args.force_delete else []
    call(args, "DELETE", "/personal-access-tokens/%s" % args.token_id, query=query)


def cmd_task_list(args):
    query = []
    add_if_present_query(query, "status", args.status)
    add_if_present_query(query, "page_count", args.page_count)
    call(args, "GET", "/tasks", query=query, renderer=render_task_list)


def cmd_task_show(args):
    call(args, "GET", "/tasks/%s" % args.task_id, renderer=render_record)


def cmd_task_counts(args):
    call(args, "GET", "/tasks/tab-counts", renderer=render_record)


def cmd_task_all(args):
    query = []
    add_if_present_query(query, "status", args.status)
    add_if_present_query(query, "mode", args.mode)
    call(args, "GET", "/tasks/all", query=query)


def cmd_task_priority(args):
    query = []
    add_if_present_query(query, "status", args.status)
    add_if_present_query(query, "mode", args.mode)
    call(args, "GET", "/tasks/priority", query=query)


def cmd_task_parents(args):
    query = []
    add_if_present_query(query, "exclude_task_id", args.exclude_task_id)
    call(args, "GET", "/tasks/parent-tasks", query=query)


def cmd_task_create(args):
    body = load_json(args.data) or {}
    add_if_present(body, "name", args.name)
    add_if_present(body, "mode", args.mode)
    add_if_present(body, "priority", args.priority)
    add_if_present(body, "remindtime", args.remindtime)
    add_if_present(body, "deadline", args.deadline)
    add_if_present(body, "parent_task_id", args.parent_task_id)
    add_if_present(body, "plan_id", args.plan_id)
    if body.get("mode") is None:
        body["mode"] = 1
    if not body.get("name"):
        raise SystemExit("task create requires --name, or --data with name.")
    call(args, "POST", "/tasks", data=body)


def cmd_task_update(args):
    body = load_json(args.data) or {}
    add_if_present(body, "status", args.status)
    add_if_present(body, "is_doing", args.is_doing)
    add_if_present(body, "name", args.name)
    add_if_present(body, "content", args.content)
    add_if_present(body, "mode", args.mode)
    add_if_present(body, "priority", args.priority)
    add_if_present(body, "parent_task_id", args.parent_task_id)
    add_if_present(body, "plan_id", args.plan_id)
    add_if_present(body, "is_top", args.is_top)
    add_if_present(body, "rating", args.rating)
    add_if_present(body, "review_note", args.review_note)
    add_if_present(body, "planned_start_time", args.planned_start_time)
    add_if_present(body, "planned_end_time", args.planned_end_time)
    add_if_present(body, "remindtime", args.remindtime)
    add_if_present(body, "deadline", args.deadline)
    if not body:
        raise SystemExit("task-update requires fields or --data.")
    call(args, "PUT", "/tasks/%s" % args.task_id, data=body)


def cmd_task_complete(args):
    body = {"status": 2}
    add_if_present(body, "rating", args.rating)
    add_if_present(body, "review_note", args.review_note)
    call(args, "PUT", "/tasks/%s" % args.task_id, data=body)


def cmd_task_doing(args):
    call(args, "PUT", "/tasks/%s" % args.task_id, data={"is_doing": 1})


def cmd_task_stop(args):
    call(args, "PUT", "/tasks/%s" % args.task_id, data={"is_doing": 0})


def cmd_task_reopen(args):
    call(args, "PUT", "/tasks/%s" % args.task_id, data={"status": 1, "is_doing": 0})


def cmd_task_archive(args):
    call(args, "PUT", "/tasks/%s" % args.task_id, data={"status": 3, "is_doing": 0})


def cmd_task_delete(args):
    query = []
    add_if_present_query(query, "type", args.type)
    call(args, "DELETE", "/tasks/%s" % args.task_id, query=query)


def cmd_note_list(args):
    query = []
    for key in ("type", "add_content", "source_type", "source_id", "tag_id", "keyword"):
        add_if_present_query(query, key, getattr(args, key))
    call(args, "GET", "/notes", query=query, renderer=render_note_list)


def cmd_note_create(args):
    body = load_json(args.data) or {}
    add_if_present(body, "name", args.name)
    add_if_present(body, "content", text_value(args.content, args.content_file))
    add_if_present(body, "status", args.status)
    add_if_present(body, "add_image", args.add_image)
    add_if_present(body, "fname", args.fname)
    add_if_present(body, "source_type", args.source_type)
    add_if_present(body, "source_id", args.source_id)
    add_if_present(body, "tags", args.tags)
    if not body.get("name") and not body.get("content"):
        raise SystemExit("note create requires --content/--name, or --data with content/name.")
    if body.get("status") is None:
        body["status"] = 1
    call(args, "POST", "/notes", data=body)


def cmd_note_update(args):
    body = load_json(args.data) or {}
    add_if_present(body, "name", args.name)
    add_if_present(body, "content", text_value(args.content, args.content_file))
    add_if_present(body, "status", args.status)
    add_if_present(body, "tags", args.tags)
    if body.get("status") is None or ("name" not in body and "content" not in body):
        raise SystemExit("note update requires --status and --content/--name, or equivalent --data.")
    call(args, "PUT", "/notes/%s" % args.note_id, data=body)


def cmd_note_delete(args):
    call(args, "DELETE", "/notes/%s" % args.note_id)


def cmd_note_show(args):
    call(args, "GET", "/notes/%s" % args.note_id, renderer=render_record)


def cmd_note_like(args):
    call(args, "POST", "/notes/%s/like" % args.note_id)


def cmd_article_list(args):
    query = []
    for key in (
        "status",
        "page_count",
        "category_id",
        "feed_id",
        "keyword",
        "time_range",
        "start_date",
        "end_date",
        "read_duration",
        "mode",
        "view_mode",
        "primary_category",
        "min_quality_score",
    ):
        add_if_present_query(query, key, getattr(args, key))
    call(args, "GET", "/articles", query=query, renderer=render_article_list)


def cmd_article_feed_list(args):
    query = ["feed_id=%s" % args.feed_id]
    for key in (
        "page_count",
        "keyword",
        "time_range",
        "start_date",
        "end_date",
        "read_duration",
        "mode",
        "view_mode",
        "primary_category",
        "min_quality_score",
    ):
        add_if_present_query(query, key, getattr(args, key))
    call(args, "GET", "/articles/list", query=query, renderer=render_article_list)


def cmd_article_nav(args):
    call(args, "GET", "/articles/navinfo", query=["status=%s" % args.status])


def cmd_article_counts(args):
    call(args, "GET", "/articles/navcountinfo", query=["status=%s" % args.status])


def cmd_article_status(args):
    call(args, "POST", "/articles/status/%s" % args.article_sub_id, data={"status": args.status})


def cmd_articles_status(args):
    body = {"status": args.status}
    add_if_present(body, "ids", args.ids)
    add_if_present(body, "feed_id", args.feed_id)
    if not args.ids and not args.feed_id:
        raise SystemExit("articles-status requires --ids or --feed-id.")
    call(args, "POST", "/articles/allstatus", data=body)


def cmd_article_mark(args):
    call(args, "POST", "/articles/mark", data={"article_id": args.article_id, "content": args.content})


def cmd_article_show(args):
    call(args, "GET", "/articles/%s" % args.article_id)


def cmd_article_reader(args):
    query = []
    add_if_present_query(query, "article_sub_id", args.article_sub_id)
    call(args, "GET", "/articles/%s/reader-view" % args.article_id, query=query)


def cmd_article_ai_render(args):
    body = {"template_style": args.template_style}
    add_if_present(body, "article_sub_id", args.article_sub_id)
    if args.force:
        body["force"] = 1
    call(args, "POST", "/articles/%s/ai-render/generate" % args.article_id, data=body)


def cmd_article_ai_show(args):
    query = []
    add_if_present_query(query, "article_sub_id", args.article_sub_id)
    call(args, "GET", "/articles/%s/ai-render" % args.article_id, query=query)


def cmd_article_delete(args):
    call(args, "DELETE", "/articles/%s" % args.article_sub_id)


def cmd_feed_list(args):
    call(args, "GET", "/feeds")


def cmd_feed_refresh(args):
    call(args, "POST", "/feeds/%s/refresh" % args.feed_sub_id)


def cmd_feed_quickstore(args):
    body = load_json(args.data) or {}
    add_if_present(body, "url", args.url)
    add_if_present(body, "category_id", args.category_id)
    if not body:
        raise SystemExit("feed-quickstore requires --url or --data.")
    call(args, "POST", "/feeds/quickstore", data=body)


def cmd_focus_status(args):
    call(args, "GET", "/focuss/status")


def cmd_focus_start(args):
    call(args, "POST", "/focuss/start")


def cmd_focus_complete(args):
    call(args, "POST", "/focuss/%s" % args.focus_id, data={"name": args.name})


def cmd_plan_list(args):
    query = []
    add_if_present_query(query, "status", args.status)
    call(args, "GET", "/plans", query=query)


def cmd_plan_create(args):
    call(args, "POST", "/plans", data={"name": args.name})


def cmd_plan_update(args):
    call(args, "PUT", "/plans/%s" % args.plan_id, data={"name": args.name})


def cmd_plan_finish(args):
    call(args, "DELETE", "/plans/%s" % args.plan_id, query=["type=finish"])


def cmd_daily_summary_create(args):
    body = {
        "summary_date": args.summary_date,
        "work_content": args.work_content or "",
        "life_content": args.life_content or "",
    }
    call(args, "POST", "/daily-summaries", data=body)


def cmd_daily_summary_date(args):
    call(args, "GET", "/daily-summaries/by-date", query=["summary_date=%s" % args.summary_date])


# ---------------------------------------------------------------- digest
def cmd_digest_profile_get(args):
    call(args, "GET", "/digest/profile")


def cmd_digest_profile_save(args):
    body = load_json(args.data) or {}
    for key in (
        "topics", "include_keywords", "exclude_keywords", "preferred_categories",
        "time_window_days", "frequency", "max_articles", "output_style",
    ):
        add_if_present(body, key, getattr(args, key.replace("-", "_"), None))
    if args.enabled is not None:
        body["enabled"] = args.enabled
    if not body:
        raise SystemExit("digest save-profile requires --data or at least one option.")
    call(args, "POST", "/digest/profile", data=body)


def cmd_digest_pages(args):
    query = []
    add_if_present_query(query, "page_count", args.page_count)
    call(args, "GET", "/digest/pages", query=query)


def cmd_digest_show_page(args):
    call(args, "GET", "/digest/pages/%s" % args.page_id)


def cmd_digest_generate(args):
    call(args, "POST", "/digest/pages/generate", data={})


# ---------------------------------------------------------------- study
def cmd_study_overview(args):
    query = []
    add_if_present_query(query, "date", args.date)
    call(args, "GET", "/study/overview", query=query)


def cmd_study_checkins(args):
    query = []
    for key in ("date_from", "date_to", "page", "page_size"):
        add_if_present_query(query, key, getattr(args, key))
    call(args, "GET", "/study/checkins", query=query)


def cmd_study_plans(args):
    call(args, "GET", "/study/plans")


def cmd_study_plan(args):
    call(args, "GET", "/study/plans/%s" % args.plan_id)


def cmd_study_checkin(args):
    body = {}
    add_if_present(body, "date", args.date)
    add_if_present(body, "content", args.content)
    if not body.get("content"):
        raise SystemExit("study checkin requires --content.")
    call(args, "POST", "/study/tasks/%s/checkin" % args.task_id, data=body)


def cmd_study_generate(args):
    body = {}
    add_if_present(body, "date_from", args.date_from)
    add_if_present(body, "date_to", args.date_to)
    call(args, "POST", "/study/generate", data=body)


def cmd_study_plan_generate(args):
    body = {}
    add_if_present(body, "date_from", args.date_from)
    add_if_present(body, "date_to", args.date_to)
    call(args, "POST", "/study/plans/%s/generate" % args.plan_id, data=body)


# ---------------------------------------------------------------- note record (multipart)
def api_multipart_request(args, path, fields, file_field, file_value, file_name, content_type="audio/mpeg"):
    from uuid import uuid4
    boundary = "----montagegtd" + uuid4().hex
    parts = []
    for key, value in (fields or {}).items():
        parts.append(
            '--%s\r\nContent-Disposition: form-data; name="%s"\r\n\r\n%s\r\n' % (boundary, key, value)
        )
    file_bytes = Path(file_value).read_bytes()
    parts.append(
        '--%s\r\nContent-Disposition: form-data; name="%s"; filename="%s"\r\n'
        'Content-Type: %s\r\n\r\n' % (boundary, file_field, file_name, content_type)
    )
    body = ("".join(parts)).encode("utf-8") + file_bytes + ("\r\n--%s--\r\n" % boundary).encode("utf-8")

    base_url = getattr(args, "base_url", None) or DEFAULT_BASE_URL
    token = getattr(args, "token", None)
    headers = {
        "Accept": "application/json",
        "User-Agent": "montagegtd-cli/1.0",
        "Content-Type": "multipart/form-data; boundary=%s" % boundary,
    }
    if token:
        headers["Authorization"] = "Bearer " + token
    request = urllib.request.Request(
        build_url(base_url, path, []),
        data=body,
        headers=headers,
        method="POST",
    )
    try:
        with urllib.request.urlopen(request, timeout=getattr(args, "timeout", 30)) as response:
            raw = response.read().decode("utf-8")
            return response.status, parse_json(raw), raw
    except urllib.error.HTTPError as exc:
        raw = exc.read().decode("utf-8", errors="replace")
        return exc.code, parse_json(raw), raw
    except urllib.error.URLError as exc:
        raise SystemExit("Request failed: %s" % exc)


def cmd_note_record(args):
    mp3 = Path(args.mp3)
    if not mp3.exists():
        raise SystemExit("Note record requires an existing .mp3 file: %s" % args.mp3)
    fname = args.fname or ("%s" % mp3.stem)
    _, upload_parsed, raw = api_multipart_request(
        args, "/notes/upload", {"fname": fname}, "file", str(mp3), mp3.name
    )
    record_name = nested_value(upload_parsed, ("result", "record_name")) if isinstance(upload_parsed, dict) else None

    body = load_json(args.data) or {}
    add_if_present(body, "name", args.title)
    add_if_present(body, "content", args.content)
    add_if_present(body, "fname", args.fname)
    if record_name:
        body["fname"] = record_name
    if args.tag:
        body["tags"] = args.tag
    if body.get("status") is None:
        body["status"] = 1
    if not body.get("content") and not record_name:
        raise SystemExit("note record needs image/content or a successful upload; upload failed: %s" % raw)
    call(args, "POST", "/notes", data=body)


# ---------------------------------------------------------------- article clip
def cmd_article_clip(args):
    # 入参是 article_sub_id，先解析出 article_id
    if not args.article_id:
        status, parsed, raw = call(args, "GET", "/articles/%s" % args.article_sub_id, check_code=True)
        article_id = nested_value(parsed, ("result", "article_id"))
    else:
        article_id = args.article_id

    if args.note:
        # 存为来源关联笔记（source_type=2 文章）
        body = {"name": args.title, "content": args.content, "status": 1,
                "source_type": 2, "source_id": article_id}
        if args.tag:
            body["tags"] = args.tag
        call(args, "POST", "/notes", data=body)
    else:
        # 退化为文章划线摘录
        if not args.content:
            raise SystemExit("article clip without --note requires --content.")
        call(args, "POST", "/articles/mark", data={"article_id": article_id, "content": args.content})


# ---------------------------------------------------------------- feeds
def cmd_feed_explore(args):
    call(args, "GET", "/feeds/explorer")


def cmd_feed_search(args):
    query = []
    add_if_present_query(query, "name", args.name)
    add_if_present_query(query, "recommend_category_id", args.recommend_category_id)
    if not args.name and not args.recommend_category_id:
        raise SystemExit("feed search requires --name or --recommend-category-id.")
    call(args, "GET", "/feeds/search", query=query)


def cmd_feed_check_url(args):
    call(args, "GET", "/feeds/check-feed-url", query=["url=%s" % args.url])


def cmd_feed_quickstore(args):
    # 后端契约要求 feed_id；修复旧命令只发 url 的 422
    if args.feed_id:
        call(args, "POST", "/feeds/quickstore", data={"feed_id": args.feed_id})
    else:
        call(args, "POST", "/feeds/quickstore", data=load_json(args.data) or {})


def cmd_feed_show(args):
    call(args, "GET", "/feeds/%s" % args.feed_sub_id)


def cmd_feed_refresh_all(args):
    call(args, "POST", "/feeds/refresh", data={})


def cmd_feed_update(args):
    body = load_json(args.data) or {}
    add_if_present(body, "feed_name", args.name)
    add_if_present(body, "category_id", args.category_id)
    add_if_present(body, "feed_order", args.feed_order)
    if not body:
        raise SystemExit("feed update requires --name/--category-id or --data.")
    call(args, "PUT", "/feeds/%s" % args.feed_sub_id, data=body)


def cmd_feed_toggle_status(args):
    body = {}
    if args.enable is not None:
        body["enable"] = args.enable
    call(args, "POST", "/feeds/%s/toggle-status" % args.feed_sub_id, data=body)


def cmd_feed_clear_articles(args):
    call(args, "POST", "/feeds/%s/clear-articles" % args.feed_sub_id, data={})


def cmd_feed_sort(args):
    body = {"feed_sub_ids": args.feed_sub_ids}
    add_if_present(body, "change_feed_sub_id", args.change_feed_sub_id)
    add_if_present(body, "change_feed_sub_category", args.change_feed_sub_category)
    if args.category_feed_sub_ids:
        body["category_feed_sub_ids"] = [s.strip() for s in args.category_feed_sub_ids.split(",") if s.strip()]
    call(args, "POST", "/feeds/sort", data=body)


def cmd_feed_import_opml(args):
    path = Path(args.opml)
    if not path.exists():
        raise SystemExit("feed import-opml requires an existing .opml/.xml file: %s" % args.opml)
    api_multipart_request(
        args, "/feeds/import-opml", {}, "opml_file", str(path), path.name, content_type="text/xml"
    )
    print(json.dumps({"code": 9999, "msg": "ok", "result": {}}, ensure_ascii=False))


# ---------------------------------------------------------------- journal
def cmd_journal_list(args):
    query = []
    for key in ("date", "status"):
        add_if_present_query(query, key, getattr(args, key))
    call(args, "GET", "/journals", query=query)


def cmd_journal_show(args):
    call(args, "GET", "/journals/%s" % args.journal_id)


def cmd_journal_create(args):
    body = load_json(args.data) or {}
    add_if_present(body, "name", args.name)
    add_if_present(body, "content", args.content)
    if not body:
        raise SystemExit("journal create requires --name/--content or --data.")
    call(args, "POST", "/journals", data=body)


def cmd_journal_update(args):
    body = load_json(args.data) or {}
    add_if_present(body, "name", args.name)
    add_if_present(body, "content", args.content)
    if not body:
        raise SystemExit("journal update requires fields or --data.")
    call(args, "PUT", "/journals/%s" % args.journal_id, data=body)


def cmd_journal_delete(args):
    call(args, "DELETE", "/journals/%s" % args.journal_id)


# ---------------------------------------------------------------- mind
def cmd_mind_list(args):
    call(args, "GET", "/minds")


def cmd_mind_show(args):
    call(args, "GET", "/minds/%s" % args.mind_id)


def cmd_mind_outline(args):
    call(args, "GET", "/minds/%s/outline" % args.mind_id)


def cmd_mind_jsmind(args):
    call(args, "GET", "/minds/%s/jsmind" % args.mind_id)


def cmd_mind_create(args):
    body = load_json(args.data) or {}
    add_if_present(body, "name", args.name)
    if not body.get("name"):
        raise SystemExit("mind create requires --name or --data.")
    call(args, "POST", "/minds", data=body)


# ---------------------------------------------------------------- achievement / points
def cmd_achievement_list(args):
    call(args, "GET", "/achievements")


def cmd_achievement_claim(args):
    call(args, "POST", "/achievements/claim", data={"achievement_code": args.achievement_code})


def cmd_points(args):
    call(args, "GET", "/points")


def cmd_points_mall_goods(args):
    call(args, "GET", "/point-mall/goods")


def cmd_points_mall_orders(args):
    call(args, "GET", "/point-mall/orders")


def cmd_points_lottery_draw(args):
    body = {"pool_id": args.pool_id}
    add_if_present(body, "times", args.times)
    call(args, "POST", "/point-mall/lottery/draw", data=body)


# ---------------------------------------------------------------- course / quiz
def cmd_course_list(args):
    query = []
    add_if_present_query(query, "status", args.status)
    call(args, "GET", "/courses", query=query)


def cmd_course_management(args):
    call(args, "GET", "/courses/management")


def cmd_course_show(args):
    call(args, "GET", "/courses/%s" % args.course_id)


def cmd_course_enrollments(args):
    query = []
    add_if_present_query(query, "status", args.status)
    call(args, "GET", "/course-enrollments", query=query)


def cmd_course_items(args):
    call(args, "GET", "/courses/%s/items" % args.course_id)


def cmd_course_structure(args):
    call(args, "GET", "/course-items/structure/%s" % args.course_id)


def cmd_course_item_show(args):
    call(args, "GET", "/course-items/%s" % args.item_id)


def cmd_course_item_complete(args):
    call(args, "POST", "/course-items/%s/complete" % args.item_id, data={})


def cmd_quiz_show(args):
    call(args, "GET", "/course-items/%s/quiz" % args.item_id)


def cmd_quiz_submit(args):
    body = load_json(args.data) or {}
    if not body.get("answers"):
        raise SystemExit("quiz submit requires --data with answers array.")
    call(args, "POST", "/course-items/%s/quiz/attempts" % args.item_id, data=body)


def cmd_quiz_attempts(args):
    call(args, "GET", "/course-items/%s/quiz/attempts" % args.item_id)


# ---------------------------------------------------------------- wechat
def cmd_wechat_explorer(args):
    call(args, "GET", "/wechat/explorer")


def cmd_wechat_articles(args):
    query = []
    for key in ("page", "status", "page_date", "feed_id"):
        add_if_present_query(query, key, getattr(args, key))
    call(args, "GET", "/wechat/articles", query=query)


def cmd_wechat_articleview(args):
    call(args, "GET", "/wechat/articleview", query=["article_id=%s" % args.article_id])


def cmd_wechat_notes(args):
    call(args, "GET", "/wechat/notes")


def cmd_wechat_add_note(args):
    body = {}
    add_if_present(body, "content", args.content)
    add_if_present(body, "name", args.title)
    add_if_present(body, "status", args.status)
    if args.tag:
        body["tags"] = args.tag
    if not body.get("content") and not body.get("name"):
        raise SystemExit("wechat add-note requires --content or --title.")
    call(args, "POST", "/wechat/addNote", data=body)


def add_if_present_query(query, key, value):
    if value is not None:
        query.append("%s=%s" % (key, value))


def common(parser, token=True):
    parser.add_argument("--base-url", default=env_value("MONTAGE_GTD_BASE_URL", "TASK_GITEE_BASE_URL", DEFAULT_BASE_URL))
    parser.add_argument("--timeout", type=int, default=30)
    parser.add_argument("--raw", action="store_true")
    parser.add_argument("--output", choices=("json", "table", "raw"), default="json")
    if token:
        parser.add_argument("--token", default=env_value("MONTAGE_GTD_TOKEN", "TASK_GITEE_TOKEN"))


def add_task_create_arguments(parser):
    parser.add_argument("--name")
    parser.add_argument("--mode", type=int, choices=(1, 2, 3))
    parser.add_argument("--priority", type=int, choices=(1, 2, 3, 4))
    parser.add_argument("--remindtime")
    parser.add_argument("--deadline")
    parser.add_argument("--parent-task-id", type=int)
    parser.add_argument("--plan-id", type=int)
    parser.add_argument("--data")


def add_task_update_arguments(parser):
    parser.add_argument("task_id", type=int)
    parser.add_argument("--data")
    parser.add_argument("--name")
    parser.add_argument("--content")
    parser.add_argument("--mode", type=int, choices=(1, 2, 3))
    parser.add_argument("--priority", type=int, choices=(1, 2, 3, 4))
    parser.add_argument("--status", type=int, choices=(1, 2, 3))
    parser.add_argument("--is-doing", type=int, choices=(0, 1))
    parser.add_argument("--is-top", type=int, choices=(0, 1))
    parser.add_argument("--parent-task-id", type=int)
    parser.add_argument("--plan-id", type=int)
    parser.add_argument("--rating", type=int, choices=(1, 2, 3, 4, 5))
    parser.add_argument("--review-note")
    parser.add_argument("--planned-start-time")
    parser.add_argument("--planned-end-time")
    parser.add_argument("--remindtime")
    parser.add_argument("--deadline")


def add_note_list_arguments(parser):
    parser.add_argument("--type")
    parser.add_argument("--add-content")
    parser.add_argument("--source-type", type=int, choices=(1, 2, 3, 4))
    parser.add_argument("--source-id", type=int)
    parser.add_argument("--tag-id", type=int)
    parser.add_argument("--keyword")


def add_note_write_arguments(parser, update=False):
    if update:
        parser.add_argument("note_id", type=int)
    parser.add_argument("--name", "--title", dest="name")
    parser.add_argument("--content")
    parser.add_argument("--content-file")
    parser.add_argument("--status", type=int, choices=(1, 2))
    parser.add_argument("--tag", dest="tags", action="append")
    if not update:
        parser.add_argument("--add-image")
        parser.add_argument("--fname")
        parser.add_argument("--source-type", type=int, choices=(1, 2, 3, 4))
        parser.add_argument("--source-id", type=int)
    parser.add_argument("--data")


def add_article_list_arguments(parser):
    parser.add_argument("--status", default="unread", choices=("all", "unread", "read", "read_later", "star"))
    parser.add_argument("--page-count", type=int)
    parser.add_argument("--category-id", type=int)
    parser.add_argument("--feed-id", type=int)
    parser.add_argument("--keyword")
    parser.add_argument("--time-range", choices=("all", "3h", "6h", "1d", "3d", "7d", "custom"))
    parser.add_argument("--start-date")
    parser.add_argument("--end-date")
    parser.add_argument("--read-duration", choices=("all", "short", "medium", "long"))
    parser.add_argument("--mode", choices=("simple", "full"), default="simple")
    parser.add_argument(
        "--view-mode",
        choices=("all", "personalized", "tech", "product", "read_later_suggest", "low_priority"),
    )
    parser.add_argument("--primary-category")
    parser.add_argument("--min-quality-score", type=int)


def add_article_filter_arguments(parser):
    parser.add_argument("--page-count", type=int)
    parser.add_argument("--keyword")
    parser.add_argument("--time-range", choices=("all", "3h", "6h", "1d", "3d", "7d", "custom"))
    parser.add_argument("--start-date")
    parser.add_argument("--end-date")
    parser.add_argument("--read-duration", choices=("all", "short", "medium", "long"))
    parser.add_argument("--mode", choices=("simple", "full"), default="simple")
    parser.add_argument(
        "--view-mode",
        choices=("all", "personalized", "tech", "product", "read_later_suggest", "low_priority"),
    )
    parser.add_argument("--primary-category")
    parser.add_argument("--min-quality-score", type=int)


def add_grouped_task_parser(sub):
    task = sub.add_parser("task", help="Manage tasks.")
    commands = task.add_subparsers(dest="task_command", required=True)

    p = commands.add_parser("list", aliases=["ls"]); common(p)
    p.add_argument("--status", type=int, choices=(1, 2, 3)); p.add_argument("--page-count", type=int)
    p.set_defaults(func=cmd_task_list)
    p = commands.add_parser("show"); common(p); p.add_argument("task_id", type=int); p.set_defaults(func=cmd_task_show)
    p = commands.add_parser("counts"); common(p); p.set_defaults(func=cmd_task_counts)
    p = commands.add_parser("all"); common(p)
    p.add_argument("--status", type=int, default=1, choices=(1, 2, 3))
    p.add_argument("--mode", type=int, default=1, choices=(1, 2, 3)); p.set_defaults(func=cmd_task_all)
    p = commands.add_parser("priority"); common(p)
    p.add_argument("--status", type=int, default=1, choices=(1, 2, 3))
    p.add_argument("--mode", type=int, default=1, choices=(1, 2, 3)); p.set_defaults(func=cmd_task_priority)
    p = commands.add_parser("parents"); common(p)
    p.add_argument("--exclude-task-id", type=int); p.set_defaults(func=cmd_task_parents)
    p = commands.add_parser("create", aliases=["add"]); common(p); add_task_create_arguments(p); p.set_defaults(func=cmd_task_create)
    p = commands.add_parser("update", aliases=["edit"]); common(p); add_task_update_arguments(p); p.set_defaults(func=cmd_task_update)
    p = commands.add_parser("doing", aliases=["start"]); common(p); p.add_argument("task_id", type=int); p.set_defaults(func=cmd_task_doing)
    p = commands.add_parser("stop"); common(p); p.add_argument("task_id", type=int); p.set_defaults(func=cmd_task_stop)
    p = commands.add_parser("complete", aliases=["done"]); common(p)
    p.add_argument("task_id", type=int); p.add_argument("--rating", type=int, choices=(1, 2, 3, 4, 5))
    p.add_argument("--review-note"); p.set_defaults(func=cmd_task_complete)
    p = commands.add_parser("reopen"); common(p); p.add_argument("task_id", type=int); p.set_defaults(func=cmd_task_reopen)
    p = commands.add_parser("archive"); common(p); p.add_argument("task_id", type=int); p.set_defaults(func=cmd_task_archive)
    p = commands.add_parser("delete", aliases=["rm"]); common(p)
    p.add_argument("task_id", type=int); p.add_argument("--type"); p.set_defaults(func=cmd_task_delete)


def add_grouped_note_parser(sub):
    note = sub.add_parser("note", help="Manage notes.")
    commands = note.add_subparsers(dest="note_command", required=True)

    p = commands.add_parser("list", aliases=["ls", "search"]); common(p); add_note_list_arguments(p); p.set_defaults(func=cmd_note_list)
    p = commands.add_parser("show"); common(p); p.add_argument("note_id", type=int); p.set_defaults(func=cmd_note_show)
    p = commands.add_parser("create", aliases=["add"]); common(p); add_note_write_arguments(p); p.set_defaults(func=cmd_note_create)
    p = commands.add_parser("update", aliases=["edit"]); common(p); add_note_write_arguments(p, update=True); p.set_defaults(func=cmd_note_update)
    p = commands.add_parser("delete", aliases=["rm"]); common(p); p.add_argument("note_id", type=int); p.set_defaults(func=cmd_note_delete)
    p = commands.add_parser("like"); common(p); p.add_argument("note_id", type=int); p.set_defaults(func=cmd_note_like)


def add_grouped_article_parser(sub):
    article = sub.add_parser("article", help="Manage reading queue articles.")
    commands = article.add_subparsers(dest="article_command", required=True)

    p = commands.add_parser("list", aliases=["ls"]); common(p); add_article_list_arguments(p); p.set_defaults(func=cmd_article_list)
    p = commands.add_parser("by-feed"); common(p)
    p.add_argument("feed_id", type=int); add_article_filter_arguments(p); p.set_defaults(func=cmd_article_feed_list)
    p = commands.add_parser("show"); common(p); p.add_argument("article_id", type=int); p.set_defaults(func=cmd_article_show)
    p = commands.add_parser("reader", aliases=["read"]); common(p)
    p.add_argument("article_id", type=int); p.add_argument("--article-sub-id", type=int); p.set_defaults(func=cmd_article_reader)
    p = commands.add_parser("status"); common(p)
    p.add_argument("article_sub_id", type=int)
    p.add_argument("status", choices=("read", "unread", "read_later", "star")); p.set_defaults(func=cmd_article_status)
    p = commands.add_parser("batch-status"); common(p)
    p.add_argument("status", choices=("read", "unread", "read_later", "star"))
    p.add_argument("--ids"); p.add_argument("--feed-id", type=int); p.set_defaults(func=cmd_articles_status)
    p = commands.add_parser("mark"); common(p)
    p.add_argument("article_id", type=int); p.add_argument("--content", required=True); p.set_defaults(func=cmd_article_mark)
    p = commands.add_parser("ai-show"); common(p)
    p.add_argument("article_id", type=int); p.add_argument("--article-sub-id", type=int); p.set_defaults(func=cmd_article_ai_show)
    p = commands.add_parser("ai-render", aliases=["ai-generate"]); common(p)
    p.add_argument("article_id", type=int); p.add_argument("--article-sub-id", type=int)
    p.add_argument("--template-style", default="magazine"); p.add_argument("--force", action="store_true")
    p.set_defaults(func=cmd_article_ai_render)
    p = commands.add_parser("nav"); common(p)
    p.add_argument("--status", default="unread", choices=("unread", "read", "read_later", "star")); p.set_defaults(func=cmd_article_nav)
    p = commands.add_parser("counts"); common(p)
    p.add_argument("--status", default="unread", choices=("unread", "read", "read_later", "star")); p.set_defaults(func=cmd_article_counts)
    p = commands.add_parser("delete", aliases=["rm"]); common(p)
    p.add_argument("article_sub_id", type=int); p.set_defaults(func=cmd_article_delete)


def build_parser():
    parser = argparse.ArgumentParser(description="Montage GTD 领域能力 CLI。")
    sub = parser.add_subparsers(dest="command", required=True)

    add_grouped_task_parser(sub)
    add_grouped_note_parser(sub)
    add_grouped_article_parser(sub)

    p = sub.add_parser("health"); common(p, token=False); p.set_defaults(func=cmd_health)
    p = sub.add_parser("me"); common(p); p.set_defaults(func=cmd_me)

    p = sub.add_parser("request"); common(p)
    p.add_argument("method"); p.add_argument("path")
    p.add_argument("--data"); p.add_argument("--query", action="append", default=[])
    p.set_defaults(func=cmd_request)

    p = sub.add_parser("endpoints")
    p.add_argument("--openapi"); p.add_argument("--capability")
    p.add_argument("--tag"); p.set_defaults(func=cmd_endpoints)

    p = sub.add_parser("pat-create"); common(p)
    p.add_argument("--name", required=True); p.add_argument("--scopes", default="read")
    p.add_argument("--expires-at"); p.set_defaults(func=cmd_pat_create)

    p = sub.add_parser("pat-revoke"); common(p)
    p.add_argument("token_id"); p.add_argument("--force-delete", action="store_true")
    p.set_defaults(func=cmd_pat_revoke)

    p = sub.add_parser("task-list"); common(p)
    p.add_argument("--status", type=int, choices=(1, 2, 3)); p.add_argument("--page-count", type=int)
    p.set_defaults(func=cmd_task_list)

    p = sub.add_parser("task-create"); common(p)
    add_task_create_arguments(p)
    p.set_defaults(func=cmd_task_create)

    p = sub.add_parser("task-update"); common(p)
    add_task_update_arguments(p)
    p.set_defaults(func=cmd_task_update)

    p = sub.add_parser("task-complete"); common(p)
    p.add_argument("task_id"); p.add_argument("--rating"); p.add_argument("--review-note")
    p.set_defaults(func=cmd_task_complete)

    p = sub.add_parser("task-doing"); common(p)
    p.add_argument("task_id"); p.set_defaults(func=cmd_task_doing)

    p = sub.add_parser("task-delete"); common(p)
    p.add_argument("task_id"); p.add_argument("--type")
    p.set_defaults(func=cmd_task_delete)

    p = sub.add_parser("note-list"); common(p)
    add_note_list_arguments(p)
    p.set_defaults(func=cmd_note_list)

    p = sub.add_parser("note-create"); common(p)
    add_note_write_arguments(p); p.set_defaults(func=cmd_note_create)

    p = sub.add_parser("note-update"); common(p)
    add_note_write_arguments(p, update=True)
    p.set_defaults(func=cmd_note_update)

    p = sub.add_parser("note-delete"); common(p)
    p.add_argument("note_id"); p.set_defaults(func=cmd_note_delete)

    p = sub.add_parser("note-show"); common(p)
    p.add_argument("note_id"); p.set_defaults(func=cmd_note_show)

    p = sub.add_parser("article-list"); common(p)
    add_article_list_arguments(p)
    p.set_defaults(func=cmd_article_list)

    p = sub.add_parser("article-status"); common(p)
    p.add_argument("article_sub_id"); p.add_argument("--status", required=True, choices=["read", "unread", "read_later", "star"])
    p.set_defaults(func=cmd_article_status)

    p = sub.add_parser("articles-status"); common(p)
    p.add_argument("--status", required=True, choices=["read", "unread", "read_later", "star"])
    p.add_argument("--ids"); p.add_argument("--feed-id")
    p.set_defaults(func=cmd_articles_status)

    p = sub.add_parser("article-mark"); common(p)
    p.add_argument("--article-id", required=True); p.add_argument("--content", required=True)
    p.set_defaults(func=cmd_article_mark)

    p = sub.add_parser("article-show"); common(p)
    p.add_argument("article_id"); p.set_defaults(func=cmd_article_show)

    p = sub.add_parser("article-reader"); common(p)
    p.add_argument("article_id"); p.add_argument("--article-sub-id")
    p.set_defaults(func=cmd_article_reader)

    p = sub.add_parser("article-ai-render"); common(p)
    p.add_argument("article_id"); p.add_argument("--article-sub-id")
    p.add_argument("--template-style", default="magazine"); p.add_argument("--force", action="store_true")
    p.set_defaults(func=cmd_article_ai_render)

    p = sub.add_parser("feed-list"); common(p); p.set_defaults(func=cmd_feed_list)
    p = sub.add_parser("feed-refresh"); common(p); p.add_argument("feed_sub_id"); p.set_defaults(func=cmd_feed_refresh)
    p = sub.add_parser("feed-quickstore"); common(p)
    p.add_argument("--feed-id"); p.add_argument("--data")
    p.set_defaults(func=cmd_feed_quickstore)

    p = sub.add_parser("feed-explore"); common(p); p.set_defaults(func=cmd_feed_explore)
    p = sub.add_parser("feed-search"); common(p)
    p.add_argument("--name"); p.add_argument("--recommend-category-id")
    p.set_defaults(func=cmd_feed_search)
    p = sub.add_parser("feed-check-url"); common(p)
    p.add_argument("url"); p.set_defaults(func=cmd_feed_check_url)

    p = sub.add_parser("feed-show"); common(p)
    p.add_argument("feed_sub_id"); p.set_defaults(func=cmd_feed_show)
    p = sub.add_parser("feed-refresh-all"); common(p); p.set_defaults(func=cmd_feed_refresh_all)
    p = sub.add_parser("feed-update"); common(p)
    p.add_argument("feed_sub_id")
    p.add_argument("--name"); p.add_argument("--category-id", type=int)
    p.add_argument("--feed-order", type=int); p.add_argument("--data")
    p.set_defaults(func=cmd_feed_update)
    p = sub.add_parser("feed-toggle-status"); common(p)
    p.add_argument("feed_sub_id")
    p.add_argument("--enable", type=int, choices=(0, 1))
    p.set_defaults(func=cmd_feed_toggle_status)
    p = sub.add_parser("feed-clear-articles"); common(p)
    p.add_argument("feed_sub_id"); p.set_defaults(func=cmd_feed_clear_articles)
    p = sub.add_parser("feed-sort"); common(p)
    p.add_argument("--feed-sub-ids", required=True)
    p.add_argument("--change-feed-sub-id")
    p.add_argument("--change-feed-sub-category")
    p.add_argument("--category-feed-sub-ids")
    p.set_defaults(func=cmd_feed_sort)
    p = sub.add_parser("feed-import-opml"); common(p)
    p.add_argument("opml"); p.set_defaults(func=cmd_feed_import_opml)

    p = sub.add_parser("focus-status"); common(p); p.set_defaults(func=cmd_focus_status)
    p = sub.add_parser("focus-start"); common(p); p.set_defaults(func=cmd_focus_start)
    p = sub.add_parser("focus-complete"); common(p)
    p.add_argument("focus_id"); p.add_argument("--name", required=True)
    p.set_defaults(func=cmd_focus_complete)

    p = sub.add_parser("plan-list"); common(p)
    p.add_argument("--status"); p.set_defaults(func=cmd_plan_list)
    p = sub.add_parser("plan-create"); common(p)
    p.add_argument("--name", required=True); p.set_defaults(func=cmd_plan_create)
    p = sub.add_parser("plan-update"); common(p)
    p.add_argument("plan_id"); p.add_argument("--name", required=True)
    p.set_defaults(func=cmd_plan_update)
    p = sub.add_parser("plan-finish"); common(p)
    p.add_argument("plan_id"); p.set_defaults(func=cmd_plan_finish)

    p = sub.add_parser("daily-summary-create"); common(p)
    p.add_argument("--summary-date", required=True)
    p.add_argument("--work-content")
    p.add_argument("--life-content")
    p.set_defaults(func=cmd_daily_summary_create)

    p = sub.add_parser("daily-summary-date"); common(p)
    p.add_argument("--summary-date", required=True)
    p.set_defaults(func=cmd_daily_summary_date)

    # digest 汇合页
    p = sub.add_parser("digest-get-profile"); common(p); p.set_defaults(func=cmd_digest_profile_get)
    p = sub.add_parser("digest-save-profile"); common(p)
    p.add_argument("--data")
    p.add_argument("--topics", action="append")
    p.add_argument("--include-keywords", action="append")
    p.add_argument("--exclude-keywords", action="append")
    p.add_argument("--preferred-categories", action="append")
    p.add_argument("--time-window-days", type=int)
    p.add_argument("--frequency", choices=("daily", "weekly"))
    p.add_argument("--max-articles", type=int)
    p.add_argument("--output-style")
    p.add_argument("--enabled", type=lambda v: str(v).lower() in ("1", "true", "yes"))
    p.set_defaults(func=cmd_digest_profile_save)
    p = sub.add_parser("digest-pages"); common(p)
    p.add_argument("--page-count", type=int); p.set_defaults(func=cmd_digest_pages)
    p = sub.add_parser("digest-show-page"); common(p)
    p.add_argument("page_id"); p.set_defaults(func=cmd_digest_show_page)
    p = sub.add_parser("digest-generate"); common(p); p.set_defaults(func=cmd_digest_generate)

    # study 学习
    p = sub.add_parser("study-overview"); common(p)
    p.add_argument("--date"); p.set_defaults(func=cmd_study_overview)
    p = sub.add_parser("study-checkins"); common(p)
    p.add_argument("--date-from"); p.add_argument("--date-to")
    p.add_argument("--page", type=int); p.add_argument("--page-size", type=int)
    p.set_defaults(func=cmd_study_checkins)
    p = sub.add_parser("study-plans"); common(p); p.set_defaults(func=cmd_study_plans)
    p = sub.add_parser("study-plan"); common(p)
    p.add_argument("plan_id"); p.set_defaults(func=cmd_study_plan)
    p = sub.add_parser("study-checkin"); common(p)
    p.add_argument("task_id"); p.add_argument("--date"); p.add_argument("--content")
    p.set_defaults(func=cmd_study_checkin)
    p = sub.add_parser("study-generate"); common(p)
    p.add_argument("--date-from"); p.add_argument("--date-to")
    p.set_defaults(func=cmd_study_generate)
    p = sub.add_parser("study-plan-generate"); common(p)
    p.add_argument("plan_id"); p.add_argument("--date-from"); p.add_argument("--date-to")
    p.set_defaults(func=cmd_study_plan_generate)

    # note record 语音
    p = sub.add_parser("note-record"); common(p)
    p.add_argument("mp3"); p.add_argument("--title"); p.add_argument("--content")
    p.add_argument("--tag", action="append"); p.add_argument("--fname"); p.add_argument("--data")
    p.set_defaults(func=cmd_note_record)

    # article clip 摘录流
    p = sub.add_parser("article-clip"); common(p)
    p.add_argument("article_sub_id"); p.add_argument("--article-id", type=int)
    p.add_argument("--content"); p.add_argument("--title"); p.add_argument("--tag", action="append")
    p.add_argument("--note", action="store_true")
    p.set_defaults(func=cmd_article_clip)

    # journal 手账
    p = sub.add_parser("journal-list"); common(p)
    p.add_argument("--date"); p.add_argument("--status")
    p.set_defaults(func=cmd_journal_list)
    p = sub.add_parser("journal-show"); common(p)
    p.add_argument("journal_id"); p.set_defaults(func=cmd_journal_show)
    p = sub.add_parser("journal-create"); common(p)
    p.add_argument("--name"); p.add_argument("--content"); p.add_argument("--data")
    p.set_defaults(func=cmd_journal_create)
    p = sub.add_parser("journal-update"); common(p)
    p.add_argument("journal_id"); p.add_argument("--name"); p.add_argument("--content"); p.add_argument("--data")
    p.set_defaults(func=cmd_journal_update)
    p = sub.add_parser("journal-delete"); common(p)
    p.add_argument("journal_id"); p.set_defaults(func=cmd_journal_delete)

    # mind 思维导图
    p = sub.add_parser("mind-list"); common(p); p.set_defaults(func=cmd_mind_list)
    p = sub.add_parser("mind-show"); common(p)
    p.add_argument("mind_id"); p.set_defaults(func=cmd_mind_show)
    p = sub.add_parser("mind-outline"); common(p)
    p.add_argument("mind_id"); p.set_defaults(func=cmd_mind_outline)
    p = sub.add_parser("mind-jsmind"); common(p)
    p.add_argument("mind_id"); p.set_defaults(func=cmd_mind_jsmind)
    p = sub.add_parser("mind-create"); common(p)
    p.add_argument("--name"); p.add_argument("--data")
    p.set_defaults(func=cmd_mind_create)

    # achievement / points
    p = sub.add_parser("achievement-list"); common(p); p.set_defaults(func=cmd_achievement_list)
    p = sub.add_parser("achievement-claim"); common(p)
    p.add_argument("achievement_code"); p.set_defaults(func=cmd_achievement_claim)
    p = sub.add_parser("points"); common(p); p.set_defaults(func=cmd_points)
    p = sub.add_parser("points-mall-goods"); common(p); p.set_defaults(func=cmd_points_mall_goods)
    p = sub.add_parser("points-mall-orders"); common(p); p.set_defaults(func=cmd_points_mall_orders)
    p = sub.add_parser("points-lottery-draw"); common(p)
    p.add_argument("pool_id", type=int); p.add_argument("--times", type=int)
    p.set_defaults(func=cmd_points_lottery_draw)

    # course / quiz
    p = sub.add_parser("course-list"); common(p)
    p.add_argument("--status"); p.set_defaults(func=cmd_course_list)
    p = sub.add_parser("course-management"); common(p); p.set_defaults(func=cmd_course_management)
    p = sub.add_parser("course-show"); common(p)
    p.add_argument("course_id"); p.set_defaults(func=cmd_course_show)
    p = sub.add_parser("course-enrollments"); common(p)
    p.add_argument("--status"); p.set_defaults(func=cmd_course_enrollments)
    p = sub.add_parser("course-items"); common(p)
    p.add_argument("course_id"); p.set_defaults(func=cmd_course_items)
    p = sub.add_parser("course-structure"); common(p)
    p.add_argument("course_id"); p.set_defaults(func=cmd_course_structure)
    p = sub.add_parser("course-item-show"); common(p)
    p.add_argument("item_id"); p.set_defaults(func=cmd_course_item_show)
    p = sub.add_parser("course-item-complete"); common(p)
    p.add_argument("item_id"); p.set_defaults(func=cmd_course_item_complete)
    p = sub.add_parser("quiz-show"); common(p)
    p.add_argument("item_id"); p.set_defaults(func=cmd_quiz_show)
    p = sub.add_parser("quiz-submit"); common(p)
    p.add_argument("item_id"); p.add_argument("--data"); p.set_defaults(func=cmd_quiz_submit)
    p = sub.add_parser("quiz-attempts"); common(p)
    p.add_argument("item_id"); p.set_defaults(func=cmd_quiz_attempts)

    # wechat
    p = sub.add_parser("wechat-explorer"); common(p); p.set_defaults(func=cmd_wechat_explorer)
    p = sub.add_parser("wechat-articles"); common(p)
    p.add_argument("--page", type=int); p.add_argument("--status", default="read_later")
    p.add_argument("--page-date"); p.add_argument("--feed-id", type=int)
    p.set_defaults(func=cmd_wechat_articles)
    p = sub.add_parser("wechat-articleview"); common(p)
    p.add_argument("article_id"); p.set_defaults(func=cmd_wechat_articleview)
    p = sub.add_parser("wechat-notes"); common(p); p.set_defaults(func=cmd_wechat_notes)
    p = sub.add_parser("wechat-add-note"); common(p)
    p.add_argument("--content"); p.add_argument("--title"); p.add_argument("--status", type=int)
    p.add_argument("--tag", action="append")
    p.set_defaults(func=cmd_wechat_add_note)

    return parser


def main():
    args = build_parser().parse_args()
    if hasattr(args, "token") and not args.token and args.command != "endpoints":
        raise SystemExit("Missing token. Set MONTAGE_GTD_TOKEN or pass --token.")
    args.func(args)


if __name__ == "__main__":
    main()
