# YYG: ההגדרות של כל פרויקט לדוגמה. נטען מתוך tools/demo.py (exec), ומשתמש בפונקציות ובקבועים שלו.
# כל פרויקט: קובץ נתונים, תיקייה באתר, 3 עיצובים, טקסטים, הצעת מחיר, ומה מוצג בשלב 1.

def admin_anaf(lang, pfx, n):
    he = lang == 'he'
    pts = (['הוספת עבודה: תמונה מהטלפון, שם, סוג עץ ומשפט קצר', 'עריכה, סידור מחדש ומחיקה של עבודות', 'הודעה קצרה בראש האתר, למשל לפני חג', 'התמונה מוקטנת לבד, כדי שהאתר יישאר מהיר', 'נוח בטלפון, בלי לחכות לאף אחד']
           if he else ['Add a project: a photo from the phone, a name, the wood and one sentence', 'Edit, reorder and delete projects', 'A short notice at the top of the site, e.g. before a holiday', 'Photos are resized automatically, so the site stays fast', 'Easy on the phone, no waiting for anyone'])
    return f'''  <section class="d-step" id="sa" aria-labelledby="sa-t">
   <div class="d-step-h"><span class="d-num" aria-hidden="true">{n}</span><h2 id="sa-t">{E('מסך ניהול התוכן' if he else 'The content admin screen')}</h2></div>
   <p>{E('בגלל זה בחרנו באתר עם אזור ניהול: בעל הנגריה מוסיף עבודות לגלריה בערך פעם בחודש. במסך הזה הוא מוסיף עבודה עם תמונה, משנה טקסט, מסדר ומוחק, ושומר. הגלריה באתר מתעדכנת מיד.' if he else 'This is why we chose a website with an admin area: the owner adds projects to the gallery about once a month. On this screen he adds a project with a photo, edits text, reorders and deletes, and saves. The gallery on the site updates right away.')}</p>
   <div class="d-hours">
    <a href="/{pfx}admin/"><img src="/{pfx}shots/admin-d.webp" width="960" height="660" alt="{E('מסך ניהול התוכן של הנגריה' if he else 'The carpentry content admin screen')}" loading="lazy"></a>
    <div><ul>{''.join(f'<li>{E(x)}</li>' for x in pts)}</ul>
     <a class="btn btn-gold" href="/{pfx}admin/">{E('לנסות את מסך הניהול' if he else 'Try the admin screen')}</a>
     <small>{E('בדוגמה השינוי נשמר רק אצלכם בדפדפן, כדי שכל אחד יוכל לנסות. פתחו את הגרסה הסופית בלשונית אחרת, ותראו אותה מתעדכנת.' if he else 'In the example, changes are saved only in your browser, so anyone can try. Open the final version in another tab and watch it update.')}</small></div>
   </div>
  </section>
'''

ANAF = dict(prefix='demo/', data=DATA, designs=DESIGNS, T=T, quote=QUOTE, hi=hi_anaf, files=files_anaf, extra=None, after=admin_anaf,
            card={'he': ('אתר מלא לנגריה', '3 כיוונים שונים לגמרי, הגרסה הסופית ומסך ניהול לגלריה'), 'en': ('A full website for a carpentry shop', '3 completely different directions, the final version and a gallery admin screen')},
            other={'he': ('/demo/optika/', 'דוגמה נוספת', 'דף נחיתה לחנות משקפיים, עם שעות שבעל העסק מעדכן בעצמו', '3 עיצובים, מסך עדכון השעות, משוב והגרסה הסופית'),
                   'en': ('/en/demo/optika/', 'Another example', 'A landing page for an eyewear shop, with hours the owner updates himself', '3 designs, the hours update screen, feedback and the final version')})

# ---------- צלול: חנות משקפיים, דף נחיתה + עדכון שעות עצמי ----------
DESIGNS_OPT = [
 dict(n=1, he=dict(name='נקי ומדויק', text='עיצוב בהיר ומדויק, כמו מכון אופטיקה טוב: לבן וכחול, המשקפיים בתוך עדשה עגולה עם שנתות, ולוח ראייה קטן שכתוב בו "רואים טוב, נראים טוב".', fonts='רוביק ואסיסטנט'),
      en=dict(name='Clean and precise', text='A bright, precise design, like a good optical clinic: white and blue, the glasses inside a round lens with tick marks, and a little eye chart that reads "see well, look good".', fonts='Rubik and Assistant'),
      colors=['#FFFFFF', '#13202B', '#2F6F8F', '#DDEFEA']),
 dict(n=2, he=dict(name='בוטיק אופנתי', text='עיצוב כהה ודרמטי, כמו בוטיק אופנה: שחור וזהב, כותרות סריף גדולות, והמסגרות מוצגות כמו בקטלוג של מגזין.', fonts='פרנק רוהל ליברה ואסיסטנט'),
      en=dict(name='Fashion boutique', text='A dark, dramatic design, like a fashion boutique: black and gold, large serif headlines, and frames shown like a magazine lookbook.', fonts='Frank Ruhl Libre and Assistant'),
      colors=['#111315', '#C9A36A', '#F4EDE4', '#8D8378']),
 dict(n=3, he=dict(name='צבעוני ושכונתי', text='עיצוב שמח למשפחות: תכלת, צהוב וקורל, מדבקות כמו "פתוח עכשיו" ו"מוכן תוך 3 ימי עבודה", והמסגרות בכרטיסי פולרויד.', fonts='ורלה ראונד ורוביק'),
      en=dict(name='Colorful neighborhood', text='A cheerful design for families: sky blue, yellow and coral, stickers like "open now" and "ready in 3 working days", and frames on polaroid cards.', fonts='Varela Round and Rubik'),
      colors=['#FFF9F0', '#2E8BC0', '#F6C343', '#E8684A']),
]

T_OPT = {lang: dict(T[lang]) for lang in T}
T_OPT['he'].update(
  title='דף נחיתה לדוגמה: חנות משקפיים עם שעות שמתעדכנות | YYG',
  desc='חנות משקפיים דמיונית עוברת את כל התהליך של YYG: שאלון, הצעת מחיר, 3 עיצובים לדף נחיתה, מסך שבו בעל העסק מעדכן בעצמו את שעות הפתיחה, ושאלון משוב.',
  h1='דף נחיתה לחנות משקפיים, עם שעות שבעל העסק מעדכן בעצמו',
  lead='עוד דוגמה מלאה, הפעם דף נחיתה: חנות משקפיים דמיונית בשם "צלול", שבעליה ביקש דבר אחד מעל הכול: לעדכן בעצמו את שעות הפתיחה, מהטלפון. עברנו את כל התהליך כמו עם לקוח אמיתי: שאלון, הצעת מחיר, 3 עיצובים, מסך העדכון, ומשוב.',
  note='עסק דמיוני לדוגמה: השם, הפרטים וההמלצות בדויים. התמונות הן הדמיות תלת־ממד שבניתי במיוחד, וגם הלוגו עוצב לדוגמה.',
  nav=['שאלון אפיון', 'הצעת מחיר', '3 עיצובים', 'עדכון השעות', 'משוב ובחירה', 'הגרסה הסופית'],
  s1p='בעל החנות מילא את השאלון בכ־10 דקות. הלוגו הישן לא שירת אותו, ותמונות לא היו בכלל. אלה התשובות שהכי השפיעו:',
  files='מה הכנתי בשבילו: לוגו חדש והדמיות מוצר',
  s2p='הבונה שלי זיהה מהשאלון דף נחיתה, לוגו חדש, ושני דברים לתמחור: תמונות, ומסך לעדכון השעות. תמחרתי, בדקתי, והלקוח קיבל במייל הצעה מסודרת עם אישור בלחיצה.',
  s3p='אחרי 10% הראשונים מכינים 3 כיוונים שונים לגמרי לאותו דף נחיתה. בכל אחד מהם השעות והסטטוס "פתוח עכשיו" חיים, ומתעדכנים לפי שעון ישראל:',
  s5p='הלקוח בחר בעיצוב 1 ושילם 40%. בניתי את הדף על עיצוב 1, עם מה שביקש מהעיצובים האחרים. משם הלקוח מאשר, משלם את ה־50% האחרונים, והדף באוויר. מאותו יום הוא מעדכן את השעות ואת המבצע לבד.',
  changes=['סטטוס "פתוח עכשיו" גדול בראש הדף, בסגנון המדבקה של עיצוב 3', 'המדבקה "מוכן תוך 3 ימי עבודה" מעיצוב 3', 'תמונת מסגרות הזהב הגדולה, כמו בפתיחה של עיצוב 2, בחלק של המסגרות', 'הערה שבמוצאי שבת פתוח רק בחורף', 'פחות תנועה ואנימציות, כמו שביקש', 'המבצע בדף מתעדכן מאותו מסך של השעות'],
)
T_OPT['en'].update(
  title='Example landing page: an eyewear shop with self-updating hours | YYG',
  desc='A fictional eyewear shop goes through the whole YYG process: a questionnaire, a quote, 3 landing page designs, a screen where the owner updates the opening hours himself, and a feedback form.',
  h1='A landing page for an eyewear shop, with hours the owner updates himself',
  lead='Another complete example, this time a landing page: a fictional eyewear shop called "Tzalul", whose owner asked for one thing above all: to update the opening hours himself, from his phone. We went through the whole process as with a real client: a questionnaire, a quote, 3 designs, the update screen, and feedback.',
  note='A fictional example business: the name, details and testimonials are made up. The images are 3D renders I built specially, and the logo was designed for the example too.',
  nav=['Questionnaire', 'Quote', '3 designs', 'Hours screen', 'Feedback', 'Final version'],
  s1p='The shop owner filled in the questionnaire in about 10 minutes. His old logo wasn\'t working for him, and there were no photos at all. These are the answers that shaped the work most:',
  files='What I prepared for him: a new logo and product renders',
  s2p='From the questionnaire, my builder picked a landing page, a new logo, and two things to price: images, and a screen for updating the hours. I priced and checked it, and the client got a formal quote by email with one-click approval.',
  s3p='After the first 10%, I prepare 3 completely different directions for the same landing page. In each, the hours and the "open now" status are live, by Israel time:',
  s5p='The client chose design 1 and paid 40%. I built the page on design 1, with what he asked for from the other designs. From here he approves it, pays the final 50%, and the page goes live. From that day on he updates the hours and the promotion himself.',
  changes=['A big "open now" status at the top, in the style of the sticker from design 3', 'The "ready in 3 working days" sticker from design 3', 'The big gold-frames shot, like the opening of design 2, in the frames section', 'A note that Saturday night opening is in winter only', 'Less motion and animation, as he asked', 'The promotion on the page is updated from the same screen as the hours'],
)

QUOTE_OPT = {
 'he': dict(items=[('דף נחיתה', 2000), ('לוגו בסיסי: סקיצה אחת וסבב תיקונים אחד', 590), ('מסך עדכון קטן לבעל העסק: שעות פתיחה והודעה זמנית, מהטלפון', 450), ('12 הדמיות מוצר של המסגרות (תלת־ממד, בלי אנשים)', 350)],
   incl=['עמוד אחד, עד 7 חלקים', 'טופס פנייה וכפתור חיוג', 'שינויים קטנים עליי בתקופת הליווי', 'דומיין, אחסון ומייל עסקי לשנה הראשונה', 'טקסטים לאתר: כתיבה בעזרת AI ועריכה אישית', 'התאמה לטלפון, לגוגל ולנגישות, והצהרת נגישות'],
   terms=['3 כיווני עיצוב', '3 סבבי תיקונים', '4 ימי עבודה לאספקה', '4 חודשי ליווי'],
   pay=QUOTE['he']['pay'], annual=QUOTE['he']['annual'],
   notes=['הבונה הציע לוגו מקצועי (1,500 ₪). בגלל התקציב בחרנו בלוגו בסיסי, ואפשר לשדרג בהמשך.', 'צריך לרשום דומיין: משהו קצר עם השם צלול. כלול בשנה הראשונה.', 'לוח זמנים: תוך שבועיים. התקציב שציין: 2,000 עד 4,000 ₪.']),
 'en': dict(items=[('Landing page', 2000), ('Basic logo: one sketch and one revision round', 590), ('A small update screen for the owner: opening hours and a temporary notice, from the phone', 450), ('12 product renders of the frames (3D, no people)', 350)],
   incl=['One page, up to 7 sections', 'A contact form and a call button', 'Small changes on me during the support period', 'Domain, hosting and business email for the first year', 'Website copy: written with AI and personally edited', 'Mobile, Google and accessibility ready, with an accessibility statement'],
   terms=['3 design directions', '3 revision rounds', '4 working days to deliver', '4 months of support'],
   pay=QUOTE['en']['pay'], annual=QUOTE['en']['annual'],
   notes=['The builder suggested a professional logo (₪1,500). Because of the budget we chose a basic logo, with an upgrade possible later.', 'A domain needs registering: something short with the name Tzalul. Included in the first year.', 'Timing: within two weeks. Stated budget: 2,000 to 4,000 ₪.']),
}

def hi_optika(ans, he):
    return [
     ('העסק' if he else 'The business', ans('intake', 'biz_what')),
     ('מה מייחד אותם' if he else 'What sets them apart', ans('intake', 'biz_unique')),
     ('הלקוחות' if he else 'The customers', ans('intake', 'cust_who') + '. ' + ans('intake', 'cust_need')),
     ('מה לקוחות שואלים הכי הרבה' if he else 'What customers ask most', ans('intake', 'cust_questions')),
     ('מה הוא רוצה לעדכן בעצמו' if he else 'What he wants to update himself', ans('intake', 'update_what')),
     ('3 מילים שהאתר צריך לשדר' if he else '3 words the site should convey', ans('intake', 'feel') + '. ' + ('רקע: ' if he else 'Background: ') + ans('intake', 'bg') + '.'),
     ('מה לא רוצים לראות' if he else "What he doesn't want to see", ans('intake', 'dislikes')),
     ('מסלול, תקציב וזמנים' if he else 'Package, budget and timing', ('מסלול: ' if he else 'Package: ') + ans('intake', 'package') + '\n' + ('תקציב: ' if he else 'Budget: ') + ans('intake', 'budget') + '\n' + ('מתי: ' if he else 'When: ') + ans('intake', 'deadline')),
    ]

def files_optika(he):
    return [('tzalul-logo-sm.webp', 'הלוגו החדש של צלול' if he else 'The new Tzalul logo', 293), ('round-560.webp', 'הדמיה: מסגרת עגולה שחורה' if he else 'Render: a round black frame', 126),
            ('gold-round-560.webp', 'הדמיה: מסגרת זהב דקה' if he else 'Render: a thin gold frame', 126), ('tortoise-560.webp', 'הדמיה: מסגרת בגוון צב' if he else 'Render: a tortoise frame', 126), ('sun-aviator-560.webp', 'הדמיה: משקפי שמש טייסים' if he else 'Render: aviator sunglasses', 126)]

def extra_optika(lang, pfx):
    he = lang == 'he'
    pts = (['ימים ושעות, כולל מוצאי שבת', 'הודעה זמנית עם תאריך סיום, למשל לפני חג', 'סטטוס חי בדף: "פתוח עכשיו", "נסגר בעוד 25 דקות", "נפתח מחר ב־10:00"', 'מבצע בדף, עם תאריך סיום', 'בדיקה שהשעות הגיוניות לפני שמירה', 'נוח בטלפון: חצי דקה, בלי לחכות לאף אחד']
           if he else ['Days and hours, including Saturday night', 'A temporary notice with an end date, e.g. before a holiday', 'A live status on the page: "open now", "closes in 25 minutes", "opens tomorrow at 10:00"', 'A promotion on the page, with an end date', 'A check that the hours make sense before saving', 'Easy on the phone: half a minute, no waiting for anyone'])
    return f'''  <section class="d-step" id="sx" aria-labelledby="sx-t">
   <div class="d-step-h"><span class="d-num" aria-hidden="true">4</span><h2 id="sx-t">{E('המסך שבעל העסק ביקש' if he else 'The screen the owner asked for')}</h2></div>
   <p>{E('בדף נחיתה רגיל אין אזור ניהול, אז בניתי מסך עדכון קטן אחד, בדיוק למה שהוא צריך. מסמנים אילו ימים פתוח, משנים שעות, מוסיפים הודעה לחג או מבצע, ושומרים. הדף מתעדכן מיד.' if he else "A standard landing page has no admin area, so I built one small update screen for exactly what he needs. Mark which days are open, change hours, add a holiday notice or a promotion, and save. The page updates right away.")}</p>
   <div class="d-hours">
    <a href="/{pfx}admin/"><img src="/{pfx}shots/admin-d.webp" width="960" height="700" alt="{E('מסך עדכון השעות והמבצע' if he else 'The hours and promotion update screen')}" loading="lazy"></a>
    <div><ul>{''.join(f'<li>{E(x)}</li>' for x in pts)}</ul>
     <a class="btn btn-gold" href="/{pfx}admin/">{E('לנסות את מסך העדכון' if he else 'Try the update screen')}</a>
     <small>{E('בדוגמה השינוי נשמר רק אצלכם בדפדפן, כדי שכל אחד יוכל לנסות. פתחו את הגרסה הסופית בלשונית אחרת, ותראו אותה מתעדכנת.' if he else 'In the example, changes are saved only in your browser, so anyone can try. Open the final version in another tab and watch it update.')}</small></div>
   </div>
  </section>
'''

OPTIKA = dict(prefix='demo/optika/', data=json.loads((ROOT / 'tools/demo-optika.json').read_text()), designs=DESIGNS_OPT, T=T_OPT, quote=QUOTE_OPT, hi=hi_optika, files=files_optika, extra=extra_optika,
              card={'he': ('דף נחיתה לחנות משקפיים', 'שעות פתיחה ומבצע שבעל העסק מעדכן בעצמו'), 'en': ('A landing page for an eyewear shop', 'Opening hours and a promotion the owner updates himself')},
              other={'he': ('/demo/', 'דוגמה נוספת', 'אתר מלא לנגריה, עם 3 כיוונים שונים לגמרי', 'שאלון של 60 תשובות, 3 עיצובים, הגרסה הסופית ומסך ניהול התוכן'),
                     'en': ('/en/demo/', 'Another example', 'A full website for a carpentry shop, with 3 completely different directions', 'A 60-answer questionnaire, 3 designs, the final version and the content admin screen')})

exec(open(ROOT / 'tools/demo_projects_more.py').read())

# פרויקט נכנס לאתר רק כשיש לו גרסה סופית (כדי שלא יעלה חצי פרויקט)
def ready(v):
    need = ('final/index.html', 'shots/final-d.webp') + (() if v.get('no_admin') else ('admin/index.html', 'shots/admin-d.webp'))
    return all((ROOT / v['prefix'] / f).exists() for f in need) and v['T']['he'].get('s5p', 'x') != ''
PROJECTS = {k: v for k, v in {'anaf': ANAF, 'optika': OPTIKA, 'donate': DONATE, 'shul': SHUL, 'shop': SHOP}.items() if ready(v)}

def use(key):
    global DATA, DESIGNS, T, QUOTE, P
    P = PROJECTS[key]
    DATA, DESIGNS, T, QUOTE = P['data'], P['designs'], P['T'], P['quote']
