/* ענף (עסק לדוגמה): התוכן שבעל העסק מעדכן בעצמו, כלומר העבודות בגלריה והודעה קצרה בראש האתר.
   ברירת המחדל (6 עבודות) כתובה כאן. מה שמשנים במסך הניהול לדוגמה (/demo/admin/) נשמר רק בדפדפן הזה,
   כדי שכל אחד יוכל לנסות בלי לקלקל לאחרים. באתר אמיתי השינוי נשמר בשרת, והוא מופיע מיד לכל הגולשים.
   שימוש בעיצוב: מוסיפים אלמנטים עם התכונות האלה, והסקריפט ממלא אותם:
     data-content-gallery  הגלריה: נבנות בו <figure class="g-item ..."> עם תמונה, מספר וכיתוב.
                           לכל פריט גם צורה למחשב (g-tall / g-wide / g-side / g-full) ולטלפון (g-mfull),
                           כך שהגלריה נראית טוב בכל מספר עבודות, מ־1 עד 12
     data-content-notice   ההודעה הקצרה. מוסתר (hidden) כשאין הודעה
   כל הטקסטים נכנסים עם textContent, בלי innerHTML, כך שאי אפשר "להזריק" קוד דרך מסך הניהול. */
(() => {
  const KEY = 'anaf-content-v1';
  const LIMIT = 12;
  const IMG = '/demo/img/';
  const p = (id, title, kind, wood, text, file, w, h, alt, pos) => ({
    id, title, kind, wood, text, img: `${IMG}${file}-960.webp`, img_small: `${IMG}${file}-560.webp`, alt, w, h, pos: pos || '',
  });
  const DEFAULT = {
    notice: '',
    projects: [
      p('p-wardrobe', 'ארון קיר עד התקרה, מודיעין עילית', 'ארון', 'אשור',
        'ארון בהיר עד התקרה, עם מקום לתלייה ומדפים לנעליים של כל המשפחה.',
        'wardrobe', 960, 1280, 'ארון קיר בהיר עד התקרה, עם דלת פתוחה, בגדים תלויים ומדפים לנעליים'),
      p('p-drawer', 'מגירת סכו״ם מאלון, מטבח ברמת בית שמש', 'מטבח', 'אלון',
        'מחיצות עץ שנבנו לפי הכלים של הבית, כולל מגירה נפרדת לסכו״ם של שבת.',
        'kitchen-drawer', 960, 641, 'מגירת סכו״ם פתוחה מעץ אלון, עם מחיצות עץ ובהן כפות ומזלגות', '78% 50%'),
      p('p-bench', 'שולחן אוכל וספסל מעץ מלא, בית שמש', 'שולחן', 'אלון',
        'שולחן שנפתח למשפחה גדולה, עם ספסל תואם לאורך החלונות.',
        'table-bench', 960, 640, 'שולחן אוכל מעץ מלא עם ספסל עץ לאורכו, ליד חלונות גבוהים'),
      p('p-library', 'ספריות מקיר לקיר בסלון, ירושלים', 'ספרייה', 'אשור צבוע',
        'ספריות לבנות משני צדי האח, עם מדפים עמוקים לספרי קודש.',
        'library', 960, 540, 'סלון עם ספריות לבנות מקיר לקיר משני צדי האח'),
      p('p-kids', 'חדר ילדים עם מיטה ושולחן כתיבה, ירושלים', 'חדר ילדים', 'אורן',
        'מיטה עם מגירות אחסון, ושולחן כתיבה אחד לאורך כל הקיר.',
        'kids-room', 960, 540, 'חדר ילדים עם מיטה, מגירות מתחת למיטה ושולחן כתיבה לאורך הקיר', '40% 60%'),
      p('p-shelves', 'ספרייה לבית כנסת, רמות', 'בית כנסת', 'אגוז',
        'מדפים עמוקים וחזקים, שלא מתכופפים גם תחת סטים מלאים של ספרים.',
        'shelves', 960, 569, 'מדפי ספרייה כהים עמוסים בספרים, באור חם', '30% 50%'),
    ],
  };

  const clone = (o) => JSON.parse(JSON.stringify(o));
  const str = (v, max) => (typeof v === 'string' ? v.replace(/\s+/g, ' ').trim().slice(0, max) : '');
  /* רק תמונות מתיקיית התמונות של הדוגמה, או תמונה שהועלתה (data:) */
  const okImg = (v) => typeof v === 'string' && (/^\/demo\/img\/[\w-]+\.(?:webp|jpg|png)$/.test(v) || /^data:image\/(?:webp|jpeg|png);base64,[A-Za-z0-9+/=]+$/.test(v));
  const num = (v, d) => (Number.isFinite(v) && v > 0 && v <= 6000 ? Math.round(v) : d);
  let seq = 0;
  const newId = () => `p-${Date.now().toString(36)}-${(seq++).toString(36)}`;

  /* בודק נתונים שנשמרו, ומחזיר רק מה שתקין */
  const clean = (d) => {
    if (!d || typeof d !== 'object' || !Array.isArray(d.projects)) return null;
    const seen = new Set();
    const projects = d.projects.filter((x) => x && typeof x === 'object' && okImg(x.img) && str(x.title, 90)).slice(0, LIMIT).map((x) => {
      let id = typeof x.id === 'string' && /^[\w-]{1,40}$/.test(x.id) ? x.id : newId();
      if (seen.has(id)) id = newId();
      seen.add(id);
      return {
        id,
        title: str(x.title, 90),
        kind: str(x.kind, 30),
        wood: str(x.wood, 30),
        text: str(x.text, 180),
        img: x.img,
        img_small: okImg(x.img_small) ? x.img_small : '',
        alt: str(x.alt, 180),
        w: num(x.w, 1200),
        h: num(x.h, 900),
        pos: typeof x.pos === 'string' && /^\d{1,3}% \d{1,3}%$/.test(x.pos) ? x.pos : '',
      };
    });
    if (!projects.length) return null;
    return { notice: str(d.notice, 140), projects, updated: Number(d.updated) || 0 };
  };

  const load = () => {
    try { const d = clean(JSON.parse(localStorage.getItem(KEY) || 'null')); if (d) return d; } catch (e) { /* בלי שמירה */ }
    return clone(DEFAULT);
  };
  /* מחזיר true אם נשמר. אם הדפדפן מלא (תמונות גדולות) או חוסם שמירה, מחזיר false ו־lastError מסביר */
  const api = { lastError: '' };
  const save = (d) => {
    const c = clean(d);
    if (!c) { api.lastError = 'invalid'; return false; }
    try {
      localStorage.setItem(KEY, JSON.stringify(Object.assign(c, { updated: Date.now() })));
    } catch (e) {
      api.lastError = e && (e.name === 'QuotaExceededError' || e.code === 22 || e.code === 1014) ? 'quota' : 'blocked';
      return false;
    }
    api.lastError = '';
    render();
    return true;
  };
  const reset = () => { try { localStorage.removeItem(KEY); } catch (e) { /* */ } render(); };

  /* צורת כל פריט. במחשב: בלוקים של 6 כמו בעיצוב 1 (גבוה ורחב, שניים קטנים, ושורה של 5+7), והבלוק הבא הפוך.
     בלוק חלקי (פחות מ־6) מקבל סידור משלו, כדי שלא יישארו חורים. בטלפון: חצי, חצי, שלם */
  const DESK = { 1: ['full'], 2: ['h5', 'h7'], 3: ['t4', 't4', 't4'], 4: ['tall', 'wide', 's3', 's4'], 5: ['tall', 'wide', 's3', 's4', 'full'], 6: ['tall', 'wide', 's3', 's4', 'h5', 'h7'] };
  const FLIP = { s3: 's4', s4: 's3', h5: 'h7', h7: 'h5' };
  const SIZES = { tall: 520, wide: 720, s3: 300, s4: 400, h5: 520, h7: 720, t4: 400, full: 1240 };
  const shapes = (n) => {
    const out = [];
    for (let i = 0, b = 0; i < n; i += 6, b++) {
      DESK[Math.min(6, n - i)].forEach((d) => out.push({ d: b % 2 ? (FLIP[d] || d) : d, flip: b % 2 === 1 }));
    }
    return out.map((s, i) => {
      const inThree = i % 3, rest = n - (i - inThree);
      return Object.assign(s, { mfull: inThree === 2 || (rest === 1 && inThree === 0) });
    });
  };

  const el = (tag, cls, text) => {
    const x = document.createElement(tag);
    if (cls) x.className = cls;
    if (text) x.textContent = text;
    return x;
  };
  const figure = (x, i, shape) => {
    const fig = el('figure', `g-item g-${shape.d}${shape.flip && shape.d === 'tall' ? ' g-flip' : ''}${shape.mfull ? ' g-mfull' : ''} reveal`);
    if (i % 2) fig.style.setProperty('--d', '1');
    const ph = el('div', 'ph');
    const img = document.createElement('img');
    img.src = x.img;
    if (x.img_small) {
      img.srcset = `${x.img_small} 560w, ${x.img} 960w`;
      img.sizes = `(min-width:1024px) ${SIZES[shape.d]}px, ${shape.mfull ? '100vw' : '50vw'}`;
    }
    img.width = x.w; img.height = x.h;
    img.loading = 'lazy'; img.decoding = 'async';
    img.alt = x.alt || x.title;
    if (x.pos) img.style.objectPosition = x.pos;
    ph.append(img);
    const cap = el('figcaption');
    cap.append(el('span', 'cap-n', String(i + 1).padStart(2, '0')));
    const body = el('span', 'cap-body');
    const meta = [x.kind, x.wood].filter(Boolean).join(' · ');
    if (meta) body.append(el('span', 'cap-meta', meta));
    body.append(el('span', 'cap-title', x.title));
    if (x.text) body.append(el('span', 'cap-text', x.text));
    cap.append(body);
    fig.append(ph, cap);
    return fig;
  };

  function render() {
    const data = load();
    document.querySelectorAll('[data-content-notice]').forEach((n) => { n.textContent = data.notice; n.hidden = !data.notice; });
    const sh = shapes(data.projects.length);
    document.querySelectorAll('[data-content-gallery]').forEach((g) => {
      g.replaceChildren(...data.projects.map((x, i) => figure(x, i, sh[i])));
      g.dataset.count = String(data.projects.length);
    });
  }

  Object.assign(api, { KEY, LIMIT, DEFAULT, load, save, reset, render, clean, newId });
  window.AnafContent = api;
  /* הסקריפט נטען בסוף העמוד, אחרי הגלריה, אז ממלאים מיד (לפני אנימציית הכניסה של העמוד) */
  if (document.querySelector('[data-content-gallery],[data-content-notice]') || document.readyState !== 'loading') render();
  else document.addEventListener('DOMContentLoaded', render, { once: true });
  /* שינוי במסך הניהול בלשונית אחרת מתעדכן כאן מיד */
  window.addEventListener('storage', (e) => { if (e.key === KEY || e.key === null) render(); });
})();
