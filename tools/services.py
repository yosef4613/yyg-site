# יוצר את עמודי השירות (קידום בגוגל לפי מילות מפתח) בתיקייה services/
# שימוש: python3 tools/services.py
import html, json, os, pathlib, re

ROOT = pathlib.Path(__file__).resolve().parent.parent
SITE = 'https://yygweb.com'
TODAY = '2026-11-25'
# אותו עיצוב כמו במדריכים, נקרא מתוך guides.py בלי להריץ אותו
STYLE = re.search(r"STYLE = '''(.*?)'''", (ROOT / 'tools' / 'guides.py').read_text(), re.S).group(1)
os.makedirs(ROOT / 'services', exist_ok=True)

COMMON_INCL = [
    '3 כיווני עיצוב מלאים לפני תשלום, עם התוכן שלכם',
    '3 סבבי תיקונים',
    'כל הטקסטים: נכתבים בעזרת AI, עם עריכה אישית שלי ואישור שלכם',
    'התאמה מלאה לטלפון וטעינה מהירה',
    'הכנה מלאה לגוגל: כותרות, תיאורים, מפת אתר, נתונים מובנים ורישום בכלי של גוגל',
    'התאמות נגישות והצהרת נגישות',
    'דומיין, אחסון ומייל עסקי לשנה הראשונה. הדומיין רשום על שמכם',
    '4 חודשי ליווי אחרי ההשקה',
]
HOW = '''<h2>איך זה עובד</h2>
<ol>
<li><b>שיחה קצרה</b>: מה העסק עושה, למי, ומה האתר צריך להשיג.</li>
<li><b>3 עיצובים לפני תשלום</b>: עם התוכן שלכם. גוללים בהם בטלפון כמו באתר אמיתי.</li>
<li><b>בוחרים כיוון</b>: משלמים 50% וממשיכים לבנייה.</li>
<li><b>בנייה ובדיקה</b>: אתם בודקים ומאשרים, ומשלמים את היתרה לפני העלייה לאוויר.</li>
<li><b>עלייה לאוויר והדרכה</b>, ו־4 חודשי ליווי.</li>
</ol>'''


def ul(items):
    return '<ul>' + ''.join(f'<li>{i}</li>' for i in items) + '</ul>'


PAGES = [
    dict(
        slug='landing-page', kw='בניית דף נחיתה',
        title='בניית דף נחיתה לעסק מ־2,000 ₪ | 3 עיצובים לפני תשלום | YYG',
        desc='בניית דף נחיתה שמביא לקוחות: 3 עיצובים לפני תשלום, 3 סבבי תיקונים, באוויר תוך 4 ימי עבודה. מ־2,000 ₪, מחיר סופי בלי מע״מ, כולל שנה ראשונה של דומיין ואחסון.',
        h1='בניית דף נחיתה שמביא לקוחות', lead='עמוד אחד, ממוקד ומהיר, שכל מה שבו מוביל לפעולה אחת: שיתקשרו אליכם או ישאירו פרטים.',
        price='2,000', price_n=2000, service='בניית דף נחיתה',
        body='''<h2>למי מתאים דף נחיתה</h2>
<ul>
<li>עסק עם <b>שירות אחד מרכזי</b> או מבצע מסוים.</li>
<li>מי שמפרסם בפלאייר, במודעה או בגוגל, ורוצה שכל מי שמגיע יעשה דבר אחד.</li>
<li>עסק חדש שרוצה <b>להתחיל מהר</b> ובתקציב נמוך, ולהרחיב לאתר מלא בהמשך.</li>
</ul>
<h2>מה עושה דף נחיתה טוב</h2>
<ul>
<li><b>כותרת אחת ברורה</b>: למי זה, ומה מקבלים.</li>
<li><b>הוכחה</b>: עבודות, המלצות או מספרים, לפני שמבקשים משהו.</li>
<li><b>כפתור אחד</b> שבולט, וחוזר בסוף העמוד.</li>
<li><b>מהירות בטלפון</b>: רוב הגולשים בטלפון, וכל שנייה של המתנה עולה בלקוחות.</li>
</ul>
<h2>מה כלול</h2>
{incl}
<p>עמוד אחד עם עד 7 חלקים, טופס פנייה וכפתור חיוג שתמיד נראה. <b>זמן אספקה: עד 4 ימי עבודה</b> מהיום שמקבלים את החומרים.</p>
<h2>מחיר</h2>
<p><b>מ־2,000 ₪</b>, מחיר סופי בלי מע״מ. המחיר הסופי נקבע לפי מה שהדף צריך, ומופיע בהצעה מסודרת לפני שמתחילים. גרסה בשפה נוספת: 500 ₪.</p>
{how}''',
        faq=[('כמה זמן לוקח לבנות דף נחיתה?', 'עד 4 ימי עבודה מהיום שמקבלים את כל החומרים, לא כולל ימים שמחכים לתשובה שלכם.'),
             ('אפשר להרחיב את דף הנחיתה לאתר מלא בהמשך?', 'כן. דף הנחיתה הופך לעמוד הבית או לאחד העמודים של האתר, ולא בונים מאפס.'),
             ('דף נחיתה מופיע בגוגל?', 'כן. הוא נבנה מוכן לגוגל, אבל בדרך כלל מופיע על פחות חיפושים מאתר מלא, כי יש בו פחות עמודים.'),
             ('מה צריך להכין?', 'לוגו, משפט על העסק, השירות שאתם רוצים לקדם, ותמונות אם יש. את הטקסטים המלאים אני כותב.')],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='business-website', kw='בניית אתר לעסק',
        title='בניית אתר לעסק עם אזור ניהול מ־4,000 ₪ | 3 עיצובים לפני תשלום | YYG',
        desc='בניית אתר תדמית לעסק עם אזור ניהול לעדכון עצמי: עד 6 עמודים, 3 עיצובים לפני תשלום, באוויר תוך 10 ימי עבודה. מ־4,000 ₪, מחיר סופי בלי מע״מ.',
        h1='בניית אתר לעסק, שאתם מעדכנים לבד', lead='אתר תדמית של כמה עמודים, עם אזור ניהול פשוט לעדכון מה שמשתנה: שירותים, מחירים, מבצעים ותמונות.',
        price='4,000', price_n=4000, service='בניית אתר תדמית לעסק',
        body='''<h2>למי מתאים אתר עם אזור ניהול</h2>
<ul>
<li>עסק עם <b>כמה שירותים</b>, שכל אחד צריך הסבר משלו.</li>
<li>מי שרוצה <b>להופיע בגוגל על כמה חיפושים</b>: לכל עמוד יש סיכוי משלו.</li>
<li>מי שמעדכן תוכן: מבצעים, מתכונים, פרויקטים, חדשות או לוח זמנים.</li>
</ul>
<h2>מה כלול</h2>
{incl}
<p>עד 6 עמודים, אזור ניהול לעדכון התוכן שמשתנה, טפסים ומדור תוכן, והדרכה אישית עם מדריך כתוב. <b>זמן אספקה: עד 10 ימי עבודה</b>.</p>
<h2>דוגמה מהעבודה שלי</h2>
<p>לקייטרינג הבוטיק "הכיף שבאפיה" בניתי אתר עם קטלוג לפי קטגוריות, מתכנן בר ששולח לעסק בקשה מסודרת, ותבנית מתכונים שבעלת העסק מעדכנת לבד. האתר החדש נפתח בטלפון פי שלושה מהר יותר מהקודם. <a class="text-link" href="/#work">לסיפור המלא</a></p>
<h2>מחיר</h2>
<p><b>מ־4,000 ₪</b>, מחיר סופי בלי מע״מ. עמוד נוסף מעבר ל־6: 350 ₪. גרסה בשפה נוספת: 1,000 ₪.</p>
{how}''',
        faq=[('כמה זמן לוקח לבנות אתר לעסק?', 'עד 10 ימי עבודה מהיום שמקבלים את כל החומרים.'),
             ('אני אוכל לעדכן את האתר לבד?', 'כן. יש אזור ניהול פשוט לעדכון התוכן שמשתנה, ואני מדריך אתכם בשיחה אישית ובמדריך כתוב.'),
             ('על שם מי האתר והדומיין?', 'על שמכם. האתר, הדומיין והתוכן שייכים לכם, ואפשר לעבור לחברה אחרת בכל זמן.'),
             ('יש תשלום חודשי?', 'לא. השנה הראשונה של דומיין, אחסון ומייל עסקי כלולה, ואחר כך 590 ₪ לשנה אם רוצים שאני אדאג להכול.')],
        related=['landing-page', 'online-store'],
    ),
    dict(
        slug='online-store', kw='בניית חנות אינטרנטית',
        title='בניית חנות אינטרנטית מ־7,000 ₪ | עד 30 מוצרים כלולים | YYG',
        desc='בניית חנות אינטרנטית עם עגלה, תשלום באשראי ומשלוחים, וניהול מוצרים, מלאי והזמנות. עד 30 מוצרים שאני מכניס, 3 עיצובים לפני תשלום. מ־7,000 ₪, מחיר סופי בלי מע״מ.',
        h1='בניית חנות אינטרנטית שקל לקנות בה', lead='קטלוג, עגלה ותשלום באשראי, עם ניהול פשוט של מוצרים, מלאי והזמנות. בנויה לטלפון, כי שם קונים.',
        price='7,000', price_n=7000, service='בניית חנות אינטרנטית',
        body='''<h2>מה כלול בחנות</h2>
{incl}
<ul>
<li>עגלת קניות ותשלום באשראי (העמלות של חברת הסליקה בנפרד).</li>
<li>משלוחים, החזרות, תקנון ופרטיות.</li>
<li>ניהול מוצרים, מלאי והזמנות, עם הדרכה אישית.</li>
<li><b>עד 30 מוצרים שאני מכניס בשבילכם.</b> כל 10 מוצרים נוספים: 200 ₪.</li>
</ul>
<p><b>זמן אספקה: עד 20 ימי עבודה.</b> כדאי לפתוח חשבון אצל חברת סליקה כבר בהתחלה, כי האישור לוקח כמה ימים.</p>
<h2>מחיר</h2>
<p><b>מ־7,000 ₪</b>, מחיר סופי בלי מע״מ. גרסה בשפה נוספת: 1,800 ₪.</p>
{how}''',
        faq=[('כמה עולה לבנות חנות אינטרנטית?', 'אצלי מ־7,000 ₪, מחיר סופי בלי מע״מ, כולל עד 30 מוצרים שאני מכניס, ושנה ראשונה של דומיין, אחסון ומייל עסקי.'),
             ('איך מקבלים תשלום באשראי?', 'דרך חברת סליקה ישראלית. פותחים אצלה חשבון, ואני מחבר אותה לחנות. העמלות שלה משולמות לה ישירות.'),
             ('אוכל להוסיף מוצרים לבד?', 'כן. יש ניהול פשוט של מוצרים, מלאי והזמנות, ואני מדריך אתכם עליו.')],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='institutions', kw='בניית אתר למוסד',
        title='בניית אתר למוסד, עמותה או חוג, עם הרשמה ורשימת תפוצה | YYG',
        desc='בניית אתר למוסדות, עמותות וחוגים: טופס הרשמה, חשבונות משתמש, רשימת תפוצה ו־3 תבניות מייל. תוכן ותמונות שמתאימים לקהל. מ־6,000 ₪, מחיר סופי בלי מע״מ.',
        h1='בניית אתר למוסד, עמותה או חוג', lead='אתר שמציג את הפעילות ומקבל הרשמות מסודרות: הורים ומשתתפים מוצאים מה מתאים להם, ונרשמים ישר מהטלפון.',
        price='6,000', price_n=6000, service='בניית אתר למוסד, עמותה או חוג',
        body='''<h2>מה צריך אתר של מוסד</h2>
<ul>
<li><b>למצוא מהר</b>: חוג, שיעור או פעילות לפי גיל, מקום או נושא.</li>
<li><b>להירשם בקלות</b>: טופס הרשמה מלא, עם אישור במייל.</li>
<li><b>לעדכן את כולם</b>: רשימת תפוצה עם הסכמה, ותבניות מייל מוכנות.</li>
<li><b>תוכן ותמונות שמתאימים לקהל</b>, ואתר נקי שקל לאשר בסינונים.</li>
</ul>
<h2>מה כלול</h2>
{incl}
<p>עד 8 עמודים, הרשמה, חשבונות משתמש, רשימת תפוצה ו־3 תבניות מייל. אזור ניהול לתוכן, לפניות ולמנויים. <b>זמן אספקה: עד 15 ימי עבודה</b>.</p>
<h2>דוגמה מהעבודה שלי</h2>
<p>למקהלת הילדים "פרחי שמיים" בניתי אתר עם חיפוש חוג לפי גיל הילד ועיר, טופס הרשמה מלא עם אפשרות לפתוח חשבון, ספריית שירים עם נגן שלא מאט את האתר, וגרסה באנגלית. לפני הבנייה הם ראו חמישה עיצובים ובחרו. <a class="text-link" href="/#work">לעבודה המלאה</a></p>
<h2>מחיר</h2>
<p><b>מ־6,000 ₪</b>, מחיר סופי בלי מע״מ. גרסה בשפה נוספת: 1,500 ₪.</p>
{how}''',
        faq=[('אפשר לקבל הרשמות ותשלומים באתר?', 'הרשמות כן, כולל אישור במייל. אפשר גם לחבר תשלום באשראי דרך חברת סליקה, בתוספת שמופיעה בהצעת המחיר.'),
             ('האתר יתאים לציבור שומר תורה ומצוות?', 'כן. התוכן והתמונות נבחרים כך שיתאימו לקהל, והאתר בנוי נקי ופשוט, כך שקל לאשר אותו בסינונים.'),
             ('אפשר לשלוח עדכונים לרשומים?', 'כן. יש רשימת תפוצה עם הסכמה ו־3 תבניות מייל מוכנות, ואתם שולחים לבד.')],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='logo-branding', kw='עיצוב לוגו ומיתוג לעסק',
        title='עיצוב לוגו ומיתוג לעסק מ־590 ₪ | מותג מלא עם אתר | YYG',
        desc='עיצוב לוגו בסיסי ב־590 ₪, לוגו מקצועי עם 3 סקיצות ב־1,500 ₪, מיתוג בסיסי ב־2,500 ₪, ומותג מלא מ־0 עד 100% עם אתר, תוכן, דפוס וגוגל מ־5,900 ₪. מחירים סופיים, בלי מע״מ.',
        h1='עיצוב לוגו ומיתוג לעסק', lead='מלוגו פשוט לעסק שמתחיל, ועד מותג שלם: לוגו, צבעים, פונטים, אתר ותוכן, הכול באותה שפה.',
        price='590', price_n=590, service='עיצוב לוגו ומיתוג',
        body='''<h2>לוגו, מיתוג או מותג מלא: מה ההבדל?</h2>
<p><b>לוגו</b> הוא הסימן של העסק. <b>מיתוג</b> הוא איך שהעסק נראה בכל מקום: לוגו, צבעים, פונטים וכרטיס ביקור. <b>מותג מלא</b> הוא הכול מוכן: מיתוג, אתר, תוכן, דפוס, רשתות וגוגל, ואתם רק מאשרים.</p>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>מה</th><th>מחיר</th><th>מה כלול</th></tr></thead>
<tbody>
<tr><td>לוגו בסיסי</td><td>590 ₪</td><td>סקיצה אחת, סבב תיקונים אחד, קבצים לאתר ולרשתות</td></tr>
<tr><td>לוגו מקצועי</td><td>1,500 ₪</td><td>3 סקיצות, 3 סבבי תיקונים, כל הקבצים: וקטור, רקע שקוף, צבע אחד ואייקון</td></tr>
<tr><td>מיתוג בסיסי</td><td>2,500 ₪</td><td>לוגו מקצועי, צבעים, פונטים, דף הנחיות וכרטיס ביקור</td></tr>
<tr><td>מותג מלא + דף נחיתה</td><td>מ־5,900 ₪</td><td>מיתוג, אתר, כל התוכן, כרטיס ביקור ופלאייר, תמונות לרשתות, גוגל ו־4 חודשי ליווי</td></tr>
<tr><td>מותג מלא + אתר</td><td>מ־7,900 ₪</td><td>כמו למעלה, עם אתר עם אזור ניהול (עם הרשמה מ־9,900 ₪, עם חנות מ־10,900 ₪)</td></tr>
</tbody></table></div>
<p>כל המחירים סופיים, בלי מע״מ.</p>
<h2>למה כדאי להתחיל מהלוגו</h2>
<p>האתר מעוצב סביב הלוגו: ממנו נגזרים הצבעים, הפונטים והתחושה. כשמתחילים מהמיתוג, האתר, הכרטיס והשלט נראים כמו עסק אחד, ולא כמו אוסף של דברים.</p>
<h2>איך זה עובד</h2>
<ol>
<li><b>שיחת אפיון</b>: מה העסק עושה, למי, מה מייחד אתכם, ואילו צבעים וסגנון אתם אוהבים.</li>
<li><b>סקיצות</b>: כיוון אחד בלוגו בסיסי, 3 כיוונים בלוגו מקצועי ובמיתוג.</li>
<li><b>בחירה ותיקונים</b>, ואז קבצים מוכנים לכל שימוש.</li>
</ol>''',
        faq=[('כמה עולה לעצב לוגו?', 'לוגו בסיסי עם סקיצה אחת עולה 590 ₪. לוגו מקצועי עם 3 סקיצות, 3 סבבי תיקונים וכל הקבצים עולה 1,500 ₪. מחירים סופיים, בלי מע״מ.'),
             ('מה ההבדל בין לוגו למיתוג?', 'לוגו הוא הסימן. מיתוג הוא כל השפה: לוגו, צבעים, פונטים, דף הנחיות וכרטיס ביקור, כדי שהעסק ייראה אותו דבר בכל מקום.'),
             ('מה זה מותג מ־0 עד 100%?', 'אתם מביאים את הרעיון של העסק, ואני בונה את כל השאר: מיתוג, אתר, כל התוכן, דפוס, רשתות ופרופיל עסק בגוגל.'),
             ('צריך שם לעסק לפני?', 'כן. החבילות מניחות שיש שם לעסק. המצאת שם היא עבודה נפרדת, בהצעת מחיר.')],
        related=['landing-page', 'business-website'],
    ),
]
NAMES = {p['slug']: p['h1'] for p in PAGES}


def page(p, is_index=False, cards=''):
    slug = 'index' if is_index else p['slug']
    url = f'{SITE}/services/' if is_index else f'{SITE}/services/{slug}.html'
    en_url = url.replace(f'{SITE}/services/', f'{SITE}/en/services/')
    title, desc, h1, lead = p['title'], p['desc'], p['h1'], p['lead']
    ld = []
    if not is_index:
        ld.append({'@context': 'https://schema.org', '@type': 'Service', 'name': p['service'], 'serviceType': p['kw'],
                   'description': desc, 'url': url, 'areaServed': {'@type': 'Country', 'name': 'ישראל'}, 'inLanguage': 'he',
                   'provider': {'@type': 'ProfessionalService', 'name': 'YYG', 'url': SITE + '/', 'telephone': '+972-54-854-0700'},
                   'offers': {'@type': 'Offer', 'price': str(p['price_n']), 'priceCurrency': 'ILS', 'url': url}})
        ld.append({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': [
            {'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}} for q, a in p['faq']]})
    ld.append({'@context': 'https://schema.org', '@type': 'BreadcrumbList', 'itemListElement': [
        {'@type': 'ListItem', 'position': 1, 'name': 'YYG', 'item': SITE + '/'},
        {'@type': 'ListItem', 'position': 2, 'name': 'שירותים', 'item': SITE + '/services/'}]
        + ([] if is_index else [{'@type': 'ListItem', 'position': 3, 'name': h1, 'item': url}])})
    if is_index:
        body = cards
    else:
        body = p['body'].replace('{incl}', ul(COMMON_INCL)).replace('{how}', HOW)
        body += '\n<h2>שאלות נפוצות</h2>\n' + '\n'.join(f'<details><summary>{html.escape(q)}</summary><p>{html.escape(a)}</p></details>' for q, a in p['faq'])
        body += '<aside class="g-rel" aria-labelledby="rel-t"><h2 id="rel-t">עוד שירותים</h2><ul>' + ''.join(
            f'<li><a class="text-link" href="/services/{s}.html">{NAMES[s]}</a></li>' for s in p['related']) + \
            '<li><a class="text-link" href="/guides/website-cost.html">כמה עולה לבנות אתר לעסק?</a></li></ul></aside>'
    return f'''<!doctype html>
<html lang="he" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<script>if(location.protocol==="http:"&&location.hostname==="yygweb.com")location.replace("https://yygweb.com"+location.pathname+location.search+location.hash)</script>
<title>{html.escape(title)}</title>
<meta name="description" content="{html.escape(desc)}">
<link rel="canonical" href="{url}">
<link rel="alternate" hreflang="he" href="{url}">
<link rel="alternate" hreflang="en" href="{en_url}">
<link rel="alternate" hreflang="x-default" href="{url}">
<meta property="og:type" content="website">
<meta property="og:locale" content="he_IL">
<meta property="og:url" content="{url}">
<meta property="og:title" content="{html.escape(h1)}">
<meta property="og:description" content="{html.escape(desc)}">
<meta property="og:image" content="{SITE}/assets/img/og.jpg">
<meta name="theme-color" content="#151920">
<link rel="icon" href="/assets/img/icon-64.png" type="image/png">
<link rel="preload" href="/assets/fonts/karantina-700-hebrew.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/site.css?v=34">
{STYLE}
<script type="application/ld+json">{json.dumps(ld, ensure_ascii=False, separators=(',', ':'))}</script>
</head>
<body>
<a class="skip" href="#main">דילוג לתוכן</a>
<header class="g-top">
 <div class="wrap">
  <a href="/"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="YYG, לעמוד הבית"></a>
  <nav aria-label="ניווט">
   <a class="text-link" href="/services/">שירותים</a>
   <a class="text-link" href="/#plan">מחשבון מחיר</a>
   <a class="text-link" href="/#contact">יצירת קשר</a>
   <a class="text-link" href="{en_url.replace(SITE, '')}" hreflang="en" lang="en">English</a>
  </nav>
 </div>
</header>
<main id="main" class="g-main">
 <div class="wrap">
  <p class="g-crumb"><a href="/">YYG</a> › <a href="/services/">שירותים</a></p>
  <h1>{h1}</h1>
  <p class="g-lead">{lead}</p>
{body}
  <section class="g-cta" aria-labelledby="cta-t">
   <h2 id="cta-t">רוצים לדעת כמה זה יעלה לכם?</h2>
   <p>3 שאלות קצרות במחשבון, בלי להשאיר פרטים. או השאירו פרטים, ואחזור אליכם בימים א׳ עד ד׳, בין 10:00 ל־13:30.</p>
   <a class="btn btn-gold" href="/?src=svc-{slug}#plan">למחשבון המחיר</a>
   <p style="margin-top:12px"><a class="text-link" href="/#contact">או להשאיר פרטים</a> · <a class="text-link" href="tel:0548540700">054-854-0700</a> (שיחות בלבד)</p>
  </section>
 </div>
</main>
<footer class="foot">
 <div class="wrap foot-in">
  <p class="foot-brand"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="" loading="lazy"> YYG, יהודה גורדון. עיצוב ובניית אתרים</p>
  <p><a class="foot-link" href="/services/">שירותים</a> · <a class="foot-link" href="/guides/">מדריכים</a> · <a class="foot-link" href="/accessibility.html">הצהרת נגישות</a></p>
 </div>
</footer>
<script>if(location.hostname==="yygweb.com"&&navigator.sendBeacon){{try{{var q=new URLSearchParams(location.search);navigator.sendBeacon("/hit.php",new Blob([JSON.stringify({{e:"view",p:location.pathname,r:document.referrer,s:q.get("src")||""}})],{{type:"application/json"}}))}}catch(e){{}}}}</script>
</body>
</html>
'''


for p in PAGES:
    (ROOT / 'services' / f"{p['slug']}.html").write_text(page(p))
cards = '<div class="g-cards">\n' + '\n'.join(
    f'<a class="g-card" href="/services/{p["slug"]}.html"><b>{p["h1"]}</b><span>{p["lead"]}</span></a>' for p in PAGES) + '\n</div>'
(ROOT / 'services' / 'index.html').write_text(page(dict(title='שירותים: בניית אתרים, חנויות, לוגו ומיתוג | YYG',
    desc='כל השירותים של YYG: בניית דף נחיתה, אתר לעסק, חנות אינטרנטית, אתר למוסד, עמותה או חוג, ועיצוב לוגו ומיתוג. 3 עיצובים לפני תשלום, מחירים סופיים בלי מע״מ.',
    h1='שירותים', lead='בניית אתרים, חנויות, לוגו ומיתוג לעסקים קטנים ולמוסדות. בכל שירות רואים את העיצוב לפני שמשלמים.'), True, cards))
print('ok', [p['slug'] for p in PAGES])
