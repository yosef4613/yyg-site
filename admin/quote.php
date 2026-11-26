<?php
/*
 * YYG Web Design: בונה הצעות מחיר. https://yygweb.com/admin/quote.php
 * ?lead=מזהה  הצעה חדשה עם פרטי הפנייה
 * ?id=מספר    עריכת הצעה קיימת
 * ?dup=מספר   הצעה חדשה שמועתקת מהצעה קיימת
 * כל הצעה מקבלת קישור פרטי ללקוח (/q/?t=...), שבו הוא רואה את ההצעה, מדפיס אותה ומאשר בלחיצה.
 */
declare(strict_types=1);
require __DIR__ . '/_lib.php';
require __DIR__ . '/_intake.php';
adm_require();

$quotes = quotes_load();
$leads = leads_load();

/* שאלון אפיון → טיוטת הצעה: admin/_intake.php (משותף גם לשאלון עצמו, להערכה המיידית) */
$csrf = adm_csrf();
$JS = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

/* ---------- שמירה ופעולות ---------- */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) { http_response_code(400); exit('הטופס פג תוקף. חזרו אחורה, רעננו ונסו שוב.'); }
    $do = (string) ($_POST['do'] ?? '');
    $num = (string) ($_POST['num'] ?? '');
    if ($do === 'status' && isset($quotes[$num]) && isset(Q_STATUSES[$_POST['st'] ?? ''])) {
        $quotes[$num]['status'] = (string) $_POST['st'];
        $quotes[$num]['updated'] = time();
        quotes_save($quotes);
        header('Location: /admin/quote.php?id=' . rawurlencode($num) . '&saved=1', true, 303); exit;
    }
    if (in_array($do, ['email_quote', 'email_feedback'], true) && isset($quotes[$num])) {
        $q = $quotes[$num];
        $to = (string) ($q['client']['email'] ?? '');
        $name = (string) ($q['client']['name'] ?? '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { header('Location: /admin/quote.php?id=' . rawurlencode($num) . '&mailerr=1', true, 303); exit; }
        $en = ($q['lang'] ?? 'he') === 'en';   // הצעה באנגלית: המייל ללקוח באנגלית
        if ($do === 'email_quote' && $en) {
            $valid = (new DateTime((string) ($q['valid_until'] ?? 'now')))->format('d/m/Y');
            $t = quote_totals($q);
            $body = '<p>Hi ' . h($name) . ',</p><p>Here is your quote' . ($q['title'] ? ': <b>' . h($q['title']) . '</b>' : '') . ', for a total of ' . h(nis_lang($t['total'], true)) . '. Final price, no VAT added.</p>'
                . '<p>At the link you can see all the details, print it or save it as a PDF, and approve it with one click.</p>' . mail_button(quote_url($q), 'View the quote')
                . '<p style="color:#5A6472;font-size:14px">The quote is valid until ' . h($valid) . '. Questions? Just reply to this email or call 054-854-0700.</p><p>Yehuda</p>';
            $ok = mail_send($to, 'Your quote from YYG Web Design' . ($q['title'] ? ': ' . $q['title'] : ''), mail_wrap('Quote ' . $num, $body, '', true), "Hi $name,\nYour quote: " . quote_url($q) . "\nYehuda, 054-854-0700", mail_from(true));
            $what = 'ההצעה';
        } elseif ($do === 'email_quote') {
            $valid = (new DateTime((string) ($q['valid_until'] ?? 'now')))->format('d/m/Y');
            $t = quote_totals($q);
            $body = '<p>שלום ' . h($name) . ',</p><p>מצורפת הצעת המחיר' . ($q['title'] ? ': <b>' . h($q['title']) . '</b>' : '') . ', על סך ' . h(nis($t['total'])) . '. המחיר סופי, לא מוסיפים מע״מ.</p>'
                . '<p>בקישור אפשר לראות את כל הפרטים, להדפיס או לשמור כ־PDF, ולאשר בלחיצה.</p>' . mail_button(quote_url($q), 'לצפייה בהצעה')
                . '<p style="color:#5A6472;font-size:14px">ההצעה בתוקף עד ' . h($valid) . '. שאלות? אפשר להשיב למייל הזה או להתקשר ל־054-854-0700.</p><p>יהודה</p>';
            $ok = mail_send($to, 'הצעת מחיר מ־YYG Web Design' . ($q['title'] ? ': ' . $q['title'] : ''), mail_wrap('הצעת מחיר ' . $num, $body), "שלום $name,
הצעת המחיר: " . quote_url($q) . "
יהודה, 054-854-0700");
            $what = 'ההצעה';
        } elseif ($en) {
            $link = 'https://yygweb.com/en/feedback/?' . http_build_query(['name' => $name, 'phone' => $q['client']['phone'] ?? '', 'email' => $to, 'business' => $q['client']['business'] ?? '', 'quote' => $num]);
            $body = '<p>Hi ' . h($name) . ',</p><p>Once you\'ve gone through the 3 designs, I\'d love some short feedback: what you liked, what you didn\'t, and which direction to take. It takes about 5 minutes, and your details are already filled in.</p>'
                . mail_button($link, 'To the feedback form') . '<p style="color:#5A6472;font-size:14px">Prefer to talk? 054-854-0700.</p><p>Yehuda</p>';
            $ok = mail_send($to, 'Feedback on your website designs', mail_wrap('Feedback on the designs', $body, '', true), "Hi $name,\nThe feedback form: $link\nYehuda, 054-854-0700", mail_from(true));
            $what = 'שאלון המשוב';
        } else {
            $link = 'https://yygweb.com/feedback/?' . http_build_query(['name' => $name, 'phone' => $q['client']['phone'] ?? '', 'email' => $to, 'business' => $q['client']['business'] ?? '', 'quote' => $num]);
            $body = '<p>שלום ' . h($name) . ',</p><p>אחרי שתעברו על 3 העיצובים, אשמח למשוב קצר: מה אהבתם, מה פחות, ובאיזה כיוון ממשיכים. זה לוקח כ־5 דקות, והפרטים שלכם כבר ממולאים.</p>'
                . mail_button($link, 'לשאלון המשוב') . '<p style="color:#5A6472;font-size:14px">מעדיפים לדבר? 054-854-0700.</p><p>יהודה</p>';
            $ok = mail_send($to, 'משוב על העיצובים של האתר שלכם', mail_wrap('משוב על העיצובים', $body), "שלום $name,
שאלון המשוב: $link
יהודה, 054-854-0700");
            $what = 'שאלון המשוב';
        }
        if ($ok) {
            $quotes = quotes_load();
            $quotes[$num]['sent_log'][] = ['t' => time(), 'what' => $what, 'to' => $to];
            if (($quotes[$num]['status'] ?? 'draft') === 'draft' && $do === 'email_quote') $quotes[$num]['status'] = 'sent';
            quotes_save($quotes);
        }
        header('Location: /admin/quote.php?id=' . rawurlencode($num) . '&' . ($ok ? 'mailed=' . rawurlencode($what) : 'mailerr=2'), true, 303); exit;
    }
    if ($do === 'delete' && isset($quotes[$num])) {
        unset($quotes[$num]);
        quotes_save($quotes);
        header('Location: /admin/?saved=1#quotes', true, 303); exit;
    }
    if ($do === 'save') {
        $in = json_decode((string) ($_POST['data'] ?? ''), true);
        if (!is_array($in)) { http_response_code(400); exit('נתונים לא תקינים'); }
        $str = static fn($v, int $max): string => mb_substr(trim(str_replace("\0", '', is_scalar($v) ? (string) $v : '')), 0, $max, 'UTF-8');
        $num_ = static fn($v, float $min, float $max): float => max($min, min($max, is_numeric($v) ? (float) $v : 0.0));
        $items = [];
        foreach (array_slice((array) ($in['items'] ?? []), 0, 40) as $it) {
            $label = $str($it['label'] ?? '', 200);
            if ($label === '') continue;
            $items[] = ['label' => $label, 'desc' => $str($it['desc'] ?? '', 400), 'qty' => (int) $num_($it['qty'] ?? 1, 1, 999), 'price' => round($num_($it['price'] ?? 0, 0, 1000000))];
        }
        $recurring = [];
        foreach (array_slice((array) ($in['recurring'] ?? []), 0, 10) as $r) {
            $label = $str($r['label'] ?? '', 200);
            if ($label === '') continue;
            $recurring[] = ['label' => $label, 'price' => round($num_($r['price'] ?? 0, 0, 1000000)), 'per' => ($r['per'] ?? '') === 'month' ? 'month' : 'year', 'note' => $str($r['note'] ?? '', 120)];
        }
        $payment = [];
        foreach (array_slice((array) ($in['payment'] ?? []), 0, 6) as $p) $payment[] = ['pct' => round($num_($p['pct'] ?? 0, 0, 100)), 'when' => $str($p['when'] ?? '', 160)];
        $valid = (string) ($in['valid_until'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valid)) $valid = (new DateTime('+14 days', tz()))->format('Y-m-d');
        $email = $str($in['client']['email'] ?? '', 160);
        $q = [
            'client' => ['name' => $str($in['client']['name'] ?? '', 120), 'phone' => $str($in['client']['phone'] ?? '', 40), 'email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '', 'business' => $str($in['client']['business'] ?? '', 160)],
            'title' => $str($in['title'] ?? '', 200),
            'lang' => ($in['lang'] ?? '') === 'en' ? 'en' : 'he',   // שפת ההצעה: הדף /q/ והמיילים ללקוח
            'items' => $items,
            'recurring' => $recurring,
            'discount' => ['label' => $str($in['discount']['label'] ?? '', 120), 'amount' => round($num_($in['discount']['amount'] ?? 0, 0, 1000000))],
            'terms' => ['designs' => (int) $num_($in['terms']['designs'] ?? 0, 0, 20), 'revisions' => (int) $num_($in['terms']['revisions'] ?? 0, 0, 20), 'days' => (int) $num_($in['terms']['days'] ?? 0, 0, 365), 'support' => (int) $num_($in['terms']['support'] ?? 0, 0, 60)],
            'payment' => $payment,
            'valid_until' => $valid,
            'includes' => $str($in['includes'] ?? '', 3000),
            'notes' => $str($in['notes'] ?? '', 3000),
            'lead_id' => preg_match('/^[a-f0-9]{12}$/', (string) ($in['lead_id'] ?? '')) ? (string) $in['lead_id'] : '',
            'ui' => is_array($in['ui'] ?? null) && strlen(json_encode($in['ui'])) < 30000 ? $in['ui'] : [],
        ];
        if ($q['client']['name'] === '') { http_response_code(422); exit('חסר שם לקוח'); }
        if (!isset($quotes[$num])) {
            $num = quote_next_number($quotes);
            $q += ['token' => bin2hex(random_bytes(12)), 'created' => time(), 'status' => 'draft', 'views' => 0];
        } else {
            $q = $q + $quotes[$num];   // שומרים קישור, תאריך יצירה, סטטוס, צפיות ואישור
            if (($quotes[$num]['status'] ?? '') === 'approved') $q['changed_after_approval'] = true;
        }
        $q['updated'] = time();
        $quotes[$num] = $q;
        if (!quotes_save($quotes)) { http_response_code(500); exit('השמירה נכשלה'); }
        header('Location: /admin/quote.php?id=' . rawurlencode($num) . '&saved=1', true, 303); exit;
    }
    http_response_code(400); exit('פעולה לא מוכרת');
}

/* ---------- טעינה לעריכה ---------- */
$num = (string) ($_GET['id'] ?? '');
$q = $quotes[$num] ?? null;
if ($q === null) $num = '';
if ($q === null && isset($_GET['dup'], $quotes[(string) $_GET['dup']])) {
    $q = $quotes[(string) $_GET['dup']];
    unset($q['token'], $q['created'], $q['status'], $q['views'], $q['approved'], $q['last_view']);
}
$lead = null;
if ($q === null && isset($_GET['lead'], $leads[(string) $_GET['lead']])) $lead = $leads[(string) $_GET['lead']];
$formRec = null;
if ($q === null && $lead === null && isset($_GET['form'])) {
    foreach ((@file(data_dir() . '/forms.jsonl', FILE_IGNORE_NEW_LINES) ?: []) as $l) { $r = json_decode($l, true); if (is_array($r) && ($r['id'] ?? '') === (string) $_GET['form']) $formRec = $r; }
}
$init = $q ? (($q['ui'] ?? null) ?: null) : null;
$auto = $formRec ? intake_to_quote($formRec) : null;
if ($auto && ($formRec['lang'] ?? '') === 'en') {
    /* שאלון מהאתר באנגלית: השורות שנוספו ממנו נכתבות באנגלית, כי הלקוח יראה אותן בהצעה */
    $enLabel = static function (string $l): string {
        if ($l === 'הרשמה, חשבונות משתמש ורשימת תפוצה') return 'Sign-ups, user accounts and a mailing list';
        if (isset(EST_TODO_EN[$l])) return EST_TODO_EN[$l];
        foreach (['מסך עדכון קטן לבעל העסק' => 'A small update screen for the owner', 'שפה נוספת' => 'Another language', 'יכולת נוספת' => 'Another feature'] as $he => $en) if (str_starts_with($l, $he)) return $en . mb_substr($l, mb_strlen($he, 'UTF-8'), null, 'UTF-8');
        return $l;
    };
    foreach ($auto['custom'] as $i => $c) $auto['custom'][$i]['label'] = $enLabel((string) $c['label']);
}
$prefill = $formRec ? ['name' => $formRec['contact']['name'], 'phone' => $formRec['contact']['phone'], 'business' => $formRec['contact']['business'], 'email' => $formRec['contact']['email'],
    'plan' => '', 'lead_id' => '', 'auto' => $auto, 'lang' => ($formRec['lang'] ?? '') === 'en' ? 'en' : 'he'] : ($lead ? ['name' => $lead['name'] ?? '', 'phone' => $lead['phone'] ?? '', 'email' => $lead['email'] ?? '', 'business' => $lead['business'] ?? '', 'plan' => $lead['plan'] ?? '', 'lead_id' => (string) $lead['id'], 'lang' => ($lead['lang'] ?? '') === 'en' ? 'en' : 'he'] : null);
$totals = $q ? quote_totals($q) : null;

ob_start(); ?>
<?= adm_nav() ?>
<main class="wrap qb">
<p><a class="muted" href="/admin/#quotes">חזרה לרשימה</a></p>
<h1><?= $q && $num ? 'הצעת מחיר ' . h($num) : 'הצעת מחיר חדשה' ?></h1>
<?php if (isset($_GET['saved'])): ?><p class="flash">נשמר.</p><?php endif; ?>
<?php if ($q && $num): $st = (string) ($q['status'] ?? 'draft'); $url = quote_url($q);
      $phone = preg_replace('/[^\d+]/', '', (string) ($q['client']['phone'] ?? ''));
      $hasMail = (bool) filter_var((string) ($q['client']['email'] ?? ''), FILTER_VALIDATE_EMAIL);
      $smsQuote = 'שלום ' . ($q['client']['name'] ?? '') . ', כאן יהודה מ־YYG Web Design. הצעת המחיר שלך: ' . $url . ' אפשר לאשר אותה ישירות מהקישור.';
      $fbLink = 'https://yygweb.com/feedback/?' . http_build_query(['name' => $q['client']['name'] ?? '', 'phone' => $q['client']['phone'] ?? '', 'business' => $q['client']['business'] ?? '', 'quote' => $num]);
      $smsFb = 'שלום ' . ($q['client']['name'] ?? '') . ', אחרי שתעבור/י על 3 העיצובים, אשמח למשוב קצר כאן: ' . $fbLink; ?>
<?php if (isset($_GET['mailed'])): ?><p class="flash"><?= h((string) $_GET['mailed']) ?> <?= $_GET['mailed'] === 'ההצעה' ? 'נשלחה' : 'נשלח' ?> במייל ל־<bdi><?= h($q['client']['email'] ?? '') ?></bdi>.</p><?php endif; ?>
<?php if (isset($_GET['mailerr'])): ?><p class="flash" style="background:#3a1f1f;border-color:#6b3434"><?= $_GET['mailerr'] === '1' ? 'כדי לשלוח במייל, צריך להכניס את המייל של הלקוח בפרטים למטה ולשמור.' : 'השליחה במייל נכשלה. אפשר לנסות שוב בעוד רגע, או להעתיק את הקישור.' ?></p><?php endif; ?>
<div class="card qlink">
 <div class="row"><span>סטטוס</span><b><span class="tag<?= $st === 'approved' ? ' ok' : '' ?>"><?= h(Q_STATUSES[$st] ?? $st) ?></span></b></div>
 <div class="row"><span>צפיות של הלקוח</span><b><?= (int) ($q['views'] ?? 0) ?><?= !empty($q['last_view']) ? ' <small>· אחרונה ' . h(fmt_time((int) $q['last_view'], 'd/m H:i')) . '</small>' : '' ?></b></div>
 <?php if (!empty($q['approved'])): ?><div class="row"><span>אושרה</span><b><?= h($q['approved']['name'] ?? '') ?> <small>· <?= h(fmt_time((int) $q['approved']['t'])) ?></small></b></div><?php endif; ?>
 <?php if (!empty($q['changed_after_approval'])): ?><p class="tag warn" style="margin-top:8px">שיניתם את ההצעה אחרי שהלקוח אישר. כדאי לעדכן אותו.</p><?php endif; ?>
 <div class="row"><span>הקישור ללקוח</span><b><bdi><a href="<?= h($url) ?>" target="_blank" rel="noopener" id="qurl"><?= h($url) ?></a></bdi></b></div>
 <div class="acts">
  <a class="btn gold" href="<?= h($url) ?>" target="_blank" rel="noopener">פתיחת ההצעה</a>
  <button class="btn" type="button" id="copy-url">העתקת הקישור</button>
  <a class="btn" href="/admin/quote.php?dup=<?= h(rawurlencode($num)) ?>">שכפול להצעה חדשה</a>
 </div>
 <h3 style="margin:16px 0 6px;font-size:1rem;color:#fff">שליחה ללקוח</h3>
 <div class="acts">
  <form method="post" onsubmit="return <?= $hasMail ? "confirm('לשלוח את ההצעה במייל ל־" . h($q['client']['email']) . "?')" : 'true' ?>"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="do" value="email_quote"><input type="hidden" name="num" value="<?= h($num) ?>"><button class="btn gold" type="submit">שליחת ההצעה במייל ללקוח</button></form>
  <form method="post" onsubmit="return <?= $hasMail ? "confirm('לשלוח את שאלון המשוב במייל ל־" . h($q['client']['email']) . "?')" : 'true' ?>"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="do" value="email_feedback"><input type="hidden" name="num" value="<?= h($num) ?>"><button class="btn" type="submit">שליחת שאלון המשוב במייל</button></form>
 </div>
 <?php if (!$hasMail): ?><p class="small muted" style="margin-top:6px">כדי לשלוח במייל, מכניסים את המייל של הלקוח בפרטים למטה ושומרים.</p><?php endif; ?>
 <?php if (!empty($q['sent_log'])): ?><p class="small muted" style="margin-top:6px">נשלח: <?= h(implode(' · ', array_map(static fn($l) => $l['what'] . ' ל־' . $l['to'] . ' ב־' . fmt_time((int) $l['t'], 'd/m H:i'), array_slice($q['sent_log'], -4)))) ?></p><?php endif; ?>
 <form class="acts" method="post" style="margin-top:8px">
  <input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="do" value="status"><input type="hidden" name="num" value="<?= h($num) ?>">
  <select name="st" aria-label="סטטוס" style="width:auto"><?php foreach (Q_STATUSES as $k => $label): ?><option value="<?= $k ?>"<?= $k === $st ? ' selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select>
  <button class="btn" type="submit">עדכון סטטוס</button>
 </form>
</div>
<?php endif; ?>

<form id="qform" method="post" action="/admin/quote.php" novalidate>
<input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="do" value="save"><input type="hidden" name="num" value="<?= h($num) ?>"><input type="hidden" name="data" id="qdata">
<div class="qgrid">
<div class="qmain">
 <fieldset><legend>הלקוח</legend>
  <div class="two">
   <label>שם<input type="text" id="c-name" required></label>
   <label>טלפון<input type="tel" id="c-phone" dir="ltr"></label>
   <label>שם העסק<input type="text" id="c-business"></label>
   <label>מייל <small>לא חובה. אם הלקוח רשום באתר, ההצעה תופיע גם באזור האישי שלו</small><input type="email" id="c-email" dir="ltr"></label>
   <label>שפת ההצעה <small>הלקוח רואה את ההצעה ואת המיילים בשפה הזו. שורות והערות שכותבים בעצמכם, כותבים בשפה הזו</small><select id="q-lang"><option value="he">עברית</option><option value="en">English</option></select></label>
  </div>
 </fieldset>

 <fieldset><legend>מה בונים</legend>
  <label>מסלול בסיס<select id="base"></select></label>
  <div class="two"><label>מחיר המסלול<input type="number" id="base-price" min="0" step="10" inputmode="numeric"></label><label>כותרת ההצעה<input type="text" id="title"></label></div>
  <p class="muted small">אפשר לשנות כל מחיר. המחירים מגיעים מהאתר, ומחירי המסלולים הבסיסיים הם הצעה עד שתאשר אותם.</p>
 </fieldset>

 <fieldset><legend>תוספות</legend>
  <div id="addons"></div>
  <h3 class="sub">שורות משלך</h3>
  <div id="custom"></div>
  <button class="btn" type="button" id="add-custom">+ שורה</button>
 </fieldset>

 <fieldset><legend>הנחה</legend>
  <div class="acts" style="margin:0 0 10px"><button class="btn" type="button" id="launch-10">מחיר השקה: 10%</button></div>
  <div class="three"><label>תיאור<input type="text" id="disc-label" placeholder="למשל: הנחת השקה"></label><label>סכום ב־₪<input type="number" id="disc-amount" min="0" step="10" inputmode="numeric"></label><label>או באחוזים<input type="number" id="disc-pct" min="0" max="100" step="1" inputmode="numeric"></label></div>
 </fieldset>

 <fieldset><legend>תנאים</legend>
  <div class="four"><label>כיווני עיצוב<input type="number" id="t-designs" min="0" max="20"></label><label>סבבי תיקונים<input type="number" id="t-revisions" min="0" max="20"></label><label>ימי עבודה לאספקה<input type="number" id="t-days" min="0" max="365"></label><label>חודשי ליווי<input type="number" id="t-support" min="0" max="60"></label></div>
  <label>מה כלול <small>שורה לכל פריט</small><textarea id="includes" rows="6"></textarea></label>
 </fieldset>

 <fieldset><legend>שלבי תשלום</legend>
  <div id="pay"></div>
  <div class="acts"><button class="btn" type="button" id="pay-10">30% · 40% · 30%</button><button class="btn" type="button" id="pay-50">50% · 50%</button><button class="btn" type="button" id="pay-add">+ שלב</button></div>
  <p class="small" id="pay-sum"></p>
 </fieldset>

 <fieldset><legend>תשלומים קבועים <small>לא נכללים בסכום החד־פעמי</small></legend>
  <div id="recurring"></div>
 </fieldset>

 <fieldset><legend>תוקף והערות</legend>
  <label>בתוקף עד<input type="date" id="valid"></label>
  <label>הערות ללקוח <small>מופיעות בהצעה</small><textarea id="notes" rows="3"></textarea></label>
 </fieldset>
</div>

<aside class="qside">
 <div class="card">
  <h3>סיכום</h3>
  <div id="sum-lines"></div>
  <div class="row total"><span>סה״כ חד־פעמי</span><b id="sum-total">0 ₪</b></div>
  <small>המחיר סופי, לא מוסיפים מע״מ.</small>
  <div id="sum-pay" style="margin-top:10px"></div>
  <div id="sum-rec" style="margin-top:10px"></div>
  <button class="btn gold" type="submit" style="width:100%;margin-top:14px"><?= $q && $num ? 'שמירת השינויים' : 'שמירה ויצירת קישור ללקוח' ?></button>
  <p class="small" id="form-err" role="alert" style="color:var(--bad)"></p>
 </div>
</aside>
</div>
</form>

<?php if ($q && $num): ?>
<form method="post" onsubmit="return confirm('למחוק את ההצעה? הקישור ששלחתם ללקוח יפסיק לעבוד.')" style="margin-top:28px">
 <input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="do" value="delete"><input type="hidden" name="num" value="<?= h($num) ?>">
 <button class="btn" type="submit" style="color:var(--bad)">מחיקת ההצעה</button>
</form>
<?php endif; ?>
</main>

<script>
const INIT = <?= json_encode($init, $JS) ?>;
const PREFILL = <?= json_encode($prefill, $JS) ?>;
const LEAD_ID = <?= json_encode($q['lead_id'] ?? ($prefill['lead_id'] ?? ''), $JS) ?>;
const VALID_DEFAULT = <?= json_encode($q['valid_until'] ?? (new DateTime('+14 days', tz()))->format('Y-m-d'), $JS) ?>;
const LANG0 = <?= json_encode($q ? (($q['lang'] ?? 'he') === 'en' ? 'en' : 'he') : ($prefill['lang'] ?? 'he'), $JS) ?>;

const HOSTING = 'דומיין, אחסון ומייל עסקי לשנה הראשונה';
const TEXTS = 'טקסטים לאתר: כתיבה בעזרת AI ועריכה אישית';
const GOOGLE = 'התאמה לטלפון, לגוגל ולנגישות, והצהרת נגישות';
const P10 = [[30, 'כדי להתחיל, לפני הכנת 3 העיצובים'], [40, 'אחרי שבוחרים עיצוב, לפני הבנייה'], [30, 'בסיום, אחרי האישור שלכם ולפני העלייה לאוויר']];   // השם נשאר P10, הפריסה עכשיו 30/40/30
const P50 = [[50, 'כדי להתחיל'], [50, 'בסיום, אחרי האישור שלכם ולפני העלייה לאוויר']];
const BRAND = ['אפיון: שיחת היכרות על הלקוחות, הייחוד והמסר', 'לוגו: 3 כיוונים, 3 סבבי תיקונים וקבצים לכל שימוש', 'שפה עיצובית: צבעים, פונטים ודף הנחיות קצר', 'כל התוכן: טקסטים, בחירת תמונות והכנסת הכול לאתר', 'דפוס ורשתות: כרטיס ביקור, פלאייר, תמונת פרופיל ותמונת שער', 'גוגל: רישום האתר ופתיחת פרופיל עסק'];
const BASES = {
  none: { label: 'בלי מסלול, רק תוספות', price: 0, en: 0, t: [0, 0, 0, 0], pay: P50, incl: [] },
  landing_basic: { label: 'דף נחיתה בסיסי', price: 1490, en: 450, t: [1, 1, 4, 1], pay: P50, incl: ['עמוד אחד, עד 5 חלקים', 'עיצוב אחד וסבב תיקונים אחד', 'הטקסטים מגיעים מכם', 'טופס פנייה וכפתור חיוג', 'דומיין ואחסון לשנה הראשונה', GOOGLE] },
  landing: { label: 'דף נחיתה', price: 2290, en: 700, t: [3, 3, 7, 4], pay: P10, incl: ['עמוד אחד, עד 7 חלקים', 'טופס פנייה וכפתור חיוג', 'שינויים קטנים עליי בתקופת הליווי', HOSTING, TEXTS, GOOGLE] },
  site_basic: { label: 'אתר בסיסי', price: 2900, en: 1000, t: [1, 1, 7, 1], pay: P50, incl: ['עד 4 עמודים', 'עיצוב אחד וסבב תיקונים אחד', 'הטקסטים מגיעים מכם', 'טופס פנייה וכפתור חיוג', 'דומיין ואחסון לשנה הראשונה', GOOGLE] },
  site: { label: 'אתר תדמית', price: 4900, en: 1500, t: [3, 3, 14, 4], pay: P10, incl: ['עד 6 עמודים', 'מסך עדכון לתוכן שמשתנה', 'טפסים לפי הצורך', 'הדרכה: שיחה אישית ומדריך כתוב', HOSTING, TEXTS, GOOGLE] },
  club: { label: 'אתר תדמית מורחב', price: 6900, en: 2100, t: [3, 3, 20, 4], pay: P10, incl: ['עד 12 עמודים', 'מדור תוכן: חדשות, מאמרים או אירועים', 'מסך עדכון לתוכן שמשתנה', 'הדרכה: שיחה אישית ומדריך כתוב', HOSTING, TEXTS, GOOGLE] },
  store: { label: 'חנות אונליין', price: 7900, en: 2400, t: [3, 3, 25, 4], pay: P10, incl: ['חנות מלאה, עד 30 מוצרים, ואני מכניס אותם', 'עגלה, סליקה באשראי, משלוחים ועמודי תקנון', 'מסך מוצרים, מלאי והזמנות', 'הדרכה: שיחה אישית ומדריך כתוב', HOSTING, TEXTS, GOOGLE] },
  brand_landing: { label: 'מותג מ־0 עד 100% עם דף נחיתה', price: 6190, en: 700, t: [3, 3, 17, 4], pay: P10, incl: BRAND.concat(['דף נחיתה בעיצוב המותג', HOSTING, GOOGLE]) },
  brand_site: { label: 'מותג מ־0 עד 100% עם אתר תדמית', price: 8800, en: 1500, t: [3, 3, 24, 4], pay: P10, incl: BRAND.concat(['אתר תדמית, עד 6 עמודים', HOSTING, GOOGLE]) },
  brand_club: { label: 'מותג מ־0 עד 100% עם אתר תדמית מורחב', price: 10800, en: 2100, t: [3, 3, 30, 4], pay: P10, incl: BRAND.concat(['אתר תדמית מורחב, עד 12 עמודים ומדור תוכן', HOSTING, GOOGLE]) },
  brand_store: { label: 'מותג מ־0 עד 100% עם חנות', price: 11800, en: 2400, t: [3, 3, 35, 4], pay: P10, incl: BRAND.concat(['חנות אונליין, עד 30 מוצרים', HOSTING, GOOGLE]) }
};
const ADDONS = [
  { k: 'page', label: 'עמוד נוסף', price: 400, qty: true },
  { k: 'en', label: 'גרסה באנגלית', price: null },
  { k: 'design', label: 'כיוון עיצוב נוסף', price: 400, qty: true },
  { k: 'rev', label: 'סבב תיקונים נוסף או שעת עבודה', price: 250, qty: true },
  { k: 'products', label: '10 מוצרים נוספים בחנות', price: 250, qty: true },
  { k: 'm_upd', label: 'מסך עדכון לדף נחיתה (שעות פתיחה, מבצע)', price: 1000 },
  { k: 'm_reg', label: 'טופס הרשמה מתקדם: שדות לפי הצורך, אישור במייל וייצוא לאקסל', price: 700 },
  { k: 'm_sched', label: 'לוח זמנים או אירועים שמתעדכן', price: 700 },
  { k: 'm_mail', label: 'רשימת תפוצה ו־3 תבניות מייל', price: 900 },
  { k: 'm_acc', label: 'חשבונות משתמשים: כניסה, פרופיל והיסטוריה', price: 2000 },
  { k: 'm_don', label: 'תרומות באשראי, חד־פעמי ובהוראת קבע', price: 1200 },
  { k: 'm_camp', label: 'קמפיין עם מד יעד ורשימת תורמים', price: 800 },
  { k: 'logo1', label: 'לוגו בסיסי: סקיצה אחת וסבב תיקונים אחד', price: 590 },
  { k: 'logo2', label: 'לוגו מקצועי: 3 כיוונים ו־3 סבבי תיקונים', price: 1500 },
  { k: 'brand', label: 'מיתוג בסיסי: לוגו, צבעים, פונטים, דף הנחיות וכרטיס ביקור', price: 2500 }
];
const RECURRING = [
  { k: 'care', label: '"הכול עליי": דומיין, אחסון, מייל עסקי, אבטחה, גיבויים ותיקון תקלות', price: 590, per: 'year', note: 'מהשנה השנייה', on: true },
  { k: 'maint', label: 'תחזוקה: עדכונים ושינויים קטנים, עד חצי שעה בחודש', price: 199, per: 'month', note: 'לא חובה', on: false },
  { k: 'seo', label: 'קידום בגוגל: תוכן חודשי ומעקב', price: 0, per: 'month', note: 'לפי הצעה נפרדת', on: false }
];

/* הצעה באנגלית: אותם מסלולים, תוספות ותנאים, בניסוח באנגלית. בהצעה נשמר רק הטקסט שהלקוח רואה */
const EN_HOSTING = 'Domain, hosting and business email for the first year';
const EN_TEXTS = 'Website copy: written with AI and personally edited';
const EN_GOOGLE = 'Mobile, Google and accessibility ready, with an accessibility statement';
const EN_P10 = [[30, 'To start, before the 3 designs'], [40, 'After you choose a design, before the build'], [30, 'At the end, after your approval and before going live']];
const EN_P50 = [[50, 'To start'], [50, 'At the end, after your approval and before going live']];
const EN_BRAND = ['Discovery: a call about your customers, what sets you apart and your message', 'Logo: 3 directions, 3 revision rounds and files for every use', 'Visual language: colors, fonts and a short style guide', 'All the content: copy, choosing photos and putting it all on the site', 'Print and social media: a business card, a flyer, a profile picture and a cover image', 'Google: registering the site and opening a Business Profile'];
const BASES_EN = {
  none: { label: 'No package, add-ons only', incl: [] },
  landing_basic: { label: 'Basic landing page', incl: ['One page, up to 5 sections', 'One design and one revision round', 'You provide the text', 'A contact form and a call button', 'Domain and hosting for the first year', EN_GOOGLE] },
  landing: { label: 'Landing page', incl: ['One page, up to 7 sections', 'A contact form and a call button', 'Small changes on me during the support period', EN_HOSTING, EN_TEXTS, EN_GOOGLE] },
  site_basic: { label: 'Basic website', incl: ['Up to 4 pages', 'One design and one revision round', 'You provide the text', 'A contact form and a call button', 'Domain and hosting for the first year', EN_GOOGLE] },
  site: { label: 'Business website', incl: ['Up to 6 pages', 'An update screen for the content that changes', 'Forms as needed', 'Training: a personal call and a written guide', EN_HOSTING, EN_TEXTS, EN_GOOGLE] },
  club: { label: 'Extended business website', incl: ['Up to 12 pages', 'A content section: news, articles or events', 'An update screen for the content that changes', 'Training: a personal call and a written guide', EN_HOSTING, EN_TEXTS, EN_GOOGLE] },
  store: { label: 'Online store', incl: ['A full store with up to 30 products, which I add for you', 'A cart, card payments, shipping and policy pages', 'A screen for products, stock and orders', 'Training: a personal call and a written guide', EN_HOSTING, EN_TEXTS, EN_GOOGLE] },
  brand_landing: { label: 'Brand from 0 to 100% with a landing page', incl: EN_BRAND.concat(['A landing page in the brand design', EN_HOSTING, EN_GOOGLE]) },
  brand_site: { label: 'Brand from 0 to 100% with a business website', incl: EN_BRAND.concat(['A business website, up to 6 pages', EN_HOSTING, EN_GOOGLE]) },
  brand_club: { label: 'Brand from 0 to 100% with an extended business website', incl: EN_BRAND.concat(['An extended business website, up to 12 pages and a content section', EN_HOSTING, EN_GOOGLE]) },
  brand_store: { label: 'Brand from 0 to 100% with a store', incl: EN_BRAND.concat(['An online store, up to 30 products', EN_HOSTING, EN_GOOGLE]) }
};
const ADDONS_EN = { page: 'An extra page', en: 'A second language version', design: 'An extra design direction', rev: 'An extra revision round or hour of work', products: '10 more products in the store', m_upd: 'An update screen for a landing page (opening hours, a promotion)', m_reg: 'An advanced sign-up form: custom fields, email confirmation and Excel export', m_sched: 'An updatable schedule or events', m_mail: 'A mailing list and 3 email templates', m_acc: 'User accounts: login, profile and history', m_don: 'Card donations, one-time and monthly', m_camp: 'A campaign with a goal meter and donor list', logo1: 'Basic logo: one sketch and one revision round', logo2: 'Professional logo: 3 directions and 3 revision rounds', brand: 'Basic branding: logo, colors, fonts, a style guide and a business card' };
const RECURRING_EN = { care: { label: '"All-in Care": domain, hosting, business email, security, backups and fixes', note: 'from the second year' }, maint: { label: 'Maintenance: updates and small changes, up to half an hour a month', note: 'optional' }, seo: { label: 'Google promotion: monthly content and tracking', note: 'by separate quote' } };
const LAUNCH = ['מחיר השקה (תמורת הצגת האתר בעבודות והמלצה כתובה)', 'Launch price (in return for showing the site in my work and a written testimonial)'];

const $ = (id) => document.getElementById(id);
const isEn = () => $('q-lang').value === 'en';
const bLabel = (k) => (isEn() ? BASES_EN[k].label : BASES[k].label);
const bIncl = (k) => (isEn() ? BASES_EN[k].incl : BASES[k].incl);
const bPay = (k) => (isEn() ? (BASES[k].pay === P10 ? EN_P10 : EN_P50) : BASES[k].pay);
const aLabel = (a) => (isEn() ? ADDONS_EN[a.k] : a.label);
const rText = (r) => (isEn() ? RECURRING_EN[r.k] : r);
const forWho = (who) => (who ? (isEn() ? ' for ' : ' עבור ') + who : '');
const nis = (n) => Math.round(n).toLocaleString('en-US') + ' ₪';
const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

/* בניית הטופס */
$('base').innerHTML = Object.entries(BASES).map(([k, b]) => `<option value="${k}">${esc(b.label)}${b.price ? ' · ' + nis(b.price) : ''}</option>`).join('');
$('addons').innerHTML = ADDONS.map((a) => `<div class="line" data-k="${a.k}"><label class="chk"><input type="checkbox" data-on><span>${esc(a.label)}</span></label>${a.qty ? '<input type="number" data-qty min="1" max="99" value="1" aria-label="כמות">' : '<span></span>'}<input type="number" data-price min="0" step="10" aria-label="מחיר" value="${a.price ?? ''}"></div>`).join('');
$('recurring').innerHTML = RECURRING.map((r) => `<div class="line rec" data-k="${r.k}"><label class="chk"><input type="checkbox" data-on${r.on ? ' checked' : ''}><span>${esc(r.label)}</span></label><input type="number" data-price min="0" step="1" value="${r.price}" aria-label="מחיר"><small>${r.per === 'year' ? 'לשנה' : 'לחודש'}, ${esc(r.note)}</small></div>`).join('');
const customRow = (c = {}) => {
  const d = document.createElement('div');
  d.className = 'line custom';
  d.innerHTML = `<input type="text" data-label placeholder="תיאור" value="${esc(c.label || '')}" aria-label="תיאור"><input type="number" data-qty min="1" max="999" value="${c.qty || 1}" aria-label="כמות"><input type="number" data-price min="0" step="10" placeholder="מחיר" value="${c.price ?? ''}" aria-label="מחיר"><button type="button" class="btn x" aria-label="הסרה">✕</button>`;
  d.querySelector('.x').addEventListener('click', () => { d.remove(); calc(); });
  $('custom').append(d);
};
const payRow = (p = [0, '']) => {
  const d = document.createElement('div');
  d.className = 'line pay';
  d.innerHTML = `<input type="number" data-pct min="0" max="100" value="${p[0]}" aria-label="אחוז"><span>%</span><input type="text" data-when value="${esc(p[1])}" aria-label="מתי"><button type="button" class="btn x" aria-label="הסרה">✕</button>`;
  d.querySelector('.x').addEventListener('click', () => { d.remove(); calc(); });
  $('pay').append(d);
};
const setPay = (rows) => { $('pay').innerHTML = ''; rows.forEach(payRow); calc(); };

const applyBase = (k, keepPrice) => {
  const b = BASES[k];
  if (!keepPrice) $('base-price').value = b.price;
  const ent = document.querySelector('#addons [data-k="en"] [data-price]');
  if (!keepPrice) ent.value = b.en || '';
  [$('t-designs').value, $('t-revisions').value, $('t-days').value, $('t-support').value] = b.t;
  $('includes').value = bIncl(k).join('\n');
  setPay(bPay(k));
  const care = document.querySelector('#recurring [data-k="care"] [data-price]');   // "הכול עליי": בחנות 890 ₪ לשנה
  if (care && !keepPrice) care.value = /store/.test(k) ? 890 : 590;
  const who = $('c-business').value.trim() || $('c-name').value.trim();
  if (k !== 'none') $('title').value = bLabel(k) + forWho(who);
};
$('base').addEventListener('change', () => applyBase($('base').value, false));
$('add-custom').addEventListener('click', () => { customRow(); calc(); });
$('pay-10').addEventListener('click', () => setPay(isEn() ? EN_P10 : P10));
$('pay-50').addEventListener('click', () => setPay(isEn() ? EN_P50 : P50));
$('pay-add').addEventListener('click', () => { payRow([0, '']); calc(); });
$('disc-pct').addEventListener('input', () => { const pct = Number($('disc-pct').value) || 0; $('disc-amount').value = Math.round(subtotal() * pct / 100); calc(); });
$('disc-amount').addEventListener('input', () => { $('disc-pct').value = ''; });
$('launch-10').addEventListener('click', () => { $('disc-label').value = LAUNCH[isEn() ? 1 : 0]; $('disc-pct').value = 10; $('disc-pct').dispatchEvent(new Event('input')); });

/* חישוב */
const items = () => {
  const out = [];
  const k = $('base').value;
  if (k !== 'none') out.push({ label: bLabel(k), qty: 1, price: Number($('base-price').value) || 0 });
  document.querySelectorAll('#addons .line').forEach((l) => {
    if (!l.querySelector('[data-on]').checked) return;
    const a = ADDONS.find((x) => x.k === l.dataset.k);
    const q = l.querySelector('[data-qty]');
    out.push({ label: aLabel(a), qty: q ? Math.max(1, Number(q.value) || 1) : 1, price: Number(l.querySelector('[data-price]').value) || 0 });
  });
  document.querySelectorAll('#custom .line').forEach((l) => {
    const label = l.querySelector('[data-label]').value.trim();
    if (label) out.push({ label, qty: Math.max(1, Number(l.querySelector('[data-qty]').value) || 1), price: Number(l.querySelector('[data-price]').value) || 0 });
  });
  return out;
};
const subtotal = () => items().reduce((s, i) => s + i.price * i.qty, 0);
const payRows = () => [...document.querySelectorAll('#pay .line')].map((l) => ({ pct: Number(l.querySelector('[data-pct]').value) || 0, when: l.querySelector('[data-when]').value.trim() }));
const recRows = () => [...document.querySelectorAll('#recurring .line')].filter((l) => l.querySelector('[data-on]').checked).map((l) => { const r = RECURRING.find((x) => x.k === l.dataset.k); const x = rText(r); return { label: x.label, price: Number(l.querySelector('[data-price]').value) || 0, per: r.per, note: x.note }; });
function calc() {
  document.querySelectorAll('#addons .line, #recurring .line').forEach((l) => l.classList.toggle('off', !l.querySelector('[data-on]').checked));
  const its = items();
  const sub = its.reduce((s, i) => s + i.price * i.qty, 0);
  const disc = Math.min(sub, Number($('disc-amount').value) || 0);
  const total = sub - disc;
  $('sum-lines').innerHTML = its.map((i) => `<div class="row"><span>${esc(i.label)}${i.qty > 1 ? ' × ' + i.qty : ''}</span><b>${nis(i.price * i.qty)}</b></div>`).join('') + (disc ? `<div class="row"><span>${esc($('disc-label').value.trim() || 'הנחה')}</span><b>−${nis(disc)}</b></div>` : '');
  $('sum-total').textContent = nis(total);
  const pr = payRows();
  const sumPct = pr.reduce((s, p) => s + p.pct, 0);
  $('pay-sum').textContent = sumPct === 100 ? 'סה״כ 100%.' : `סה״כ ${sumPct}%. צריך להגיע ל־100%.`;
  $('pay-sum').style.color = sumPct === 100 ? 'var(--muted)' : 'var(--bad)';
  $('sum-pay').innerHTML = pr.map((p) => `<div class="row"><span>${p.pct}% ${esc(p.when)}</span><b>${nis(total * p.pct / 100)}</b></div>`).join('');
  $('sum-rec').innerHTML = recRows().map((r) => `<div class="row"><span>${esc(r.label.split(':')[0])}</span><b>${r.price ? nis(r.price) + (r.per === 'year' ? ' לשנה' : ' לחודש') : 'לפי הצעה'}</b></div>`).join('');
}
/* שינוי כמות או מחיר בתוספת מסמן אותה, כדי שלא ייראה כאילו נוספה כשהיא לא */
document.getElementById('qform').addEventListener('input', (e) => { const l = e.target.closest('#addons .line, #recurring .line'); if (l && e.target.matches('[data-qty], [data-price]')) l.querySelector('[data-on]').checked = true; });
document.getElementById('qform').addEventListener('input', calc);
document.getElementById('qform').addEventListener('change', calc);

/* שמירה */
const uiState = () => ({
  base: $('base').value, basePrice: $('base-price').value,
  addons: [...document.querySelectorAll('#addons .line')].map((l) => ({ k: l.dataset.k, on: l.querySelector('[data-on]').checked, qty: l.querySelector('[data-qty]')?.value, price: l.querySelector('[data-price]').value })),
  custom: [...document.querySelectorAll('#custom .line')].map((l) => ({ label: l.querySelector('[data-label]').value, qty: l.querySelector('[data-qty]').value, price: l.querySelector('[data-price]').value })),
  recurring: [...document.querySelectorAll('#recurring .line')].map((l) => ({ k: l.dataset.k, on: l.querySelector('[data-on]').checked, price: l.querySelector('[data-price]').value })),
  pay: payRows(), title: $('title').value, lang: $('q-lang').value, discLabel: $('disc-label').value, discAmount: $('disc-amount').value,
  terms: [$('t-designs').value, $('t-revisions').value, $('t-days').value, $('t-support').value], includes: $('includes').value, notes: $('notes').value, valid: $('valid').value,
  client: { name: $('c-name').value, phone: $('c-phone').value, business: $('c-business').value, email: $('c-email').value }
});
$('qform').addEventListener('submit', (e) => {
  const err = $('form-err');
  if (!$('c-name').value.trim()) { e.preventDefault(); err.textContent = 'חסר שם לקוח.'; $('c-name').focus(); return; }
  if (!items().length) { e.preventDefault(); err.textContent = 'אין בהצעה אף שורה.'; return; }
  if (payRows().reduce((s, p) => s + p.pct, 0) !== 100) { e.preventDefault(); err.textContent = 'שלבי התשלום צריכים להסתכם ב־100%.'; return; }
  const unpriced = [...document.querySelectorAll('#custom .line')].filter((l) => l.querySelector('[data-label]').value.trim() && l.querySelector('[data-price]').value === '');
  if (unpriced.length && !confirm(`יש ${unpriced.length} שורות בלי מחיר. לשמור בכל זאת? הן יופיעו ללקוח ב־0 ₪.`)) { e.preventDefault(); return; }
  const u = uiState();
  $('qdata').value = JSON.stringify({
    client: u.client, title: u.title, lang: u.lang, items: items(), recurring: recRows(),
    discount: { label: u.discLabel.trim(), amount: Number(u.discAmount) || 0 },
    terms: { designs: u.terms[0], revisions: u.terms[1], days: u.terms[2], support: u.terms[3] },
    payment: payRows(), valid_until: u.valid, includes: u.includes, notes: u.notes, lead_id: LEAD_ID, ui: u
  });
});

/* טעינה */
$('valid').value = VALID_DEFAULT;
$('q-lang').value = LANG0;
/* החלפת שפה: מתרגמת את מה שעוד לא נערך ידנית (מה כלול, שלבי התשלום והכותרת), ומשאירה מחירים ותנאים */
$('q-lang').addEventListener('change', () => {
  const k = $('base').value, en = isEn();
  const from = en ? BASES[k].incl : BASES_EN[k].incl;
  if ($('includes').value.trim() === '' || $('includes').value === from.join('\n')) $('includes').value = bIncl(k).join('\n');
  const pairs = [...P10.map((p, i) => [p[1], EN_P10[i][1]]), ...P50.map((p, i) => [p[1], EN_P50[i][1]])];
  document.querySelectorAll('#pay [data-when]').forEach((w) => { const m = pairs.find((x) => x[en ? 0 : 1] === w.value); if (m) w.value = m[en ? 1 : 0]; });
  const who = $('c-business').value.trim() || $('c-name').value.trim();
  const oldTitle = (en ? BASES[k].label : BASES_EN[k].label) + (who ? (en ? ' עבור ' : ' for ') + who : '');
  if (k !== 'none' && ($('title').value === oldTitle || $('title').value.trim() === '')) $('title').value = bLabel(k) + forWho(who);
  if ($('disc-label').value === LAUNCH[en ? 0 : 1]) $('disc-label').value = LAUNCH[en ? 1 : 0];
  calc();
});
if (INIT) {
  const u = INIT;
  $('base').value = BASES[u.base] ? u.base : 'none';
  $('base-price').value = u.basePrice;
  (u.addons || []).forEach((a) => { const l = document.querySelector(`#addons [data-k="${a.k}"]`); if (!l) return; l.querySelector('[data-on]').checked = !!a.on; if (l.querySelector('[data-qty]') && a.qty) l.querySelector('[data-qty]').value = a.qty; l.querySelector('[data-price]').value = a.price; });
  (u.custom || []).forEach(customRow);
  (u.recurring || []).forEach((r) => { const l = document.querySelector(`#recurring [data-k="${r.k}"]`); if (!l) return; l.querySelector('[data-on]').checked = !!r.on; l.querySelector('[data-price]').value = r.price; });
  $('pay').innerHTML = ''; (u.pay || []).forEach((p) => payRow([p.pct, p.when]));
  $('title').value = u.title || ''; $('disc-label').value = u.discLabel || ''; $('disc-amount').value = u.discAmount || '';
  [$('t-designs').value, $('t-revisions').value, $('t-days').value, $('t-support').value] = u.terms || [0, 0, 0, 0];
  $('includes').value = u.includes || ''; $('notes').value = u.notes || ''; if (u.valid) $('valid').value = u.valid;
  $('c-name').value = u.client?.name || ''; $('c-phone').value = u.client?.phone || ''; $('c-business').value = u.client?.business || ''; $('c-email').value = u.client?.email || '';
} else {
  let guess = 'site';
  if (PREFILL) {
    $('c-name').value = PREFILL.name; $('c-phone').value = PREFILL.phone; $('c-business').value = PREFILL.business; if (PREFILL.email) $('c-email').value = PREFILL.email;
    const p = PREFILL.plan || '';
    if (PREFILL.auto) guess = PREFILL.auto.base; else guess = /מותג|Brand/.test(p) ? 'brand_site' : /בסיסי|Basic landing/.test(p) ? 'landing_basic' : /חנות|store/i.test(p) ? 'store' : /הרשמה|sign-?ups/i.test(p) ? 'club' : /דף נחיתה|landing/i.test(p) ? 'landing' : 'site';
  }
  $('base').value = guess;
  applyBase(guess, false);
  /* תוספת שפה שנייה רק כשביקשו אותה במחשבון. "(Sent from the English site)" לבד לא אומר שצריך שתי שפות */
  if (PREFILL && /אנגלית|English version|with English|second language/i.test(PREFILL.plan || '')) document.querySelector('#addons [data-k="en"] [data-on]').checked = true;
  const extra = (PREFILL?.plan || '').match(/כולל (\d+) עמודים נוספים|כולל עמוד אחד נוסף/);
  if (extra) { const l = document.querySelector('#addons [data-k="page"]'); l.querySelector('[data-on]').checked = true; l.querySelector('[data-qty]').value = extra[1] || 1; }
  const A = PREFILL && PREFILL.auto;
  if (A) {
    Object.entries(A.addons || {}).forEach(([k, v]) => { const l = document.querySelector(`#addons [data-k="${k}"]`); if (!l || !v) return; l.querySelector('[data-on]').checked = true; if (typeof v === 'number' && l.querySelector('[data-qty]')) l.querySelector('[data-qty]').value = v; });
    (A.custom || []).forEach(customRow);
    Object.entries(A.recurring || {}).forEach(([k, v]) => { const l = document.querySelector(`#recurring [data-k="${k}"]`); if (l) l.querySelector('[data-on]').checked = !!v; });
    if (A.who && guess !== 'none') $('title').value = bLabel(guess) + forWho(A.who);
    const icon = { ok: '✓ נוסף', todo: '⚠ לתמחר', note: 'ℹ לשיחה' };
    const box = document.createElement('div');
    box.className = 'card qhints';
    box.innerHTML = '<h3>מה נלקח משאלון האפיון</h3><ul>' + (A.hints || []).map(([k, t]) => `<li class="h-${k}"><b>${icon[k]}:</b> ${esc(t)}</li>`).join('') + '</ul><p class="small">שורות "לתמחר" נכנסו להצעה בלי מחיר. צריך להשלים מחיר או למחוק אותן לפני השליחה.</p>';
    $('qform').before(box);
  }
}
calc();
const cp = $('copy-url');
if (cp) cp.addEventListener('click', async () => { try { await navigator.clipboard.writeText($('qurl').href); cp.textContent = 'הועתק'; } catch (e) { cp.textContent = $('qurl').href; } });
</script>
<?php
adm_page(($num ? $num . ' | ' : '') . 'הצעת מחיר | YYG Web Design', (string) ob_get_clean(), '
.qgrid{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:20px;align-items:start;margin-top:16px}
.qside{position:sticky;top:76px}.qside .total{font-size:1.15rem;border-top:2px solid var(--line);margin-top:6px;padding-top:10px}
fieldset{border:1px solid var(--line);border-radius:14px;padding:14px 16px;margin:0 0 14px;background:var(--panel);min-width:0}
legend{font-weight:700;padding:0 6px}legend small{font-weight:400}
label{display:grid;gap:4px;font-size:.92rem;color:var(--muted);margin-bottom:10px}
.two{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}.three{display:grid;grid-template-columns:2fr 1fr 1fr;gap:0 12px}.four{display:grid;grid-template-columns:repeat(4,1fr);gap:0 12px}
.line{display:grid;grid-template-columns:minmax(0,1fr) 70px 110px;gap:8px;align-items:center;padding:6px 0;border-top:1px solid var(--line)}.line:first-child{border-top:0}
.line.custom{grid-template-columns:minmax(0,1fr) 70px 110px 40px}.line.pay{grid-template-columns:80px 16px minmax(0,1fr) 40px}.line.rec{grid-template-columns:minmax(0,1fr) 90px 150px}
.chk{display:flex;align-items:center;gap:8px;margin:0;color:var(--text);font-size:.95rem}.chk input{width:18px;height:18px;min-height:0;flex:none;accent-color:var(--gold)}
.btn.x{min-height:36px;padding:4px 10px}
.line.off [data-qty],.line.off [data-price],.line.off small{opacity:.4}.line.off .chk span{color:var(--muted)}
.line.custom input[data-price]:placeholder-shown{border-color:var(--warn);box-shadow:0 0 0 2px rgba(242,201,90,.2)}
.qhints{margin-top:14px}.qhints h3{color:#fff;font-size:1.05rem;margin:0 0 8px}.qhints ul{margin:0;padding-inline-start:18px;display:grid;gap:4px}.qhints .h-ok b{color:var(--ok)}.qhints .h-todo b{color:var(--warn)}.qhints .h-note b{color:var(--cyan)}.sub{font-size:.95rem;margin:14px 0 6px}.small{font-size:.88rem}
.qlink{margin-top:12px}.qlink .row b{overflow-wrap:anywhere;text-align:left}
@media (max-width:860px){.qgrid{grid-template-columns:1fr}.qside{position:static}.four{grid-template-columns:1fr 1fr}.three{grid-template-columns:1fr}}
@media (max-width:520px){.two{grid-template-columns:1fr}.line,.line.rec{grid-template-columns:minmax(0,1fr) 64px 96px}.line.rec small{grid-column:1/-1}}');
