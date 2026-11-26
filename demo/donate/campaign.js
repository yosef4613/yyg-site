/* סל ברכה (עמותה לדוגמה): הקמפיין, מד היעד, התורמים והעדכונים מהשטח.
   נתוני ברירת המחדל כתובים כאן. מה שמשנים במסך הניהול לדוגמה (/demo/donate/admin/), וכל תרומה לדוגמה שנשלחת מהטופס,
   נשמרים רק בדפדפן של מי שמנסה (localStorage, המפתח salbracha-campaign-v1). באתר אמיתי הכול נשמר בשרת, והתרומה עוברת בחברת הסליקה.
   כאן לא נגבה כסף בשום שלב.

   איך סופרים: תרומה חד־פעמית נספרת ביעד בסכום שלה. הוראת קבע (תרומה חודשית) נספרת ביעד כ־12 חודשים (12 × הסכום),
   כמו שנהוג בקמפיינים. את ההסבר הזה כדאי להציג ליד הבחירה "חודשי": SalBracha.MONTHLY_NOTE, או התכונה data-camp-monthly-note.
   לסכום נוסף גם campaign.offline: תרומות במזומן, בצ׳קים ובהעברה שנרשמו ביחד (בלי פירוט), ו־campaign.offlineCount: כמה תרומות כאלה.

   שימוש בעיצוב: מוסיפים אלמנטים עם התכונות האלה, והסקריפט ממלא אותם (תמיד כטקסט בלבד, textContent):
     data-camp-title          כותרת הקמפיין ("חורף של סלים מלאים")
     data-camp-subtitle       שורת משנה
     data-camp-raised         כמה נאסף ("152,840 ₪")
     data-camp-goal           היעד ("250,000 ₪")
     data-camp-remaining      כמה חסר ליעד ("97,160 ₪", או "0 ₪" אחרי היעד)
     data-camp-percent        אחוז מהיעד ("61%"). יכול לעבור את 100%
     data-camp-count          מספר התורמים (כל תרומה = תורם אחד, כולל מה שנרשם ביד)
     data-camp-monthly-count  כמה תורמים בהוראת קבע
     data-camp-monthly-note   ההסבר על הוראת קבע (MONTHLY_NOTE)
     data-camp-progress       המסילה של מד היעד. מקבל role="progressbar", aria-valuemin/max/now, aria-valuetext,
                              aria-label (אם אין), ומשתני CSS:  --p  מספר בלי יחידה 0 עד 100 (מוגבל ל־100),
                              --p-raw  האחוז האמיתי, --pct  "61%". למשל: width:calc(var(--p) * 1%) או conic-gradient(... calc(var(--p) * 1%), ...)
     data-camp-fill           המילוי של המד: מקבל style.width = האחוז (עד 100%) וגם --p
     data-camp-days           כמה ימים נשארו, לפי שעון ישראל: "21". אחרי הסיום "הסתיים". ביום האחרון אין מספר:
                              האלמנט מתרוקן ומוסתר (hidden), ו־data-camp-days-label אומר "היום האחרון" (בלי "0" יתום).
                              עם הערך data-camp-days="text": משפט מלא ("נשארו 21 ימים", "נשאר יום אחד", "היום האחרון", "הקמפיין הסתיים").
                              כל אלמנט כזה מקבל data-state="upcoming|live|last|ended"
     data-camp-days-label     המילים שליד המספר: "ימים לסיום" / "יום לסיום" / "היום האחרון" / "הקמפיין הסתיים"
     data-camp-end            תאריך הסיום: "17.12". עם הערך data-camp-end="long": "יום חמישי, 17 בדצמבר"
     data-camp-cd="d|h|m"     ספירה לאחור עד סוף יום הסיום (שעון ישראל): ימים, שעות או דקות, בשתי ספרות לשעות ולדקות.
                              בלי שניות, בכוונה: הלקוח לא רוצה ספירה שמלחיצה. מתעדכן כל 30 שניות (בלי לבנות מחדש את הרשימות).
                              סופרת רק כשהקמפיין פתוח (live או last). לפני הפתיחה ואחרי הסיום התאים ריקים, וכל אחד מקבל data-state.
     data-camp-cd-wrap        עטיפה של הספירה לאחור: מוסתרת (hidden) כשאין מה לספור, כלומר לפני הפתיחה ואחרי הסיום
     data-camp-when="live|open|ended|upcoming"   האלמנט מוצג רק במצב הזה (open = live או last). אחרת hidden
     data-camp-donors="8"     רשימת התורמים האחרונים (המספר = כמה להציג, ברירת מחדל 8). ב־<ul>/<ol> נוצרים <li>, אחרת <div>.
                              כל שורה: class="donor" (+ is-monthly, is-anon, is-new לתרומה שנשלחה עכשיו), data-method="card|cash|check|transfer",
                              ובתוכה: span.donor-initial (אות ראשונה, ריק לבעילום שם), span.donor-name ("משפחת כהן" או "בעילום שם"),
                              span.donor-amount ("360 ₪" או "180 ₪ לחודש"), span.donor-ded (רק כשיש הקדשה), time.donor-time ("לפני 3 שעות")
     data-camp-updates        העדכונים מהשטח, מהחדש לישן. ערך אופציונלי = כמה להציג. data-heading="2|3|4" (ברירת מחדל 3).
                              כל עדכון: article.upd (או li.upd ב־<ul>/<ol>) עם time.upd-date, hN.upd-title, p.upd-text
     על <html>: data-camp-state="upcoming|live|last|ended", כדי לעצב לפי מצב הקמפיין.

   הטופס (דוגמה): SalBracha.donate({amount, name, anonymous, dedication, monthly}) מוסיף את התרומה, שומר ומרנדר מחדש.
     dedication: {kind, name} כש־kind אחד מ־SalBracha.DEDICATIONS, או מחרוזת. מחזיר {ok:true, donation, totals} או {ok:false, error}.
   אירועים על document: "salbracha:change" (detail = totals) אחרי כל רינדור, ו־"salbracha:donate" (detail = התרומה) אחרי תרומה.
   שינוי בלשונית אחרת (למשל במסך הניהול) מתעדכן כאן מיד, דרך האירוע storage. */
(() => {
  const KEY = 'salbracha-campaign-v1';
  const MONTHS = 12;
  const MONTHLY_NOTE = 'הוראת קבע נספרת ביעד הקמפיין כ־12 חודשים';
  const DEDICATIONS = ['לעילוי נשמת', 'לרפואה שלמה של', 'להצלחת', 'לזכות'];
  const METHODS = { card: 'אשראי', cash: 'מזומן', check: 'צ׳ק', transfer: 'העברה בנקאית' };
  const MIN = 10, MAX = 100000;
  const d = (kind, name) => ({ kind, name });
  /* תרומה לדוגמה: [שם, סכום, חודשי, הקדשה, אמצעי, זמן (שעון ישראל)]. שם ריק = בעילום שם */
  const SEED = [
    ['משפחת ברגר', 1800, false, d('לעילוי נשמת', 'אבינו ר׳ יעקב ז״ל'), 'card', '2026-11-10T09:12'],
    ['', 360, false, null, 'card', '2026-11-10T13:40'],
    ['משפחת כהן', 180, true, null, 'card', '2026-11-11T08:05'],
    ['יוסף ל.', 720, false, d('להצלחת', 'הילדים'), 'card', '2026-11-11T21:30'],
    ['', 5000, false, null, 'card', '2026-11-12T10:18'],
    ['משפחת דהן', 360, false, null, 'card', '2026-11-13T11:02'],
    ['מאיר ק.', 180, true, d('לרפואה שלמה של', 'אבא'), 'card', '2026-11-15T20:45'],
    ['משפחת אוחיון', 1000, false, null, 'check', '2026-11-16T09:30'],
    ['', 100, true, null, 'card', '2026-11-16T22:10'],
    ['משפחת רוזנברג', 2500, false, d('לעילוי נשמת', 'הורינו היקרים'), 'card', '2026-11-17T12:25'],
    ['שמואל ח.', 250, false, null, 'card', '2026-11-18T08:40'],
    ['משפחת גולדשטיין', 1000, false, null, 'card', '2026-11-18T19:55'],
    ['', 50, true, null, 'card', '2026-11-19T07:15'],
    ['משפחת אזולאי', 360, true, d('לזכות', 'בני המשפחה'), 'card', '2026-11-19T20:30'],
    ['יעקב מ.', 180, false, null, 'card', '2026-11-20T10:05'],
    ['משפחת לוי', 3600, false, d('לעילוי נשמת', 'סבנו ר׳ שלמה ז״ל'), 'card', '2026-11-21T20:40'],
    ['', 360, false, null, 'card', '2026-11-22T09:20'],
    ['משה ר.', 180, false, null, 'card', '2026-11-22T17:45'],
    ['משפחת ביטון', 540, false, null, 'cash', '2026-11-23T11:30'],
    ['משפחת שטרן', 720, false, d('להצלחת', 'הנכד לרגל בר המצווה'), 'card', '2026-11-23T21:05'],
    ['דוד ו.', 100, true, null, 'card', '2026-11-24T08:50'],
    ['', 1800, false, d('לרפואה שלמה של', 'ידידנו ר׳ אהרן'), 'card', '2026-11-24T20:15'],
    ['משפחת פרידמן', 720, false, null, 'card', '2026-11-25T12:40'],
    ['אברהם ש.', 180, false, null, 'card', '2026-11-25T22:30'],
    ['משפחת אברמוביץ', 360, false, d('לעילוי נשמת', 'זקננו ר׳ משה ז״ל'), 'card', '2026-11-26T06:55'],
  ];
  const DEFAULT = {
    campaign: {
      title: 'חורף של סלים מלאים',
      subtitle: 'סל לכל שבת בחורף, וסל חג לחנוכה, ל־400 משפחות בירושלים',
      goal: 250000,
      /* תאריכים בפורמט YYYY-MM-DD, לפי שעון ישראל. הקמפיין נגמר בסוף יום הסיום */
      start: '2026-11-10',
      end: '2026-12-17',
      /* מזומן, צ׳קים והעברות שנרשמו ביחד, בלי פירוט לפי תורם */
      offline: 118120,
      offlineCount: 268,
    },
    donations: SEED.map(([name, amount, monthly, dedication, method, t], i) => ({
      id: 's' + (i + 1), name, anonymous: !name, amount, monthly, dedication, method, ts: t + ':00+02:00',
    })),
    updates: [
      { id: 'u4', date: '2026-11-26', title: 'הערב אורזים לשבת פרשת וישלח', text: 'מ־18:00 במחסן. השבוע 412 סלים, ועדיין חסרות ידיים לאריזת הירקות. מי שמגיע עם הבנים, יש עבודה לכולם.' },
      { id: 'u3', date: '2026-11-23', title: 'השמן והנרות לחנוכה הוזמנו', text: 'סגרנו עם הספק 420 בקבוקי שמן זית ו־420 חבילות נרות. הם ייכנסו לסלים של יום חמישי, ערב חנוכה.' },
      { id: 'u2', date: '2026-11-20', title: '406 סלים יצאו לשבת', text: '34 מתנדבים ארזו עד 21:40, והנהגים סיימו לחלק עד 11:00. תודה למי שבא בגשם.' },
      { id: 'u1', date: '2026-11-10', title: 'קמפיין החורף נפתח', text: 'בראש חודש כסלו יצאנו לדרך: סל לכל משפחה בכל שבת בחורף, וסל חג לחנוכה. כל 180 ₪ הם שבת אחת למשפחה אחת.' },
    ],
    tiers: [
      { amount: 180, title: 'סל לשבת', text: 'שבת אחת למשפחה אחת: חלות, מיץ ענבים, עוף, ירקות ופירות.' },
      { amount: 360, title: 'סל לחג', text: 'סל חג מלא, ובחנוכה גם שמן זית ונרות.' },
      { amount: 720, title: 'חודש של סלים', text: 'ארבע שבתות למשפחה אחת.' },
      { amount: 1800, title: 'שבת לעשר משפחות', text: 'עשרה סלים לשבת אחת.' },
    ],
  };

  const clone = (o) => JSON.parse(JSON.stringify(o));
  const NF = new Intl.NumberFormat('he-IL', { maximumFractionDigits: 0 });
  const fmt = (n) => `${NF.format(Math.round(Number(n) || 0))} ₪`;
  const num = (n) => NF.format(Math.round(Number(n) || 0));
  const isDate = (s) => /^\d{4}-\d{2}-\d{2}$/.test(String(s || ''));
  let mem = null;          /* כשאין גישה ל־localStorage (גלישה פרטית): שומרים בזיכרון של הדף */
  let lastId = '';         /* התרומה שנשלחה עכשיו, כדי לסמן אותה ברשימה */

  const valid = (x) => x && typeof x === 'object' && x.campaign && Array.isArray(x.donations);
  function load() {
    let x = null;
    try { x = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) { x = null; }
    if (!valid(x)) x = mem && valid(mem) ? mem : null;
    if (!x) return clone(DEFAULT);
    /* השלמה של חלקים חסרים משמירה ישנה */
    const out = clone(x);
    out.campaign = Object.assign(clone(DEFAULT.campaign), out.campaign || {});
    if (!Array.isArray(out.updates)) out.updates = clone(DEFAULT.updates);
    if (!Array.isArray(out.tiers) || !out.tiers.length) out.tiers = clone(DEFAULT.tiers);
    return out;
  }
  function save(data) {
    const x = Object.assign({}, data, { updated: Date.now() });
    mem = clone(x);
    let ok = true;
    try { localStorage.setItem(KEY, JSON.stringify(x)); } catch (e) { ok = false; }
    render();
    return ok;
  }
  function reset() { mem = null; lastId = ''; try { localStorage.removeItem(KEY); } catch (e) { /* */ } render(); }

  /* ---------- זמן לפי שעון ישראל ---------- */
  const parts = (date) => Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jerusalem', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23' }).formatToParts(date).map((p) => [p.type, p.value]));
  const today = () => { const p = parts(new Date()); return `${p.year}-${p.month}-${p.day}`; };
  const dayNum = (iso) => { const [y, m, dd] = iso.split('-').map(Number); return Date.UTC(y, m - 1, dd) / 864e5; };
  /* הרגע שבו נגמר יום מסוים בישראל (חצות שאחריו) */
  function endOfDay(iso) {
    const [y, m, dd] = iso.split('-').map(Number);
    const guess = Date.UTC(y, m - 1, dd + 1, 0, 0, 0);
    const p = parts(new Date(guess));
    const asUTC = Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second);
    return guess - (asUTC - guess);
  }
  /* מצב הקמפיין: לפני, פעיל, יום אחרון, הסתיים */
  function timing(c) {
    const t = today(), s = isDate(c.start) ? c.start : t, e = isDate(c.end) ? c.end : t;
    if (t < s) return { state: 'upcoming', days: dayNum(s) - dayNum(t) };
    if (t > e) return { state: 'ended', days: 0 };
    const days = dayNum(e) - dayNum(t);
    return { state: days === 0 ? 'last' : 'live', days };
  }
  const goalValue = (x) => (x.monthly ? x.amount * MONTHS : x.amount);

  function totals(data) {
    const x = data || load(), c = x.campaign;
    const list = x.donations.filter((v) => v && Number(v.amount) > 0);
    const raised = (Number(c.offline) || 0) + list.reduce((s, v) => s + goalValue(v), 0);
    const goal = Math.max(1, Number(c.goal) || 1);
    const monthly = list.filter((v) => v.monthly);
    const tm = timing(c);
    return {
      goal, raised, remaining: Math.max(0, goal - raised),
      percent: Math.floor((raised / goal) * 100), pct: Math.min(100, (raised / goal) * 100),
      count: list.length + (Number(c.offlineCount) || 0),
      monthlyCount: monthly.length, monthlyPerMonth: monthly.reduce((s, v) => s + v.amount, 0),
      online: list.filter((v) => (v.method || 'card') === 'card').reduce((s, v) => s + goalValue(v), 0),
      byHand: (Number(c.offline) || 0) + list.filter((v) => (v.method || 'card') !== 'card').reduce((s, v) => s + goalValue(v), 0),
      state: tm.state, days: tm.days, start: c.start, end: c.end, endsAt: isDate(c.end) ? endOfDay(c.end) : null,
    };
  }

  /* ---------- תרומה לדוגמה ---------- */
  function cleanDed(ded) {
    if (!ded) return null;
    if (typeof ded === 'string') { const n = ded.trim().slice(0, 80); return n ? { kind: '', name: n } : null; }
    const name = String(ded.name || '').trim().slice(0, 60);
    if (!name) return null;
    const kind = DEDICATIONS.includes(ded.kind) ? ded.kind : '';
    return { kind, name };
  }
  function validate(input) {
    const a = Math.round(Number(String((input && input.amount) ?? '').replace(/[^\d.]/g, '')));
    if (!a) return 'נא לבחור סכום, או לכתוב סכום אחר.';
    if (a < MIN) return `הסכום המינימלי הוא ${fmt(MIN)}.`;
    if (a > MAX) return `לתרומה של יותר מ־${fmt(MAX)} נשמח שתתקשרו אלינו.`;
    return '';
  }
  function donate(input = {}) {
    const error = validate(input);
    if (error) return { ok: false, error };
    const name = String(input.name || '').trim().slice(0, 60);
    const anonymous = !!input.anonymous || !name;
    const method = METHODS[input.method] ? input.method : 'card';
    const ts = input.ts && !isNaN(Date.parse(input.ts)) ? new Date(input.ts).toISOString() : new Date().toISOString();
    const donation = {
      id: 'd' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6),
      name: anonymous ? '' : name, anonymous, amount: Math.round(Number(String(input.amount).replace(/[^\d.]/g, ''))),
      monthly: !!input.monthly, dedication: cleanDed(input.dedication), method, ts,
    };
    const data = load();
    data.donations.push(donation);
    lastId = donation.id;
    save(data);
    const t = totals();
    document.dispatchEvent(new CustomEvent('salbracha:donate', { detail: donation }));
    return { ok: true, donation, totals: t };
  }
  /* עזרים קטנים למסך הניהול */
  function removeDonation(id) { const x = load(); x.donations = x.donations.filter((v) => v.id !== id); return save(x); }
  function addUpdate(u = {}) {
    const x = load();
    const title = String(u.title || '').trim(), text = String(u.text || '').trim();
    if (!title && !text) return false;
    x.updates.unshift({ id: 'u' + Date.now().toString(36), date: isDate(u.date) ? u.date : today(), title, text });
    return save(x);
  }
  function removeUpdate(id) { const x = load(); x.updates = x.updates.filter((v) => v.id !== id); return save(x); }
  function setCampaign(patch = {}) { const x = load(); Object.assign(x.campaign, patch); return save(x); }

  /* ---------- טקסטים ---------- */
  const dm = (iso) => { const [, m, dd] = String(iso).split('-'); return `${Number(dd)}.${Number(m)}`; };
  const longDate = (iso) => { const [y, m, dd] = iso.split('-').map(Number); return new Intl.DateTimeFormat('he-IL', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' }).format(new Date(Date.UTC(y, m - 1, dd))); };
  const dedText = (v) => { const x = cleanDed(v); return x ? (x.kind ? `${x.kind} ${x.name}` : x.name) : ''; };
  const donorName = (v) => (v.anonymous || !v.name ? 'בעילום שם' : v.name);
  const initial = (v) => { if (v.anonymous || !v.name) return ''; const w = v.name.replace(/^משפחת\s+/, '').trim(); return w.charAt(0); };
  const amountText = (v) => (v.monthly ? `${fmt(v.amount)} לחודש` : fmt(v.amount));
  function ago(ts) {
    const t = Date.parse(ts); if (isNaN(t)) return '';
    const min = Math.max(0, Math.round((Date.now() - t) / 60000));
    if (min < 1) return 'עכשיו';
    if (min < 60) return min === 1 ? 'לפני דקה' : `לפני ${min} דקות`;
    const h = Math.floor(min / 60);
    const p = parts(new Date(t)), iso = `${p.year}-${p.month}-${p.day}`, dd = dayNum(today()) - dayNum(iso);
    if (dd === 0 || h < 6) return h === 1 ? 'לפני שעה' : h === 2 ? 'לפני שעתיים' : `לפני ${h} שעות`;
    if (dd === 1) return 'אתמול';
    if (dd === 2) return 'לפני יומיים';
    if (dd < 7) return `לפני ${dd} ימים`;
    return dm(iso);
  }
  const daysTexts = (t) => {
    if (t.state === 'ended') return { num: 'הסתיים', label: 'הקמפיין הסתיים', text: 'הקמפיין הסתיים' };
    if (t.state === 'upcoming') return { num: String(t.days), label: t.days === 1 ? 'יום לפתיחה' : 'ימים לפתיחה', text: t.days === 1 ? 'נפתח מחר' : `נפתח בעוד ${t.days} ימים` };
    if (t.state === 'last') return { num: '', label: 'היום האחרון', text: 'היום האחרון' };
    return { num: String(t.days), label: t.days === 1 ? 'יום לסיום' : 'ימים לסיום', text: t.days === 1 ? 'נשאר יום אחד' : `נשארו ${t.days} ימים` };
  };
  /* מסתיר או מציג, ומחזיר להצגה רק אלמנט שהמנוע עצמו הסתיר */
  const setHidden = (el, hide) => {
    if (hide) { if (!el.hidden) { el.hidden = true; el.setAttribute('data-camp-hid', ''); } }
    else if (el.hasAttribute('data-camp-hid')) { el.hidden = false; el.removeAttribute('data-camp-hid'); }
  };
  const make = (tag, cls, text) => { const el = document.createElement(tag); if (cls) el.className = cls; if (text != null) el.textContent = text; return el; };
  const each = (sel, fn) => document.querySelectorAll(sel).forEach(fn);

  /* החלקים שתלויים בשעה: ימים, ספירה לאחור, מצב, "לפני 3 שעות". רץ גם כל 30 שניות, בלי לבנות מחדש את הרשימות */
  function tick(data, tt) {
    const x = data || load(), c = x.campaign, t = tt || totals(x), dt = daysTexts(t);
    document.documentElement.setAttribute('data-camp-state', t.state);
    each('[data-camp-days]', (el) => {
      const isText = el.getAttribute('data-camp-days') === 'text';
      el.textContent = isText ? dt.text : dt.num;
      el.setAttribute('data-state', t.state);
      /* ביום האחרון המספר נעלם, וה־label מספר שזה היום האחרון */
      if (!isText) setHidden(el, t.state === 'last');
    });
    each('[data-camp-days-label]', (el) => { el.textContent = dt.label; });
    each('[data-camp-end]', (el) => { el.textContent = isDate(c.end) ? (el.getAttribute('data-camp-end') === 'long' ? longDate(c.end) : dm(c.end)) : ''; });
    const counting = !!t.endsAt && (t.state === 'live' || t.state === 'last');
    const left = counting ? Math.max(0, t.endsAt - Date.now()) : 0;
    const cd = { d: Math.floor(left / 864e5), h: Math.floor(left / 36e5) % 24, m: Math.floor(left / 6e4) % 60 };
    each('[data-camp-cd]', (el) => {
      const k = el.getAttribute('data-camp-cd');
      el.setAttribute('data-state', t.state);
      if (k in cd) el.textContent = !counting ? '' : k === 'd' ? String(cd.d) : String(cd[k]).padStart(2, '0');
    });
    each('[data-camp-cd-wrap]', (el) => setHidden(el, !counting));
    each('[data-camp-when]', (el) => {
      const w = el.getAttribute('data-camp-when');
      el.hidden = !(w === t.state || (w === 'open' && (t.state === 'live' || t.state === 'last')));
    });
    each('.donor-time[datetime], [data-camp-donors] time[datetime]', (el) => { el.textContent = ago(el.getAttribute('datetime')); });
  }

  /* ---------- רינדור ---------- */
  function render() {
    const data = load(), c = data.campaign, t = totals(data);
    each('[data-camp-title]', (el) => { el.textContent = c.title || ''; });
    each('[data-camp-subtitle]', (el) => { el.textContent = c.subtitle || ''; el.hidden = !c.subtitle; });
    each('[data-camp-raised]', (el) => { el.textContent = fmt(t.raised); });
    each('[data-camp-goal]', (el) => { el.textContent = fmt(t.goal); });
    each('[data-camp-remaining]', (el) => { el.textContent = fmt(t.remaining); });
    each('[data-camp-percent]', (el) => { el.textContent = `${t.percent}%`; });
    each('[data-camp-count]', (el) => { el.textContent = num(t.count); });
    each('[data-camp-monthly-count]', (el) => { el.textContent = num(t.monthlyCount); });
    each('[data-camp-monthly-note]', (el) => { el.textContent = MONTHLY_NOTE; });
    const vt = `נאספו ${fmt(t.raised)} מתוך ${fmt(t.goal)} (${t.percent}%)`;
    each('[data-camp-progress]', (el) => {
      el.setAttribute('role', 'progressbar');
      el.setAttribute('aria-valuemin', '0'); el.setAttribute('aria-valuemax', '100');
      el.setAttribute('aria-valuenow', String(Math.min(100, t.percent)));
      el.setAttribute('aria-valuetext', vt);
      if (!el.hasAttribute('aria-label') && !el.hasAttribute('aria-labelledby')) el.setAttribute('aria-label', 'התקדמות הקמפיין');
      el.style.setProperty('--p', t.pct.toFixed(2)); el.style.setProperty('--p-raw', String(t.percent)); el.style.setProperty('--pct', `${t.pct.toFixed(2)}%`);
    });
    each('[data-camp-fill]', (el) => { el.style.width = `${t.pct.toFixed(2)}%`; el.style.setProperty('--p', t.pct.toFixed(2)); });
    tick(data, t);
    /* התורמים: מהחדש לישן */
    const sorted = data.donations.filter((v) => v && v.amount > 0).slice().sort((a, b) => Date.parse(b.ts) - Date.parse(a.ts));
    each('[data-camp-donors]', (el) => {
      const n = parseInt(el.getAttribute('data-camp-donors'), 10) || 8;
      const tag = el.tagName === 'UL' || el.tagName === 'OL' ? 'li' : 'div';
      const frag = document.createDocumentFragment();
      sorted.slice(0, n).forEach((v) => {
        const row = make(tag, 'donor');
        if (v.monthly) row.classList.add('is-monthly');
        if (v.anonymous || !v.name) row.classList.add('is-anon');
        if (v.id === lastId) row.classList.add('is-new');
        row.setAttribute('data-method', v.method || 'card');
        const ini = make('span', 'donor-initial', initial(v)); ini.setAttribute('aria-hidden', 'true'); row.append(ini);
        row.append(make('span', 'donor-name', donorName(v)), make('span', 'donor-amount', amountText(v)));
        const ded = dedText(v.dedication); if (ded) row.append(make('span', 'donor-ded', ded));
        const tm = make('time', 'donor-time', ago(v.ts)); tm.setAttribute('datetime', v.ts); row.append(tm);
        frag.append(row);
      });
      el.replaceChildren(frag);
    });
    /* עדכונים מהשטח */
    const ups = data.updates.slice().sort((a, b) => String(b.date).localeCompare(String(a.date)));
    each('[data-camp-updates]', (el) => {
      const n = parseInt(el.getAttribute('data-camp-updates'), 10) || ups.length;
      const hl = Math.min(6, Math.max(2, parseInt(el.getAttribute('data-heading'), 10) || 3));
      const tag = el.tagName === 'UL' || el.tagName === 'OL' ? 'li' : 'article';
      const frag = document.createDocumentFragment();
      ups.slice(0, n).forEach((u) => {
        const item = make(tag, 'upd');
        const tm = make('time', 'upd-date', isDate(u.date) ? dm(u.date) : ''); if (isDate(u.date)) tm.setAttribute('datetime', u.date);
        item.append(tm);
        if (u.title) item.append(make('h' + hl, 'upd-title', u.title));
        if (u.text) item.append(make('p', 'upd-text', u.text));
        frag.append(item);
      });
      el.replaceChildren(frag);
    });
    document.dispatchEvent(new CustomEvent('salbracha:change', { detail: t }));
  }

  window.SalBracha = {
    KEY, DEFAULT, MONTHS, MONTHLY_NOTE, DEDICATIONS, METHODS, MIN, MAX,
    load, save, reset, donate, validate, totals, render, tick,
    removeDonation, addUpdate, removeUpdate, setCampaign,
    fmt, ago, dedText, goalValue,
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render); else render();
  setInterval(() => tick(), 30000);
  /* שינוי במסך הניהול בלשונית אחרת מתעדכן כאן מיד */
  window.addEventListener('storage', (e) => { if (e.key === KEY || e.key === null) render(); });
})();
