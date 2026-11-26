/* ===== פרטי קשר: להחליף כאן לפני העלייה לאוויר ===== */
const CONFIG = {
  email: ['yehuda', 'yygweb.com'].join('@'),   // שמור בחלקים, כדי שרובוטים שאוספים כתובות לא ימצאו אותו
  phone: '054-854-0700',                     // לשיחות בלבד
  formEndpoint: '/send.php',                 // שולח מהשרת בהוסטינגר, בלי שירות חיצוני
  extraPagePrice: 400,                       // עמוד מעבר למכסה של המסלול
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
/* שפה: האתר בעברית, והעותק באנגלית יושב ב־/en/. T(עברית, English) */
const EN = document.documentElement.lang === 'en';
const T = (he, en) => (EN ? en : he);
const nis = (n) => (EN ? '₪' + Math.round(n).toLocaleString('en-US') : Math.round(n).toLocaleString('he-IL') + ' ₪');
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
const DEFAULT_MAIL = T({ subject: 'פנייה מהאתר: אתר לעסק שלי', body: 'שלום יהודה,\n\nהשם שלי: \nהעסק שלי: \nיש לו כבר אתר? \nטלפון (לא חובה): \n' },
  { subject: 'Website inquiry: a site for my business', body: 'Hi Yehuda,\n\nMy name: \nMy business: \nDo we already have a website? \nPhone (optional): \n' });
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
  menu.addEventListener('focusout', (e) => { if (menu.classList.contains('is-open') && !header.contains(e.relatedTarget)) set(false); });
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
    const d = (r.top <= vh / 2 && r.bottom >= vh / 2) ? 0 : Math.abs((r.top + r.bottom) / 2 - vh / 2);
    if (d < best) { best = d; active = m; }
  });
  if (!active || performance.now() < busyUntil) return;
  active.update(active.el.getBoundingClientRect(), vh);
};
const requestMovers = () => { if (!ticking) { ticking = true; requestAnimationFrame(runMovers); } };
window.addEventListener('scroll', requestMovers, { passive: true });
window.addEventListener('resize', requestMovers);
motionListeners.push(requestMovers);

/* ===== מונה כניסות פרטי: רק באתר האמיתי, בלי עוגיות ובלי פרטים אישיים ===== */
const countHit = (() => {
  if (location.hostname !== 'yygweb.com' || !navigator.sendBeacon) return () => {};
  const q = new URLSearchParams(location.search);
  const src = q.get('src') || q.get('utm_source') || '';
  const sent = new Set();
  return (e) => {
    if (sent.has(e)) return;
    sent.add(e);
    try { navigator.sendBeacon('/hit.php', new Blob([JSON.stringify({ e, p: location.pathname, r: e === 'view' ? document.referrer : '', s: src })], { type: 'application/json' })); } catch (err) { /* בלי מונה */ }
  };
})();
countHit('view');
if ('IntersectionObserver' in window) {
  const seen = new IntersectionObserver((es) => es.forEach((en) => { if (en.isIntersecting) { countHit(en.target.id); seen.unobserve(en.target); } }), { threshold: 0.25 });
  ['process', 'results', 'work', 'plan', 'packages', 'examples', 'contact'].forEach((id) => { const el = document.getElementById(id); if (el) seen.observe(el); });
}

/* ===== פתיחה: אתר אמיתי שמתעצב מחדש בלחיצה (תפקיד: להראות את המוצר ומה השתנה) =====
   קו אור עובר על האתר מימין לשמאל, ומאחוריו כבר מופיע העיצוב החדש. הכול בתוך המסגרת. */
(() => {
  const lab = $('lab');
  const mini = $('mini');
  const note = $('proof-note');
  const url = $('lab-url');
  const img = $('m-img');
  const inset = $('m-inset');
  const scan = lab && lab.querySelector('.lab-scan');
  const sw = document.querySelector('.switch');
  const buttons = [...document.querySelectorAll('.switch button')];
  if (!lab || !buttons.length) return;
  const src = (name) => `assets/img/hk/${name}-480.webp`;
  /* את התמונות של שאר העיצובים טוענים רק כשמתקרבים לכפתורים */
  let warmed = false;
  const warm = () => {
    if (warmed) return; warmed = true;
    buttons.forEach((b) => [b.dataset.img, b.dataset.inset].forEach((n) => { const i = new Image(); i.src = src(n); }));
  };
  ['pointerenter', 'focusin', 'touchstart'].forEach((ev) => sw.addEventListener(ev, warm, { passive: true, once: true }));

  const apply = (btn) => {
    mini.dataset.theme = btn.dataset.theme;
    img.src = src(btn.dataset.img);
    inset.src = src(btn.dataset.inset);
    url.textContent = btn.dataset.theme === 'live' ? T('hakef.com, האתר שבאוויר', 'hakef.com, the live site') : T(`הכיף שבאפיה, ${btn.dataset.name}`, `HaKef SheBaAfiya, ${btn.dataset.name}`);
    mini.setAttribute('aria-label', T(`דף הבית של הכיף שבאפיה, ${btn.dataset.name}`, `HaKef SheBaAfiya home page, ${btn.dataset.name}`));
    note.textContent = btn.dataset.note;
    buttons.forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
  };
  let busy = false;
  const show = async (btn) => {
    if (btn.getAttribute('aria-pressed') === 'true' || busy) return;
    if (!motionOn() || !mini.animate) { apply(btn); return; }
    busy = true;
    /* עותק של המצב הנוכחי נשאר מעל, והאתר האמיתי מתחלף מתחתיו */
    const clone = mini.cloneNode(true);
    clone.removeAttribute('id');
    clone.querySelectorAll('[id]').forEach((el) => el.removeAttribute('id'));
    clone.classList.add('mini-clone');
    clone.setAttribute('aria-hidden', 'true');
    lab.appendChild(clone);
    apply(btn);
    await Promise.all([img, inset].map((i) => (i.decode ? i.decode().catch(() => {}) : null)));
    const dur = 650, ease = 'cubic-bezier(.55,0,.25,1)';
    /* בעברית הקו עובר מימין לשמאל, ובאנגלית משמאל לימין */
    const a = clone.animate([{ clipPath: 'inset(0 0 0 0)' }, { clipPath: EN ? 'inset(0 0 0 100%)' : 'inset(0 100% 0 0)' }], { duration: dur, easing: ease, fill: 'forwards' });
    scan.animate(EN ? [{ right: '100%', opacity: 1 }, { right: '0%', opacity: 1 }] : [{ right: '0%', opacity: 1 }, { right: '100%', opacity: 1 }], { duration: dur, easing: ease });
    await a.finished.catch(() => {});
    clone.remove();
    busy = false;
  };
  buttons.forEach((b) => b.addEventListener('click', () => show(b)));
  sw.addEventListener('keydown', (e) => {
    const i = buttons.indexOf(document.activeElement);
    if (i < 0) return;
    let n = null;
    if (e.key === (EN ? 'ArrowRight' : 'ArrowLeft')) n = (i + 1) % buttons.length;   // הבא: שמאלה בעברית, ימינה באנגלית
    if (e.key === (EN ? 'ArrowLeft' : 'ArrowRight')) n = (i - 1 + buttons.length) % buttons.length;
    if (e.key === 'Home') n = 0;
    if (e.key === 'End') n = buttons.length - 1;
    if (n === null) return;
    e.preventDefault();
    buttons[n].focus();
    show(buttons[n]);
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

/* ===== סיפור: שרטוט, כיוונים, האתר החי והתוצאה. דבר אחד זז בכל שלב ===== */
(() => {
  const stage = $('stage');
  const list = $('story-steps');
  const race = $('race');
  if (!stage || !list) return;
  const items = [...list.querySelectorAll('li')];
  const tag = $('stage-tag');
  const TAGS = T({ 1: 'שרטוט', 2: 'כיווני עיצוב', 3: 'באוויר', 4: 'התוצאה' }, { 1: 'Blueprint', 2: 'Design directions', 3: 'Live', 4: 'The result' });

  /* המרוץ: רץ פעם אחת כשמגיעים לשלב 4 */
  const nums = [...race.querySelectorAll('.race-num b')];
  const bars = [...race.querySelectorAll('.race-bar i')].map((i, k) => ({
    num: nums[k], sec: parseFloat(i.dataset.sec), dur: parseFloat(i.style.getPropertyValue('--t')) * 1000
  }));
  const longest = Math.max(...bars.map((b) => b.dur));
  let raf = 0, raced = false;
  const tick = (t0) => (t) => {
    let running = false;
    bars.forEach((b) => {
      const k = Math.min(1, (t - t0) / b.dur);
      b.num.textContent = (b.sec * k).toFixed(1);       // המספר נספר יחד עם הפס
      if (k < 1) running = true;
    });
    if (running) raf = requestAnimationFrame(tick(t0));
  };
  const runRace = () => {
    if (!motionOn()) return;
    cancelAnimationFrame(raf);
    race.classList.remove('is-running');
    race.classList.add('is-ready');
    void race.offsetWidth;
    race.classList.add('is-running');
    busyUntil = performance.now() + longest + 450;
    raf = requestAnimationFrame((t0) => tick(t0)(t0));
  };
  $('race-replay').addEventListener('click', runRace);
  if (motionOn()) race.classList.add('is-ready');
  const finalNums = () => bars.forEach((b) => { b.num.textContent = b.sec.toFixed(1); });

  /* התמונות של הבמה נטענות מראש כשמתקרבים לסיפור, כדי שהחשיפה לא תראה מסגרת ריקה */
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((es) => {
      if (!es.some((e) => e.isIntersecting)) return;
      stage.querySelectorAll('img').forEach((im) => { im.loading = 'eager'; if (im.decode) im.decode().catch(() => {}); });
      io.disconnect();
    }, { rootMargin: '1200px 0px' });
    io.observe(stage);
  }

  let step = 0;
  const setStep = (n) => {
    if (n === step) return;
    step = n;
    stage.dataset.step = String(n);
    tag.textContent = TAGS[n];
    items.forEach((li) => li.classList.toggle('is-active', Number(li.dataset.step) === n));
    if (n === 4 && !raced) { raced = true; if (motionOn()) { bars.forEach((b) => { b.num.textContent = '0.0'; }); setTimeout(runRace, 450); } else { race.classList.remove('is-ready'); finalNums(); } }
  };
  const activeStep = (vh) => {
    let best = 1, d = Infinity;
    items.forEach((li) => {
      const r = li.getBoundingClientRect();
      const dist = (r.top <= vh * 0.55 && r.bottom >= vh * 0.55) ? 0 : Math.abs((r.top + r.bottom) / 2 - vh * 0.55);
      if (dist < d) { d = dist; best = Number(li.dataset.step); }
    });
    return best;
  };
  const drawProgress = (vh) => {
    const r = items[0].getBoundingClientRect();
    return clamp01((vh * 0.9 - r.top) / (r.height * 0.95 + vh * 0.15));
  };
  movers.push({
    el: list,
    update(r, vh) {
      const n = activeStep(vh);
      stage.style.setProperty('--p', n > 1 ? '1' : drawProgress(vh).toFixed(3));
      setStep(n);
    },
    settle(where) {
      const vh = window.innerHeight;
      if (where === 'static') { stage.style.setProperty('--p', '1'); setStep(activeStep(vh)); raced = true; race.classList.remove('is-ready', 'is-running'); finalNums(); return; }
      if (where === 'after') { stage.style.setProperty('--p', '1'); if (!raced) { raced = true; race.classList.remove('is-ready'); finalNums(); } setStep(4); }
      else { stage.style.setProperty('--p', '0'); setStep(1); }
    }
  });
  motionListeners.push((on) => { if (!on) race.classList.remove('is-ready', 'is-running'); });
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
    /* הקו מגיע לשלב 5 כשכל השלבים עוד על המסך: במחשב כשראש האזור בשליש העליון של המסך,
       ובטלפון (קו אנכי) כשקו הקריאה, ב־62% מגובה המסך, מגיע לסוף הרשימה */
    update(r, vh) {
      const vertical = window.matchMedia('(max-width: 900px)').matches;
      set(clamp01(vertical ? (vh * 0.62 - r.top) / Math.max(r.height, 1) : (vh * 0.85 - r.top) / (vh * 0.5)));
    },
    settle(where) { set(where === 'before' ? 0 : 1); }
  });
})();
requestMovers();

/* ===== הבחירה: מה שנשלח ביצירת קשר (מהמתכנן או מכרטיס מסלול) ===== */
const extraText = (n) => T(n === 1 ? `כולל עמוד אחד נוסף (${nis(CONFIG.extraPagePrice)})` : `כולל ${n} עמודים נוספים (${nis(CONFIG.extraPagePrice)} לעמוד)`,
  n === 1 ? `includes 1 extra page (${nis(CONFIG.extraPagePrice)})` : `includes ${n} extra pages (${nis(CONFIG.extraPagePrice)} per page)`);
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
      `${s.source === 'planner' ? T('התכנית מהמתכנן', 'Plan from the calculator') : T('המסלול שנבחר', 'Chosen package')}: ${p.name}`,
      T(`מחיר: ${s.source === 'planner' ? 'משוער ' : 'החל מ־'}${nis(priceOf())}, בלי תוספת מע״מ`, `Price: ${s.source === 'planner' ? 'estimated ' : 'from '}${nis(priceOf())}, no VAT added`) + (en.checked ? T(' (כולל גרסה באנגלית)', ' (including an English version)') : ''),
      s.extraPages ? extraText(s.extraPages) : null,
      ...p.incl.map((r) => `${r.label}: ${r.value}`),
      s.answers ? T(`\nתשובות במתכנן:\n${s.answers}`, `\nCalculator answers:\n${s.answers}`) : null,
      s.pages ? T(`עמודים: ${s.pages.join(', ')}`, `Pages: ${s.pages.join(', ')}`) : null,
      s.feats ? T(`יכולות: ${s.feats.join(', ')}`, `Features: ${s.feats.join(', ')}`) : null
    ].filter(Boolean).join('\n');
  };
  const render = () => {
    if (!s) return;
    const p = PKG[s.track];
    box.hidden = false;
    $('picked-k').textContent = s.source === 'planner' ? T('התכנית שלכם', 'Your plan') : T('המסלול שבחרתם', 'Your package');
    const pq = $('picked-q');
    if (pq) pq.href = T('/questionnaire/', '/en/questionnaire/') + '?package=' + s.track + '&src=' + (s.source === 'planner' ? 'calc' : 'pkg');
    $('picked-name').textContent = p.name;
    $('picked-price').textContent = T(`${s.source === 'planner' ? 'מחיר משוער ' : 'החל מ־'}${nis(priceOf())}, בלי תוספת מע״מ`, `${s.source === 'planner' ? 'Estimated ' : 'From '}${nis(priceOf())}, no VAT added`) + (en.checked ? T(', כולל אנגלית', ', with English') : '') + (s.extraPages ? `, ${extraText(s.extraPages)}` : '');
    $('picked-incl').replaceChildren(...p.incl.map((r) => {
      const li = document.createElement('li');
      const sp = document.createElement('span'); sp.textContent = r.label;
      const b = document.createElement('b'); b.textContent = r.value;
      li.append(sp, b); return li;
    }));
    const text = summary();
    $('lead-plan').value = text;
    setMailLinks(T(`${s.source === 'planner' ? 'תכנית לאתר' : 'בחירת מסלול'}: ${p.name}`, `${s.source === 'planner' ? 'Website plan' : 'Package choice'}: ${p.name}`),
      T(`שלום יהודה,\n\n${text}\n\nשם: \nשם העסק: \nטלפון (לא חובה): \n`, `Hi Yehuda,\n\n${text}\n\nName: \nBusiness: \nPhone (optional): \n`));
  };
  en.addEventListener('change', render);
  $('picked-change').addEventListener('click', () => { const h = $('pkgs-title'); h.setAttribute('tabindex', '-1'); $('packages').scrollIntoView({ behavior: motionOn() ? 'smooth' : 'auto' }); h.focus({ preventScroll: true }); });
  return {
    set(next, quiet) {
      s = next;
      en.checked = !!next.en;
      render();
      if (quiet) return;     // מהמחשבון: מעדכנים את הפנייה בשקט, בלי לקפוץ למטה
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
    products: T({ label: 'מוצרים או תפריט', pages: ['קטלוג', 'עמוד לכל מוצר'] }, { label: 'Products or a menu', pages: ['Catalog', 'A page per product'] }),
    org: T({ label: 'חוגים, מוסד או ארגון', pages: ['חוגים ופעילויות', 'מידע להורים ולמשתתפים'] }, { label: 'Classes, an institution or an organization', pages: ['Classes & activities', 'Info for parents & participants'] }),
    service: T({ label: 'שירות מקצועי', pages: ['שירותים', 'לקוחות ועבודות'] }, { label: 'A professional service', pages: ['Services', 'Clients & work'] }),
    store: T({ label: 'חנות אונליין', pages: ['קטגוריות', 'עמוד לכל מוצר'] }, { label: 'An online store', pages: ['Categories', 'A page per product'] })
  };
  const ACT = {
    order: T({ label: 'להזמין או לבקש הצעת מחיר', feat: 'טופס הזמנה שמגיע עם כל הפרטים' }, { label: 'Order or request a quote', feat: 'An order form that arrives with all the details' }),
    register: T({ label: 'להירשם', feat: 'טופס הרשמה ואישור קבלה במייל', page: 'הרשמה' }, { label: 'Sign up', feat: 'A sign-up form with an email confirmation', page: 'Sign-up' }),
    contact: T({ label: 'להתקשר או לכתוב', feat: 'כפתור חיוג ופנייה שתמיד נראה' }, { label: 'Call or write', feat: 'Call and contact buttons that are always visible' }),
    read: T({ label: 'לקרוא תוכן שמתעדכן', feat: 'מדור תוכן (מתכונים, שירים או מאמרים)', page: 'מדור תוכן' }, { label: 'Read updated content', feat: 'A content section (recipes, songs or articles)', page: 'Content section' }),
    pay: T({ label: 'לקנות ולשלם באתר', feat: 'עגלת קניות ותשלום באשראי (חברת הסליקה בנפרד)' }, { label: 'Buy and pay on the site', feat: 'A shopping cart and card payments (payment provider billed separately)' })
  };
  const UPD = T({ none: 'כמעט כלום', me: 'תוכן מדי פעם, ואני מעדכן', team: 'גם לשלוח עדכונים לרשומים' }, { none: 'Almost nothing', me: 'Occasional content, and I update it myself', team: 'Also send updates to subscribers' });
  const TRACK = {
    landing: T('עמוד אחד ברור, עם כל מה שצריך כדי לפנות אליכם.', 'One clear page with everything people need to reach you.'),
    site: T('כמה עמודים, ומסך פשוט לעדכון מה שמשתנה.', 'A few pages, plus a simple screen to update what changes.'),
    club: T('עד 12 עמודים, ומדור תוכן שמתעדכן: חדשות, מאמרים או אירועים.', 'Up to 12 pages, plus a content section that updates: news, articles or events.'),
    store: T('קטלוג, עגלה ותשלום באתר, עם ניהול מוצרים והזמנות.', 'A catalog, cart and checkout, with product and order management.')
  };

  const read = () => {
    const fd = new FormData(form);
    return { kind: fd.get('kind'), acts: fd.getAll('act'), upd: fd.get('upd'), en: fd.get('en') === 'on' };
  };

  /* יכולות שמוסיפים לאתר (כמו בבונה ההצעות ובהערכה בסוף השאלון): הרשמה ורשימת תפוצה הן תוספות, לא מסלול */
  const MOD = {
    reg: { price: 700, label: T('טופס הרשמה מתקדם', 'An advanced sign-up form') },
    mail: { price: 900, label: T('רשימת תפוצה ו־3 תבניות מייל', 'A mailing list and 3 email templates') }
  };
  const plan = ({ kind, acts, upd, en }) => {
    let track = 'site';
    if (kind === 'store' || acts.includes('pay')) track = 'store';
    else if (acts.includes('read')) track = 'club';   // מדור תוכן שמתעדכן = אתר תדמית מורחב
    else if (upd === 'none' && !acts.includes('register')) track = 'landing';
    const mods = [];
    if (acts.includes('register') && track !== 'store') mods.push(MOD.reg);
    if (upd === 'team') mods.push(MOD.mail);

    let pages;
    const feats = [];
    if (track === 'landing') pages = [T('עמוד אחד: פתיחה, מה אתם מציעים, אודות ויצירת קשר', 'One page: intro, what you offer, about and contact')];
    else if (track === 'store') pages = T(['דף הבית', 'קטגוריות', 'עמוד לכל מוצר', 'עגלה ותשלום', 'משלוחים והחזרות', 'תקנון ופרטיות', 'אודות', 'יצירת קשר'], ['Home', 'Categories', 'A page per product', 'Cart & checkout', 'Shipping & returns', 'Terms & privacy', 'About', 'Contact']);
    else {
      pages = [T('דף הבית', 'Home'), ...KIND[kind].pages];
      acts.forEach((a) => ACT[a].page && pages.push(ACT[a].page));
      pages.push(T('אודות', 'About'), T('יצירת קשר', 'Contact'));
    }
    acts.forEach((a) => { if (!(a === 'register' && mods.includes(MOD.reg))) feats.push(ACT[a].feat); });
    if (track === 'store') feats.push(T('ניהול מוצרים, מלאי והזמנות', 'Product, stock and order management'));
    else if (track !== 'landing') feats.push(T('מסך עדכון לתוכן שמשתנה', 'An update screen for content that changes'));
    mods.forEach((m) => feats.push(`${m.label} (${nis(m.price)})`));
    if (en) feats.push(T('גרסה באנגלית עם מעבר בלחיצה', 'A second language with a one-click switch'));
    feats.push(T('התאמה לגוגל ולטלפון', 'Optimized for Google and mobile'), T('הצהרת נגישות', 'Accessibility statement'));

    const p = PKG[track];
    const extraPages = (track === 'site' || track === 'club') ? Math.max(0, pages.length - p.quota) : 0;
    const price = p.price + (en ? p.en : 0) + extraPages * CONFIG.extraPagePrice + mods.reduce((t, m) => t + m.price, 0);
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
    T(`במה העסק עוסק: ${KIND[ans.kind].label}`, `Business type: ${KIND[ans.kind].label}`),
    T(`מה אנשים צריכים לעשות: ${ans.acts.length ? ans.acts.map((a) => ACT[a].label).join(', ') : 'לא נבחר'}`, `What visitors need to do: ${ans.acts.length ? ans.acts.map((a) => ACT[a].label).join(', ') : 'not chosen'}`),
    T(`מה ישתנה באתר: ${UPD[ans.upd]}`, `What will change on the site: ${UPD[ans.upd]}`),
    ans.en ? T('צריך גם גרסה באנגלית', 'Needs a second language too') : null
  ].filter(Boolean).join('\n');

  const render = () => {
    const ans = read();
    const p = plan(ans);
    last = { ans, p };
    $('r-track').textContent = PKG[p.track].name;
    $('r-why').textContent = TRACK[p.track];
    const rq = $('r-q');   // הכפתור לשאלון: המסלול שיצא כבר מסומן בשאלון
    if (rq) rq.href = T('/questionnaire/', '/en/questionnaire/') + '?package=' + p.track + '&src=calc';
    if (!CONFIG.showPrice) $('r-price-wrap').hidden = true; else setPrice(p.price);
    const extra = $('r-extra');
    extra.hidden = !p.extraPages;
    extra.textContent = p.extraPages ? extraText(p.extraPages) : '';
    $('r-count').textContent = p.track === 'landing' ? T('עמוד אחד', '1 page') : T(`${p.pages.length} עמודים`, `${p.pages.length} pages`);
    fill($('r-pages'), p.pages, 'pages');
    fill($('r-feats'), p.feats, 'feats');
    $('r-live').textContent = T(`המסלול שמתאים לכם: ${PKG[p.track].name}. מחיר משוער ${nis(p.price)}.`, `Your best fit: ${PKG[p.track].name}. Estimated price ${nis(p.price)}.`);
    $('pb-track').textContent = PKG[p.track].name;
    $('pb-price').textContent = nis(p.price);
    if (touched) {
      /* אחרי שענו: התכנית מצטרפת לכל טופס באתר, והמסלול המומלץ מסומן ברשימת המסלולים */
      Selection.set({ source: 'planner', track: p.track, en: ans.en, extraPages: p.extraPages, answers: answersText(ans), pages: p.pages, feats: p.feats }, true);
      document.dispatchEvent(new CustomEvent('yyg:recommend', { detail: { track: p.track } }));
    }
  };

  $('r-more').addEventListener('click', (e) => {
    e.preventDefault();
    const card = PKG[last.p.track].el;
    card.scrollIntoView({ behavior: motionOn() ? 'smooth' : 'auto', block: 'center', inline: 'center' });
    card.classList.remove('is-flash'); void card.offsetWidth; card.classList.add('is-flash');
    card.classList.add('is-open');
    const tg = card.querySelector('.pkg-toggle');
    if (tg) { tg.setAttribute('aria-expanded', 'true'); tg.textContent = T('פחות פרטים', 'Fewer details'); }
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

  form.addEventListener('change', (e) => {
    if (e.target.name === 'kind' && e.target.value === 'store') { const pay = form.querySelector('input[name="act"][value="pay"]'); if (pay) pay.checked = true; }
    if (!touched) countHit('planner'); touched = true; render();
  });
  render();
  return { touched: () => touched, en: () => last.ans.en };
})();

/* ===== מסלולים: בחירה מכפתור או מהכרטיס, וממשיכים ליצירת קשר ===== */
(() => {
  const cards = Object.values(PKG).map((p) => p.el);
  const grid = document.querySelector('.pkgs');
  const mark = (track, fromPlanner) => {
    cards.forEach((c) => {
      const on = c.dataset.track === track;
      const btn = c.querySelector('.pkg-pick');
      c.classList.toggle('is-selected', on);
      c.classList.toggle('is-match', on && fromPlanner);
      c.dataset.match = on && fromPlanner ? T('מתאים לכם לפי המחשבון', 'Your match from the calculator') : '';
      btn.setAttribute('aria-pressed', String(on));
      btn.classList.toggle('btn-gold', on);          // הזהב עובר לכפתור של מה שנבחר
      btn.classList.toggle('btn-line', !on);
      btn.firstChild.textContent = !on ? T('לבחור במסלול הזה', 'Choose this package') : fromPlanner ? T('להמשיך עם המסלול הזה', 'Continue with this package') : T('נבחר, ממשיכים ✓', 'Selected, continue ✓');
    });
  };
  /* בחירת מסלול: ישר לשאלון האפיון, כשהמסלול כבר מסומן בו */
  const pick = (track) => {
    countHit('pick');
    mark(track, false);
    location.href = T('/questionnaire/', '/en/questionnaire/') + '?package=' + encodeURIComponent(track) + '&src=pkg';
  };
  document.addEventListener('yyg:recommend', (e) => {
    mark(e.detail.track, true);
    /* בטלפון המסלולים נגללים לצדדים: מזיזים את הרשימה (לא את הדף) אל המסלול שהומלץ */
    const card = PKG[e.detail.track].el;
    if (grid.scrollWidth > grid.clientWidth) grid.scrollLeft = card.offsetLeft - (grid.clientWidth - card.offsetWidth) / 2;
  });
  cards.forEach((c) => {
    c.addEventListener('click', (e) => {
      if (e.target.closest('a, .pkg-toggle')) return;
      if (!finePointer && !e.target.closest('.pkg-pick')) return;   // בטלפון: נגיעה בכרטיס לא קופצת
      if (String(window.getSelection && window.getSelection()).length) return;   // מסמנים טקסט, לא בוחרים
      pick(c.dataset.track);
    });
    const t = c.querySelector('.pkg-toggle');
    if (t) t.addEventListener('click', () => {
      const open = !c.classList.contains('is-open');
      c.classList.toggle('is-open', open);
      t.setAttribute('aria-expanded', String(open));
      t.textContent = open ? T('פחות פרטים', 'Fewer details') : T('כל הפרטים', 'All details');
    });
  });
  /* בטלפון המסלולים נגללים לצדדים: נפתחים על המסלול המומלץ, ואפשר לגלול אותם במקלדת */
  const small = window.matchMedia('(max-width: 700px)');
  const syncGrid = () => {
    if (small.matches) {
      grid.setAttribute('tabindex', '0');
      const rec = grid.querySelector('.pkg.is-rec');
      if (rec) grid.scrollLeft = rec.offsetLeft - (grid.clientWidth - rec.offsetWidth) / 2;
    } else grid.removeAttribute('tabindex');
  };
  syncGrid();
  small.addEventListener('change', syncGrid);
})();

/* ===== מה כדאי להכין: רשימה שנשמרת אצל הגולש ומצורפת לפנייה ===== */
(() => {
  const boxes = [...document.querySelectorAll('.prep-item input')];
  if (!boxes.length) return;
  const KEY = 'yyg-prep';
  const count = $('prep-count'), bar = $('prep-bar'), ready = $('lead-ready'), attached = $('prep-attached');
  const words = (n, all) => n === 0 ? T('אפשר להתחיל גם מאפס.', 'Starting from zero is fine too.') : n === all ? T('מוכנים מצוין לשיחה.', 'You’re fully ready to talk.') : n >= all / 2 ? T('אתם כבר בדרך.', 'You’re well on your way.') : T('התחלה טובה.', 'Good start.');
  let saved = [];
  try { saved = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { saved = []; }
  boxes.forEach((b) => { b.checked = saved.includes(b.value); });
  const sync = (store) => {
    const on = boxes.filter((b) => b.checked);
    const n = on.length, all = boxes.length;
    count.textContent = T(`סימנתם ${n} מתוך ${all}. ${words(n, all)}`, `${n} of ${all} ready. ${words(n, all)}`);
    bar.style.width = `${(n / all) * 100}%`;
    const list = on.map((b) => b.dataset.label);
    const missing = boxes.filter((b) => !b.checked).map((b) => b.dataset.label);
    ready.value = n ? T(`מה כבר מוכן אצלם (${n} מתוך ${all}):`, `Already prepared (${n} of ${all}):`) + `\n• ${list.join('\n• ')}` + (missing.length ? T('\nעוד לא:', '\nNot yet:') + `\n• ${missing.join('\n• ')}` : '') : '';
    attached.hidden = !n;
    attached.textContent = n ? T(`יצורף לפנייה: מה כבר מוכן אצלכם (${n} מתוך ${all})`, `Will be attached to your message: what you’ve prepared (${n} of ${all})`) : '';
    if (store) { try { localStorage.setItem(KEY, JSON.stringify(on.map((b) => b.value))); } catch (e) { /* בלי שמירה */ } }
  };
  boxes.forEach((b) => b.addEventListener('change', () => sync(true)));
  sync(false);
})();

/* ===== דף נחיתה בסיסי ומחיר השקה: הכפתורים מסמנים את הבקשה בטופס ===== */
(() => {
  const basic = $('basic-cta');
  if (basic) basic.addEventListener('click', () => {
    $('lead-plan').value = T('בקשה: דף נחיתה בסיסי, 1,490 ₪ (עיצוב אחד, סבב תיקונים אחד, הטקסטים מהלקוח, חודש ליווי, 50% בהתחלה ו־50% בסיום)', 'Request: Basic landing page, ₪1,490 (one design, one revision round, client provides the text, one month of support, 50% upfront and 50% at launch)');
    countHit('pick');
  });
  const launch = $('launch-cta');
  if (launch) launch.addEventListener('click', () => {
    const notes = document.querySelector('#lead-form [name=notes]');
    const line = T('אני מעוניין/ת במחיר ההשקה (10% הנחה תמורת הצגת האתר בעבודות והמלצה כתובה). ', 'I’m interested in the launch price (10% off in return for showing the site in your work with a written testimonial). ');
    if (notes && !notes.value.includes(line.trim())) notes.value = line + notes.value;
  });
})();

/* ===== מותג מ־0: הכפתור מסמן את הבקשה בטופס ===== */
(() => {
  const cta = $('brand-cta');
  if (!cta) return;
  cta.addEventListener('click', () => {
    $('lead-plan').value = T('בקשה: מותג מ־0 עד 100% (אפיון, לוגו, שפה עיצובית, אתר, כל התוכן, דפוס, רשתות וגוגל)', 'Request: Brand from 0 to 100% (discovery, logo, brand identity, website, all content, print, social and Google)');
    const notes = document.querySelector('#lead-form [name=notes]');
    if (notes && !notes.value.trim()) notes.value = T('אני מעוניין/ת במותג מלא מ־0. ', 'I’m interested in a full brand from scratch. ');
    countHit('pick');
  });
})();

/* ===== רשימת תפוצה: הצטרפות בלי לעזוב את הדף. אחרי זה מגיע מייל עם קישור אישור ===== */
(() => {
  const form = $('news-form');
  if (!form) return;
  const status = form.querySelector('.news-status');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = form.elements.email.value.trim();
    status.className = 'news-status';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { status.textContent = T('נא לכתוב כתובת מייל תקינה.', 'Please enter a valid email address.'); form.elements.email.focus(); return; }
    if (!form.elements.consent.checked) { status.textContent = T('כדי להצטרף צריך לסמן את ההסכמה לקבל מיילים.', 'Please tick the consent box to join.'); form.elements.consent.focus(); return; }
    const btn = form.querySelector('button'); btn.disabled = true;
    status.textContent = T('שולח…', 'Sending…');
    try {
      const res = await fetch('/account/list.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, consent: 1, source: form.elements.source.value, lang: EN ? 'en' : 'he', website: form.elements.website.value }) });
      const j = await res.json();
      status.textContent = j.message || '';
      if (String(j.success) === 'true') { status.className = 'news-status is-ok'; form.classList.add('is-sent'); }
    } catch (err) {
      status.textContent = T('לא הצלחנו לשלוח מכאן. אפשר לנסות שוב בעוד רגע.', 'We couldn’t send this. Please try again in a moment.');
    } finally { btn.disabled = false; }
  });
})();

/* ===== פנייה מהירה: שם ומייל (טלפון לא חובה), למי שכבר השתכנע ולא רוצה לגלול עד הסוף =====
   אחרי השליחה הלקוח מקבל מיד מייל אישי עם קישור לשאלון, ואת הטלפון שלי רק אחרי שאישר את המייל */
const validEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);
const qLink = (d) => (EN ? '/en/questionnaire/?' : '/questionnaire/?') + new URLSearchParams({ name: d.name, email: d.email, phone: d.phone || '', business: d.business || '' }).toString();
const sentMsg = (status, d) => {
  status.textContent = T(`תודה, ${d.name}. שלחתי לכם עכשיו מייל עם הצעד הבא: שאלון קצר, ואחריו הצעת מחיר מסודרת. `, `Thank you, ${d.name}. I just emailed you the next step: a short questionnaire, then a formal quote. `);
  status.append(Object.assign(document.createElement('a'), { className: 'text-link', href: qLink(d), textContent: T('אפשר גם להתחיל את השאלון כבר עכשיו', 'You can also start the questionnaire right now') }));
};
(() => {
  document.querySelectorAll('form.quick-lead').forEach((form) => {
    const status = form.querySelector('.ql-status');
    const btn = form.querySelector('button[type=submit]');
    const { name: fName, email: fEmail, phone: fPhone } = form.elements;
    let token = '';
    const getToken = () => fetch(CONFIG.formEndpoint + '?token=1', { cache: 'no-store' }).then((r) => r.json()).then((t) => { token = t.token || ''; }).catch(() => {});
    if ('IntersectionObserver' in window) {
      const io = new IntersectionObserver((es) => { if (es.some((e) => e.isIntersecting)) { getToken(); io.disconnect(); } }, { rootMargin: '600px 0px' });
      io.observe(form);
    } else getToken();
    form.addEventListener('focusin', () => { if (!token) getToken(); }, { once: true });
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const name = fName.value.trim();
      const email = fEmail.value.trim();
      const phone = fPhone.value.trim();
      const okEmail = validEmail(email);
      const okPhone = !phone || phone.replace(/\D/g, '').length >= 9;
      fName.setAttribute('aria-invalid', String(!name));
      fEmail.setAttribute('aria-invalid', String(!okEmail));
      fPhone.setAttribute('aria-invalid', String(!okPhone));
      status.className = 'ql-status';
      if (!name) { status.textContent = T('נא לכתוב שם.', 'Please enter your name.'); fName.focus(); return; }
      if (!okEmail) { status.textContent = T('נא לכתוב כתובת מייל תקינה, למשל name@gmail.com.', 'Please enter a valid email address, e.g. name@gmail.com.'); fEmail.focus(); return; }
      if (!okPhone) { status.textContent = T('הטלפון לא חובה, אבל אם כותבים, צריך מספר מלא, למשל 050-1234567.', 'Phone is optional, but if you add one, it needs to be a full number, e.g. 050-1234567.'); fPhone.focus(); return; }
      if (form.elements._honey.value) return;
      const picked = $('lead-plan') ? $('lead-plan').value : '';
      const data = {
        name, email, phone,
        when: T('בימים א׳ עד ד׳, בין 10:00 ל־13:30', 'Sun to Wed, 10:00 to 13:30'),
        plan: form.elements.plan.value + '\n' + (picked || T('לא נבחר מסלול', 'No package chosen')) + (EN ? '\n(Sent from the English site)' : ''),
        _token: token
      };
      if (!token) { await getToken(); data._token = token; }
      btn.disabled = true;
      status.textContent = T('שולח…', 'Sending…');
      try {
        const res = await fetch(CONFIG.formEndpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(data) });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || String(json.success) !== 'true') throw new Error(json.message || res.status);
        form.reset();
        form.classList.add('is-sent');
        status.className = 'ql-status is-ok';
        sentMsg(status, data);
        countHit('lead');
      } catch (err) {
        status.textContent = '';
        status.append(T('לא הצלחנו לשלוח מכאן. אפשר להתקשר ל־', 'We couldn’t send this from here. You can call '), Object.assign(document.createElement('a'), { className: 'text-link', href: telHref, textContent: CONFIG.phone }), '.');
      } finally {
        btn.disabled = false;
      }
    });
  });
})();

/* ===== יצירת קשר: טופס שחוזר אליכם ===== */
(() => {
  const form = $('lead-form');
  if (!form) return;
  const status = $('lead-status');
  const submit = $('lead-submit');
  const fields = { name: form.elements.name, email: form.elements.email, phone: form.elements.phone };
  /* הגנה מרובוטים: חותמת זמן חתומה מהשרת. רובוט ששולח ישר לשרת, בלי לטעון את הדף, לא מקבל אותה */
  let token = '';
  const getToken = () => fetch(CONFIG.formEndpoint + '?token=1', { cache: 'no-store' }).then((r) => r.json()).then((t) => { token = t.token || ''; }).catch(() => {});
  if ('IntersectionObserver' in window) {
    const tio = new IntersectionObserver((es) => { if (es.some((e) => e.isIntersecting)) { getToken(); tio.disconnect(); } }, { rootMargin: '600px 0px' });
    tio.observe(form);
  } else getToken();
  form.addEventListener('focusin', () => { if (!token) getToken(); }, { once: true });
  const validPhone = (v) => !v || v.replace(/\D/g, '').length >= 9;
  const setErr = (name, msg) => {
    const f = fields[name];
    f.setAttribute('aria-invalid', String(!!msg));
    $('err-' + name).textContent = msg || '';
  };
  const fallback = (data) => {
    const body = T(`שלום יהודה,\n\nשם: ${data.name}\nמייל: ${data.email}\nטלפון: ${data.phone}\nשם העסק: ${data.business}\nמתי נוח שתתקשר: ${data.when}\nרקע מועדף: ${data.style}\nאתרים שאני אוהב: ${data.likes}\nהערות: ${data.notes}\n\n${data.ready}\n\n${data.plan}`,
      `Hi Yehuda,\n\nName: ${data.name}\nEmail: ${data.email}\nPhone: ${data.phone}\nBusiness: ${data.business}\nBest time to call: ${data.when}\nPreferred background: ${data.style}\nSites I like: ${data.likes}\nNotes: ${data.notes}\n\n${data.ready}\n\n${data.plan}`);
    status.textContent = '';
    status.append(T('לא הצלחנו לשלוח מכאן. אפשר להתקשר ל־', 'We couldn’t send this from here. You can call '), Object.assign(document.createElement('a'), { className: 'text-link', href: telHref, textContent: CONFIG.phone }),
      T(', או ', ', '), Object.assign(document.createElement('a'), { className: 'text-link', href: mailHref(T(`פנייה מהאתר: ${data.name}`, `Website inquiry: ${data.name}`), body), textContent: T('לשלוח את הפרטים במייל', 'email the details') }),
      T(' או ', ' or use '), Object.assign(document.createElement('a'), { className: 'text-link', href: gmailHref(T(`פנייה מהאתר: ${data.name}`, `Website inquiry: ${data.name}`), body), target: '_blank', rel: 'noopener', textContent: T('ב־Gmail', 'Gmail') }), '.');
  };
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = fields.name.value.trim();
    const email = fields.email.value.trim();
    const phone = fields.phone.value.trim();
    setErr('name', name ? '' : T('נא לכתוב שם.', 'Please enter your name.'));
    setErr('email', validEmail(email) ? '' : T('נא לכתוב כתובת מייל תקינה, למשל name@gmail.com.', 'Please enter a valid email address, e.g. name@gmail.com.'));
    setErr('phone', validPhone(phone) ? '' : T('הטלפון לא חובה, אבל אם כותבים, צריך מספר מלא, למשל 050-1234567.', 'Phone is optional, but if you add one, it needs to be a full number, e.g. 050-1234567.'));
    if (!name) { status.textContent = ''; fields.name.focus(); return; }
    if (!validEmail(email)) { status.textContent = ''; fields.email.focus(); return; }
    if (!validPhone(phone)) { status.textContent = ''; fields.phone.focus(); return; }
    if (form.elements._honey.value) return;
    const data = {
      name, email, phone,
      business: form.elements.business.value.trim(),
      when: form.elements.when.value,
      style: form.elements.style.value,
      likes: form.elements.likes.value.trim(),
      notes: form.elements.notes.value.trim(),
      ready: $('lead-ready').value || T('לא סימנו ברשימת ההכנה', 'Prep checklist not filled in'),
      plan: ($('lead-plan').value || T('לא נבחר מסלול', 'No package chosen')) + (EN ? '\n(Sent from the English site)' : ''),
      _token: token
    };
    if (!token) { await getToken(); data._token = token; }
    submit.disabled = true;
    status.className = 'lead-status';
    status.textContent = T('שולח…', 'Sending…');
    try {
      const res = await fetch(CONFIG.formEndpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(data) });
      const json = await res.json().catch(() => ({}));
      if (!res.ok || String(json.success) !== 'true') throw new Error(json.message || res.status);
      const keepReady = $('lead-ready').value;
      form.reset();
      $('lead-ready').value = keepReady;
      countHit('lead');
      status.className = 'lead-status is-ok';
      sentMsg(status, data);
    } catch (err) {
      fallback(data);
    } finally {
      submit.disabled = false;
    }
  });

  /* העתקת כתובת המייל */
  const copy = $('copy-mail');
  if (copy) copy.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(CONFIG.email); copy.textContent = T('הועתק', 'Copied'); setTimeout(() => { copy.textContent = T('העתקה', 'Copy'); }, 1800); }
    catch (e) { copy.textContent = CONFIG.email; }
  });

  /* ברק אחד על כפתור השליחה כשמגיעים לחלק (תפקיד: להוביל את העין לפעולה) */
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((es) => { if (es.some((e) => e.isIntersecting) && motionOn()) { submit.classList.add('is-cue'); io.disconnect(); } }, { threshold: 0.6 });
    io.observe(submit);
  }
})();

/* ===== הכפתור "להצעת מחיר" בכותרת: תמיד גלוי. כשכפתור זהוב אחר נראה במסך הוא מסגרת בלבד, וכשאין, הוא זהוב (זהב אחד בכל מסך) ===== */
(() => {
  if (!document.querySelector('.top-q') || !('IntersectionObserver' in window)) return;
  const root = document.documentElement;
  root.classList.add('has-hero-cta');
  const shown = new Set();
  const io = new IntersectionObserver((es) => {
    es.forEach((e) => (e.isIntersecting ? shown.add(e.target) : shown.delete(e.target)));
    root.classList.toggle('hero-cta-gone', shown.size === 0);
  });
  document.querySelectorAll('main .btn-gold').forEach((b) => io.observe(b));
})();

/* ===== מחובר? בכותרת: "האזור האישי" במקום "כניסה", ולבעל האתר גם קישור "ניהול".
   העוגיות כאן הן רק רמז לתצוגה. ההרשאה נבדקת בשרת, בכל דף של האזור האישי והניהול ===== */
(() => {
  const acc = document.querySelector('.acc-link');
  if (!acc) return;
  const has = (n) => document.cookie.split('; ').includes(n + '=1');
  const en = document.documentElement.lang === 'en';
  if (has('yyg_in')) acc.textContent = en ? 'My account' : 'האזור האישי';
  if (has('yyg_adm')) {
    const a = document.createElement('a');
    a.className = 'text-link acc-link adm-link';
    a.href = '/admin/';
    a.lang = 'he';
    a.textContent = 'ניהול';
    acc.after(a);
  }
})();
