#!/usr/bin/env python3
"""Montage App Workbench CLI for /api/v2/app-manage operations.

管理与发布 Montage 应用工作台 APP（创建应用、管理文件、虚拟数据表与记录）。

认证：与后端一致，走 PAT（Bearer）。除路由要求 read/write 外，
后端还会校验“应用管理者白名单”——只有配置允许的账号才能操作。
"""

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


def text_value(value, file_path=None):
    if file_path:
        return Path(file_path).read_text(encoding="utf-8")
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


def build_ssl_context(args):
    """构造 SSL 上下文。

    默认跳过证书校验（宽松模式，开箱即用，适合个人/内部服务）；
    使用 --verify 时执行严格校验并尝试加载系统 CA 证书。"""
    import ssl

    # 保留兼容：旧版 --insecure 语义为“跳过校验”，默认已宽松，无需额外处理。
    if getattr(args, "verify", False) and not getattr(args, "insecure", False):
        context = ssl.create_default_context()
        if not context.get_ca_certs():
            for path in ("/etc/ssl/cert.pem", "/usr/local/etc/openssl/cert.pem", "/etc/pki/tls/certs/ca-bundle.crt"):
                try:
                    if os.path.exists(path):
                        context.load_verify_locations(cafile=path)
                        break
                except (OSError, ssl.SSLError):
                    continue
        return context

    context = ssl.create_default_context()
    context.check_hostname = False
    context.verify_mode = ssl.CERT_NONE
    return context


def api_request(args, method, path, data=None, query=None, token=None):
    token = token if token is not None else getattr(args, "token", None)
    base_url = getattr(args, "base_url", None) or env_value("MONTAGE_APP_BASE_URL", "TASK_GITEE_BASE_URL") or DEFAULT_BASE_URL
    headers = {"Accept": "application/json", "User-Agent": "montageapp-cli/1.0"}
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
        context = build_ssl_context(args)
        with urllib.request.urlopen(request, timeout=getattr(args, "timeout", 60), context=context) as response:
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


def render_app_list(parsed):
    apps = nested_value(parsed, ("result", "applications"), []) or []
    rows = []
    for app in apps:
        rows.append((
            app.get("id"),
            app.get("slug"),
            app.get("status"),
            app.get("auth_mode") or "",
            app.get("codes_count"),
            app.get("name") or "",
        ))
    render_table(("ID", "SLUG", "STATUS", "AUTH", "FILES", "NAME"), rows)


def render_app_detail(parsed):
    app = nested_value(parsed, ("result", "application"), {}) or {}
    files = nested_value(parsed, ("result", "files"), []) or []
    print("应用: %s (id=%s, slug=%s, status=%s, auth_mode=%s)" % (
        app.get("name"), app.get("id"), app.get("slug"), app.get("status"), app.get("auth_mode") or "public"))
    print("预览: %s" % (nested_value(parsed, ("result", "preview_url"), "") or ""))
    rows = []
    for code in files:
        rows.append((
            code.get("id"),
            code.get("path"),
            code.get("type"),
            code.get("status"),
            code.get("auth_mode") or "-",
            (code.get("content") or "")[:40],
        ))
    render_table(("FILE_ID", "PATH", "TYPE", "STATUS", "AUTH", "CONTENT_PREVIEW"), rows)


def render_code_detail(parsed):
    code = nested_value(parsed, ("result", "file"), {}) or {}
    print(json.dumps(code, ensure_ascii=False, indent=2))


def render_history(parsed):
    history = nested_value(parsed, ("result", "history"), []) or []
    rows = [(item.get("id"), item.get("created_at") or "") for item in history]
    render_table(("HISTORY_ID", "CREATED_AT"), rows)
    current = nested_value(parsed, ("result", "current"), {}) or {}
    print("当前内容长度: %d" % len(current.get("content") or ""))


def render_tables(parsed):
    tables = nested_value(parsed, ("result", "tables"), []) or []
    rows = []
    for table in tables:
        rows.append((
            table.get("id"),
            table.get("slug"),
            table.get("status"),
            "%d" % (len(table.get("fields") or [])),
            table.get("name") or "",
        ))
    render_table(("TABLE_ID", "SLUG", "STATUS", "FIELDS", "NAME"), rows)


def render_records(parsed):
    records = nested_value(parsed, ("result", "records"), []) or []
    if isinstance(records, dict):
        # 服务端返回分页结构 {total, page, per_page, items}
        total = records.get("total")
        if total is not None:
            print("共 %s 条（page=%s）" % (total, records.get("page", 1)))
        records = records.get("items", [])
    if not records:
        print("(无记录)")
        return
    headers = sorted({key for record in records for key in record.keys()})
    rows = [[record.get(key, "") for key in headers] for record in records]
    render_table(headers, rows)


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


def add_common_parser(sub, name, help_text):
    parser = sub.add_parser(name, help=help_text)
    parser.add_argument("--token", default=None, help="PAT token（缺省用 MONTAGE_APP_TOKEN 或 TASK_GITEE_TOKEN）")
    parser.add_argument("--base-url", default=None, help="API base（缺省 https://task.congcong.us/api/v2）")
    parser.add_argument("--timeout", type=int, default=60)
    parser.add_argument("--output", choices=("json", "table", "raw"), default="json")
    parser.add_argument("--raw", action="store_true", help="输出原始 HTTP 响应")
    parser.add_argument("--verify", action="store_true", help="严格校验 HTTPS 证书（默认跳过校验，宽松模式）")
    parser.add_argument("--insecure", action="store_true", help="兼容旧参数；跳过校验（默认行为）")
    return parser


def main():
    parser = argparse.ArgumentParser(description="Montage App Workbench CLI")
    parser.add_argument("--token", default=None, help="PAT token（缺省用 MONTAGE_APP_TOKEN 或 TASK_GITEE_TOKEN）")
    parser.add_argument("--base-url", default=None, help="API base（缺省 https://task.congcong.us/api/v2）")
    parser.add_argument("--output", choices=("json", "table", "raw"), default="json")
    parser.add_argument("--verify", action="store_true", help="严格校验 HTTPS 证书（默认跳过校验，宽松模式）")
    parser.add_argument("--insecure", action="store_true", help="兼容旧参数；跳过校验（默认行为）")
    sub = parser.add_subparsers(dest="command", required=True)

    # 应用
    p = add_common_parser(sub, "app-list", "列出应用")
    p.add_argument("--query", action="append", default=[])
    p.set_defaults(handler=cmd_app_list, renderer=render_app_list)

    p = add_common_parser(sub, "app-show", "查看应用详情（含文件）")
    p.add_argument("id", type=int)
    p.set_defaults(handler=cmd_app_show, renderer=render_app_detail)

    p = add_common_parser(sub, "app-create", "创建应用")
    p.add_argument("--name", required=True)
    p.add_argument("--slug", required=True)
    p.add_argument("--description", default=None)
    p.add_argument("--status", type=int, default=1, choices=(1, 2, 3, 4))
    p.add_argument("--auth-mode", default="public", choices=("public", "login", "whitelist", "pat"))
    p.set_defaults(handler=cmd_app_create)

    p = add_common_parser(sub, "app-update", "更新应用元信息")
    p.add_argument("id", type=int)
    p.add_argument("--name", default=None)
    p.add_argument("--slug", default=None)
    p.add_argument("--description", default=None)
    p.add_argument("--status", type=int, default=None, choices=(1, 2, 3, 4))
    p.add_argument("--auth-mode", default=None, choices=("public", "login", "whitelist", "pat"))
    p.set_defaults(handler=cmd_app_update)

    p = add_common_parser(sub, "app-delete", "删除应用（软删）")
    p.add_argument("id", type=int)
    p.set_defaults(handler=cmd_app_delete)

    # 文件（codes）
    p = add_common_parser(sub, "code-create", "创建文件（type: 2=html 3=js 4=css 5=json）")
    p.add_argument("app_id", type=int)
    p.add_argument("--name", required=True)
    p.add_argument("--path", required=True)
    p.add_argument("--type", type=int, required=True, choices=(2, 3, 4, 5))
    p.add_argument("--status", type=int, default=1, choices=(1, 2))
    p.add_argument("--auth-mode", default=None, choices=("public", "login", "whitelist", "pat"))
    p.add_argument("--content", default=None)
    p.add_argument("--content-file", default=None, help="从本地文件读取内容")
    p.set_defaults(handler=cmd_code_create, renderer=render_code_detail)

    p = add_common_parser(sub, "code-update", "更新文件内容或元信息")
    p.add_argument("app_id", type=int)
    p.add_argument("code_id", type=int)
    p.add_argument("--name", default=None)
    p.add_argument("--path", default=None)
    p.add_argument("--type", type=int, default=None, choices=(2, 3, 4, 5))
    p.add_argument("--status", type=int, default=None, choices=(1, 2))
    p.add_argument("--auth-mode", default=None, choices=("public", "login", "whitelist", "pat"))
    p.add_argument("--content", default=None)
    p.add_argument("--content-file", default=None, help="从本地文件读取内容")
    p.set_defaults(handler=cmd_code_update, renderer=render_code_detail)

    p = add_common_parser(sub, "code-delete", "删除文件")
    p.add_argument("app_id", type=int)
    p.add_argument("code_id", type=int)
    p.set_defaults(handler=cmd_code_delete)

    p = add_common_parser(sub, "code-history", "查看文件历史版本")
    p.add_argument("app_id", type=int)
    p.add_argument("code_id", type=int)
    p.set_defaults(handler=cmd_code_history, renderer=render_history)

    p = add_common_parser(sub, "code-rollback", "回滚到指定历史版本")
    p.add_argument("app_id", type=int)
    p.add_argument("code_id", type=int)
    p.add_argument("history_id", type=int)
    p.set_defaults(handler=cmd_code_rollback, renderer=render_code_detail)

    # 虚拟数据表
    p = add_common_parser(sub, "table-list", "列出虚拟数据表")
    p.add_argument("app_id", type=int)
    p.set_defaults(handler=cmd_table_list, renderer=render_tables)

    p = add_common_parser(sub, "table-create", "创建虚拟数据表")
    p.add_argument("app_id", type=int)
    p.add_argument("--name", required=True)
    p.add_argument("--slug", required=True)
    p.add_argument("--description", default=None)
    p.add_argument("--status", type=int, default=1, choices=(0, 1))
    p.set_defaults(handler=cmd_table_create)

    p = add_common_parser(sub, "table-field", "给虚拟数据表添加字段")
    p.add_argument("app_id", type=int)
    p.add_argument("table_id", type=int)
    p.add_argument("--name", required=True)
    p.add_argument("--slug", required=True)
    p.add_argument("--type", required=True, choices=("string", "text", "integer", "decimal", "boolean", "date", "datetime", "json"))
    p.add_argument("--length", type=int, default=None)
    p.add_argument("--nullable", type=int, default=None, choices=(0, 1))
    p.add_argument("--indexed", type=int, default=None, choices=(0, 1))
    p.add_argument("--default-value", default=None)
    p.add_argument("--description", default=None)
    p.set_defaults(handler=cmd_table_field)

    # 记录
    p = add_common_parser(sub, "record-list", "列出记录")
    p.add_argument("app_id", type=int)
    p.add_argument("table_id", type=int)
    p.add_argument("--page", type=int, default=1)
    p.add_argument("--per-page", type=int, default=100)
    p.set_defaults(handler=cmd_record_list, renderer=render_records)

    p = add_common_parser(sub, "record-create", "新增记录（--data JSON 或 @file）")
    p.add_argument("app_id", type=int)
    p.add_argument("table_id", type=int)
    p.add_argument("--data", default=None)
    p.set_defaults(handler=cmd_record_create)

    p = add_common_parser(sub, "record-update", "更新记录")
    p.add_argument("app_id", type=int)
    p.add_argument("table_id", type=int)
    p.add_argument("record_id", type=int)
    p.add_argument("--data", default=None)
    p.set_defaults(handler=cmd_record_update)

    p = add_common_parser(sub, "record-delete", "删除记录")
    p.add_argument("app_id", type=int)
    p.add_argument("table_id", type=int)
    p.add_argument("record_id", type=int)
    p.set_defaults(handler=cmd_record_delete)

    args = parser.parse_args()
    if getattr(args, "token", None) is None:
        args.token = env_value("MONTAGE_APP_TOKEN", "TASK_GITEE_TOKEN")
    if args.token is None:
        raise SystemExit("缺少 token：--token 或环境变量 MONTAGE_APP_TOKEN (或 TASK_GITEE_TOKEN)")
    args.handler(args)


def cmd_app_list(args):
    call(args, "GET", "/app-manage/apps", query=args.query, renderer=getattr(args, "renderer", None))


def cmd_app_show(args):
    call(args, "GET", "/app-manage/apps/%d" % args.id, renderer=getattr(args, "renderer", None))


def cmd_app_create(args):
    body = {"name": args.name, "slug": args.slug, "status": args.status, "auth_mode": args.auth_mode}
    if args.description is not None:
        body["description"] = args.description
    call(args, "POST", "/app-manage/apps", data=body)


def cmd_app_update(args):
    body = {}
    for field in ("name", "slug", "description", "status", "auth_mode"):
        value = getattr(args, field, None)
        if value is not None:
            body[field] = value
    call(args, "PUT", "/app-manage/apps/%d" % args.id, data=body)


def cmd_app_delete(args):
    call(args, "DELETE", "/app-manage/apps/%d" % args.id)


def cmd_code_create(args):
    content = text_value(args.content, args.content_file)
    body = {"name": args.name, "path": args.path, "type": args.type, "status": args.status}
    if args.auth_mode is not None:
        body["auth_mode"] = args.auth_mode
    body["content"] = content or ""
    call(args, "POST", "/app-manage/apps/%d/codes" % args.app_id, data=body, renderer=getattr(args, "renderer", None))


def cmd_code_update(args):
    body = {}
    content = text_value(args.content, args.content_file)
    if content is not None:
        body["content"] = content
    for field in ("name", "path", "type", "status", "auth_mode"):
        value = getattr(args, field, None)
        if value is not None:
            body[field] = value
    call(args, "PUT", "/app-manage/apps/%d/codes/%d" % (args.app_id, args.code_id), data=body, renderer=getattr(args, "renderer", None))


def cmd_code_delete(args):
    call(args, "DELETE", "/app-manage/apps/%d/codes/%d" % (args.app_id, args.code_id))


def cmd_code_history(args):
    call(args, "GET", "/app-manage/apps/%d/codes/%d/history" % (args.app_id, args.code_id), renderer=getattr(args, "renderer", None))


def cmd_code_rollback(args):
    call(args, "POST", "/app-manage/apps/%d/codes/%d/rollback/%d" % (args.app_id, args.code_id, args.history_id), renderer=getattr(args, "renderer", None))


def cmd_table_list(args):
    call(args, "GET", "/app-manage/apps/%d/virtual-tables" % args.app_id, renderer=getattr(args, "renderer", None))


def cmd_table_create(args):
    body = {"name": args.name, "slug": args.slug, "status": args.status}
    if args.description is not None:
        body["description"] = args.description
    call(args, "POST", "/app-manage/apps/%d/virtual-tables" % args.app_id, data=body)


def cmd_table_field(args):
    body = {"name": args.name, "slug": args.slug, "type": args.type}
    for field, arg_name in (("length", "length"), ("nullable", "nullable"), ("indexed", "indexed"),
                            ("default_value", "default_value"), ("description", "description")):
        value = getattr(args, arg_name, None)
        if value is not None:
            body[field] = value
    call(args, "POST", "/app-manage/apps/%d/virtual-tables/%d/fields" % (args.app_id, args.table_id), data=body)


def cmd_record_list(args):
    call(args, "GET", "/app-manage/apps/%d/virtual-tables/%d/records" % (args.app_id, args.table_id),
         query=["page=%d" % args.page, "per_page=%d" % args.per_page],
         renderer=getattr(args, "renderer", None))


def cmd_record_create(args):
    body = load_json(args.data) or {}
    call(args, "POST", "/app-manage/apps/%d/virtual-tables/%d/records" % (args.app_id, args.table_id), data=body)


def cmd_record_update(args):
    body = load_json(args.data) or {}
    call(args, "PUT", "/app-manage/apps/%d/virtual-tables/%d/records/%d" % (args.app_id, args.table_id, args.record_id), data=body)


def cmd_record_delete(args):
    call(args, "DELETE", "/app-manage/apps/%d/virtual-tables/%d/records/%d" % (args.app_id, args.table_id, args.record_id))


if __name__ == "__main__":
    main()