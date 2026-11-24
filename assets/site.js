/* ===== פרטי קשר: להחליף כאן לפני העלייה לאוויר ===== */
const CONFIG = {
  whatsapp: '972500000000',          // מספר בפורמט בינלאומי, בלי + ובלי אפס בהתחלה
  greeting: 'שלום יוסף, ראיתי את האתר שלך ואשמח לדבר על אתר לעסק שלי.'
};

const waLink = (text) => `https://wa.me/${CONFIG.whatsapp}?text=${encodeURIComponent(text)}`;

/* כל כפתורי הוואטסאפ */
document.querySelectorAll('[data-wa]').forEach((a) => {
  if (a.id === 'r-send') return; // כפתור המתכנן מקבל הודעה משלו
  a.href = waLink(CONFIG.greeting);
  a.target = '_blank';
  a.rel = 'noopener';
});

/* קו תחתון לכותרת אחרי גלילה */
const header = document.querySelector('.top');
const onScroll = () => header.classList.toggle('is-stuck', window.scrollY > 8);
onScroll();
window.addEventListener('scroll', onScroll, { passive: true });

/* ===== החלפת כיווני העיצוב בפתיחה ===== */
(() => {
  const img = document.getElementById('proof-img');
  const url = document.getElementById('proof-url');
  const note = document.getElementById('proof-note');
  const buttons = [...document.querySelectorAll('.switch button')];
  if (!img || !buttons.length) return;

  const srcset = (s) => `assets/img/${s}-720.webp 720w, assets/img/${s}-1200.webp 1200w`;
  const warmed = new Set(['hk-noir']);
  const warm = (s) => {
    if (warmed.has(s)) return;
    warmed.add(s);
    const pre = new Image();
    pre.sizes = img.sizes;
    pre.srcset = srcset(s);
  };

  const show = (btn) => {
    const s = btn.dataset.shot;
    buttons.forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
    url.textContent = btn.dataset.shot === 'hk-site' ? 'הכיף שבאפיה, האתר שנבנה' : `הכיף שבאפיה, ${btn.dataset.name}`;
    note.textContent = btn.dataset.note;
    img.classList.add('is-swapping');
    const swap = () => {
      img.srcset = srcset(s);
      img.src = `assets/img/${s}-1200.webp`;
      img.alt = btn.dataset.alt;
      const done = () => img.classList.remove('is-swapping');
      if (img.complete) requestAnimationFrame(done);
      else img.addEventListener('load', done, { once: true });
    };
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    reduce ? swap() : setTimeout(swap, 140);
  };

  buttons.forEach((b) => {
    b.addEventListener('click', () => show(b));
    b.addEventListener('pointerenter', () => warm(b.dataset.shot));
    b.addEventListener('focus', () => warm(b.dataset.shot));
  });

  /* חיצים במקלדת בין הכפתורים */
  document.querySelector('.switch').addEventListener('keydown', (e) => {
    const i = buttons.indexOf(document.activeElement);
    if (i < 0) return;
    let n = null;
    if (e.key === 'ArrowLeft') n = (i + 1) % buttons.length;   // RTL: שמאלה = הבא
    if (e.key === 'ArrowRight') n = (i - 1 + buttons.length) % buttons.length;
    if (n === null) return;
    e.preventDefault();
    buttons[n].focus();
    show(buttons[n]);
  });
})();

/* ===== מתכנן האתר ===== */
(() => {
  const form = document.getElementById('planner');
  if (!form) return;
  const $ = (id) => document.getElementById(id);
  const prev = { pages: new Set(), feats: new Set() };

  const KIND = {
    products: { label: 'מוצרים או תפריט', pages: ['קטלוג', 'עמוד לכל מוצר'] },
    org: { label: 'חוגים, מוסד או ארגון', pages: ['חוגים ופעילויות', 'מידע להורים ולמשתתפים'] },
    service: { label: 'שירות מקצועי', pages: ['שירותים', 'לקוחות ועבודות'] }
  };
  const ACT = {
    order: { label: 'להזמין או לבקש הצעת מחיר', feat: 'טופס הזמנה שנשלח לוואטסאפ עם כל הפרטים' },
    register: { label: 'להירשם', feat: 'טופס הרשמה ואישור קבלה במייל', page: 'הרשמה' },
    contact: { label: 'להתקשר או לשלוח וואטסאפ', feat: 'כפתור וואטסאפ וחיוג שתמיד נראה' },
    read: { label: 'לקרוא תוכן שמתעדכן', feat: 'מדור תוכן (מתכונים, שירים או מאמרים)', page: 'מדור תוכן' }
  };
  const UPD = { none: 'אף אחד, הוא קבוע', me: 'אני, מדי פעם', team: 'צוות, עם רשימת תפוצה' };
  const TRACK = {
    landing: { name: 'דף נחיתה', why: 'עמוד אחד ברור, עם כל מה שצריך כדי לפנות אליכם.' },
    site: { name: 'אתר עם אזור ניהול', why: 'כמה עמודים, ומסך פשוט לעדכון מה שמשתנה.' },
    club: { name: 'אתר ומועדון עדכונים', why: 'כל מה שבאתר עם אזור ניהול, ועוד חשבונות ורשימת תפוצה.' }
  };

  const read = () => {
    const fd = new FormData(form);
    return { kind: fd.get('kind'), acts: fd.getAll('act'), upd: fd.get('upd'), en: fd.get('en') === 'on' };
  };

  const plan = ({ kind, acts, upd, en }) => {
    let track = 'site';
    if (upd === 'team') track = 'club';
    else if (upd === 'none' && !acts.includes('register') && !acts.includes('read')) track = 'landing';

    let pages, feats = [];
    if (track === 'landing') {
      pages = ['עמוד אחד: פתיחה, מה אתם מציעים, אודות ויצירת קשר', 'הצהרת נגישות'];
    } else {
      pages = ['דף הבית', ...KIND[kind].pages];
      acts.forEach((a) => ACT[a].page && pages.push(ACT[a].page));
      pages.push('אודות', 'יצירת קשר', 'הצהרת נגישות');
    }
    acts.forEach((a) => feats.push(ACT[a].feat));
    if (track !== 'landing') feats.push('אזור ניהול לעדכון תוכן');
    if (track === 'club') feats.push('חשבונות משתמש ורשימת תפוצה עם הסכמה');
    if (en) feats.push('גרסה באנגלית עם מעבר בלחיצה');
    feats.push('התאמה לגוגל ולטלפון');
    return { track, pages, feats };
  };

  const fill = (ul, items, key) => {
    ul.replaceChildren(...items.map((t) => {
      const li = document.createElement('li');
      li.textContent = t;
      if (prev[key].size && !prev[key].has(t)) li.className = 'is-new';
      return li;
    }));
    prev[key] = new Set(items);
  };

  const render = () => {
    const ans = read();
    const p = plan(ans);
    $('r-track').textContent = TRACK[p.track].name;
    $('r-why').textContent = TRACK[p.track].why;
    $('r-count').textContent = p.track === 'landing' ? '1' : String(p.pages.length);
    fill($('r-pages'), p.pages, 'pages');
    fill($('r-feats'), p.feats, 'feats');

    const msg = [
      'שלום יוסף, בניתי תכנית באתר שלך ואשמח להצעת מחיר.',
      '',
      `מה יש לנו: ${KIND[ans.kind].label}`,
      `מה הלקוח צריך לעשות: ${ans.acts.length ? ans.acts.map((a) => ACT[a].label).join(', ') : 'לא נבחר'}`,
      `מי יעדכן: ${UPD[ans.upd]}`,
      ans.en ? 'צריך גם גרסה באנגלית' : '',
      `המסלול שהומלץ: ${TRACK[p.track].name}`
    ].filter((l, i) => l !== '' || i === 1).join('\n');
    const send = $('r-send');
    send.href = waLink(msg);
    send.target = '_blank';
    send.rel = 'noopener';
  };

  form.addEventListener('change', render);
  render();
})();
