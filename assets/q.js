/* YYG Web Design: קישורים עם פרטי מעקב (מאיזה כפתור הגיעו לשאלון, איזה מסלול כבר מסומן).
   הפרטים יושבים ב־data-q ונוספים לכתובת רק ברגע הלחיצה, כדי שגוגל יראה כתובות נקיות בלי עותקים של אותו דף. */
(function () {
  var add = function (e) {
    var a = e.target.closest && e.target.closest('a[data-q]');
    if (!a || a.getAttribute('href').indexOf('?') >= 0) return;
    var h = a.getAttribute('href'), i = h.indexOf('#');
    a.setAttribute('href', (i < 0 ? h : h.slice(0, i)) + '?' + a.getAttribute('data-q') + (i < 0 ? '' : h.slice(i)));
  };
  document.addEventListener('click', add, true);
  document.addEventListener('auxclick', add, true);
  document.addEventListener('contextmenu', add, true);
})();
