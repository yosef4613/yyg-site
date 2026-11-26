/* נקודה · החזרי מס (עסק לדוגמה): סקריפט קטן ומשותף ל־3 העיצובים ולגרסה הסופית. בלי אזור ניהול ובלי שמירה.
   מה הוא עושה:
   1. שנות המס שאפשר לבקש עליהן החזר (6 השנים האחרונות), לפי שעון ישראל. מתעדכן לבד כל שנה:
        data-tax-years     "2020 עד 2025"
        data-tax-from      "2020"   השנה הראשונה שעוד אפשר לבדוק
        data-tax-to        "2025"   השנה האחרונה שהסתיימה
        data-tax-closing   "2020"   השנה שנסגרת בסוף השנה הנוכחית (זהה ל־from)
        data-tax-deadline  "31.12.2026"
        data-tax-days      "36"     כמה ימים נשארו עד 31.12 (כולל היום)
        data-tax-days-text "בעוד 36 ימים" / "מחרתיים" / "מחר" / "היום"   (בא אחרי "נסגרת")
        data-tax-list      <ul> או <ol> ריקים: נוצרות 6 שורות <li>, אחת לכל שנה, מהישנה לחדשה (data-tax-list="desc": מהחדשה לישנה).
                           בכל שורה <span class="tax-y">2020</span><span class="tax-s">נסגרת בעוד 36 ימים</span>.
                           השנה שנסגרת מקבלת class="is-closing", והשאר "פתוחה לבדיקה"
   2. זמינות בטלפון לפי שעות הפעילות (א׳ עד ה׳ 9:00 עד 20:00, ו׳ 9:00 עד 12:00, שבת סגור):
        data-avail         "זמין עכשיו בטלפון ובוואטסאפ" או "אחזור אליכם מחר מ־9:00". מקבל data-open="true|false"
   3. טופס בדיקת הזכאות: form[data-check-form]. בודק שם וטלפון, מסמן שגיאות ליד השדה,
        ובשליחה תקינה כותב ב־[data-form-status] (role="status"): "זה אתר לדוגמה, הטופס לא נשלח." ושום דבר לא נשלח.
        [data-form-done] (לא חובה): מוסתר עם hidden, ונחשף אחרי שליחה תקינה. הטופס מקבל class="is-sent".
        פאנל ריק מתמלא בהודעה ומתרענן בכל שליחה תקינה (למשל כשמשנים את השעה). בפאנל שהעיצוב מילא, [data-when-out] מקבל את השעה שנבחרה.
        שליחה לא תקינה מורידה את is-sent ומסתירה שוב את [data-form-done], כדי שהצלחה ושגיאות לא יופיעו יחד.
        טלפון: 0 ועוד 8 או 9 ספרות, וגם ‎+972 או 00972.
        הודעת שגיאה לשדה: אלמנט [data-error-for="name|phone"], ואם אין, הסקריפט יוצר <p class="field-error">.
   4. הערכה לדוגמה (לא חובה): [data-estimate]. קורא את התיבות name="reasons" ואת select[name="kids"] בכל הדף, וכותב:
        data-estimate-range  "בין 1,000 ל־9,500 ₪", או טקסט ריק־מצב
        data-estimate-low / data-estimate-high   "1,000" / "9,500"
        data-estimate-net    "אחרי 15% שכר טרחה נשארים לכם בערך 850 עד 8,050 ₪"
        data-estimate-count  כמה סיבות סומנו ("2")
        data-estimate-note   משפט הסבר לפי המצב
        ועל [data-estimate]: data-state="empty|unsure|range" ו־style="--est: 0..1" (לפס התקדמות, יחסית לתקרה של 15,000 ₪).
        ערכי התיבות: job, donate, kids, part, deposit, unsure. ערכי ה־select: 0, 1-2, 3-4, 5-6, 7+.
        "kids" עם 0 ילדים לא נחשב סיבה (אם אין סיבה אחרת, המצב הוא unsure).
        הטווחים מומצאים לדוגמה בלבד. בעיצוב חייבת להופיע ליד ההערכה השורה: "הערכה לדוגמה בלבד, לא ייעוץ מס."
   הכול נכתב כטקסט בלבד (textContent). */
(() => {
  'use strict';
  const NIS = (n) => Math.round(n).toLocaleString('en-US');
  const r50 = (n) => Math.round(n / 50) * 50;
  const DAYS = ['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת'];
  /* היום, השעה והתאריך עכשיו, לפי שעון ישראל */
  const now = () => {
    const p = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jerusalem', weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date()).map((x) => [x.type, x.value]));
    return { y: Number(p.year), m: Number(p.month), d: Number(p.day), day: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(p.weekday), min: Number(p.hour) * 60 + Number(p.minute) };
  };
  const setAll = (sel, text) => document.querySelectorAll(sel).forEach((el) => { el.textContent = text; });

  /* ---------- 1. שנות המס ---------- */
  function taxYears(t = now()) {
    const from = t.y - 6, to = t.y - 1;
    /* ימים עד 31.12 כולל היום: לפי תאריכים בלבד, בלי שעות */
    const days = Math.round((Date.UTC(t.y, 11, 31) - Date.UTC(t.y, t.m - 1, t.d)) / 864e5) + 1;
    const daysText = days <= 1 ? 'היום' : days === 2 ? 'מחר' : days === 3 ? 'מחרתיים' : `בעוד ${days} ימים`;
    return { from, to, closing: from, deadline: `31.12.${t.y}`, days, daysText, years: `${from} עד ${to}` };
  }
  function renderYears() {
    const y = taxYears();
    setAll('[data-tax-years]', y.years);
    setAll('[data-tax-from]', String(y.from));
    setAll('[data-tax-to]', String(y.to));
    setAll('[data-tax-closing]', String(y.closing));
    setAll('[data-tax-deadline]', y.deadline);
    setAll('[data-tax-days]', String(y.days));
    setAll('[data-tax-days-text]', y.daysText);
    document.querySelectorAll('[data-tax-list]').forEach((list) => {
      const years = []; for (let v = y.from; v <= y.to; v++) years.push(v);
      if (list.dataset.taxList === 'desc') years.reverse();
      list.replaceChildren(...years.map((v) => {
        const li = document.createElement('li'), a = document.createElement('span'), b = document.createElement('span');
        a.className = 'tax-y'; a.textContent = String(v);
        b.className = 'tax-s'; b.textContent = v === y.closing ? `נסגרת ${y.daysText}` : 'פתוחה לבדיקה';
        if (v === y.closing) li.className = 'is-closing';
        li.append(a, ' ', b); return li;
      }));
    });
  }

  /* ---------- 2. זמינות בטלפון ---------- */
  const HOURS = [[540, 1200], [540, 1200], [540, 1200], [540, 1200], [540, 1200], [540, 720], null];
  const hm = (m) => `${Math.floor(m / 60)}:${String(m % 60).padStart(2, '0')}`;
  function availability(t = now()) {
    const h = HOURS[t.day];
    if (h && t.min >= h[0] && t.min < h[1]) return { open: true, text: 'זמין עכשיו בטלפון ובוואטסאפ', until: hm(h[1]) };
    if (h && t.min < h[0]) return { open: false, text: `אחזור אליכם היום מ־${hm(h[0])}` };
    for (let i = 1; i <= 7; i++) {
      const d = (t.day + i) % 7;
      if (HOURS[d]) return { open: false, text: i === 1 ? `אחזור אליכם מחר מ־${hm(HOURS[d][0])}` : `אחזור אליכם ביום ${DAYS[d]} מ־${hm(HOURS[d][0])}` };
    }
    return { open: false, text: '' };
  }
  function renderAvail() {
    const a = availability();
    document.querySelectorAll('[data-avail]').forEach((el) => { el.textContent = a.text; el.dataset.open = String(a.open); });
  }

  /* ---------- 3. הערכה לדוגמה ---------- */
  /* טווחים מומצאים, לכל 6 השנים יחד, בשקלים. לדוגמה בלבד */
  const RANGES = { job: [1000, 5000], donate: [800, 4500], part: [800, 3500], deposit: [400, 2000] };
  const KIDS = { '1-2': [500, 2000], '3-4': [1000, 3500], '5-6': [1500, 5000], '7+': [2000, 6000], '': [1000, 3500] };
  const MAX = 15000;
  function estimate(reasons, kids = '') {
    const set = new Set(reasons);
    let lo = 0, hi = 0, n = 0;
    for (const k of Object.keys(RANGES)) if (set.has(k)) { lo += RANGES[k][0]; hi += RANGES[k][1]; n++; }
    /* "יש ילדים" עם "אין ילדים" בבחירה: לא נחשב סיבה. סותר, אז בודקים יחד */
    const noKids = set.has('kids') && kids === '0';
    if (set.has('kids') && !noKids) { const r = KIDS[kids] || KIDS['']; lo += r[0]; hi += r[1]; n++; }
    if (!n) return { state: set.has('unsure') || noKids ? 'unsure' : 'empty', count: 0, lo: 0, hi: 0 };
    /* חפיפה בין הסיבות: הקצה התחתון קצת נמוך יותר, והעליון מוגבל */
    lo = Math.floor((lo * 0.8) / 500) * 500;
    hi = Math.min(MAX, Math.ceil(hi / 500) * 500);
    return { state: 'range', count: n, lo, hi };
  }
  function renderEstimate() {
    const boxes = document.querySelectorAll('[data-estimate]');
    if (!boxes.length) return;
    const reasons = [...document.querySelectorAll('input[name="reasons"]:checked')].map((i) => i.value);
    const kidsEl = document.querySelector('select[name="kids"]');
    const e = estimate(reasons, kidsEl ? kidsEl.value : '');
    const note = e.state === 'range'
      ? (e.count === 1 ? 'סימנתם סיבה אחת נפוצה להחזר.' : `סימנתם ${e.count} סיבות נפוצות להחזר.`)
      : e.state === 'unsure' ? 'לא בטוחים? זה בסדר גמור. בשיחה קצרה נבדוק יחד.' : 'סמנו בטופס מה נכון לגביכם, ותראו כאן טווח לדוגמה.';
    boxes.forEach((box) => {
      box.dataset.state = e.state;
      box.style.setProperty('--est', e.state === 'range' ? String(Math.min(1, e.hi / MAX)) : '0');
      box.querySelectorAll('[data-estimate-range]').forEach((el) => { el.textContent = e.state === 'range' ? `בין ${NIS(e.lo)} ל־${NIS(e.hi)} ₪` : (e.state === 'unsure' ? 'נבדוק יחד' : 'עוד לא סימנתם'); });
      box.querySelectorAll('[data-estimate-low]').forEach((el) => { el.textContent = e.state === 'range' ? NIS(e.lo) : '0'; });
      box.querySelectorAll('[data-estimate-high]').forEach((el) => { el.textContent = e.state === 'range' ? NIS(e.hi) : '0'; });
      box.querySelectorAll('[data-estimate-net]').forEach((el) => { el.textContent = e.state === 'range' ? `אחרי 15% שכר טרחה נשארים לכם בערך ${NIS(r50(e.lo * 0.85))} עד ${NIS(r50(e.hi * 0.85))} ₪` : ''; el.hidden = e.state !== 'range'; });
      box.querySelectorAll('[data-estimate-count]').forEach((el) => { el.textContent = String(e.count); });
      box.querySelectorAll('[data-estimate-note]').forEach((el) => { el.textContent = note; });
    });
  }

  /* ---------- 4. הטופס ---------- */
  const digits = (v) => String(v || '').replace(/[^\d]/g, '');
  /* מספר ישראלי: 0 ועוד 8 או 9 ספרות. מקבל גם ‎+972 או 00972, עם או בלי ה־0 שאחרי הקידומת */
  const localPhone = (v) => {
    let d = digits(v);
    if (d.startsWith('00')) d = d.slice(2);
    if (d.startsWith('972')) d = '0' + d.slice(3).replace(/^0/, '');
    return d;
  };
  const CHECKS = {
    name: (v) => (String(v).trim().length >= 2 ? '' : 'נא לכתוב שם, כדי שנדע איך לפנות אליכם.'),
    phone: (v) => (/^0\d{8,9}$/.test(localPhone(v)) ? '' : 'נא לכתוב מספר טלפון תקין: 9 או 10 ספרות, או עם ‎+972.'),
  };
  function errorEl(form, input) {
    let el = form.querySelector(`[data-error-for="${input.name}"]`);
    if (!el) {
      el = document.createElement('p'); el.className = 'field-error'; el.dataset.errorFor = input.name;
      input.insertAdjacentElement('afterend', el);
    }
    if (!el.id) el.id = `err-${input.name}-${Math.random().toString(36).slice(2, 7)}`;
    const ids = (input.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
    if (!ids.includes(el.id)) input.setAttribute('aria-describedby', ids.concat(el.id).join(' '));
    return el;
  }
  function check(form, input) {
    const msg = CHECKS[input.name] ? CHECKS[input.name](input.value) : '';
    const el = errorEl(form, input);
    el.textContent = msg; el.hidden = !msg;
    if (msg) input.setAttribute('aria-invalid', 'true'); else input.removeAttribute('aria-invalid');
    /* שגיאה אחרי שליחה תקינה (למשל מחקו את השם): הודעת ההצלחה יורדת */
    if (msg && form.classList.contains('is-sent')) {
      form.classList.remove('is-sent');
      const done = form.querySelector('[data-form-done]') || document.querySelector('[data-form-done]');
      if (done) done.hidden = true;
    }
    return !msg;
  }
  function wireForm(form) {
    form.setAttribute('novalidate', '');
    const fields = () => [...form.querySelectorAll('input[name="name"], input[name="phone"]')];
    fields().forEach((i) => {
      i.addEventListener('blur', () => { if (i.value) check(form, i); });
      i.addEventListener('input', () => { if (i.getAttribute('aria-invalid') === 'true') check(form, i); });
    });
    form.addEventListener('submit', (ev) => {
      ev.preventDefault();
      const status = form.querySelector('[data-form-status]');
      const bad = fields().filter((i) => !check(form, i));
      const done = form.querySelector('[data-form-done]') || document.querySelector('[data-form-done]');
      if (bad.length) {
        /* לא מציגים יחד הודעת הצלחה ושגיאות */
        form.classList.remove('is-sent');
        if (done) done.hidden = true;
        bad[0].focus();
        if (status) { status.textContent = ''; setTimeout(() => { status.textContent = 'כמעט: חסר פרט אחד או יותר. סימנו לכם איפה.'; }, 30); }
        return;
      }
      const when = form.querySelector('input[name="when"]:checked');
      const msg = 'זה אתר לדוגמה, הטופס לא נשלח.';
      const more = `באתר אמיתי, אברהם היה חוזר אליכם תוך יום עבודה${when ? `, ${when.value}` : ''}.`;
      form.classList.add('is-sent');
      if (status) { status.textContent = ''; setTimeout(() => { status.textContent = `${msg} ${more}`; }, 30); }
      if (done) {
        /* פאנל ריק: הסקריפט כותב בו, ומרענן בכל שליחה. פאנל שהעיצוב מילא: רק [data-when-out] מתעדכן */
        if (!done.textContent.trim() || done.dataset.auto === 'true') { done.textContent = `${msg} ${more}`; done.dataset.auto = 'true'; }
        if (when) done.querySelectorAll('[data-when-out]').forEach((el) => { el.textContent = when.value; });
        done.hidden = false;
        if (!done.hasAttribute('tabindex')) done.setAttribute('tabindex', '-1');
        done.focus({ preventScroll: false });
      } else if (!status) {
        window.alert(msg);
      }
    });
  }

  function init() {
    renderYears(); renderAvail(); renderEstimate();
    document.querySelectorAll('form[data-check-form]').forEach(wireForm);
    document.addEventListener('change', (e) => { if (e.target.matches('input[name="reasons"], select[name="kids"]')) renderEstimate(); });
    /* פעם בדקה: זמינות ושנים (למי שמשאיר את הדף פתוח בחצות) */
    setInterval(() => { renderYears(); renderAvail(); }, 60000);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
  /* לבדיקות ולעיצובים שרוצים לקרוא את החישוב בעצמם */
  window.Nekuda = { taxYears, availability, estimate, refresh: () => { renderYears(); renderAvail(); renderEstimate(); } };
})();
