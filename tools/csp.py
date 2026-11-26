#!/usr/bin/env python3
"""YYG Web Design: מדיניות אבטחת תוכן (Content-Security-Policy) לעמודי ה־HTML.

הדפדפן מריץ רק סקריפטים מהאתר עצמו, ורק סקריפטים פנימיים שטביעת האצבע (sha256) שלהם מופיעה כאן.
כך קוד שמישהו יצליח להחדיר לעמוד לא ירוץ.

מריצים אחרי כל שינוי בסקריפט פנימי בעמוד (הסקריפט נמצא ברשימת המחוללים, אחרון):
    python3 tools/csp.py          מעדכן את .htaccess
    python3 tools/csp.py --check  רק בודק שכל הסקריפטים מכוסים (יוצא בשגיאה אם לא)
עמודי PHP (ניהול, אזור אישי, הצעת מחיר) קובעים לעצמם כותרות משלהם.
"""
import base64
import hashlib
import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
HTACCESS = ROOT / '.htaccess'
SKIP = {'tools', '_private', 'node_modules', '.git'}
START, END = '    # CSP:start (נוצר על ידי tools/csp.py, לא לערוך ידנית)', '    # CSP:end'
SCRIPT = re.compile(r'<script(\s[^>]*)?>(.*?)</script>', re.S | re.I)


def inline_hashes():
    found = {}
    for f in sorted(ROOT.rglob('*.html')):
        if SKIP & set(f.relative_to(ROOT).parts):
            continue
        for m in SCRIPT.finditer(f.read_text(encoding='utf-8')):
            attrs = m.group(1) or ''
            if re.search(r'\ssrc=', attrs):
                continue
            t = re.search(r'type=["\']?([^"\'\s>]+)', attrs)
            if t and t.group(1).lower() not in ('module', 'text/javascript', 'application/javascript'):
                continue  # נתונים (JSON-LD וכדומה) לא רצים, ולכן לא צריכים טביעה
            h = base64.b64encode(hashlib.sha256(m.group(2).encode('utf-8')).digest()).decode()
            found.setdefault(h, f.relative_to(ROOT).as_posix())
    return found


def policy(hashes):
    scripts = ' '.join(f"'sha256-{h}'" for h in sorted(hashes))
    return '; '.join([
        "default-src 'self'",
        f"script-src 'self' {scripts}",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com",
        "img-src 'self' data: blob:",
        "connect-src 'self'",
        "frame-src 'self'",
        "frame-ancestors 'self'",
        "form-action 'self'",
        "base-uri 'self'",
        "object-src 'none'",
        'upgrade-insecure-requests',
    ])


def main():
    hashes = inline_hashes()
    text = HTACCESS.read_text(encoding='utf-8')
    a, b = text.find(START), text.find(END)
    if a < 0 or b < 0:
        sys.exit('.htaccess: חסרים הסימונים CSP:start / CSP:end')
    line = f'    Header set Content-Security-Policy "{policy(hashes)}"'
    new = text[:a] + START + '\n' + line + '\n' + text[b:]
    if '--check' in sys.argv:
        if new != text:
            sys.exit(f'CSP לא מעודכן: יש סקריפט פנימי חדש או שהשתנה. מריצים python3 tools/csp.py ({len(hashes)} סקריפטים)')
        print(f'CSP מעודכן ({len(hashes)} סקריפטים פנימיים)')
        return
    HTACCESS.write_text(new, encoding='utf-8')
    print(f'CSP עודכן: {len(hashes)} סקריפטים פנימיים, {len(line)} תווים')


if __name__ == '__main__':
    main()
