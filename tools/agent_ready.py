#!/usr/bin/env python3
"""YYG Web Design: עמודים וקבצים שעוזרים לסוכני בינה מלאכותית להבין את האתר.
- /pricing/, /about/, /contact/ (ואותם באנגלית תחת /en/): עמודי HTML רגילים, בלי צורך ב־JavaScript
- גרסת Markdown לכל עמוד (index.md), שהשרת מגיש כשסוכן מבקש Accept: text/markdown (ראו .htaccess)
- כרטיס שרת MCP: /.well-known/mcp/server-card.json (וגם /.well-known/mcp.json)
המקור לכל המחירים והתהליך: assets/offer.json. המייל לא מופיע באף קובץ כאן, בכוונה (הגנה מספאם).
הרצה: python3 tools/agent_ready.py"""
import html, json, pathlib, re

ROOT = pathlib.Path(__file__).resolve().parent.parent
SITE = 'https://yygweb.com'
O = json.loads((ROOT / 'assets/offer.json').read_text())
E = lambda s: html.escape(str(s), quote=True)
nis = lambda n: f'{n:,} ₪'
CSS_V = re.search(r'site\.css\?v=(\d+)', (ROOT / 'index.html').read_text()).group(1)

STYLE = '''<style>
 .g-top{border-bottom:1px solid var(--line);background:var(--base)}
 .g-top .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:72px}
 .g-top img{height:48px;width:auto}
 .g-top nav{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
 .g-main{padding:clamp(36px,6vw,72px) 0 clamp(56px,8vw,104px)}
 .g-main .wrap{max-width:820px}
 .g-crumb{font-size:.92rem;color:var(--muted)}
 .g-crumb a{color:var(--muted)}
 .g-main h1{margin-top:12px;font-family:var(--display);font-weight:700;font-size:clamp(2.3rem,1.6rem + 2.6vw,3.6rem);line-height:1}
 .g-lead{margin-top:16px;font-size:var(--step-1);color:#D7DDE6;line-height:1.6}
 .g-main h2{margin-top:40px;font-size:var(--step-2);line-height:1.25}
 .g-main p,.g-main li{margin-top:12px;color:#C9D1DD;line-height:1.75}
 .g-main ul{padding-inline-start:24px;list-style:disc}
 .g-main ol{padding-inline-start:24px;list-style:decimal}
 .g-main b{color:#fff}
 .g-table{width:100%;margin-top:16px;border-collapse:collapse;font-size:.98rem}
 .g-table th,.g-table td{padding:12px;border-bottom:1px solid var(--line);text-align:start;vertical-align:top}
 .g-table th{color:#fff;background:var(--panel)}
 .g-table td.p{white-space:nowrap;font-weight:700;color:#fff}
 .g-table td:nth-child(3){white-space:nowrap}
 .g-scroll{overflow-x:auto}
 .g-cta{margin-top:36px;padding:clamp(20px,3vw,28px);border:1px solid var(--line-2);border-radius:16px;background:var(--panel)}
 .g-cta h2{margin-top:0}
 .g-cta .acts{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
 .c-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr));gap:14px;margin-top:20px}
 .c-card{padding:18px 20px;border:1px solid var(--line);border-radius:14px;background:var(--panel)}
 .c-card small{display:block;color:var(--gold);font-weight:700;font-size:.85rem}
 .c-card p{margin-top:6px}
 .c-form{display:grid;gap:14px;margin-top:20px;padding:clamp(18px,3vw,26px);border:1px solid var(--line-2);border-radius:16px;background:var(--panel)}
 .c-form textarea{min-height:120px}
 .c-form .row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
 @media (max-width:560px){.c-form .row{grid-template-columns:1fr}}
 .hp{position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;clip:rect(0 0 0 0);pointer-events:none}
</style>'''

def page(lang, slug, title, desc, h1, lead, body, ld, crumb):
    he = lang == 'he'
    base = '/' if he else '/en/'
    url = f'{SITE}{base}{slug}/'
    he_url, en_url = f'{SITE}/{slug}/', f'{SITE}/en/{slug}/'
    other = f'<a class="text-link" href="{"/en/" if he else "/"}{slug}/" hreflang="{"en" if he else "he"}" lang="{"en" if he else "he"}">{"English" if he else "עברית"}</a>'
    foot = ' · '.join(f'<a class="foot-link" href="{base}{h}">{t}</a>' for h, t in (
        [('', 'עמוד הבית'), ('about/', 'אודות'), ('pricing/', 'מחירון'), ('contact/', 'יצירת קשר'), ('services/', 'שירותים'), ('guides/', 'מדריכים'), ('demo/', 'פרויקטים לדוגמה'), ('privacy.html', 'מדיניות פרטיות'), ('accessibility.html', 'הצהרת נגישות')] if he else
        [('', 'Home'), ('about/', 'About'), ('pricing/', 'Pricing'), ('contact/', 'Contact'), ('services/', 'Services'), ('guides/', 'Guides'), ('demo/', 'Example projects'), ('privacy.html', 'Privacy policy'), ('accessibility.html', 'Accessibility statement')]))
    return f'''<!doctype html>
<html lang="{lang}" dir="{'rtl' if he else 'ltr'}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<script>if(location.protocol==="http:"&&location.hostname==="yygweb.com")location.replace("https://yygweb.com"+location.pathname+location.search+location.hash)</script>
<title>{E(title)}</title>
<meta name="description" content="{E(desc)}">
<link rel="canonical" href="{url}">
<link rel="alternate" hreflang="he" href="{he_url}">
<link rel="alternate" hreflang="en" href="{en_url}">
<link rel="alternate" hreflang="x-default" href="{he_url}">
<link rel="alternate" type="text/markdown" href="{base}{slug}/index.md">
<meta property="og:type" content="website">
<meta property="og:locale" content="{'he_IL' if he else 'en_US'}">
<meta property="og:url" content="{url}">
<meta property="og:title" content="{E(h1)}">
<meta property="og:description" content="{E(desc)}">
<meta property="og:image" content="{SITE}/assets/img/og.jpg">
<meta name="theme-color" content="#151920">
<link rel="icon" href="/assets/img/icon-64.png" type="image/png">
<link rel="preload" href="/assets/fonts/karantina-700-hebrew.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/site.css?v={CSS_V}">
{STYLE}
<script type="application/ld+json">{json.dumps(ld, ensure_ascii=False, separators=(',', ':'))}</script>
</head>
<body>
<a class="skip" href="#main">{'דילוג לתוכן' if he else 'Skip to content'}</a>
<header class="g-top">
 <div class="wrap">
  <a href="{base}"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="{'YYG Web Design, לעמוד הבית' if he else 'YYG Web Design, to the home page'}"></a>
  <nav aria-label="{'ניווט' if he else 'Navigation'}">
   <a class="text-link" href="{base}pricing/">{'מחירון' if he else 'Pricing'}</a>
   <a class="text-link" href="{base}about/">{'אודות' if he else 'About'}</a>
   <a class="text-link" href="{base}contact/">{'יצירת קשר' if he else 'Contact'}</a>
   {other}
  </nav>
 </div>
</header>
<main id="main" class="g-main">
 <div class="wrap">
  <p class="g-crumb"><a href="{base}">YYG Web Design</a> › {E(crumb)}</p>
  <h1>{E(h1)}</h1>
  <p class="g-lead">{E(lead)}</p>
{body}
 </div>
</main>
<footer class="foot">
 <div class="wrap foot-in">
  <p class="foot-brand"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="" loading="lazy"> {'YYG Web Design, יהודה גורדון. עיצוב ובניית אתרים' if he else 'YYG Web Design, Yehuda Gordon. Web design and development'}</p>
  <p>{foot}</p>
 </div>
</footer>
<script>if(location.hostname==="yygweb.com"&&navigator.sendBeacon){{try{{var q=new URLSearchParams(location.search);navigator.sendBeacon("/hit.php",new Blob([JSON.stringify({{e:"view",p:location.pathname,r:document.referrer,s:q.get("src")||""}})],{{type:"application/json"}}))}}catch(e){{}}}}</script>
</body>
</html>
'''

ORG = {'@type': 'Organization', '@id': f'{SITE}/#organization', 'name': 'YYG Web Design', 'url': f'{SITE}/', 'logo': f'{SITE}/assets/img/logo-320.webp'}

def crumbs(lang, slug, name):
    base = '/' if lang == 'he' else '/en/'
    return {'@context': 'https://schema.org', '@type': 'BreadcrumbList', 'itemListElement': [
        {'@type': 'ListItem', 'position': 1, 'name': 'YYG Web Design', 'item': f'{SITE}{base}'},
        {'@type': 'ListItem', 'position': 2, 'name': name, 'item': f'{SITE}{base}{slug}/'}]}

def offers_ld(lang):
    base = '/' if lang == 'he' else '/en/'
    items = []
    for p in O['packages']:
        o = {'@type': 'Offer', 'name': p['name'][lang], 'description': p['summary'][lang], 'price': str(p['price']), 'priceCurrency': 'ILS', 'url': f'{SITE}{base}pricing/', 'itemOffered': {'@type': 'Service', 'name': p['name'][lang], 'provider': {'@id': ORG['@id']}}}
        if p.get('from'): o['priceSpecification'] = {'@type': 'PriceSpecification', 'minPrice': str(p['price']), 'priceCurrency': 'ILS', 'valueAddedTaxIncluded': True}
        items.append(o)
    for a in O['addons']:
        items.append({'@type': 'Offer', 'name': a['name'][lang], 'price': str(a['price']), 'priceCurrency': 'ILS', 'url': f'{SITE}{base}pricing/', 'itemOffered': {'@type': 'Service', 'name': a['name'][lang]}})
    for r in O['recurring']:
        items.append({'@type': 'Offer', 'name': r['name'][lang], 'price': str(r['price']), 'priceCurrency': 'ILS', 'url': f'{SITE}{base}pricing/',
                      'priceSpecification': {'@type': 'UnitPriceSpecification', 'price': str(r['price']), 'priceCurrency': 'ILS', 'unitCode': 'ANN' if r['per'] == 'year' else 'MON'}})
    return {'@context': 'https://schema.org', '@type': 'OfferCatalog', 'name': 'מחירון YYG Web Design' if lang == 'he' else 'YYG Web Design pricing', 'url': f'{SITE}{base}pricing/', 'itemListElement': items, 'offeredBy': ORG}

# ---------- עמוד המחירון ----------
def pricing(lang):
    he = lang == 'he'; base = '/' if he else '/en/'
    days = lambda d: (f'עד {d} ימי עבודה' if he else f'up to {d} business days')
    rows = ''.join(f'<tr><td><b>{E(p["name"][lang])}</b><br>{E(p["summary"][lang])}</td><td class="p">{("מ־" if he else "from ") if p.get("from") else ""}{nis(p["price"])}</td><td>{E(p["days_text"][lang]) if p.get("days_text") else days(p["days"])}</td></tr>' for p in O['packages'])
    adds = ''.join(f'<li>{E(a["name"][lang])}: <b>{("מ־" if he else "from ") if a.get("from") else ""}{nis(a["price"])}</b></li>' for a in O['addons'])
    rec = ''.join(f'<li>{E(r["name"][lang])}: <b>{nis(r["price"])} {("לשנה" if he else "a year") if r["per"] == "year" else ("לחודש" if he else "a month")}</b></li>' for r in O['recurring'])
    body = f'''  <div class="g-scroll"><table class="g-table">
   <thead><tr><th>{'מסלול' if he else 'Package'}</th><th>{'מחיר' if he else 'Price'}</th><th>{'זמן אספקה' if he else 'Delivery'}</th></tr></thead>
   <tbody>{rows}</tbody></table></div>
  <p>{E(O['vat_note'][lang])} {E(O['included_everywhere'][lang])}</p>
  <h2>{'תוספות' if he else 'Add-ons'}</h2>
  <ul>{adds}</ul>
  <h2>{'תשלומים קבועים' if he else 'Recurring'}</h2>
  <ul>{rec}</ul>
  <h2>{'איך משלמים' if he else 'How payment works'}</h2>
  <p>{E(O['payment'][lang])}</p>
  <p><b>{'מחיר השקה' if he else 'Launch price'}:</b> {E(O['launch_offer'][lang])}</p>
  <section class="g-cta" aria-labelledby="cta-t">
   <h2 id="cta-t">{'כמה יעלה האתר שלכם?' if he else 'What will your site cost?'}</h2>
   <p>{'המחירים כאן הם מחירי התחלה. המחיר הסופי נקבע לפי מה שאתם צריכים, ומגיע בהצעה מסודרת לפני שמתחילים.' if he else 'These are starting prices. The final price depends on what you need, and comes in a formal quote before we start.'}</p>
   <div class="acts"><a class="btn btn-gold" href="{base}#plan">{'למחשבון המחיר' if he else 'To the price calculator'}</a><a class="btn btn-line" href="{base}questionnaire/">{'לשאלון האפיון' if he else 'To the questionnaire'}</a></div>
  </section>'''
    t = 'מחירון: כמה עולה דף נחיתה, אתר וחנות | YYG Web Design' if he else 'Pricing: landing pages, websites and stores | YYG Web Design'
    d = 'המחירים של YYG Web Design: דף נחיתה מ־2,000 ₪, אתר עם אזור ניהול מ־4,000 ₪, אתר עם הרשמה מ־6,000 ₪, חנות מ־7,000 ₪, תוספות ותשלומים קבועים. בלי מע״מ.' if he else 'YYG Web Design prices: landing page from 2,000, website with an admin area from 4,000, website with sign-ups from 6,000, online store from 7,000 (ILS), add-ons and recurring. No VAT added.'
    h = 'מחירון' if he else 'Pricing'
    lead = 'כל המסלולים, התוספות והתשלומים הקבועים, במקום אחד. המחירים בשקלים, סופיים, ולא מוסיפים עליהם מע״מ.' if he else 'Every package, add-on and recurring cost in one place. Prices are in shekels (ILS), final, and no VAT is added.'
    ld = [offers_ld(lang), crumbs(lang, 'pricing', h)]
    return page(lang, 'pricing', t, d, h, lead, body, ld, h), md_pricing(lang)

def md_pricing(lang):
    he = lang == 'he'
    L = [f'# {"מחירון YYG Web Design" if he else "YYG Web Design pricing"}', '', O['vat_note'][lang] + ' ' + O['included_everywhere'][lang], '', f'## {"מסלולים" if he else "Packages"}', '']
    for p in O['packages']:
        L.append(f'- **{p["name"][lang]}**: {("מ־" if he else "from ") if p.get("from") else ""}{nis(p["price"])}, {p["days_text"][lang] if p.get("days_text") else (("עד " + str(p["days"]) + " ימי עבודה") if he else ("up to " + str(p["days"]) + " business days"))}. {p["summary"][lang]}')
    L += ['', f'## {"תוספות" if he else "Add-ons"}', ''] + [f'- {a["name"][lang]}: {("מ־" if he else "from ") if a.get("from") else ""}{nis(a["price"])}' for a in O['addons']]
    L += ['', f'## {"תשלומים קבועים" if he else "Recurring"}', ''] + [f'- {r["name"][lang]}: {nis(r["price"])} {("לשנה" if he else "a year") if r["per"] == "year" else ("לחודש" if he else "a month")}' for r in O['recurring']]
    L += ['', f'## {"איך משלמים" if he else "How payment works"}', '', O['payment'][lang], '', O['launch_offer'][lang], '']
    return '\n'.join(L)

# ---------- אודות ----------
ABOUT = {
 'he': dict(lead='נעים מאוד, יהודה גורדון. אני מעצב ובונה אתרים לעסקים קטנים ולמוסדות. את כל העבודה, מהשרטוט ועד העלייה לאוויר, אני עושה בעצמי, כך שאתם מדברים כל הזמן עם אותו אדם.',
   parts=[('איך אני עובד', ['מתחילים בשאלון קצר או בשיחה, ומקבלים הצעת מחיר מסודרת וסגורה.', 'משלמים 10% ומקבלים 3 עיצובים מלאים של האתר שלכם, עם התוכן שלכם. בוחרים בעיניים, לא מתוך דמיון.', 'בוחרים עיצוב, משלמים עוד 40%, ואני בונה. את 50% האחרונים משלמים רק אחרי שאישרתם שהכול טוב.', 'המחירים סופיים, ולא מוסיפים עליהם מע״מ.']),
          ('מה חשוב לי באתר', ['שיביא לקוחות: ברור מה אתם עושים, ומה הצעד הבא של הגולש.', 'שייטען מהר, גם בטלפון ובקליטה חלשה.', 'שיהיה נגיש, מוכן לגוגל ולכלי בינה מלאכותית, ושתוכלו לעדכן את מה שמשתנה.']),
          ('עבודות', ['הכיף שבאפיה (hakef.com): אתר לקייטרינג בוטיק ובר פטיפורים בירושלים, אחרי 6 כיווני עיצוב לבחירה.', 'פרחי שמיים (פרחישמים.com): אתר למקהלת ילדים וחוגי פיתוח קול, עם חיפוש חוג לפי גיל ועיר, הרשמה, חשבונות ועדכונים.', 'פרויקטים לדוגמה מלאים: אתר לנגריה, דף נחיתה לחנות משקפיים עם עדכון שעות עצמי, וחנות אונליין לתבלינים ותה.'])],
   links=[('/demo/', 'לפרויקטים לדוגמה'), ('/pricing/', 'למחירון'), ('/contact/', 'ליצירת קשר')]),
 'en': dict(lead="Nice to meet you, I'm Yehuda Gordon. I design and build websites for small businesses and institutions. I do all the work myself, from the first sketch to launch, so you always talk to the same person.",
   parts=[('How I work', ['We start with a short questionnaire or a call, and you get a formal, fixed quote.', 'You pay 10% and get 3 complete designs of your own site, with your content. You choose with your eyes, not your imagination.', 'You pick a design, pay another 40%, and I build. You pay the final 50% only after you confirm everything is right.', 'Prices are final, and no VAT is added.']),
          ('What matters to me in a website', ['That it brings in customers: clear about what you do, and clear about the next step.', 'That it loads fast, even on a phone with a weak signal.', 'That it is accessible, ready for Google and AI tools, and that you can update what changes.']),
          ('Work', ['HaKef SheBaAfiya (hakef.com): a website for a boutique catering and petit-four bar business in Jerusalem, after 6 design directions to choose from.', 'Pirchei Shomayim: a website for a boys\' choir and voice classes, with a class search by age and city, sign-ups, accounts and updates.', 'Complete example projects: a carpentry website, an eyewear shop landing page with self-updated hours, and an online spice and tea store.'])],
   links=[('/en/demo/', 'Example projects'), ('/en/pricing/', 'Pricing'), ('/en/contact/', 'Contact')]),
}

def about(lang):
    he = lang == 'he'; A = ABOUT[lang]
    body = ''.join(f'  <h2>{E(h)}</h2>\n  <ul>{"".join(f"<li>{E(x)}</li>" for x in items)}</ul>\n' for h, items in A['parts'])
    body += '  <p><b>' + ('זמינות' if he else 'Availability') + ':</b> ' + E(O['business']['hours'][lang]) + '. ' + ('טלפון לשיחות: ' if he else 'Phone, for calls: ') + f'<a class="text-link" href="tel:0548540700" dir="ltr">{O["business"]["phone"]}</a></p>\n'
    body += '  <p>' + ' · '.join(f'<a class="text-link" href="{h}">{E(t)}</a>' for h, t in A['links']) + '</p>'
    h = 'אודות' if he else 'About'
    t = 'אודות: יהודה גורדון, עיצוב ובניית אתרים | YYG Web Design' if he else 'About: Yehuda Gordon, web design and development | YYG Web Design'
    d = A['lead']
    ld = [{'@context': 'https://schema.org', '@type': 'AboutPage', 'name': h, 'url': f'{SITE}{"/" if he else "/en/"}about/', 'inLanguage': lang, 'about': ORG,
           'mainEntity': {'@type': 'Person', 'name': O['business']['owner'][lang], 'jobTitle': 'מעצב ובונה אתרים' if he else 'Web designer and developer', 'worksFor': ORG, 'url': f'{SITE}/'}}, crumbs(lang, 'about', h)]
    md = '\n'.join([f'# {h}: YYG Web Design', '', A['lead'], ''] + sum([[f'## {hh}', ''] + [f'- {x}' for x in items] + [''] for hh, items in A['parts']], []) +
                   [f'{"זמינות" if he else "Availability"}: {O["business"]["hours"][lang]}. {"טלפון לשיחות" if he else "Phone, for calls"}: {O["business"]["phone"]}', ''] +
                   [f'- [{tt}]({SITE}{hh})' for hh, tt in A['links']] + [''])
    return page(lang, 'about', t, d, h, A['lead'], body, ld, h), md

# ---------- יצירת קשר ----------
def contact(lang):
    he = lang == 'he'; B = O['business']; base = '/' if he else '/en/'
    cards = [(('טלפון, לשיחות בלבד' if he else 'Phone, calls only'), f'<a class="text-link" href="tel:0548540700" dir="ltr">{B["phone"]}</a>'),
             (('מתי' if he else 'When'), E(B['hours'][lang])),
             (('מייל' if he else 'Email'), '<a class="text-link" data-mail href="#">' + ('המייל יופיע כאן' if he else 'The email appears here') + '</a>'),
             (('הדרך המהירה להצעת מחיר' if he else 'The fastest way to a quote'), f'<a class="text-link" href="{base}questionnaire/">' + ('שאלון אפיון, כ־10 דקות' if he else 'The discovery questionnaire, about 10 minutes') + '</a>')]
    cg = ''.join(f'<div class="c-card"><small>{k}</small><p>{v}</p></div>' for k, v in cards)
    lbl = (lambda a, b: a if he else b)
    form = f'''  <h2>{lbl('השאירו פרטים', 'Leave your details')}</h2>
  <p>{lbl('תקבלו מיד במייל את הצעד הבא: שאלון קצר, ואחריו הצעת מחיר מסודרת. בלי שיחות חובה.', "You'll get the next step by email right away: a short questionnaire, then a formal quote. No required calls.")}</p>
  <form class="c-form" method="post" action="/send.php" toolname="contact_yyg" tooldescription="{lbl('שליחת פנייה ליהודה גורדון מ־YYG Web Design לבניית אתר. חובה: שם ומייל.', 'Send an inquiry to Yehuda Gordon at YYG Web Design about building a website. Required: name and email.')}">
   <div class="row"><label class="field"><span>{lbl('שם', 'Name')}</span><input name="name" autocomplete="name" required toolparamdescription="{lbl('שם הפונה', 'Your name')}"></label>
   <label class="field"><span>{lbl('מייל', 'Email')}</span><input name="email" type="email" autocomplete="email" dir="ltr" required toolparamdescription="{lbl('כתובת מייל לחזרה', 'Email address for the reply')}"></label></div>
   <label class="field"><span>{lbl('טלפון', 'Phone')} <small>{lbl('לא חובה', 'Optional')}</small></span><input name="phone" type="tel" autocomplete="tel" dir="ltr" toolparamdescription="{lbl('טלפון, לא חובה', 'Phone, optional')}"></label>
   <label class="field"><span>{lbl('במה אפשר לעזור?', 'How can I help?')}</span><textarea name="notes" rows="4" toolparamdescription="{lbl('מה העסק, ואיזה אתר צריך', 'What the business is, and what kind of site you need')}"></textarea></label>
   <input type="text" name="_honey" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
   <input type="hidden" name="_token" value="">
   <input type="hidden" name="plan" value="{lbl('פנייה מעמוד יצירת קשר', 'Inquiry from the contact page (English site)')}">
   <button class="btn btn-gold" type="submit">{lbl('שליחה', 'Send')}</button>
   <p style="font-size:.9rem;margin:0">{lbl('הפרטים משמשים רק כדי לחזור אליכם.', 'Your details are used only to get back to you.')} <a class="text-link" href="{base}privacy.html">{lbl('מדיניות פרטיות', 'Privacy policy')}</a></p>
  </form>
<script>(function(){{var m=["yehuda","yygweb.com"].join("@");document.querySelectorAll("[data-mail]").forEach(function(a){{a.textContent=m;a.href="mailto:"+m;a.dir="ltr"}});var f=document.querySelector(".c-form");fetch("/send.php?token=1",{{cache:"no-store"}}).then(function(r){{return r.json()}}).then(function(t){{f.elements._token.value=t.token||""}}).catch(function(){{}});}})();</script>'''
    body = f'  <div class="c-grid">{cg}</div>\n{form}'
    h = 'יצירת קשר' if he else 'Contact'
    t = 'יצירת קשר | YYG Web Design, עיצוב ובניית אתרים' if he else 'Contact | YYG Web Design web design and development'
    lead = lbl('משאירים שם ומייל, ומקבלים מיד מייל אישי עם הצעד הבא. אפשר גם למלא ישר את שאלון האפיון, או להתקשר. תשובה אישית תוך יום עבודה.', 'Leave your name and email, and get a personal email with the next step right away. You can also go straight to the discovery questionnaire, or call. A personal reply within one business day.')
    ld = [{'@context': 'https://schema.org', '@type': 'ContactPage', 'name': h, 'url': f'{SITE}{base}contact/', 'inLanguage': lang,
           'about': dict(ORG, contactPoint={'@type': 'ContactPoint', 'telephone': '+972-54-854-0700', 'contactType': 'customer service', 'areaServed': 'IL', 'availableLanguage': ['he', 'en'],
                                           'hoursAvailable': {'@type': 'OpeningHoursSpecification', 'dayOfWeek': ['Sunday', 'Monday', 'Tuesday', 'Wednesday'], 'opens': '10:00', 'closes': '13:30'}})}, crumbs(lang, 'contact', h)]
    md = '\n'.join([f'# {h}: YYG Web Design', '', lead, '', f'- {"טלפון, לשיחות בלבד" if he else "Phone, calls only"}: {B["phone"]}', f'- {"מתי" if he else "When"}: {B["hours"][lang]}',
                    f'- {"טופס פנייה" if he else "Contact form"}: {B["contact_page"][lang]}', f'- {"שאלון אפיון" if he else "Discovery questionnaire"}: {B["questionnaire"][lang]}', ''])
    return page(lang, 'contact', t, lead, h, lead, body, ld, h), md

# ---------- Markdown לעמוד הבית ----------
def md_home(lang):
    he = lang == 'he'; B = O['business']; base = '/' if he else '/en/'
    s = (ROOT / ('index.html' if he else 'en/index.html')).read_text()
    faq = []
    for m in re.finditer(r'<script type="application/ld\+json">(.*?)</script>', s, re.S):
        d = json.loads(m.group(1))
        for x in (d if isinstance(d, list) else [d]):
            if x.get('@type') == 'FAQPage': faq = [(q['name'], q['acceptedAnswer']['text']) for q in x['mainEntity']]
    L = [f'# YYG Web Design: {"עיצוב ובניית אתרים" if he else "Web design and development"}', '', B['description'][lang], '',
         f'{"מי" if he else "Who"}: {B["owner"][lang]}. {"אזור" if he else "Area"}: {B["area"][lang]}.', '',
         f'## {"מסלולים ומחירים" if he else "Packages and prices"}', '']
    L += [f'- **{p["name"][lang]}**: {("מ־" if he else "from ") if p.get("from") else ""}{nis(p["price"])}. {p["summary"][lang]}' for p in O['packages']]
    L += ['', O['vat_note'][lang] + ' ' + O['included_everywhere'][lang], f'{"המחירון המלא" if he else "Full pricing"}: {SITE}{base}pricing/', '',
          f'## {"איך עובדים" if he else "How it works"}', ''] + [f'{i}. {x[lang]}' for i, x in enumerate(O['process'], 1)]
    L += ['', f'## {"איך משלמים" if he else "How payment works"}', '', O['payment'][lang], '', O['launch_offer'][lang], '']
    if faq: L += [f'## {"שאלות נפוצות" if he else "FAQ"}', ''] + sum([[f'### {q}', '', a, ''] for q, a in faq], [])
    L += [f'## {"דוגמאות" if he else "Examples"}', ''] + [f'- [{x["name"][lang]}]({x["url"] if he else x["url"].replace(SITE + "/", SITE + "/en/")})' for x in O['examples']]
    L += ['', f'## {"יצירת קשר" if he else "Contact"}', '', f'- {"טלפון, לשיחות בלבד" if he else "Phone, calls only"}: {B["phone"]} ({B["hours"][lang]})',
          f'- {"טופס פנייה" if he else "Contact form"}: {B["contact_page"][lang]}', f'- {"שאלון אפיון" if he else "Discovery questionnaire"}: {B["questionnaire"][lang]}', '',
          f'## {"עוד" if he else "More"}', '', f'- {"אודות" if he else "About"}: {SITE}{base}about/', f'- {"שירותים" if he else "Services"}: {SITE}{base}services/',
          f'- {"מדריכים" if he else "Guides"}: {SITE}{base}guides/', f'- llms.txt: {SITE}/llms.txt', f'- {"שרת MCP (לסוכנים)" if he else "MCP server (for agents)"}: {SITE}/mcp', '']
    return '\n'.join(L)

# ---------- כרטיס שרת MCP ----------
TOOLS = [
 ('get_packages_and_prices', 'All YYG Web Design packages, add-ons and recurring costs, with prices in ILS (final, no VAT added).'),
 ('get_process_and_payment', 'How a project works step by step, and how payment works (10% / 40% / 50%).'),
 ('estimate_price', 'A rough starting estimate for a website by type, page count, English version and logo.'),
 ('list_examples', 'Live client sites and complete example projects, with links.'),
 ('get_contact', 'How to reach YYG Web Design: phone, hours, the contact form and the discovery questionnaire.'),
]
def server_card():
    return {
     'name': 'com.yygweb/studio', 'title': 'YYG Web Design web design studio', 'version': '1.0.0',
     'description': 'Read-only MCP server for YYG Web Design, a web design and development studio in Israel: packages and prices, process and payment, examples, and contact options.',
     'serverInfo': {'name': 'yygweb', 'title': 'YYG Web Design web design studio', 'version': '1.0.0'},
     'protocolVersion': '2025-06-18',
     'transport': {'type': 'streamable-http', 'endpoint': f'{SITE}/mcp'},
     'endpoint': f'{SITE}/mcp',
     'remotes': [{'type': 'streamable-http', 'url': f'{SITE}/mcp'}],
     'capabilities': {'tools': {'listChanged': False}, 'resources': {}, 'prompts': {}},
     'authentication': {'required': False},
     'tools': [{'name': n, 'description': d} for n, d in TOOLS],
     'documentation': f'{SITE}/llms.txt',
     'homepage': f'{SITE}/',
     'languages': ['he', 'en'],
    }

if __name__ == '__main__':
    out = []
    for lang in ('he', 'en'):
        pre = '' if lang == 'he' else 'en/'
        for name, fn in (('pricing', pricing), ('about', about), ('contact', contact)):
            h, md = fn(lang)
            (ROOT / f'{pre}{name}').mkdir(parents=True, exist_ok=True)
            (ROOT / f'{pre}{name}/index.html').write_text(h); (ROOT / f'{pre}{name}/index.md').write_text(md)
            out += [f'{pre}{name}/index.html', f'{pre}{name}/index.md']
        (ROOT / f'{pre}index.md').write_text(md_home(lang)); out.append(f'{pre}index.md')
    wk = ROOT / '.well-known/mcp'; wk.mkdir(parents=True, exist_ok=True)
    card = json.dumps(server_card(), ensure_ascii=False, indent=1) + '\n'
    (wk / 'server-card.json').write_text(card); (ROOT / '.well-known/mcp.json').write_text(card)
    out += ['.well-known/mcp/server-card.json', '.well-known/mcp.json']
    print(out)
