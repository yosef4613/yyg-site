import json, os, html
import pathlib
root = str(pathlib.Path(__file__).resolve().parent.parent)
os.makedirs(f'{root}/guides', exist_ok=True)
SITE = 'https://yygweb.com'
TODAY = '2026-11-25'

GUIDES = []
STYLE = '''<style>
 .g-top{border-bottom:1px solid var(--line);background:var(--base)}
 .g-top .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:72px}
 .g-top img{height:48px;width:auto}
 .g-top nav{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
 .g-main{padding:clamp(36px,6vw,72px) 0 clamp(56px,8vw,104px)}
 .g-main .wrap{max-width:780px}
 .g-crumb{font-size:.92rem;color:var(--muted)}
 .g-crumb a{color:var(--muted)}
 .g-main h1{margin-top:12px;font-family:var(--display);font-weight:700;font-size:clamp(2.3rem,1.6rem + 2.6vw,3.6rem);line-height:1}
 .g-lead{margin-top:16px;font-size:var(--step-1);color:#D7DDE6}
 .g-meta{margin-top:10px;font-size:.92rem;color:var(--muted)}
 .g-main h2{margin-top:40px;font-size:var(--step-2);line-height:1.25}
 .g-main h3{margin-top:24px;font-size:var(--step-1)}
 .g-main p,.g-main li{margin-top:12px;color:#C9D1DD;line-height:1.75}
 .g-main ul,.g-main ol{padding-inline-start:24px}
 .g-main ul{list-style:disc}
 .g-main ol{list-style:decimal}
 .g-main b{color:#fff}
 .g-table{width:100%;margin-top:16px;border-collapse:collapse;font-size:.98rem}
 .g-table th,.g-table td{padding:10px 12px;border-bottom:1px solid var(--line);text-align:right;vertical-align:top}
 .g-table th{color:#fff;background:var(--panel)}
 .g-scroll{overflow-x:auto}
 .g-table td:nth-child(2){white-space:nowrap}
 .g-cta{margin-top:36px;padding:clamp(20px,3vw,28px);border:1px solid var(--line-2);border-radius:16px;background:var(--panel)}
 .g-cta h2{margin-top:0}
 .g-cta .btn{margin-top:16px}
 .g-main details{margin-top:4px}
 .g-rel{margin-top:40px}
 .g-rel ul{list-style:none;padding:0}
 .g-cards{display:grid;gap:14px;margin-top:24px}
 .g-card{display:block;padding:20px;border:1px solid var(--line);border-radius:14px;background:var(--panel);text-decoration:none;color:var(--text)}
 .g-card:hover{border-color:var(--gold)}
 .g-card b{display:block;font-size:var(--step-1)}
 .g-card span{display:block;margin-top:6px;color:var(--muted)}
</style>'''


def page(slug, title, desc, h1, lead, body, faq=None, related=()):
    url = f'{SITE}/guides/{slug}.html' if slug != 'index' else f'{SITE}/guides/'
    ld = [{
        '@context': 'https://schema.org', '@type': 'Article', 'headline': h1, 'description': desc,
        'inLanguage': 'he', 'datePublished': TODAY, 'dateModified': TODAY, 'mainEntityOfPage': url,
        'author': {'@type': 'Person', 'name': 'יהודה גורדון', 'url': SITE + '/'},
        'publisher': {'@type': 'Organization', 'name': 'YYG', 'logo': {'@type': 'ImageObject', 'url': SITE + '/assets/img/logo-320.webp'}},
        'image': SITE + '/assets/img/og.jpg',
    }]
    if faq:
        ld.append({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': [
            {'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}} for q, a in faq]})
    ld.append({'@context': 'https://schema.org', '@type': 'BreadcrumbList', 'itemListElement': [
        {'@type': 'ListItem', 'position': 1, 'name': 'YYG', 'item': SITE + '/'},
        {'@type': 'ListItem', 'position': 2, 'name': 'מדריכים', 'item': SITE + '/guides/'},
    ] + ([{'@type': 'ListItem', 'position': 3, 'name': h1, 'item': url}] if slug != 'index' else [])})
    faq_html = ''
    if faq:
        faq_html = '<h2>שאלות נפוצות</h2>\n' + '\n'.join(
            f'<details><summary>{html.escape(q)}</summary><p>{html.escape(a)}</p></details>' for q, a in faq)
    rel = ''
    if related:
        rel = '<aside class="g-rel" aria-labelledby="rel-t"><h2 id="rel-t">עוד מדריכים</h2><ul>' + ''.join(
            f'<li><a class="text-link" href="/guides/{s}.html">{t}</a></li>' for s, t in related) + '</ul></aside>'
    return f'''<!doctype html>
<html lang="he" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<script>if(location.protocol==="http:"&&location.hostname==="yygweb.com")location.replace("https://yygweb.com"+location.pathname+location.search+location.hash)</script>
<title>{html.escape(title)}</title>
<meta name="description" content="{html.escape(desc)}">
<link rel="canonical" href="{url}">
<meta property="og:type" content="article">
<meta property="og:locale" content="he_IL">
<meta property="og:url" content="{url}">
<meta property="og:title" content="{html.escape(h1)}">
<meta property="og:description" content="{html.escape(desc)}">
<meta property="og:image" content="{SITE}/assets/img/og.jpg">
<meta name="theme-color" content="#151920">
<link rel="icon" href="/assets/img/icon-64.png" type="image/png">
<link rel="preload" href="/assets/fonts/karantina-700-hebrew.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/site.css?v=18">
{STYLE}
<script type="application/ld+json">{json.dumps(ld, ensure_ascii=False, separators=(',', ':'))}</script>
</head>
<body>
<a class="skip" href="#main">דילוג לתוכן</a>
<header class="g-top">
 <div class="wrap">
  <a href="/"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="YYG, לעמוד הבית"></a>
  <nav aria-label="ניווט">
   <a class="text-link" href="/guides/">מדריכים</a>
   <a class="text-link" href="/#plan">מחשבון מחיר</a>
   <a class="text-link" href="/#contact">יצירת קשר</a>
  </nav>
 </div>
</header>
<main id="main" class="g-main">
 <div class="wrap">
  <p class="g-crumb"><a href="/">YYG</a> › <a href="/guides/">מדריכים</a></p>
  <h1>{h1}</h1>
  <p class="g-lead">{lead}</p>
  <p class="g-meta">יהודה גורדון, מעצב ובונה אתרים · עודכן בכסלו תשפ״ז</p>
{body}
{faq_html}
  <section class="g-cta" aria-labelledby="cta-t">
   <h2 id="cta-t">רוצים לדעת כמה יעלה האתר שלכם?</h2>
   <p>3 שאלות קצרות במחשבון, בלי להשאיר פרטים. ואם מתאים, אכין לכם 3 עיצובים של האתר לפני שמשלמים.</p>
   <a class="btn btn-gold" href="/?src=guide-{slug}#plan">למחשבון המחיר</a>
  </section>
{rel}
 </div>
</main>
<footer class="foot">
 <div class="wrap foot-in">
  <p class="foot-brand"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="" loading="lazy"> YYG, יהודה גורדון. עיצוב ובניית אתרים</p>
  <p><a class="foot-link" href="/guides/">מדריכים</a> · <a class="foot-link" href="/accessibility.html">הצהרת נגישות</a></p>
 </div>
</footer>
<script>if(location.hostname==="yygweb.com"&&navigator.sendBeacon){{try{{var q=new URLSearchParams(location.search);navigator.sendBeacon("/hit.php",new Blob([JSON.stringify({{e:"view",p:location.pathname,r:document.referrer,s:q.get("src")||""}})],{{type:"application/json"}}))}}catch(e){{}}}}</script>
</body>
</html>
'''


# ---------- מדריך 1: כמה עולה אתר ----------
cost_body = '''
<h2>התשובה הקצרה</h2>
<p>מחיר של אתר תלוי בעיקר בשלושה דברים: <b>כמה עמודים</b>, <b>מה האתר צריך לעשות</b> (רק להציג, לקבל הרשמות או למכור), ו<b>מי מעדכן אותו</b> אחר כך. הנה המחירים ההתחלתיים אצלי, כדי שתהיה לכם נקודת פתיחה. בלי תוספת מע״מ. המחיר הסופי נקבע לפי הצרכים והדרישות שלכם, ותמיד מופיע בהצעה מסודרת לפני שמתחילים:</p>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>סוג האתר</th><th>מחיר</th><th>זמן אספקה</th><th>למי זה מתאים</th></tr></thead>
<tbody>
<tr><td>דף נחיתה</td><td>מ־1,900 ₪</td><td>עד 4 ימי עבודה</td><td>עסק עם שירות אחד או מבצע, שרוצה שיתקשרו אליו</td></tr>
<tr><td>אתר עם אזור ניהול</td><td>מ־3,900 ₪</td><td>עד 10 ימי עבודה</td><td>עסק עם כמה שירותים, שרוצה לעדכן תוכן לבד</td></tr>
<tr><td>אתר עם הרשמה ורשימת תפוצה</td><td>מ־5,900 ₪</td><td>עד 15 ימי עבודה</td><td>חוגים, מוסדות וארגונים שמקבלים הרשמות</td></tr>
<tr><td>חנות אונליין</td><td>מ־6,900 ₪</td><td>עד 20 ימי עבודה</td><td>מי שמוכר מוצרים, עם תשלום באשראי ומשלוחים</td></tr>
</tbody></table></div>
<p>גרסה באנגלית היא תוספת, מ־500 ₪ בדף נחיתה ועד 1,800 ₪ בחנות.</p>

<h2>מה מייקר אתר, ומה לא</h2>
<h3>1. מספר העמודים</h3>
<p>כל עמוד צריך עיצוב, תוכן ובדיקה בטלפון. דף נחיתה הוא עמוד אחד ארוך. אתר תדמית רגיל הוא בדרך כלל 4 עד 8 עמודים: בית, שירותים, אודות, שאלות ויצירת קשר.</p>
<h3>2. מה האתר עושה</h3>
<p>אתר שרק מציג מידע הוא הזול ביותר. ברגע שהאתר צריך לקבל הרשמות, לשלוח מיילים, לנהל חשבונות של משתמשים או לגבות תשלום, יש יותר בנייה ויותר בדיקות.</p>
<h3>3. מי מעדכן את האתר</h3>
<p>אם התוכן כמעט לא משתנה, אפשר לוותר על אזור ניהול ולחסוך. אם אתם מעלים חדשות, מבצעים, מוצרים או לוחות זמנים, כדאי אזור ניהול שבו אתם מעדכנים לבד, בלי לחכות למישהו.</p>
<h3>4. עיצוב מותאם או תבנית</h3>
<p>תבנית מוכנה זולה יותר בהתחלה, אבל נראית כמו עוד מאות אתרים. עיצוב מותאם בנוי סביב העסק שלכם, הלקוחות שלכם והפעולה שאתם רוצים שהם יעשו.</p>
<h3>5. נגישות</h3>
<p>החוק בישראל מחייב חלק גדול מהעסקים והמוסדות להנגיש את האתר. הנגשה שנבנית מההתחלה זולה בהרבה מהנגשה של אתר קיים. אצלי היא כלולה בכל מסלול.</p>

<h2>העלויות הקבועות, אחרי שהאתר מוכן</h2>
<ul>
<li><b>דומיין</b> (הכתובת של האתר): כתובת ‎.com עולה בדרך כלל עשרות שקלים בשנה.</li>
<li><b>אחסון</b> (השרת שהאתר יושב עליו): בדרך כלל כמה מאות שקלים בשנה, לפי החברה והמסלול.</li>
<li><b>מייל עסקי</b> על הדומיין: לא חובה. יש חברות אחסון שנותנות אותו בחינם עם המסלול.</li>
</ul>
<p>אצלי הדומיין והאחסון נרשמים <b>על שמכם</b>, כך שהאתר שלכם באמת, גם אם תחליטו לעבור לאיש מקצוע אחר.</p>

<h2>איך לא לשלם על אתר שלא אהבתם</h2>
<p>הסיכון הכי גדול בבניית אתר הוא לשלם מראש ולגלות בסוף שהעיצוב לא מה שדמיינתם. לכן אצלי זה עובד אחרת:</p>
<ol>
<li>אחרי שיחה קצרה, אני מכין <b>3 עיצובים</b> של האתר שלכם, עם התוכן שלכם. בלי תשלום.</li>
<li>אהבתם כיוון? משלמים 50% וממשיכים לבנייה.</li>
<li>את היתרה משלמים בסוף, <b>אחרי שבדקתם ואישרתם</b>, לפני שהאתר עולה לאוויר.</li>
</ol>
'''
cost_faq = [
    ('כמה עולה דף נחיתה?', 'אצלי דף נחיתה מתחיל ב־1,900 ₪, בלי מע״מ. הוא כולל 3 כיווני עיצוב, 3 סבבי תיקונים, טופס פנייה וכפתור חיוג, ומוכן עד 4 ימי עבודה. המחיר הסופי נקבע לפי מה שהדף צריך.'),
    ('האם המחיר כולל מע״מ?', 'כן. המחירים אצלי סופיים, בלי תוספת מע״מ.'),
    ('יש תשלום חודשי?', 'אצלי אין תשלום חודשי על האתר. יש רק את העלויות השנתיות של הדומיין והאחסון, ששניהם רשומים על שמכם.'),
    ('מה אם אצטרך עוד עמודים בהמשך?', 'אפשר להוסיף עמודים גם אחרי שהאתר עלה. עמוד נוסף מעבר למסלול עולה 350 ₪.'),
]

# ---------- מדריך 2: מה להכין ----------
prep_body = '''
<p>לא חייבים להכין כלום כדי להתחיל לדבר. אבל כל דבר מהרשימה הזו מקצר את הדרך, חוסך סבבי תיקונים ועוזר להגיע לאתר שבאמת מתאים לכם. אפשר גם <a class="text-link" href="/#prep">לסמן את הרשימה באתר</a>, והיא תצורף לפנייה שלכם.</p>

<h2>1. שניים או שלושה אתרים שאתם אוהבים</h2>
<p>זה הדבר שהכי עוזר. לא חייבים אתרים מהתחום שלכם. כתבו במשפט אחד מה מצא חן בעיניכם: הצבעים? הסדר? התמונות הגדולות? התחושה הנקייה? <b>גם אתר שלא אהבתם עוזר</b>, כי הוא מסמן מה לא לעשות.</p>

<h2>2. מה האתר צריך להשיג</h2>
<p>שיתקשרו אליכם? שישאירו פרטים? שיירשמו לחוג? שיזמינו מוצר? בחרו <b>מטרה ראשית אחת</b>. אתר שמנסה לעשות הכול באותה מידה, בסוף לא עושה כלום טוב.</p>

<h2>3. מי הלקוחות שלכם</h2>
<p>מי מגיע לאתר, ומה הוא רוצה לדעת? טיפ: חשבו על <b>השאלות שהכי שואלים אתכם בטלפון</b>. התשובות להן הן בדרך כלל הכותרות הכי טובות לאתר.</p>

<h2>4. העדפות עיצוב</h2>
<p>רקע <b>כהה או בהיר</b>? צבעים שאתם אוהבים, וגם כאלה שלא? סגנון קלאסי, מודרני או חגיגי? אם זה כתוב מראש, העיצובים שתקבלו יהיו קרובים יותר למה שדמיינתם, ולא תצטרכו לעבור על דוגמאות מיותרות. גם "עוד לא יודע" זו תשובה טובה: אז אכין את העיצובים בכמה כיוונים שונים.</p>

<h2>5. לוגו וצבעים</h2>
<p>אם יש לוגו, שלחו אותו באיכות הכי טובה שיש (רצוי קובץ מקורי מהמעצב). אין עדיין לוגו? זה בסדר גמור. אפשר להתחיל עם שם העסק בפונט יפה, ולהוסיף לוגו בהמשך.</p>

<h2>6. תמונות אמיתיות</h2>
<p>תמונות של העבודה, המוצרים, המקום או הצוות. <b>צילום טוב בטלפון, באור יום, עדיף על תמונה ממאגר</b>, כי לקוחות מרגישים מיד מה אמיתי. אם אין תמונות, אפשר להתחיל עם מעט ולהוסיף.</p>

<h2>7. התוכן, בנקודות</h2>
<p>מה אתם מציעים, כמה זה עולה (אם רוצים לפרסם), שעות פעילות, אזור שירות ושאלות נפוצות. <b>מספיק לכתוב בנקודות</b>. את הניסוח הסופי עושים יחד.</p>

<h2>8. כתובת לאתר, אם יש</h2>
<p>אם כבר יש לכם דומיין, ספרו איפה הוא רשום. אין עדיין? אעזור לבחור שם קצר, קל לאיות בטלפון וקל לזכור.</p>

<h2>9. מועד ותקציב</h2>
<p>יש תאריך שהאתר חייב להיות באוויר? פתיחה, עונה, אירוע? כתבו אותו, ונתכנן אחורה ממנו. וגם טווח תקציב בערך, כדי שאציע את המסלול הנכון ולא יותר ממה שצריך.</p>

<h2>איך לפנות כדי לקבל בדיוק מה שצריך</h2>
<ul>
<li><b>משפט אחד על העסק</b>: מה אתם עושים ולמי, כאילו אתם מסבירים לשכן.</li>
<li><b>קישורים לאתרים שאהבתם</b>: יש לזה שדה בטופס.</li>
<li><b>מה לא מתאים לכם</b>: צבע, סגנון או משהו שלא מתאים לקהל שלכם. זה חוסך סבב תיקונים.</li>
<li><b>לא יודעים משהו?</b> זה בסדר. בשביל זה יש שיחה, ואני אשאל את השאלות.</li>
</ul>
'''
prep_faq = [
    ('חייבים להכין את כל הרשימה לפני שפונים?', 'לא. אפשר לפנות גם בלי שום הכנה. כל פריט מהרשימה רק מקצר את הדרך.'),
    ('אין לי לוגו. אפשר לבנות אתר?', 'כן. אפשר להתחיל עם שם העסק בפונט מתאים, ולהוסיף לוגו בהמשך.'),
    ('מי כותב את הטקסטים לאתר?', 'אתם שולחים את המידע בנקודות, ואת הניסוח הסופי עושים יחד.'),
]

# ---------- מדריך 3: דף נחיתה או אתר ----------
lp_body = '''
<p>זו אחת השאלות הראשונות שבעלי עסקים שואלים. התשובה תלויה בשאלה פשוטה: <b>כמה דברים שונים אתם רוצים שהלקוח יעשה, וכמה תוכן יש לכם להגיד?</b></p>

<h2>מה זה דף נחיתה</h2>
<p>עמוד אחד, ארוך וממוקד, שמוביל לפעולה אחת: להתקשר, להשאיר פרטים או להירשם. אין בו תפריט עם הרבה עמודים, ואין בו הסחות.</p>
<h3>דף נחיתה מתאים כש:</h3>
<ul>
<li>יש לכם <b>שירות אחד מרכזי</b> או מבצע מסוים.</li>
<li>אתם רוצים <b>להתחיל מהר</b> (אצלי עד 4 ימי עבודה) ובתקציב נמוך (מ־1,900 ₪).</li>
<li>אתם מפרסמים בפרסומת או בפלאייר, ורוצים שכל מי שמגיע יעשה דבר אחד.</li>
</ul>

<h2>מה זה אתר מלא</h2>
<p>כמה עמודים, עם תפריט: בית, שירותים, אודות, שאלות, יצירת קשר, ולפעמים גם מדור תוכן. באתר עם אזור ניהול אתם מעדכנים לבד את מה שמשתנה.</p>
<h3>אתר מלא מתאים כש:</h3>
<ul>
<li>יש לכם <b>כמה שירותים</b>, וכל אחד צריך הסבר משלו.</li>
<li>אתם רוצים <b>להופיע בגוגל</b> על כמה חיפושים שונים. לכל עמוד יש סיכוי משלו להופיע.</li>
<li>יש תוכן שמתעדכן: חדשות, לוח זמנים, מבצעים או פרויקטים.</li>
</ul>

<h2>ואם צריך הרשמה או מכירה?</h2>
<p>חוגים, מוסדות וארגונים שמקבלים הרשמות צריכים <b>אתר עם הרשמה ורשימת תפוצה</b>: טופס הרשמה, חשבונות ומיילים אוטומטיים. ומי שמוכר מוצרים צריך <b>חנות אונליין</b> עם עגלה, תשלום באשראי ומשלוחים.</p>

<h2>השוואה מהירה</h2>
<div class="g-scroll"><table class="g-table">
<thead><tr><th></th><th>דף נחיתה</th><th>אתר עם אזור ניהול</th></tr></thead>
<tbody>
<tr><th>מחיר אצלי</th><td>מ־1,900 ₪</td><td>מ־3,900 ₪</td></tr>
<tr><th>עמודים</th><td>עמוד אחד, עד 7 חלקים</td><td>עד 6 עמודים</td></tr>
<tr><th>זמן אספקה</th><td>עד 4 ימי עבודה</td><td>עד 10 ימי עבודה</td></tr>
<tr><th>עדכון לבד</th><td>שינויים קטנים עליי</td><td>אתם מעדכנים את התוכן שמשתנה</td></tr>
<tr><th>מתאים ל־</th><td>שירות אחד, מבצע, התחלה מהירה</td><td>כמה שירותים, תוכן ונוכחות בגוגל</td></tr>
</tbody></table></div>

<h2>הטיפ שלי</h2>
<p>לא בטוחים? <b>אפשר להתחיל בדף נחיתה ולהרחיב לאתר מלא בהמשך</b>, בלי לזרוק את מה שנבנה. ואם עדיין לא ברור, במחשבון באתר עונים על 3 שאלות ומקבלים המלצה על הסוג המתאים.</p>
'''
lp_faq = [
    ('אפשר להתחיל בדף נחיתה ולהגדיל אחר כך?', 'כן. דף הנחיתה הופך לעמוד הבית או לאחד העמודים של האתר המלא, ולא בונים מאפס.'),
    ('דף נחיתה יופיע בגוגל?', 'כן, אבל בדרך כלל על פחות חיפושים מאתר מלא, כי יש בו פחות תוכן ופחות עמודים.'),
    ('מה ההבדל במחיר?', 'אצלי דף נחיתה מתחיל ב־1,900 ₪ ואתר עם אזור ניהול ב־3,900 ₪. המחירים סופיים, בלי מע״מ.'),
]

G = [
    ('website-cost', 'כמה עולה לבנות אתר לעסק? מחירים ומה משפיע עליהם',
     'כמה עולה אתר לעסק בישראל: מחירים של דף נחיתה, אתר עם אזור ניהול, אתר עם הרשמה וחנות, מה מייקר אתר ומה העלויות הקבועות.',
     'כמה עולה לבנות אתר לעסק?', 'מחירים אמיתיים, מה משפיע עליהם, ואיך לא לשלם על אתר שלא אהבתם.', cost_body, cost_faq),
    ('before-you-build', 'מה להכין לפני שבונים אתר: 9 דברים שחוסכים זמן וכסף',
     'רשימה של 9 דברים שכדאי להכין לפני שבונים אתר: אתרים שאוהבים, מטרה, קהל, רקע כהה או בהיר, לוגו, תמונות, תוכן, דומיין, מועד ותקציב. ואיך לפנות נכון.',
     'מה להכין לפני שבונים אתר', '9 דברים שמקצרים את הדרך לאתר שמתאים לכם בדיוק. לא חובה, אבל כל אחד עוזר.', prep_body, prep_faq),
    ('landing-or-website', 'דף נחיתה או אתר מלא: מה מתאים לעסק שלכם?',
     'ההבדל בין דף נחיתה לאתר מלא: למי מתאים כל אחד, מחירים, זמני אספקה והשוואה מהירה. ומתי צריך אתר עם הרשמה או חנות.',
     'דף נחיתה או אתר מלא?', 'מה ההבדל, למי מתאים כל אחד, ואיך מחליטים בלי לשלם על מה שלא צריך.', lp_body, lp_faq),
]

for slug, title, desc, h1, lead, body, faq in G:
    related = [(s, h) for s, _, _, h, *_ in G if s != slug]
    open(f'{root}/guides/{slug}.html', 'w').write(page(slug, title + ' | YYG', desc, h1, lead, body, faq, related))

cards = '\n'.join(f'<a class="g-card" href="/guides/{s}.html"><b>{h}</b><span>{l}</span></a>' for s, _, _, h, l, *_ in G)
open(f'{root}/guides/index.html', 'w').write(page('index', 'מדריכים לבניית אתר לעסק | YYG',
    'מדריכים קצרים לבעלי עסקים ומוסדות: כמה עולה אתר, מה להכין לפני שבונים, ודף נחיתה או אתר מלא.',
    'מדריכים לבניית אתר', 'תשובות קצרות וברורות לשאלות שבעלי עסקים שואלים לפני שבונים אתר.',
    f'<div class="g-cards">\n{cards}\n</div>'))

# מפת האתר
urls = [('', '1.0'), ('guides/', '0.6')] + [(f'guides/{s}.html', '0.7') for s, *_ in G] + [('accessibility.html', '0.3')]
open(f'{root}/sitemap.xml', 'w').write('<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' +
    ''.join(f'  <url><loc>{SITE}/{u}</loc><lastmod>{TODAY}</lastmod><priority>{p}</priority></url>\n' for u, p in urls) + '</urlset>\n')
print('ok', [s for s, *_ in G])
