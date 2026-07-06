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


DEFAULT_BASE_URL = "http://testtask.congcong.us/api/v2"
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
    headers = {"Accept": "application/json", "User-Agent": "montage-gtd-cli/1.0"}
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
        "view_mode",
        "primary_category",
        "min_quality_score",
    ):
        add_if_present_query(query, key, getattr(args, key))
    call(args, "GET", "/articles", query=query, renderer=render_article_list)


def cmd_article_feed_list(args):
    query = ["feed_id=%s" % args.feed_id]
    for key in ("page_count", "view_mode", "primary_category", "min_quality_score"):
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
    parser.add_argument("--status", default="unread", choices=("unread", "read", "read_later", "star"))
    parser.add_argument("--page-count", type=int)
    parser.add_argument("--category-id", type=int)
    parser.add_argument("--feed-id", type=int)
    parser.add_argument(
        "--view-mode",
        choices=("all", "personalized", "tech", "product", "read_later_suggest", "low_priority"),
    )
    parser.add_argument("--primary-category")
    parser.add_argument("--min-quality-score", type=int)


def add_article_filter_arguments(parser):
    parser.add_argument("--page-count", type=int)
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
    p.add_argument("--url"); p.add_argument("--category-id"); p.add_argument("--data")
    p.set_defaults(func=cmd_feed_quickstore)

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

    return parser


def main():
    args = build_parser().parse_args()
    if hasattr(args, "token") and not args.token and args.command != "endpoints":
        raise SystemExit("Missing token. Set MONTAGE_GTD_TOKEN or pass --token.")
    args.func(args)


if __name__ == "__main__":
    main()
