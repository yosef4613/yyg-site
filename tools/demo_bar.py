#!/usr/bin/env python3
"""YYG Web Design: הפס העליון והתווית הצדדית בכל עמודי הדוגמה, כדי שאף אחד לא יחשוב שזה אתר אמיתי של עסק.
מחליף את הפס הקיים (HTML ו־CSS) בכל העיצובים, בגרסה הסופית ובמסך השעות של צלול. אפשר להריץ שוב ושוב.
הרצה: python3 tools/demo_bar.py"""
import pathlib, re

ROOT = pathlib.Path(__file__).resolve().parent.parent
PAGES = {  # תיקיית פרויקט -> עמודים, וכל עמוד: איזה קישור מסומן כנוכחי
 '/demo/': ['design-1', 'design-2', 'design-3', 'final', 'admin'],
 '/demo/optika/': ['design-1', 'design-2', 'design-3', 'final', 'admin'],
 '/demo/donate/': ['design-1', 'design-2', 'design-3', 'final', 'admin'],
 '/demo/shul/': ['design-1', 'design-2', 'design-3', 'final', 'admin'],
 '/demo/shop/': ['design-1', 'design-2', 'design-3', 'final', 'admin'],
 '/demo/basic/': ['design-1', 'final'],
 '/demo/landing/': ['design-1', 'design-2', 'design-3', 'final'],
}
CSS = '''.demo-bar{display:flex;align-items:center;gap:8px 14px;flex-wrap:wrap;padding:8px 16px;background:#151920;color:#E9EDF3;font:500 14px/1.45 system-ui,-apple-system,"Segoe UI",Arial,sans-serif;position:relative;z-index:100;border-bottom:3px solid #F2C95A}
.demo-flag{flex:none;background:#F2C95A;color:#151920;font-weight:800;padding:3px 10px;border-radius:6px}
.demo-say{flex:1 1 260px;color:#C9D1DD}
.demo-bar nav{display:flex;flex-wrap:wrap;gap:4px}
.demo-bar a{color:#E9EDF3;text-decoration:none;padding:6px 10px;border-radius:8px;min-height:32px;display:inline-flex;align-items:center}
.demo-bar nav a[aria-current]{background:#F2C95A;color:#151920;font-weight:700}
.demo-bar a:hover{background:rgba(255,255,255,.1)}.demo-bar nav a[aria-current]:hover{background:#F2C95A}
.demo-bar a:focus-visible{outline:2px solid #F2C95A;outline-offset:2px}
.demo-ribbon{position:fixed;top:42%;right:0;z-index:99;writing-mode:vertical-rl;background:#F2C95A;color:#151920;font:800 12px/1 system-ui,-apple-system,"Segoe UI",Arial,sans-serif;letter-spacing:.04em;padding:12px 6px;border-radius:0 8px 8px 0;box-shadow:0 4px 14px rgba(0,0,0,.25);pointer-events:none}
.demo-home{position:fixed;left:16px;bottom:calc(16px + var(--lift,0px) + env(safe-area-inset-bottom,0px));z-index:98;display:inline-flex;align-items:center;gap:8px;padding:7px 16px 7px 8px;background:#151920;color:#fff;border:2px solid #F2C95A;border-radius:999px;box-shadow:0 8px 24px rgba(0,0,0,.35);font:700 15px/1.2 system-ui,-apple-system,"Segoe UI",Arial,sans-serif;text-decoration:none}
.demo-home img{width:30px;height:30px;flex:none}
.demo-home:hover{background:#262D3A}
.demo-home:focus-visible{outline:3px solid #F2C95A;outline-offset:3px}
.demo-home-s{display:none}
@media (max-width:560px){.demo-say{flex-basis:100%;font-size:13px}.demo-home-l{display:none}.demo-home-s{display:inline}.demo-home{left:12px;font-size:14px;padding:6px 14px 6px 7px}}
@media print{.demo-ribbon,.demo-home{display:none}}
'''

ADMIN = {'/demo/': 'מסך ניהול התוכן', '/demo/optika/': 'מסך עדכון השעות', '/demo/donate/': 'מסך ניהול הקמפיין', '/demo/shul/': 'מסך הגבאי', '/demo/shop/': 'ניהול החנות'}

def bar(prefix, current):
    pages = PAGES[prefix]   # בדף הבסיסי יש עיצוב אחד, ובדף נחיתה רגיל אין מסך ניהול
    names = {'design-1': 'העיצוב' if len([x for x in pages if x.startswith('design-')]) == 1 else 'עיצוב 1', 'design-2': 'עיצוב 2', 'design-3': 'עיצוב 3', 'final': 'הגרסה הסופית', 'admin': ADMIN.get(prefix, 'מסך הניהול')}
    links = [(k, names[k]) for k in pages]
    nav = ''.join(f'<a href="{prefix}{k}/"{" aria-current=\"page\"" if k == current else ""}>{t}</a>' for k, t in links)
    kind = 'הגרסה הסופית, אחרי המשוב של הלקוח.' if current == 'final' else ('העיצוב האחד של דף הנחיתה הבסיסי, לפני סבב התיקונים.' if prefix == '/demo/basic/' and current == 'design-1' else None)
    kind = kind or ('מסך ניהול לדוגמה: מה שמשנים כאן נשמר רק בדפדפן שלכם, ומופיע בגרסה הסופית.' if current == 'admin' else 'אחד מ־3 כיווני עיצוב לאותו תוכן.')
    return (f'<div class="demo-bar" role="note" aria-label="אתר לדוגמה">\n'
            f' <strong class="demo-flag">אתר לדוגמה בלבד</strong>\n'
            f' <span class="demo-say">זה לא אתר של עסק אמיתי: העסק, הפרטים וההמלצות דמיוניים. {kind}</span>\n'
            f' <nav aria-label="עמודי הדוגמה">{nav}</nav>\n'
            f' <a class="demo-back" href="{prefix}">לסיפור הפרויקט</a>\n'
            f'</div>\n<div class="demo-ribbon" aria-hidden="true">אתר לדוגמה</div>\n'
            # כפתור צף וברור לחזרה לאתר שלי, בכל עמוד דוגמה. demo-home.js: באנגלית למי שהגיע מהאתר באנגלית, ומעל פסים קבועים בתחתית
            f'<a class="demo-home" href="/"><img src="/assets/img/yyg-icon-96.png" width="30" height="30" alt=""><span class="demo-home-l">חזרה לאתר של YYG Web Design</span><span class="demo-home-s">חזרה ל־YYG Web Design</span></a>\n'
            f'<script src="/demo/demo-home.js" defer></script>')

def fix(path, prefix, current):
    s = path.read_text()
    # ה־HTML הישן של הפס (והתווית, אם כבר יש)
    s = re.sub(r'<div class="demo-ribbon"[^>]*>[^<]*</div>\n?', '', s)
    s = re.sub(r'<a class="demo-home".*?</a>\n?', '', s, flags=re.S)
    s = re.sub(r'<script src="/demo/demo-home\.js" defer></script>\n?', '', s)
    s, n = re.subn(r'<div class="demo-bar" role="note"[^>]*>.*?</div>', bar(prefix, current), s, count=1, flags=re.S)
    if not n: raise SystemExit(f'no demo bar in {path}')
    # ה־CSS הישן: כל כלל שמתחיל ב־.demo-bar או .demo-flag/.demo-say/.demo-ribbon, וגם בתוך @media
    st = s.index('<style>') + len('<style>')
    head, rest = s[:st], s[st:]
    end = rest.index('</style>')
    css = rest[:end]
    css = re.sub(r'\.demo-(?:bar|flag|say|ribbon|back|home)[^{}]*\{[^{}]*\}', '', css)
    css = re.sub(r'@media \(max-width:\d+px\)\{\s*\}', '', css)
    css = re.sub(r'@media print\{\s*\}', '', css)
    css = re.sub(r'\n\s*\n+', '\n', css)
    s = head + '\n' + CSS + css + rest[end:]
    path.write_text(s)

if __name__ == '__main__':
    done = []
    for prefix, pages in PAGES.items():
        for p in pages:
            f = ROOT / prefix.strip('/') / p / 'index.html'
            if f.exists(): fix(f, prefix, p); done.append(str(f.relative_to(ROOT)))
    print(done)
