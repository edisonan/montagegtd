#!/usr/bin/env python3
"""Fetch a DeepSeek Harness docs page and convert its vp-doc content to clean Markdown.

Cleans the artifacts found in course 4: zero-width spaces, [](#anchor) residues,
stray language labels before fences, nav/footer noise.
"""
import re
import sys
import urllib.request
from html.parser import HTMLParser

BASE = "https://deepseek-harness.github.io/deepseek-harness/"


def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": "montagegtd-course-extractor/1.0"})
    with urllib.request.urlopen(req, timeout=30) as resp:
        return resp.read().decode("utf-8", errors="replace")


def read_html_file(path):
    with open(path, encoding="utf-8", errors="replace") as fh:
        return fh.read()


def extract_vp_doc(html_text):
    """Return the HTML of the vp-doc container (main doc body)."""
    m = re.search(
        r'<div[^>]*class="[^"]*\bvp-doc\b[^"]*"[^>]*>(.*?)</div>\s*(?:</main>|<footer|</div>\s*</div>\s*</div>|$)',
        html_text, re.S)
    if m:
        return m.group(1)
    # fallback: strip head/nav and take body content
    m = re.search(r"<body[^>]*>(.*?)</body>", html_text, re.S)
    return m.group(1) if m else html_text


class MarkdownConverter(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.out = []
        self.pre_depth = 0       # inside <pre>
        self.code_lang = ""
        self.skip_stack = 0      # inside elements we drop entirely
        self.list_stack = []     # 'ul' | 'ol'
        self.ol_counter = 0
        self.in_table = False
        self.pending_newline = False
        self.para_open = False
        self.inline_stack = []   # formatting: b, i, a, code

    # ---------- helpers ----------
    def _emit(self, text):
        if self.skip_stack:
            return
        if self.pre_depth:
            self.out.append(text)
            return
        self.out.append(text)
        self.pending_newline = self.pending_newline and text == ""

    def _newline(self):
        if self.skip_stack:
            return
        if self.out and not self.out[-1].endswith("\n"):
            self.out.append("\n")

    def _blank(self):
        self._newline()
        if self.out and not self.out[-1].endswith("\n\n"):
            self.out.append("\n")

    def _inline_text(self):
        # flush accumulated plain text node as-is
        pass

    # ---------- parser callbacks ----------
    def handle_starttag(self, tag, attrs):
        ad = dict(attrs)
        cls = ad.get("class", "")
        if self.skip_stack:
            if tag not in ("script", "style"):
                self.skip_stack += 1
            return
        if tag in ("script", "style", "nav", "footer", "button", "svg", "iframe", "canvas", "aside"):
            self.skip_stack += 1
            return
        if tag == "pre":
            self.pre_depth = 1
            m = re.search(r"language-([\w+-]+)", cls)
            self.code_lang = m.group(1) if m else ""
            self.out.append("```" + self.code_lang + "\n")
            return
        if tag == "span" and "lang" in cls.split():
            # shiki 语言标签（<span class="lang">ts</span>）直接丢弃，围栏已有语言
            self.skip_stack += 1
            return
        if tag == "br":
            self._newline()
            return
        if tag == "p" or tag == "div":
            if tag == "p":
                self._blank()
                self.para_open = True
            return
        if re.fullmatch(r"h[1-6]", tag):
            self._blank()
            self.out.append("#" * int(tag[1]) + " ")
            return
        if tag == "ul":
            self._blank()
            self.list_stack.append("ul")
            return
        if tag == "ol":
            self._blank()
            self.list_stack.append("ol")
            self.ol_counter = 0
            return
        if tag == "li":
            self._newline()
            if self.list_stack and self.list_stack[-1] == "ol":
                self.ol_counter += 1
                self.out.append(f"{self.ol_counter}. ")
            else:
                self.out.append("- ")
            return
        if tag == "a":
            href = ad.get("href", "")
            self.out.append("[")
            self.inline_stack.append(("a", href))
            return
        if tag == "strong" or tag == "b":
            self.out.append("**")
            self.inline_stack.append(tag)
            return
        if tag == "em" or tag == "i":
            self.out.append("*")
            self.inline_stack.append(tag)
            return
        if tag == "code":
            if not self.pre_depth:
                self.out.append("`")
            return
        if tag == "table":
            self._blank()
            self.in_table = True
            return
        if tag in ("thead", "tbody", "tr", "td", "th"):
            if tag == "tr":
                self._newline()
            if tag in ("td", "th"):
                self.out.append(" | ")
            return
        if tag == "blockquote":
            self._blank()
            self.out.append("> ")
            return
        if tag == "hr":
            self._blank()
            self.out.append("---")
            self._blank()
            return
        if tag == "img":
            src = ad.get("src", ad.get("data-src", ""))
            alt = ad.get("alt", "")
            self.out.append(f"![{alt}]({src})")
            return

    def handle_endtag(self, tag):
        if self.skip_stack:
            if (tag in ("script", "style", "nav", "footer", "button", "svg", "iframe", "canvas", "aside")
                    or (tag == "span" and self.skip_stack > 0)):
                self.skip_stack -= 1
            return
        if tag == "pre":
            self.pre_depth = 0
            self.out.append("\n```\n")
            return
        if tag == "p":
            self._newline()
            self.para_open = False
            return
        if re.fullmatch(r"h[1-6]", tag):
            self._newline()
            self._blank()
            return
        if tag in ("ul", "ol"):
            if self.list_stack:
                self.list_stack.pop()
            self._blank()
            return
        if tag == "li":
            self._newline()
            return
        if tag == "a":
            href = ""
            if self.inline_stack and self.inline_stack[-1][0] == "a":
                href = self.inline_stack[-1][1]
                self.inline_stack.pop()
            self.out.append(f"]({href})")
            return
        if tag == "strong" or tag == "b":
            if self.inline_stack and self.inline_stack[-1] == tag:
                self.inline_stack.pop()
            self.out.append("**")
            return
        if tag == "em" or tag == "i":
            if self.inline_stack and self.inline_stack[-1] == tag:
                self.inline_stack.pop()
            self.out.append("*")
            return
        if tag == "code":
            if not self.pre_depth:
                self.out.append("`")
            return
        if tag == "table":
            self.in_table = False
            self._blank()
            return
        if tag == "tr":
            self._newline()
            return
        if tag in ("td", "th"):
            return
        if tag == "blockquote":
            self._newline()
            self._blank()
            return

    def handle_data(self, data):
        if self.skip_stack:
            return
        if self.pre_depth:
            self.out.append(data)
            return
        # collapse whitespace outside pre
        text = re.sub(r"\s+", " ", data)
        # In headings the text keeps spaces; trailing spaces are trimmed on flush
        if text:
            self.out.append(text)

    # ---------- finalize ----------
    def result(self):
        text = "".join(self.out)
        # 1. remove zero-width chars
        text = text.replace("\u200b", "").replace("\u200c", "").replace("\u200d", "")
        # 2. fix []( #anchor ) residues: "[text](  #anchor )" -> keep as plain
        text = re.sub(r"\[\s*\]\(\s*#?[^)]*\)", "", text)
        # 3. collapse blank runs
        text = re.sub(r"\n{3,}", "\n\n", text)
        # 4. trim trailing spaces per line
        text = re.sub(r" +$", "", text, flags=re.M)
        # 5. strip leading/trailing
        return text.strip()


def doc_to_markdown_from_html(html_text):
    body = extract_vp_doc(html_text)
    conv = MarkdownConverter()
    conv.feed(body)
    conv.close()
    return conv.result()


def doc_to_markdown(url):
    h = fetch(url)
    return doc_to_markdown_from_html(h)


if __name__ == "__main__":
    if len(sys.argv) >= 2 and sys.argv[1] == "--file":
        print(doc_to_markdown_from_html(read_html_file(sys.argv[2])))
        sys.exit(0)
    url = sys.argv[1] if len(sys.argv) > 1 else BASE + "develop/framework/"
    if not url.startswith("http"):
        url = BASE + url.lstrip("/")
    print(doc_to_markdown(url))