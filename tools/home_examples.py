#!/usr/bin/env python3
"""YYG: החלק "פרויקטים לדוגמה" בדף הבית (עברית ואנגלית), לפי סדר המסלולים ומחיר ההצעה.
פרויקט נכנס רק כשיש לו גרסה סופית, מסך ניהול וצילומים (כמו ב־tools/demo_projects.py).
מחליף את מה שבין <!-- examples:start --> ל־<!-- examples:end -->. הרצה: python3 tools/home_examples.py"""
import html, pathlib

ROOT = pathlib.Path(__file__).resolve().parent.parent
E = lambda s: html.escape(str(s), quote=True)

# לפי סדר המחיר: דף נחיתה, אתר, אתר עם תרומות, חנות
CARDS = [
 dict(slug='basic', price=1290, admin=False,
      he=dict(kind='דף נחיתה בסיסי', name='הנדימן: תיקונים קטנים בבית', text='המסלול הכי חסכוני: עיצוב אחד, סבב תיקונים אחד, והטקסטים מהלקוח. רואים את העיצוב, את המשוב ואת הגרסה הסופית אחרי הסבב.', admin=''),
      en=dict(kind='Basic landing page', name='A handyman: small home repairs', text='The most affordable package: one design, one revision round, and the client brings the texts. See the design, the feedback and the final version after the round.', admin='')),
 dict(slug='landing', price=2000, admin=False,
      he=dict(kind='דף נחיתה', name='יועץ החזרי מס למשפחות', text='דף נחיתה במחיר הבסיס, בלי תוספות: 3 עיצובים, טופס בדיקת זכאות וכפתור חיוג. שינויים קטנים עליי בתקופת הליווי, בלי מסך ניהול.', admin=''),
      en=dict(kind='Landing page', name='A tax-refund advisor for families', text='A landing page at the base price, with no add-ons: 3 designs, an eligibility form and a call button. Small changes are on me during the support period, with no admin screen.', admin='')),
 dict(slug='optika', price=3390,
      he=dict(kind='דף נחיתה', name='צלול: חנות משקפיים', text='דף נחיתה, ומסך קטן שבו בעל החנות מעדכן לבד את שעות הפתיחה והמבצע מהטלפון. הדף מראה "פתוח עכשיו" לפי השעון.', admin='למסך השעות'),
      en=dict(kind='Landing page', name='Tzalul: an eyewear shop', text='A landing page, plus a small screen where the owner updates the opening hours and the promotion himself from his phone. The page shows "open now" by the clock.', admin='The hours screen')),
 dict(slug='', price=4000,
      he=dict(kind='אתר עם אזור ניהול', name='ענף: נגריה לרהיטים בהתאמה', text='שאלון של 60 תשובות, שלושה כיוונים שונים, גרסה סופית לפי המשוב, ומסך שבו בעל הנגריה מוסיף עבודות לגלריה.', admin='למסך הניהול'),
      en=dict(kind='Website with an admin area', name='Anaf: a custom furniture carpentry shop', text='A 60-answer questionnaire, three different directions, a final version based on the feedback, and a screen where the owner adds projects to the gallery.', admin='The admin screen')),
 dict(slug='donate', price=6000,
      he=dict(kind='אתר לעמותה עם תרומות', name='סל ברכה: סלי מזון לשבת ולחג', text='תרומות באשראי, חד־פעמי או בהוראת קבע, וקמפיין עם מד יעד ורשימת תורמים שמתעדכנים לבד. מסך ניהול לקמפיין ולתרומות במזומן.', admin='לניהול הקמפיין'),
      en=dict(kind='Nonprofit site with donations', name='Sal Bracha: Shabbat and holiday food baskets', text='Card donations, one-time or monthly, and a campaign with a goal meter and a donor list that update themselves. An admin screen for the campaign and cash donations.', admin='The campaign admin')),
 dict(slug='shul', price=6300,
      he=dict(kind='אתר תדמית ותרומות', name='נחלת יצחק: בית כנסת', text='זמני תפילה ושיעורים שהגבאי מעדכן, קמפיין לשיפוץ ההיכל, והקדשות והנצחה. מסך גבאי לכל מה שמשתנה.', admin='למסך הגבאי'),
      en=dict(kind='Presentation site with donations', name='Nachalat Yitzchak: a synagogue', text='Prayer times and classes the gabbai updates, a campaign to renovate the sanctuary, and dedications. A gabbai screen for everything that changes.', admin='The gabbai screen')),
 dict(slug='shop', price=7200,
      he=dict(kind='חנות אונליין', name='גרגר: תבלינים ותה', text='חנות עם עגלה שעובדת, משלוח חינם מעל 250 ₪ והזמנה לדוגמה. מסך ניהול להזמנות, למלאי ולתאריך הטחינה של כל מוצר.', admin='לניהול החנות'),
      en=dict(kind='Online store', name='Gargar: spices and tea', text="A store with a working cart, free shipping over ₪250 and an example order. An admin screen for orders, stock and each product's grinding date.", admin='The store admin')),
]

def ready(c):
    d = ROOT / 'demo' / c['slug'] if c['slug'] else ROOT / 'demo'
    need = ('final/index.html', 'shots/final-d.webp', 'index.html') + (('admin/index.html',) if c.get('admin', True) else ())
    return all((d / f).exists() for f in need)

def block(he):
    L = 'he' if he else 'en'
    cards = [c for c in CARDS if ready(c)]
    T = (dict(title='פרויקטים לדוגמה: כל התהליך, מההתחלה ועד הסוף', sub=f'{len(cards)} עסקים דמיוניים שעברו בדיוק את מה שעובר לקוח אמיתי: שאלון, הצעת מחיר, 3 עיצובים שונים לגמרי, משוב, גרסה סופית ומסך ניהול. מסודרים לפי המסלול והמחיר, כדי שתראו מה מקבלים בכל אחד. אלה דוגמאות, לא אתרים חיים של עסקים.',
              flag='עסק דמיוני · לא אתר חי', all='לכל התהליך', final='לגרסה הסופית', region='הפרויקטים לדוגמה. בטלפון אפשר לגלול לצדדים', price='הצעה של')
         if he else dict(title='Example projects: the whole process, start to finish', sub=f'{len(cards)} fictional businesses that went through exactly what a real client does: a questionnaire, a quote, 3 completely different designs, feedback, a final version and an admin screen. Ordered by package and price, so you can see what each one gets you. These are examples, not live business websites.',
              flag='Fictional business · not a live site', all='See the whole process', final='The final version', region='The example projects. On a phone you can scroll sideways', price='Quote:'))
    out = []
    for c in cards:
        t = c[L]; pfx = f'demo/{c["slug"]}/' if c['slug'] else 'demo/'
        case = ('/' if he else '/en/') + pfx
        price = f'{c["price"]:,} ₪' if he else f'₪{c["price"]:,}'
        out.append(f'''    <article class="ex-card">
     <a class="ex-img" href="{case}" tabindex="-1" aria-hidden="true"><img src="/{pfx}shots/final-d.webp" width="960" height="660" alt="" loading="lazy"><span class="ex-flag">{E(T['flag'])}</span></a>
     <div class="ex-body"><p class="ex-kind">{E(t['kind'])} · {E(T['price'])} {price}</p><h3 class="ex-name"><a href="{case}">{E(t['name'])}</a></h3><p class="ex-text">{E(t['text'])}</p>
      <p class="ex-links"><a class="btn btn-line" href="{case}">{E(T['all'])}</a> <a class="text-link" href="/{pfx}final/">{E(T['final'])}</a> {f' <a class="text-link" href="/{pfx}admin/">{E(t["admin"])}</a>' if c.get('admin', True) else ''}</p></div>
    </article>''')
    return f'''<!-- examples:start -->
<section class="examples-sec" id="examples" aria-labelledby="examples-title">
 <div class="wrap">
  <h2 id="examples-title" class="sec-title">{E(T['title'])}</h2>
  <p class="sec-lead">{E(T['sub'])}</p>
  <div class="ex-grid" role="region" aria-label="{E(T['region'])}" tabindex="0">
{chr(10).join(out)}
  </div>
 </div>
</section>
<!-- examples:end -->'''

if __name__ == '__main__':
    for f, he in (('index.html', True), ('en/index.html', False)):
        p = ROOT / f; s = p.read_text()
        a, b = s.index('<!-- examples:start -->'), s.index('<!-- examples:end -->') + len('<!-- examples:end -->')
        p.write_text(s[:a] + block(he) + s[b:])
        print(f, s[:a].count('\n'))
