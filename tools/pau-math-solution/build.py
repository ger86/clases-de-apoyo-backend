#!/usr/bin/env python3
"""Build and check the solution PDF of one PAU Math exam.

Usage: python3 tools/pau-math-solution/build.py OUT [--all-pages | --pages 4,7] [--force-figures]

OUT must hold:
  meta.json     {"title": "Solución Matemáticas II Modelo 2027"}
  content.html  everything inside <body> after the watermark: title block, statements, solutions
  figures.py    optional; run with the shared venv when it is newer than the PDF or a figure is missing

The script fills template.html, runs figures.py, prints solucion.pdf with headless Chrome,
runs the layout checks and renders review PNGs: only the pages with a problem, plus the pages
given with --pages (for example --pages 4,7 or --pages 2-4), or every page with --all-pages.
It prints a short report and exits with 1 when a check fails.
"""
import json
import re
import subprocess
import sys
import venv
from pathlib import Path

TOOL = Path(__file__).resolve().parent
VENV = TOOL / ".venv"
CHROME = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
PRINT_WIDTH_PX = 673  # 178 mm content width (A4 minus 16 mm margins) at 96 dpi
HEADINGS = ("Pregunta", "Ejercicio", "EJERCICIO", "Responda", "Solución.", "PARTE", "BLOQUE", "OPCIÓN", "Opción")

LINT_JS = r"""
<script>
window.addEventListener('load', function () {
  document.fonts.ready.then(function () { setTimeout(runLint, 50); });
});
function plain(el) {
  if (!el) return '';
  var c = el.cloneNode(true);
  c.querySelectorAll('.katex').forEach(function (k) { k.replaceWith(' | '); });
  return c.textContent.replace(/\s+/g, ' ').trim();
}
function where(el) {
  var p = el;
  while (p && !(p.previousElementSibling && plain(p.previousElementSibling).length > 15)) p = p.parentElement;
  return p ? plain(p.previousElementSibling).slice(0, 70) : '';
}
function contentBox(display) {
  var r = null;
  display.querySelectorAll('.base').forEach(function (b) {
    var q = b.getBoundingClientRect();
    if (q.width === 0) return;
    r = r ? {l: Math.min(r.l, q.left), t: Math.min(r.t, q.top), r: Math.max(r.r, q.right), b: Math.max(r.b, q.bottom)}
          : {l: q.left, t: q.top, r: q.right, b: q.bottom};
  });
  return r;
}
function runLint() {
  var out = {issues: [], watermarks: document.querySelectorAll('.watermark').length};
  if (out.watermarks !== 1) out.issues.push({kind: 'watermark', msg: out.watermarks + ' watermark divs (must be 1)'});
  document.querySelectorAll('.katex-error').forEach(function (e) {
    out.issues.push({kind: 'katex-error', msg: e.getAttribute('title') || e.textContent.slice(0, 80), where: where(e)});
  });
  document.querySelectorAll('.katex-html [style*="204, 0, 0"]').forEach(function (e) {
    if (e.parentElement.closest('[style*="204, 0, 0"]')) return;
    out.issues.push({kind: 'katex-error', msg: 'unknown command ' + e.textContent.slice(0, 40), where: where(e.closest('.katex-display') || e.closest('.katex'))});
  });
  document.querySelectorAll('img').forEach(function (i) {
    if (!i.complete || i.naturalWidth === 0) out.issues.push({kind: 'image', msg: 'image not loaded: ' + i.getAttribute('src')});
  });
  var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
  var n;
  while ((n = walker.nextNode())) {
    if (n.parentElement.closest('.katex, script, style')) continue;
    if (/\\\(|\\\)|\\\[|\\\]|\$|\\frac|\\left/.test(n.textContent)) {
      out.issues.push({kind: 'raw-latex', msg: n.textContent.trim().slice(0, 80), where: where(n.parentElement)});
    }
  }
  var figs = Array.prototype.map.call(document.querySelectorAll('.fig-r'), function (f) {
    var q = f.getBoundingClientRect(); return {l: q.left, t: q.top, r: q.right, b: q.bottom, src: (f.querySelector('img') || {}).getAttribute ? f.querySelector('img').getAttribute('src') : ''};
  });
  document.querySelectorAll('.katex-display').forEach(function (d) {
    var r = contentBox(d);
    if (!r) return;
    var parent = d.closest('.txt, .eq, li, p, body').parentElement.getBoundingClientRect();
    if (r.r > parent.right + 2 || r.l < parent.left - 2) {
      out.issues.push({kind: 'overflow', msg: 'formula wider than the text column', where: where(d)});
    }
    figs.forEach(function (f) {
      if (r.r > f.l + 2 && r.l < f.r - 2 && r.b > f.t + 2 && r.t < f.b - 2) {
        var ov = Math.round(Math.min(r.r - f.l, f.r - r.l)) + 'x' + Math.round(Math.min(r.b - f.t, f.b - r.t));
        out.issues.push({kind: 'figure-collision', msg: 'formula runs into figure ' + f.src + ' (overlap ' + ov + ' px)', where: where(d)});
      }
    });
  });
  var pre = document.createElement('pre');
  pre.id = 'lint-result';
  pre.textContent = JSON.stringify(out);
  document.body.appendChild(pre);
}
</script>
"""


def run(cmd, **kw):
    return subprocess.run(cmd, capture_output=True, text=True, **kw)


def ensure_venv():
    py = VENV / "bin" / "python"
    if not py.exists():
        venv.create(VENV, with_pip=True)
        run([str(py), "-m", "pip", "install", "-q", "sympy", "matplotlib", "scipy"], check=True)
    return py


def chrome(*args):
    return run([CHROME, "--headless", "--disable-gpu", "--virtual-time-budget=10000", *args])


def norm(text):
    return re.sub(r"\s+", " ", text).strip()


def main():
    argv = sys.argv[1:]
    wanted = set()
    for i, a in enumerate(argv):
        if a == "--pages" or a.startswith("--pages="):
            value = a.split("=", 1)[1] if "=" in a else (argv[i + 1] if i + 1 < len(argv) else "")
            if not re.fullmatch(r"\d+(-\d+)?(,\d+(-\d+)?)*", value):
                sys.exit("--pages needs page numbers, for example --pages 4,7 or --pages 2-4")
            for part in value.split(","):
                first, _, last = part.partition("-")
                wanted.update(range(int(first), int(last or first) + 1))
            argv = argv[:i] + argv[i + (1 if "=" in a else 2):]
            break
    args = [a for a in argv if not a.startswith("--")]
    flags = {a for a in argv if a.startswith("--")}
    if len(args) != 1:
        sys.exit(__doc__)
    out = Path(args[0]).resolve()
    meta = json.loads((out / "meta.json").read_text())
    content = (out / "content.html").read_text()
    for bad in ("<html", "<head", "<body", 'class="watermark"', "<script", "<style"):
        if bad in content:
            sys.exit(f"content.html must not contain {bad}: the template already has it")

    html = (TOOL / "template.html").read_text()
    html = html.replace("{{TITLE}}", meta["title"]).replace("{{CONTENT}}", content)
    (out / "solucion.html").write_text(html)
    pdf = out / "solucion.pdf"

    # Figures
    figures = out / "figures.py"
    srcs = re.findall(r'<img[^>]+src="([^"]+)"', content)
    missing = [s for s in srcs if not (out / s).exists()]
    if figures.exists() and ("--force-figures" in flags or missing or not pdf.exists()
                             or figures.stat().st_mtime > pdf.stat().st_mtime):
        r = run([str(ensure_venv()), str(figures)], cwd=out)
        if r.returncode != 0:
            sys.exit("figures.py failed:\n" + r.stderr[-2000:])

    issues = []
    if "—" in html:
        issues.append({"kind": "em-dash", "msg": f"{html.count('—')} em-dashes in solucion.html"})

    # DOM checks at print width
    lint_html = out / ".lint.html"
    lint_html.write_text(html.replace("</body>", LINT_JS + "</body>"))
    dom = chrome(f"--window-size={PRINT_WIDTH_PX},1200", "--dump-dom", lint_html.as_uri()).stdout
    lint_html.unlink()
    m = re.search(r'<pre id="lint-result">(.*?)</pre>', dom, re.S)
    if not m:
        issues.append({"kind": "lint", "msg": "DOM lint did not run (KaTeX or fonts did not load?)"})
    else:
        issues += json.loads(m.group(1).replace("&amp;", "&").replace("&lt;", "<").replace("&gt;", ">").replace("&quot;", '"'))["issues"]

    # Print
    chrome(f"--print-to-pdf={pdf}", "--no-pdf-header-footer", (out / "solucion.html").as_uri())
    if not pdf.exists():
        sys.exit("Chrome did not write solucion.pdf")
    pages = int(re.search(r"Pages:\s+(\d+)", run(["pdfinfo", str(pdf)]).stdout).group(1))

    # PDF checks
    page_text = [norm(run(["pdftotext", "-layout", "-f", str(p), "-l", str(p), str(pdf), "-"]).stdout) for p in range(1, pages + 1)]
    bbox = run(["pdftotext", "-bbox", str(pdf), "-"]).stdout
    for p, page in enumerate(re.findall(r"<page (.*?)</page>", bbox, re.S), start=1):
        height = float(re.search(r'height="([\d.]+)"', page).group(1))
        words = re.findall(r'xMin="([\d.]+)" yMin="([\d.]+)" xMax="[\d.]+" yMax="[\d.]+">([^<]*)<', page)
        if "www.clasesdeapoyo.com" not in page_text[p - 1]:
            issues.append({"kind": "header", "msg": "blue header missing", "page": p})
        body = [w for w in words if 60 < float(w[1]) < height - 40]
        last_y = max((float(w[1]) for w in body), default=0)
        for x, y, word in body:
            if float(x) < 70 and word.startswith(HEADINGS) and float(y) > 0.80 * height and last_y - float(y) < 60:
                issues.append({"kind": "orphan-heading", "msg": f'"{word}" alone at the bottom of the page', "page": p})
        if re.search(r"\\frac|\\left|\\\(|\\\[|\$", page_text[p - 1]):
            issues.append({"kind": "raw-latex", "msg": "raw LaTeX in the PDF text", "page": p})

    # Find the page of each DOM issue from its nearby text
    for it in issues:
        if "page" not in it and it.get("where"):
            parts = [norm(x) for x in it["where"].split("|")]
            key = next((x for x in parts if len(x) >= 12), "")[:40]
            for p, t in enumerate(page_text, start=1):
                if key and key in t:
                    it["page"] = p
                    break

    # Review images
    for old in out.glob("review-*.png"):
        old.unlink()
    flagged = sorted({it["page"] for it in issues if it.get("page")})
    to_render = range(1, pages + 1) if "--all-pages" in flags else sorted(set(flagged) | {p for p in wanted if 1 <= p <= pages})
    for p in to_render:
        run(["pdftoppm", "-r", "90", "-png", "-f", str(p), "-l", str(p), "-singlefile", str(pdf), str(out / f"review-{p:02d}")])

    print(f"solucion.pdf: {pages} pages")
    for it in issues:
        loc = f"page {it['page']}" if it.get("page") else "page ?"
        near = f' | near: "{norm(it["where"].replace("|", "…"))[:60]}"' if it.get("where") else ""
        print(f"FAIL {it['kind']} ({loc}): {it['msg']}{near}")
    if to_render:
        print("review images: " + ", ".join(f"review-{p:02d}.png" for p in to_render))
    print("ALL CHECKS PASSED" if not issues else f"{len(issues)} problem(s)")
    sys.exit(1 if issues else 0)


if __name__ == "__main__":
    main()
