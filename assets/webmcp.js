/* YYG Web Design: כלים לסוכני בינה מלאכותית שרצים בדפדפן (WebMCP).
   סוכן שעוזר לגולש יכול לקבל מחירים, הערכת מחיר, דרכי יצירת קשר, ולשלוח פנייה בשמו (אותן הגנות כמו בטופס).
   הנתונים: /assets/offer.json. בדפדפנים בלי WebMCP הקובץ לא עושה כלום. */
(() => {
  const mc = document.modelContext || navigator.modelContext;
  if (!mc || typeof mc.registerTool !== 'function') return;
  const EN = document.documentElement.lang === 'en';
  const lang = EN ? 'en' : 'he';
  let offer = null;
  const getOffer = async () => offer || (offer = await fetch('/assets/offer.json', { cache: 'no-cache' }).then((r) => r.json()));
  const nis = (n) => n.toLocaleString('en-US') + ' ILS';
  const text = (t) => ({ content: [{ type: 'text', text: t }] });
  const tools = [
    {
      name: 'yyg_get_prices',
      description: 'Get all YYG Web Design website packages, add-ons and recurring costs, with prices in ILS (final, no VAT added).',
      inputSchema: { type: 'object', properties: {} },
      annotations: { readOnlyHint: true },
      async execute() {
        const o = await getOffer();
        const lines = o.packages.map((p) => `- ${p.name[lang]}: ${p.from ? (EN ? 'from ' : 'מ־') : ''}${nis(p.price)}. ${p.summary[lang]}`)
          .concat(o.addons.map((a) => `- ${a.name[lang]}: ${nis(a.price)}`), o.recurring.map((r) => `- ${r.name[lang]}: ${nis(r.price)} / ${r.per}`), [o.vat_note[lang], o.payment[lang]]);
        return text(lines.join('\n'));
      },
    },
    {
      name: 'yyg_estimate_price',
      description: 'Get a rough starting estimate in ILS for a website from YYG Web Design.',
      inputSchema: {
        type: 'object',
        properties: {
          type: { type: 'string', enum: ['landing_basic', 'landing', 'site', 'club', 'store'], description: 'landing_basic, landing (one page), site (website with an admin area), club (sign-ups and a mailing list), store (online store)' },
          pages: { type: 'integer', minimum: 1, maximum: 40, description: 'Number of pages (for site or club)' },
          english: { type: 'boolean', description: 'Also an English version' },
        },
        required: ['type'],
      },
      annotations: { readOnlyHint: true },
      async execute({ type, pages, english } = {}) {
        const o = await getOffer();
        const p = o.packages.find((x) => x.id === type);
        if (!p) return text('Unknown type. Use one of: landing_basic, landing, site, club, store.');
        let total = p.price;
        const quota = { site: 6, club: 8 }[type];
        if (quota && pages > quota) total += (pages - quota) * 350;
        if (english) total += { landing_basic: 500, landing: 500, site: 1000, club: 1500, store: 1800 }[type];
        return text(`${p.name[lang]}: ${EN ? 'estimate from ' : 'הערכה מ־'}${nis(total)} (${o.vat_note[lang]}) ${EN ? 'A formal quote comes after a short questionnaire:' : 'הצעה מסודרת מגיעה אחרי שאלון קצר:'} ${o.business.questionnaire[lang]}`);
      },
    },
    {
      name: 'yyg_send_inquiry',
      description: 'Send an inquiry to YYG Web Design (web design) on behalf of the user. The user then gets an email with the next step. Ask the user before sending.',
      inputSchema: {
        type: 'object',
        properties: {
          name: { type: 'string', description: "The user's name" },
          email: { type: 'string', description: "The user's email address" },
          phone: { type: 'string', description: 'Phone number (optional)' },
          message: { type: 'string', description: 'What the business is and what kind of website is needed' },
        },
        required: ['name', 'email'],
      },
      async execute({ name, email, phone, message } = {}) {
        if (!name || !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email || '')) return text('A name and a valid email are required.');
        const t = await fetch('/send.php?token=1', { cache: 'no-store' }).then((r) => r.json()).catch(() => ({}));
        await new Promise((r) => setTimeout(r, 3500));   // אותו מנגנון זמן כמו בטופס הרגיל
        const res = await fetch('/send.php', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
          body: JSON.stringify({ name, email, phone: phone || '', notes: message || '', plan: (EN ? 'Inquiry sent by an AI agent (English site)' : 'פנייה שנשלחה דרך סוכן בינה מלאכותית'), _token: t.token || '' }) });
        const j = await res.json().catch(() => ({}));
        return text(String(j.success) === 'true' ? (EN ? 'Sent. The user will get an email with the next step, and Yehuda will reply within one business day.' : 'נשלח. הגולש יקבל מייל עם הצעד הבא, ויהודה יחזור תוך יום עבודה.') : 'Sending failed. The user can use the contact page: ' + (EN ? '/en/contact/' : '/contact/'));
      },
    },
    {
      name: 'yyg_contact_info',
      description: 'How to reach YYG Web Design: phone (calls only), hours, the contact page and the discovery questionnaire.',
      inputSchema: { type: 'object', properties: {} },
      annotations: { readOnlyHint: true },
      async execute() {
        const b = (await getOffer()).business;
        return text([`Phone (calls only): ${b.phone}`, `Hours: ${b.hours[lang]}`, `Contact page: ${b.contact_page[lang]}`, `Questionnaire: ${b.questionnaire[lang]}`].join('\n'));
      },
    },
  ];
  for (const tool of tools) {
    try { const r = mc.registerTool(tool); if (r && r.catch) r.catch(() => {}); } catch (e) { /* דפדפן בלי תמיכה מלאה */ }
  }
})();
