/* ===== פרטי קשר ומחירים: להחליף כאן לפני העלייה לאוויר ===== */
const CONFIG = {
  email: 'yyrkg22@gmail.com',
  phone: '054-854-0700',              // לשיחות בלבד
  showPrice: true                     // false מסתיר את הערכת המחיר במתכנן
};
/* המחירים ומה שכלול בכל מסלול נקראים מכרטיסי המסלולים ב-index.html (data-price, data-en),
   כך שמעדכנים מחיר במקום אחד בלבד. */
const PKG = {};
document.querySelectorAll('.pkg[data-track]').forEach((el) => {
  const row = (k) => { const r = el.querySelector(`[data-k="${k}"]`); return r ? { label: r.querySelector('span').textContent, value: r.querySelector('b').textContent } : null; };
  PKG[el.dataset.track] = {
    price: Number(el.dataset.price), en: Number(el.dataset.en),
    incl: ['design', 'revisions', 'delivery', 'support'].map(row).filter(Boolean)
  };
});

const root = document.documentElement;
const telHref = 'tel:' + CONFIG.phone.replace(/[^\d+]/g, '');
const mailHref = (subject, body) =>
  `mailto:${CONFIG.email}?subject=${encodeURIComponent(subject)}` + (body ? `&body=${encodeURIComponent(body)}` : '');
const finePointer = window.matchMedia('(pointer: fine)').matches;

/* פרטי הקשר בכל העמוד */
document.querySelectorAll('[data-tel]').forEach((a) => {
  a.href = telHref;
  const bdi = a.querySelector('bdi');
  if (bdi) bdi.textContent = CONFIG.phone;
  else if (/\d/.test(a.textContent)) a.textContent = CONFIG.phone;
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

/* לולאת גלילה אחת לכל מה שזז עם הגלילה */
const scrollJobs = [];
let scrollTicking = false;
const runScrollJobs = () => { scrollTicking = false; scrollJobs.forEach((fn) => fn()); };
const requestScrollJobs = () => { if (!scrollTicking) { scrollTicking = true; requestAnimationFrame(runScrollJobs); } };
window.addEventListener('scroll', requestScrollJobs, { passive: true });
window.addEventListener('resize', requestScrollJobs);
motionListeners.push(requestScrollJobs);
const progressIn = (el, start = 1, span = 1) => {
  const r = el.getBoundingClientRect();
  const vh = window.innerHeight;
  return Math.min(1, Math.max(0, (vh * start - r.top) / (r.height * span + vh * (start - 0.15))));
};

/* ===== פתיחה: חפיסת כיווני העיצוב (כלל 4, תפקיד: להראות את המוצר) ===== */
(() => {
  const deck = document.getElementById('deck');
  const note = document.getElementById('proof-note');
  const buttons = [...document.querySelectorAll('.switch button')];
  if (!deck || !buttons.length) return;
  const cards = [...deck.querySelectorAll('.card')];
  let order = cards.map((c) => c.dataset.shot);

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
    layout();
    if (motionOn()) {
      const front = cards.find((c) => c.dataset.shot === shot);
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
  const deal = () => {
    if (!motionOn()) { deck.classList.remove('is-stacked', 'is-dealing'); return; }
    deck.classList.add('is-dealing');
    requestAnimationFrame(() => requestAnimationFrame(() => deck.classList.remove('is-stacked')));
    setTimeout(() => deck.classList.remove('is-dealing'), 1400);
  };
  const front = cards[0].querySelector('img');
  if (front.complete) deal(); else { front.addEventListener('load', deal, { once: true }); setTimeout(deal, 1200); }

  /* הטיה לפי העכבר: רואים את החפיסה מזווית אחרת */
  const hero = document.querySelector('.hero');
  const dial = document.querySelector('.dial');
  if (finePointer) {
    hero.addEventListener('pointermove', (e) => {
      if (!motionOn()) return;
      const r = hero.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - 0.5;
      const y = (e.clientY - r.top) / r.height - 0.5;
      hero.style.setProperty('--mx', `${(x + 0.5) * 100}%`);
      hero.style.setProperty('--my', `${(y + 0.5) * 100}%`);
      deck.style.setProperty('--ry', `${14 + x * 10}deg`);
      deck.style.setProperty('--rx', `${7 - y * 8}deg`);
      dial.style.setProperty('--dx', `${x * -24}px`);
      dial.style.setProperty('--dy', `${y * -18}px`);
      dial.style.setProperty('--dr', `${x * 8}deg`);
    });
    hero.addEventListener('pointerleave', () => {
      ['--ry', '--rx'].forEach((v) => deck.style.removeProperty(v));
      ['--dx', '--dy', '--dr'].forEach((v) => dial.style.removeProperty(v));
    });
  }
  motionListeners.push((on) => {
    if (!on) {
      deck.classList.remove('is-stacked', 'is-dealing');
      ['--ry', '--rx'].forEach((v) => deck.style.removeProperty(v));
      ['--dx', '--dy', '--dr'].forEach((v) => dial.style.removeProperty(v));
    }
  });
})();

/* ===== עבודות: המסכים גוללים את האתר האמיתי בזמן שגוללים את העמוד ===== */
(() => {
  const groups = [...document.querySelectorAll('.case')].map((c) => ({
    el: c.querySelector('.shot'),
    screens: [...c.querySelectorAll('.live')].map((box) => ({ box, img: box.querySelector('img') }))
  }));
  const visible = new Set();
  const io = 'IntersectionObserver' in window
    ? new IntersectionObserver((es) => { es.forEach((e) => (e.isIntersecting ? visible.add(e.target) : visible.delete(e.target))); requestScrollJobs(); }, { rootMargin: '100px 0px' })
    : null;
  groups.forEach((g) => (io ? io.observe(g.el) : visible.add(g.el)));
  scrollJobs.push(() => {
    groups.forEach((g) => {
      if (!visible.has(g.el) && motionOn()) return;
      const r = g.el.getBoundingClientRect();
      const vh = window.innerHeight;
      let p = (vh * 0.9 - r.top) / (vh * 0.9 + r.height * 0.4);
      p = motionOn() ? Math.min(1, Math.max(0, p)) : 0;
      g.screens.forEach(({ box, img }) => {
        const max = Math.max(0, img.offsetHeight - box.clientHeight);
        img.style.setProperty('--shift', `${(-max * p).toFixed(1)}px`);
      });
    });
  });
  groups.forEach((g) => g.screens.forEach(({ img }) => img.addEventListener('load', requestScrollJobs)));
})();

/* ===== מרוץ המהירות: מראה את התוצאה, פעם אחת כשמגיעים אליו ===== */
(() => {
  const box = document.getElementById('race');
  const replay = document.getElementById('race-replay');
  if (!box) return;
  const bars = [...box.querySelectorAll('.race-bar i')].map((i) => ({
    clock: i.querySelector('.race-clock'), sec: parseFloat(i.dataset.sec), dur: parseFloat(i.style.getPropertyValue('--t')) * 1000
  }));
  let raf = 0;
  const tick = (t0) => (t) => {
    let running = false;
    bars.forEach((b) => {
      const k = Math.min(1, (t - t0) / b.dur);
      if (b.clock) b.clock.textContent = (b.sec * k).toFixed(1) + 's';
      if (k < 1) running = true;
    });
    if (running) raf = requestAnimationFrame(tick(t0));
  };
  const run = () => {
    if (!motionOn()) return;
    cancelAnimationFrame(raf);
    box.classList.remove('is-running');
    box.classList.add('is-ready');
    void box.offsetWidth;            // מאפס את הפסים לפני הריצה
    box.classList.add('is-running');
    raf = requestAnimationFrame((t0) => tick(t0)(t0));
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

/* ===== תהליך: הקו מתמלא בגלילה ומדליק כל שלב שהוא מגיע אליו ===== */
(() => {
  const steps = document.getElementById('steps');
  if (!steps) return;
  const items = [...steps.children];
  scrollJobs.push(() => {
    const p = motionOn() ? progressIn(steps, 0.85, 1.3) : 1;
    steps.style.setProperty('--p', p.toFixed(3));
    const vertical = window.matchMedia('(max-width: 900px)').matches;
    items.forEach((li, i) => {
      const at = vertical ? li.offsetTop / steps.offsetHeight : i / items.length;
      li.classList.toggle('is-on', p >= at + 0.02 || p === 1);
    });
  });
  requestScrollJobs();
})();

/* ===== משרטוט לאתר חי: לפני ואחרי (כלל 4, תפקיד: להראות שינוי מצב) ===== */
(() => {
  const ba = document.getElementById('ba');
  if (!ba) return;
  const range = ba.querySelector('.ba-range');
  let touched = false;
  const set = (p) => ba.style.setProperty('--p', Math.min(1, Math.max(0, p)).toFixed(3));
  const fromRange = () => { touched = true; set(range.value / 100); };
  range.addEventListener('input', fromRange);
  /* גרירה ישירה על התמונה, גם בטלפון */
  ba.addEventListener('pointerdown', (e) => {
    if (e.pointerType === 'mouse' && e.button !== 0) return;
    const r = ba.getBoundingClientRect();
    const move = (ev) => { touched = true; const p = (r.right - ev.clientX) / r.width; set(p); range.value = Math.round(p * 100); };
    move(e);
    const up = () => { window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', up); };
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
  });
  /* עד שנוגעים: השרטוט "נבנה" לאתר בזמן הגלילה */
  scrollJobs.push(() => {
    if (touched) return;
    if (!motionOn()) { set(0.5); range.value = 50; return; }
    const r = ba.getBoundingClientRect();
    const vh = window.innerHeight;
    const k = Math.min(1, Math.max(0, (vh * 0.85 - r.top) / (vh * 0.85)));
    const p = 0.92 - k * 0.84;
    set(p); range.value = Math.round(p * 100);
  });
  requestScrollJobs();
})();

/* ===== יצירת קשר: קו האור נמשך אל הכפתור (תפקיד: להוביל את העין) ===== */
(() => {
  const sw = document.querySelector('.swoosh');
  const sec = document.getElementById('contact');
  if (!sw || !sec) return;
  scrollJobs.push(() => {
    const d = motionOn() ? progressIn(sec, 1, 0.6) : 1;
    sw.style.setProperty('--draw', d.toFixed(3));
  });
  requestScrollJobs();
})();

/* ===== בחירת מסלול: לחיצה על כרטיס בוחרת וממשיכה ליצירת קשר ===== */
const pickPackage = (() => {
  const cards = [...document.querySelectorAll('.pkg[data-track]')];
  const box = document.getElementById('picked');
  if (!cards.length || !box) return () => {};
  const nis = (n) => n.toLocaleString('he-IL') + ' ₪';
  const en = document.getElementById('picked-en');
  let current = null;

  const render = () => {
    if (!current) return;
    const card = cards.find((c) => c.dataset.track === current);
    const name = card.querySelector('h3').textContent;
    const price = Number(card.dataset.price) + (en.checked ? Number(card.dataset.en) : 0);
    const rows = [...card.querySelectorAll('.pkg-row')].map((r) => ({ k: r.dataset.k, label: r.querySelector('span').textContent, value: r.querySelector('b').textContent }));
    document.getElementById('picked-name').textContent = name;
    document.getElementById('picked-price').textContent = `החל מ־${nis(price)} לפני מע״מ` + (en.checked ? ', כולל אנגלית' : '');
    document.getElementById('picked-incl').replaceChildren(...rows.filter((r) => ['design', 'revisions', 'delivery', 'support'].includes(r.k)).map((r) => {
      const li = document.createElement('li');
      const sp = document.createElement('span'); sp.textContent = r.label;
      const b = document.createElement('b'); b.textContent = r.value;
      li.append(sp, b); return li;
    }));
    const body = [
      'שלום,', '',
      `אשמח להתקדם עם המסלול: ${name}`,
      `מחיר: החל מ־${nis(price)} לפני מע״מ` + (en.checked ? ' (כולל גרסה באנגלית)' : ''),
      ...rows.map((r) => `${r.label}: ${r.value}`),
      '', 'שם העסק: ', 'יש לעסק כבר אתר? ', 'טלפון לחזרה: '
    ].join('\n');
    document.querySelectorAll('#contact [data-mail]').forEach((a) => { a.href = mailHref(`בחירת מסלול: ${name}`, body); });
  };

  const pick = (track, go = true) => {
    current = track;
    cards.forEach((c) => {
      const on = c.dataset.track === track;
      c.classList.toggle('is-selected', on);
      c.querySelector('.pkg-pick').setAttribute('aria-pressed', String(on));
      c.querySelector('.pkg-pick').firstChild.textContent = on ? 'נבחר. ממשיכים' : 'לבחור במסלול הזה';
    });
    box.hidden = false;
    box.style.animation = 'none'; void box.offsetWidth; box.style.animation = '';
    render();
    if (go) {
      document.getElementById('contact').scrollIntoView({ behavior: motionOn() ? 'smooth' : 'auto', block: 'start' });
      setTimeout(() => box.focus({ preventScroll: true }), motionOn() ? 700 : 0);
    }
  };

  cards.forEach((c) => {
    c.addEventListener('click', (e) => { if (e.target.closest('a')) return; pick(c.dataset.track); });
  });
  /* מסגרת האור של המסלול המומלץ זזה רק כשרואים אותה */
  const grid = document.querySelector('.pkgs');
  if ('IntersectionObserver' in window && grid) {
    new IntersectionObserver((es) => es.forEach((e) => grid.classList.toggle('is-live', e.isIntersecting)), { threshold: 0.25 }).observe(grid);
  }
  en.addEventListener('change', render);
  document.getElementById('picked-change').addEventListener('click', () => {
    document.getElementById('packages').scrollIntoView({ behavior: motionOn() ? 'smooth' : 'auto' });
  });
  return pick;
})();

/* ===== מתכנן האתר: הדוגמה החינמית (כלל 5) ===== */
(() => {
  const form = document.getElementById('planner');
  if (!form) return;
  const $ = (id) => document.getElementById(id);
  const prev = { pages: new Set(), feats: new Set() };
  const nis = (n) => Math.round(n).toLocaleString('he-IL') + ' ₪';

  const KIND = {
    products: { label: 'מוצרים או תפריט', pages: ['קטלוג', 'עמוד לכל מוצר'] },
    org: { label: 'חוגים, מוסד או ארגון', pages: ['חוגים ופעילויות', 'מידע להורים ולמשתתפים'] },
    service: { label: 'שירות מקצועי', pages: ['שירותים', 'לקוחות ועבודות'] }
  };
  const ACT = {
    order: { label: 'להזמין או לבקש הצעת מחיר', feat: 'טופס הזמנה שמגיע למייל עם כל הפרטים' },
    register: { label: 'להירשם', feat: 'טופס הרשמה ואישור קבלה במייל', page: 'הרשמה' },
    contact: { label: 'להתקשר או לכתוב במייל', feat: 'כפתור חיוג ומייל שתמיד נראה' },
    read: { label: 'לקרוא תוכן שמתעדכן', feat: 'מדור תוכן (מתכונים, שירים או מאמרים)', page: 'מדור תוכן' },
    pay: { label: 'לקנות ולשלם באתר', feat: 'עגלת קניות ותשלום באשראי (חברת הסליקה בנפרד)' }
  };
  const UPD = { none: 'אף אחד, הוא קבוע', me: 'אני, מדי פעם', team: 'צוות, עם רשימת תפוצה' };
  const TRACK = {
    landing: { name: 'דף נחיתה', why: 'עמוד אחד ברור, עם כל מה שצריך כדי לפנות אליכם.' },
    site: { name: 'אתר עם אזור ניהול', why: 'כמה עמודים, ומסך פשוט לעדכון מה שמשתנה.' },
    club: { name: 'אתר ומועדון עדכונים', why: 'כל מה שבאתר עם אזור ניהול, ועוד חשבונות ורשימת תפוצה.' },
    store: { name: 'חנות אונליין', why: 'קטלוג, עגלה ותשלום באתר, עם ניהול מוצרים והזמנות.' }
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
    if (track === 'landing') {
      pages = ['עמוד אחד: פתיחה, מה אתם מציעים, אודות ויצירת קשר', 'הצהרת נגישות'];
    } else if (track === 'store') {
      pages = ['דף הבית', 'קטגוריות', 'עמוד לכל מוצר', 'עגלה ותשלום', 'משלוחים והחזרות', 'תקנון ופרטיות', 'אודות', 'יצירת קשר', 'הצהרת נגישות'];
    } else {
      pages = ['דף הבית', ...KIND[kind].pages];
      acts.forEach((a) => ACT[a].page && pages.push(ACT[a].page));
      pages.push('אודות', 'יצירת קשר', 'הצהרת נגישות');
    }
    acts.forEach((a) => feats.push(ACT[a].feat));
    if (track === 'store') feats.push('ניהול מוצרים, מלאי והזמנות');
    else if (track !== 'landing') feats.push('אזור ניהול לעדכון תוכן');
    if (track === 'club' || (track === 'store' && upd === 'team')) feats.push('חשבונות משתמש ורשימת תפוצה עם הסכמה');
    if (en) feats.push('גרסה באנגלית עם מעבר בלחיצה');
    feats.push('התאמה לגוגל ולטלפון');
    const pkg = PKG[track];
    const price = CONFIG.showPrice && pkg ? pkg.price + (en ? pkg.en : 0) : null;
    return { track, pages, feats, price, incl: pkg ? pkg.incl : [] };
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

  /* המחיר "מתגלגל" לערך החדש: מראה מה השתנה */
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

  let planText = '';
  let lastTrack = null;
  const resultBox = document.getElementById('result');
  const render = () => {
    const ans = read();
    const p = plan(ans);
    if (lastTrack && motionOn()) { resultBox.classList.remove('is-pulse'); void resultBox.offsetWidth; resultBox.classList.add('is-pulse'); }
    lastTrack = p.track;
    $('r-track').textContent = TRACK[p.track].name;
    $('r-why').textContent = TRACK[p.track].why;
    $('r-count').textContent = p.track === 'landing' ? '1' : String(p.pages.length);
    if (p.price === null) $('r-price-wrap').hidden = true;
    else setPrice(p.price);
    fill($('r-pages'), p.pages, 'pages');
    fill($('r-feats'), p.feats, 'feats');
    $('r-incl').replaceChildren(...p.incl.map(({ label, value }) => {
      const li = document.createElement('li');
      const sp = document.createElement('span'); sp.textContent = label;
      const b = document.createElement('b'); b.textContent = value;
      li.append(sp, b);
      return li;
    }));

    planText = [
      'שלום, מילאתי את המתכנן באתר ואשמח להצעת מחיר.',
      '',
      `מה יש לנו: ${KIND[ans.kind].label}`,
      `מה הלקוח צריך לעשות: ${ans.acts.length ? ans.acts.map((a) => ACT[a].label).join(', ') : 'לא נבחר'}`,
      `מי יעדכן: ${UPD[ans.upd]}`,
      ans.en ? 'צריך גם גרסה באנגלית' : null,
      `המסלול שהומלץ: ${TRACK[p.track].name}`,
      p.price !== null ? `הערכה ראשונית: ${nis(p.price)} לפני מע״מ` : null,
      ...p.incl.map(({ label, value }) => `${label}: ${value}`),
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

  $('r-more').addEventListener('click', (e) => {
    e.preventDefault();
    const card = document.querySelector(`.pkg[data-track="${lastTrack}"]`);
    if (!card) return;
    card.scrollIntoView({ behavior: motionOn() ? 'smooth' : 'auto', block: 'center', inline: 'center' });
    card.classList.remove('is-flash'); void card.offsetWidth; card.classList.add('is-flash');
    card.querySelector('.pkg-pick').focus({ preventScroll: true });
  });

  form.addEventListener('change', render);
  render();
})();
