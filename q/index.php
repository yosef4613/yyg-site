<?php
/*
 * YYG Web Design: דף הצעת המחיר שהלקוח רואה. https://yygweb.com/q/?t=...
 * הקישור פרטי (קוד אקראי של 24 תווים). הלקוח רואה את ההצעה, מדפיס או שומר כ־PDF, ומאשר בלחיצה.
 * כשהלקוח פותח את ההצעה בפעם הראשונה, וכשהוא מאשר, נשלח אליי מייל.
 * הצעה עם lang=en (נקבע בבונה ההצעות) מוצגת ללקוח באנגלית, משמאל לימין. המיילים אליי נשארים בעברית.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/admin/_lib.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: no-referrer');
header('X-Frame-Options: DENY');

$quotes = quotes_load();
$token = (string) ($_GET['t'] ?? '');
$num = quote_find_by_token($quotes, $token);
$doc = static function (string $title, string $body, bool $en = false): void {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="' . ($en ? 'en' : 'he') . '" dir="' . ($en ? 'ltr' : 'rtl') . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><link rel="icon" href="/assets/img/yyg-icon-96.png" type="image/png"><title>' . h($title) . '</title><style>' . QDOC_CSS . '</style></head><body>' . $body . '</body></html>';
    exit;
};
const QDOC_CSS = '@font-face{font-family:"Plex";font-weight:400;src:url(/assets/fonts/plex-400-hebrew.woff2) format("woff2");unicode-range:U+0590-05FF,U+200C-2010,U+20AA,U+FB1D-FB4F}
@font-face{font-family:"Plex";font-weight:400;src:url(/assets/fonts/plex-400-latin.woff2) format("woff2");unicode-range:U+0000-00FF,U+2013-2014,U+2022}
@font-face{font-family:"Plex";font-weight:700;src:url(/assets/fonts/plex-700-hebrew.woff2) format("woff2");unicode-range:U+0590-05FF,U+200C-2010,U+20AA,U+FB1D-FB4F}
@font-face{font-family:"Plex";font-weight:700;src:url(/assets/fonts/plex-700-latin.woff2) format("woff2");unicode-range:U+0000-00FF,U+2013-2014,U+2022}
:root{--ink:#151920;--muted:#5A6472;--line:#DCE0E6;--soft:#F4F5F7;--gold:#B7860F;--ok:#1D7448}
*{box-sizing:border-box}body{margin:0;background:#E9ECF0;color:var(--ink);font:16px/1.6 "Plex",Arial,sans-serif;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.paper{max-width:820px;margin:24px auto;background:#fff;border-radius:14px;box-shadow:0 10px 40px -20px rgba(0,0,0,.35);padding:clamp(20px,5vw,48px)}
.head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;padding-bottom:18px;border-bottom:3px solid var(--ink)}
.brand{display:flex;align-items:center;gap:12px}.brand img{width:64px;height:auto;background:#151920;border-radius:12px;padding:6px}.brand b{display:block;font-size:1.15rem}.brand span{color:var(--muted);font-size:.92rem}
.contact{font-size:.9rem;color:var(--muted);text-align:left;direction:ltr}.contact a{color:var(--ink);text-decoration:none}
h1{font-size:1.9rem;margin:22px 0 2px}.meta{display:flex;flex-wrap:wrap;gap:4px 20px;color:var(--muted);font-size:.95rem}
.for{margin-top:14px;font-size:1.05rem}.title{margin-top:4px;font-weight:700;font-size:1.15rem}
table{width:100%;border-collapse:collapse;margin-top:18px;font-variant-numeric:tabular-nums}th,td{padding:10px 8px;border-bottom:1px solid var(--line);text-align:start;vertical-align:top}
th{font-size:.82rem;color:var(--muted);font-weight:700;background:var(--soft)}td.n,th.n{text-align:end;white-space:nowrap}
tr.total td{border-bottom:0;border-top:2px solid var(--ink);font-size:1.25rem;font-weight:700;padding-top:14px}
.vat{color:var(--muted);font-size:.9rem;margin-top:4px}
h2{font-size:1.15rem;margin:28px 0 8px}
.terms{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:10px}.terms div{background:var(--soft);border-radius:10px;padding:10px 12px}.terms b{display:block;font-size:1.2rem}.terms span{color:var(--muted);font-size:.85rem}
ul{margin:8px 0 0;padding-inline-start:22px}li{margin:4px 0}
.notes{white-space:pre-wrap;background:var(--soft);border-radius:10px;padding:12px 14px}
.fine{color:var(--muted);font-size:.88rem}.fine li{margin:3px 0}
.approve{margin-top:28px;border:2px solid var(--ink);border-radius:14px;padding:18px 20px}.approve.done{border-color:var(--ok);background:#EEF8F1}
.approve form{display:grid;gap:10px;margin-top:10px}.approve input[type=text]{min-height:46px;padding:8px 12px;border:1.5px solid #9AA3AF;border-radius:10px;font:inherit}
.approve label.chk{display:flex;gap:10px;align-items:flex-start}.approve label.chk input{width:20px;height:20px;margin-top:3px;accent-color:#151920}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:8px 20px;border-radius:12px;border:1.5px solid var(--ink);background:#fff;color:var(--ink);font:700 1rem "Plex",Arial,sans-serif;text-decoration:none;cursor:pointer}
.btn.main{background:var(--ink);color:#fff}.acts{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
.err{color:#AF2A20;font-weight:700}.hp{position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;clip:rect(0 0 0 0);pointer-events:none}
html[dir=ltr] .contact{text-align:right}
@media (max-width:640px){.terms{grid-template-columns:1fr 1fr}.contact{text-align:right;direction:rtl}html[dir=ltr] .contact{text-align:left;direction:ltr}th:nth-child(2),td:nth-child(2){display:none}}
@media print{body{background:#fff}.paper{box-shadow:none;margin:0;max-width:none;padding:0}.no-print{display:none!important}.approve form{display:none}a{text-decoration:none}}';

if ($num === null) {
    http_response_code(404);
    $doc('הצעה לא נמצאה | YYG Web Design', '<main class="paper"><h1>ההצעה לא נמצאה</h1><p>ייתכן שהקישור לא הועתק עד הסוף, או שההצעה עודכנה. אפשר להתקשר ל־<a href="tel:0548540700">054-854-0700</a> ואשלח קישור חדש.</p>'
        . '<div lang="en" dir="ltr"><h1>Quote not found</h1><p>The link may not have been copied in full, or the quote was updated. Call <a href="tel:0548540700">054-854-0700</a> and I\'ll send you a new link.</p></div></main>');
}
$q = $quotes[$num];
$EN = ($q['lang'] ?? 'he') === 'en';
$L = static fn(string $he, string $en): string => $EN ? $en : $he;
$money = static fn($n): string => nis_lang($n, $EN);
$isAdmin = adm_authed();
$today = (new DateTime('now', tz()))->format('Y-m-d');
$approved = $q['approved'] ?? null;
$expired = !$approved && ($q['valid_until'] ?? '9999-12-31') < $today;
$adminLink = 'https://yygweb.com/admin/quote.php?id=' . rawurlencode($num);

/* אישור ההצעה */
$err = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['do'] ?? '') === 'approve') {
    $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 120, 'UTF-8');
    if (!empty($_POST['website'])) { header('Location: /q/?t=' . $token, true, 303); exit; }
    if ($approved) { header('Location: /q/?t=' . $token . '#approve', true, 303); exit; }
    if ($expired) $err = $L('תוקף ההצעה עבר. אפשר להתקשר ואשלח הצעה מעודכנת.', 'This quote has expired. Call me and I\'ll send you an updated quote.');
    elseif (mb_strlen($name, 'UTF-8') < 2) $err = $L('נא לכתוב שם מלא.', 'Please enter your full name.');
    elseif (empty($_POST['agree'])) $err = $L('נא לסמן שקראתם ואתם מאשרים את ההצעה.', 'Please tick the box to confirm you\'ve read and approve the quote.');
    else {
        $quotes = quotes_load();   // טוענים מחדש, למקרה שמשהו השתנה בינתיים
        $quotes[$num]['approved'] = ['name' => $name, 't' => time(), 'total' => quote_totals($quotes[$num])['total']];
        $quotes[$num]['status'] = 'approved';
        $quotes[$num]['updated'] = time();
        quotes_save($quotes);
        $t = quote_totals($quotes[$num]);
        notify_me('ההצעה ' . $num . ' אושרה: ' . $name,
            '<div dir="rtl" style="font-family:Arial,sans-serif;font-size:16px;line-height:1.6"><h2 style="margin:0 0 8px">ההצעה אושרה</h2><p><b>' . h($name) . '</b> אישר/ה את הצעת המחיר ' . h($num) . ' (' . h($q['client']['name'] ?? '') . ') על סך ' . h(nis($t['total'])) . '.</p><p><a href="' . h($adminLink) . '">להצעה בדף הניהול</a></p><p>הצעד הבא: לתאם את התשלום הראשון ואת תחילת העבודה.</p></div>',
            "ההצעה $num אושרה על ידי $name, על סך " . nis($t['total']) . ".\n$adminLink");
        header('Location: /q/?t=' . $token . '&ok=1#approve', true, 303); exit;
    }
}

/* ספירת צפיות של הלקוח (לא שלי), ומייל בפתיחה הראשונה */
$ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
if (!$isAdmin && !isset($_GET['ok']) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && $ua !== '' && !preg_match('/bot|crawl|spider|preview|whatsapp|telegram|facebookexternalhit|slack|headless|curl|python/i', $ua)) {
    $first = empty($q['views']);
    $quotes[$num]['views'] = (int) ($q['views'] ?? 0) + 1;
    $quotes[$num]['last_view'] = time();
    quotes_save($quotes);
    if ($first) notify_me('נפתחה הצעת המחיר ' . $num . ': ' . ($q['client']['name'] ?? ''),
        '<div dir="rtl" style="font-family:Arial,sans-serif;font-size:16px;line-height:1.6"><p>' . h($q['client']['name'] ?? '') . ' פתח/ה עכשיו את הצעת המחיר ' . h($num) . '. זה זמן טוב להתקשר.</p><p><a href="' . h($adminLink) . '">להצעה בדף הניהול</a></p></div>',
        ($q['client']['name'] ?? '') . " פתח/ה עכשיו את הצעת המחיר $num.\n$adminLink");
}

$t = quote_totals($q);
$terms = (array) ($q['terms'] ?? []);
$pay = (array) ($q['payment'] ?? []);
$first10 = isset($pay[0]) && (int) $pay[0]['pct'] === 10;
$created = fmt_time((int) ($q['created'] ?? time()), 'd/m/Y');
$validTxt = (new DateTime((string) ($q['valid_until'] ?? $today)))->format('d/m/Y');
$incl = array_values(array_filter(array_map('trim', explode("\n", (string) ($q['includes'] ?? ''))), 'strlen'));

ob_start(); ?>
<main class="paper">
 <?php if ($isAdmin): ?><p class="no-print" lang="he" dir="rtl" style="background:#FBF0DE;border-radius:10px;padding:8px 12px">זו התצוגה שהלקוח רואה. הצפיות שלך לא נספרות. <a href="<?= h($adminLink) ?>">לעריכה</a></p><?php endif; ?>
 <header class="head">
  <div class="brand"><img src="/assets/img/yyg-logo-160.webp" width="160" height="122" alt="YYG Web Design"><div><b><?= $L('YYG Web Design · יהודה גורדון', 'YYG Web Design · Yehuda Gordon') ?></b><span><?= $L('עיצוב ובניית אתרים, מיתוג וקידום', 'Web design and development, branding and SEO') ?></span></div></div>
  <div class="contact"><a href="tel:0548540700">054-854-0700</a><br><a href="mailto:yehuda@yygweb.com">yehuda@yygweb.com</a><br><a href="https://yygweb.com/">yygweb.com</a></div>
 </header>

 <h1><?= $L('הצעת מחיר', 'Quote') ?></h1>
 <div class="meta"><span><?= $L('מספר', 'No.') ?> <?= h($num) ?></span><span><?= $L('תאריך', 'Date') ?> <?= h($created) ?></span><span><?= $L('בתוקף עד', 'Valid until') ?> <?= h($validTxt) ?></span></div>
 <p class="for"><?= $L('עבור:', 'For:') ?> <b><?= h($q['client']['name'] ?? '') ?></b><?= !empty($q['client']['business']) ? ' · ' . h($q['client']['business']) : '' ?></p>
 <?php if (!empty($q['title'])): ?><p class="title"><?= h($q['title']) ?></p><?php endif; ?>

 <table>
  <thead><tr><th><?= $L('מה', 'Item') ?></th><th class="n"><?= $L('כמות', 'Qty') ?></th><th class="n"><?= $L('מחיר', 'Price') ?></th></tr></thead>
  <tbody>
  <?php foreach ((array) ($q['items'] ?? []) as $it): ?>
   <tr><td><?= h($it['label']) ?><?= !empty($it['desc']) ? '<br><small class="fine">' . h($it['desc']) . '</small>' : '' ?></td><td class="n"><?= (int) $it['qty'] ?></td><td class="n"><?= h($money((float) $it['price'] * (int) $it['qty'])) ?></td></tr>
  <?php endforeach; ?>
  <?php if ($t['discount'] > 0): ?><tr><td><?= h(($q['discount']['label'] ?? '') ?: $L('הנחה', 'Discount')) ?></td><td class="n"></td><td class="n">−<?= h($money($t['discount'])) ?></td></tr><?php endif; ?>
   <tr class="total"><td><?= $L('סה״כ', 'Total') ?></td><td class="n"></td><td class="n"><?= h($money($t['total'])) ?></td></tr>
  </tbody>
 </table>
 <p class="vat"><?= $L('המחיר סופי, לא מוסיפים מע״מ. תשלום חד־פעמי.', 'Final price, no VAT added. A one-time payment.') ?></p>

 <?php if (array_filter($terms)): ?>
 <h2><?= $L('התנאים', 'Terms') ?></h2>
 <div class="terms">
  <?php if (!empty($terms['designs'])): ?><div><b><?= (int) $terms['designs'] ?></b><span><?= (int) $terms['designs'] === 1 ? $L('כיוון עיצוב', 'design direction') : $L('כיווני עיצוב מלאים', 'complete design directions') ?></span></div><?php endif; ?>
  <?php if (!empty($terms['revisions'])): ?><div><b><?= (int) $terms['revisions'] ?></b><span><?= (int) $terms['revisions'] === 1 ? $L('סבב תיקונים', 'revision round') : $L('סבבי תיקונים', 'revision rounds') ?></span></div><?php endif; ?>
  <?php if (!empty($terms['days'])): ?><div><b><?= $L('עד', 'Up to') ?> <?= (int) $terms['days'] ?></b><span><?= $L('ימי עבודה לאספקה', 'business days to deliver') ?></span></div><?php endif; ?>
  <?php if (!empty($terms['support'])): ?><div><b><?= (int) $terms['support'] ?></b><span><?= (int) $terms['support'] === 1 ? $L('חודש ליווי אחרי ההשקה', 'month of support after launch') : $L('חודשי ליווי אחרי ההשקה', 'months of support after launch') ?></span></div><?php endif; ?>
 </div>
 <?php endif; ?>

 <?php if ($incl): ?><h2><?= $L('מה כלול', 'What\'s included') ?></h2><ul><?php foreach ($incl as $l): ?><li><?= h($l) ?></li><?php endforeach; ?></ul><?php endif; ?>

 <?php if ($pay): ?>
 <h2><?= $L('שלבי תשלום', 'Payment stages') ?></h2>
 <table><thead><tr><th><?= $L('מתי', 'When') ?></th><th class="n"><?= $L('אחוז', 'Share') ?></th><th class="n"><?= $L('סכום', 'Amount') ?></th></tr></thead><tbody>
 <?php foreach ($pay as $p): ?><tr><td><?= h($p['when']) ?></td><td class="n"><?= (int) $p['pct'] ?>%</td><td class="n"><?= h($money($t['total'] * (int) $p['pct'] / 100)) ?></td></tr><?php endforeach; ?>
 </tbody></table>
 <?php endif; ?>

 <?php if (!empty($q['recurring'])): ?>
 <h2><?= $L('תשלומים קבועים', 'Recurring payments') ?> <small class="fine"><?= $L('לא כלולים בסכום שלמעלה', 'not included in the total above') ?></small></h2>
 <ul><?php foreach ($q['recurring'] as $r): ?><li><?= h($r['label']) ?>: <b><?= $r['price'] ? h($money($r['price'])) . ($r['per'] === 'month' ? $L(' לחודש', ' a month') : $L(' לשנה', ' a year')) : $L('לפי הצעה נפרדת', 'by separate quote') ?></b><?= $r['note'] ? ' <span class="fine">(' . h($r['note']) . ')</span>' : '' ?></li><?php endforeach; ?></ul>
 <?php endif; ?>

 <?php if (!empty($q['notes'])): ?><h2><?= $L('הערות', 'Notes') ?></h2><p class="notes"><?= h($q['notes']) ?></p><?php endif; ?>

 <h2><?= $L('חשוב לדעת', 'Good to know') ?></h2>
 <ul class="fine">
  <li><?= $L('האתר, הדומיין והתוכן רשומים על שמכם.', 'The website, the domain and the content are registered in your name.') ?></li>
  <?php if (!empty($terms['days'])): ?><li><?= $L('זמן האספקה נספר מהיום שמקבלים את כל החומרים (טקסטים, תמונות ולוגו), ולא כולל ימים שבהם מחכים לתשובה שלכם.', 'The delivery time is counted from the day I receive all the materials (text, photos and logo), not including days spent waiting for your reply.') ?></li><?php endif; ?>
  <?php if ($first10): ?><li><?= $L('ה־10% הם חלק מהמחיר, לא תוספת. אם אחרי העיצובים מחליטים לא להמשיך, הם מכסים את עבודת העיצוב, ולא משלמים יותר.', 'The 10% is part of the price, not an extra. If you decide not to go ahead after the designs, it covers the design work, and you pay nothing more.') ?></li><?php endif; ?>
  <li><?= $L('עבודה שלא כתובה בהצעה מתומחרת מראש, ומתבצעת רק באישור שלכם.', 'Any work not listed in the quote is priced in advance, and done only with your approval.') ?></li>
  <li><?= $L('ההצעה בתוקף עד', 'This quote is valid until') ?> <?= h($validTxt) ?>.</li>
 </ul>

 <section class="approve<?= $approved ? ' done' : '' ?>" id="approve">
 <?php if ($approved): ?>
  <h2 style="margin:0"><?= $L('ההצעה אושרה', 'Quote approved') ?></h2>
  <p><?= $L('אושרה על ידי', 'Approved by') ?> <b><?= h($approved['name']) ?></b> <?= $L('ב־', 'on ') ?><?= h(fmt_time((int) $approved['t'])) ?>. <?= isset($_GET['ok']) ? $L('תודה! אחזור אליכם בהקדם לתיאום התשלום הראשון ותחילת העבודה.', 'Thank you! I\'ll be in touch soon to arrange the first payment and the start of the work.') : '' ?></p>
 <?php elseif ($expired): ?>
  <h2 style="margin:0"><?= $L('תוקף ההצעה עבר', 'This quote has expired') ?></h2>
  <p><?= $L('אפשר להתקשר ל־', 'Call me at ') ?><a href="tel:0548540700">054-854-0700</a><?= $L(', ואשלח הצעה מעודכנת.', ', and I\'ll send you an updated quote.') ?></p>
 <?php else: ?>
  <h2 style="margin:0"><?= $L('מאשרים?', 'Ready to approve?') ?></h2>
  <p class="fine"><?= $L('האישור לא גובה כסף. אחריו אחזור אליכם לתיאום התשלום הראשון ותחילת העבודה.', 'Approving doesn\'t charge anything. After it, I\'ll be in touch to arrange the first payment and the start of the work.') ?></p>
  <?php if ($err): ?><p class="err" role="alert"><?= h($err) ?></p><?php endif; ?>
  <form method="post" action="/q/?t=<?= h($token) ?>#approve">
   <input type="hidden" name="do" value="approve"><input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
   <label><?= $L('שם מלא', 'Full name') ?><br><input type="text" name="name" autocomplete="name" required value="<?= h($_POST['name'] ?? '') ?>"></label>
   <label class="chk"><input type="checkbox" name="agree" value="1" required><span><?= $L('קראתי את ההצעה ואני מאשר/ת אותה', 'I\'ve read the quote and I approve it') ?></span></label>
   <button class="btn main" type="submit"><?= $L('אישור ההצעה', 'Approve the quote') ?></button>
  </form>
 <?php endif; ?>
 </section>

 <div class="acts no-print">
  <button class="btn" type="button" onclick="window.print()"><?= $L('הדפסה או שמירה כ־PDF', 'Print or save as PDF') ?></button>
  <a class="btn" href="tel:0548540700"><?= $L('שאלה?', 'Questions?') ?> 054-854-0700</a>
 </div>
</main>
<?php
$doc($L('הצעת מחיר ', 'Quote ') . $num . ' | YYG Web Design', (string) ob_get_clean(), $EN);
