/* ===== פרטי קשר: להחליף כאן לפני העלייה לאוויר ===== */
const CONFIG = {
  email: 'yyrkg22@gmail.com',
  phone: '054-854-0700',                     // לשיחות בלבד
  formEndpoint: 'https://formsubmit.co/ajax/yyrkg22@gmail.com',
  extraPagePrice: 350,                       // עמוד מעבר למכסה של המסלול
  showPrice: true                            // false מסתיר את המחיר המשוער במתכנן
};

/* המחירים ומה שכלול בכל מסלול נקראים מכרטיסי המסלולים ב-index.html,
   כך שמעדכנים מחיר או פרט במקום אחד בלבד. */
const PKG = {};
document.querySelectorAll('.pkg[data-track]').forEach((el) => {
  const rows = [...el.querySelectorAll('.pkg-row')].map((r) => ({ k: r.dataset.k, label: r.querySelector('span').textContent, value: r.querySelector('b').textContent }));
  PKG[el.dataset.track] = {
    el, name: el.querySelector('h3').textContent,
    price: Number(el.dataset.price), en: Number(el.dataset.en), quota: Number(el.dataset.quota),
    rows, incl: rows.filter((r) => ['design', 'revisions', 'delivery', 'support'].includes(r.k))
  };
});

const root = document.documentElement;
const $ = (id) => document.getElementById(id);
const nis = (n) => Math.round(n).toLocaleString('he-IL') + ' ₪';
const telHref = 'tel:' + CONFIG.phone.replace(/[^\d+]/g, '');
const mailHref = (subject, body) =>
  `mailto:${CONFIG.email}?subject=${encodeURIComponent(subject)}` + (body ? `&body=${encodeURIComponent(body)}` : '');
const gmailHref = (subject, body) =>
  `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(CONFIG.email)}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
const finePointer = window.matchMedia('(pointer: fine)').matches;
const clamp01 = (v) => Math.min(1, Math.max(0, v));

/* פרטי הקשר בכל העמוד */
document.querySelectorAll('[data-tel]').forEach((a) => {
  a.href = telHref;
  const bdi = a.querySelector('bdi');
  if (bdi) bdi.textContent = CONFIG.phone;
  else if (/\d/.test(a.textContent)) a.textContent = CONFIG.phone;
});
const DEFAULT_MAIL = { subject: 'פנייה מהאתר: אתר לעסק שלי', body: 'שלום יהודה,\n\nהעסק שלי: \nיש לו כבר אתר? \nהטלפון שלי: \nמתי נוח שתתקשר: \n' };
const setMailLinks = (subject, body) => {
  document.querySelectorAll('[data-mail]').forEach((a) => {
    a.href = mailHref(subject, body);
    const b = a.querySelector('bdi');
    if (b) b.textContent = CONFIG.email;
  });
  const g = $('gmail-link');
  if (g) g.href = gmailHref(subject, body);
};
setMailLinks(DEFAULT_MAIL.subject, DEFAULT_MAIL.body);

/* ===== כותרת עליונה ותפריט לטלפון ===== */
const header = document.querySelector('.top');
const onScrollHeader = () => header.classList.toggle('is-stuck', window.scrollY > 8);
onScrollHeader();
window.addEventListener('scroll', onScrollHeader, { passive: true });
(() => {
  const btn = $('menu-btn');
  const menu = $('menu');
  if (!btn || !menu) return;
  const set = (open) => {
    btn.setAttribute('aria-expanded', String(open));
    menu.classList.toggle('is-open', open);
  };
  btn.addEventListener('click', () => set(btn.getAttribute('aria-expanded') !== 'true'));
  menu.addEventListener('click', (e) => { if (e.target.closest('a')) set(false); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && menu.classList.contains('is-open')) { set(false); btn.focus(); } });
  document.addEventListener('click', (e) => { if (!e.target.closest('.top')) set(false); });
})();

/* ===== מתג תנועה (כלל 4: חייב להיות מתג כיבוי) ===== */
const reduceQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
const store = {
  get() { try { return localStorage.getItem('yyg-motion'); } catch (e) { return null; } },
  set(v) { try { localStorage.setItem('yyg-motion', v); } catch (e) { /* בלי אחסון: המתג עובד עד רענון */ } }
};
const motionOn = () => !root.classList.contains('motion-off');
const motionBtn = $('motion-toggle');
const motionListeners = [];
const setMotion = (on, save) => {
  root.classList.toggle('motion-off', !on);
  motionBtn.setAttribute('aria-pressed', String(on));
  if (save) store.set(on ? 'on' : 'off');
  motionListeners.forEach((fn) => fn(on));
};
const saved = store.get();
setMotion(saved ? saved === 'on' : !reduceQuery.matches, false);
motionBtn.addEventListener('click', () => setMotion(!motionOn(), true));

/* ===== דבר אחד זז בכל מסך (כלל 4) =====
   כל אנימציה שקשורה לגלילה נרשמת כאן. בכל רגע רק זו שהכי קרובה למרכז המסך זזה,
   והשאר מחכות. מה שכבר עברנו או שעוד לא הגענו אליו מתייצב בלי שרואים אותו זז. */
const movers = [];
let busyUntil = 0;                 // אנימציה מבוססת זמן (המרוץ) תופסת את המסך עד שהיא נגמרת
let ticking = false;
const runMovers = () => {
  ticking = false;
  const vh = window.innerHeight;
  if (!motionOn()) { movers.forEach((m) => m.settle('static')); return; }
  let active = null, best = Infinity;
  movers.forEach((m) => {
    const r = m.el.getBoundingClientRect();
    if (r.bottom <= 0) { m.settle('after'); return; }
    if (r.top >= vh) { m.settle('before'); return; }
    const d = Math.abs((r.top + r.bottom) / 2 - vh / 2);
    if (d < best) { best = d; active = m; }
  });
  if (!active || performance.now() < busyUntil) return;
  active.update(active.el.getBoundingClientRect(), vh);
};
const requestMovers = () => { if (!ticking) { ticking = true; requestAnimationFrame(runMovers); } };
window.addEventListener('scroll', requestMovers, { passive: true });
window.addEventListener('resize', requestMovers);
motionListeners.push(requestMovers);

/* ===== פתיחה: חפיסת כיווני העיצוב (תפקיד: להראות את המוצר) ===== */
(() => {
  const deck = $('deck');
  const note = $('proof-note');
  const buttons = [...document.querySelectorAll('.switch button')];
  if (!deck || !buttons.length) return;
  const cards = [...deck.querySelectorAll('.card')];
  let order = cards.map((c) => c.dataset.shot);

  /* הקלפים האחוריים נטענים רק אחרי שהעמוד מוכן, כדי לא להאט את הפתיחה */
  const loadCard = (card) => {
    const img = card.querySelector('img[data-src]');
    if (!img) return;
    img.srcset = img.dataset.srcset; img.src = img.dataset.src;
    img.removeAttribute('data-src'); img.removeAttribute('data-srcset');
  };
  const loadAll = () => cards.forEach(loadCard);
  if (document.readyState === 'complete') setTimeout(loadAll, 200);
  else window.addEventListener('load', () => ('requestIdleCallback' in window ? requestIdleCallback(loadAll, { timeout: 1500 }) : setTimeout(loadAll, 300)), { once: true });

  const layout = () => {
    order.forEach((shot, d) => {
      const card = cards.find((c) => c.dataset.shot === shot);
      card.style.setProperty('--d', d);
      card.dataset.depth = d;
      const img = card.querySelector('img');
      const btn = buttons.find((b) => b.dataset.shot === shot);
      if (d === 0) { card.removeAttribute('aria-hidden'); img.alt = btn.dataset.alt; }
      else { card.setAttribute('aria-hidden', 'true'); img.alt = ''; }
    });
  };

  const show = (shot) => {
    order = [shot, ...order.filter((s) => s !== shot)];
    const btn = buttons.find((b) => b.dataset.shot === shot);
    buttons.forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
    note.textContent = btn.dataset.note;
    const front = cards.find((c) => c.dataset.shot === shot);
    loadCard(front);
    layout();
    if (motionOn()) {     // קו סריקה אחד על הקלף שעלה: מראה מה השתנה
      front.classList.remove('is-scan'); void front.offsetWidth; front.classList.add('is-scan');
      setTimeout(() => front.classList.remove('is-scan'), 900);
    }
  };

  buttons.forEach((b) => b.addEventListener('click', () => show(b.dataset.shot)));
  cards.forEach((c) => c.addEventListener('click', () => { if (c.dataset.depth !== '0') show(c.dataset.shot); }));
  document.querySelector('.switch').addEventListener('keydown', (e) => {
    const i = buttons.indexOf(document.activeElement);
    if (i < 0) return;
    let n = null;
    if (e.key === 'ArrowLeft') n = (i + 1) % buttons.length;   // RTL: שמאלה = הבא
    if (e.key === 'ArrowRight') n = (i - 1 + buttons.length) % buttons.length;
    if (n === null) return;
    e.preventDefault();
    buttons[n].focus();
    show(buttons[n].dataset.shot);
  });

  /* החלוקה: הקלפים נפרסים פעם אחת. הטקסט כבר מוצג, רק התמונות זזות */
  layout();
  let dealt = false;
  const deal = () => {
    if (dealt) return; dealt = true;
    if (!motionOn()) { deck.classList.remove('is-stacked', 'is-dealing'); return; }
    deck.classList.add('is-dealing');
    requestAnimationFrame(() => requestAnimationFrame(() => deck.classList.remove('is-stacked')));
    setTimeout(() => deck.classList.remove('is-dealing'), 1400);
  };
  const frontImg = cards[0].querySelector('img');
  if (frontImg.complete) deal(); else { frontImg.addEventListener('load', deal, { once: true }); setTimeout(deal, 1200); }

  /* במחשב רק החפיסה נוטה עם העכבר. שום דבר אחר בפתיחה לא זז */
  const hero = document.querySelector('.hero');
  if (finePointer) {
    hero.addEventListener('pointermove', (e) => {
      if (!motionOn()) return;
      const r = hero.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - 0.5;
      const y = (e.clientY - r.top) / r.height - 0.5;
      deck.style.setProperty('--ry', `${14 + x * 10}deg`);
      deck.style.setProperty('--rx', `${7 - y * 8}deg`);
    });
    hero.addEventListener('pointerleave', () => ['--ry', '--rx'].forEach((v) => deck.style.removeProperty(v)));
  }
  motionListeners.push((on) => {
    if (!on) { deck.classList.remove('is-stacked', 'is-dealing'); ['--ry', '--rx'].forEach((v) => deck.style.removeProperty(v)); }
  });
})();

/* ===== עבודות: מסך המחשב גולל את האתר האמיתי בזמן הגלילה (תפקיד: להראות את המוצר) ===== */
document.querySelectorAll('.case .shot').forEach((shot) => {
  const box = shot.querySelector('.screen.live');
  const img = box && box.querySelector('img');
  if (!img) return;
  const set = (p) => img.style.setProperty('--shift', `${(-Math.max(0, img.offsetHeight - box.clientHeight) * p).toFixed(1)}px`);
  movers.push({
    el: shot,
    update(r, vh) { set(clamp01((vh * 0.9 - r.top) / (vh * 0.9 + r.height * 0.4))); },
    settle(where) { set(where === 'after' ? 1 : 0); }
  });
  img.addEventListener('load', requestMovers);
});

/* ===== מרוץ המהירות: מראה את התוצאה, פעם אחת כשהוא במרכז המסך ===== */
(() => {
  const box = $('race');
  if (!box) return;
  const bars = [...box.querySelectorAll('.race-bar i')].map((i) => ({
    clock: i.querySelector('.race-clock'), sec: parseFloat(i.dataset.sec), dur: parseFloat(i.style.getPropertyValue('--t')) * 1000
  }));
  const longest = Math.max(...bars.map((b) => b.dur));
  let raf = 0, done = false;
  const tick = (t0) => (t) => {
    let running = false;
    bars.forEach((b) => {
      const k = Math.min(1, (t - t0) / b.dur);
      b.clock.textContent = (b.sec * k).toFixed(1) + 's';
      b.clock.classList.toggle('is-done', k === 1);
      if (k < 1) running = true;
    });
    if (running) raf = requestAnimationFrame(tick(t0));
  };
  const run = () => {
    if (!motionOn()) return;
    cancelAnimationFrame(raf);
    bars.forEach((b) => b.clock.classList.remove('is-done'));
    box.classList.remove('is-running');
    box.classList.add('is-ready');
    void box.offsetWidth;            // מאפס את הפסים לפני הריצה
    box.classList.add('is-running');
    busyUntil = performance.now() + longest;
    raf = requestAnimationFrame((t0) => tick(t0)(t0));
  };
  if (motionOn()) box.classList.add('is-ready');
  movers.push({
    el: box,
    update(r, vh) { if (!done && r.top > 0 && r.bottom < vh) { done = true; run(); } },
    settle(where) { if (where !== 'before' && !done) { done = true; box.classList.remove('is-ready'); } }
  });
  $('race-replay').addEventListener('click', run);
  motionListeners.push((on) => { if (!on) { box.classList.remove('is-ready', 'is-running'); done = true; } });
})();

/* ===== משרטוט לאתר חי (תפקיד: להראות שינוי מצב) ===== */
(() => {
  const ba = $('ba');
  if (!ba) return;
  const range = ba.querySelector('.ba-range');
  let touched = false;
  const set = (p) => {
    p = clamp01(p);
    ba.style.setProperty('--p', p.toFixed(3));
    const v = Math.round(p * 100);
    range.value = v;
    range.setAttribute('aria-valuetext', `${v}% שרטוט, ${100 - v}% האתר החי`);
  };
  range.addEventListener('input', () => { touched = true; set(range.value / 100); });
  ba.addEventListener('pointerdown', (e) => {
    if (e.pointerType === 'mouse' && e.button !== 0) return;
    const r = ba.getBoundingClientRect();
    const move = (ev) => { touched = true; set((r.right - ev.clientX) / r.width); };
    move(e);
    const up = () => { window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', up); };
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
  });
  movers.push({
    el: ba,
    update(r, vh) { if (!touched) set(0.92 - clamp01((vh * 0.85 - r.top) / (vh * 0.85)) * 0.84); },
    settle(where) { if (!touched) set(where === 'static' ? 0.5 : where === 'after' ? 0.08 : 0.92); }
  });
})();

/* ===== תהליך: הקו מתמלא ומדליק כל שלב שהוא מגיע אליו (תפקיד: להוביל את העין) ===== */
(() => {
  const wrap = $('steps-wrap');
  const steps = $('steps');
  if (!wrap || !steps) return;
  const items = [...steps.querySelectorAll('li')];
  const set = (p) => {
    wrap.style.setProperty('--p', p.toFixed(3));
    const vertical = window.matchMedia('(max-width: 900px)').matches;
    items.forEach((li, i) => {
      const at = vertical ? li.offsetTop / steps.offsetHeight : i / items.length;
      li.classList.toggle('is-on', p >= at + 0.01 || p === 1);
    });
  };
  movers.push({
    el: wrap,
    update(r, vh) { set(clamp01((vh * 0.85 - r.top) / (r.height * 1.3 + vh * 0.7))); },
    settle(where) { set(where === 'before' ? 0 : 1); }
  });
})();
requestMovers();

/* ===== הבחירה: מה שנשלח ביצירת קשר (מהמתכנן או מכרטיס מסלול) ===== */
const Selection = (() => {
  const box = $('picked');
  const en = $('picked-en');
  let s = null;
  const priceOf = () => {
    const p = PKG[s.track];
    return p.price + (en.checked ? p.en : 0) + (s.extraPages || 0) * CONFIG.extraPagePrice;
  };
  const summary = () => {
    if (!s) return '';
    const p = PKG[s.track];
    return [
      `${s.source === 'planner' ? 'התכנית מהמתכנן' : 'המסלול שנבחר'}: ${p.name}`,
      `מחיר ${s.source === 'planner' ? 'משוער' : 'החל מ־'}: ${nis(priceOf())} לפני מע״מ` + (en.checked ? ' (כולל גרסה באנגלית)' : ''),
      s.extraPages ? `כולל ${s.extraPages} עמודים מעבר למסלול` : null,
      ...p.incl.map((r) => `${r.label}: ${r.value}`),
      s.answers ? `\nתשובות במתכנן:\n${s.answers}` : null,
      s.pages ? `עמודים: ${s.pages.join(', ')}` : null,
      s.feats ? `יכולות: ${s.feats.join(', ')}` : null
    ].filter(Boolean).join('\n');
  };
  const render = () => {
    if (!s) return;
    const p = PKG[s.track];
    box.hidden = false;
    $('picked-k').textContent = s.source === 'planner' ? 'התכנית שלכם' : 'המסלול שבחרתם';
    $('picked-name').textContent = p.name;
    $('picked-price').textContent = `${s.source === 'planner' ? 'מחיר משוער' : 'החל מ־'} ${nis(priceOf())} לפני מע״מ` + (en.checked ? ', כולל אנגלית' : '');
    $('picked-incl').replaceChildren(...p.incl.map((r) => {
      const li = document.createElement('li');
      const sp = document.createElement('span'); sp.textContent = r.label;
      const b = document.createElement('b'); b.textContent = r.value;
      li.append(sp, b); return li;
    }));
    const text = summary();
    $('lead-plan').value = text;
    setMailLinks(`${s.source === 'planner' ? 'תכנית לאתר' : 'בחירת מסלול'}: ${p.name}`, `שלום יהודה,\n\n${text}\n\nשם: \nשם העסק: \nטלפון לחזרה: \nמתי נוח שתתקשר: \n`);
  };
  en.addEventListener('change', render);
  $('picked-change').addEventListener('click', () => $('packages').scrollIntoView({ behavior: motionOn() ? 'smooth' : 'auto' }));
  return {
    set(next) {
      s = next;
      en.checked = !!next.en;
      render();
      box.style.animation = 'none'; void box.offsetWidth; box.style.animation = '';
      $('contact').scrollIntoView({ behavior: motionOn() ? 'smooth' : 'auto', block: 'start' });
      setTimeout(() => box.focus({ preventScroll: true }), motionOn() ? 700 : 0);
    },
    text: summary
  };
})();

/* ===== מתכנן האתר: הדוגמה החינמית (כלל 5) ===== */
const Planner = (() => {
  const form = $('planner');
  if (!form) return null;
  const prev = { pages: new Set(), feats: new Set() };
  let touched = false;

  const KIND = {
    products: { label: 'מוצרים או תפריט', pages: ['קטלוג', 'עמוד לכל מוצר'] },
    org: { label: 'חוגים, מוסד או ארגון', pages: ['חוגים ופעילויות', 'מידע להורים ולמשתתפים'] },
    service: { label: 'שירות מקצועי', pages: ['שירותים', 'לקוחות ועבודות'] }
  };
  const ACT = {
    order: { label: 'להזמין או לבקש הצעת מחיר', feat: 'טופס הזמנה שמגיע עם כל הפרטים' },
    register: { label: 'להירשם', feat: 'טופס הרשמה ואישור קבלה במייל', page: 'הרשמה' },
    contact: { label: 'להתקשר או לכתוב', feat: 'כפתור חיוג ופנייה שתמיד נראה' },
    read: { label: 'לקרוא תוכן שמתעדכן', feat: 'מדור תוכן (מתכונים, שירים או מאמרים)', page: 'מדור תוכן' },
    pay: { label: 'לקנות ולשלם באתר', feat: 'עגלת קניות ותשלום באשראי (חברת הסליקה בנפרד)' }
  };
  const UPD = { none: 'כמעט כלום', me: 'תוכן מדי פעם, ואני מעדכן', team: 'גם לשלוח עדכונים לרשומים' };
  const TRACK = {
    landing: 'עמוד אחד ברור, עם כל מה שצריך כדי לפנות אליכם.',
    site: 'כמה עמודים, ומסך פשוט לעדכון מה שמשתנה.',
    club: 'אתר עם אזור ניהול, ועוד הרשמה, חשבונות ורשימת תפוצה.',
    store: 'קטלוג, עגלה ותשלום באתר, עם ניהול מוצרים והזמנות.'
  };

  const read = () => {
    const fd = new FormData(form);
    return { kind: fd.get('kind'), acts: fd.getAll('act'), upd: fd.get('upd'), en: fd.get('en') === 'on' };
  };

  const plan = ({ kind, acts, upd, en }) => {
    let track = 'site';
    if (acts.includes('pay')) track = 'store';
    else if (upd === 'team') track = 'club';
    else if (upd === 'none' && !acts.includes('register') && !acts.includes('read')) track = 'landing';

    let pages;
    const feats = [];
    if (track === 'landing') pages = ['עמוד אחד: פתיחה, מה אתם מציעים, אודות ויצירת קשר'];
    else if (track === 'store') pages = ['דף הבית', 'קטגוריות', 'עמוד לכל מוצר', 'עגלה ותשלום', 'משלוחים והחזרות', 'תקנון ופרטיות', 'אודות', 'יצירת קשר'];
    else {
      pages = ['דף הבית', ...KIND[kind].pages];
      acts.forEach((a) => ACT[a].page && pages.push(ACT[a].page));
      pages.push('אודות', 'יצירת קשר');
    }
    acts.forEach((a) => feats.push(ACT[a].feat));
    if (track === 'store') feats.push('ניהול מוצרים, מלאי והזמנות');
    else if (track !== 'landing') feats.push('אזור ניהול לעדכון תוכן');
    if (track === 'club' || (track === 'store' && upd === 'team')) feats.push('חשבונות משתמש ורשימת תפוצה עם הסכמה');
    if (en) feats.push('גרסה באנגלית עם מעבר בלחיצה');
    feats.push('התאמה לגוגל ולטלפון', 'הצהרת נגישות');

    const p = PKG[track];
    const extraPages = (track === 'site' || track === 'club') ? Math.max(0, pages.length - p.quota) : 0;
    const price = p.price + (en ? p.en : 0) + extraPages * CONFIG.extraPagePrice;
    return { track, pages, feats, extraPages, price };
  };

  const fill = (ul, items, key) => {
    ul.replaceChildren(...items.map((t) => {
      const li = document.createElement('li');
      li.textContent = t;
      if (prev[key].size && !prev[key].has(t)) li.className = 'is-new';   // סימון סטטי, בלי עוד תנועה
      return li;
    }));
    prev[key] = new Set(items);
  };

  /* התגובה היחידה שזזה: המחיר מתגלגל לערך החדש */
  const priceEl = $('r-price');
  let shown = null, raf = 0;
  const setPrice = (to) => {
    cancelAnimationFrame(raf);
    if (shown === null || !motionOn()) { shown = to; priceEl.textContent = nis(to); return; }
    const from = shown, t0 = performance.now(), dur = 450;
    const step = (t) => {
      const k = Math.min(1, (t - t0) / dur);
      shown = from + (to - from) * (1 - Math.pow(1 - k, 3));
      priceEl.textContent = nis(k === 1 ? to : Math.round(shown / 10) * 10);
      if (k < 1) raf = requestAnimationFrame(step); else shown = to;
    };
    raf = requestAnimationFrame(step);
  };

  let last = null;
  const answersText = (ans) => [
    `במה העסק עוסק: ${KIND[ans.kind].label}`,
    `מה אנשים צריכים לעשות: ${ans.acts.length ? ans.acts.map((a) => ACT[a].label).join(', ') : 'לא נבחר'}`,
    `מה ישתנה באתר: ${UPD[ans.upd]}`,
    ans.en ? 'צריך גם גרסה באנגלית' : null
  ].filter(Boolean).join('\n');

  const render = () => {
    const ans = read();
    const p = plan(ans);
    last = { ans, p };
    $('r-track').textContent = PKG[p.track].name;
    $('r-why').textContent = TRACK[p.track];
    if (!CONFIG.showPrice) $('r-price-wrap').hidden = true; else setPrice(p.price);
    const extra = $('r-extra');
    extra.hidden = !p.extraPages;
    extra.textContent = p.extraPages ? `כולל ${p.extraPages === 1 ? 'עמוד אחד' : p.extraPages + ' עמודים'} מעבר למסלול (${nis(CONFIG.extraPagePrice)} לעמוד)` : '';
    $('r-incl').replaceChildren(...PKG[p.track].incl.map(({ label, value }) => {
      const li = document.createElement('li');
      const sp = document.createElement('span'); sp.textContent = label;
      const b = document.createElement('b'); b.textContent = value;
      li.append(sp, b); return li;
    }));
    $('r-count').textContent = p.track === 'landing' ? 'עמוד אחד' : `${p.pages.length} עמודים`;
    fill($('r-pages'), p.pages, 'pages');
    fill($('r-feats'), p.feats, 'feats');
    $('r-live').textContent = `המסלול שמתאים לכם: ${PKG[p.track].name}. מחיר משוער ${nis(p.price)}.`;
    $('pb-track').textContent = PKG[p.track].name;
    $('pb-price').textContent = nis(p.price);
    $('r-status').textContent = '';
  };

  const planText = () => `${answersText(last.ans)}\n\nהמסלול שהומלץ: ${PKG[last.p.track].name}\nמחיר משוער: ${nis(last.p.price)} לפני מע״מ\nעמודים: ${last.p.pages.join(', ')}\nיכולות: ${last.p.feats.join(', ')}`;

  $('r-send').addEventListener('click', (e) => {
    e.preventDefault();
    Selection.set({ source: 'planner', track: last.p.track, en: last.ans.en, extraPages: last.p.extraPages, answers: answersText(last.ans), pages: last.p.pages, feats: last.p.feats });
  });
  $('r-copy').addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(planText()); $('r-status').textContent = 'התכנית הועתקה.'; }
    catch (e) { $('r-status').textContent = `לא הצלחנו להעתיק. אפשר לכתוב אל ${CONFIG.email}`; }
  });
  $('r-more').addEventListener('click', (e) => {
    e.preventDefault();
    const card = PKG[last.p.track].el;
    card.scrollIntoView({ behavior: motionOn() ? 'smooth' : 'auto', block: 'center', inline: 'center' });
    card.classList.remove('is-flash'); void card.offsetWidth; card.classList.add('is-flash');
    card.querySelector('.pkg-pick').focus({ preventScroll: true });
  });

  /* בטלפון: פס עם המסלול והמחיר, כדי לראות את התוצאה בזמן שעונים */
  const bar = $('plan-bar');
  if ('IntersectionObserver' in window && bar) {
    let formIn = false, resultIn = false;
    const sync = () => { bar.hidden = !(formIn && !resultIn); };
    new IntersectionObserver((es) => es.forEach((e) => { formIn = e.isIntersecting; sync(); }), { threshold: 0.15 }).observe(form);
    new IntersectionObserver((es) => es.forEach((e) => { resultIn = e.isIntersecting; sync(); }), { threshold: 0.25 }).observe($('result'));
    bar.addEventListener('click', (e) => { e.preventDefault(); $('result').scrollIntoView({ behavior: motionOn() ? 'smooth' : 'auto', block: 'start' }); $('result').focus({ preventScroll: true }); });
  }

  form.addEventListener('change', () => { touched = true; render(); });
  render();
  return { touched: () => touched, en: () => last.ans.en };
})();

/* ===== מסלולים: בחירה מכפתור או מהכרטיס, וממשיכים ליצירת קשר ===== */
(() => {
  const cards = Object.values(PKG).map((p) => p.el);
  const pick = (track) => {
    cards.forEach((c) => {
      const on = c.dataset.track === track;
      const btn = c.querySelector('.pkg-pick');
      c.classList.toggle('is-selected', on);
      btn.setAttribute('aria-pressed', String(on));
      btn.classList.toggle('btn-gold', on);          // הזהב עובר לכפתור של מה שנבחר
      btn.classList.toggle('btn-line', !on);
      btn.firstChild.textContent = on ? 'נבחר, ממשיכים ✓' : 'לבחור במסלול הזה';
    });
    Selection.set({ source: 'package', track, en: Planner && Planner.touched() ? Planner.en() : false });
  };
  cards.forEach((c) => {
    c.addEventListener('click', (e) => {
      if (e.target.closest('a, .pkg-toggle')) return;
      if (String(window.getSelection && window.getSelection()).length) return;   // מסמנים טקסט, לא בוחרים
      pick(c.dataset.track);
    });
    const t = c.querySelector('.pkg-toggle');
    if (t) t.addEventListener('click', () => {
      const open = !c.classList.contains('is-open');
      c.classList.toggle('is-open', open);
      t.setAttribute('aria-expanded', String(open));
      t.textContent = open ? 'פחות פרטים' : 'כל הפרטים';
    });
  });
  /* מעבר אור זהב אחד סביב המסלול המומלץ, בפעם הראשונה שמגיעים אליו (תפקיד: להוביל את העין) */
  const grid = document.querySelector('.pkgs');
  if ('IntersectionObserver' in window && grid) {
    const io = new IntersectionObserver((es) => { if (es.some((e) => e.isIntersecting) && motionOn()) { grid.classList.add('is-lit'); io.disconnect(); } }, { threshold: 0.35 });
    io.observe(grid);
  }
})();

/* ===== יצירת קשר: טופס שחוזר אליכם ===== */
(() => {
  const form = $('lead-form');
  if (!form) return;
  const status = $('lead-status');
  const submit = $('lead-submit');
  const fields = { name: form.elements.name, phone: form.elements.phone };
  const validPhone = (v) => v.replace(/\D/g, '').length >= 9;
  const fallback = () => {
    status.textContent = '';
    status.append('לא הצלחנו לשלוח מכאן. אפשר להתקשר ל־', Object.assign(document.createElement('a'), { className: 'text-link', href: telHref, textContent: CONFIG.phone }),
      ' או ', Object.assign(document.createElement('a'), { className: 'text-link', href: document.querySelector('[data-mail]').href, textContent: 'לשלוח מייל עם הפרטים' }), '.');
  };
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = fields.name.value.trim();
    const phone = fields.phone.value.trim();
    fields.name.setAttribute('aria-invalid', String(!name));
    fields.phone.setAttribute('aria-invalid', String(!validPhone(phone)));
    if (!name) { status.textContent = 'נא לכתוב שם.'; fields.name.focus(); return; }
    if (!validPhone(phone)) { status.textContent = 'נא לכתוב מספר טלפון מלא.'; fields.phone.focus(); return; }
    if (form.elements._honey.value) return;
    const data = {
      name, phone,
      business: form.elements.business.value.trim(),
      when: form.elements.when.value,
      notes: form.elements.notes.value.trim(),
      plan: $('lead-plan').value || 'לא נבחר מסלול',
      _subject: `פנייה חדשה מהאתר: ${name}`,
      _template: 'table',
      _captcha: 'false'
    };
    submit.disabled = true;
    status.className = 'lead-status';
    status.textContent = 'שולח…';
    try {
      const res = await fetch(CONFIG.formEndpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(data) });
      const json = await res.json().catch(() => ({}));
      if (!res.ok || String(json.success) !== 'true') throw new Error(json.message || res.status);
      form.reset();
      status.className = 'lead-status is-ok';
      status.textContent = `תודה, ${name}. הפרטים התקבלו, ואחזור אליכם בשעה שבחרתם.`;
    } catch (err) {
      fallback();
    } finally {
      submit.disabled = false;
    }
  });

  /* העתקת כתובת המייל */
  const copy = $('copy-mail');
  if (copy) copy.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(CONFIG.email); copy.textContent = 'הועתק'; setTimeout(() => { copy.textContent = 'העתקה'; }, 1800); }
    catch (e) { copy.textContent = CONFIG.email; }
  });

  /* ברק אחד על כפתור השליחה כשמגיעים לחלק (תפקיד: להוביל את העין לפעולה) */
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((es) => { if (es.some((e) => e.isIntersecting) && motionOn()) { submit.classList.add('is-cue'); io.disconnect(); } }, { threshold: 0.6 });
    io.observe(submit);
  }
})();
