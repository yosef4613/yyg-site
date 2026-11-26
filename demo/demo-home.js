/* YYG Web Design: הכפתור הצף "חזרה לאתר" בכל עמודי הדוגמה (נוסף על ידי tools/demo_bar.py).
   1. מי שהגיע לדוגמה מהאתר באנגלית חוזר לאתר באנגלית. נזכר לכל הביקור, גם במעבר בין עיצובים.
   2. הכפתור עולה מעל פסים וכפתורים קבועים בתחתית המסך (חיוג, תרומה, עגלה), כדי לא להסתיר אותם. */
(() => {
  const a = document.querySelector('.demo-home');
  if (!a) return;

  try {
    const r = document.referrer ? new URL(document.referrer) : null;
    if (r && r.origin === location.origin && !r.pathname.startsWith('/demo/')) {
      sessionStorage.setItem('yygLang', r.pathname.startsWith('/en/') ? 'en' : 'he');
    }
    if (sessionStorage.getItem('yygLang') === 'en') {
      a.href = '/en/';
      a.lang = 'en';
      a.dir = 'ltr';
      a.querySelector('.demo-home-l').textContent = 'Back to YYG Web Design';
      a.querySelector('.demo-home-s').textContent = 'Back to YYG Web Design';
    }
  } catch (e) { /* בלי sessionStorage: נשאר בעברית */ }

  /* אלמנטים קבועים בעמוד (חוץ מהכפתור והתווית). נסרקים מחדש מדי פעם, כי חלקם מופיעים רק אחרי גלילה */
  let fixed = [];
  const scan = () => {
    fixed = [...document.body.querySelectorAll('*')].filter((el) =>
      el !== a && !a.contains(el) && !el.classList.contains('demo-ribbon') && getComputedStyle(el).position === 'fixed');
  };
  let lift = -1;
  const place = () => {
    const h = innerHeight;
    const me = a.getBoundingClientRect();
    let need = 0;
    for (const el of fixed) {
      const b = el.getBoundingClientRect();
      if (!b.width || !b.height || b.top < h * 0.5 || b.top >= h) continue;
      if (b.right < me.left - 8 || b.left > me.right + 8) continue;
      const st = getComputedStyle(el);
      if (st.visibility === 'hidden' || st.display === 'none' || Number(st.opacity) < 0.1) continue;
      need = Math.max(need, Math.ceil(h - b.top));
    }
    if (need !== lift) { lift = need; a.style.setProperty('--lift', need + 'px'); }
  };

  let raf = 0;
  const soon = () => { if (!raf) raf = requestAnimationFrame(() => { raf = 0; place(); }); };
  let rescan = 0;
  const later = () => { clearTimeout(rescan); rescan = setTimeout(() => { scan(); place(); }, 250); };

  scan();
  place();
  addEventListener('scroll', () => { soon(); later(); }, { passive: true });
  addEventListener('resize', () => { scan(); soon(); });
  addEventListener('click', later, true);
  setTimeout(() => { scan(); place(); }, 1200);
})();
