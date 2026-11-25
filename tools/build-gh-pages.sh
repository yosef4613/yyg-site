#!/bin/bash
# בונה את העותק של האתר לגיטהאב (yosef4613.github.io/yyg-site) ודוחף אותו לענף gh-pages.
# העותק עצמאי: בלי הפניה לדומיין, עם קישורים שמתאימים לכתובת בגיטהאב,
# ומסומן "לא לאנדקס" כדי שגוגל יציג רק את yygweb.com.
# שימוש: bash tools/build-gh-pages.sh  (מתוך תיקיית המאגר)
set -e
SRC=$(cd "$(dirname "$0")/.." && pwd)
OUT=$(mktemp -d)
cd "$SRC"
git archive HEAD | tar -x -C "$OUT"
cd "$OUT"
rm -rf CNAME README.md .gitignore .hostinger tools _private .htaccess ./*.php
python3 - "$OUT" <<'PY'
import sys, pathlib, re
root = pathlib.Path(sys.argv[1])
BASE = '/yyg-site'
for f in root.rglob('*.html'):
    s = f.read_text()
    # לא לאנדקס את העותק
    if 'name="robots"' not in s:
        s = s.replace('<meta charset="utf-8">', '<meta charset="utf-8">\n<meta name="robots" content="noindex">', 1)
    # קישורים שמתחילים ב־/ מקבלים את תחילית המאגר
    s = re.sub(r'(href|src)="/(?!/)', lambda m: f'{m.group(1)}="{BASE}/', s)
    # המונה לא רץ בעותק
    f.write_text(s)
# הטופס שולח לשרת בהוסטינגר
js = root / 'assets' / 'site.js'
t = js.read_text()
t = t.replace("formEndpoint: '/send.php',", "formEndpoint: 'https://yygweb.com/send.php',")
js.write_text(t)
PY
cd "$SRC"
WT=$(mktemp -d)
git -C /home/user/yyg-site fetch -q origin gh-pages
git -C /home/user/yyg-site worktree add -q -B gh-pages "$WT" origin/gh-pages
find "$WT" -mindepth 1 -maxdepth 1 ! -name .git -exec rm -rf {} +
cp -a "$OUT"/. "$WT"/
touch "$WT/.nojekyll"
cd "$WT"
git add -A
git commit -q -m "עותק עצמאי של האתר לגיטהאב, מעודכן לגרסה $(git -C "$SRC" rev-parse --short HEAD)" || echo "אין שינויים"
git -c push.negotiate=true push -q origin gh-pages
cd /home/user/yyg-site && git worktree remove --force "$WT"
rm -rf "$OUT"
echo "העותק בגיטהאב עודכן"
