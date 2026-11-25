/* YYG: מנוע השאלונים. שלבים, פס התקדמות, שמירה אוטומטית בדפדפן, סיכום לפני שליחה, ושליחה לשרת.
   התוכן עצמו נמצא ב־q-defs.js. הדף מגדיר <div id="qf" data-form="intake|feedback">. */
(() => {
  const root = document.getElementById('qf');
  if (!root || !window.YYG_FORMS) return;
  const EN = document.documentElement.lang === 'en';
  const t = (he, en) => (EN ? en : he);
  const FORM = root.dataset.form;
  const def = window.YYG_FORMS[FORM](t);
  const KEY = 'yyg-q-' + FORM + (EN ? '-en' : '');
  const OTHER = '__other';
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const steps = def.steps;
  const REVIEW = steps.length;

  let state = {};
  try { state = JSON.parse(localStorage.getItem(KEY) || '{}') || {}; } catch (e) { state = {}; }
  const params = new URLSearchParams(location.search);
  ['name', 'phone', 'email', 'business', 'quote'].forEach((k) => { if (params.get(k) && !state[k]) state[k] = params.get(k).slice(0, 120); });
  /* זמן ההתחלה נשמר עם התשובות, כדי שגם מי שחוזר אחרי הפסקה ייחשב לפי הזמן הכולל (הגנה מרובוטים בשרת) */
  if (!state.__t0) state.__t0 = Date.now();
  let step = Math.min(Number(state.__step) || 0, REVIEW);
  let visited = Math.max(step, Number(state.__visited) || 0);
  const save = () => { state.__step = step; state.__visited = visited; try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) { /* בלי שמירה */ } };

  /* קבצים מצורפים: נשמרים בזיכרון עד השליחה (אי אפשר לשמור קבצים בדפדפן) */
  const FILES = {};
  const MAX_MB = 25;
  const kb = (n) => (n >= 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB');
  const shrink = (file) => new Promise((resolve) => {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 700 * 1024) return resolve(file);
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => {
      const k = Math.min(1, 2200 / Math.max(img.naturalWidth, img.naturalHeight));
      const c = document.createElement('canvas');
      c.width = Math.round(img.naturalWidth * k); c.height = Math.round(img.naturalHeight * k);
      c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
      URL.revokeObjectURL(url);
      c.toBlob((b) => resolve(b && b.size < file.size ? new File([b], file.name.replace(/\.(png|webp)$/i, '.jpg'), { type: 'image/jpeg' }) : file), 'image/jpeg', 0.86);
    };
    img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
    img.src = url;
  });
  const fileList = (q) => (FILES[q.id] || []).map((f, i) => `<li><span>${esc(f.name)}</span> <small>${kb(f.size)}</small> <button type="button" class="qf-x" data-rm="${esc(q.id)}" data-i="${i}" aria-label="${t('הסרת', 'Remove')} ${esc(f.name)}">✕</button></li>`).join('');

  const visible = (q) => !q.show || (q.show.in || []).includes(state[q.show.id]);
  const hasAnswer = (q) => { if (q.type === 'file') return (FILES[q.id] || []).length > 0; const v = state[q.id]; return Array.isArray(v) ? v.length > 0 : v != null && String(v).trim() !== ''; };
  const qTitle = (q) => q.label || [q.prefix, q.from && q.to ? `${q.from} / ${q.to}` : ''].filter(Boolean).join(': ');
  const answerText = (q) => {
    const v = state[q.id];
    const other = (state[q.id + OTHER] || '').trim();
    if (q.type === 'checks') return (v || []).map((x) => (x === OTHER ? t('אחר: ', 'Other: ') + other : x)).join(', ');
    if (q.type === 'radio') return v === OTHER ? t('אחר: ', 'Other: ') + other : v;
    if (q.type === 'scale') return `${q.prefix ? q.prefix + ': ' : ''}${v} ${t('מתוך 5', 'of 5')} (1 = ${q.from}, 5 = ${q.to})`;
    if (q.type === 'file') return (FILES[q.id] || []).map((f) => f.name).join(', ');
    return String(v).trim();
  };

  /* ---------- שאלה אחת ---------- */
  const field = (q) => {
    const id = 'q-' + q.id;
    const hint = q.hint ? `<small class="qf-hint" id="${id}-h">${esc(q.hint)}</small>` : '';
    const showAttr = q.show ? ` data-show="${esc(q.id)}"` : '';
    const v = state[q.id] ?? '';
    if (['text', 'email', 'tel', 'url', 'date'].includes(q.type)) {
      const ltr = q.ltr || ['email', 'tel', 'url'].includes(q.type) ? ' dir="ltr"' : '';
      const im = q.type === 'tel' ? ' inputmode="tel"' : q.type === 'email' ? ' inputmode="email"' : '';
      return `<div class="qf-q"${showAttr}><label class="field" for="${id}"><span>${esc(q.label)}${q.required ? ' <b class="qf-req" aria-hidden="true">*</b>' : ''}</span>${hint}<input id="${id}" name="${esc(q.id)}" type="${q.type}"${ltr}${im}${q.auto ? ` autocomplete="${q.auto}"` : ''}${q.required ? ' required' : ''}${q.hint ? ` aria-describedby="${id}-h"` : ''} value="${esc(v)}"></label><small class="qf-err" id="${id}-e" aria-live="polite"></small></div>`;
    }
    if (q.type === 'textarea') {
      return `<div class="qf-q"${showAttr}><label class="field" for="${id}"><span>${esc(q.label)}</span>${hint}<textarea id="${id}" name="${esc(q.id)}" rows="${q.rows || 3}" dir="auto"${q.hint ? ` aria-describedby="${id}-h"` : ''}>${esc(v)}</textarea></label></div>`;
    }
    if (q.type === 'file') {
      return `<div class="qf-q"${showAttr}><label class="field qf-file" for="${id}"><span>${esc(q.label)}</span>${hint}<input id="${id}" type="file" data-file="${esc(q.id)}" accept="${esc(q.accept || '')}"${(q.max || 1) > 1 ? ' multiple' : ''}${q.hint ? ` aria-describedby="${id}-h"` : ''}></label><ul class="qf-files" id="${id}-list">${fileList(q)}</ul><small class="qf-err" id="${id}-e" aria-live="polite"></small></div>`;
    }
    if (q.type === 'radio' || q.type === 'checks') {
      const type = q.type === 'radio' ? 'radio' : 'checkbox';
      const opts = q.options.concat(q.other ? [OTHER] : []);
      const isOn = (o) => (q.type === 'radio' ? v === o : (v || []).includes(o));
      const chips = opts.map((o) => `<label><input type="${type}" name="${esc(q.id)}" value="${esc(o)}"${isOn(o) ? ' checked' : ''}><span>${o === OTHER ? t('אחר', 'Other') : esc(o)}</span></label>`).join('');
      const otherOn = q.other && isOn(OTHER);
      return `<fieldset class="qf-q"${showAttr}><legend>${esc(q.label)}</legend>${hint}<div class="chips">${chips}</div>`
        + (q.other ? `<input class="qf-other" type="text" name="${esc(q.id + OTHER)}" aria-label="${t('פירוט', 'Details')}" placeholder="${t('פרטו כאן', 'Tell me more')}" value="${esc(state[q.id + OTHER] || '')}"${otherOn ? '' : ' hidden'}>` : '')
        + (q.max ? `<small class="qf-err" aria-live="polite"></small>` : '') + '</fieldset>';
    }
    if (q.type === 'scale') {
      const legend = q.label ? esc(q.label) : `<span class="sr">${esc(qTitle(q))}</span>`;
      const chips = [1, 2, 3, 4, 5].map((n) => `<label><input type="radio" name="${esc(q.id)}" value="${n}"${String(v) === String(n) ? ' checked' : ''}><span>${n}</span></label>`).join('');
      return `<fieldset class="qf-q qf-scale${q.label ? '' : ' qf-tight'}"${showAttr}><legend>${legend}</legend>${hint}${q.prefix ? `<p class="qf-prefix">${esc(q.prefix)}</p>` : ''}<div class="qf-scale-row"><span class="qf-end">${esc(q.from)}</span><div class="chips">${chips}</div><span class="qf-end">${esc(q.to)}</span></div></fieldset>`;
    }
    return '';
  };

  /* ---------- שלב ---------- */
  const head = () => {
    const pct = Math.round((Math.min(step, REVIEW) / REVIEW) * 100);
    const names = steps.map((s, i) => `<li><button type="button" data-go="${i}" ${i > visited ? 'disabled' : ''} class="${i === step ? 'on' : ''}">${esc(s.title)}</button></li>`).join('')
      + `<li><button type="button" data-go="${REVIEW}" ${visited < REVIEW ? 'disabled' : ''} class="${step === REVIEW ? 'on' : ''}">${t('סיכום ושליחה', 'Review and send')}</button></li>`;
    return `<div class="qf-head"><p class="qf-k">${step < REVIEW ? t(`שלב ${step + 1} מתוך ${REVIEW}`, `Step ${step + 1} of ${REVIEW}`) : t('כמעט סיימנו', 'Almost done')}</p><div class="qf-bar" aria-hidden="true"><i style="width:${pct}%"></i></div><ol class="qf-steps">${names}</ol></div>`;
  };
  const render = () => {
    if (step === REVIEW) return renderReview();
    const s = steps[step];
    root.innerHTML = head() + `<form class="qf-step" novalidate><h2 class="qf-title" tabindex="-1">${esc(s.title)}</h2>${s.intro ? `<p class="qf-intro">${esc(s.intro)}</p>` : ''}`
      + s.qs.map(field).join('')
      + `<div class="qf-nav">${step > 0 ? `<button type="button" class="btn btn-line" data-prev>${t('הקודם', 'Back')}</button>` : '<span></span>'}<button type="submit" class="btn btn-gold">${step === REVIEW - 1 ? t('לסיכום', 'Review') : t('המשך', 'Next')}</button></div>`
      + `<p class="qf-saved">${t('התשובות נשמרות אצלכם בדפדפן. אפשר לסגור ולחזור.', 'Your answers are saved in your browser. You can close this and come back.')} <button type="button" class="qf-reset" data-reset>${t('להתחיל מחדש', 'Start over')}</button></p></form>`;
    syncShow();
  };
  const syncShow = () => {
    steps[step]?.qs.forEach((q) => { if (!q.show) return; const el = root.querySelector(`[data-show="${CSS.escape(q.id)}"]`); if (el) el.hidden = !visible(q); });
  };
  const renderReview = () => {
    const blocks = steps.map((s, i) => {
      const rows = s.qs.filter((q) => visible(q) && hasAnswer(q)).map((q) => `<div class="qf-row"><dt>${esc(qTitle(q))}</dt><dd>${esc(answerText(q))}</dd></div>`).join('');
      return `<section class="qf-sum"><div class="qf-sum-h"><h3>${esc(s.title)}</h3><button type="button" class="text-link as-button" data-go="${i}">${t('עריכה', 'Edit')}</button></div>${rows ? `<dl>${rows}</dl>` : `<p class="qf-empty">${t('לא מולא. זה בסדר.', 'Skipped. That\'s fine.')}</p>`}</section>`;
    }).join('');
    root.innerHTML = head() + `<div class="qf-step"><h2 class="qf-title" tabindex="-1">${t('סיכום ושליחה', 'Review and send')}</h2><p class="qf-intro">${t('כך התשובות יגיעו אליי. אפשר לחזור ולערוך כל חלק.', 'This is how your answers will reach me. You can go back and edit any part.')}</p>${blocks}`
      + `<input type="text" name="_hpq" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">`
      + `<p class="qf-privacy">${t('הפרטים משמשים רק כדי להכין לכם הצעה ולבנות את האתר.', 'Your details are used only to prepare your quote and build your site.')} <a class="text-link" href="${t('/privacy.html', '/en/privacy.html')}">${t('מדיניות פרטיות', 'Privacy policy')}</a></p>`
      + `<div class="qf-nav"><button type="button" class="btn btn-line" data-prev>${t('הקודם', 'Back')}</button><button type="button" class="btn btn-gold" data-send>${t('שליחה', 'Send')}</button></div><p class="qf-status" role="status" aria-live="polite"></p></div>`;
  };
  const go = (n) => { step = Math.max(0, Math.min(REVIEW, n)); visited = Math.max(visited, step); save(); render(); root.scrollIntoView({ behavior: 'auto', block: 'start' }); const h = root.querySelector('.qf-title'); if (h) h.focus({ preventScroll: true }); };

  /* ---------- בדיקה של שלב הפרטים ---------- */
  const validStep = () => {
    let ok = true;
    const err = (id, msg) => { const e = document.getElementById('q-' + id + '-e'); const i = document.getElementById('q-' + id); if (e) e.textContent = msg || ''; if (i) i.setAttribute('aria-invalid', msg ? 'true' : 'false'); if (msg && ok) { ok = false; if (i) i.focus(); } };
    const ids = steps[step].qs.map((q) => q.id);
    if (ids.includes('name')) err('name', (state.name || '').trim().length < 2 ? t('נא לכתוב שם.', 'Please enter your name.') : '');
    if (ids.includes('phone') && ids.includes('email')) {
      const phoneOk = (state.phone || '').replace(/\D/g, '').length >= 9;
      const mail = (state.email || '').trim();
      const mailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail);
      err('phone', !phoneOk && !mailOk ? t('נא לכתוב טלפון או מייל, כדי שאוכל לחזור אליכם.', 'Please enter a phone or an email, so I can get back to you.') : '');
      if (mail && !mailOk) err('email', t('כתובת המייל לא נראית תקינה.', 'This email address doesn\'t look right.'));
      else err('email', '');
    }
    return ok;
  };

  /* ---------- אירועים ---------- */
  root.addEventListener('change', async (e) => {
    const el = e.target;
    if (el.type !== 'file' || !el.dataset.file) return;
    const q = steps[step].qs.find((x) => x.id === el.dataset.file);
    const errEl = document.getElementById('q-' + q.id + '-e');
    const allowed = (q.accept || '').split(',').map((x) => x.trim().toLowerCase()).filter(Boolean);
    const list = FILES[q.id] || (FILES[q.id] = []);
    errEl.textContent = '';
    for (const f of [...el.files]) {
      const ext = '.' + (f.name.split('.').pop() || '').toLowerCase();
      if (allowed.length && !allowed.includes(ext)) { errEl.textContent = t(`הקובץ ${f.name} מסוג שלא מתאים כאן.`, `${f.name} isn't a supported file type here.`); continue; }
      if (list.length >= (q.max || 1)) { errEl.textContent = t(`אפשר לצרף עד ${q.max || 1} קבצים כאן.`, `You can attach up to ${q.max || 1} files here.`); break; }
      const ready = q.resize ? await shrink(f) : f;
      if (ready.size > MAX_MB * 1048576) { errEl.textContent = t(`הקובץ ${f.name} גדול מדי (עד ${MAX_MB} MB). אפשר לשלוח קישור לתיקייה.`, `${f.name} is too large (up to ${MAX_MB} MB). You can share a folder link instead.`); continue; }
      list.push(ready);
    }
    el.value = '';
    document.getElementById('q-' + q.id + '-list').innerHTML = fileList(q);
  });
  root.addEventListener('input', (e) => {
    const el = e.target;
    if (!el.name || el.type === 'file') return;
    const q = steps[step]?.qs.find((x) => x.id === el.name);
    if (el.type === 'checkbox' && q) {
      const cur = new Set(state[q.id] || []);
      if (el.checked) cur.add(el.value); else cur.delete(el.value);
      if (q.max && [...cur].filter((x) => x !== OTHER).length > q.max) { el.checked = false; cur.delete(el.value); const m = el.closest('fieldset').querySelector('.qf-err'); if (m) m.textContent = t(`אפשר לבחור עד ${q.max}.`, `You can pick up to ${q.max}.`); }
      state[q.id] = [...cur];
    } else if (el.type === 'radio') state[el.name] = el.value;
    else state[el.name] = el.value;
    if (q && q.other) { const o = root.querySelector(`input[name="${CSS.escape(q.id + OTHER)}"]`); const on = q.type === 'radio' ? state[q.id] === OTHER : (state[q.id] || []).includes(OTHER); if (o) { o.hidden = !on; if (on && e.type === 'input' && el.value === OTHER) o.focus(); } }
    save(); syncShow();
  });
  root.addEventListener('submit', (e) => { e.preventDefault(); if (validStep()) go(step + 1); });
  root.addEventListener('click', async (e) => {
    const b = e.target.closest('button');
    if (!b) return;
    if (b.hasAttribute('data-prev')) go(step - 1);
    else if (b.dataset.go != null) go(Number(b.dataset.go));
    else if (b.hasAttribute('data-reset')) { if (confirm(t('למחוק את כל התשובות ולהתחיל מחדש?', 'Clear all your answers and start over?'))) { state = { __t0: Date.now() }; step = 0; visited = 0; save(); render(); } }
    else if (b.hasAttribute('data-send')) send(b);
    else if (b.dataset.rm) { const q = steps[step].qs.find((x) => x.id === b.dataset.rm); FILES[q.id].splice(Number(b.dataset.i), 1); document.getElementById('q-' + q.id + '-list').innerHTML = fileList(q); }
  });

  /* ---------- שליחה ---------- */
  const send = async (btn) => {
    const status = root.querySelector('.qf-status');
    if ((state.name || '').trim().length < 2) { go(0); validStep(); return; }
    const items = [];
    steps.forEach((s) => s.qs.forEach((q) => { if (['name', 'phone', 'email', 'business', 'quote'].includes(q.id)) return; if (visible(q) && hasAnswer(q)) items.push({ s: s.title, q: qTitle(q), a: answerText(q) }); }));
    btn.disabled = true;
    const payload = {
      form: FORM, lang: EN ? 'en' : 'he', t: Date.now() - Number(state.__t0 || Date.now()), hp: root.querySelector('[name=_hpq]')?.value || '',
      contact: { name: state.name || '', phone: state.phone || '', email: state.email || '', business: state.business || '', quote: state.quote || '' }, items,
      raw: Object.fromEntries(Object.entries(state).filter(([k]) => !k.startsWith('__'))) };
    const fd = new FormData();
    fd.append('payload', JSON.stringify(payload));
    let total = 0;
    Object.entries(FILES).forEach(([qid, arr]) => { const q = steps.flatMap((s) => s.qs).find((x) => x.id === qid); if (!q || !visible(q)) return; arr.forEach((f) => { fd.append('files[]', f, f.name); fd.append('fileq[]', qid); total += f.size; }); });
    status.textContent = total ? t('שולח את התשובות והקבצים…', 'Sending your answers and files…') : t('שולח…', 'Sending…');
    try {
      const j = await new Promise((resolve, reject) => {
        const x = new XMLHttpRequest();
        x.open('POST', '/forms/submit.php');
        x.setRequestHeader('Accept', 'application/json');
        x.upload.onprogress = (ev) => { if (total > 200000 && ev.lengthComputable) status.textContent = t(`שולח… ${Math.round((ev.loaded / ev.total) * 100)}%`, `Sending… ${Math.round((ev.loaded / ev.total) * 100)}%`); };
        x.onload = () => { let r = {}; try { r = JSON.parse(x.responseText); } catch (e) { /* תשובה לא תקינה */ } if (x.status >= 200 && x.status < 300 && String(r.success) === 'true') resolve(r); else reject(new Error(r.message || x.status)); };
        x.onerror = () => reject(new Error('network'));
        x.send(fd);
      });
      try { localStorage.removeItem(KEY); } catch (e) { /* לא קריטי */ }
      try { if (location.hostname === 'yygweb.com' && navigator.sendBeacon) navigator.sendBeacon('/hit.php', new Blob([JSON.stringify({ e: 'lead', p: location.pathname, r: '', s: '' })], { type: 'application/json' })); } catch (e) { /* בלי מונה */ }
      root.innerHTML = `<div class="qf-step qf-done"><h2 class="qf-title" tabindex="-1">${t('נשלח', 'Sent')}</h2><p class="qf-intro">${esc(def.done)}</p><p><a class="btn btn-line" href="${t('/', '/en/')}">${t('חזרה לאתר', 'Back to the site')}</a></p></div>`;
      root.querySelector('.qf-title').focus();
    } catch (err) {
      btn.disabled = false;
      status.innerHTML = t('לא הצלחנו לשלוח מכאן. התשובות שמורות אצלכם. אפשר לנסות שוב, או להתקשר ל־', 'We couldn\'t send this. Your answers are saved. You can try again, or call ') + '<a class="text-link" href="tel:0548540700">054-854-0700</a>.';
    }
  };

  render();
})();
