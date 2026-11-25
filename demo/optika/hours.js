/* צלול (עסק לדוגמה): שעות פתיחה, סטטוס "פתוח עכשיו" והודעה זמנית.
   השעות הקבועות כתובות כאן. מה שבעל העסק משנה במסך הניהול לדוגמה (/demo/optika/admin/) נשמר רק בדפדפן שלו,
   כדי שכל אחד יוכל לנסות. באתר אמיתי השינוי נשמר בשרת ומופיע מיד לכל הגולשים.
   שימוש בעיצוב: מוסיפים אלמנטים עם התכונות האלה, והסקריפט ממלא אותם:
     data-hours-status  שורת סטטוס ("פתוח עכשיו · נסגר ב־20:00"). מקבל data-open="true|false"
     data-hours-list    רשימת הימים: ב־<ul>/<ol> נוצרים <li>, ב־<tbody>/<table> שורות, ב־<dl> זוגות dt/dd.
                        היום הנוכחי מקבל class="is-today" ו־aria-current="date"
     data-hours-today   "היום: 10:00 עד 20:00"
     data-hours-notice  ההודעה הזמנית. מוסתר (hidden) כשאין הודעה */
(() => {
  const KEY = 'tzalul-hours-v1';
  const DAYS = ['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת'];
  const DEFAULT = {
    days: [
      { open: '10:00', close: '20:00' },
      { open: '10:00', close: '20:00' },
      { open: '10:00', close: '20:00' },
      { open: '10:00', close: '20:00' },
      { open: '10:00', close: '20:00' },
      { open: '09:00', close: '13:00' },
      { open: '20:00', close: '22:30', label: 'מוצאי שבת' },
    ],
    notice: { text: '', until: '' },
  };
  const clone = (o) => JSON.parse(JSON.stringify(o));
  const load = () => {
    try { const d = JSON.parse(localStorage.getItem(KEY) || 'null'); if (d && Array.isArray(d.days) && d.days.length === 7) return d; } catch (e) { /* בלי שמירה */ }
    return clone(DEFAULT);
  };
  const save = (d) => { try { localStorage.setItem(KEY, JSON.stringify(Object.assign({}, d, { updated: Date.now() }))); } catch (e) { return false; } render(); return true; };
  const reset = () => { try { localStorage.removeItem(KEY); } catch (e) { /* */ } render(); };
  const mins = (t) => { const [h, m] = String(t || '').split(':').map(Number); return h * 60 + (m || 0); };
  const range = (d) => `${d.open} עד ${d.close}`;
  /* היום והשעה עכשיו, לפי שעון ישראל */
  const now = () => {
    const p = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jerusalem', weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date()).map((x) => [x.type, x.value]));
    return { day: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(p.weekday), min: Number(p.hour) * 60 + Number(p.minute), date: `${p.year}-${p.month}-${p.day}` };
  };
  const dayText = (d, i) => {
    if (d.closed) return 'סגור';
    if (i === 6 && d.label) return `${d.label}, ${range(d)}`;
    return range(d);
  };
  const status = () => {
    const data = load(), n = now(), today = data.days[n.day];
    if (today && !today.closed && n.min >= mins(today.open) && n.min < mins(today.close)) {
      const left = mins(today.close) - n.min;
      return { open: true, text: left <= 60 ? `פתוח עכשיו · נסגר בעוד ${left} דקות` : `פתוח עכשיו · נסגר ב־${today.close}` };
    }
    for (let k = 0; k < 8; k++) {
      const i = (n.day + k) % 7, d = data.days[i];
      if (!d || d.closed) continue;
      if (k === 0 && n.min >= mins(d.open)) continue;
      const when = k === 0 ? (i === 6 && d.label ? `ב${d.label}` : 'היום') : k === 1 ? (i === 6 && d.label ? `ב${d.label}` : 'מחר') : (i === 6 && d.label ? `ב${d.label}` : `ביום ${DAYS[i]}`);
      return { open: false, text: `סגור עכשיו · נפתח ${when} ב־${d.open}` };
    }
    return { open: false, text: 'סגור עכשיו' };
  };
  const notice = () => {
    const nt = load().notice || {};
    if (!nt.text || !String(nt.text).trim()) return '';
    if (nt.until && nt.until < now().date) return '';
    return String(nt.text).trim();
  };
  const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  function render() {
    const data = load(), n = now(), st = status(), nt = notice();
    document.querySelectorAll('[data-hours-status]').forEach((el) => { el.textContent = st.text; el.dataset.open = String(st.open); });
    document.querySelectorAll('[data-hours-today]').forEach((el) => { const d = data.days[n.day]; el.textContent = `היום: ${dayText(d, n.day)}`; });
    document.querySelectorAll('[data-hours-notice]').forEach((el) => { el.textContent = nt; el.hidden = !nt; });
    document.querySelectorAll('[data-hours-list]').forEach((el) => {
      const tag = el.tagName;
      el.innerHTML = data.days.map((d, i) => {
        const cur = i === n.day ? ' class="is-today" aria-current="date"' : '';
        const name = esc(DAYS[i]), t = esc(dayText(d, i));
        if (tag === 'DL') return `<dt${cur}>${name}</dt><dd${cur}>${t}</dd>`;
        if (tag === 'TABLE' || tag === 'TBODY') return `<tr${cur}><th scope="row">${name}</th><td>${t}</td></tr>`;
        return `<li${cur}><span class="h-day">${name}</span><span class="h-time">${t}</span></li>`;
      }).join('');
    });
  }
  window.TzalulHours = { KEY, DAYS, DEFAULT, load, save, reset, status, notice, render };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render); else render();
  setInterval(render, 60000);
  /* שינוי במסך הניהול בלשונית אחרת מתעדכן כאן מיד */
  window.addEventListener('storage', (e) => { if (e.key === KEY) render(); });
})();
