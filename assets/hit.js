/* YYG: מונה כניסות פרטי לעמודים שאין בהם את הסקריפט הראשי (מדריכים, שירותים, שאלונים). בלי עוגיות */
(() => {
  if (location.hostname !== 'yygweb.com' || !navigator.sendBeacon) return;
  const q = new URLSearchParams(location.search);
  try { navigator.sendBeacon('/hit.php', new Blob([JSON.stringify({ e: 'view', p: location.pathname, r: document.referrer, s: q.get('src') || q.get('utm_source') || '' })], { type: 'application/json' })); } catch (e) { /* בלי מונה */ }
})();
