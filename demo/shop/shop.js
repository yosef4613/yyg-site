/* גרגר (עסק לדוגמה): מנוע החנות. קטלוג, מלאי, הזמנות, ועגלת הקניות של הגולש.
   הקטלוג, המלאי וההזמנות כתובים כאן כברירת מחדל. מה שמשנים במסך הניהול לדוגמה (/demo/shop/admin/) נשמר רק בדפדפן הזה,
   כדי שכל אחד יוכל לנסות בלי לקלקל לאחרים. באתר אמיתי הכול נשמר בשרת, והתשלום עובר דרך חברת סליקה.
   כאן אין תשלום בכלל: "ההזמנה" לדוגמה רק נרשמת בדפדפן, מורידה מהמלאי ומרוקנת את העגלה.

   מפתחות בדפדפן:
     gargar-shop-v1  הקטלוג, הקטגוריות, המשלוחים, ההודעה וההזמנות (מסך הניהול כותב לכאן)
     gargar-cart-v1  העגלה של הגולש: { items: [{ id, qty }], delivery: 'ship' | 'pickup' }

   שימוש בעיצוב: מוסיפים אלמנטים עם התכונות האלה, והסקריפט ממלא אותם. הכול נכתב עם textContent, בלי innerHTML.

   רשת מוצרים
     data-shop-grid            הרשת. בתוכה אפשר לשים <template data-shop-card> עם כרטיס משלכם (ראו "שדות" למטה).
                               בלי תבנית נבנה כרטיס ברירת מחדל: <article class="shop-card"> (ב־<ul>/<ol> עטוף ב־<li>).
       data-cat="spices"       מסנן לפי קטגוריה: spices | tea | gifts, או השם בעברית ("תבלינים"). all או ריק: הכול
       data-featured           רק מוצרים מומלצים (featured), לדף הבית
       data-ids="zaatar,nana"  מוצרים מסוימים, בסדר הזה
       data-limit="8"          עד כמה מוצרים
       data-sizes="..."        ערך sizes לתמונות (ברירת מחדל: (max-width: 640px) 50vw, 300px)
       data-heading="h3"       רמת הכותרת בכרטיס ברירת המחדל
     הרשת מקבלת data-count עם מספר המוצרים שמוצגים. [data-shop-empty] בתוכה מוצג רק כשאין מוצרים.
   שדות בתבנית (data-f="..."), גם בכרטיס וגם בשורת עגלה:
     name, desc, unit, cat, badge (מוסתר כשאין), price ("24 ₪", עם רווח קשיח לפני ₪), stock ("במלאי" / "נשארו רק 3" / "אזל מהמלאי"),
     ground ("נטחן ב־25.11" / "נטחן היום" / "נארז אתמול"; תה, מארזים ותבלינים שלמים "נארזים"), ground-date ("25.11"; על <time> גם datetime),
     img (על <img>: src, srcset, sizes, alt, width, height, loading), qty ומספר, line (סכום השורה, בעגלה)
     שורש הכרטיס מקבל data-product, data-cat, ו־class: is-soldout / is-low.
     [data-add] בכרטיס מקבל את המזהה, ו־disabled כשאזל. שימו לב: הטקסט של הכפתור מוחלף (textContent) ב"אזל מהמלאי" וחוזר,
     כך שאייקון בתוך הכפתור נמחק. אייקון שמים ב־::before, או ליד הכפתור. data-label ו־data-soldout קובעים את שני הנוסחים.
     [data-qty] (input) בכרטיס קובע כמה להוסיף.
   סינון
     [data-shop-filter="spices"]  כפתורי קטגוריה (all להכול). מקבלים aria-pressed. data-target="#grid" בוחר רשת,
                                  אחרת הרשת הראשונה בעמוד. [data-cat-count="tea"] מספר המוצרים בקטגוריה.
   עגלה
     [data-add="zaatar"]       הוספה לעגלה (האזנה אחת לכל העמוד). data-qty="2" על הכפתור, או [data-qty] באותו כרטיס.
     [data-inc] [data-dec] [data-remove]="id"   הוספה, הפחתה והסרה של שורה
     data-cart-count           מספר הפריטים (וגם data-count על האלמנט, לעיצוב [data-count="0"])
     data-cart-subtotal        סכום המוצרים
     data-cart-shipping        "30 ₪" / "חינם" / "איסוף עצמי, חינם"
     data-cart-total           סה״כ לתשלום
     data-free-left            "עוד 130 ₪ למשלוח חינם" / "יש לכם משלוח חינם"
     data-free-bar             פס התקדמות: ב־<progress> נקבעים value ו־max, אחרת משתנה CSS בשם --free (0% עד 100%)
     data-cart-list            רשימת השורות. אפשר <template data-cart-item> בפנים. בלי תבנית נבנית שורה של class="cart-line"
     data-cart-empty / data-cart-full   מוצגים רק כשהעגלה ריקה / לא ריקה
     input[name="delivery"]    רדיו: ship (משלוח) או pickup (איסוף עצמי). [data-ship-only] מוסתר באיסוף עצמי,
                               ושדות required בתוכו מפסיקים להיות חובה
     data-cart-toast           הודעה קצרה אחרי הוספה (class="is-show" לכ־2.5 שניות)
   מגירת עגלה (לא חובה)
     [data-cart-toggle] פותח, [data-cart-close] סוגר, [data-cart-panel] היא המגירה. ב־<dialog> נפתח כחלון (showModal),
     אחרת מתחלף hidden (או class="is-open" כשיש למגירה data-animate). Esc סוגר, והפוקוס חוזר לכפתור שפתח.
     data-open-on-add על המגירה: נפתחת אחרי הוספה. לחיצה מחוץ למלבן של ה־<dialog> (על הרקע) סוגרת אותו.
   הודעות לקורא מסך: aria-live="polite". כשהעגלה פתוחה ההודעות נכתבות בתוכה (מחוץ לחלון מודאלי שום דבר לא מוקרא).
     [data-shop-live] של העמוד משמש אם קיים, גם אם העיצוב מעביר אותו לתוך העגלה. אחרת המנוע יוצר אזורים משלו.
   הזמנה לדוגמה
     [data-checkout]           טופס (או כפתור). שדות לפי name: name, phone, city, address, notes, delivery.
                               יוצר הזמנה לדוגמה (בלי תשלום), מוריד מהמלאי ומרוקן את העגלה.
                               לטופס חובה novalidate: המנוע בודק את השדות בעצמו, מסמן aria-invalid="true" ומעביר פוקוס לשדה הראשון.
                               בלי novalidate הדפדפן עוצר את השליחה לפני שהמנוע רואה אותה.
     data-checkout-result      ההודעה אחרי ההזמנה (role="status" מומלץ). מחוץ ל־[data-cart-full], כדי שתישאר אחרי שהעגלה מתרוקנת.
                               כשפותחים שוב את העגלה אחרי הזמנה, ההודעה הישנה מוסתרת (אלא אם ההזמנה נשלחה הרגע).
   משלוחים והודעה מהנתונים (מתעדכנים ממסך הניהול)
     data-ship-threshold ("250 ₪"), data-ship-fee ("30 ₪"), data-ship-days ("2 עד 4 ימי עבודה"),
     data-pickup-address, data-product-count, data-shop-notice (מוסתר כשאין הודעה)
   אירועים: 'gargar:cart' ו־'gargar:order' על document, עם detail.
   window.Gargar: load, save, reset, DEFAULT, cart, totals, render, checkout, product, format, img, STATUSES, FLOW ועוד.
     למסך הניהול: updateProduct, setStock, setOrderStatus, stats, flow(order), grind(id, date?), grindAll(date?, cat='spices'),
     groundOf, groundText, today() ו־lastGrind() (תאריכים בפורמט YYYY-MM-DD, שעון ישראל). */
(() => {
  'use strict';
  const KEY = 'gargar-shop-v1', CART_KEY = 'gargar-cart-v1';
  const IMG = '/demo/shop/img/';
  /* סטטוסים של הזמנה. משלוח: חדשה, שולמה, נארזה, נשלחה, נמסרה. איסוף עצמי: חדשה, שולמה, נארזה (מוכנה לאיסוף), נאספה */
  const STATUSES = ['חדשה', 'שולמה', 'נארזה', 'נשלחה', 'נמסרה', 'נאספה', 'בוטלה'];
  const FLOW = { ship: ['חדשה', 'שולמה', 'נארזה', 'נשלחה', 'נמסרה'], pickup: ['חדשה', 'שולמה', 'נארזה', 'נאספה'] };
  const DONE = ['נמסרה', 'נאספה', 'בוטלה'];
  const p = (id, name, cat, price, unit, desc, img, alt, stock, extra) => Object.assign({ id, name, cat, price, unit, desc, img, alt, stock }, extra || {});
  const DEFAULT = {
    v: 1,
    notice: '',
    categories: [
      { id: 'spices', name: 'תבלינים', desc: 'טחונים אצלנו פעמיים בשבוע, במנות קטנות', img: 'jar-paprika' },
      { id: 'tea', name: 'תה וחליטות', desc: 'עלים שלמים, מיובשים בצל', img: 'tin-green' },
      { id: 'gifts', name: 'מארזים', desc: 'מוכנים למתנה, עם כרטיס ברכה', img: 'gift-jars' },
    ],
    shipping: { threshold: 250, fee: 30, days: '2 עד 4 ימי עבודה', pickup: 'רחוב הדוגמה 7, ירושלים' },
    products: [
      p('zaatar', 'זעתר ירושלמי', 'spices', 24, '100 גרם', 'אזוב טחון עם שומשום קלוי, סומאק ומלח ים. לפיתה חמה עם שמן זית.', 'jar-zaatar',
        'צנצנת זכוכית מלאה זעתר ירוק עם מכסה עץ, ולידה ערימה קטנה של זעתר', 42, { badge: 'הכי נמכר', featured: true }),
      p('baharat', 'בהרט של הבית', 'spices', 26, '100 גרם', 'פלפל אנגלי, קינמון, ציפורן ואגוז מוסקט. לבשר, לאורז ולממולאים.', 'jar-baharat',
        'צנצנת בהרט חום כהה עם מכסה עץ, ולידה מקל קינמון וציפורן', 30, { featured: true }),
      p('hawaij-soup', 'חוואיג׳ למרק', 'spices', 22, '100 גרם', 'כמון, כורכום, פלפל שחור והל, כמו שסבא טחן. למרק, לחמין ולעוף.', 'jar-hawaij',
        'צנצנת חוואיג׳ למרק בצבע זהוב, ולידה גרגרי פלפל שחור ותרמילי הל', 25, { badge: 'המתכון של סבא', featured: true }),
      p('hawaij-coffee', 'חוואיג׳ לקפה', 'spices', 28, '80 גרם', 'הל, ג׳ינג׳ר, קינמון וציפורן. כפית קטנה לפינג׳אן.', 'pouch-coffee',
        'שקית נייר קראפט של חוואיג׳ לקפה, ערימה קטנה של תבלין בהיר ותרמילי הל', 18),
      p('ras-el-hanout', 'ראס אל חנות', 'spices', 32, '80 גרם', 'תערובת מרוקאית של 14 תבלינים עם עלי ורד. לטאג׳ין, לקוסקוס ולירקות צלויים.', 'jar-ras',
        'צנצנת ראס אל חנות כתומה עם מכסה פליז, כוכב אניס ועלי ורד מיובשים', 12, { badge: 'חדש' }),
      p('paprika', 'פפריקה מעושנת', 'spices', 24, '100 גרם', 'פלפל אדום מעושן על עץ, מתוק ועמוק. לתפוחי אדמה, לשקשוקה ולצלי.', 'jar-paprika',
        'צנצנת פפריקה אדומה עם מכסה עץ, וערימה קטנה של פפריקה לידה', 34, { featured: true }),
      p('turmeric', 'כורכום', 'spices', 18, '100 גרם', 'כורכום טחון בצבע זהב עמוק. לאורז, למרקים ולמשקה חם.', 'jar-turmeric',
        'צנצנת כורכום צהוב עם מכסה שחור, וערימה קטנה של כורכום לידה', 40),
      p('cumin', 'כמון טחון', 'spices', 20, '100 גרם', 'זרעי כמון קלויים וטחונים אצלנו. לחומוס, לפלאפל ולתבשילי קטניות.', 'jar-cumin',
        'צנצנת כמון טחון בצבע חום בהיר עם מכסה עץ, וזרעי כמון מפוזרים', 28),
      p('sumac', 'סומאק', 'spices', 22, '100 גרם', 'חמצמץ ופירותי, בצבע בורדו. לסלטים, לבצל ולעוף בתנור.', 'jar-sumac',
        'צנצנת סומאק בצבע בורדו עם מכסה שחור, וערימה קטנה של סומאק', 0),
      p('chili', 'צ׳ילי גרוס', 'spices', 22, '80 גרם', 'פלפל אדום חריף, גרוס עם הגרעינים. למי שאוהב חריף באמת.', 'spoon-chili',
        'כף עץ מלאה צ׳ילי גרוס אדום, וערימה קטנה של צ׳ילי לידה', 3, { badge: 'חריף' }),
      p('whole-spices', 'תבלינים שלמים לבישול', 'spices', 34, '120 גרם', 'כוכבי אניס, מקלות קינמון, הל וציפורן. לתבשילים, לאורז ולתה חם.', 'bowl-whole',
        'קערה לבנה מלאה כוכבי אניס, מקלות קינמון, תרמילי הל וציפורן', 15, { groundVerb: 'נארז' }),
      p('nana', 'נענע מיובשת', 'tea', 18, '50 גרם', 'עלי נענע שלמים שיובשו בצל. לכוס תה מתוק אחרי הארוחה.', 'pouch-nana',
        'שקית נייר קראפט של נענע מיובשת, ועלי נענע מפוזרים לידה', 22, { featured: true }),
      p('louisa', 'לואיזה', 'tea', 20, '40 גרם', 'עלים ריחניים בטעם לימוני עדין. חליטה רגועה לערב.', 'tin-louisa',
        'פחית תה בצבע שמנת עם מכסה פליז, ועלי לואיזה מיובשים לידה', 9),
      p('green-mint', 'תה ירוק עם נענע', 'tea', 28, '100 גרם', 'תה ירוק בגלגולים עם נענע מיובשת, כמו שמגישים בבית מרוקאי.', 'tin-green',
        'פחית תה ירוקה כהה עם טבעת פליז, ועלי תה מפוזרים לידה', 20, { featured: true }),
      p('sage', 'מרווה', 'tea', 18, '40 גרם', 'עלי מרווה מיובשים. חליטה חמה לימי החורף.', 'tin-sage',
        'פחית תה בירוק אפרפר, ועלי מרווה מיובשים לידה', 4),
      p('black-cardamom', 'תה שחור עם הל', 'tea', 26, '100 גרם', 'תה שחור חזק עם תרמילי הל גרוסים. ארומטי, עם חלב או בלי.', 'tin-chai',
        'פחית תה בצבע חמרה, תרמילי הל ועלי תה כהים לידה', 16),
      p('gift-holiday', 'מארז לחג: 4 תבלינים', 'gifts', 149, '4 צנצנות', 'זעתר, בהרט, חוואיג׳ למרק ופפריקה מעושנת, בקופסת עץ עם כרטיס ברכה.', 'gift-jars',
        'קופסת עץ פתוחה עם ארבע צנצנות תבלינים, וכרטיס ברכה "חג שמח" לידה', 24, { badge: 'לחגים', featured: true }),
      p('gift-tea', 'מארז תה וחליטות', 'gifts', 129, '4 פחיות', 'תה ירוק, מרווה, לואיזה ותה שחור עם הל, בקופסת עץ עם כרטיס ברכה.', 'gift-tea',
        'קופסת עץ עם ארבע פחיות תה צבעוניות, וכרטיס ברכה לידה', 14, { featured: true }),
      p('gift-mortar', 'מארז טוחנים בבית', 'gifts', 189, 'מכתש ו־3 תבלינים', 'מכתש ועלי מאבן, עם פלפל שחור, הל וכוכבי אניס שלמים.', 'mortar',
        'מכתש ועלי מאבן אפורה מלא תבלינים שלמים, ולידו כוכב אניס ופלפל שחור', 6),
    ],
    /* הזמנות לדוגמה, בשביל מסך הניהול. date: ISO, שעון ישראל */
    orders: [
      { id: 'G-1037', date: '2026-11-19T09:42', name: 'משפחת לוי', phone: '050-0000000', city: 'בני ברק', address: 'רחוב הדוגמה 12', delivery: 'ship',
        items: [{ id: 'zaatar', name: 'זעתר ירושלמי', qty: 2, price: 24 }, { id: 'hawaij-soup', name: 'חוואיג׳ למרק', qty: 2, price: 22 }, { id: 'nana', name: 'נענע מיובשת', qty: 1, price: 18 }],
        subtotal: 110, shipping: 30, total: 140, status: 'נמסרה' },
      { id: 'G-1038', date: '2026-11-20T13:05', name: 'משרד דוגמה בע״מ', phone: '050-0000000', city: 'פתח תקווה', address: 'שדרות הדוגמה 40', delivery: 'ship',
        items: [{ id: 'gift-holiday', name: 'מארז לחג: 4 תבלינים', qty: 12, price: 149 }],
        subtotal: 1788, shipping: 0, total: 1788, status: 'נשלחה', note: 'מתנות לעובדים. כרטיס ברכה בכל מארז.' },
      { id: 'G-1039', date: '2026-11-23T11:18', name: 'י. מזרחי', phone: '050-0000000', city: 'ירושלים', address: '', delivery: 'pickup',
        items: [{ id: 'paprika', name: 'פפריקה מעושנת', qty: 1, price: 24 }, { id: 'cumin', name: 'כמון טחון', qty: 1, price: 20 }, { id: 'turmeric', name: 'כורכום', qty: 1, price: 18 }],
        subtotal: 62, shipping: 0, total: 62, status: 'נארזה' },
      { id: 'G-1040', date: '2026-11-24T16:30', name: 'משפחת כהן', phone: '050-0000000', city: 'אלעד', address: 'רחוב הדוגמה 5', delivery: 'ship',
        items: [{ id: 'gift-tea', name: 'מארז תה וחליטות', qty: 1, price: 129 }, { id: 'louisa', name: 'לואיזה', qty: 1, price: 20 }, { id: 'sage', name: 'מרווה', qty: 1, price: 18 }],
        subtotal: 167, shipping: 30, total: 197, status: 'נארזה' },
      { id: 'G-1041', date: '2026-11-25T10:02', name: 'א. פרידמן', phone: '050-0000000', city: 'בית שמש', address: 'רחוב הדוגמה 21', delivery: 'ship',
        items: [{ id: 'gift-holiday', name: 'מארז לחג: 4 תבלינים', qty: 1, price: 149 }, { id: 'gift-mortar', name: 'מארז טוחנים בבית', qty: 1, price: 189 }],
        subtotal: 338, shipping: 0, total: 338, status: 'שולמה' },
      { id: 'G-1042', date: '2026-11-26T08:47', name: 'משפחת אברהם', phone: '050-0000000', city: 'מודיעין עילית', address: 'רחוב הדוגמה 9', delivery: 'ship',
        items: [{ id: 'hawaij-coffee', name: 'חוואיג׳ לקפה', qty: 2, price: 28 }, { id: 'black-cardamom', name: 'תה שחור עם הל', qty: 1, price: 26 }, { id: 'green-mint', name: 'תה ירוק עם נענע', qty: 1, price: 28 }],
        subtotal: 110, shipping: 30, total: 140, status: 'חדשה' },
    ],
    nextOrder: 1043,
  };

  /* ---------- שמירה וטעינה ---------- */
  const clone = (o) => JSON.parse(JSON.stringify(o));
  const read = (k) => { try { return JSON.parse(localStorage.getItem(k) || 'null'); } catch (e) { return null; } };
  /* lastError: 'quota' כשאין מקום בדפדפן (למשל תמונות גדולות), 'blocked' כשהדפדפן לא מאפשר לשמור */
  let lastError = '';
  const write = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); lastError = ''; return true; } catch (e) { lastError = e && (e.name === 'QuotaExceededError' || e.code === 22 || e.code === 1014) ? 'quota' : 'blocked'; return false; } };
  function load() {
    const d = read(KEY);
    if (d && d.v === 1 && Array.isArray(d.products) && Array.isArray(d.orders)) {
      /* שמירה חלקית: משלימים מה שחסר מברירת המחדל */
      if (!Array.isArray(d.categories)) d.categories = clone(DEFAULT.categories);
      if (!d.shipping || typeof d.shipping !== 'object') d.shipping = clone(DEFAULT.shipping);
      if (typeof d.notice !== 'string') d.notice = '';
      if (!Number.isFinite(d.nextOrder)) d.nextOrder = DEFAULT.nextOrder;
      return d;
    }
    return clone(DEFAULT);
  }
  const save = (d) => { const ok = write(KEY, Object.assign({}, d, { v: 1, updated: Date.now() })); render(); return ok; };
  /* reset('shop') מחזיר רק את הקטלוג וההזמנות, reset('cart') רק את העגלה, ובלי פרמטר את שניהם */
  const reset = (scope) => { try { if (scope !== 'cart') localStorage.removeItem(KEY); if (scope !== 'shop') localStorage.removeItem(CART_KEY); } catch (e) { /* */ } render(); };

  /* ---------- עזרים ---------- */
  const NF = new Intl.NumberFormat('he-IL', { maximumFractionDigits: 2 });
  const format = (n) => `${NF.format(Number(n) || 0)} ₪`;
  const num = (v, d = 0) => (Number.isFinite(Number(v)) ? Number(v) : d);
  const visible = (data) => data.products.filter((x) => !x.hidden);
  const product = (id, data = load()) => data.products.find((x) => x.id === id) || null;
  const catOf = (key, data = load()) => data.categories.find((c) => c.id === key || c.name === key) || null;
  const stockText = (x) => (num(x.stock) <= 0 ? 'אזל מהמלאי' : num(x.stock) <= 5 ? `נשארו רק ${num(x.stock)}` : 'במלאי');
  /* תאריך הטחינה. ground: 'YYYY-MM-DD' על המוצר. בלי תאריך (או בשמירה ישנה): יום ראשון או רביעי האחרון לפני היום, לפי שעון ישראל.
     תבלינים "נטחנים", תה ומארזים "נארזים" (groundVerb על המוצר גובר על הקטגוריה) */
  const ISO = /^\d{4}-\d{2}-\d{2}$/;
  const today = () => { const pp = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jerusalem', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date()).map((q) => [q.type, q.value])); return `${pp.year}-${pp.month}-${pp.day}`; };
  const addDays = (iso, n) => new Date(Date.parse(`${iso}T12:00:00Z`) + n * 864e5).toISOString().slice(0, 10);
  function lastGrind(from = today()) { for (let i = 1; i <= 7; i++) { const d = addDays(from, -i), wd = new Date(`${d}T12:00:00Z`).getUTCDay(); if (wd === 0 || wd === 3) return d; } return addDays(from, -1); }
  const groundOf = (x) => (x && ISO.test(String(x.ground || '')) ? x.ground : lastGrind());
  const groundVerb = (x) => (x.groundVerb || (x.cat === 'spices' ? 'נטחן' : 'נארז'));
  const dm = (iso) => { const [, mm, dd] = iso.split('-'); return `${Number(dd)}.${Number(mm)}`; };
  function groundText(x) {
    const g = groundOf(x), t = today(), v = groundVerb(x);
    if (g === t) return `${v} היום`;
    if (g === addDays(t, -1)) return `${v} אתמול`;
    return `${v} ב־${dm(g)}`;
  }
  /* תמונה: שם בסיס (jar-zaatar) נהיה /demo/shop/img/jar-zaatar-960.webp, וכתובת מלאה או data: נשארת כמו שהיא */
  const isUrl = (s) => /^(data:|blob:|https?:|\/)/.test(String(s || ''));
  const img = (x, w = 960) => (isUrl(x.img) ? x.img : `${IMG}${x.img}-${w}.webp`);
  const srcset = (x) => (isUrl(x.img) ? '' : `${IMG}${x.img}-560.webp 560w, ${IMG}${x.img}-960.webp 960w`);
  const items = (n) => (n === 1 ? 'פריט אחד' : `${n} פריטים`);
  const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; };
  const all = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  /* הודעות לקורא מסך. כשהעגלה פתוחה כחלון (showModal), כל מה שמחוץ לה "אינרטי" ולא מוקרא,
     ולכן ההודעה נכתבת לאזור הודעות שבתוך העגלה: [data-shop-live] של העמוד אם הוא שם (עיצוב יכול להעביר אותו פנימה),
     אחרת אזור שהמנוע יוצר בתוך העגלה. כשהעגלה סגורה: [data-shop-live] של העמוד, או אזור שהמנוע יוצר ב־<body>.
     האזור נבחר רק ברגע הכתיבה, אחרי שהעגלה כבר נפתחה, ונוצר מראש כדי שקוראי מסך יכירו אותו לפני שמשהו נכתב בו. */
  const SR_STYLE = 'position:absolute;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;border:0';
  function makeLive(parent) {
    const r = el('div'); r.setAttribute('aria-live', 'polite'); r.setAttribute('aria-atomic', 'true'); r.setAttribute('data-shop-live-auto', '');
    r.style.cssText = SR_STYLE; parent.appendChild(r); return r;
  }
  function ensureLive() {
    if (!document.body) return;
    const pn = panel();
    if (pn && !pn.querySelector('[data-shop-live-auto]')) makeLive(pn);
    const own = all('[data-shop-live]').some((e) => !(pn && pn.contains(e)));
    if (!own && !all(':scope > [data-shop-live-auto]', document.body).length) makeLive(document.body);
  }
  function liveRegion() {
    ensureLive();
    const pn = panel();
    if (pn && isOpen(pn)) return pn.querySelector('[data-shop-live]') || pn.querySelector('[data-shop-live-auto]');
    return all('[data-shop-live]').find((e) => !(pn && pn.contains(e))) || document.querySelector('body > [data-shop-live-auto]');
  }
  let liveTimer = 0;
  function announce(msg) {
    clearTimeout(liveTimer);
    /* מחכים רגע: אם העגלה נפתחת עכשיו (אחרי הוספה), ההודעה תיכתב כבר לתוכה */
    liveTimer = setTimeout(() => {
      const r = liveRegion(); if (!r) return;
      all('[data-shop-live], [data-shop-live-auto]').forEach((x) => { if (x !== r) x.textContent = ''; });
      r.textContent = '';
      liveTimer = setTimeout(() => { r.textContent = msg; }, 60);
    }, 80);
  }

  /* ---------- עגלה ---------- */
  function cartRead() {
    const c = read(CART_KEY);
    const items = c && Array.isArray(c.items) ? c.items.filter((i) => i && typeof i.id === 'string' && num(i.qty) > 0).map((i) => ({ id: i.id, qty: Math.floor(num(i.qty)) })) : [];
    return { items, delivery: c && c.delivery === 'pickup' ? 'pickup' : 'ship' };
  }
  const cartWrite = (c) => { write(CART_KEY, c); render(); };
  const emit = (name, detail) => document.dispatchEvent(new CustomEvent(name, { detail }));
  /* שורות העגלה, רק מוצרים שעדיין קיימים, והכמות לא עוברת את המלאי */
  function lines(data = load(), c = cartRead()) {
    return c.items.map((i) => { const x = product(i.id, data); if (!x || x.hidden) return null; const qty = Math.min(i.qty, Math.max(0, num(x.stock))); return { id: x.id, product: x, qty, line: qty * num(x.price) }; }).filter((l) => l && l.qty > 0);
  }
  const cart = {
    get: () => cartRead(),
    lines: () => lines(),
    count: () => lines().reduce((s, l) => s + l.qty, 0),
    add(id, qty = 1) {
      const data = load(), x = product(id, data); qty = Math.max(1, Math.floor(num(qty, 1)));
      if (!x || x.hidden) return { ok: false, msg: 'המוצר לא נמצא' };
      const stock = Math.max(0, num(x.stock));
      if (!stock) { announce(`${x.name}: אזל מהמלאי`); return { ok: false, msg: 'אזל מהמלאי' }; }
      const c = cartRead(), it = c.items.find((i) => i.id === id), have = it ? Math.min(it.qty, stock) : 0;
      const next = Math.min(stock, have + qty), added = next - have;
      if (it) it.qty = next; else c.items.push({ id, qty: next });
      cartWrite(c);
      const msg = added <= 0 ? `נשארו רק ${stock} במלאי. ${x.name} כבר בעגלה.` : `נוסף לעגלה: ${x.name}${added > 1 ? ` (${added})` : ''}. בעגלה: ${items(cart.count())}.`;
      announce(added < qty && added > 0 ? `${msg} נשארו רק ${stock} במלאי.` : msg);
      emit('gargar:cart', { action: 'add', id, qty: added, cart: cartRead() });
      return { ok: added > 0, qty: next, msg };
    },
    set(id, qty) {
      const x = product(id), c = cartRead(), it = c.items.find((i) => i.id === id);
      qty = Math.floor(num(qty));
      if (!x || qty <= 0) return cart.remove(id);
      const q = Math.min(qty, Math.max(0, num(x.stock)));
      if (it) it.qty = q; else c.items.push({ id, qty: q });
      cartWrite(c); announce(`${x.name}: כמות ${q}`); emit('gargar:cart', { action: 'set', id, qty: q, cart: cartRead() });
      return { ok: true, qty: q };
    },
    remove(id) {
      const x = product(id), c = cartRead();
      c.items = c.items.filter((i) => i.id !== id); cartWrite(c);
      announce(`הוסר מהעגלה: ${x ? x.name : ''}. ${cart.count() ? `בעגלה: ${items(cart.count())}.` : 'העגלה ריקה.'}`);
      emit('gargar:cart', { action: 'remove', id, cart: cartRead() });
      return { ok: true };
    },
    clear() { const c = cartRead(); c.items = []; cartWrite(c); emit('gargar:cart', { action: 'clear', cart: cartRead() }); },
    setDelivery(v) { const c = cartRead(); c.delivery = v === 'pickup' ? 'pickup' : 'ship'; cartWrite(c); emit('gargar:cart', { action: 'delivery', cart: cartRead() }); },
  };

  /* ---------- סכומים ---------- */
  function totals(data = load(), c = cartRead()) {
    const ls = lines(data, c), sh = data.shipping || DEFAULT.shipping;
    const count = ls.reduce((s, l) => s + l.qty, 0), subtotal = ls.reduce((s, l) => s + l.line, 0);
    const threshold = num(sh.threshold, 250), fee = num(sh.fee, 30), pickup = c.delivery === 'pickup';
    const free = pickup || subtotal >= threshold;
    const shipping = !count || free ? 0 : fee;
    return { count, subtotal, shipping, total: subtotal + shipping, delivery: c.delivery, free, threshold, fee,
      freeLeft: Math.max(0, threshold - subtotal), freePct: Math.min(100, Math.round(subtotal / threshold * 100)) };
  }

  /* ---------- כרטיס מוצר ---------- */
  function fill(root, x, data, extra = {}) {
    const fields = all('[data-f]', root); if (root.matches && root.matches('[data-f]')) fields.unshift(root);
    const cat = catOf(x.cat, data);
    for (const f of fields) {
      const k = f.dataset.f;
      if (k === 'img') {
        if (f.tagName === 'IMG') {
          f.src = img(x); const ss = srcset(x); if (ss) { f.srcset = ss; f.sizes = f.getAttribute('sizes') || extra.sizes || '(max-width: 640px) 50vw, 300px'; } else f.removeAttribute('srcset');
          f.alt = x.alt || x.name; if (!f.hasAttribute('width')) { f.width = 960; f.height = 960; } if (!f.hasAttribute('loading')) f.loading = 'lazy'; f.decoding = 'async';
        }
        continue;
      }
      let v = '';
      if (k === 'name') v = x.name; else if (k === 'desc') v = x.desc || ''; else if (k === 'unit') v = x.unit || '';
      else if (k === 'cat') v = cat ? cat.name : ''; else if (k === 'badge') v = x.badge || '';
      else if (k === 'price') v = format(x.price); else if (k === 'stock') v = stockText(x);
      else if (k === 'qty') v = String(extra.qty ?? ''); else if (k === 'line') v = format(extra.line ?? 0);
      else if (k === 'ground') v = groundText(x); else if (k === 'ground-date') v = dm(groundOf(x));
      else if (k in x) v = String(x[k]);
      f.textContent = v;
      if ((k === 'ground' || k === 'ground-date') && f.tagName === 'TIME') f.dateTime = groundOf(x);
      if (k === 'badge' || k === 'desc' || k === 'unit') f.hidden = !v;
    }
    root.dataset.product = x.id; root.dataset.cat = x.cat;
    root.classList.toggle('is-soldout', num(x.stock) <= 0); root.classList.toggle('is-low', num(x.stock) > 0 && num(x.stock) <= 5);
    const soldout = num(x.stock) <= 0;
    for (const b of all('[data-add]', root)) {
      b.dataset.add = x.id;
      if (!b.dataset.label) b.dataset.label = b.textContent.trim() || 'הוספה לעגלה';
      b.disabled = soldout; b.textContent = soldout ? (b.dataset.soldout || 'אזל מהמלאי') : b.dataset.label;
      b.setAttribute('aria-label', soldout ? `${x.name}: אזל מהמלאי` : `${b.dataset.label}: ${x.name}`);
    }
    for (const q of all('input[data-qty]', root)) { q.min = 1; q.max = Math.max(1, num(x.stock)); if (!q.value) q.value = 1; q.disabled = soldout; q.setAttribute('aria-label', `כמות: ${x.name}`); }
    return root;
  }
  function defaultCard(x, data, grid) {
    const h = grid.dataset.heading || 'h3';
    const card = el('article', 'shop-card');
    const media = el('div', 'shop-card__media'); const im = el('img', 'shop-card__img'); im.dataset.f = 'img'; media.appendChild(im);
    const badge = el('span', 'shop-card__badge'); badge.dataset.f = 'badge'; media.appendChild(badge);
    const body = el('div', 'shop-card__body');
    const catEl = el('p', 'shop-card__cat'); catEl.dataset.f = 'cat';
    const name = el(h, 'shop-card__name'); name.dataset.f = 'name';
    const desc = el('p', 'shop-card__desc'); desc.dataset.f = 'desc';
    const meta = el('p', 'shop-card__meta'); const pr = el('span', 'shop-card__price'); pr.dataset.f = 'price'; const un = el('span', 'shop-card__unit'); un.dataset.f = 'unit'; meta.append(pr, ' ', un);
    const st = el('p', 'shop-card__stock'); st.dataset.f = 'stock';
    const gr = el('p', 'shop-card__ground'); gr.dataset.f = 'ground';
    const add = el('button', 'shop-card__add', 'הוספה לעגלה'); add.type = 'button'; add.dataset.add = x.id;
    body.append(catEl, name, desc, meta, gr, st, add); card.append(media, body);
    return fill(card, x, data, { sizes: grid.dataset.sizes });
  }
  function renderGrid(grid, data, force) {
    /* הרשת תלויה רק בקטלוג, לא בעגלה: לא בונים מחדש בלי צורך, כדי שהפוקוס והתמונות יישארו במקום */
    const sig = JSON.stringify([data.products, data.categories, grid.dataset.cat, grid.dataset.ids, grid.dataset.limit, 'featured' in grid.dataset]);
    if (!force && grid._gargarSig === sig) return Number(grid.dataset.count || 0);
    grid._gargarSig = sig;
    let list = visible(data);
    const cat = grid.dataset.cat;
    if (cat && cat !== 'all') { const c = catOf(cat, data); list = list.filter((x) => c && x.cat === c.id); }
    if ('featured' in grid.dataset) list = list.filter((x) => x.featured);
    if (grid.dataset.ids) list = grid.dataset.ids.split(',').map((id) => list.find((x) => x.id === id.trim())).filter(Boolean);
    const lim = parseInt(grid.dataset.limit, 10); if (lim > 0) list = list.slice(0, lim);
    const tpl = grid.querySelector('template[data-shop-card]');
    const empty = grid.querySelector('[data-shop-empty]');
    for (const ch of Array.from(grid.children)) if (ch !== tpl && ch !== empty) ch.remove();
    const wrapLi = /^(UL|OL)$/.test(grid.tagName);
    for (const x of list) {
      let node;
      if (tpl) { const frag = tpl.content.cloneNode(true); node = frag.firstElementChild; fill(node, x, data, { sizes: grid.dataset.sizes }); }
      else { node = defaultCard(x, data, grid); if (wrapLi) { const li = el('li'); li.appendChild(node); node = li; } }
      grid.insertBefore(node, empty || null);
    }
    if (empty) empty.hidden = list.length > 0;
    grid.dataset.count = String(list.length);
    return list.length;
  }

  /* ---------- רשימת העגלה ---------- */
  function lineNode(l, data, list) {
    const tpl = list.querySelector('template[data-cart-item]');
    let node;
    if (tpl) node = tpl.content.cloneNode(true).firstElementChild;
    else {
      const x = l.product;
      node = el(/^(UL|OL)$/.test(list.tagName) ? 'li' : 'div', 'cart-line');
      const im = el('img', 'cart-line__img'); im.dataset.f = 'img'; im.width = 96; im.height = 96; im.sizes = '64px';
      const info = el('div', 'cart-line__info'); const nm = el('p', 'cart-line__name'); nm.dataset.f = 'name';
      const meta = el('p', 'cart-line__meta'); const un = el('span'); un.dataset.f = 'unit'; const pr = el('span'); pr.dataset.f = 'price'; meta.append(un, ' · ', pr); info.append(nm, meta);
      const q = el('div', 'cart-line__qty'); q.setAttribute('role', 'group'); q.setAttribute('aria-label', `כמות: ${x.name}`);
      const dec = el('button', 'cart-line__btn', '−'); dec.type = 'button'; dec.dataset.dec = x.id;
      const out = el('span', 'cart-line__count'); out.dataset.f = 'qty';
      const inc = el('button', 'cart-line__btn', '+'); inc.type = 'button'; inc.dataset.inc = x.id;
      q.append(dec, out, inc);
      const tot = el('p', 'cart-line__total'); tot.dataset.f = 'line';
      const rm = el('button', 'cart-line__remove', '×'); rm.type = 'button'; rm.dataset.remove = x.id;
      node.append(im, info, q, tot, rm);
    }
    fill(node, l.product, data, { qty: l.qty, line: l.line, sizes: '64px' });
    const x = l.product, max = num(x.stock);
    for (const b of all('[data-dec]', node)) { b.dataset.dec = x.id; b.setAttribute('aria-label', l.qty <= 1 ? `הסרה מהעגלה: ${x.name}` : `פחות אחד: ${x.name}`); }
    for (const b of all('[data-inc]', node)) { b.dataset.inc = x.id; b.disabled = l.qty >= max; b.setAttribute('aria-label', l.qty >= max ? `${x.name}: אין עוד במלאי` : `עוד אחד: ${x.name}`); }
    for (const b of all('[data-remove]', node)) { b.dataset.remove = x.id; b.setAttribute('aria-label', `הסרה מהעגלה: ${x.name}`); }
    for (const q of all('[data-f="qty"]', node)) q.setAttribute('aria-label', `כמות: ${l.qty}`);
    return node;
  }
  function renderCartList(list, data, ls) {
    /* שומרים על הפוקוס כשהשורות נבנות מחדש (למשל אחרי לחיצה על +) */
    const a = document.activeElement, inList = a && list.contains(a);
    const focusKey = inList ? ['inc', 'dec', 'remove'].find((k) => a.dataset && a.dataset[k]) : null, focusId = focusKey ? a.dataset[focusKey] : null;
    const tpl = list.querySelector('template[data-cart-item]');
    for (const ch of Array.from(list.children)) if (ch !== tpl) ch.remove();
    for (const l of ls) list.appendChild(lineNode(l, data, list));
    if (inList) {
      let t = focusId && list.querySelector(`[data-${focusKey}="${CSS.escape(focusId)}"]:not([disabled])`);
      if (!t && focusId) t = list.querySelector(`[data-remove="${CSS.escape(focusId)}"], [data-dec="${CSS.escape(focusId)}"]`);
      if (!t) t = list.querySelector('[data-remove], [data-dec]');
      if (!t) { list.tabIndex = -1; t = list; }
      t.focus({ preventScroll: true });
    }
  }

  /* ---------- ציור הכול ---------- */
  function render() {
    ensureLive();
    const data = load(), c = cartRead(), t = totals(data, c), ls = lines(data, c), sh = data.shipping || DEFAULT.shipping;
    all('[data-shop-grid]').forEach((g) => renderGrid(g, data));
    all('[data-cat-count]').forEach((e) => { const cat = catOf(e.dataset.catCount, data); e.textContent = String(visible(data).filter((x) => cat && x.cat === cat.id).length); });
    all('[data-product-count]').forEach((e) => { e.textContent = String(visible(data).length); });
    all('[data-cart-count]').forEach((e) => { e.textContent = String(t.count); e.dataset.count = String(t.count); });
    all('[data-cart-subtotal]').forEach((e) => { e.textContent = format(t.subtotal); });
    all('[data-cart-shipping]').forEach((e) => { e.textContent = t.delivery === 'pickup' ? 'איסוף עצמי, חינם' : !t.count ? `חינם מעל ${format(t.threshold)}` : t.shipping ? format(t.shipping) : 'חינם'; });
    all('[data-cart-total]').forEach((e) => { e.textContent = format(t.total); });
    all('[data-free-left]').forEach((e) => {
      e.textContent = t.delivery === 'pickup' ? 'איסוף עצמי מהחנות, בלי עלות משלוח' : !t.count ? `משלוח חינם בהזמנה מעל ${format(t.threshold)}` : t.freeLeft > 0 ? `עוד ${format(t.freeLeft)} למשלוח חינם` : 'יש לכם משלוח חינם';
      e.dataset.free = String(t.free && t.count > 0);
    });
    all('[data-free-bar]').forEach((e) => {
      const pct = t.delivery === 'pickup' ? 100 : t.freePct;
      if (e.tagName === 'PROGRESS') { e.max = t.threshold; e.value = Math.min(t.subtotal, t.threshold); }
      else e.style.setProperty('--free', `${pct}%`);
      if (e.getAttribute('role') === 'progressbar') { e.setAttribute('aria-valuemin', '0'); e.setAttribute('aria-valuemax', '100'); e.setAttribute('aria-valuenow', String(pct)); }
    });
    all('[data-cart-list]').forEach((l) => renderCartList(l, data, ls));
    all('[data-cart-empty]').forEach((e) => { e.hidden = t.count > 0; });
    all('[data-cart-full]').forEach((e) => { e.hidden = t.count === 0; });
    all('input[name="delivery"]').forEach((i) => { i.checked = i.value === c.delivery; });
    all('[data-ship-only]').forEach((box) => {
      box.hidden = c.delivery === 'pickup';
      all('input, select, textarea', box).forEach((f) => { if (f.dataset.req === undefined) f.dataset.req = f.required ? '1' : '0'; f.required = c.delivery !== 'pickup' && f.dataset.req === '1'; });
    });
    all('[data-ship-threshold]').forEach((e) => { e.textContent = format(t.threshold); });
    all('[data-ship-fee]').forEach((e) => { e.textContent = format(t.fee); });
    all('[data-ship-days]').forEach((e) => { e.textContent = sh.days || DEFAULT.shipping.days; });
    all('[data-pickup-address]').forEach((e) => { e.textContent = sh.pickup || DEFAULT.shipping.pickup; });
    all('[data-shop-notice]').forEach((e) => { const n = String(data.notice || '').trim(); e.textContent = n; e.hidden = !n; });
    all('[data-shop-filter]').forEach((b) => {
      const g = filterGrid(b); const cur = (g && g.dataset.cat) || 'all';
      const mine = b.dataset.shopFilter, same = mine === cur || (catOf(mine, data) && catOf(mine, data) === catOf(cur, data));
      if (b.tagName === 'BUTTON' || b.getAttribute('role') === 'button') b.setAttribute('aria-pressed', String(!!same));
    });
  }
  const filterGrid = (b) => (b.dataset.target ? document.querySelector(b.dataset.target) : document.querySelector('[data-shop-grid]'));

  /* ---------- מגירת העגלה ---------- */
  let opener = null, openerAdd = null, freshResult = false;
  const panel = () => document.querySelector('[data-cart-panel]');
  /* מחזירים את הפוקוס למי שפתח. אם הכרטיס נבנה מחדש בינתיים (למשל המלאי השתנה), לכפתור החדש של אותו מוצר */
  function returnFocus() {
    let t = opener && document.contains(opener) ? opener : null;
    if (!t && openerAdd) t = document.querySelector(`[data-add="${CSS.escape(openerAdd)}"]:not([disabled])`);
    if (!t && opener) t = document.querySelector('[data-cart-toggle]');
    if (t) t.focus({ preventScroll: true });
    opener = null; openerAdd = null;
  }
  const isOpen = (pn) => !!pn && (pn.tagName === 'DIALOG' ? pn.open : 'animate' in pn.dataset ? pn.classList.contains('is-open') : !pn.hidden);
  function openCart(from) {
    const pn = panel(); if (!pn) return;
    opener = from || document.activeElement; openerAdd = opener && opener.dataset ? opener.dataset.add || null : null;
    /* פותחים שוב אחרי הזמנה: ההודעה הישנה כבר לא רלוונטית, אלא אם ההזמנה נשלחה הרגע */
    if (!freshResult) all('[data-checkout-result]').forEach((x) => { x.hidden = true; });
    if (pn.tagName === 'DIALOG') { if (!pn.open) pn.showModal(); }
    else if ('animate' in pn.dataset) { pn.classList.add('is-open'); pn.removeAttribute('inert'); pn.setAttribute('aria-hidden', 'false'); }
    else pn.hidden = false;
    document.documentElement.classList.add('cart-open');
    all('[data-cart-toggle]').forEach((b) => b.setAttribute('aria-expanded', 'true'));
    const f = pn.querySelector('[data-cart-focus]') || pn.querySelector('h2, h3') || pn;
    if (!f.hasAttribute('tabindex') && !/^(BUTTON|A|INPUT|SELECT|TEXTAREA)$/.test(f.tagName)) f.tabIndex = -1;
    setTimeout(() => f.focus({ preventScroll: true }), 30);
  }
  function closeCart(noFocus) {
    const pn = panel(); if (!pn) return;
    if (noFocus) { opener = null; openerAdd = null; }
    if (pn.tagName === 'DIALOG') { if (pn.open) pn.close(); }
    else if ('animate' in pn.dataset) { pn.classList.remove('is-open'); pn.setAttribute('inert', ''); pn.setAttribute('aria-hidden', 'true'); }
    else pn.hidden = true;
    document.documentElement.classList.remove('cart-open');
    all('[data-cart-toggle]').forEach((b) => b.setAttribute('aria-expanded', 'false'));
    freshResult = false;
    returnFocus();
  }

  /* ---------- הזמנה לדוגמה ---------- */
  const orderId = (n) => `G-${n}`;
  const nowIso = () => {
    const pp = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jerusalem', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }).formatToParts(new Date()).map((x) => [x.type, x.value]));
    return `${pp.year}-${pp.month}-${pp.day}T${pp.hour}:${pp.minute}`;
  };
  function checkout(fields = {}, opts = {}) {
    const data = load(), c = cartRead(), ls = lines(data, c);
    if (!ls.length) return { ok: false, msg: 'העגלה ריקה. הוסיפו מוצר אחד לפחות.' };
    /* בודקים שהמלאי עדיין מספיק (אולי השתנה במסך הניהול בינתיים) */
    const short = c.items.filter((i) => { const x = product(i.id, data); return x && i.qty > num(x.stock); });
    if (short.length) {
      c.items = c.items.map((i) => { const x = product(i.id, data); return x ? { id: i.id, qty: Math.min(i.qty, Math.max(0, num(x.stock))) } : null; }).filter((i) => i && i.qty > 0);
      cartWrite(c);
      return { ok: false, msg: `המלאי השתנה: ${short.map((i) => product(i.id, data).name).join(', ')}. עדכנו את העגלה ונסו שוב.` };
    }
    const t = totals(data, c);
    const order = {
      id: orderId(data.nextOrder), date: nowIso(),
      name: String(fields.name || '').trim() || 'לקוח לדוגמה', phone: String(fields.phone || '').trim() || '050-0000000',
      city: c.delivery === 'pickup' ? '' : String(fields.city || '').trim(), address: c.delivery === 'pickup' ? '' : String(fields.address || '').trim(),
      delivery: c.delivery, note: String(fields.notes || '').trim(),
      items: ls.map((l) => ({ id: l.id, name: l.product.name, qty: l.qty, price: num(l.product.price) })),
      subtotal: t.subtotal, shipping: t.shipping, total: t.total, status: 'חדשה', demo: true,
    };
    for (const l of ls) { const x = product(l.id, data); x.stock = Math.max(0, num(x.stock) - l.qty); }
    data.orders.push(order); data.nextOrder = num(data.nextOrder, DEFAULT.nextOrder) + 1;
    write(KEY, Object.assign({}, data, { updated: Date.now() }));
    write(CART_KEY, { items: [], delivery: c.delivery });
    render();
    const msg = `תודה! הזמנה ${order.id} נקלטה. זו חנות לדוגמה: לא בוצע תשלום, ושום דבר לא יישלח.`;
    freshResult = true;
    if (!opts.quiet) announce(msg);
    emit('gargar:order', { order });
    return { ok: true, order, msg };
  }

  /* ---------- האזנה אחת לכל העמוד ---------- */
  function onClick(e) {
    const t = e.target.closest('[data-add], [data-inc], [data-dec], [data-remove], [data-shop-filter], [data-cart-toggle], [data-cart-close], button[data-checkout]');
    const pn = panel();
    /* לחיצה על הרקע של החלון: רק כשהלחיצה מחוץ למלבן של החלון עצמו (לחיצה על שטח ריק בתוך החלון לא סוגרת) */
    if (pn && pn.tagName === 'DIALOG' && pn.open && e.target === pn) {
      const r = pn.getBoundingClientRect();
      const inside = e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e.clientY <= r.bottom;
      if (!inside && (e.clientX || e.clientY)) { closeCart(); return; }
    }
    if (!t) return;
    if (t.matches('[data-add]')) {
      e.preventDefault();
      const card = t.closest('[data-product]'), qi = card && card.querySelector('input[data-qty]');
      const r = cart.add(t.dataset.add, t.dataset.qty || (qi ? qi.value : 1));
      if (r.ok) {
        all('[data-checkout-result]').forEach((x) => { x.hidden = true; });
        t.classList.add('is-added'); setTimeout(() => t.classList.remove('is-added'), 1200);
        all('[data-cart-toast]').forEach((toast) => { toast.textContent = r.msg; toast.hidden = false; toast.classList.add('is-show'); clearTimeout(toast._t); toast._t = setTimeout(() => { toast.classList.remove('is-show'); toast.hidden = true; }, 2500); });
        if (pn && 'openOnAdd' in pn.dataset && !isOpen(pn)) openCart(t);
      }
    } else if (t.matches('[data-inc]')) { const it = cartRead().items.find((i) => i.id === t.dataset.inc); cart.set(t.dataset.inc, (it ? it.qty : 0) + 1); }
    else if (t.matches('[data-dec]')) { const it = cartRead().items.find((i) => i.id === t.dataset.dec); cart.set(t.dataset.dec, (it ? it.qty : 1) - 1); }
    else if (t.matches('[data-remove]')) cart.remove(t.dataset.remove);
    else if (t.matches('[data-shop-filter]')) {
      const g = filterGrid(t); if (!g) return;
      g.dataset.cat = t.dataset.shopFilter; const n = renderGrid(g, load(), true); render();
      const c = catOf(t.dataset.shopFilter); announce(`מוצגים ${n} מוצרים${c ? `: ${c.name}` : ''}`);
    } else if (t.matches('[data-cart-toggle]')) { e.preventDefault(); isOpen(pn) ? closeCart() : openCart(t); }
    else if (t.matches('[data-cart-close]')) {
      /* קישור כמו <a href="#shop" data-cart-close> סוגר את המגירה וממשיך אל העוגן, בלי להחזיר את הפוקוס לכפתור */
      const hash = t.tagName === 'A' && (t.getAttribute('href') || '').startsWith('#');
      if (!hash) e.preventDefault();
      closeCart(hash);
    }
    else if (t.matches('button[data-checkout]')) { e.preventDefault(); showResult(checkout({}, { quiet: true })); }
  }
  function showResult(r) {
    const els = all('[data-checkout-result]');
    els.forEach((e) => { e.textContent = r.msg; e.hidden = false; e.dataset.ok = String(r.ok); });
    /* אם התוצאה עצמה היא אזור הודעות (role="status"), היא כבר מוקראת. אחרת מודיעים מכאן */
    if (!els.some((e) => e.matches('[role="status"], [role="alert"], [aria-live]'))) announce(r.msg);
  }
  function onSubmit(e) {
    const f = e.target.closest('form[data-checkout]'); if (!f) return;
    e.preventDefault();
    all('[aria-invalid]', f).forEach((x) => x.removeAttribute('aria-invalid'));
    if (!f.checkValidity()) {
      const bad = all('input, select, textarea', f).filter((x) => !x.disabled && !x.checkValidity());
      bad.forEach((x) => x.setAttribute('aria-invalid', 'true'));
      f.reportValidity(); if (bad[0]) bad[0].focus();
      announce('חסרים פרטים בטופס ההזמנה.');
      return;
    }
    const fd = new FormData(f), fields = {};
    for (const [k, v] of fd.entries()) fields[k] = v;
    const r = checkout(fields, { quiet: true });
    showResult(r);
    if (r.ok) f.reset();
    render();
  }
  function onChange(e) {
    const i = e.target;
    if (i.matches && i.matches('input[name="delivery"]')) cart.setDelivery(i.value);
  }
  function onKey(e) { if (e.key === 'Escape') { const pn = panel(); if (pn && pn.tagName !== 'DIALOG' && isOpen(pn)) closeCart(); } }

  window.Gargar = { KEY, CART_KEY, IMG, STATUSES, FLOW, DEFAULT, load, save, reset, cart, totals, lines: () => lines(), render, checkout, product: (id) => product(id), category: (k) => catOf(k),
    get lastError() { return lastError; },
    format, img, stockText, openCart, closeCart, announce, today, lastGrind,
    groundOf: (x) => groundOf(typeof x === 'string' ? product(x) : x), groundText: (x) => groundText(typeof x === 'string' ? product(x) : x),
    flow: (o) => FLOW[o && o.delivery === 'pickup' ? 'pickup' : 'ship'],
    /* עזרים למסך הניהול */
    updateProduct(id, patch) { const d = load(), x = product(id, d); if (!x) return false; Object.assign(x, patch); return save(d); },
    setStock(id, n) { return window.Gargar.updateProduct(id, { stock: Math.max(0, Math.floor(num(n))) }); },
    /* "נטחן היום": מוצר אחד, או כל המוצרים בקטגוריה (ברירת מחדל: כל התבלינים). date: 'YYYY-MM-DD', ברירת מחדל היום */
    grind(id, date) { const d = date && ISO.test(date) ? date : today(); return window.Gargar.updateProduct(id, { ground: d }); },
    grindAll(date, cat = 'spices') { const d0 = load(), d = date && ISO.test(date) ? date : today(); let n = 0; d0.products.forEach((x) => { if (!cat || cat === 'all' || x.cat === cat) { x.ground = d; n++; } }); save(d0); return n; },
    setOrderStatus(oid, status) { const d = load(), o = d.orders.find((x) => x.id === oid); if (!o || !STATUSES.includes(status)) return false; o.status = status; return save(d); },
    stats() { const d = load(), vis = visible(d); return { products: vis.length, soldOut: vis.filter((x) => num(x.stock) <= 0), low: vis.filter((x) => num(x.stock) > 0 && num(x.stock) <= 5),
      orders: d.orders.length, open: d.orders.filter((o) => !DONE.includes(o.status)).length, revenue: d.orders.filter((o) => o.status !== 'בוטלה').reduce((s, o) => s + num(o.total), 0) }; },
  };

  document.addEventListener('click', onClick);
  document.addEventListener('submit', onSubmit);
  document.addEventListener('change', onChange);
  document.addEventListener('keydown', onKey);
  document.addEventListener('close', (e) => { if (e.target === panel()) { freshResult = false; document.documentElement.classList.remove('cart-open'); all('[data-cart-toggle]').forEach((b) => b.setAttribute('aria-expanded', 'false')); if (opener) returnFocus(); } }, true);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render); else render();
  /* שינוי במסך הניהול או בעגלה בלשונית אחרת מתעדכן כאן מיד */
  window.addEventListener('storage', (e) => { if (e.key === KEY || e.key === CART_KEY || e.key === null) render(); });
})();
