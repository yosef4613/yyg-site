/* ===== פרטי קשר ומחירים: להחליף כאן לפני העלייה לאוויר ===== */
const CONFIG = {
  email: 'hello@example.com',
  phone: '050-000-0000',              // לשיחות בלבד
  // הערכת מחיר במתכנן (מתוך הצעת המחיר של פרחי שמיים). null מסתיר את ההערכה.
  prices: { landing: 1500, site: 3000, club: 4300 },
  english: { landing: 200, site: 400, club: 500 }
};

const root = document.documentElement;
const telHref = 'tel:' + CONFIG.phone.replace(/[^\d+]/g, '');
const mailHref = (subject, body) =>
  `mailto:${CONFIG.email}?subject=${encodeURIComponent(subject)}` + (body ? `&body=${encodeURIComponent(body)}` : '');

/* פרטי הקשר בכל העמוד */
document.querySelectorAll('[data-tel]').forEach((a) => {
  a.href = telHref;
  const label = a.querySelector('bdi') || (/\d/.test(a.textContent) ? a : null);
  if (label && label !== a) label.textContent = CONFIG.phone;
  else if (label) a.textContent = CONFIG.phone;
});
document.querySelectorAll('[data-mail]').forEach((a) => {
  a.href = mailHref('פנייה מהאתר: אתר לעסק שלי', 'שלום,\n\nהעסק שלי: \nיש לו כבר אתר? \nהטלפון שלי: \n');
  const b = a.querySelector('bdi');
  if (b) b.textContent = CONFIG.email;
});

/* קו תחתון לכותרת אחרי גלילה */
const header = document.querySelector('.top');
const onScrollHeader = () => header.classList.toggle('is-stuck', window.scrollY > 8);
onScrollHeader();
window.addEventListener('scroll', onScrollHeader, { passive: true });

/* ===== מתג תנועה (כלל 4: חייב להיות מתג כיבוי) ===== */
const reduceQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
const store = {
  get() { try { return localStorage.getItem('yyg-motion'); } catch (e) { return null; } },
  set(v) { try { localStorage.setItem('yyg-motion', v); } catch (e) { /* בלי אחסון: המתג עובד עד רענון */ } }
};
const motionOn = () => !root.classList.contains('motion-off');
const motionBtn = document.getElementById('motion-toggle');
const motionListeners = [];
const setMotion = (on, save) => {
  root.classList.toggle('motion-off', !on);
  motionBtn.setAttribute('aria-pressed', String(on));
  motionBtn.setAttribute('aria-label', on ? 'תנועה פועלת. לחצו לכיבוי' : 'תנועה כבויה. לחצו להפעלה');
  if (save) store.set(on ? 'on' : 'off');
  motionListeners.forEach((fn) => fn(on));
};
const saved = store.get();
setMotion(saved ? saved === 'on' : !reduceQuery.matches, false);
motionBtn.addEventListener('click', () => setMotion(!motionOn(), true));

/* ===== פתיחה: החלפת כיווני העיצוב (תנועה רק בתגובה ללחיצה) ===== */
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
    url.textContent = s === 'hk-site' ? 'הכיף שבאפיה, האתר שנבנה' : `הכיף שבאפיה, ${btn.dataset.name}`;
    note.textContent = btn.dataset.note;
    const swap = () => {
      img.srcset = srcset(s);
      img.src = `assets/img/${s}-1200.webp`;
      img.alt = btn.dataset.alt;
      const done = () => img.classList.remove('is-swapping');
      if (img.complete) requestAnimationFrame(done);
      else img.addEventListener('load', done, { once: true });
    };
    if (motionOn()) { img.classList.add('is-swapping'); setTimeout(swap, 140); }
    else swap();
  };

  buttons.forEach((b) => {
    b.addEventListener('click', () => show(b));
    b.addEventListener('pointerenter', () => warm(b.dataset.shot));
    b.addEventListener('focus', () => warm(b.dataset.shot));
  });

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

/* ===== השוואת מהירות: מרוץ שמראה את התוצאה, פעם אחת כשמגיעים אליו ===== */
(() => {
  const box = document.getElementById('speed');
  const replay = document.getElementById('speed-replay');
  if (!box) return;
  const run = () => {
    if (!motionOn()) return;
    box.classList.remove('is-running');
    box.classList.add('is-ready');
    void box.offsetWidth;            // מאפס את הפסים לפני הריצה
    box.classList.add('is-running');
  };
  if ('IntersectionObserver' in window && motionOn()) {
    box.classList.add('is-ready');
    const io = new IntersectionObserver((entries) => {
      if (entries.some((e) => e.isIntersecting)) { run(); io.disconnect(); }
    }, { threshold: 0.6 });
    io.observe(box);
  }
  replay.addEventListener('click', run);
  motionListeners.push((on) => { if (!on) box.classList.remove('is-ready', 'is-running'); });
})();

/* ===== תהליך: הקו מתמלא בגלילה ומוביל את העין לשלב הבא ===== */
(() => {
  const steps = document.getElementById('steps');
  if (!steps) return;
  let ticking = false;
  const update = () => {
    ticking = false;
    if (!motionOn()) { steps.style.setProperty('--p', 1); return; }
    const r = steps.getBoundingClientRect();
    const vh = window.innerHeight;
    const p = Math.min(1, Math.max(0, (vh * 0.85 - r.top) / (r.height + vh * 0.35)));
    steps.style.setProperty('--p', p.toFixed(3));
  };
  const onScroll = () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } };
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  motionListeners.push(update);
  update();
})();

/* ===== מתכנן האתר: הדוגמה החינמית ===== */
(() => {
  const form = document.getElementById('planner');
  if (!form) return;
  const $ = (id) => document.getElementById(id);
  const prev = { pages: new Set(), feats: new Set() };
  const nis = (n) => n.toLocaleString('he-IL') + ' ₪';

  const KIND = {
    products: { label: 'מוצרים או תפריט', pages: ['קטלוג', 'עמוד לכל מוצר'] },
    org: { label: 'חוגים, מוסד או ארגון', pages: ['חוגים ופעילויות', 'מידע להורים ולמשתתפים'] },
    service: { label: 'שירות מקצועי', pages: ['שירותים', 'לקוחות ועבודות'] }
  };
  const ACT = {
    order: { label: 'להזמין או לבקש הצעת מחיר', feat: 'טופס הזמנה שמגיע למייל עם כל הפרטים' },
    register: { label: 'להירשם', feat: 'טופס הרשמה ואישור קבלה במייל', page: 'הרשמה' },
    contact: { label: 'להתקשר או לכתוב במייל', feat: 'כפתור חיוג ומייל שתמיד נראה' },
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

    let pages;
    const feats = [];
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
    const price = CONFIG.prices ? CONFIG.prices[track] + (en ? CONFIG.english[track] : 0) : null;
    return { track, pages, feats, price };
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

  let planText = '';
  const render = () => {
    const ans = read();
    const p = plan(ans);
    $('r-track').textContent = TRACK[p.track].name;
    $('r-why').textContent = TRACK[p.track].why;
    $('r-count').textContent = p.track === 'landing' ? '1' : String(p.pages.length);
    if (p.price === null) { $('r-price-wrap').hidden = true; }
    else {
      const el = $('r-price');
      const txt = nis(p.price);
      if (el.textContent !== txt) { el.textContent = txt; el.classList.remove('is-new'); void el.offsetWidth; el.classList.add('is-new'); }
    }
    fill($('r-pages'), p.pages, 'pages');
    fill($('r-feats'), p.feats, 'feats');

    planText = [
      'שלום, מילאתי את המתכנן באתר ואשמח להצעת מחיר.',
      '',
      `מה יש לנו: ${KIND[ans.kind].label}`,
      `מה הלקוח צריך לעשות: ${ans.acts.length ? ans.acts.map((a) => ACT[a].label).join(', ') : 'לא נבחר'}`,
      `מי יעדכן: ${UPD[ans.upd]}`,
      ans.en ? 'צריך גם גרסה באנגלית' : null,
      `המסלול שהומלץ: ${TRACK[p.track].name}`,
      p.price !== null ? `הערכה ראשונית: ${nis(p.price)}` : null,
      '',
      'שם העסק: ',
      'טלפון לחזרה: '
    ].filter((l) => l !== null).join('\n');
    $('r-send').href = mailHref(`תכנית לאתר: ${TRACK[p.track].name}`, planText);
    $('r-status').textContent = '';
  };

  $('r-copy').addEventListener('click', async () => {
    const status = $('r-status');
    try {
      await navigator.clipboard.writeText(planText);
      status.textContent = `התכנית הועתקה. אפשר להדביק אותה במייל אל ${CONFIG.email}`;
    } catch (e) {
      status.textContent = `לא הצלחנו להעתיק. אפשר לכתוב אל ${CONFIG.email}`;
    }
  });
  $('r-send').addEventListener('click', () => {
    $('r-status').textContent = `נפתחת תוכנת המייל. אם היא לא נפתחה, כתבו אל ${CONFIG.email}`;
  });

  form.addEventListener('change', render);
  render();
})();
