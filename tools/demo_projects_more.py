# YYG Web Design: שלושת הפרויקטים לדוגמה הנוספים: עמותה (סל ברכה), בית כנסת (נחלת יצחק) וחנות (גרגר).
# נטען מתוך tools/demo_projects.py (exec), ומשתמש בפונקציות ובקבועים של tools/demo.py.

def admin_step(title, p, pts, alt, btn, h=660):
    """שלב "מסך הניהול" בעמוד הסיפור. כל טקסט הוא זוג (עברית, אנגלית)"""
    def f(lang, pfx, n):
        he = lang == 'he'; L = lambda x: x[0] if he else x[1]
        return f'''  <section class="d-step" id="sa" aria-labelledby="sa-t">
   <div class="d-step-h"><span class="d-num" aria-hidden="true">{n}</span><h2 id="sa-t">{E(L(title))}</h2></div>
   <p>{E(L(p))}</p>
   <div class="d-hours">
    <a href="/{pfx}admin/"><img src="/{pfx}shots/admin-d.webp" width="960" height="{h}" alt="{E(L(alt))}" loading="lazy"></a>
    <div><ul>{''.join(f'<li>{E(x)}</li>' for x in L(pts))}</ul>
     <a class="btn btn-gold" href="/{pfx}admin/">{E(L(btn))}</a>
     <small>{E('בדוגמה השינוי נשמר רק אצלכם בדפדפן, כדי שכל אחד יוכל לנסות. פתחו את הגרסה הסופית בלשונית אחרת, ותראו אותה מתעדכנת.' if he else 'In the example, changes are saved only in your browser, so anyone can try. Open the final version in another tab and watch it update.')}</small></div>
   </div>
  </section>
'''
    return f

def t_for(he_upd, en_upd):
    t = {lang: dict(T[lang]) for lang in T}
    t['he'].update(nav=['שאלון אפיון', 'הצעת מחיר', '3 עיצובים', 'משוב ובחירה', 'הגרסה הסופית', 'מסך הניהול'], **he_upd)
    t['en'].update(nav=['Questionnaire', 'Quote', '3 designs', 'Feedback', 'Final version', 'Admin screen'], **en_upd)
    return t

def hi_generic(rows):
    """rows: (תווית עברית, תווית אנגלית, פונקציה שמקבלת ans ו־he ומחזירה טקסט)"""
    return lambda ans, he: [((a if he else b), fn(ans, he)) for a, b, fn in rows]

def files_from(rows):
    return lambda he: [(f, (a if he else b), w) for f, a, b, w in rows]

def pkg_line(ans, he, when='deadline'):
    return (('מסלול: ' if he else 'Package: ') + ans('intake', 'package') + '\n' + ('תקציב: ' if he else 'Budget: ') + ans('intake', 'budget') + '\n' + ('מתי: ' if he else 'When: ') + ans('intake', when))

def data_of(slug):
    return json.loads((ROOT / f'tools/demo-{slug}.json').read_text())

# ---------- סל ברכה: עמותה, תרומות באשראי וקמפיין חי ----------
DESIGNS_DON = [
 dict(n=1, he=dict(name='חם ומכובד', text='עיצוב שקט ומכובד, כמו מכתב תודה מודפס: נייר בהיר, דיו ירוק וזהב של חיטה, כותרות סריף גדולות, ולוח תורמים שנראה חרוט.', fonts='פרנק רוהל ליברה ואסיסטנט'),
      en=dict(name='Warm and dignified', text='A quiet, dignified design, like a printed letter of thanks: light paper, green ink and wheat gold, large serif headlines, and a donor board that looks engraved.', fonts='Frank Ruhl Libre and Assistant'),
      colors=['#F7F2E8', '#1E3A2D', '#93651F', '#E6DCC6']),
 dict(n=2, he=dict(name='קמפיין חי', text='הקמפיין במרכז: מד ענק עם ספרות גדולות שמתעדכנות בכל תרומה, אריחי נתונים וטופס תרומה ממש לידו. כחול לילה וזעפרן, נקי ובטוח בעצמו.', fonts='היבו'),
      en=dict(name='Live campaign', text='The campaign takes center stage: a huge meter with big numbers that update with every donation, data tiles and the donation form right beside it. Night blue and saffron, clean and confident.', fonts='Heebo'),
      colors=['#13233F', '#FFFCF6', '#F2B23E', '#B83A45']),
 dict(n=3, he=dict(name='שכונתי וקהילתי', text='עיצוב חם ורך, כמו לוח מודעות שכונתי: צבעי אדמה, כרטיסים מעוגלים, סל שמתמלא לפי היעד, ועדכונים מהשטח כפתקים נעוצים.', fonts='ורלה ראונד ורוביק'),
      en=dict(name='Neighborhood community', text='A warm, soft design, like a neighborhood notice board: earthy colors, rounded cards, a basket that fills up toward the goal, and field updates as pinned notes.', fonts='Varela Round and Rubik'),
      colors=['#FBF6EC', '#4A6E42', '#B1532F', '#2E2A24']),
]
T_DON = t_for(dict(
  title='אתר לדוגמה: עמותה עם תרומות באשראי וקמפיין חי | YYG Web Design',
  desc='עמותה דמיונית עוברת את כל התהליך של YYG Web Design: שאלון, הצעת מחיר, 3 עיצובים לאתר עם תרומות באשראי וקמפיין חי, משוב, הגרסה הסופית ומסך ניהול הקמפיין.',
  h1='אתר לעמותה, עם תרומות באשראי וקמפיין שמתעדכן לבד',
  lead='עמותה דמיונית בשם "סל ברכה", שמביאה סלי מזון לשבת ולחג לכ־400 משפחות בירושלים. עד היום תרמו לה רק במזומן ובהעברה. ביקשו אתר שאפשר לתרום בו באשראי, חד־פעמי או בהוראת קבע, עם קמפיין חורף, מד יעד ורשימת תורמים שמתעדכנים לבד. עברנו את כל התהליך: שאלון, הצעת מחיר, 3 עיצובים, משוב, הגרסה הסופית ומסך הניהול.',
  note='עמותה דמיונית לדוגמה: השם, הפרטים, התורמים וההמלצות בדויים, ושום תרומה לא נגבית. התמונות הן הדמיות תלת־ממד שבניתי במיוחד.',
  s1p='יו״ר העמותה מילא את השאלון בכ־10 דקות. היה לו רק צילום של הלוגו, ובלי תמונות בכלל, כי את המשפחות לא מצלמים. אלה התשובות שהכי השפיעו:',
  files='מה הכנתי בשבילו: שרטוט נקי של הלוגו, והדמיות תלת־ממד',
  s2p='מהשאלון יצאו אתר עם אזור ניהול, ושני דברים לתמחור: תרומות באשראי, וקמפיין שמתעדכן לבד. בונה ההצעות שלי הכין טיוטה, עברתי עליה, והעמותה קיבלה במייל הצעה מסודרת עם אישור בלחיצה. עמלות חברת הסליקה משולמות ישירות לחברה.',
  s3p='אחרי 10% הראשונים מכינים 3 כיוונים שונים לגמרי לאותו אתר. בכל אחד מהם המד, הימים שנותרו ורשימת התורמים חיים: אפשר לתרום לדוגמה ולראות אותם מתעדכנים.',
  s5p='', changes=[]),
 dict(
  title='Example website: a nonprofit with card donations and a live campaign | YYG Web Design',
  desc='A fictional nonprofit goes through the whole YYG Web Design process: a questionnaire, a quote, 3 designs for a site with card donations and a live campaign, feedback, the final version and the campaign admin screen.',
  h1='A nonprofit website, with card donations and a campaign that updates itself',
  lead='A fictional nonprofit called "Sal Bracha", which brings Shabbat and holiday food baskets to about 400 families in Jerusalem. Until now it only took cash and bank transfers. It asked for a site where people can give by card, one-time or monthly, with a winter campaign, a goal meter and a donor list that update themselves. We went through the whole process: a questionnaire, a quote, 3 designs, feedback, the final version and the admin screen.',
  note='A fictional example nonprofit: the name, details, donors and testimonials are made up, and no donation is charged. The images are 3D renders I built specially.',
  s1p='The nonprofit\'s chairman filled in the questionnaire in about 10 minutes. He had only a photo of the logo, and no photos at all, since the families are never photographed. These are the answers that shaped the work most:',
  files='What I prepared for him: a clean redraw of the logo, and 3D renders',
  s2p='From the questionnaire came a website with an admin area, and two things to price: card donations, and a campaign that updates itself. My quote builder drafted it, I reviewed it, and the nonprofit got a formal quote by email with one-click approval. The payment provider\'s fees are paid directly to the provider.',
  s3p='After the first 10%, I prepare 3 completely different directions for the same site. In each, the meter, the days left and the donor list are live: you can make an example donation and watch them update.',
  s5p='', changes=[]))
QUOTE_DON = {
 'he': dict(items=[('אתר עם אזור ניהול', 4000), ('תרומות באשראי: חיבור לחברת הסליקה, תרומה חד־פעמית או חודשית, וקבלה במייל', 1500), ('קמפיין: מד יעד, ספירה לאחור ורשימת תורמים שמתעדכנים לבד', 500)],
   incl=['עד 6 עמודים', 'אזור ניהול: הקמפיין, עדכונים מהשטח, תרומות שנרשמות ביד ורשימת התורמים', 'עמוד תרומה: סכומים קבועים וסכום חופשי, הקדשה ובעילום שם', 'שרטוט נקי של הלוגו הקיים מתוך הצילום', 'הדרכה: שיחה אישית ומדריך כתוב', 'דומיין, אחסון ומייל עסקי לשנה הראשונה', 'טקסטים לאתר: כתיבה בעזרת AI ועריכה אישית', 'התאמה לטלפון, לגוגל ולנגישות, והצהרת נגישות'],
   terms=['3 כיווני עיצוב', '3 סבבי תיקונים', '14 ימי עבודה לאספקה', '4 חודשי ליווי'],
   pay=QUOTE['he']['pay'], annual=QUOTE['he']['annual'],
   notes=['סימן 5 עמודים, ועוד עמוד תרומה: 6 עמודים, בדיוק מה שכלול במסלול. אין תוספת עמודים.', 'תרומות באשראי: העמותה פותחת חשבון בחברת סליקה שתבחר, ואני מחבר אותו לאתר. העמלות של חברת הסליקה משולמות ישירות אליה.', 'לוח זמנים: הקמפיין נפתח בראש חודש כסלו, ו־14 ימי עבודה מספיקים. התקציב שציין: 4,000 עד 7,000 ₪.']),
 'en': dict(items=[('Website with an admin area', 4000), ('Card donations: connection to the payment provider, one-time or monthly donations, and an emailed receipt', 1500), ('Campaign: a goal meter, a countdown and a donor list that update themselves', 500)],
   incl=['Up to 6 pages', 'An admin area: the campaign, field updates, donations recorded by hand and the donor list', 'A donation page: set amounts and any amount, dedications and anonymous giving', 'A clean redraw of the existing logo from the photo', 'Training: a personal call and a written guide', 'Domain, hosting and business email for the first year', 'Website copy: written with AI and personally edited', 'Mobile, Google and accessibility ready, with an accessibility statement'],
   terms=['3 design directions', '3 revision rounds', '14 working days to deliver', '4 months of support'],
   pay=QUOTE['en']['pay'], annual=QUOTE['en']['annual'],
   notes=['Marked 5 pages, plus a donation page: 6 pages, exactly what the package includes. No extra pages.', 'Card donations: the nonprofit opens an account with a payment provider of its choice, and I connect it to the site. The provider\'s fees are paid directly to it.', 'Timing: the campaign opens on Rosh Chodesh Kislev, and 14 working days are enough. Stated budget: 4,000 to 7,000 ₪.']),
}
DONATE = dict(prefix='demo/donate/', data=data_of('donate'), designs=DESIGNS_DON, T=T_DON, quote=QUOTE_DON, extra=None,
  hi=hi_generic([
    ('העמותה', 'The organization', lambda a, he: a('intake', 'biz_what')),
    ('מה מייחד אותם', 'What sets them apart', lambda a, he: a('intake', 'biz_unique')),
    ('התורמים', 'The donors', lambda a, he: a('intake', 'cust_who') + '. ' + a('intake', 'cust_need')),
    ('רגישות', 'Sensitivities', lambda a, he: a('intake', 'cust_sensitive')),
    ('המטרה של האתר', 'The goal of the site', lambda a, he: a('intake', 'goal_action') + '. ' + a('intake', 'goal_success')),
    ('מה הוא רוצה לעדכן בעצמו', 'What he wants to update himself', lambda a, he: a('intake', 'update_what')),
    ('3 מילים שהאתר צריך לשדר', '3 words the site should convey', lambda a, he: a('intake', 'feel') + '. ' + ('רקע: ' if he else 'Background: ') + a('intake', 'bg') + '.'),
    ('מסלול, תקציב וזמנים', 'Package, budget and timing', lambda a, he: pkg_line(a, he)),
  ]),
  files=files_from([('salbracha-logo-sm.webp', 'הלוגו של סל ברכה, אחרי שרטוט נקי', 'The Sal Bracha logo, cleanly redrawn', 292),
                    ('basket-560.webp', 'הדמיה: ארגז מלא מצרכים לשבת', 'Render: a crate full of Shabbat groceries', 126),
                    ('shabbat-560.webp', 'הדמיה: שולחן שבת עם חלות מכוסות', 'Render: a Shabbat table with covered challot', 126),
                    ('boxes-560.webp', 'הדמיה: קופסאות מוכנות לחלוקה', 'Render: boxes ready for delivery', 126),
                    ('coinjar-560.webp', 'הדמיה: צנצנת מטבעות', 'Render: a jar of coins', 67)]),
  card={'he': ('אתר לעמותה, עם תרומות באשראי וקמפיין חי', 'מד יעד, רשימת תורמים ומסך ניהול לקמפיין'),
        'en': ('A nonprofit website with card donations and a live campaign', 'A goal meter, a donor list and a campaign admin screen')},
  after=None)

# ---------- נחלת יצחק: בית כנסת, אתר תדמית ותרומות ----------
DESIGNS_SHUL = [
 dict(n=1, he=dict(name='היכל קלאסי', text='עיצוב מכובד ומסורתי, כמו היכל בערב שבת: כחול לילה, זהב עתיק ונייר קלף, כותרות סריף, ארון הקודש בתוך קשת, ולוח זמני התפילות מעוצב כמו הלוח מעץ שבכניסה לבית הכנסת.', fonts='פרנק רוהל ליברה ואסיסטנט'),
      en=dict(name='Classic sanctuary', text='A dignified, traditional design, like the sanctuary on Friday night: midnight blue, antique gold and parchment, serif headlines, the ark inside an arch, and the prayer-times board styled like the wooden board at the synagogue entrance.', fonts='Frank Ruhl Libre and Assistant'),
      colors=['#14213D', '#B8913F', '#F7F1E5', '#6D1A2A']),
 dict(n=2, he=dict(name='קהילה פתוחה', text='עיצוב בהיר ומודרני, כמו אפליקציה של הקהילה: לבן, טורקיז וצהוב חם, כרטיסים מעוגלים, פס "השבוע בבית הכנסת" עם התפילה הבאה והשיעור של היום, וטבעת גדולה שמראה כמה נאסף.', fonts='רוביק ואסיסטנט'),
      en=dict(name='Open community', text='A bright, modern design, like the community\'s own app: white, teal and warm yellow, rounded cards, a "this week at shul" strip with the next prayer and today\'s class, and a big ring showing how much has been raised.', fonts='Rubik and Assistant'),
      colors=['#FFFFFF', '#12222D', '#1E6E86', '#F3B63F']),
 dict(n=3, he=dict(name='אבן ירושלמית', text='עיצוב חם ואדמתי, כמו קירות האבן של בית שמש: אבן, זית ונחושת, תמונות גדולות וקשתות, כותרות שנראות חקוקות, ומד הקמפיין הוא קיר של 35 אבנים שמתמלא אבן אחרי אבן.', fonts='סואץ וואן והיבו'),
      en=dict(name='Jerusalem stone', text='A warm, earthy design, like the stone walls of Beit Shemesh: stone, olive and copper, large images and arches, headlines that look carved, and a campaign meter that is a wall of 35 stones filling in one stone at a time.', fonts='Suez One and Heebo'),
      colors=['#EFE6D6', '#2D2418', '#4D5A36', '#9A4C2A']),
]
T_SHUL = t_for(dict(
  title='אתר לדוגמה: בית כנסת עם זמני תפילה, קמפיין והקדשות | YYG Web Design',
  desc='בית כנסת דמיוני עובר את כל התהליך של YYG Web Design: שאלון, הצעת מחיר, 3 עיצובים לאתר תדמית עם תרומות, זמני תפילה שהגבאי מעדכן, קמפיין שיפוץ והקדשות, משוב, הגרסה הסופית ומסך הניהול.',
  h1='אתר לבית כנסת: זמני תפילה, קמפיין שיפוץ והקדשות',
  lead='בית כנסת דמיוני בשם "נחלת יצחק" בבית שמש, קהילה של כ־120 משפחות. הגבאי ביקש שני דברים: לעדכן בעצמו את זמני התפילות מהטלפון, וקמפיין לשיפוץ ההיכל עם תרומות באשראי והקדשות. עברנו את כל התהליך: שאלון, הצעת מחיר, 3 עיצובים, משוב, הגרסה הסופית ומסך הניהול.',
  note='בית כנסת דמיוני לדוגמה: השם, הפרטים, התורמים והסכומים בדויים, ושום תרומה לא נגבית. התמונות הן הדמיות תלת־ממד שבניתי במיוחד, והסמל שורטט מחדש לדוגמה.',
  s1p='הגבאי מילא את השאלון בכ־10 דקות. היה לו רק צילום של הסמל הישן מהשלט בכניסה, ובלי תמונות בכלל. אלה התשובות שהכי השפיעו:',
  files='מה הכנתי בשבילו: שרטוט נקי של הסמל, והדמיות תלת־ממד',
  s2p='מהשאלון יצאו אתר עם אזור ניהול, ושני דברים לתמחור: תרומות באשראי, והקדשות עם קמפיין שמתעדכן לבד. בונה ההצעות שלי הכין טיוטה, עברתי עליה, והגבאי קיבל במייל הצעה מסודרת עם אישור בלחיצה. עמלות חברת הסליקה משולמות ישירות לחברה.',
  s3p='אחרי 10% הראשונים מכינים 3 כיוונים שונים לגמרי לאותו אתר. בכל אחד מהם זמני התפילות, התפילה הבאה, מד הקמפיין וההקדשות חיים: אפשר לתרום לדוגמה ולראות אותם מתעדכנים.',
  s5p='', changes=[]),
 dict(
  title='Example website: a synagogue with prayer times, a campaign and dedications | YYG Web Design',
  desc='A fictional synagogue goes through the whole YYG Web Design process: a questionnaire, a quote, 3 designs for a presentation site with donations, prayer times the gabbai updates, a renovation campaign and dedications, feedback, the final version and the admin screen.',
  h1='A synagogue website: prayer times, a renovation campaign and dedications',
  lead='A fictional synagogue called "Nachalat Yitzchak" in Beit Shemesh, a community of about 120 families. The gabbai asked for two things: to update the prayer times himself from his phone, and a campaign to renovate the sanctuary, with card donations and dedications. We went through the whole process: a questionnaire, a quote, 3 designs, feedback, the final version and the admin screen.',
  note='A fictional example synagogue: the name, details, donors and amounts are made up, and no donation is charged. The images are 3D renders I built specially, and the emblem was redrawn for the example.',
  s1p='The gabbai filled in the questionnaire in about 10 minutes. He had only a photo of the old emblem from the sign at the entrance, and no photos at all. These are the answers that shaped the work most:',
  files='What I prepared for him: a clean redraw of the emblem, and 3D renders',
  s2p='From the questionnaire came a website with an admin area, and two things to price: card donations, and dedications with a campaign that updates itself. My quote builder drafted it, I reviewed it, and the gabbai got a formal quote by email with one-click approval. The payment provider\'s fees are paid directly to the provider.',
  s3p='After the first 10%, I prepare 3 completely different directions for the same site. In each, the prayer times, the next prayer, the campaign meter and the dedications are live: you can make an example donation and watch them update.',
  s5p='', changes=[]))
QUOTE_SHUL = {
 'he': dict(items=[('אתר עם אזור ניהול', 4000), ('תרומות באשראי: חיבור לחברת הסליקה, תרומה חד־פעמית או חודשית, וקבלה במייל', 1500), ('הקדשות וקמפיין: מד יעד, ספירה לאחור, פריטים להקדשה ורשימת תורמים שמתעדכנים לבד', 800)],
   incl=['עד 6 עמודים', 'אזור ניהול לגבאי: זמני תפילות, שיעורים, הודעות לקהילה ועדכוני קמפיין', 'טופס תרומה מאובטח: חד־פעמית או הוראת קבע, גם בעילום שם', 'קבלה אוטומטית במייל אחרי כל תרומה', 'הקדשות במחירים קבועים, שמסומנות "הוקדש" לבד', 'טופס פנייה, כפתורי חיוג ווואטסאפ, והוראות הגעה', 'הדרכה: שיחה אישית ומדריך כתוב', 'דומיין, אחסון ומייל עסקי לשנה הראשונה', 'טקסטים לאתר: כתיבה בעזרת AI ועריכה אישית', 'התאמה לטלפון, לגוגל ולנגישות, והצהרת נגישות'],
   terms=['3 כיווני עיצוב', '3 סבבי תיקונים', '14 ימי עבודה לאספקה', '4 חודשי ליווי'],
   pay=QUOTE['he']['pay'], annual=QUOTE['he']['annual'],
   notes=['סימן 6 עמודים: בית, אודות, זמני תפילות ושיעורים, תרומות והקדשות, אירועים והאולם, צור קשר. בדיוק 6, כמו שכלול במסלול. שאלות נפוצות הן חלק בעמוד התרומות.', 'יש רק צילום של הסמל הישן מהשלט בכניסה. משרטטים אותו מחדש כקובץ נקי לאתר, בלי לשנות את הסמל עצמו, וזה כלול.', 'לוח זמנים: לפני ערב ההקדשה שבו נפתח הקמפיין. התקציב שציין: 4,000 עד 7,000 ₪. עמלות חברת הסליקה בנפרד.']),
 'en': dict(items=[('Website with an admin area', 4000), ('Card donations: connection to the payment provider, one-time or monthly giving, and an email receipt', 1500), ('Dedications and campaign: a goal meter, a countdown, items to dedicate and a donor list that all update by themselves', 800)],
   incl=['Up to 6 pages', 'An admin area for the gabbai: prayer times, classes, community announcements and campaign updates', 'A secure donation form: one-time or monthly, anonymous giving too', 'An automatic email receipt after every donation', 'Dedications at fixed prices, marked "dedicated" automatically', 'A contact form, call and WhatsApp buttons, and directions', 'Training: a personal call and a written guide', 'Domain, hosting and business email for the first year', 'Website copy: written with AI and personally edited', 'Mobile, Google and accessibility ready, with an accessibility statement'],
   terms=['3 design directions', '3 revision rounds', '14 working days to deliver', '4 months of support'],
   pay=QUOTE['en']['pay'], annual=QUOTE['en']['annual'],
   notes=['Marked 6 pages: home, about, prayer times and classes, donations and dedications, events and the hall, contact. Exactly 6, as included in the package. The FAQ is a section of the donations page.', "There's only a photo of the old emblem from the sign at the entrance. I redraw it as a clean file for the site, without changing the emblem itself, and that's included.", 'Timing: before the dedication evening where the campaign launches. Stated budget: ₪4,000 to ₪7,000. Payment provider fees are separate.']),
}
SHUL = dict(prefix='demo/shul/', data=data_of('shul'), designs=DESIGNS_SHUL, T=T_SHUL, quote=QUOTE_SHUL, extra=None,
  hi=hi_generic([
    ('הקהילה', 'The community', lambda a, he: a('intake', 'biz_what')),
    ('מה מייחד אותם', 'What sets them apart', lambda a, he: a('intake', 'biz_unique')),
    ('מי באתר', "Who it's for", lambda a, he: a('intake', 'cust_who') + '. ' + a('intake', 'cust_need')),
    ('מה שואלים את הגבאי', 'What people ask the gabbai', lambda a, he: a('intake', 'cust_questions')),
    ('מה הגבאי רוצה לעדכן בעצמו', 'What the gabbai wants to update himself', lambda a, he: a('intake', 'update_what')),
    ('רגישויות', 'Sensitivities', lambda a, he: a('intake', 'cust_sensitive')),
    ('איך יידעו שהאתר הצליח', "How they'll know it's working", lambda a, he: a('intake', 'goal_success')),
    ('מסלול, תקציב וזמנים', 'Package, budget and timing', lambda a, he: ('מסלול: ' if he else 'Package: ') + a('intake', 'package') + '\n' + ('תקציב: ' if he else 'Budget: ') + a('intake', 'budget') + '\n' + ('עד: ' if he else 'By: ') + a('intake', 'deadline_why')),
  ]),
  files=files_from([('nachalat-logo-sm.webp', 'הסמל של בית הכנסת, משורטט מחדש', 'The synagogue emblem, redrawn', 311),
                    ('aron-560.webp', 'הדמיה: ארון הקודש החדש', 'Render: the new ark', 126),
                    ('sanctuary-560.webp', 'הדמיה: ההיכל המחודש', 'Render: the renovated sanctuary', 126),
                    ('model-560.webp', 'הדמיה: דגם הבניין אחרי השיפוץ', 'Render: a model of the renovated building', 126),
                    ('memorial-560.webp', 'הדמיה: לוח ההנצחה', 'Render: the memorial board', 126)]),
  card={'he': ('אתר תדמית לבית כנסת, עם תרומות והקדשות', 'זמני תפילה שהגבאי מעדכן, קמפיין שיפוץ והקדשות'),
        'en': ('A synagogue presentation site, with donations and dedications', 'Prayer times the gabbai updates, a renovation campaign and dedications')},
  after=None)

# ---------- גרגר: חנות תבלינים ותה ----------
DESIGNS_SHOP = [
 dict(n=1, he=dict(name='שוק התבלינים', text='עיצוב חם וחגיגי, כמו דוכן משפחתי בשוק: רקע חום קלוי וחול, פפריקה וזעפרן, מסגרות בצורת קשת כמו בשוק של ירושלים, חותמות גומי, וכרטיסי מוצר בצורת תווית מחיר עם חוט.', fonts='סואץ וואן ואסיסטנט'),
      en=dict(name='The Spice Market', text='A warm, festive design, like a family stall at the market: roasted brown and sand, paprika and saffron, arch-shaped frames like the Jerusalem market, rubber stamps, and product cards shaped like price tags on a string.', fonts='Suez One and Assistant'),
      colors=['#2B1A12', '#A93A1F', '#E3A42B', '#F3E7D3']),
 dict(n=2, he=dict(name='בית מרקחת לתבלינים', text='עיצוב לבן, שקט ויוקרתי, כמו בית מרקחת ישן או קטלוג של מוזיאון: קווים דקים, הרבה אוויר, כל מוצר עם מספר ותווית במסגרת כפולה, והצבע מגיע רק מהתבלינים עצמם.', fonts='פרנק רוהל ליברה ואסיסטנט'),
      en=dict(name='The Spice Apothecary', text='A white, quiet, premium design, like an old apothecary or a museum catalog: hairline rules, lots of air, every product numbered with a double-ruled label, and the only color comes from the spices themselves.', fonts='Frank Ruhl Libre and Assistant'),
      colors=['#FBF9F5', '#1F1B16', '#8A4B2A', '#D9D2C5']),
 dict(n=3, he=dict(name='מכולת צבעונית', text='עיצוב נועז ושמח, כמו מכולת מודרנית: כותרות שחורות עבות, משבצות צבע בכורכום, פפריקה ומנטה, מדבקות, כרטיסים עגולים עם צל חד, ופס גדול שמראה כמה חסר למשלוח חינם.', fonts='היבו ורוביק'),
      en=dict(name='The Corner Grocery', text='A bold, cheerful design, like a modern grocery: chunky black headlines, color blocks in turmeric, paprika and mint, stickers, rounded cards with a hard shadow, and a big bar showing how much is left for free shipping.', fonts='Heebo and Rubik'),
      colors=['#FFF8EC', '#1C1A2E', '#C8381F', '#F2B32A']),
]
T_SHOP = t_for(dict(
  title='חנות לדוגמה: תבלינים ותה עם עגלה, משלוחים וניהול מלאי | YYG Web Design',
  desc='חנות תבלינים דמיונית עוברת את כל התהליך של YYG Web Design: שאלון, הצעת מחיר, 3 עיצובים לחנות אונליין עם עגלה שעובדת, משוב, הגרסה הסופית, ומסך ניהול למוצרים, למלאי ולהזמנות.',
  h1='חנות אונליין לתבלינים ותה, עם עגלה, משלוחים וניהול מלאי',
  lead='חנות תבלינים משפחתית דמיונית בשם "גרגר", דור שלישי לטוחנים ליד השוק בירושלים. הם רצו למכור באתר ולשלוח לכל הארץ, עם כ־40 מוצרים, משלוח חינם מעל 250 ₪ ואיסוף עצמי. עברנו את כל התהליך: שאלון, הצעת מחיר, 3 עיצובים, משוב, הגרסה הסופית ומסך הניהול.',
  note='חנות דמיונית לדוגמה: השם, הפרטים וההמלצות בדויים, ושום הזמנה לא נשלחת ולא נגבית. התמונות הן הדמיות תלת־ממד שבניתי במיוחד, וגם הלוגו עוצב לדוגמה.',
  s1p='בעל החנות מילא את השאלון בכ־10 דקות, וצירף את הלוגו הקיים. אלה התשובות שהכי השפיעו:',
  files='מה הכנתי בשבילו: לוגו מחודש והדמיות של המוצרים',
  s2p='מהשאלון יצאה חנות אונליין עם כ־40 מוצרים: 30 כלולים בחנות, ועוד 10 בתוספת. בונה ההצעות שלי הכין טיוטה, עברתי עליה, והלקוח קיבל במייל הצעה מסודרת עם אישור בלחיצה. עמלות חברת הסליקה משולמות ישירות לחברה.',
  s3p='אחרי 10% הראשונים מכינים 3 כיוונים שונים לגמרי לאותה חנות. בכל אחד מהם העגלה עובדת: אפשר להוסיף מוצרים, לראות כמה חסר למשלוח חינם, ולבצע הזמנה לדוגמה בלי תשלום.',
  s5p='', changes=[]),
 dict(
  title='Example store: spices and tea with a cart, shipping and stock management | YYG Web Design',
  desc='A fictional spice shop goes through the whole YYG Web Design process: a questionnaire, a quote, 3 designs for an online store with a working cart, feedback, the final version, and an admin screen for products, stock and orders.',
  h1='An online spice and tea store, with a cart, shipping and stock management',
  lead='A fictional family spice shop called "Gargar", the third generation grinding near the market in Jerusalem. They wanted to sell online and ship across Israel, with about 40 products, free shipping over 250 ₪ and pickup from the shop. We went through the whole process: a questionnaire, a quote, 3 designs, feedback, the final version and the admin screen.',
  note='A fictional example store: the name, details and testimonials are made up, and no order is shipped or charged. The images are 3D renders I built specially, and the logo was designed for the example too.',
  s1p='The shop owner filled in the questionnaire in about 10 minutes and attached his existing logo. These are the answers that shaped the work most:',
  files='What I prepared for him: a refined logo and product renders',
  s2p='From the questionnaire came an online store with about 40 products: 30 are included in the store, plus 10 as an add-on. My quote builder drafted it, I reviewed it, and the client got a formal quote by email with one-click approval. The payment provider\'s fees are paid directly to the provider.',
  s3p='After the first 10%, I prepare 3 completely different directions for the same store. In each, the cart works: you can add products, see how much is left for free shipping, and place an example order with no payment.',
  s5p='', changes=[]))
QUOTE_SHOP = {
 'he': dict(items=[('חנות אונליין', 7000), ('10 מוצרים נוספים בחנות', 200)],
   incl=['עד 30 מוצרים שאני מכניס, ועוד 10 בתוספת: 40 מוצרים בסך הכול', 'עגלת קניות, סליקה באשראי ומשלוחים', 'משלוח חינם מעל סכום שתקבעו, ואיסוף עצמי מהחנות', 'אזור ניהול למוצרים, למלאי ולהזמנות', 'הדרכה: שיחה אישית ומדריך כתוב', 'אחסון ומייל עסקי לשנה הראשונה, על הדומיין הקיים', 'טקסטים לאתר: כתיבה בעזרת AI ועריכה אישית', 'התאמה לטלפון, לגוגל ולנגישות, והצהרת נגישות'],
   terms=['3 כיווני עיצוב', '3 סבבי תיקונים', '20 ימי עבודה לאספקה', '4 חודשי ליווי'],
   pay=QUOTE['he']['pay'], annual=QUOTE['he']['annual'],
   notes=['יש כ־40 מוצרים: 30 כלולים בחנות, ועוד 10 בתוספת של 200 ₪. עמלות חברת הסליקה משולמות ישירות לחברה.', 'צירף את הלוגו הקיים: מעדנים אותו ומכינים קובץ נקי לאתר, בלי עלות נוספת. הדומיין כבר רשום על שמו.', 'לוח זמנים: עד 31.1.2027, לפני עונת המארזים לפורים ולפסח. התקציב שציין: מעל 7,000 ₪.']),
 'en': dict(items=[('Online store', 7000), ('10 more products in the store', 200)],
   incl=['Up to 30 products that I upload, plus 10 more as an add-on: 40 products in all', 'A shopping cart, card payments and shipping', 'Free shipping above an amount you set, and pickup from the shop', 'An admin area for products, stock and orders', 'Training: a personal call and a written guide', 'Hosting and business email for the first year, on the existing domain', 'Website copy: written with AI and personally edited', 'Mobile, Google and accessibility ready, with an accessibility statement'],
   terms=['3 design directions', '3 revision rounds', '20 working days to deliver', '4 months of support'],
   pay=QUOTE['en']['pay'], annual=QUOTE['en']['annual'],
   notes=['About 40 products: 30 are included in the store, plus 10 more for ₪200. Card-payment fees are paid directly to the payment provider.', 'He attached his existing logo: I refine it and prepare a clean file for the site, at no extra cost. The domain is already registered in his name.', 'Timing: by 31.1.2027, before the Purim and Passover gift box season. Stated budget: over 7,000 ₪.']),
}
SHOP = dict(prefix='demo/shop/', data=data_of('shop'), designs=DESIGNS_SHOP, T=T_SHOP, quote=QUOTE_SHOP, extra=None,
  hi=hi_generic([
    ('העסק', 'The business', lambda a, he: a('intake', 'biz_what')),
    ('מה מייחד אותם', 'What sets them apart', lambda a, he: a('intake', 'biz_unique')),
    ('הלקוחות', 'The customers', lambda a, he: a('intake', 'cust_who') + '. ' + a('intake', 'cust_need')),
    ('מה לקוחות שואלים הכי הרבה', 'What customers ask most', lambda a, he: a('intake', 'cust_questions')),
    ('מה הוא רוצה לנהל בעצמו', 'What he wants to manage himself', lambda a, he: a('intake', 'update_what')),
    ('משלוחים ומלאי', 'Shipping and stock', lambda a, he: a('intake', 'extra')),
    ('3 מילים שהאתר צריך לשדר', '3 words the site should convey', lambda a, he: a('intake', 'feel') + '. ' + ('רקע: ' if he else 'Background: ') + a('intake', 'bg') + '.'),
    ('מסלול, תקציב וזמנים', 'Package, budget and timing', lambda a, he: ('מסלול: ' if he else 'Package: ') + a('intake', 'package') + '\n' + ('תקציב: ' if he else 'Budget: ') + a('intake', 'budget') + '\n' + ('מתי: ' if he else 'When: ') + a('intake', 'deadline_date') + ', ' + a('intake', 'deadline_why')),
  ]),
  files=files_from([('gargar-logo-sm.webp', 'הלוגו המחודש של גרגר', 'The refined Gargar logo', 186),
                    ('jar-zaatar-560.webp', 'הדמיה: צנצנת זעתר', "Render: a jar of za'atar", 84),
                    ('pouch-nana-560.webp', 'הדמיה: שקית נענע', 'Render: a pouch of mint', 84),
                    ('tin-green-560.webp', 'הדמיה: פחית תה ירוק', 'Render: a green tea tin', 84),
                    ('gift-jars-560.webp', 'הדמיה: מארז לחג', 'Render: a holiday gift box', 84)]),
  card={'he': ('חנות אונליין לתבלינים ותה', 'עגלה שעובדת, משלוחים, וניהול מוצרים, מלאי והזמנות'),
        'en': ('An online spice and tea store', 'A working cart, shipping, and product, stock and order management')},
  after=None)

# ---------- גרגר: הגרסה הסופית ומסך הניהול (מתוך scratchpad/new-demos/shop/CASE.md) ----------
T_SHOP['he'].update(
  s5p='הלקוח בחר בעיצוב 1, "שוק התבלינים", ושילם 40%. בניתי את החנות על עיצוב 1, עם מה שביקש מהעיצובים האחרים: הפס הגדול של המשלוח החינם מעיצוב 3, וסיכום העגלה כמו קבלה מעיצוב 2, ושניהם צוירו מחדש בשפה של השוק. והוספתי את מה שהיה חסר לו: תאריך הטחינה האחרון על כל מוצר. משם הלקוח מאשר, משלם את ה־50% האחרונים, והחנות עולה לאוויר עם סליקה אמיתית. מאותו יום הוא מנהל לבד הזמנות, מלאי, מחירים ותאריכי טחינה.',
  changes=['פס משלוח חינם גדול מתחת לתפריט, שנשאר גלוי בגלילה ומראה כמה חסר. הרעיון מעיצוב 3, בצבעים ובגופנים של השוק', 'סיכום העגלה כמו קבלה: קווים מנוקדים, קו כפול לפני הסה״כ וקצוות משוננים. הרעיון מעיצוב 2', 'בטלפון תגיות המחיר ישרות, בלי ההטיה, כדי שיהיה קל לקרוא את המחיר', 'תאריך הטחינה האחרון על כל מוצר, כמו חותמת תאריך: "נטחן אתמול", "נטחן ב־25.11". תה ומארזים: "נארז"', 'הפס האדום הקטן מעל התפריט הוסר, כי הפס החדש מחליף אותו', 'החנות מתעדכנת מיד ממסך הניהול: מחירים, מלאי, "אזל מהמלאי", הודעה בראש החנות ותאריך הטחינה'],
)
T_SHOP['en'].update(
  s5p='The client chose design 1, "The Spice Market", and paid 40%. I built the store on design 1, with what he asked for from the other designs: the big free-shipping bar from design 3 and the receipt-style cart summary from design 2, both redrawn in the market\'s language. I also added what he felt was missing: the latest grinding date on every product. From here he approves, pays the final 50%, and the store goes live with real card payments. From that day on, he manages orders, stock, prices and grinding dates himself.',
  changes=['A big free-shipping bar under the menu that stays visible while scrolling and shows how much is left. The idea from design 3, in the market\'s colors and type', 'A receipt-style cart summary: dotted leaders, a double rule before the total, and torn edges. The idea from design 2', 'On the phone the price tags are straight, without the tilt, so the price is easy to read', 'The latest grinding date on every product, like a date stamp: "ground yesterday", "ground on 25.11". Teas and gift boxes: "packed"', 'The small red strip above the menu was removed, since the new bar replaces it', 'The store updates instantly from the admin screen: prices, stock, "sold out", the notice at the top and the grinding date'],
)
SHOP['after'] = admin_step(
  ('מסך ניהול החנות', 'The store admin screen'),
  ('בגלל זה בחרנו בחנות אונליין: נתנאל רוצה לנהל לבד את מה שמשתנה כל יום, מהמחשב בחנות או מהטלפון. במסך הזה הוא רואה הזמנה חדשה ברגע שהיא נכנסת, מעדכן את הסטטוס שלה, משנה מחיר ומלאי, ולוחץ "נטחן היום" אחרי כל טחינה. החנות מתעדכנת מיד.',
   'This is why we chose an online store: Netanel wants to manage what changes every day himself, from the shop computer or his phone. On this screen he sees a new order the moment it comes in, updates its status, changes prices and stock, and presses "ground today" after every grinding. The store updates right away.'),
  (['הזמנות: הזמנה חדשה מופיעה מיד, ומשנים סטטוס בלחיצה: שולמה, נארזה, נשלחה או נאספה', 'מחיר ומלאי לכל מוצר, ו"אזל מהמלאי" בלחיצה אחת', '"נטחן היום" למוצר אחד או לכל התבלינים יחד, והתאריך מופיע בחנות', 'מוצר חדש עם תמונה מהטלפון, שמוקטנת אוטומטית', 'משלוח חינם, דמי משלוח והודעה בראש החנות, למשל לפני פורים'],
   ['Orders: a new order shows up right away, and the status changes with one tap: paid, packed, shipped or picked up', 'Price and stock for every product, and "sold out" with one tap', '"Ground today" for one product or for all the spices at once, and the date shows in the store', 'A new product with a photo from the phone, resized automatically', 'The free-shipping threshold, the shipping fee and a notice at the top of the store, for example before Purim']),
  ('מסך ניהול החנות של גרגר: הזמנות, מלאי והמספרים של היום', "The Gargar store admin screen: orders, stock and today's numbers"),
  ('לנסות את מסך הניהול', 'Try the admin screen'))


# ---------- סל ברכה: הגרסה הסופית ומסך הניהול (מתוך scratchpad/new-demos/donate/CASE.md) ----------
T_DON['he'].update(
  s5p='הלקוח בחר בעיצוב 2 ושילם 40%. בניתי את האתר על עיצוב 2, עם שלושה דברים מהעיצובים האחרים: לוח התורמים החרוט מעיצוב 1, שבו כל הסכומים באותו גודל, תמונת הסל מעיצוב 1 בפתיחה, ליד המד, וההזמנה לערב חמישי מעיצוב 3. כולם הותאמו לכחול, לזעפרן ולגופן של עיצוב 2. הוספתי גם מה שביקש בשאלון המשוב: הצהרה ברורה ששמות המשפחות לא נחשפים, ושאין צילומים שלהן. משם הלקוח מאשר, משלם את ה־50% האחרונים, והאתר באוויר. מאותו יום הוא מנהל את הקמפיין לבד.',
  changes=['לוח התורמים החרוט מעיצוב 1, בכחול ובזעפרן: השמות במרכז, וכל הסכומים באותו גודל קטן', 'תמונת הסל מעיצוב 1 בפתיחה, והמד הגדול עליה: רואים מה התרומה קונה', 'ההזמנה לערב חמישי מעיצוב 3, ככרטיס נייר על הרקע הכחול', 'הצהרה ברורה בחלק "לאן הולך הכסף": שמות המשפחות לא נחשפים, ואין צילומים שלהן', 'שאלה חדשה בשאלות הנפוצות: "יש באתר שמות או תמונות של המשפחות?"'])
T_DON['en'].update(
  s5p='The client chose design 2 and paid 40%. I built the site on design 2, with three things from the other designs: the engraved donor board from design 1, where every amount is the same size, the basket picture from design 1 in the opening, next to the meter, and the Thursday evening invitation from design 3. All of them were adapted to the navy, saffron and typeface of design 2. I also added what he asked for in the feedback form: a clear statement that the families\' names are never revealed, and that there are no photos of them. From here the client approves, pays the final 50%, and the site goes live. From that day on he runs the campaign himself.',
  changes=['The engraved donor board from design 1, in navy and saffron: names centered, and every amount in the same small size', 'The basket picture from design 1 in the opening, with the big meter over it: you see what a donation buys', 'The Thursday evening invitation from design 3, as a paper ticket on the navy background', 'A clear statement in the "Where the money goes" section: the families\' names are never revealed, and there are no photos of them', 'A new FAQ question: "Are there names or photos of the families on the site?"'])
DONATE['after'] = admin_step(
  ('מסך ניהול הקמפיין', 'The campaign admin screen'),
  ('בגלל זה בחרנו באתר עם אזור ניהול: תרומות באשראי נכנסות לבד, אבל חלק גדול מהתרומות עדיין מגיע במזומן ובצ׳קים, ויו״ר העמותה רוצה לכתוב עדכון אחרי כל יום חמישי. במסך הזה הוא מוסיף תרומה שהגיעה ביד, כותב עדכון מהשטח, משנה את היעד ואת התאריכים, ורואה מי תרם. מד היעד, הספירה לאחור ורשימת התורמים באתר מתעדכנים מיד.',
   'This is why we chose a website with an admin area: card donations come in by themselves, but much of the giving still arrives as cash and cheques, and the chairman wants to write an update after every Thursday. On this screen he adds a donation that came in by hand, writes a field update, changes the goal and the dates, and sees who gave. The goal meter, the countdown and the donor list on the site update right away.'),
  (['תרומה שהגיעה ביד: מזומן, צ׳ק או העברה, בשם או בעילום שם, עם הקדשה', 'עדכון מהשטח אחרי כל יום חמישי, עם פתיחות מוכנות', 'רשימת התורמים: הסתרת שם או מחיקת תרומה, תמיד עם אישור לפני', 'הקמפיין: כותרת, יעד ותאריכים, ותמונת מצב של היום, השבוע והוראות הקבע', 'קובץ CSV לגזבר, לקבלות ולהנהלת החשבונות'],
   ['A donation that came in by hand: cash, cheque or transfer, named or anonymous, with a dedication', 'A field update after every Thursday, with ready-made openings', 'The donor list: hide a name or delete a donation, always with a confirmation first', 'The campaign: title, goal and dates, plus a snapshot of today, this week and the monthly donors', 'A CSV file for the treasurer, for receipts and bookkeeping']),
  ('מסך ניהול הקמפיין של סל ברכה: תמונת מצב, תרומה שהגיעה ביד והגדרות הקמפיין', 'The Sal Bracha campaign admin screen: a snapshot, a donation that came in by hand, and the campaign settings'),
  ('לנסות את מסך הניהול', 'Try the admin screen'))

# ---------- נחלת יצחק: הגרסה הסופית ומסך הגבאי (מתוך scratchpad/new-demos/shul/CASE.md) ----------
T_SHUL['he'].update(
  s5p='הגבאי בחר בעיצוב 3 ושילם 40%. בניתי את האתר על עיצוב 3, והוספתי בדיוק את מה שביקש מהעיצובים האחרים: את לוח הזמנים מעץ מעיצוב 1, ואת הפס "השבוע בבית הכנסת" ואת כפתור התרומה הקבוע בטלפון מעיצוב 2. שניהם הותאמו לצבעי האבן ולגופנים של עיצוב 3. הוספתי גם את מה שחסר לו: הדלקת נרות וצאת השבת, שהוא מקליד בעצמו כל שבוע. משם הגבאי מאשר, משלם את ה־50% האחרונים, והאתר באוויר. מאותו יום הוא מעדכן לבד את הזמנים, את השיעורים ואת הקמפיין.',
  changes=['לוח זמני התפילות מעץ, כמו הלוח בכניסה, מעיצוב 1: בצבעי האבן והנחושת, והודעת השבוע כפתק מתחתיו', 'הפס "השבוע בבית הכנסת" מעיצוב 2, מתחת לפתיחה: התפילה הבאה, השיעורים של היום וקיר האבנים של הקמפיין', 'בטלפון, כפתור "לתרומה" קבוע בתחתית, עם האחוז שכבר נאסף. הוא נעלם ליד טופס התרומה ובזמן הקלדה', 'הדלקת נרות וצאת השבת בלוח השבת. הגבאי מקליד אותן כל שבוע במסך העדכון, ושדה ריק פשוט לא מופיע', 'ליטוש בטופס: בחירת פריט להקדשה מסמנת את מחיר הפריט, ולא נשאר סכום ישן מודגש'])
T_SHUL['en'].update(
  s5p='The gabbai chose design 3 and paid 40%. I built the site on design 3 and added exactly what he asked for from the other designs: the wooden prayer-times board from design 1, and the "this week at shul" strip and the fixed donate button on phones from design 2. Both were adapted to design 3\'s stone colors and fonts. I also added what he said was missing: candle lighting and the end of Shabbat, which he types in himself every week. From here the gabbai approves, pays the final 50%, and the site goes live. From that day on he updates the times, the classes and the campaign himself.',
  changes=['The wooden prayer-times board from design 1, like the board at the entrance: in stone and copper colors, with the weekly note pinned underneath', 'The "this week at shul" strip from design 2, under the opening: the next prayer, today\'s classes and the campaign\'s stone wall', 'On phones, a "donate" button fixed at the bottom, showing how much has been raised. It hides near the donation form and while typing', 'Candle lighting and the end of Shabbat on the Shabbat board. The gabbai types them in every week on the update screen, and an empty field simply doesn\'t show', 'Polish in the form: picking an item to dedicate selects its price, so no old amount stays highlighted'])
SHUL['after'] = admin_step(
  ('מסך הגבאי', "The gabbai's screen"),
  ('בגלל זה אתר עם אזור ניהול: הזמנים זזים עם השקיעה, השיעורים מתחלפים, והקמפיין חי כל יום. במסך הזה הגבאי מעדכן הכול בעצמו, מהטלפון, בחמש דקות במוצאי שבת. לוחצים "שמירה", והאתר מתעדכן מיד.',
   "This is why it's a website with an admin area: the times move with sunset, the classes change, and the campaign is alive every day. On this screen the gabbai updates everything himself, from his phone, in five minutes on Saturday night. He presses \"save\", and the site updates right away."),
  (['זמני התפילות לימי חול ולשבת, כולל הדלקת נרות וצאת השבת, והודעת השבוע', 'קיצור לשקיעה: מנחה ומעריב 5 דקות מוקדם או מאוחר יותר, בלחיצה אחת', 'שיעורים והודעות לקהילה: הוספה, עריכה ומחיקה', 'הקמפיין: היעד, תאריך הסיום, עדכונים לקהילה, ותרומות שהגיעו במזומן או בצ׳ק', 'הקדשות: מחירים, מה כבר הוקדש, ושמות חדשים בלוח ההנצחה'],
   ['Weekday and Shabbat prayer times, including candle lighting, the end of Shabbat and the weekly note', 'A sunset shortcut: Mincha and Maariv 5 minutes earlier or later, in one tap', 'Classes and community announcements: add, edit and delete', 'The campaign: the goal, the end date, updates for the community, and donations that came in as cash or a cheque', "Dedications: prices, what's already dedicated, and new names on the memorial board"]),
  ('מסך הגבאי: זמני התפילות, השיעורים, הקמפיין וההקדשות, עם תצוגה של האתר בצד', 'The gabbai\'s screen: prayer times, classes, the campaign and dedications, with a preview of the site alongside'),
  ("לנסות את מסך הגבאי", "Try the gabbai's screen"))

# ---------- בית בסדר: הנדימן, דף נחיתה בסיסי (עיצוב אחד, סבב תיקונים אחד) ----------
DESIGNS_BASIC = [
 dict(n=1, he=dict(name='חם ומעשי', text='עיצוב בהיר ופשוט שנבנה קודם לטלפון: נייר חם, כחול אמין וענבר של כלי עבודה. מספר הטלפון בכל מקום, כרטיסי שירות עם הדמיות, ופתק "רשימת התיקונים" שמסמן את עצמו.', fonts='היבו ואסיסטנט'),
      en=dict(name='Warm and practical', text='A light, simple design built for the phone first: warm paper, a trustworthy blue and the amber of work tools. The phone number is everywhere, the service cards have renders, and a little "repair list" note ticks itself off.', fonts='Heebo and Assistant'),
      colors=['#FBF8F3', '#22374B', '#E9A23B', '#1E7A4C']),
]
T_BASIC = {lang: dict(T[lang]) for lang in T}
T_BASIC['he'].update(
  title='דף נחיתה בסיסי לדוגמה: הנדימן לתיקונים קטנים בבית | YYG Web Design',
  desc='הנדימן דמיוני עובר את המסלול הבסיסי של YYG Web Design, ב־1,290 ₪: שאלון, הצעת מחיר, עיצוב אחד עם הטקסטים שלו, סבב תיקונים אחד והגרסה הסופית.',
  h1='דף נחיתה בסיסי להנדימן, עם עיצוב אחד וסבב תיקונים אחד',
  lead='הדוגמה הכי פשוטה, במסלול הבסיסי: הנדימן דמיוני מבית שמש בשם יוסף, "בית בסדר", שרצה את האפשרות הכי זולה שעדיין נראית מקצועית. הוא הביא את הטקסטים בעצמו, קיבל עיצוב אחד, ביקש בסבב התיקונים האחד כמה שינויים קטנים, והדף מוכן. עברנו את כל הדרך כמו עם לקוח אמיתי: שאלון, הצעת מחיר, העיצוב, המשוב והגרסה הסופית.',
  note='עסק דמיוני לדוגמה: השם, הפרטים וההמלצות בדויים. התמונות הן הדמיות תלת־ממד שבניתי במיוחד, במקום צילומי הטלפון של הלקוח. בדף הבסיסי אין עיצוב לוגו, אז השם פשוט מסודר בגופן.',
  nav=['שאלון אפיון', 'הצעת מחיר', 'העיצוב', 'משוב ותיקונים', 'הגרסה הסופית'],
  s1p='יוסף מילא את השאלון בכ־10 דקות, והדביק בו את הטקסטים שכתב בעצמו: כמה משפטים עליו, ורשימת השירותים עם מחירים. היו לו כמה צילומי טלפון של עבודות, וצילום של מגנט עם השם. אלה התשובות שהכי השפיעו:',
  files='מה הכנתי בשבילו: השם בגופן נקי, והדמיות במקום צילומי הטלפון',
  s2p='הבונה שלי זיהה מהשאלון דף נחיתה בסיסי: 5 חלקים, הטקסטים מוכנים, ושום דבר שצריך לעדכן. אין כאן תוספות, ולוגו לא כלול, אז השם מסודר בגופן. עברתי על הטיוטה, ויוסף קיבל במייל הצעה מסודרת עם אישור בלחיצה. בבסיסי משלמים 50% כדי להתחיל, ו־50% בסיום.',
  s3='עיצוב אחד, עם הטקסטים שלו',
  s3p='בבסיסי מכינים כיוון אחד, ולא 3. בחרתי כיוון חם ומעשי: נייר בהיר, כחול אמין וענבר של כלי עבודה, ומספר הטלפון בכל מקום שהעין מגיעה אליו. הטקסטים הם שלו, עם עריכה קלה, והדף נבנה קודם לטלפון:',
  s4='המשוב וסבב התיקונים האחד',
  s4p='יוסף עבר על העיצוב, גם בטלפון, ומילא את שאלון המשוב. בבסיסי יש סבב תיקונים אחד, אז הוא ריכז בו הכול:',
  s5p='יוסף אישר את העיצוב, ותיקנתי בדיוק את מה שביקש בסבב האחד. משם הוא בודק ומאשר, משלם את ה־50% האחרונים, והדף עולה לאוויר. כל העבודה לקחה 3 ימי עבודה, מהיום שהטקסטים והתמונות הגיעו.',
  changes=['כפתור החיוג בפתיחה גדול יותר, עם "להתקשר ליוסף" והמספר בגדול', 'בטלפון: פס חיוג ווואטסאפ שנשאר תמיד בתחתית המסך', 'רמת בית שמש נוספה לאזורי השירות, וגם לטופס', 'המחירון ירד. במקומו: "כמה זה עולה?", עם כפתור לשליחת תמונה בוואטסאפ', 'בפתיחה ארגז כלים במקום המקדחה, שנראתה לו כמו שיפוצים גדולים'],
  open='לפתוח את העיצוב', rating='כמה העיצוב מרגיש נכון לעסק', took='מה הוא אהב, ומה פחות',
  ctap='מתחילים בשאלון של 10 דקות, או בשיחה קצרה. בדף הבסיסי משלמים 50%, ומקבלים עיצוב אחד עם הטקסטים שלכם.',
)
T_BASIC['en'].update(
  title='Example basic landing page: a handyman for small home repairs | YYG Web Design',
  desc='A fictional handyman goes through the YYG Web Design basic package, for ₪1,290: a questionnaire, a quote, one design with his own texts, one revision round and the final version.',
  h1='A basic landing page for a handyman, with one design and one revision round',
  lead='The simplest example, on the basic package: a fictional handyman from Beit Shemesh named Yosef, "Bayit Beseder", who wanted the cheapest option that still looks professional. He brought his own texts, got one design, asked for a few small changes in his one revision round, and the page is ready. We went all the way as with a real client: a questionnaire, a quote, the design, the feedback and the final version.',
  note='A fictional example business: the name, details and testimonials are made up. The images are 3D renders I built specially, in place of the client\'s phone photos. The basic page has no logo design, so the name is simply set in a font.',
  nav=['Questionnaire', 'Quote', 'The design', 'Feedback and revisions', 'Final version'],
  s1p='Yosef filled in the questionnaire in about 10 minutes, and pasted in the texts he wrote himself: a few sentences about him, and the list of services with prices. He had a few phone photos of his work, and a photo of a magnet with the name. These are the answers that shaped the work most:',
  files='What I prepared for him: the name in a clean font, and renders in place of the phone photos',
  s2p='From the questionnaire, my builder picked a basic landing page: 5 sections, the texts ready, and nothing that needs updating. There are no add-ons, and a logo isn\'t included, so the name is set in a font. I went over the draft, and Yosef got a formal quote by email with one-click approval. On the basic package you pay 50% to start, and 50% at the end.',
  s3='One design, with his own texts',
  s3p='On the basic package I prepare one direction, not 3. I chose a warm, practical one: light paper, a trustworthy blue and the amber of work tools, with the phone number wherever the eye lands. The texts are his, lightly edited, and the page was built for the phone first:',
  s4='The feedback and the one revision round',
  s4p='Yosef went over the design, on his phone too, and filled in the feedback form. The basic package has one revision round, so he put everything into it:',
  s5p='Yosef approved the design, and I changed exactly what he asked for in the one round. From here he checks and approves it, pays the final 50%, and the page goes live. The whole job took 3 working days, from the day the texts and photos arrived.',
  changes=['A bigger call button at the top, with "call Yosef" and the number in large type', 'On the phone: a call and WhatsApp bar that always stays at the bottom of the screen', 'Ramat Beit Shemesh added to the service areas, and to the form too', 'The price list is gone. In its place: "How much does it cost?", with a button to send a photo on WhatsApp', 'A toolbox at the top instead of the drill, which looked to him like big renovations'],
  open='Open the design', rating='How right the design feels for the business', took='What he liked, and what less',
  ctap='Start with a 10-minute questionnaire, or a short call. On the basic page you pay 50%, and get one design with your own texts.',
)
QUOTE_BASIC = {
 'he': dict(items=[('דף נחיתה בסיסי', 1290)],
   incl=['עמוד אחד, עד 5 חלקים', 'הטקסטים מגיעים מכם, עם עריכה קלה שלי', 'טופס פנייה, כפתור חיוג וכפתור וואטסאפ', 'השם שלכם מסודר בגופן נקי בראש הדף', 'דומיין ואחסון לשנה הראשונה', 'התאמה לטלפון, לגוגל ולנגישות, והצהרת נגישות'],
   terms=['עיצוב אחד', 'סבב תיקונים אחד', '3 ימי עבודה לאספקה', 'חודש ליווי'],
   pay=[(50, 'כדי להתחיל, לפני העיצוב'), (50, 'בסיום, אחרי האישור שלכם ולפני העלייה לאוויר')], annual=QUOTE['he']['annual'],
   notes=['סימן 5 חלקים: פתיחה, שירותים, אודות, המלצות ויצירת קשר. אזורי השירות נכנסו לחלק של אודות, אז זה בדיוק מה שכלול בבסיסי.', 'לוגו לא כלול בבסיסי. יש לו רק צילום של מגנט, אז אסדר את השם בגופן נקי. אם ירצה בהמשך, לוגו בסיסי עולה 590 ₪.', 'לוח זמנים: תוך שבועיים, ו־3 ימי עבודה מהיום שהטקסטים והתמונות אצלי. צריך לרשום דומיין עם השם בית בסדר, וזה כלול בשנה הראשונה. התקציב שציין: עד 2,000 ₪.']),
 'en': dict(items=[('Basic landing page', 1290)],
   incl=['One page, up to 5 sections', 'You provide the texts, and I edit them lightly', 'A contact form, a call button and a WhatsApp button', 'Your name set in a clean font at the top of the page', 'Domain and hosting for the first year', 'Mobile, Google and accessibility ready, with an accessibility statement'],
   terms=['One design', 'One revision round', '3 working days to deliver', 'One month of support'],
   pay=[(50, 'To start, before the design'), (50, 'At the end, after your approval and before going live')], annual=QUOTE['en']['annual'],
   notes=['Marked 5 sections: the opening, services, about, testimonials and contact. The service areas went into the about section, so this is exactly what the basic package includes.', 'A logo isn\'t included in the basic package. He only has a photo of a magnet, so I\'ll set the name in a clean font. If he wants one later, a basic logo costs ₪590.', 'Timing: within two weeks, and 3 working days from the day I have the texts and photos. A domain with the name Bayit Beseder needs registering, and that\'s included in the first year. Stated budget: up to ₪2,000.']),
}
BASIC = dict(prefix='demo/basic/', data=data_of('basic'), designs=DESIGNS_BASIC, T=T_BASIC, quote=QUOTE_BASIC, extra=None, after=None, no_admin=True,
  hi=hi_generic([
    ('העסק', 'The business', lambda a, he: a('intake', 'biz_what')),
    ('מה מייחד אותו', 'What sets him apart', lambda a, he: a('intake', 'biz_unique')),
    ('מה לקוחות שואלים הכי הרבה', 'What customers ask most', lambda a, he: a('intake', 'cust_questions')),
    ('איך הוא יידע שהדף הצליח', "How he'll know the page works", lambda a, he: a('intake', 'goal_success')),
    ('מה הוא ביקש', 'What he asked for', lambda a, he: a('intake', 'extra')),
    ('מה לא רוצים לראות', "What he doesn't want to see", lambda a, he: a('intake', 'dislikes')),
    ('3 מילים שהאתר צריך לשדר', '3 words the site should convey', lambda a, he: a('intake', 'feel') + '. ' + ('רקע: ' if he else 'Background: ') + a('intake', 'bg') + '.'),
    ('מסלול, תקציב וזמנים', 'Package, budget and timing', lambda a, he: pkg_line(a, he)),
  ]),
  files=files_from([('beseder-wordmark-sm.webp', 'השם של בית בסדר, מסודר בגופן היבו', 'The Bayit Beseder name, set in Heebo', 293),
                    ('toolbox-560.webp', 'הדמיה: ארגז כלים מעץ', 'Render: a wooden toolbox', 112),
                    ('shelf-560.webp', 'הדמיה: מדף עץ על הקיר', 'Render: a wooden wall shelf', 112),
                    ('handle-560.webp', 'הדמיה: ידית דלת', 'Render: a door handle', 112),
                    ('tap-560.webp', 'הדמיה: ברז מטבח', 'Render: a kitchen tap', 112)]),
  card={'he': ('דף נחיתה בסיסי להנדימן', 'עיצוב אחד, סבב תיקונים אחד, והטקסטים מהלקוח'),
        'en': ('A basic landing page for a handyman', "One design, one revision round, and the client's own texts")})
