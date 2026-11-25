#!/usr/bin/env python3
"""YYG: בונה את העמוד "ככה זה עובד, על פרויקט לדוגמה" (/demo/ ו־/en/demo/).
הנתונים: tools/demo-data.json (התשובות של הלקוח לדוגמה) ו־assets/q-defs.js (נוסח השאלות, בעברית ובאנגלית).
העיצובים עצמם: demo/design-1..3/index.html. צילומי המסך שלהם: demo/shots/ (נוצרים בסקריפט צילום נפרד).
הרצה: python3 tools/demo.py"""
import html, json, pathlib, subprocess

ROOT = pathlib.Path(__file__).resolve().parent.parent
SITE = 'https://yygweb.com'
TODAY = '2026-11-25'
DATA = json.loads((ROOT / 'tools/demo-data.json').read_text())
E = lambda s: html.escape(str(s), quote=True)

# נוסח השאלות משני השאלונים, בשתי השפות
DEFS = json.loads(subprocess.run(['node', '-e', '''
global.window = {}; require(process.argv[1]);
const out = {};
for (const f of ['intake', 'feedback']) for (const lang of ['he', 'en']) {
  const d = window.YYG_FORMS[f]((he, en) => lang === 'he' ? he : en);
  out[f + '_' + lang] = d.steps.map((s) => ({ title: s.title, qs: s.qs.map((q) => ({ id: q.id, type: q.type, label: q.label || '', prefix: q.prefix || '', from: q.from || '', to: q.to || '', options: q.options || [] })) }));
}
console.log(JSON.stringify(out));''', str(ROOT / 'assets/q-defs.js')], capture_output=True, text=True, check=True).stdout)

def qmap(form, lang):
    m = {}
    for s in DEFS[f'{form}_{lang}']:
        for q in s['qs']: m[q['id']] = dict(q, step=s['title'])
    return m

def answer(form, lang, qid, v):
    """התשובה כטקסט בשפה המבוקשת"""
    he_q, q = qmap(form, 'he')[qid], qmap(form, lang)[qid]
    tr = lambda x: q['options'][he_q['options'].index(x)] if x in he_q['options'] else x
    if isinstance(v, dict) and 'files' in v: return ', '.join(v['files'])
    if isinstance(v, dict): return v[lang]
    if isinstance(v, list):
        items = [tr(x) for x in v]
        if lang == 'en': items = [x if i == 0 or x[1:2].isupper() else x[0].lower() + x[1:] for i, x in enumerate(items)]
        return ', '.join(items)
    if q['type'] == 'scale':
        return (f"{v} מתוך 5 (1 = {q['from']}, 5 = {q['to']})" if lang == 'he' else f"{v} of 5 (1 = {q['from']}, 5 = {q['to']})")
    return tr(v)

def get(form, qid):
    return dict(DATA[form])[qid]

def all_answers(form, lang):
    """כל התשובות, מקובצות לפי שלבי השאלון"""
    ans = dict(DATA[form])
    out = []
    for s in DEFS[f'{form}_{lang}']:
        rows = ''
        for q in s['qs']:
            if q['id'] not in ans: continue
            label = q['label'] or (q['prefix'] + (': ' if q['prefix'] else '') + q['from'] + ' ↔ ' + q['to'])
            if not q['label'] and q['type'] == 'scale' and not q['prefix']:
                label = (q['from'] + ' או ' + q['to']) if lang == 'he' else (q['from'] + ' or ' + q['to'])
            rows += f'<div class="d-row"><dt>{E(label)}</dt><dd>{E(answer(form, lang, q["id"], ans[q["id"]])).replace(chr(10), "<br>")}</dd></div>'
        if rows: out.append(f'<section class="d-sum"><h4>{E(s["title"])}</h4><dl>{rows}</dl></section>')
    return '\n'.join(out), len(ans)

# ---------- 3 העיצובים ----------
DESIGNS = [
 dict(n=1, he=dict(name='טבעי ושקט', text='עיצוב רגוע ומרווח בסגנון של מגזין: רקע שמנת, כותרות בפונט סריף אלגנטי, קווים דקים ותמונות גדולות של העץ. מרגיש יוקרתי בלי לצעוק.', fonts='פרנק רוהל ליברה ואסיסטנט'),
      en=dict(name='Natural and calm', text='A calm, spacious, magazine-style design: a cream background, elegant serif headings, fine lines and large photos of the wood. It feels premium without shouting.', fonts='Frank Ruhl Libre and Assistant'),
      colors=['#F6F1E8', '#2B2722', '#9A6B3C', '#7D8B6A']),
 dict(n=2, he=dict(name='סדנה עכשווית', text='עיצוב נועז ובטוח בעצמו: גושי צבע בירוק יער כהה, כתום של מסור לכפתורים, כותרות ענקיות, וסרגל מידות שעובר לאורך כל האתר כמו בשרטוט טכני.', fonts='היבו, במשקל כבד'),
      en=dict(name='Contemporary workshop', text='A bold, confident design: blocks of deep forest green, saw-orange buttons, huge headlines, and a measuring-tape ruler that runs through the site like a technical drawing.', fonts='Heebo, in heavy weights'),
      colors=['#1D3A2E', '#E5652A', '#F1EEE8', '#1A1A1A']),
 dict(n=3, he=dict(name='חם ומשפחתי', text='עיצוב רך ומזמין: צבעי חול וטרקוטה, פינות מעוגלות, אייקונים מצוירים לכל שירות, והמלצות בבועות דיבור. מרגיש כמו נגריה של משפחה, לא של מפעל.', fonts='ורלה ראונד ורוביק'),
      en=dict(name='Warm and family', text='A soft, inviting design: sand and terracotta colors, rounded corners, drawn icons for each service, and testimonials in speech bubbles. It feels like a family shop, not a factory.', fonts='Varela Round and Rubik'),
      colors=['#FBF5EC', '#B9552F', '#7C8A4D', '#3A2E26']),
]

T = {
 'he': dict(
   title='ככה זה עובד: פרויקט לדוגמה מהשאלון ועד 3 עיצובים | YYG',
   desc='נגריה דמיונית עוברת את כל התהליך של YYG: שאלון אפיון מלא, הצעת מחיר, 3 עיצובים שונים לגמרי לאותו אתר, ושאלון משוב שבוחר כיוון. אפשר להיכנס לכל שלב.',
   h1='ככה זה עובד, על פרויקט לדוגמה',
   lead='לקחתי נגריה דמיונית בשם "ענף" והעברתי אותה בדיוק את מה שעובר כל לקוח: שאלון אפיון, הצעת מחיר, 3 עיצובים שונים לגמרי לאותו תוכן, ושאלון משוב שבוחר כיוון. העסק דמיוני. השאלונים, ההצעה והעיצובים אמיתיים, ואפשר להיכנס לכל אחד מהם.',
   note='עסק דמיוני לדוגמה: השם, הפרטים וההמלצות בדויים. התמונות מ־StockSnap, ברישיון CC0.',
   nav=['שאלון אפיון', 'הצעת מחיר', '3 עיצובים', 'משוב ובחירה', 'מה הלאה'],
   s1='שאלון האפיון', s1p='הלקוח מילא את השאלון באתר בכ־10 דקות, וצירף את הלוגו ו־4 תמונות של עבודות. אלה התשובות שהכי השפיעו על העיצוב:',
   files='הקבצים שהלקוח צירף', all='כל {n} התשובות, לפי שלבי השאלון', try1='למלא את שאלון האפיון לעסק שלכם',
   s2='הצעת המחיר', s2p='מהתשובות נבנתה טיוטת הצעה אוטומטית בבונה ההצעות שלי: המסלול, התוספות, ומה שכדאי לבדוק בשיחה. עברתי עליה, והלקוח קיבל במייל הצעה מסודרת עם קישור לאישור בלחיצה.',
   s3='שלושה עיצובים, אותו תוכן', s3p='אחרי 10% הראשונים מכינים 3 כיוונים שונים לגמרי, עם התוכן האמיתי של העסק. ככה בוחרים בעיניים, ולא מתוך דמיון. כל עיצוב הוא אתר שעובד, גם בטלפון:',
   open='לפתוח את עיצוב {n}', fonts='פונטים', palette='צבעים',
   s4='שאלון המשוב', s4p='הלקוח עבר על 3 העיצובים, גם בטלפון, ומילא את שאלון המשוב. ככה נראית בחירה מדויקת:',
   chose='הבחירה', rating='כמה כל עיצוב מרגיש נכון לעסק', took='מה הוא לקח מכל עיצוב', liked='אהב', disliked='פחות', top3='3 הדברים הכי חשובים לשנות', dirs='לאן לזוז בסבב הבא',
   all2='כל התשובות לשאלון המשוב', try2='לראות את שאלון המשוב',
   s5='מה הלאה', s5p='הלקוח אישר את הכיוון ושילם 40%. עכשיו בונים את האתר המלא על עיצוב 1, עם הכפתור הבולט וסרגל המידות מעיצוב 2, והתמונות של הנגריה עצמה. כשהאתר מוכן, הלקוח בודק ומאשר, משלם את ה־50% האחרונים, והאתר עולה לאוויר.',
   cta='רוצים לעבור את התהליך הזה עם העסק שלכם?', ctap='מתחילים בשאלון של 10 דקות, או בשיחה קצרה. אחרי 10% בלבד מקבלים 3 עיצובים כאלה, עם התוכן שלכם.',
   q_open='לשאלון האפיון', calc='למחשבון המחיר', of5='מתוך 5',
   quote_h='הצעת מחיר', quote_for='עבור', item='פריט', price='מחיר', total='סה״כ', final='המחיר סופי, לא מוסיפים מע״מ.', incl='מה כלול', terms='תנאים',
   pay='תשלומים', annual='שנתי', notes='מהשאלון, לבדיקה בשיחה', approve='אישור ההצעה בלחיצה',
   crumb='YYG', home='עמוד הבית', guides='מדריכים', contact='יצירת קשר', other_lang='English', other_href='/en/demo/', lang='he', dir='rtl',
 ),
 'en': dict(
   title='How it works: an example project from questionnaire to 3 designs | YYG',
   desc='A fictional carpentry shop goes through the whole YYG process: a full discovery questionnaire, a quote, 3 completely different designs of the same site, and a feedback form that picks a direction.',
   h1='How it works, on an example project',
   lead='I took a fictional carpentry shop called "Anaf" and put it through exactly what every client goes through: a discovery questionnaire, a quote, 3 completely different designs for the same content, and a feedback form that picks a direction. The business is fictional. The questionnaires, the quote and the designs are real, and you can open each of them.',
   note='A fictional example business: the name, details and testimonials are made up. Photos from StockSnap, CC0 license.',
   nav=['Questionnaire', 'Quote', '3 designs', 'Feedback', "What's next"],
   s1='The discovery questionnaire', s1p='The client filled in the questionnaire on the site in about 10 minutes, and attached the logo and 4 photos of their work. These are the answers that shaped the design most:',
   files='Files the client attached', all='All {n} answers, by questionnaire step', try1='Fill in the questionnaire for your business',
   s2='The quote', s2p='From the answers, my quote builder drafted a quote automatically: the package, the add-ons, and what to check on the call. I reviewed it, and the client got a formal quote by email with a one-click approval link.',
   s3='Three designs, the same content', s3p='After the first 10%, I prepare 3 completely different directions with the business\'s real content. So you choose with your eyes, not your imagination. Each design is a working site, on the phone too:',
   open='Open design {n}', fonts='Fonts', palette='Colors',
   s4='The feedback form', s4p='The client went through the 3 designs, on the phone too, and filled in the feedback form. This is what a precise choice looks like:',
   chose='The choice', rating='How right each design feels for the business', took='What he took from each design', liked='Liked', disliked='Less', top3='The 3 most important changes', dirs='Where to move in the next round',
   all2='All the feedback answers', try2='See the feedback form',
   s5="What's next", s5p='The client approved the direction and paid 40%. Now the full site is built on design 1, with the stronger button and the measuring-tape ruler from design 2, and the shop\'s own photos. When the site is ready, the client checks and approves it, pays the final 50%, and the site goes live.',
   cta='Want to go through this process with your business?', ctap='Start with a 10-minute questionnaire, or a short call. After just 10%, you get 3 designs like these, with your content.',
   q_open='To the questionnaire', calc='To the price calculator', of5='of 5',
   quote_h='Quote', quote_for='for', item='Item', price='Price', total='Total', final='Final price, no VAT added.', incl="What's included", terms='Terms',
   pay='Payments', annual='Yearly', notes='From the questionnaire, to check on the call', approve='Approve the quote',
   crumb='YYG', home='Home', guides='Guides', contact='Contact', other_lang='עברית', other_href='/demo/', lang='en', dir='ltr',
 ),
}

QUOTE = {
 'he': dict(base='אתר עם אזור ניהול', price=4000,
   incl=['עד 6 עמודים', 'אזור ניהול לעדכון התוכן שמשתנה', 'טפסים ומדור תוכן', 'הדרכה: שיחה אישית ומדריך כתוב', 'דומיין, אחסון ומייל עסקי לשנה הראשונה', 'טקסטים לאתר: כתיבה בעזרת AI ועריכה אישית', 'התאמה לטלפון, לגוגל ולנגישות, והצהרת נגישות'],
   terms=['3 כיווני עיצוב', '3 סבבי תיקונים', '10 ימי עבודה לאספקה', '4 חודשי ליווי'],
   pay=[(10, 'כדי להתחיל, לפני הכנת 3 העיצובים'), (40, 'אחרי שבוחרים עיצוב, לפני הבנייה'), (50, 'בסיום, אחרי האישור שלכם ולפני העלייה לאוויר')],
   annual='"הכול עליי": דומיין, אחסון, מייל עסקי ועדכוני אבטחה. 590 ₪ לשנה, מהשנה השנייה.',
   notes=['סימן 5 עמודים, ובמסלול כלולים עד 6. אין תוספת עמודים.', 'צריך לרשום דומיין: משהו קצר עם המילה ענף. כלול בשנה הראשונה.', 'לוח זמנים: תוך חודש. התקציב שציין: 4,000 עד 7,000 ₪.']),
 'en': dict(base='Website with an admin area', price=4000,
   incl=['Up to 6 pages', 'An admin area to update changing content', 'Forms and a content section', 'Training: a personal call and a written guide', 'Domain, hosting and business email for the first year', 'Website copy: written with AI and personally edited', 'Mobile, Google and accessibility ready, with an accessibility statement'],
   terms=['3 design directions', '3 revision rounds', '10 working days to deliver', '4 months of support'],
   pay=[(10, 'To start, before the 3 designs'), (40, 'After you choose a design, before the build'), (50, 'At the end, after your approval and before going live')],
   annual='"I handle it all": domain, hosting, business email and security updates. 590 ₪ a year, from the second year.',
   notes=['Marked 5 pages; the package includes up to 6. No extra pages.', 'A domain needs registering: something short with the word Anaf. Included in the first year.', 'Timing: within a month. Stated budget: 4,000 to 7,000 ₪.']),
}

STYLE = '''<style>
 .g-top{border-bottom:1px solid var(--line);background:var(--base)}
 .g-top .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:72px}
 .g-top img{height:48px;width:auto}
 .g-top nav{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
 .d-main{padding:clamp(36px,6vw,72px) 0 clamp(56px,8vw,104px)}
 .d-main .wrap{max-width:1120px}
 .d-crumb{font-size:.92rem;color:var(--muted)}
 .d-crumb a{color:var(--muted)}
 .d-main h1{margin-top:12px;max-width:20ch;font-family:var(--display);font-weight:700;font-size:clamp(2.4rem,1.6rem + 3vw,4.2rem);line-height:1}
 .d-lead{margin-top:18px;max-width:62ch;font-size:var(--step-1);color:#D7DDE6;line-height:1.6}
 .d-note{display:inline-block;margin-top:16px;padding:8px 14px;border:1px solid var(--line-2);border-radius:999px;font-size:.9rem;color:var(--muted)}
 .d-nav{display:flex;flex-wrap:wrap;gap:8px;margin-top:26px;counter-reset:dn}
 .d-nav a{display:inline-flex;align-items:center;gap:8px;min-height:44px;padding:8px 14px;border:1px solid var(--line-2);border-radius:999px;color:var(--text);text-decoration:none;font-weight:600;font-size:.95rem}
 .d-nav a::before{counter-increment:dn;content:counter(dn);display:inline-grid;place-items:center;width:24px;height:24px;border-radius:50%;background:var(--gold);color:#1A1404;font-size:.8rem;font-weight:700}
 .d-nav a:hover,.d-nav a:focus-visible{border-color:var(--gold)}
 .d-step{margin-top:clamp(56px,8vw,96px);scroll-margin-top:24px}
 .d-step-h{display:flex;align-items:baseline;gap:14px}
 .d-num{font-family:var(--display);font-size:clamp(3rem,2rem + 3vw,4.6rem);line-height:.8;color:transparent;-webkit-text-stroke:1.5px var(--gold)}
 .d-step h2{font-size:var(--step-2);line-height:1.2}
 .d-step>p,.d-p{margin-top:12px;max-width:70ch;color:#C9D1DD;line-height:1.75}
 .d-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,300px),1fr));gap:14px;margin-top:24px}
 .d-card{padding:18px 20px;border:1px solid var(--line);border-radius:14px;background:var(--panel)}
 .d-card small{display:block;color:var(--gold);font-weight:700;font-size:.85rem}
 .d-card p{margin-top:6px;color:#D7DDE6;line-height:1.6}
 .d-files{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px;align-items:center}
 .d-files b{width:100%;font-size:.92rem;color:var(--muted);font-weight:600}
 .d-files img{height:84px;width:auto;border-radius:10px;border:1px solid var(--line);background:#fff}
 .d-main details.d-all{margin-top:18px;border:1px solid var(--line);border-radius:14px;background:var(--base-2)}
 .d-all>summary{cursor:pointer;padding:14px 18px;font-weight:700;min-height:44px;display:flex;align-items:center}
 .d-all>summary:focus-visible{outline:2px solid var(--gold);outline-offset:2px;border-radius:12px}
 .d-all-in{padding:0 18px 18px;display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr));gap:10px 28px}
 .d-sum h4{margin-top:14px;font-size:1rem;color:#fff}
 .d-row{padding:8px 0;border-bottom:1px solid var(--line)}
 .d-row dt{font-size:.88rem;color:var(--muted)}
 .d-row dd{margin-top:2px;color:#E3E8EF;line-height:1.55}
 .d-try{margin-top:16px}
 /* ההצעה, כמו שהלקוח רואה אותה */
 .d-quote{margin-top:24px;max-width:760px;padding:clamp(20px,3.4vw,34px);background:#fff;color:#151920;border-radius:16px;box-shadow:0 24px 60px -24px rgba(0,0,0,.6)}
 .d-quote h3,.d-quote h4,.d-quote th,.d-quote b{color:#151920}
 .d-quote h3{font-size:1.35rem}
 .d-quote .q-sub{margin-top:4px;color:#5A6472}
 .d-quote table{width:100%;margin-top:16px;border-collapse:collapse}
 .d-quote th,.d-quote td{padding:10px 8px;border-bottom:1px solid #DCE0E6;text-align:start}
 .d-quote td:last-child,.d-quote th:last-child{text-align:end;white-space:nowrap}
 .d-quote tr.tot td{font-weight:700;font-size:1.1rem;border-bottom:0}
 .d-quote .q-fine{font-size:.88rem;color:#5A6472}
 .d-quote h4{margin-top:18px;font-size:1rem}
 .d-quote ul{margin-top:6px;padding-inline-start:20px;list-style:disc;color:#2C333D;line-height:1.7}
 .d-quote .q-pay li{list-style:none;margin-inline-start:-20px}
 .d-quote .q-pay b{display:inline-block;min-width:4.2em}
 .d-quote .q-notes{margin-top:18px;padding:12px 14px;background:#FBF0DE;border-radius:10px;font-size:.92rem}
 .d-quote .q-notes ul{color:#3D3420}
 .d-quote .q-btn{display:inline-block;margin-top:18px;padding:12px 20px;border-radius:10px;background:#E3B341;color:#1A1404;font-weight:700}
 /* העיצובים */
 .d-designs{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin-top:26px}
 .d-design{display:flex;flex-direction:column;border:1px solid var(--line);border-radius:16px;background:var(--panel);overflow:hidden}
 .d-shot{position:relative;display:block;aspect-ratio:16/11;overflow:hidden;background:var(--base-2)}
 .d-shot img.dk{width:100%;height:100%;object-fit:cover;object-position:top}
 .d-shot img.mb{position:absolute;inset-block-end:10px;inset-inline-end:10px;width:26%;height:auto;border-radius:10px;border:3px solid #0D1016;box-shadow:0 10px 24px rgba(0,0,0,.45)}
 .d-design-b{display:flex;flex-direction:column;gap:10px;padding:18px 18px 20px;flex:1}
 .d-design h3{font-size:1.2rem}
 .d-design h3 small{display:block;font-size:.82rem;color:var(--gold);font-weight:700}
 .d-design p{color:#C9D1DD;line-height:1.6}
 .d-meta{font-size:.88rem;color:var(--muted)}
 .d-sw{display:flex;gap:6px;align-items:center}
 .d-sw i{width:22px;height:22px;border-radius:50%;border:1px solid rgba(255,255,255,.25)}
 .d-design .btn{margin-top:auto;align-self:flex-start}
 /* המשוב */
 .d-fb{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;margin-top:24px}
 .d-choice{display:flex;align-items:center;gap:14px;padding:18px 20px;border-radius:14px;background:linear-gradient(135deg,#3A2E10,#262D3A);border:1px solid #6B5520}
 .d-choice b{font-family:var(--display);font-size:2.2rem;line-height:1;color:var(--gold)}
 .d-bars{display:grid;gap:10px;margin-top:10px}
 .d-bar{display:grid;grid-template-columns:5.5em 1fr auto;align-items:center;gap:10px;font-size:.95rem}
 .d-bar i{display:block;height:10px;border-radius:6px;background:var(--line)}
 .d-bar i span{display:block;height:100%;border-radius:6px;background:var(--gold)}
 .d-took{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:16px}
 .d-took .d-card h4{font-size:1rem;color:#fff}
 .d-took .d-card p{font-size:.95rem}
 .d-tag{display:inline-block;margin:6px 0 0 6px;padding:3px 10px;border-radius:999px;font-size:.82rem;background:#1f3a2a;color:#CDEFD8}
 .d-tag.no{background:#3a1f1f;color:#F6D2D2}
 .d-scale{display:grid;grid-template-columns:7em 1fr 7em;align-items:center;gap:10px;margin-top:10px;font-size:.9rem;color:var(--muted)}
 .d-scale>span:last-child{text-align:end}
 .d-scale i{position:relative;height:4px;border-radius:2px;background:var(--line-2)}
 .d-scale i b{position:absolute;top:50%;width:16px;height:16px;margin-block-start:-8px;margin-inline-start:-8px;border-radius:50%;background:var(--gold)}
 .d-top3{margin-top:8px;padding-inline-start:0;list-style:none;display:grid;gap:8px}
 .d-top3 li{padding:10px 14px;border-radius:10px;background:var(--base-2);color:#E3E8EF;line-height:1.5}
 .d-cta{margin-top:clamp(56px,8vw,96px);padding:clamp(22px,4vw,40px);border:1px solid var(--line-2);border-radius:18px;background:var(--panel)}
 .d-cta h2{font-size:var(--step-2)}
 .d-cta p{margin-top:10px;color:#C9D1DD;max-width:60ch}
 .d-cta .acts{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
 @media (max-width:960px){.d-designs{grid-template-columns:1fr;max-width:620px}.d-fb,.d-took{grid-template-columns:1fr}}
 @media (max-width:520px){.d-bar{grid-template-columns:4.6em 1fr auto}.d-scale{grid-template-columns:5em 1fr 5em}}
</style>'''


def nis(n): return f'{n:,} ₪'

def build(lang):
    t = T[lang]; he = lang == 'he'
    path = 'demo/index.html' if he else 'en/demo/index.html'
    url = f'{SITE}/demo/' if he else f'{SITE}/en/demo/'
    ans = lambda f, q: answer(f, lang, q, get(f, q))
    # שלב 1: תשובות מרכזיות
    hi = [
     ('העסק' if he else 'The business', ans('intake', 'biz_what')),
     ('מה מייחד אותם' if he else 'What sets them apart', ans('intake', 'biz_unique')),
     ('הלקוחות' if he else 'The customers', ans('intake', 'cust_who') + '. ' + ans('intake', 'cust_sensitive')),
     ('המטרה של האתר' if he else 'The goal of the site', ans('intake', 'goal_action') + '. ' + ans('intake', 'goal_success')),
     ('3 מילים שהאתר צריך לשדר' if he else '3 words the site should convey', ans('intake', 'feel') + '. ' + ('רקע: ' if he else 'Background: ') + ans('intake', 'bg') + '.'),
     ('מה לא רוצים לראות' if he else "What they don't want to see", ans('intake', 'dislikes')),
     ('עמודים' if he else 'Pages', ans('intake', 'pages')),
     ('מסלול, תקציב וזמנים' if he else 'Package, budget and timing', ('מסלול: ' if he else 'Package: ') + ans('intake', 'package') + '\n' + ('תקציב: ' if he else 'Budget: ') + ans('intake', 'budget') + '\n' + ('מתי: ' if he else 'When: ') + ans('intake', 'deadline')),
    ]
    cards1 = ''.join(f'<div class="d-card"><small>{E(k)}</small><p>{E(v).replace(chr(10), "<br>")}</p></div>' for k, v in hi)
    files = ''.join(f'<img src="/demo/img/{f}" alt="{E(a)}" loading="lazy" width="{w}" height="84">' for f, a, w in [
        ('anaf-logo-sm.webp', 'הלוגו של ענף' if he else 'The Anaf logo', 184), ('kitchen-drawer-560.webp', 'מגירת מטבח מעץ' if he else 'A wooden kitchen drawer', 126),
        ('table-bench-560.webp', 'שולחן וספסל מעץ' if he else 'A wooden table and bench', 126), ('library-560.webp', 'ספריות בסלון' if he else 'Living room bookcases', 149), ('wardrobe-560.webp', 'ארון קיר' if he else 'A built-in wardrobe', 63)])
    all1, n1 = all_answers('intake', lang)
    # שלב 2: ההצעה
    Q = QUOTE[lang]
    pay = ''.join(f'<li><b>{p}%</b> {nis(Q["price"] * p // 100)} · {E(w)}</li>' for p, w in Q['pay'])
    quote = f'''<div class="d-quote" role="figure" aria-label="{E(t['quote_h'])}">
   <h3>{E(t['quote_h'])} {E(DATA['quote'])}</h3><p class="q-sub">{E(t['quote_for'])} {E(ans('intake', 'business'))}</p>
   <table><thead><tr><th>{E(t['item'])}</th><th>{E(t['price'])}</th></tr></thead><tbody><tr><td>{E(Q['base'])}</td><td>{nis(Q['price'])}</td></tr><tr class="tot"><td>{E(t['total'])}</td><td>{nis(Q['price'])}</td></tr></tbody></table>
   <p class="q-fine">{E(t['final'])}</p>
   <h4>{E(t['incl'])}</h4><ul>{''.join(f'<li>{E(x)}</li>' for x in Q['incl'])}</ul>
   <h4>{E(t['terms'])}</h4><ul>{''.join(f'<li>{E(x)}</li>' for x in Q['terms'])}</ul>
   <h4>{E(t['pay'])}</h4><ul class="q-pay">{pay}</ul>
   <h4>{E(t['annual'])}</h4><ul><li>{E(Q['annual'])}</li></ul>
   <div class="q-notes"><b>{E(t['notes'])}</b><ul>{''.join(f'<li>{E(x)}</li>' for x in Q['notes'])}</ul></div>
   <span class="q-btn" aria-hidden="true">{E(t['approve'])}</span>
  </div>'''
    # שלב 3: העיצובים
    designs = ''
    for d in DESIGNS:
        L = d[lang]; n = d['n']
        designs += f'''<article class="d-design">
   <a class="d-shot" href="/demo/design-{n}/" aria-label="{E(t['open'].format(n=n))}"><img class="dk" src="/demo/shots/design-{n}-d.webp" width="960" height="660" alt="" loading="lazy"><img class="mb" src="/demo/shots/design-{n}-m.webp" width="240" height="520" alt="" loading="lazy"></a>
   <div class="d-design-b"><h3><small>{'עיצוב' if he else 'Design'} {n}</small>{E(L['name'])}</h3><p>{E(L['text'])}</p>
    <p class="d-meta">{E(t['fonts'])}: {E(L['fonts'])}</p>
    <div class="d-sw" aria-label="{E(t['palette'])}"><span class="d-meta">{E(t['palette'])}:</span>{''.join(f'<i style="background:{c}"></i>' for c in d['colors'])}</div>
    <a class="btn btn-line" href="/demo/design-{n}/">{E(t['open'].format(n=n))}</a></div>
  </article>'''
    # שלב 4: המשוב
    fb = dict(DATA['feedback'])
    bars = ''.join(f'<div class="d-bar"><span>{"עיצוב" if he else "Design"} {i}</span><i><span style="width:{int(fb["rate" + str(i)]) * 20}%"></span></i><span>{fb["rate" + str(i)]} {E(t["of5"])}</span></div>' for i in (1, 2, 3))
    fq = qmap('feedback', lang)
    took = ''
    for i in (1, 2, 3):
        likes = ''.join(f'<span class="d-tag">{E(x)}</span>' for x in ans('feedback', f'd{i}_like').split(', ')) if fb.get(f'd{i}_like') else ''
        nos = ''.join(f'<span class="d-tag no">{E(x)}</span>' for x in ans('feedback', f'd{i}_dislike').split(', ')) if fb.get(f'd{i}_dislike') else ''
        took += f'<div class="d-card"><h4>{"עיצוב" if he else "Design"} {i}: {E(DESIGNS[i - 1][lang]["name"])}</h4><p>{likes}{nos}</p><p>{E(ans("feedback", f"d{i}_note"))}</p></div>'
    scales = ''
    for k in ('dir_warm', 'dir_detail', 'dir_bold', 'dir_modern'):
        q = fq[k]; v = int(fb[k])
        pos = (v - 1) * 25
        scales += f'<div class="d-scale" role="img" aria-label="{E(q["from"])} ↔ {E(q["to"])}: {v} {E(t["of5"])}"><span>{E(q["from"])}</span><i><b style="inset-inline-start:{pos}%"></b></i><span>{E(q["to"])}</span></div>'
    top3 = ''.join(f'<li>{E(x)}</li>' for x in ans('feedback', 'top3').split('\n'))
    all2, n2 = all_answers('feedback', lang)
    ld = [{'@context': 'https://schema.org', '@type': 'Article', 'headline': t['h1'], 'description': t['desc'], 'inLanguage': lang, 'datePublished': TODAY, 'dateModified': TODAY,
           'mainEntityOfPage': url, 'author': {'@type': 'Person', 'name': 'יהודה גורדון' if he else 'Yehuda Gordon', 'url': SITE + '/'},
           'publisher': {'@type': 'Organization', 'name': 'YYG', 'logo': {'@type': 'ImageObject', 'url': SITE + '/assets/img/logo-320.webp'}}, 'image': SITE + '/demo/shots/design-1-d.webp'},
          {'@context': 'https://schema.org', '@type': 'BreadcrumbList', 'itemListElement': [
           {'@type': 'ListItem', 'position': 1, 'name': 'YYG', 'item': SITE + ('/' if he else '/en/')}, {'@type': 'ListItem', 'position': 2, 'name': t['h1'], 'item': url}]}]
    home = '/' if he else '/en/'
    nav = ''.join(f'<a href="#s{i + 1}">{E(x)}</a>' for i, x in enumerate(t['nav']))
    page = f'''<!doctype html>
<html lang="{lang}" dir="{t['dir']}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<script>if(location.protocol==="http:"&&location.hostname==="yygweb.com")location.replace("https://yygweb.com"+location.pathname+location.search+location.hash)</script>
<title>{E(t['title'])}</title>
<meta name="description" content="{E(t['desc'])}">
<link rel="canonical" href="{url}">
<link rel="alternate" hreflang="he" href="{SITE}/demo/">
<link rel="alternate" hreflang="en" href="{SITE}/en/demo/">
<link rel="alternate" hreflang="x-default" href="{SITE}/demo/">
<meta property="og:type" content="article">
<meta property="og:locale" content="{'he_IL' if he else 'en_US'}">
<meta property="og:url" content="{url}">
<meta property="og:title" content="{E(t['h1'])}">
<meta property="og:description" content="{E(t['desc'])}">
<meta property="og:image" content="{SITE}/demo/shots/design-1-d.webp">
<meta name="theme-color" content="#151920">
<link rel="icon" href="/assets/img/icon-64.png" type="image/png">
<link rel="preload" href="/assets/fonts/karantina-700-hebrew.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/site.css?v=39">
{STYLE}
<script type="application/ld+json">{json.dumps(ld, ensure_ascii=False, separators=(',', ':'))}</script>
</head>
<body>
<a class="skip" href="#main">{'דילוג לתוכן' if he else 'Skip to content'}</a>
<header class="g-top">
 <div class="wrap">
  <a href="{home}"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="{'YYG, לעמוד הבית' if he else 'YYG, to the home page'}"></a>
  <nav aria-label="{'ניווט' if he else 'Navigation'}">
   <a class="text-link" href="{'/guides/' if he else '/en/guides/'}">{E(t['guides'])}</a>
   <a class="text-link" href="{home}#plan">{'מחשבון מחיר' if he else 'Price calculator'}</a>
   <a class="text-link" href="{home}#contact">{E(t['contact'])}</a>
   <a class="text-link" href="{t['other_href']}" hreflang="{'en' if he else 'he'}" lang="{'en' if he else 'he'}">{E(t['other_lang'])}</a>
  </nav>
 </div>
</header>
<main id="main" class="d-main">
 <div class="wrap">
  <p class="d-crumb"><a href="{home}">YYG</a> › {'פרויקט לדוגמה' if he else 'Example project'}</p>
  <h1>{E(t['h1'])}</h1>
  <p class="d-lead">{E(t['lead'])}</p>
  <p class="d-note">{E(t['note'])}</p>
  <nav class="d-nav" aria-label="{'שלבי הפרויקט' if he else 'Project steps'}">{nav}</nav>

  <section class="d-step" id="s1" aria-labelledby="s1-t">
   <div class="d-step-h"><span class="d-num" aria-hidden="true">1</span><h2 id="s1-t">{E(t['s1'])}</h2></div>
   <p>{E(t['s1p'])}</p>
   <div class="d-cards">{cards1}</div>
   <div class="d-files"><b>{E(t['files'])}</b>{files}</div>
   <details class="d-all"><summary>{E(t['all'].format(n=n1))}</summary><div class="d-all-in">{all1}</div></details>
   <p class="d-try"><a class="text-link" href="{'/questionnaire/' if he else '/en/questionnaire/'}">{E(t['try1'])}</a></p>
  </section>

  <section class="d-step" id="s2" aria-labelledby="s2-t">
   <div class="d-step-h"><span class="d-num" aria-hidden="true">2</span><h2 id="s2-t">{E(t['s2'])}</h2></div>
   <p>{E(t['s2p'])}</p>
   {quote}
  </section>

  <section class="d-step" id="s3" aria-labelledby="s3-t">
   <div class="d-step-h"><span class="d-num" aria-hidden="true">3</span><h2 id="s3-t">{E(t['s3'])}</h2></div>
   <p>{E(t['s3p'])}</p>
   <div class="d-designs">{designs}</div>
  </section>

  <section class="d-step" id="s4" aria-labelledby="s4-t">
   <div class="d-step-h"><span class="d-num" aria-hidden="true">4</span><h2 id="s4-t">{E(t['s4'])}</h2></div>
   <p>{E(t['s4p'])}</p>
   <div class="d-fb">
    <div class="d-card"><div class="d-choice"><span>{E(t['chose'])}:</span><b>{E(ans('feedback', 'choice'))}</b></div><p style="margin-top:12px">{E(ans('feedback', 'first'))}</p></div>
    <div class="d-card"><small>{E(t['rating'])}</small><div class="d-bars">{bars}</div></div>
   </div>
   <h3 class="d-p" style="margin-top:22px;color:#fff;font-size:var(--step-1)">{E(t['took'])}</h3>
   <div class="d-took">{took}</div>
   <div class="d-fb">
    <div class="d-card"><small>{E(t['top3'])}</small><ol class="d-top3">{top3}</ol></div>
    <div class="d-card"><small>{E(t['dirs'])}</small>{scales}</div>
   </div>
   <details class="d-all"><summary>{E(t['all2'])}</summary><div class="d-all-in">{all2}</div></details>
   <p class="d-try"><a class="text-link" href="{'/feedback/' if he else '/en/feedback/'}">{E(t['try2'])}</a></p>
  </section>

  <section class="d-step" id="s5" aria-labelledby="s5-t">
   <div class="d-step-h"><span class="d-num" aria-hidden="true">5</span><h2 id="s5-t">{E(t['s5'])}</h2></div>
   <p>{E(t['s5p'])}</p>
  </section>

  <section class="d-cta" aria-labelledby="cta-t">
   <h2 id="cta-t">{E(t['cta'])}</h2>
   <p>{E(t['ctap'])}</p>
   <div class="acts"><a class="btn btn-gold" href="{'/questionnaire/' if he else '/en/questionnaire/'}">{E(t['q_open'])}</a><a class="btn btn-line" href="{home}?src=demo#plan">{E(t['calc'])}</a></div>
  </section>
 </div>
</main>
<footer class="foot">
 <div class="wrap foot-in">
  <p class="foot-brand"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="" loading="lazy"> {'YYG, יהודה גורדון. עיצוב ובניית אתרים' if he else 'YYG, Yehuda Gordon. Web design and development'}</p>
  <p><a class="foot-link" href="{home}">{E(t['home'])}</a> · <a class="foot-link" href="{'/services/' if he else '/en/services/'}">{'שירותים' if he else 'Services'}</a> · <a class="foot-link" href="{'/guides/' if he else '/en/guides/'}">{E(t['guides'])}</a> · <a class="foot-link" href="{'/privacy.html' if he else '/en/privacy.html'}">{'מדיניות פרטיות' if he else 'Privacy policy'}</a> · <a class="foot-link" href="{'/accessibility.html' if he else '/en/accessibility.html'}">{'הצהרת נגישות' if he else 'Accessibility statement'}</a></p>
 </div>
</footer>
<script>if(location.hostname==="yygweb.com"&&navigator.sendBeacon){{try{{var q=new URLSearchParams(location.search);navigator.sendBeacon("/hit.php",new Blob([JSON.stringify({{e:"view",p:location.pathname,r:document.referrer,s:q.get("src")||q.get("utm_source")||""}})],{{type:"application/json"}}))}}catch(e){{}}}}</script>
</body>
</html>
'''
    out = ROOT / path
    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_text(page)
    return path

if __name__ == '__main__':
    print([build('he'), build('en')])
