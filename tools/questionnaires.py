#!/usr/bin/env python3
"""YYG: בונה את דפי השאלונים (אפיון ומשוב על עיצובים), בעברית ובאנגלית.
התוכן של השאלות נמצא ב־assets/q-defs.js, והמנוע ב־assets/forms.js. הרצה: python3 tools/questionnaires.py"""
import pathlib
ROOT = pathlib.Path(__file__).resolve().parent.parent
CSS_V, JS_V = "37", "2"
PAGES = {
 ('intake', 'he'): dict(path='questionnaire/index.html', alt='en/questionnaire/', title='שאלון אפיון לבניית אתר: מה חשוב לדעת לפני שמתחילים | YYG',
   desc='שאלון אפיון מלא לבניית אתר לעסק: העסק, הלקוחות, מטרת האתר, עמודים ויכולות, תוכן, עיצוב, זמנים ותקציב. כ־10 דקות, והתשובות נשמרות תוך כדי.',
   h1='שאלון אפיון לאתר', lead='כל מה שחשוב לי לדעת כדי לבנות לכם אתר שמביא לקוחות: על העסק, על הלקוחות, ועל מה שצריך לקרות באתר. ממלאים בכ־10 דקות, ומקבלים הצעת מחיר מסודרת.'),
 ('intake', 'en'): dict(path='en/questionnaire/index.html', alt='questionnaire/', title='Website discovery questionnaire: what to know first | YYG',
   desc='A complete discovery questionnaire for a business website: the business, customers, goals, pages and features, content, design, timing and budget. About 10 minutes.',
   h1='Website discovery questionnaire', lead='Everything I need to know to build you a website that brings in customers: your business, your customers, and what should happen on the site. About 10 minutes, then you get a formal quote.'),
 ('feedback', 'he'): dict(path='feedback/index.html', alt='en/feedback/', title='שאלון משוב על עיצובי האתר | YYG',
   desc='אחרי שמקבלים 3 עיצובים של האתר: בוחרים כיוון, מסמנים מה לקחת מכל עיצוב, ומדייקים צבעים, תחושה ותוכן לסבב הבא. כ־5 דקות.',
   h1='משוב על העיצובים', lead='אחרי שקיבלתם 3 עיצובים של האתר, השאלון הזה עוזר לבחור כיוון ולדייק את הסבב הבא. ככל שהמשוב מדויק יותר, העיצוב הבא קרוב יותר למה שאתם רוצים.'),
 ('feedback', 'en'): dict(path='en/feedback/index.html', alt='feedback/', title='Website design feedback form | YYG',
   desc='After you receive 3 designs of your site: choose a direction, mark what to keep from each design, and fine-tune colors, feel and content for the next round. About 5 minutes.',
   h1='Design feedback', lead='Once you\'ve received 3 designs of your site, this form helps you choose a direction and fine-tune the next round. The more precise your feedback, the closer the next design gets.'),
}
for (form, lang), p in PAGES.items():
    he = lang == 'he'
    url = 'https://yygweb.com/' + p['path'].replace('index.html', '')
    alt = 'https://yygweb.com/' + p['alt']
    he_url, en_url = (url, alt) if he else (alt, url)
    other = ('<a class="text-link" href="/' + p['alt'] + '" hreflang="en" lang="en">English</a>') if he else ('<a class="text-link" href="/' + p['alt'] + '" hreflang="he" lang="he">עברית</a>')
    foot = ('<a class="foot-link" href="/">עמוד הבית</a> · <a class="foot-link" href="/services/">שירותים</a> · <a class="foot-link" href="/guides/">מדריכים</a> · <a class="foot-link" href="/questionnaire/">שאלון אפיון</a> · <a class="foot-link" href="/feedback/">משוב על עיצובים</a> · <a class="foot-link" href="/privacy.html">מדיניות פרטיות</a> · <a class="foot-link" href="/accessibility.html">הצהרת נגישות</a>') if he else \
           ('<a class="foot-link" href="/en/">Home</a> · <a class="foot-link" href="/en/services/">Services</a> · <a class="foot-link" href="/en/guides/">Guides</a> · <a class="foot-link" href="/en/questionnaire/">Discovery questionnaire</a> · <a class="foot-link" href="/en/feedback/">Design feedback</a> · <a class="foot-link" href="/en/privacy.html">Privacy policy</a> · <a class="foot-link" href="/en/accessibility.html">Accessibility statement</a>')
    noscript = 'כדי למלא את השאלון צריך לאפשר JavaScript בדפדפן. אפשר גם להתקשר ל־<a class="text-link" href="tel:0548540700">054-854-0700</a>, ונעבור על השאלות יחד.' if he else \
               'This questionnaire needs JavaScript. You can also call <a class="text-link" href="tel:0548540700">054-854-0700</a>, and we\'ll go over the questions together.'
    talk = 'מעדיפים לענות בשיחה? <a class="text-link" href="tel:0548540700">054-854-0700</a>, ימים א׳ עד ד׳, בין 10:00 ל־13:30.' if he else 'Prefer to answer on a call? <a class="text-link" href="tel:0548540700">054-854-0700</a>, Sunday to Wednesday, 10:00 to 13:30.'
    html = f'''<!doctype html>
<html lang="{lang}" dir="{'rtl' if he else 'ltr'}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<script>if(location.protocol==="http:"&&location.hostname==="yygweb.com")location.replace("https://yygweb.com"+location.pathname+location.search+location.hash)</script>
<link rel="canonical" href="{url}">
<link rel="alternate" hreflang="he" href="{he_url}">
<link rel="alternate" hreflang="en" href="{en_url}">
<link rel="alternate" hreflang="x-default" href="{he_url}">
<meta name="description" content="{p['desc']}">
<title>{p['title']}</title>
<link rel="icon" href="/assets/img/icon-64.png" type="image/png">
<link rel="stylesheet" href="/assets/site.css?v={CSS_V}">
</head>
<body>
<main class="qf-page">
 <div class="wrap">
  <div class="qf-top"><a href="{'/' if he else '/en/'}"><img class="qf-logo" src="/assets/img/logo-160.webp" width="160" height="118" alt="{'YYG, חזרה לעמוד הבית' if he else 'YYG, back to the home page'}"></a>{other}</div>
  <h1 class="qf-h1">{p['h1']}</h1>
  <p class="qf-lead">{p['lead']}</p>
  <div id="qf" data-form="{form}"><noscript><p class="qf-intro">{noscript}</p></noscript></div>
  <p class="qf-talk">{talk}</p>
 </div>
</main>
<footer class="foot">
 <div class="wrap foot-in">
  <p class="foot-brand"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="" loading="lazy"> {'YYG, יהודה גורדון. עיצוב ובניית אתרים' if he else 'YYG, Yehuda Gordon. Web design and development'}</p>
  <p>{foot}</p>
 </div>
</footer>
<script src="/assets/q-defs.js?v={JS_V}"></script>
<script src="/assets/forms.js?v={JS_V}"></script>
<script>if(location.hostname==="yygweb.com"&&navigator.sendBeacon){{try{{navigator.sendBeacon("/hit.php",new Blob([JSON.stringify({{e:"view",p:location.pathname,r:document.referrer,s:new URLSearchParams(location.search).get("src")||""}})],{{type:"application/json"}}))}}catch(e){{}}}}</script>
</body>
</html>
'''
    out = ROOT / p['path']
    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_text(html, encoding='utf-8')
    print('wrote', p['path'])
