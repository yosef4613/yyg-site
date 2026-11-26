/* בית הכנסת נחלת יצחק (מוסד דמיוני לדוגמה): המנוע המשותף לכל העמודים.
   שני חלקים: (א) הקמפיין לחידוש ההיכל, התרומות וההקדשות. (ב) זמני התפילות והשיעורים שהגבאי מעדכן.
   מה שמשתנה (תרומה לדוגמה, עדכון במסך הגבאי) נשמר רק בדפדפן הזה, במפתח 'nachalat-yitzchak-v1', כדי שכל אחד יוכל לנסות.
   באתר אמיתי התרומה עוברת לדף תשלום מאובטח של חברת הסליקה, והנתונים נשמרים בשרת ומופיעים מיד לכל הגולשים.
   הכול נכתב כטקסט בלבד (textContent ובניית אלמנטים), כך שאפשר לכתוב כל דבר בלי לשבור את העמוד.
   סכומים: מספרים בפורמט עברי עם ₪ ("350,000 ₪"). תאריכים ושעות: לפי שעון ישראל.

   ---------- הקמפיין ----------
     data-camp-title          שם הקמפיין ("מחדשים את ההיכל")
     data-camp-text           משפט ההסבר של הקמפיין
     data-camp-raised         כמה נאסף ("214,540 ₪"). הוראת קבע נספרת כ־12 חודשים
     data-camp-goal           היעד ("350,000 ₪")
     data-camp-remaining      כמה חסר ליעד ("135,460 ₪"). אחרי היעד: "0 ₪"
     data-camp-percent        אחוז ("61%"). מעוגל כלפי מטה, ויכול לעבור 100%
     data-camp-progress       מד ההתקדמות. מקבל role="progressbar", aria-valuemin="0", aria-valuemax="100", aria-valuenow,
                              aria-valuetext ("נאספו 214,540 ₪ מתוך 350,000 ₪, 61%") ו־aria-label אם אין.
                              מקבל גם משתני CSS: --p (מספר בין 0 ל־1) ו־--pct ("61.3%"). אם זה <progress>, מקבל value/max.
     data-camp-fill           בתוך המד (או לבד): מקבל style.width לפי האחוז, עד 100%
     data-camp-count          מספר התרומות (באתר ובערב ההקדשה יחד) ("79")
     data-camp-monthly-note   ההסבר על הוראת קבע ("הוראת קבע נספרת ביעד כ־12 חודשים")
     data-camp-end            תאריך הסיום ("עד 24.12")
     data-camp-days           כמה ימים נשארו (מספר בלבד, "28"). כל אלמנטי הספירה מקבלים data-state="running|last|ended"
     data-camp-hours          כמה שעות נשארו מעבר לימים ("14")
     data-camp-minutes        כמה דקות נשארו מעבר לשעות ("35")
     data-camp-days-label     משפט מלא: "נשארו 28 ימים" · "נשאר יום אחד" · "היום האחרון לקמפיין" · "הקמפיין הסתיים. תודה לכל השותפים!"
     data-camp-live           עטיפה שמוסתרת (hidden) כשהקמפיין הסתיים
     data-camp-ended          עטיפה שמוצגת רק כשהקמפיין הסתיים
     data-camp-donors="8"     התורמים האחרונים (המספר: כמה להציג, ברירת מחדל 8). ב־<ul>/<ol> נוצרים <li>, אחרת <div class="donor">.
                              בכל שורה: .d-name (שם, או "בעילום שם"), .d-amount ("1,800 ₪" או "360 ₪ לחודש"), .d-item ("הקדשה: הבימה"),
                              .d-ded (טקסט ההקדשה), .d-when ("היום", "אתמול", "22.11"). מה שריק לא נוצר.
                              מחלקות: is-monthly, is-anon, is-mine (תרומה לדוגמה מהדפדפן הזה)
     data-camp-updates="3"    עדכונים מהקמפיין, החדש ראשון: <li> עם <time class="u-date"> ו־<span class="u-text">
   ---------- הקדשות ----------
     data-dedications         פריטי ההקדשה. ריק: הכול. "4": ארבעת הראשונים. "aron,bimah": רק אלה, בסדר הזה.
                              ב־<ul>/<ol> נוצרים <li class="ded">, אחרת <div class="ded">. בכל פריט: .ded-name, .ded-desc, .ded-price ("54,000 ₪"),
                              .ded-state ("פנוי להקדשה" · "נשארו 9 מתוך 16" · "הוקדש"), .ded-by ("משפחת רוזנברג · לעילוי נשמת הורינו היקרים"),
                              וכפתור <button class="ded-btn" data-pick-item="ID">להקדיש</button> רק כשהפריט פנוי.
                              מחלקות: is-taken / is-available. data-img-base="/demo/shul/img/" על העטיפה מוסיף <img class="ded-img"> (560).
     data-pick-item="ID"      כל כפתור או קישור עם התכונה הזה בוחר את הפריט בטופס התרומה, ממלא את הסכום וגולל לטופס.
                              משדר גם אירוע 'nachalat:pick' על document (detail: {id, item}) למי שבונה זרימה משלו.
   ---------- טופס התרומה (לא חובה: מי שמשתמש בשמות האלה מקבל את כל ההתנהגות מוכנה) ----------
     <form data-donate-form>  שדות לפי name:
       amount                 כפתורי רדיו עם סכומים (180, 360...). ערך "other" = סכום חופשי
       amount_other           שדה מספר לסכום חופשי. הקלדה בו מבטלת את הרדיו, ובחירת רדיו מנקה אותו
       freq                   רדיו "once" / "monthly" (או checkbox בשם monthly)
       name                   שם לפרסום ("משפחת כהן"). חובה, אלא אם מסמנים בעילום שם
       anonymous              checkbox: בעילום שם (משבית את שדה השם)
       dedication             טקסט הקדשה (לא חובה, עד 80 תווים), למשל "לעילוי נשמת..."
       item                   <select>: הסקריפט ממלא אותו בפריטים הפנויים. בחירת פריט קובעת את הסכום למחיר הפריט, ותרומה חד־פעמית.
                              הרדיו "סכום אחר" מסומן (שדה הסכום מציג את המחיר), וביטול הפריט מחזיר את הסכום שהיה מסומן
       email                  מייל לקבלה (לא חובה. בדוגמה לא נשמר ולא נשלח)
     [data-donate-summary]    משפט חי: "תרומה חודשית של 360 ₪, נספרת ביעד כ־4,320 ₪"
     [data-donate-error]      הודעת שגיאה (מקבל role="alert"). מוסתר כשאין
     [data-donate-thanks]     הודעת התודה אחרי שליחה (מקבל role="status"). מוסתר עד השליחה. ברור שזו דוגמה ושלא חויב דבר
     [data-demo-reset]        כפתור שמחזיר את הדוגמה למצב ההתחלה (מוחק את מה שנשמר בדפדפן)
   ---------- זמני תפילות ושיעורים ----------
     (הטיימר של כל דקה מעדכן רק את מה שתלוי בשעון: הספירה, התפילה הבאה והשיעורים של היום. רשימות נבנות מחדש רק אחרי שינוי בנתונים,
      ואז הפוקוס חוזר לכפתור של אותו פריט אם היה עליו)
     data-times-weekday       תפילות ימי החול. ב־<ul>/<ol>: <li class="t-row"> עם .t-name, .t-sub (אם יש), .t-time.
                              ב־<table>/<tbody>: <tr><th scope="row">שם <small>תת־כותרת</small></th><td>שעה</td></tr>. ב־<dl>: dt/dd.
                              התפילה הבאה מקבלת class="is-next" ו־aria-current="time"
     data-times-shabbat       תפילות שבת, באותו מבנה. אם הגבאי הקליד הדלקת נרות או צאת השבת (times.candles / times.havdalah),
                              הן נוספות כשורה ראשונה ("הדלקת נרות") וכשורה אחרונה ("צאת השבת"), עם class="is-zman" ו־data-zman="candles|havdalah".
                              הן לא תפילות, ולכן לא מסומנות "הבאה"
     data-times-candles       רק השעה של הדלקת הנרות ("16:08"). מוסתר כשאין
     data-times-havdalah      רק השעה של צאת השבת ("17:06"). מוסתר כשאין
     data-times-zmanim        עטיפה שמוסתרת כששני הזמנים האלה ריקים
     data-times-note          הודעת השבוע ("ביום שישי הקרוב מנחה גדולה ב־13:30"). מוסתר כשאין
     data-times-next          "התפילה הבאה: מנחה, היום ב־16:20"
     data-times-updated       "עודכן ב־22.11"
     data-shiurim             השיעורים. ב־<ul>/<ol>: <li class="s-row"> עם .s-topic, .s-day, .s-time, .s-by, .s-note (אם יש).
                              ב־<table>/<tbody>: <tr><th scope="row">נושא</th><td>יום</td><td>שעה</td><td>מגיד השיעור</td></tr>.
                              שיעור של היום מקבל class="is-today"
     data-shiurim-today       "היום: דף יומי ב־19:30, גמרא בעיון ב־20:45". מוסתר כשאין היום שיעור
   ---------- הודעות ואירועים בקהילה ----------
     data-news="3"            ב־<ul>/<ol>: <li class="n-row"> עם <time class="n-date"> ("28.11"), .n-day ("שבת"), .n-title, .n-when, .n-text.
                              אירוע שעבר מקבל class="is-past"
   ---------- בקוד ----------
     window.NachalatYitzchak = { KEY, DEFAULT, load, save, reset, donate, totals, render, items, pick, fmt, MONTHLY_NOTE, nextPrayer }
     totals() מחזיר {raised, goal, remaining, percent, pct, count, monthly, monthlyCount, online, offline, left: {state, days, hours, minutes}}.
     אחרי כל ציור משודר אירוע 'nachalat:render' על document, עם totals() ב־detail (לתצוגה מותאמת אישית).
     donate({amount, name, anonymous, dedication, monthly, item}) מוסיף תרומה לדוגמה (בלי חיוב), מסמן את הפריט כמוקדש ומרענן.
     pick(id) בוחר פריט בטופס ומחזיר true, או false כשהפריט כבר הוקדש או לא קיים.
     window.NACHALAT_DEFAULTS (לא חובה, לפני הסקריפט): תוספות לברירת המחדל ברמת העמוד, למשל { times: { candles: '16:08', havdalah: '17:06' } }.
     מחזיר {ok: true, donation} או {ok: false, error, field}. */
(() => {
  const KEY = 'nachalat-yitzchak-v1';
  const DAYS = ['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת'];
  const MONTHLY_NOTE = 'הוראת קבע נספרת ביעד כ־12 חודשים';
  const ANON = 'בעילום שם';
  const DEFAULT = {
    campaign: {
      title: 'מחדשים את ההיכל',
      text: 'ספסלים ושטנדרים חדשים, ארון קודש חדש, מיזוג שקט ונגישות מלאה. עשרים שנה אחרי שנבנה, ההיכל שלנו מקבל חיים חדשים.',
      goal: 350000,
      start: '2026-11-21',
      end: '2026-12-24',
      /* התחייבויות וצ׳קים מערב ההקדשה, שנרשמו ביד */
      offline: 116500,
      offlineCount: 54,
    },
    /* פריטים להקדשה. qty/left: פריט שיש ממנו כמה יחידות. camp: false לא נספר ביעד של הקמפיין */
    items: [
      { id: 'aron', name: 'ארון הקודש', desc: 'ארון חדש מעץ אגוז מגולף, עם עמודים ופרוכת קטיפה.', price: 54000, img: 'aron', taken: false },
      { id: 'bimah', name: 'הבימה', desc: 'בימה מעץ מלא, עם מעקה מסובב ושולחן קריאה.', price: 26000, img: 'bimah', taken: true, by: 'משפחת רוזנברג', ded: 'לעילוי נשמת הורינו היקרים' },
      { id: 'ramp', name: 'הכניסה הנגישה', desc: 'רמפה רחבה בכניסה הראשית, כדי שכל אחד יוכל להגיע להיכל.', price: 22000, img: 'model', taken: false },
      { id: 'chandelier', name: 'הנברשת המרכזית', desc: 'נברשת פליז גדולה, במרכז ההיכל מעל הבימה.', price: 18000, img: 'chandelier', taken: false },
      { id: 'parochet', name: 'פרוכת לארון הקודש', desc: 'פרוכת קטיפה עם רקמה בחוטי זהב.', price: 12000, img: 'aron', taken: true, by: ANON },
      { id: 'bench', name: 'ספסל שלם עם שטנדרים', desc: 'ספסל של שישה מקומות, עם שלט הקדשה בצד.', price: 7200, img: 'benches', qty: 16, left: 9, taken: false },
      { id: 'seat', name: 'מקום ישיבה קבוע', desc: 'מקום בספסל החדש, עם שלט קטן על השטנדר.', price: 1200, img: 'benches', qty: 96, left: 61, taken: false },
      { id: 'sefarim', name: 'סט ספרים לבית המדרש', desc: 'סט שלם לארון הספרים, עם מדבקת הקדשה בכל כרך.', price: 1800, img: 'sefarim', qty: 10, left: 6, taken: false },
      { id: 'plaque', name: 'שלט בלוח ההנצחה', desc: 'שלט פליז חרוט, לעילוי נשמת, בלוח החדש בכניסה להיכל.', price: 1800, img: 'memorial', qty: 60, left: 43, taken: false },
      { id: 'kiddush', name: 'חסות על קידוש בשבת', desc: 'קידוש לכל הקהילה אחרי התפילה, עם הקדשה בעלון השבת.', price: 1500, img: 'shabbat', qty: 12, left: 7, taken: false, camp: false },
    ],
    /* תרומות שכבר נכנסו באתר. at: שעון ישראל */
    donations: [
      { id: 's1', at: '2026-11-21T21:40', amount: 26000, name: 'משפחת רוזנברג', item: 'bimah', dedication: 'לעילוי נשמת הורינו היקרים' },
      { id: 's2', at: '2026-11-21T21:55', amount: 12000, anonymous: true, item: 'parochet' },
      { id: 's3', at: '2026-11-21T22:10', amount: 360, monthly: true, name: 'משפחת כהן' },
      { id: 's4', at: '2026-11-21T22:30', amount: 10000, anonymous: true },
      { id: 's5', at: '2026-11-22T08:15', amount: 3600, name: 'משפחת אדלר', dedication: 'לזכות ילדינו' },
      { id: 's6', at: '2026-11-22T09:02', amount: 1800, name: 'משפחת פרידמן', item: 'plaque', dedication: 'לעילוי נשמת אבינו ר׳ שמואל ז״ל' },
      { id: 's7', at: '2026-11-22T12:40', amount: 180, monthly: true, name: 'משפחת לוי' },
      { id: 's8', at: '2026-11-22T20:05', amount: 7200, name: 'משפחת גולדברג', item: 'bench', dedication: 'לזכות משפחתנו' },
      { id: 's9', at: '2026-11-22T21:30', amount: 500, anonymous: true },
      { id: 's10', at: '2026-11-23T07:50', amount: 1200, name: 'משפחת שטרן', item: 'seat', dedication: 'לכבוד בר המצווה של בננו' },
      { id: 's11', at: '2026-11-23T10:20', amount: 1000, name: 'משפחת ברגר' },
      { id: 's12', at: '2026-11-23T13:05', amount: 250, monthly: true, name: 'משפחת וייס' },
      { id: 's13', at: '2026-11-23T19:45', amount: 1800, anonymous: true, item: 'sefarim' },
      { id: 's14', at: '2026-11-23T21:10', amount: 2600, name: 'משפחת אזולאי', dedication: 'לעילוי נשמת סבנו ר׳ יוסף ז״ל' },
      { id: 's15', at: '2026-11-24T08:30', amount: 100, monthly: true, name: 'משפחת מזרחי' },
      { id: 's16', at: '2026-11-24T11:15', amount: 3600, name: 'משפחת קליין' },
      { id: 's17', at: '2026-11-24T14:00', amount: 1200, name: 'משפחת שפירא', item: 'seat' },
      { id: 's18', at: '2026-11-24T18:25', amount: 180, monthly: true, anonymous: true },
      { id: 's19', at: '2026-11-24T20:50', amount: 720, name: 'משפחת דהן' },
      { id: 's20', at: '2026-11-25T07:40', amount: 1800, name: 'משפחת הורוביץ', item: 'plaque', dedication: 'לעילוי נשמת אבינו ר׳ אהרן ז״ל' },
      { id: 's21', at: '2026-11-25T09:10', amount: 500, name: 'משפחת ביטון' },
      { id: 's22', at: '2026-11-25T12:35', amount: 5000, name: 'משפחת ליבוביץ', dedication: 'לכבוד הרב ולהצלחת הקהילה' },
      { id: 's23', at: '2026-11-25T17:20', amount: 1500, name: 'משפחת פרץ', item: 'kiddush', dedication: 'לרגל בר המצווה של בננו' },
      { id: 's24', at: '2026-11-25T21:05', amount: 260, monthly: true, name: 'משפחת גרינברג', dedication: 'לזכות הילדים' },
      { id: 's25', at: '2026-11-26T07:55', amount: 360, name: 'משפחת אברהמי' },
      { id: 's26', at: '2026-11-26T09:40', amount: 1200, name: 'משפחת וקנין', item: 'seat' },
    ],
    updates: [
      { date: '2026-11-26', text: 'עברנו את ה־60%! תודה לכל המשפחות. השבוע ועד בית הכנסת בוחר את הנגר שיבנה את הספסלים החדשים.' },
      { date: '2026-11-24', text: 'האדריכל הציג לוועד את התוכנית הסופית לארון הקודש: עץ אגוז, עמודים מגולפים ופרוכת קטיפה חדשה.' },
      { date: '2026-11-22', text: 'הקמפיין נפתח בערב ההקדשה. תודה לכל מי שהגיע, ולמשפחת רוזנברג שהקדישה את הבימה.' },
    ],
    /* זמני תפילות. fri: גם ביום שישי. day: 5 = ערב שבת, 6 = שבת */
    times: {
      weekday: [
        { name: 'שחרית', sub: 'מניין ראשון', time: '06:15', fri: true },
        { name: 'שחרית', sub: 'מניין שני', time: '07:45', fri: true },
        { name: 'מנחה', time: '16:20' },
        { name: 'מעריב', time: '16:50' },
        { name: 'מעריב', sub: 'מניין מאוחר', time: '20:15' },
      ],
      shabbat: [
        { name: 'מנחה וקבלת שבת', time: '16:15', day: 5 },
        { name: 'שחרית', time: '08:00', day: 6 },
        { name: 'מנחה', time: '15:50', day: 6 },
        { name: 'מעריב במוצאי שבת', time: '17:10', day: 6 },
      ],
      note: 'ביום שישי הקרוב מנחה גדולה ב־13:30, בנוסף למניין הרגיל.',
      /* הדלקת נרות וצאת השבת: הגבאי מקליד אותם כל שבוע (שעה, למשל '16:08'). ריק = לא מוצג. לא מחושבים */
      candles: '',
      havdalah: '',
      updated: '2026-11-22',
    },
    /* שיעורים. days: באילו ימים (0 = ראשון) */
    shiurim: [
      { topic: 'הלכה יומית', day: 'א׳ עד ו׳', days: [0, 1, 2, 3, 4, 5], time: '07:00', by: 'רב בית הכנסת', note: 'עשר דקות, אחרי המניין הראשון' },
      { topic: 'דף יומי', day: 'א׳ עד ה׳', days: [0, 1, 2, 3, 4], time: '19:30', by: 'אחד מאברכי הקהילה', note: 'לפני מעריב המאוחר' },
      { topic: 'גמרא בעיון', day: 'יום ב׳', days: [1], time: '20:45', by: 'רב בית הכנסת' },
      { topic: 'פרשת השבוע', day: 'יום ה׳', days: [4], time: '20:45', by: 'רב בית הכנסת' },
      { topic: 'הלכות שבת', day: 'שבת', days: [6], time: '15:00', by: 'רב בית הכנסת', note: 'לפני מנחה' },
      { topic: 'אבות ובנים', day: 'מוצאי שבת', days: [6], time: '18:15', by: 'צוות בית הכנסת', note: 'בחורף, עם פרסים לילדים' },
    ],
    /* הודעות ואירועים בקהילה */
    news: [
      { date: '2026-11-28', title: 'קידוש קהילתי', when: 'שבת, אחרי התפילה', text: 'קידוש לכל הקהילה, לכבוד המשפחות שהצטרפו לקמפיין בערב ההקדשה.' },
      { date: '2026-12-04', title: 'נרות חנוכה בבית הכנסת', when: 'כל ערב בחנוכה, בין מנחה למעריב', text: 'מדליקים יחד, ונשארים לכוס תה ולסופגנייה.' },
      { date: '2026-12-05', title: 'אבות ובנים חגיגי לחנוכה', when: 'מוצאי שבת, 18:15', text: 'לימוד, סופגניות והגרלה על ספרים לילדים שהגיעו כל החורף.' },
      { date: '2026-12-10', title: 'סיום מסכת בשיעור הגמרא', when: 'יום חמישי, 20:45', text: 'סיום וסעודת מצווה באולם. כולם מוזמנים.' },
    ],
  };

  /* תוספות לברירת המחדל ברמת העמוד: window.NACHALAT_DEFAULTS, שמוגדר לפני הסקריפט הזה.
     הגרסה הסופית ומסך הגבאי משתמשים בזה לזמני הדלקת הנרות וצאת השבת של השבוע (נוספו אחרי המשוב),
     כך ששלושת העיצובים נשארים בדיוק כמו שהלקוח ראה אותם. אובייקטים מתמזגים, מערכים מוחלפים */
  const deepMerge = (a, b) => { for (const k of Object.keys(b || {})) { if (b[k] && typeof b[k] === 'object' && !Array.isArray(b[k]) && a[k] && typeof a[k] === 'object' && !Array.isArray(a[k])) deepMerge(a[k], b[k]); else a[k] = b[k]; } return a; };
  if (window.NACHALAT_DEFAULTS && typeof window.NACHALAT_DEFAULTS === 'object') { try { deepMerge(DEFAULT, JSON.parse(JSON.stringify(window.NACHALAT_DEFAULTS))); } catch (e) { /* בלי תוספות */ } }

  /* ---------- שמירה וטעינה ---------- */
  const clone = (o) => JSON.parse(JSON.stringify(o));
  let mem = null;                                                  /* כשאין localStorage (גלישה פרטית): נשמר בזיכרון עד רענון */
  const load = () => {
    let d = mem;
    if (!d) { try { d = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) { d = null; } }
    const base = clone(DEFAULT);
    if (!d || typeof d !== 'object') return base;
    /* שמירה חלקית או ישנה: משלימים מה שחסר מברירת המחדל */
    for (const k of Object.keys(base)) {
      if (Array.isArray(base[k])) { if (Array.isArray(d[k])) base[k] = d[k]; }
      else if (d[k] && typeof d[k] === 'object') base[k] = Object.assign(base[k], d[k]);
    }
    if (d.updated) base.updated = d.updated;
    return base;
  };
  const save = (d) => {
    const data = Object.assign({}, d, { updated: Date.now() });
    try { localStorage.setItem(KEY, JSON.stringify(data)); mem = null; } catch (e) { mem = data; }
    render();
    return true;
  };
  const reset = () => { mem = null; try { localStorage.removeItem(KEY); } catch (e) { /* אין גישה */ } render(); };

  /* ---------- עזרים ---------- */
  const NF = new Intl.NumberFormat('he-IL', { maximumFractionDigits: 0 });
  const fmt = (n) => `${NF.format(Math.round(Number(n) || 0))} ₪`;
  const dm = (iso) => { const [, m, d] = String(iso).slice(0, 10).split('-'); return `${Number(d)}.${Number(m)}`; };
  const mins = (t) => { const m = /^(\d{1,2}):(\d{2})$/.exec(String(t || '').trim()); return m ? Number(m[1]) * 60 + Number(m[2]) : NaN; };
  const daysWord = (n) => (n === 1 ? 'יום אחד' : n === 2 ? 'יומיים' : `${n} ימים`);
  /* עכשיו לפי שעון ישראל */
  const now = () => {
    const p = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jerusalem', weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date()).map((x) => [x.type, x.value]));
    const date = `${p.year}-${p.month}-${p.day}`;
    return { day: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(p.weekday), min: Number(p.hour) * 60 + Number(p.minute), date, stamp: `${date}T${p.hour}:${p.minute}` };
  };
  /* הפרש ימים בין שני תאריכים בפורמט YYYY-MM-DD */
  const dayDiff = (a, b) => Math.round((Date.UTC(...b.split('-').map((x, i) => Number(x) - (i === 1 ? 1 : 0))) - Date.UTC(...a.split('-').map((x, i) => Number(x) - (i === 1 ? 1 : 0)))) / 86400000);
  const weekdayOf = (iso) => new Date(Date.UTC(...iso.split('-').map((x, i) => Number(x) - (i === 1 ? 1 : 0)))).getUTCDay();
  const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text != null && text !== '') e.textContent = text; return e; };
  const isList = (n) => n.tagName === 'UL' || n.tagName === 'OL';
  const isTable = (n) => n.tagName === 'TABLE' || n.tagName === 'TBODY';
  const fill = (sel, fn) => document.querySelectorAll(sel).forEach(fn);
  const text = (sel, t) => fill(sel, (n) => { n.textContent = t; });
  const num = (v, def) => { const n = parseInt(v, 10); return Number.isFinite(n) && n > 0 ? n : def; };

  /* ---------- הקמפיין ---------- */
  const itemOf = (d, id) => (d.items || []).find((x) => x.id === id);
  const counts = (dn, d) => dn.camp !== false && !(dn.item && itemOf(d, dn.item) && itemOf(d, dn.item).camp === false);
  const value = (dn) => (dn.monthly ? Number(dn.amount) * 12 : Number(dn.amount)) || 0;
  const available = (it) => !it.taken && (it.qty ? Number(it.left) > 0 : true);

  /* כמה זמן נשאר, עד סוף יום הסיום (חצות, שעון ישראל) */
  const countdown = (c) => {
    const n = now();
    if (!/^\d{4}-\d{2}-\d{2}$/.test(String(c.end || ''))) return { state: 'running', days: 0, hours: 0, minutes: 0, total: Infinity };
    const dd = dayDiff(n.date, c.end);
    if (dd < 0) return { state: 'ended', days: 0, hours: 0, minutes: 0, total: 0 };
    const total = dd * 1440 + (1440 - n.min);
    return { state: dd === 0 ? 'last' : 'running', days: Math.floor(total / 1440), hours: Math.floor((total % 1440) / 60), minutes: total % 60, total };
  };

  const totals = () => {
    const d = load(), c = d.campaign || {};
    const camp = (d.donations || []).filter((x) => counts(x, d));
    const online = camp.reduce((s, x) => s + value(x), 0);
    const raised = online + (Number(c.offline) || 0);
    const goal = Number(c.goal) || 0;
    const percent = goal ? (raised / goal) * 100 : 0;
    const monthly = camp.filter((x) => x.monthly);
    return {
      raised, goal, online, offline: Number(c.offline) || 0,
      remaining: Math.max(0, goal - raised),
      percent, pct: Math.floor(percent),
      count: camp.length + (Number(c.offlineCount) || 0),
      monthly: monthly.reduce((s, x) => s + Number(x.amount || 0), 0), monthlyCount: monthly.length,
      left: countdown(c),
    };
  };

  /* ---------- תרומה לדוגמה ---------- */
  const donate = (o = {}) => {
    const d = load();
    const it = o.item ? itemOf(d, o.item) : null;
    if (o.item && !it) return { ok: false, error: 'הפריט הזה לא קיים.', field: 'item' };
    if (it && !available(it)) return { ok: false, error: 'הפריט הזה כבר הוקדש. אפשר לבחור פריט אחר.', field: 'item' };
    const monthly = it ? false : !!o.monthly;
    const amount = it ? Number(it.price) : Math.round(Number(String(o.amount ?? '').replace(/[^\d.]/g, '')));
    if (!Number.isFinite(amount) || amount < 10) return { ok: false, error: 'נא לבחור סכום, 10 ₪ לפחות.', field: 'amount' };
    if (amount > 1000000) return { ok: false, error: 'לתרומה בסכום כזה, נשמח שתדברו עם הגבאי.', field: 'amount' };
    const anonymous = !!o.anonymous;
    const name = anonymous ? '' : String(o.name || '').replace(/\s+/g, ' ').trim().slice(0, 40);
    if (!anonymous && !name) return { ok: false, error: 'נא לכתוב שם לפרסום, או לסמן "בעילום שם".', field: 'name' };
    const dedication = String(o.dedication || '').replace(/\s+/g, ' ').trim().slice(0, 80);
    const dn = { id: `m${Date.now().toString(36)}`, at: now().stamp, amount, mine: true };
    if (name) dn.name = name;
    if (anonymous) dn.anonymous = true;
    if (monthly) dn.monthly = true;
    if (dedication) dn.dedication = dedication;
    if (it) {
      dn.item = it.id;
      if (it.camp === false) dn.camp = false;
      if (it.qty) { it.left = Math.max(0, Number(it.left) - 1); if (it.left === 0) it.taken = true; }
      else { it.taken = true; it.by = anonymous ? ANON : name; if (dedication) it.ded = dedication; }
    }
    d.donations = (d.donations || []).concat(dn).slice(-300);
    save(d);
    return { ok: true, donation: dn, item: it || null };
  };

  /* ---------- זמני תפילות ---------- */
  const label = (r) => (r.sub ? `${r.name}, ${r.sub}` : r.name);
  /* התפילות של יום מסוים: א׳ עד ה׳ ימי חול, שישי שחרית של חול ואז קבלת שבת, שבת תפילות השבת */
  const dayRows = (t, i) => {
    const out = [];
    if (i <= 4) (t.weekday || []).forEach((r, k) => out.push({ list: 'weekday', k, r }));
    if (i === 5) (t.weekday || []).forEach((r, k) => { if (r.fri) out.push({ list: 'weekday', k, r }); });
    if (i >= 5) (t.shabbat || []).forEach((r, k) => { if (Number(r.day ?? 6) === i) out.push({ list: 'shabbat', k, r }); });
    return out.filter((x) => Number.isFinite(mins(x.r.time))).sort((a, b) => mins(a.r.time) - mins(b.r.time));
  };
  const nextPrayer = (t) => {
    const n = now();
    for (let k = 0; k < 8; k++) {
      const i = (n.day + k) % 7;
      const rows = dayRows(t, i).filter((x) => k > 0 || mins(x.r.time) > n.min);
      if (!rows.length) continue;
      const x = rows[0];
      const when = k === 0 ? 'היום' : k === 1 ? 'מחר' : i === 6 ? 'בשבת' : `ביום ${DAYS[i]}`;
      return Object.assign({}, x, { text: `התפילה הבאה: ${label(x.r)}, ${when} ב־${x.r.time}` });
    }
    return null;
  };
  /* הדלקת נרות וצאת השבת: מה שהגבאי הקליד (שעה בפורמט 16:08), או ריק. לא מחשבים אותם */
  const zman = (t, k) => { const v = String((t || {})[k] || '').trim(); return /^\d{1,2}:\d{2}$/.test(v) ? v : ''; };
  /* שורות לרשימה: k הוא מספר התפילה (לסימון "הבאה"). שורות הדלקת נרות וצאת השבת מקבלות k = -1 */
  const rowsOf = (t, list) => {
    const out = (t[list] || []).map((r, k) => ({ r, k }));
    if (list === 'shabbat') {
      const c = zman(t, 'candles'), h = zman(t, 'havdalah');
      if (c) out.unshift({ r: { name: 'הדלקת נרות', time: c }, k: -1, zman: 'candles' });
      if (h) out.push({ r: { name: 'צאת השבת', time: h }, k: -1, zman: 'havdalah' });
    }
    return out;
  };
  const setNext = (e, on) => { e.classList.toggle('is-next', on); if (on) e.setAttribute('aria-current', 'time'); else e.removeAttribute('aria-current'); };
  const renderRows = (node, rows, cur) => {
    node.replaceChildren();
    rows.forEach(({ r, k, zman: z }) => {
      const on = k >= 0 && k === cur;
      const mark = (e) => { if (k >= 0) e.dataset.row = String(k); if (z) { e.classList.add('is-zman'); e.dataset.zman = z; } setNext(e, on); return e; };
      if (isTable(node)) {
        const tr = mark(el('tr', 't-row')), th = el('th', 't-name', r.name);
        th.scope = 'row';
        if (r.sub) { th.append(' '); th.append(el('small', 't-sub', r.sub)); }
        tr.append(th, el('td', 't-time', r.time));
        (node.tagName === 'TABLE' ? (node.tBodies[0] || node.appendChild(document.createElement('tbody'))) : node).append(tr);
      } else if (node.tagName === 'DL') {
        const dt = mark(el('dt', 't-name', label(r))), dd = mark(el('dd', 't-time', r.time));
        dd.removeAttribute('aria-current');
        node.append(dt, dd);
      } else {
        const li = mark(el(isList(node) ? 'li' : 'div', 't-row'));
        li.append(el('span', 't-name', r.name));
        if (r.sub) li.append(el('span', 't-sub', r.sub));
        li.append(el('span', 't-time', r.time));
        node.append(li);
      }
    });
  };

  /* ---------- ציור ---------- */
  /* כותב טקסט רק כשהוא השתנה, כדי לא לשבור עיצוב שמפצל את הטקסט לחלקים */
  const put = (e, t) => { if (e.textContent !== t) e.textContent = t; };
  const putAll = (sel, t) => fill(sel, (e) => put(e, t));

  /* מה שתלוי בשעון: הספירה לאחור, התפילה הבאה והשיעורים של היום. רץ כל דקה, בלי לבנות רשימות מחדש */
  function renderClock(d, T) {
    const n = now(), L = T.left, t = d.times || {}, nx = nextPrayer(t);
    const dl = L.state === 'ended' ? 'הקמפיין הסתיים. תודה לכל השותפים!' : L.state === 'last' ? 'היום האחרון לקמפיין' : L.days === 1 ? 'נשאר יום אחד' : `נשארו ${daysWord(L.days)}`;
    fill('[data-camp-days],[data-camp-hours],[data-camp-minutes],[data-camp-days-label]', (e) => { e.dataset.state = L.state; });
    putAll('[data-camp-days]', String(L.days));
    putAll('[data-camp-hours]', String(L.hours));
    putAll('[data-camp-minutes]', String(L.minutes));
    putAll('[data-camp-days-label]', dl);
    fill('[data-camp-live]', (e) => { e.hidden = L.state === 'ended'; });
    fill('[data-camp-ended]', (e) => { e.hidden = L.state !== 'ended'; });
    /* התפילה הבאה: הטקסט, והסימון ברשימות */
    putAll('[data-times-next]', nx ? nx.text : '');
    for (const list of ['weekday', 'shabbat']) {
      const cur = nx && nx.list === list ? String(nx.k) : null;
      fill(`[data-times-${list}] [data-row]`, (e) => setNext(e, e.dataset.row === cur && e.tagName !== 'DD'));
      fill(`[data-times-${list}] dd[data-row]`, (e) => e.classList.toggle('is-next', e.dataset.row === cur));
    }
    /* השיעורים של היום */
    const sh = d.shiurim || [];
    const isToday = (s) => Array.isArray(s.days) && s.days.map(Number).includes(n.day);
    fill('[data-shiurim] [data-row]', (e) => { const s = sh[Number(e.dataset.row)]; e.classList.toggle('is-today', !!(s && isToday(s))); });
    const todays = sh.filter(isToday);
    fill('[data-shiurim-today]', (e) => { put(e, todays.length ? `היום: ${todays.map((s) => `${s.topic} ב־${s.time}`).join(', ')}` : ''); e.hidden = !todays.length; });
  }
  function tick() {
    const d = load(), T = totals();
    renderClock(d, T);
    document.dispatchEvent(new CustomEvent('nachalat:render', { detail: Object.assign({ tick: true }, T) }));
  }

  /* שמירת הפוקוס: אם הכפתור שהיה בפוקוס נבנה מחדש, מחזירים את הפוקוס לכפתור החדש של אותו פריט */
  const LISTS = '[data-dedications],[data-camp-donors],[data-camp-updates],[data-news],[data-shiurim],[data-times-weekday],[data-times-shabbat]';
  const focusKey = () => {
    const a = document.activeElement;
    if (!a || a === document.body || !a.closest) return null;
    const box = a.closest(LISTS);
    if (!box) return null;
    const row = a.closest('[data-item]');
    return { a, i: Array.from(document.querySelectorAll(LISTS)).indexOf(box), pick: a.getAttribute('data-pick-item'), item: row ? row.getAttribute('data-item') : null };
  };
  const restoreFocus = (k) => {
    if (!k || k.a.isConnected) return;
    const box = document.querySelectorAll(LISTS)[k.i];
    if (!box) return;
    const id = k.pick || k.item;
    let t = id && box.querySelector(`[data-pick-item="${CSS.escape(id)}"]`);
    if (!t && k.item) { t = box.querySelector(`[data-item="${CSS.escape(k.item)}"]`); if (t) t.tabIndex = -1; }
    if (!t) { t = box; if (!t.hasAttribute('tabindex')) t.tabIndex = -1; }
    t.focus({ preventScroll: true });
  };

  function render() {
    const fk = focusKey();
    const d = load(), c = d.campaign || {}, T = totals(), n = now();

    /* הקמפיין */
    text('[data-camp-title]', c.title || '');
    text('[data-camp-text]', c.text || '');
    text('[data-camp-raised]', fmt(T.raised));
    text('[data-camp-goal]', fmt(T.goal));
    text('[data-camp-remaining]', fmt(T.remaining));
    text('[data-camp-percent]', `${T.pct}%`);
    text('[data-camp-count]', NF.format(T.count));
    text('[data-camp-monthly-note]', MONTHLY_NOTE);
    text('[data-camp-end]', c.end ? `עד ${dm(c.end)}` : '');
    const p = Math.min(1, Math.max(0, T.percent / 100)), pct = `${Math.round(p * 1000) / 10}%`;
    const vt = `נאספו ${fmt(T.raised)} מתוך ${fmt(T.goal)}, ${T.pct}%`;
    fill('[data-camp-progress]', (e) => {
      if (e.tagName === 'PROGRESS') { e.max = 100; e.value = Math.min(100, T.percent); }
      else { e.setAttribute('role', 'progressbar'); e.setAttribute('aria-valuemin', '0'); e.setAttribute('aria-valuemax', '100'); e.setAttribute('aria-valuenow', String(Math.min(100, T.pct))); }
      e.setAttribute('aria-valuetext', vt);
      if (!e.hasAttribute('aria-label') && !e.hasAttribute('aria-labelledby')) e.setAttribute('aria-label', 'התקדמות הקמפיין');
      e.style.setProperty('--p', String(p)); e.style.setProperty('--pct', pct);
    });
    fill('[data-camp-fill]', (e) => { e.style.width = pct; });

    /* התורמים האחרונים */
    /* האחרונות ראשונות: לפי הסדר שבו נוספו (התרומה החדשה נכנסת לסוף הרשימה) */
    const recent = (d.donations || []).slice().reverse();
    fill('[data-camp-donors]', (box) => {
      box.replaceChildren();
      recent.slice(0, num(box.getAttribute('data-camp-donors'), 8)).forEach((x) => {
        const row = el(isList(box) ? 'li' : 'div', 'donor');
        const anon = x.anonymous || !x.name;
        row.append(el('span', 'd-name', anon ? ANON : x.name));
        row.append(el('span', 'd-amount', x.monthly ? `${fmt(x.amount)} לחודש` : fmt(x.amount)));
        const it = x.item && itemOf(d, x.item);
        if (it) row.append(el('span', 'd-item', `הקדשה: ${it.name}`));
        if (x.dedication) row.append(el('span', 'd-ded', x.dedication));
        const day = String(x.at).slice(0, 10), diff = dayDiff(day, n.date);
        const w = el('time', 'd-when', diff === 0 ? 'היום' : diff === 1 ? 'אתמול' : dm(day));
        w.dateTime = String(x.at);
        row.append(w);
        if (x.monthly) row.classList.add('is-monthly');
        if (anon) row.classList.add('is-anon');
        if (x.mine) row.classList.add('is-mine');
        box.append(row);
      });
    });

    /* עדכונים מהקמפיין */
    fill('[data-camp-updates]', (box) => {
      box.replaceChildren();
      /* החדש ראשון: לפי התאריך, ובאותו תאריך מה שנוסף אחרון */
      (d.updates || []).map((u, i) => ({ u, i })).sort((a, b) => String(b.u.date).localeCompare(String(a.u.date)) || b.i - a.i).map((x) => x.u).slice(0, num(box.getAttribute('data-camp-updates'), 3)).forEach((u) => {
        const row = el(isList(box) ? 'li' : 'div', 'u-row');
        const t = el('time', 'u-date', dm(u.date)); t.dateTime = u.date;
        row.append(t, el('span', 'u-text', u.text));
        box.append(row);
      });
    });

    /* הקדשות */
    fill('[data-dedications]', (box) => {
      const want = (box.getAttribute('data-dedications') || '').trim(), base = box.getAttribute('data-img-base');
      let list = d.items || [];
      if (/^\d+$/.test(want)) list = list.slice(0, Number(want));
      else if (want) list = want.split(',').map((s) => itemOf(d, s.trim())).filter(Boolean);
      box.replaceChildren();
      list.forEach((it) => {
        const open = available(it);
        const row = el(isList(box) ? 'li' : 'div', `ded ${open ? 'is-available' : 'is-taken'}`);
        row.dataset.item = it.id;
        if (base && it.img) { const im = el('img', 'ded-img'); im.src = `${base}${it.img}-560.webp`; im.alt = ''; im.loading = 'lazy'; im.width = 560; im.height = 373; row.append(im); }
        row.append(el('span', 'ded-name', it.name));
        if (it.desc) row.append(el('span', 'ded-desc', it.desc));
        row.append(el('span', 'ded-price', fmt(it.price)));
        row.append(el('span', 'ded-state', it.qty ? (open ? `נשארו ${it.left} מתוך ${it.qty}` : 'כולם הוקדשו') : (open ? 'פנוי להקדשה' : 'הוקדש')));
        if (!it.qty && !open && (it.by || it.ded)) row.append(el('span', 'ded-by', [it.by, it.ded].filter(Boolean).join(' · ')));
        if (open) {
          const b = el('button', 'ded-btn', 'להקדיש');
          b.type = 'button'; b.dataset.pickItem = it.id;
          b.setAttribute('aria-label', `להקדיש: ${it.name}, ${fmt(it.price)}`);
          row.append(b);
        }
        box.append(row);
      });
    });

    /* זמני תפילות (הסימון של התפילה הבאה נעשה ב־renderClock) */
    const t = d.times || {};
    fill('[data-times-weekday]', (e) => renderRows(e, rowsOf(t, 'weekday'), -1));
    fill('[data-times-shabbat]', (e) => renderRows(e, rowsOf(t, 'shabbat'), -1));
    const note = String(t.note || '').trim();
    fill('[data-times-note]', (e) => { put(e, note); e.hidden = !note; });
    text('[data-times-updated]', t.updated ? `עודכן ב־${dm(t.updated)}` : '');
    for (const k of ['candles', 'havdalah']) {
      const v = zman(t, k);
      fill(`[data-times-${k}]`, (e) => { put(e, v); e.hidden = !v; });
    }
    fill('[data-times-zmanim]', (e) => { e.hidden = !zman(t, 'candles') && !zman(t, 'havdalah'); });

    /* שיעורים (הסימון של היום נעשה ב־renderClock) */
    const sh = d.shiurim || [];
    fill('[data-shiurim]', (box) => {
      box.replaceChildren();
      const target = box.tagName === 'TABLE' ? (box.tBodies[0] || box.appendChild(document.createElement('tbody'))) : box;
      sh.forEach((s, k) => {
        let row;
        if (isTable(box)) {
          row = el('tr', 's-row');
          const th = el('th', 's-topic', s.topic); th.scope = 'row';
          if (s.note) { th.append(' '); th.append(el('small', 's-note', s.note)); }
          row.append(th, el('td', 's-day', s.day), el('td', 's-time', s.time), el('td', 's-by', s.by));
        } else {
          row = el(isList(box) ? 'li' : 'div', 's-row');
          row.append(el('span', 's-topic', s.topic), el('span', 's-day', s.day), el('span', 's-time', s.time), el('span', 's-by', s.by));
          if (s.note) row.append(el('span', 's-note', s.note));
        }
        row.dataset.row = String(k);
        target.append(row);
      });
    });

    /* הודעות ואירועים */
    fill('[data-news]', (box) => {
      box.replaceChildren();
      (d.news || []).slice().sort((a, b) => String(a.date).localeCompare(String(b.date))).slice(0, num(box.getAttribute('data-news'), 6)).forEach((x) => {
        const row = el(isList(box) ? 'li' : 'div', 'n-row');
        if (/^\d{4}-\d{2}-\d{2}$/.test(String(x.date))) {
          const tm = el('time', 'n-date', dm(x.date)); tm.dateTime = x.date;
          row.append(tm, el('span', 'n-day', DAYS[weekdayOf(x.date)]));
          if (x.date < n.date) row.classList.add('is-past');
        }
        row.append(el('span', 'n-title', x.title));
        if (x.when) row.append(el('span', 'n-when', x.when));
        if (x.text) row.append(el('span', 'n-text', x.text));
        box.append(row);
      });
    });

    /* הספירה לאחור, התפילה הבאה והשיעורים של היום */
    renderClock(d, T);
    /* טפסי תרומה */
    document.querySelectorAll('form[data-donate-form]').forEach(wire);
    restoreFocus(fk);
    /* למי שבונה תצוגה משלו (למשל קיר אבנים שמתמלא): אירוע אחרי כל ציור, עם הסכומים */
    document.dispatchEvent(new CustomEvent('nachalat:render', { detail: T }));
  }

  /* ---------- טופס התרומה ---------- */
  const q = (f, name) => f.elements.namedItem(name);
  const radios = (f, name) => Array.from(f.querySelectorAll(`input[name="${name}"]`));
  const readForm = (f) => {
    const d = load(), sel = q(f, 'item'), it = sel && sel.value ? itemOf(d, sel.value) : null;
    const checked = radios(f, 'amount').find((r) => r.checked);
    const other = q(f, 'amount_other');
    let amount = checked && checked.value !== 'other' ? checked.value : (other ? other.value : '');
    const fr = radios(f, 'freq').find((r) => r.checked), mc = q(f, 'monthly');
    const monthly = fr ? fr.value === 'monthly' : !!(mc && mc.checked);
    const anon = q(f, 'anonymous');
    if (it) amount = it.price;
    return { it, amount: Number(amount) || 0, monthly: it ? false : monthly, anonymous: !!(anon && anon.checked), name: q(f, 'name') ? q(f, 'name').value : '', dedication: q(f, 'dedication') ? q(f, 'dedication').value : '' };
  };
  const sync = (f) => {
    const v = readForm(f), had = !!f.dataset.itemPrice, am = radios(f, 'amount');
    /* בוחרים פריט: הסכום הוא מחיר הפריט. מסמנים "סכום אחר" (או מבטלים את הסימון), כדי שלא יישאר סכום ישן מודגש.
       מבטלים את הפריט: מחזירים את הסכום שהיה מסומן קודם */
    if (v.it && !had) { const c = am.find((r) => r.checked); f.dataset.prevAmount = c ? c.value : ''; }
    if (v.it) {
      const hasOther = am.some((r) => r.value === 'other');
      am.forEach((r) => { r.checked = hasOther && r.value === 'other'; r.disabled = true; });
    } else {
      am.forEach((r) => { r.disabled = false; });
      if (had) { const pv = f.dataset.prevAmount || ''; am.forEach((r) => { r.checked = r.value === pv; }); delete f.dataset.prevAmount; }
    }
    const other = q(f, 'amount_other');
    if (other) { other.disabled = !!v.it; if (v.it) other.value = String(v.it.price); else if (had && other.value === f.dataset.itemPrice) other.value = ''; }
    f.dataset.itemPrice = v.it ? String(v.it.price) : '';
    radios(f, 'freq').forEach((r) => { if (r.value === 'monthly') r.disabled = !!v.it; if (v.it && r.value === 'once') r.checked = true; });
    const nm = q(f, 'name');
    if (nm) { nm.disabled = v.anonymous; nm.required = !v.anonymous; }
    const w = readForm(f);
    const s = w.it ? `הקדשת ${w.it.name}: ${fmt(w.it.price)}, תרומה חד־פעמית`
      : !w.amount ? 'בחרו סכום לתרומה'
      : w.monthly ? `תרומה חודשית של ${fmt(w.amount)}, נספרת ביעד כ־${fmt(w.amount * 12)}`
      : `תרומה חד־פעמית של ${fmt(w.amount)}`;
    f.querySelectorAll('[data-donate-summary]').forEach((e) => { e.textContent = s; });
  };
  const fillItems = (f) => {
    const sel = q(f, 'item');
    if (!sel || sel.tagName !== 'SELECT') return;
    const keep = sel.value, d = load();
    sel.replaceChildren(el('option', '', 'בלי הקדשת פריט'));
    sel.options[0].value = '';
    (d.items || []).filter(available).forEach((it) => { const o = el('option', '', `${it.name} · ${fmt(it.price)}`); o.value = it.id; sel.append(o); });
    sel.value = Array.from(sel.options).some((o) => o.value === keep) ? keep : '';
  };
  const msg = (f, sel) => f.querySelector(sel) || document.querySelector(sel);
  function wire(f) {
    fillItems(f);
    const err = msg(f, '[data-donate-error]'), thanks = msg(f, '[data-donate-thanks]');
    if (!f.dataset.wired) {
      f.dataset.wired = '1';
      f.noValidate = true;
      if (err) { err.setAttribute('role', 'alert'); err.hidden = true; }
      if (thanks) { thanks.setAttribute('role', 'status'); thanks.setAttribute('tabindex', '-1'); thanks.hidden = true; }
      f.addEventListener('input', (e) => {
        if (e.target.name === 'amount_other' && e.target.value) radios(f, 'amount').forEach((r) => { r.checked = r.value === 'other'; });
        if (e.target.name === 'amount' && e.target.value !== 'other') { const o = q(f, 'amount_other'); if (o) o.value = ''; }
        if (err) err.hidden = true;
        sync(f);
      });
      f.addEventListener('change', () => sync(f));
      f.addEventListener('submit', (e) => {
        e.preventDefault();
        const v = readForm(f);
        const r = donate({ amount: v.amount, name: v.name, anonymous: v.anonymous, dedication: v.dedication, monthly: v.monthly, item: v.it ? v.it.id : '' });
        if (!r.ok) {
          if (err) { err.textContent = r.error; err.hidden = false; }
          const fld = r.field === 'amount' ? (q(f, 'amount_other') && !q(f, 'amount_other').disabled ? q(f, 'amount_other') : radios(f, 'amount')[0]) : q(f, r.field);
          if (fld && fld.focus) fld.focus();
          return;
        }
        const x = r.donation, who = x.anonymous ? '' : `, ${x.name}`;
        const what = r.item ? `ההקדשה של ${r.item.name} נרשמה` : x.monthly ? `הוראת הקבע של ${fmt(x.amount)} לחודש נרשמה` : `התרומה של ${fmt(x.amount)} נרשמה`;
        if (thanks) {
          thanks.textContent = `תודה רבה${who}! ${what}. זו דוגמה בלבד: לא חויב שום כרטיס ולא בוצע תשלום. התרומה נוספה לרשימת התורמים ולמד היעד רק כאן, בדפדפן שלכם. באתר אמיתי עוברים עכשיו לדף תשלום מאובטח של חברת הסליקה, והקבלה נשלחת במייל.`;
          thanks.hidden = false;
          thanks.focus({ preventScroll: true });
          thanks.scrollIntoView({ block: 'nearest', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        }
        if (err) err.hidden = true;
        f.reset();
        delete f.dataset.itemPrice; delete f.dataset.prevAmount;
        f.classList.add('is-done');
        fillItems(f); sync(f);
      });
    }
    sync(f);
  }

  /* בחירת פריט להקדשה מתוך הרשימה: ממלא את הטופס וגולל אליו. פריט שכבר הוקדש (או לא קיים): מחזיר false ולא עושה כלום */
  const pick = (id) => {
    const d = load(), it = itemOf(d, id);
    if (!it || !available(it)) return false;
    document.dispatchEvent(new CustomEvent('nachalat:pick', { detail: { id, item: it } }));
    const f = document.querySelector('form[data-donate-form]');
    if (!f) return true;
    fillItems(f);
    const sel = q(f, 'item');
    if (sel) { sel.value = id; if (sel.value !== id) return false; sel.dispatchEvent(new Event('change', { bubbles: true })); }
    const th = msg(f, '[data-donate-thanks]'); if (th) th.hidden = true;
    f.scrollIntoView({ block: 'start', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    const nm = q(f, 'name');
    const focusEl = nm && !nm.disabled ? nm : sel;
    if (focusEl) setTimeout(() => focusEl.focus({ preventScroll: true }), 350);
    return true;
  };
  document.addEventListener('click', (e) => {
    const b = e.target.closest && e.target.closest('[data-pick-item]');
    if (b) { e.preventDefault(); pick(b.getAttribute('data-pick-item')); return; }
    const r = e.target.closest && e.target.closest('[data-demo-reset]');
    if (r) { e.preventDefault(); reset(); document.querySelectorAll('[data-donate-thanks]').forEach((x) => { x.hidden = true; }); }
  });

  const items = () => load().items;
  window.NachalatYitzchak = { KEY, DEFAULT, DAYS, MONTHLY_NOTE, load, save, reset, donate, totals, render, items, pick, fmt, nextPrayer: () => nextPrayer(load().times || {}) };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render); else render();
  /* כל דקה: רק מה שתלוי בשעון (בלי לבנות רשימות מחדש, כדי לא להזיז פוקוס) */
  setInterval(tick, 60000);
  /* שינוי בלשונית אחרת (מסך הגבאי, או תרומה) מתעדכן כאן מיד */
  window.addEventListener('storage', (e) => { if (e.key === KEY || e.key === null) { mem = null; render(); } });
})();
