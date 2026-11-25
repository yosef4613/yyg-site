# YYG: ההגדרות של כל פרויקט לדוגמה. נטען מתוך tools/demo.py (exec), ומשתמש בפונקציות ובקבועים שלו.
# כל פרויקט: קובץ נתונים, תיקייה באתר, 3 עיצובים, טקסטים, הצעת מחיר, ומה מוצג בשלב 1.

ANAF = dict(prefix='demo/', data=DATA, designs=DESIGNS, T=T, quote=QUOTE, hi=hi_anaf, files=files_anaf, extra=None,
            other={'he': ('/demo/optika/', 'דוגמה נוספת', 'דף נחיתה לחנות משקפיים, עם שעות שבעל העסק מעדכן בעצמו', '3 עיצובים, מסך עדכון השעות מהטלפון, הצעת מחיר ומשוב'),
                   'en': ('/en/demo/optika/', 'Another example', 'A landing page for an eyewear shop, with hours the owner updates himself', '3 designs, the hours update screen, a quote and feedback')})

# ---------- צלול: חנות משקפיים, דף נחיתה + עדכון שעות עצמי ----------
DESIGNS_OPT = [
 dict(n=1, he=dict(name='נקי ומדויק', text='עיצוב בהיר ומדויק, כמו מכון אופטיקה טוב: לבן וכחול, המשקפיים בתוך עדשה עגולה עם שנתות, ולוח ראייה קטן שכתוב בו "רואים טוב, נראים טוב".', fonts='רוביק ואסיסטנט'),
      en=dict(name='Clean and precise', text='A bright, precise design, like a good optical clinic: white and blue, the glasses inside a round lens with tick marks, and a little eye chart that reads "see well, look good".', fonts='Rubik and Assistant'),
      colors=['#FFFFFF', '#13202B', '#2F6F8F', '#DDEFEA']),
 dict(n=2, he=dict(name='בוטיק אופנתי', text='עיצוב כהה ודרמטי, כמו בוטיק אופנה: שחור וזהב, כותרות סריף גדולות, והמסגרות מוצגות כמו בקטלוג של מגזין.', fonts='פרנק רוהל ליברה ואסיסטנט'),
      en=dict(name='Fashion boutique', text='A dark, dramatic design, like a fashion boutique: black and gold, large serif headlines, and frames shown like a magazine lookbook.', fonts='Frank Ruhl Libre and Assistant'),
      colors=['#111315', '#C9A36A', '#F4EDE4', '#8D8378']),
 dict(n=3, he=dict(name='צבעוני ושכונתי', text='עיצוב שמח למשפחות: תכלת, צהוב וקורל, מדבקות כמו "פתוח עכשיו" ו"מוכן תוך 3 ימים", והמסגרות בכרטיסי פולרויד.', fonts='ורלה ראונד ורוביק'),
      en=dict(name='Colorful neighborhood', text='A cheerful design for families: sky blue, yellow and coral, stickers like "open now" and "ready in 3 days", and frames on polaroid cards.', fonts='Varela Round and Rubik'),
      colors=['#FFF9F0', '#2E8BC0', '#F6C343', '#E8684A']),
]

T_OPT = {lang: dict(T[lang]) for lang in T}
T_OPT['he'].update(
  title='דף נחיתה לדוגמה: חנות משקפיים עם שעות שמתעדכנות | YYG',
  desc='חנות משקפיים דמיונית עוברת את כל התהליך של YYG: שאלון, הצעת מחיר, 3 עיצובים לדף נחיתה, מסך שבו בעל העסק מעדכן בעצמו את שעות הפתיחה, ושאלון משוב.',
  h1='דף נחיתה לחנות משקפיים, עם שעות שבעל העסק מעדכן בעצמו',
  lead='עוד דוגמה מלאה, הפעם דף נחיתה: חנות משקפיים דמיונית בשם "צלול", שבעליה ביקש דבר אחד מעל הכול: לעדכן בעצמו את שעות הפתיחה, מהטלפון. עברנו את כל התהליך כמו עם לקוח אמיתי: שאלון, הצעת מחיר, 3 עיצובים, מסך העדכון, ומשוב.',
  note='עסק דמיוני לדוגמה: השם, הפרטים וההמלצות בדויים. התמונות הן הדמיות תלת־ממד שבניתי במיוחד, וגם הלוגו עוצב לדוגמה.',
  nav=['שאלון אפיון', 'הצעת מחיר', '3 עיצובים', 'עדכון השעות', 'משוב ובחירה', 'מה הלאה'],
  s1p='בעל החנות מילא את השאלון בכ־10 דקות. הלוגו הישן לא שירת אותו, ותמונות לא היו בכלל. אלה התשובות שהכי השפיעו:',
  files='מה הכנתי בשבילו: לוגו חדש והדמיות מוצר',
  s2p='הבונה שלי זיהה מהשאלון דף נחיתה, לוגו חדש, ושני דברים לתמחור: תמונות, ומסך לעדכון השעות. תמחרתי, בדקתי, והלקוח קיבל במייל הצעה מסודרת עם אישור בלחיצה.',
  s3p='אחרי 10% הראשונים מכינים 3 כיוונים שונים לגמרי לאותו דף נחיתה. בכל אחד מהם השעות והסטטוס "פתוח עכשיו" חיים, ומתעדכנים לפי שעון ישראל:',
  s5p='הלקוח בחר בעיצוב 1, עם סטטוס "פתוח עכשיו" גדול והמדבקה "מוכן תוך 3 ימי עבודה" מעיצוב 3, ותמונת מסגרות הזהב מעיצוב 2. משלמים 40%, בונים תוך 4 ימי עבודה, הלקוח מאשר ומשלם את ה־50% האחרונים, והדף באוויר. מאותו יום הוא מעדכן את השעות לבד.',
)
T_OPT['en'].update(
  title='Example landing page: an eyewear shop with self-updating hours | YYG',
  desc='A fictional eyewear shop goes through the whole YYG process: a questionnaire, a quote, 3 landing page designs, a screen where the owner updates the opening hours himself, and a feedback form.',
  h1='A landing page for an eyewear shop, with hours the owner updates himself',
  lead='Another complete example, this time a landing page: a fictional eyewear shop called "Tzalul", whose owner asked for one thing above all: to update the opening hours himself, from his phone. We went through the whole process as with a real client: a questionnaire, a quote, 3 designs, the update screen, and feedback.',
  note='A fictional example business: the name, details and testimonials are made up. The images are 3D renders I built specially, and the logo was designed for the example too.',
  nav=['Questionnaire', 'Quote', '3 designs', 'Hours screen', 'Feedback', "What's next"],
  s1p='The shop owner filled in the questionnaire in about 10 minutes. His old logo wasn\'t working for him, and there were no photos at all. These are the answers that shaped the work most:',
  files='What I prepared for him: a new logo and product renders',
  s2p='From the questionnaire, my builder picked a landing page, a new logo, and two things to price: images, and a screen for updating the hours. I priced and checked it, and the client got a formal quote by email with one-click approval.',
  s3p='After the first 10%, I prepare 3 completely different directions for the same landing page. In each, the hours and the "open now" status are live, by Israel time:',
  s5p='The client chose design 1, with a big "open now" status and the "ready in 3 working days" sticker from design 3, and the gold frames image from design 2. He pays 40%, the page is built within 4 working days, he approves and pays the final 50%, and it goes live. From that day on he updates the hours himself.',
)

QUOTE_OPT = {
 'he': dict(items=[('דף נחיתה', 2000), ('לוגו בסיסי: סקיצה אחת וסבב תיקונים אחד', 590), ('מסך עדכון קטן לבעל העסק: שעות פתיחה והודעה זמנית, מהטלפון', 450), ('12 הדמיות מוצר של המסגרות (תלת־ממד, בלי אנשים)', 350)],
   incl=['עמוד אחד, עד 7 חלקים', 'טופס פנייה וכפתור חיוג', 'שינויים קטנים עליי בתקופת הליווי', 'דומיין, אחסון ומייל עסקי לשנה הראשונה', 'טקסטים לאתר: כתיבה בעזרת AI ועריכה אישית', 'התאמה לטלפון, לגוגל ולנגישות, והצהרת נגישות'],
   terms=['3 כיווני עיצוב', '3 סבבי תיקונים', '4 ימי עבודה לאספקה', '4 חודשי ליווי'],
   pay=QUOTE['he']['pay'], annual=QUOTE['he']['annual'],
   notes=['הלוגו הקיים מתחדש: לוגו בסיסי, ואפשר לשדרג ללוגו מקצועי ב־1,500 ₪.', 'צריך לרשום דומיין: משהו קצר עם השם צלול. כלול בשנה הראשונה.', 'לוח זמנים: תוך שבועיים. התקציב שציין: 2,000 עד 4,000 ₪.']),
 'en': dict(items=[('Landing page', 2000), ('Basic logo: one sketch and one revision round', 590), ('A small update screen for the owner: opening hours and a temporary notice, from the phone', 450), ('12 product renders of the frames (3D, no people)', 350)],
   incl=['One page, up to 7 sections', 'A contact form and a call button', 'Small changes on me during the support period', 'Domain, hosting and business email for the first year', 'Website copy: written with AI and personally edited', 'Mobile, Google and accessibility ready, with an accessibility statement'],
   terms=['3 design directions', '3 revision rounds', '4 working days to deliver', '4 months of support'],
   pay=QUOTE['en']['pay'], annual=QUOTE['en']['annual'],
   notes=['The old logo gets refreshed: a basic logo, with an upgrade to a professional logo for 1,500 ₪.', 'A domain needs registering: something short with the name Tzalul. Included in the first year.', 'Timing: within two weeks. Stated budget: 2,000 to 4,000 ₪.']),
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
    pts = (['ימים ושעות, כולל מוצאי שבת', 'הודעה זמנית עם תאריך סיום, למשל לפני חג', 'סטטוס חי בדף: "פתוח עכשיו", "נסגר בעוד 25 דקות", "נפתח מחר ב־10:00"', 'בדיקה שהשעות הגיוניות לפני שמירה', 'נוח בטלפון: חצי דקה, בלי לחכות לאף אחד']
           if he else ['Days and hours, including Saturday night', 'A temporary notice with an end date, e.g. before a holiday', 'A live status on the page: "open now", "closes in 25 minutes", "opens tomorrow at 10:00"', 'A check that the hours make sense before saving', 'Easy on the phone: half a minute, no waiting for anyone'])
    return f'''  <section class="d-step" id="sx" aria-labelledby="sx-t">
   <div class="d-step-h"><span class="d-num" aria-hidden="true">4</span><h2 id="sx-t">{E('המסך שבעל העסק ביקש' if he else 'The screen the owner asked for')}</h2></div>
   <p>{E('בדף נחיתה רגיל אין אזור ניהול, אז בניתי מסך קטן אחד, בדיוק למה שהוא צריך. מסמנים אילו ימים פתוח, משנים שעות, מוסיפים הודעה לחג, ושומרים. הדף מתעדכן מיד.' if he else "A standard landing page has no admin area, so I built one small screen for exactly what he needs. Mark which days are open, change hours, add a holiday notice, and save. The page updates right away.")}</p>
   <div class="d-hours">
    <a href="/{pfx}admin/"><img src="/{pfx}shots/admin-d.webp" width="960" height="700" alt="{E('מסך עדכון שעות הפתיחה' if he else 'The opening hours update screen')}" loading="lazy"></a>
    <div><ul>{''.join(f'<li>{E(x)}</li>' for x in pts)}</ul>
     <a class="btn btn-gold" href="/{pfx}admin/">{E('לנסות את מסך העדכון' if he else 'Try the update screen')}</a>
     <small>{E('בדוגמה השינוי נשמר רק אצלכם בדפדפן, כדי שכל אחד יוכל לנסות. פתחו עיצוב בלשונית אחרת, ותראו אותו מתעדכן.' if he else 'In the example, changes are saved only in your browser, so anyone can try. Open a design in another tab and watch it update.')}</small></div>
   </div>
  </section>
'''

OPTIKA = dict(prefix='demo/optika/', data=json.loads((ROOT / 'tools/demo-optika.json').read_text()), designs=DESIGNS_OPT, T=T_OPT, quote=QUOTE_OPT, hi=hi_optika, files=files_optika, extra=extra_optika,
              other={'he': ('/demo/', 'דוגמה נוספת', 'אתר מלא לנגריה, עם 3 כיוונים שונים לגמרי', 'שאלון של 60 תשובות, הצעת מחיר, 3 עיצובים ומשוב'),
                     'en': ('/en/demo/', 'Another example', 'A full website for a carpentry shop, with 3 completely different directions', 'A 60-answer questionnaire, a quote, 3 designs and feedback')})

PROJECTS = {'anaf': ANAF, 'optika': OPTIKA}

def use(key):
    global DATA, DESIGNS, T, QUOTE, P
    P = PROJECTS[key]
    DATA, DESIGNS, T, QUOTE = P['data'], P['designs'], P['T'], P['quote']
