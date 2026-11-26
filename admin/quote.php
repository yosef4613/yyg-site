<?php
/*
 * YYG: בונה הצעות מחיר. https://yygweb.com/admin/quote.php
 * ?lead=מזהה  הצעה חדשה עם פרטי הפנייה
 * ?id=מספר    עריכת הצעה קיימת
 * ?dup=מספר   הצעה חדשה שמועתקת מהצעה קיימת
 * כל הצעה מקבלת קישור פרטי ללקוח (/q/?t=...), שבו הוא רואה את ההצעה, מדפיס אותה ומאשר בלחיצה.
 */
declare(strict_types=1);
require __DIR__ . '/_lib.php';
adm_require();

$quotes = quotes_load();
$leads = leads_load();

/* שאלון אפיון → מסלול, תוספות, שורות לתמחור ותשלומים קבועים. price=null פירושו "לתמחר": השורה נכנסת בלי מחיר ומסומנת */
/* לשאלונים ישנים בלי תשובות לפי מזהה: משחזרים מהטקסט של השאלות */
function raw_from_items(array $items): array {
    $map = ['package' => '/איזה מסלול|Which package/u', 'pages' => '/אילו עמודים|Which pages/u', 'features' => '/אילו יכולות|Which features/u', 'languages' => '/באילו שפות|Which languages/u',
        'logo' => '/^(לוגו|Logo)$/u', 'photos' => '/^(תמונות|Photos)$/u', 'texts' => '/הטקסטים לאתר|טקסטים לאתר|website text/iu', 'update_who' => '/מי יעדכן|Who will update/u',
        'goal_action' => '/הדבר הכי חשוב|most important thing/u', 'site_now' => '/יש אתר קיים|have a site now/u', 'site_url' => '/הכתובת של האתר הקיים|current site/u',
        'deadline' => '/מתי האתר צריך|need to be live/u', 'budget' => '/תקציב משוער|Rough budget/u', 'domain' => '/הדומיין|domain\)/iu', 'site_name' => '/השם שיופיע|name shown/u'];
    $multi = ['pages', 'features', 'languages'];
    $r = [];
    foreach ($items as $it) foreach ($map as $id => $re) {
        if (isset($r[$id]) || !preg_match($re, (string) $it['q'])) continue;
        $a = (string) $it['a'];
        if (in_array($id, $multi, true)) {
            $vals = array_map('trim', explode(', ', $a));
            foreach ($vals as $i => $v) if (preg_match('/^(אחר|Other): (.*)$/u', $v, $m)) { $vals[$i] = '__other'; $r[$id . '__other'] = $m[2]; }
            $r[$id] = $vals;
        } else $r[$id] = $a;
    }
    return $r;
}
function intake_to_quote(array $rec): array {
    $r = !empty($rec['raw']) ? (array) $rec['raw'] : raw_from_items((array) ($rec['items'] ?? []));
    $val = static fn(string $k): string => is_string($r[$k] ?? null) ? $r[$k] : '';
    $arr = static fn(string $k): array => is_array($r[$k] ?? null) ? $r[$k] : [];
    $any = static function (array $list, string $re): bool { foreach ($list as $x) if (preg_match($re, (string) $x)) return true; return false; };
    $pages = $arr('pages'); $feats = $arr('features'); $langs = $arr('languages');
    $needStore = $any($feats, '/אשראי|Card payments/u') || $any($pages, '/^(חנות|Store)$/u') || preg_match('/לקנות ולשלם|Buy and pay/u', $val('goal_action'));
    $needClub = $any($feats, '/הרשמה עם אישור|Sign-up with|רשימת תפוצה|Mailing list/u') || $any($pages, '/^(הרשמה|Sign-up)$|אזור אישי|Customer accounts/u');
    $pageCount = count(array_filter($pages, static fn($p) => $p !== '__other' && !preg_match('/המלצות|Testimonials|שאלות נפוצות|FAQ/u', $p))) + (in_array('__other', $pages, true) ? 1 : 0);
    $kind = $needStore ? 'store' : ($needClub ? 'club' : ($pageCount > 0 && $pageCount <= 2 ? 'landing' : 'site'));
    $pkg = $val('package');
    $hints = []; $custom = []; $addons = []; $recurring = [];
    $added = static function (string $t) use (&$hints): void { $hints[] = ['ok', $t]; };
    $todo = static function (string $label) use (&$custom, &$hints): void { $custom[] = ['label' => $label, 'qty' => 1, 'price' => null]; $hints[] = ['todo', $label]; };
    $note = static function (string $t) use (&$hints): void { $hints[] = ['note', $t]; };
    if (preg_match('/מותג|Brand/u', $pkg)) $base = 'brand_' . $kind;
    elseif (preg_match('/בסיסי|Basic/u', $pkg)) $base = 'landing_basic';
    elseif (preg_match('/חנות|store/iu', $pkg)) $base = 'store';
    elseif (preg_match('/הרשמה|sign-ups/iu', $pkg)) $base = 'club';
    elseif (preg_match('/אזור ניהול|admin panel/iu', $pkg)) $base = 'site';
    elseif (preg_match('/דף נחיתה|Landing/iu', $pkg)) $base = 'landing';
    else { $base = $kind; $note('הלקוח לא בחר מסלול. המסלול נבחר לפי מה שסימן בעמודים וביכולות.'); }
    $core = preg_replace('/^brand_/', '', $base);
    if ($pkg !== '' && $base !== $kind && !str_starts_with($base, 'brand_') && !($kind === 'landing' && $core === 'landing_basic')) $note('הלקוח בחר "' . $pkg . '", אבל לפי מה שסימן מתאים יותר מסלול אחר. כדאי לבדוק.');
    /* יכולות שלא כלולות במסלול שנבחר */
    if ($needStore && $core !== 'store') $todo('סליקה ותשלום באשראי באתר');
    if ($needClub && !in_array($core, ['club', 'store'], true)) {
        if ($core === 'site') { $custom[] = ['label' => 'הרשמה, חשבונות משתמש ורשימת תפוצה', 'qty' => 1, 'price' => 2000]; $added('הרשמה ורשימת תפוצה (ההפרש למסלול עם הרשמה, 2,000 ₪)'); }
        else $todo('הרשמה ורשימת תפוצה');
    }
    /* עמודים מעבר למכסה */
    $quota = ['site' => 6, 'club' => 8];
    $sectionsNote = $any($pages, '/המלצות|Testimonials|שאלות נפוצות|FAQ/u') ? ' (שאלות נפוצות והמלצות נספרו כחלקים בעמוד הבית, לא כעמודים נפרדים)' : '';
    if (isset($quota[$core]) && $pageCount > $quota[$core]) { $addons['page'] = $pageCount - $quota[$core]; $added(($pageCount - $quota[$core]) . ' עמודים נוספים: סימן ' . $pageCount . ' עמודים, ובמסלול כלולים עד ' . $quota[$core] . $sectionsNote); }
    elseif (isset($quota[$core]) && $pageCount > 0) $note('סימן ' . $pageCount . ' עמודים, ובמסלול כלולים עד ' . $quota[$core] . '. אין תוספת עמודים' . $sectionsNote . '.');
    if (in_array($core, ['landing', 'landing_basic'], true) && $pageCount > 3) $note('סימן ' . $pageCount . ' עמודים, אבל המסלול הוא דף נחיתה. אולי מתאים אתר.');
    /* שפות */
    $langList = array_values(array_filter($langs, static fn($l) => $l !== '__other'));
    if (in_array('__other', $langs, true) && $val('languages__other') !== '') $langList[] = $val('languages__other');
    if (count($langList) >= 2) {
        $addons['en'] = true; $added('שפה שנייה: ' . implode(' ו', array_slice($langList, 0, 2)));
        foreach (array_slice($langList, 2) as $l) $todo('שפה נוספת: ' . $l);
    }
    /* לוגו */
    $logo = $val('logo');
    if (!str_starts_with($base, 'brand_')) {
        if (preg_match('/אין, צריך|None, I need|לחדש|want a new/u', $logo)) { $addons['logo2'] = true; $added('לוגו מקצועי, 1,500 ₪ (אפשר להחליף ללוגו בסיסי ב־590 ₪)'); }
        elseif (preg_match('/רק תמונה|only have a picture/u', $logo)) $todo('שחזור הלוגו לקובץ איכותי');
    }
    /* תוכן ותמונות */
    if ($core === 'landing_basic' && preg_match('/שתכתוב|write it/u', $val('texts'))) $todo('כתיבת טקסטים (לא כלולה בבסיסי)');
    if (preg_match('/אין, צריך|None, I need/u', $val('photos'))) $todo('תמונות מתאימות מהמאגר');
    /* יכולות מיוחדות */
    if ($any($feats, '/זימון תורים|Appointment/u')) $todo('זימון תורים ביומן');
    if ($any($feats, '/נגן|player/u')) $todo('נגן מוזיקה או וידאו');
    if ($any($feats, '/מחשבון|calculator/u')) $todo('מחשבון או שאלון באתר');
    if (in_array('__other', $feats, true) && $val('features__other') !== '') $todo('יכולת נוספת: ' . $val('features__other'));
    if (in_array('__other', $pages, true) && $val('pages__other') !== '') $note('עמוד נוסף שהלקוח ביקש: ' . $val('pages__other'));
    /* דף נחיתה, והלקוח רוצה לעדכן בעצמו (שעות, מבצעים): בדף נחיתה אין אזור ניהול, אז מסך עדכון קטן */
    if (in_array($core, ['landing', 'landing_basic'], true) && preg_match('/אני או מישהו|Me or someone/u', $val('update_who'))) $todo('מסך עדכון קטן לבעל העסק' . ($val('update_what') !== '' ? ': ' . mb_substr($val('update_what'), 0, 60, 'UTF-8') . (mb_strlen($val('update_what'), 'UTF-8') > 60 ? '…' : '') : ''));
    /* תשלומים קבועים */
    if (preg_match('/שאתה תעדכן|like you to do/u', $val('update_who'))) { $recurring['maint'] = true; $added('תחזוקה חודשית (הלקוח רוצה שאתה תעדכן)'); }
    /* הערות לשיחה */
    if (preg_match('/^(כן|Yes)$/u', $val('site_now'))) $note('יש אתר קיים' . ($val('site_url') ? ' (' . $val('site_url') . ')' : '') . ': לתכנן הפניות כדי לשמור על הדירוג בגוגל.');
    if ($val('deadline') !== '' && !preg_match('/אין לחץ|No rush/u', $val('deadline'))) $note('לוח זמנים: ' . $val('deadline') . ($val('deadline_date') ? ', ' . $val('deadline_date') : '') . ($val('deadline_why') ? ' (' . $val('deadline_why') . ')' : '') . '.');
    if ($val('budget') !== '') $note('התקציב שהלקוח ציין: ' . $val('budget') . '.');
    if (preg_match('/צריך לרשום|needs registering/u', $val('domain'))) $note('צריך לרשום דומיין' . ($val('domain_wish') ? ': ' . rtrim($val('domain_wish'), '. ') : '') . '. כלול בשנה הראשונה.');
    $who = $val('site_name') ?: ($rec['contact']['business'] ?? '') ?: ($rec['contact']['name'] ?? '');
    return ['base' => $base, 'addons' => $addons, 'custom' => $custom, 'recurring' => $recurring, 'hints' => $hints, 'who' => $who];
}
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
        if ($do === 'email_quote') {
            $valid = (new DateTime((string) ($q['valid_until'] ?? 'now')))->format('d/m/Y');
            $t = quote_totals($q);
            $body = '<p>שלום ' . h($name) . ',</p><p>מצורפת הצעת המחיר' . ($q['title'] ? ': <b>' . h($q['title']) . '</b>' : '') . ', על סך ' . h(nis($t['total'])) . '. המחיר סופי, לא מוסיפים מע״מ.</p>'
                . '<p>בקישור אפשר לראות את כל הפרטים, להדפיס או לשמור כ־PDF, ולאשר בלחיצה.</p>' . mail_button(quote_url($q), 'לצפייה בהצעה')
                . '<p style="color:#5A6472;font-size:14px">ההצעה בתוקף עד ' . h($valid) . '. שאלות? אפשר להשיב למייל הזה או להתקשר ל־054-854-0700.</p><p>יהודה</p>';
            $ok = mail_send($to, 'הצעת מחיר מ־YYG' . ($q['title'] ? ': ' . $q['title'] : ''), mail_wrap('הצעת מחיר ' . $num, $body), "שלום $name,
הצעת המחיר: " . quote_url($q) . "
יהודה, 054-854-0700");
            $what = 'ההצעה';
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
$prefill = $formRec ? ['name' => $formRec['contact']['name'], 'phone' => $formRec['contact']['phone'], 'business' => $formRec['contact']['business'], 'email' => $formRec['contact']['email'],
    'plan' => '', 'lead_id' => '', 'auto' => intake_to_quote($formRec)] : ($lead ? ['name' => $lead['name'] ?? '', 'phone' => $lead['phone'] ?? '', 'email' => $lead['email'] ?? '', 'business' => $lead['business'] ?? '', 'plan' => $lead['plan'] ?? '', 'lead_id' => (string) $lead['id']] : null);
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
      $smsQuote = 'שלום ' . ($q['client']['name'] ?? '') . ', כאן יהודה מ־YYG. הצעת המחיר שלך: ' . $url . ' אפשר לאשר אותה ישירות מהקישור.';
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
  <div class="acts"><button class="btn" type="button" id="pay-10">10% · 40% · 50%</button><button class="btn" type="button" id="pay-50">50% · 50%</button><button class="btn" type="button" id="pay-add">+ שלב</button></div>
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

const HOSTING = 'דומיין, אחסון ומייל עסקי לשנה הראשונה';
const TEXTS = 'טקסטים לאתר: כתיבה בעזרת AI ועריכה אישית';
const GOOGLE = 'התאמה לטלפון, לגוגל ולנגישות, והצהרת נגישות';
const P10 = [[10, 'כדי להתחיל, לפני הכנת 3 העיצובים'], [40, 'אחרי שבוחרים עיצוב, לפני הבנייה'], [50, 'בסיום, אחרי האישור שלכם ולפני העלייה לאוויר']];
const P50 = [[50, 'כדי להתחיל'], [50, 'בסיום, אחרי האישור שלכם ולפני העלייה לאוויר']];
const BRAND = ['אפיון: שיחת היכרות על הלקוחות, הייחוד והמסר', 'לוגו: 3 כיוונים, 3 סבבי תיקונים וקבצים לכל שימוש', 'שפה עיצובית: צבעים, פונטים ודף הנחיות קצר', 'כל התוכן: טקסטים, בחירת תמונות והכנסת הכול לאתר', 'דפוס ורשתות: כרטיס ביקור, פלאייר, תמונת פרופיל ותמונת שער', 'גוגל: רישום האתר ופתיחת פרופיל עסק'];
const BASES = {
  none: { label: 'בלי מסלול, רק תוספות', price: 0, en: 0, t: [0, 0, 0, 0], pay: P50, incl: [] },
  landing_basic: { label: 'דף נחיתה בסיסי', price: 1290, en: 500, t: [1, 1, 3, 1], pay: P50, incl: ['עמוד אחד, עד 5 חלקים', 'עיצוב אחד וסבב תיקונים אחד', 'הטקסטים מגיעים מכם', 'טופס פנייה וכפתור חיוג', 'דומיין ואחסון לשנה הראשונה', GOOGLE] },
  landing: { label: 'דף נחיתה', price: 2000, en: 500, t: [3, 3, 4, 4], pay: P10, incl: ['עמוד אחד, עד 7 חלקים', 'טופס פנייה וכפתור חיוג', 'שינויים קטנים עליי בתקופת הליווי', HOSTING, TEXTS, GOOGLE] },
  site_basic: { label: 'אתר בסיסי', price: 2900, en: 1000, t: [1, 1, 7, 1], pay: P50, incl: ['עד 4 עמודים', 'עיצוב אחד וסבב תיקונים אחד', 'הטקסטים מגיעים מכם', 'טופס פנייה וכפתור חיוג', 'דומיין ואחסון לשנה הראשונה', GOOGLE] },
  site: { label: 'אתר עם אזור ניהול', price: 4000, en: 1000, t: [3, 3, 10, 4], pay: P10, incl: ['עד 6 עמודים', 'אזור ניהול לעדכון התוכן שמשתנה', 'טפסים ומדור תוכן', 'הדרכה: שיחה אישית ומדריך כתוב', HOSTING, TEXTS, GOOGLE] },
  club: { label: 'אתר עם הרשמה ורשימת תפוצה', price: 6000, en: 1500, t: [3, 3, 15, 4], pay: P10, incl: ['עד 8 עמודים', 'הרשמה, חשבונות משתמש, רשימת תפוצה ו־3 תבניות מייל', 'אזור ניהול: תוכן, פניות ומנויים', 'הדרכה: שיחה אישית ומדריך כתוב', HOSTING, TEXTS, GOOGLE] },
  store: { label: 'חנות אונליין', price: 7000, en: 1800, t: [3, 3, 20, 4], pay: P10, incl: ['חנות מלאה, עד 30 מוצרים, ואני מכניס אותם', 'עגלה, סליקה באשראי ומשלוחים', 'אזור ניהול: מוצרים, מלאי והזמנות', 'הדרכה: שיחה אישית ומדריך כתוב', HOSTING, TEXTS, GOOGLE] },
  brand_landing: { label: 'מותג מ־0 עד 100% עם דף נחיתה', price: 5900, en: 500, t: [3, 3, 14, 4], pay: P10, incl: BRAND.concat(['דף נחיתה בעיצוב המותג', HOSTING, GOOGLE]) },
  brand_site: { label: 'מותג מ־0 עד 100% עם אתר עם אזור ניהול', price: 7900, en: 1000, t: [3, 3, 20, 4], pay: P10, incl: BRAND.concat(['אתר עם אזור ניהול, עד 6 עמודים', HOSTING, GOOGLE]) },
  brand_club: { label: 'מותג מ־0 עד 100% עם אתר עם הרשמה', price: 9900, en: 1500, t: [3, 3, 25, 4], pay: P10, incl: BRAND.concat(['אתר עם הרשמה ורשימת תפוצה, עד 8 עמודים', HOSTING, GOOGLE]) },
  brand_store: { label: 'מותג מ־0 עד 100% עם חנות', price: 10900, en: 1800, t: [3, 3, 30, 4], pay: P10, incl: BRAND.concat(['חנות אונליין, עד 30 מוצרים', HOSTING, GOOGLE]) }
};
const ADDONS = [
  { k: 'page', label: 'עמוד נוסף', price: 350, qty: true },
  { k: 'en', label: 'גרסה באנגלית', price: null },
  { k: 'design', label: 'כיוון עיצוב נוסף', price: 400, qty: true },
  { k: 'rev', label: 'סבב תיקונים נוסף או שעת עבודה', price: 150, qty: true },
  { k: 'products', label: '10 מוצרים נוספים בחנות', price: 200, qty: true },
  { k: 'logo1', label: 'לוגו בסיסי: סקיצה אחת וסבב תיקונים אחד', price: 590 },
  { k: 'logo2', label: 'לוגו מקצועי: 3 כיוונים ו־3 סבבי תיקונים', price: 1500 },
  { k: 'brand', label: 'מיתוג בסיסי: לוגו, צבעים, פונטים, דף הנחיות וכרטיס ביקור', price: 2500 }
];
const RECURRING = [
  { k: 'care', label: '"הכול עליי": דומיין, אחסון, מייל עסקי, אבטחה, גיבויים ותיקון תקלות', price: 590, per: 'year', note: 'מהשנה השנייה', on: true },
  { k: 'maint', label: 'תחזוקה: עדכונים ושינויים קטנים, עד חצי שעה בחודש', price: 199, per: 'month', note: 'לא חובה', on: false },
  { k: 'seo', label: 'קידום בגוגל: תוכן חודשי ומעקב', price: 0, per: 'month', note: 'לפי הצעה נפרדת', on: false }
];

const $ = (id) => document.getElementById(id);
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
  $('includes').value = b.incl.join('\n');
  setPay(b.pay);
  const who = $('c-business').value.trim() || $('c-name').value.trim();
  if (k !== 'none') $('title').value = b.label + (who ? ' עבור ' + who : '');
};
$('base').addEventListener('change', () => applyBase($('base').value, false));
$('add-custom').addEventListener('click', () => { customRow(); calc(); });
$('pay-10').addEventListener('click', () => setPay(P10));
$('pay-50').addEventListener('click', () => setPay(P50));
$('pay-add').addEventListener('click', () => { payRow([0, '']); calc(); });
$('disc-pct').addEventListener('input', () => { const pct = Number($('disc-pct').value) || 0; $('disc-amount').value = Math.round(subtotal() * pct / 100); calc(); });
$('disc-amount').addEventListener('input', () => { $('disc-pct').value = ''; });
$('launch-10').addEventListener('click', () => { $('disc-label').value = 'מחיר השקה (תמורת הצגת האתר בעבודות והמלצה כתובה)'; $('disc-pct').value = 10; $('disc-pct').dispatchEvent(new Event('input')); });

/* חישוב */
const items = () => {
  const out = [];
  const k = $('base').value;
  if (k !== 'none') out.push({ label: BASES[k].label, qty: 1, price: Number($('base-price').value) || 0 });
  document.querySelectorAll('#addons .line').forEach((l) => {
    if (!l.querySelector('[data-on]').checked) return;
    const a = ADDONS.find((x) => x.k === l.dataset.k);
    const q = l.querySelector('[data-qty]');
    out.push({ label: a.label, qty: q ? Math.max(1, Number(q.value) || 1) : 1, price: Number(l.querySelector('[data-price]').value) || 0 });
  });
  document.querySelectorAll('#custom .line').forEach((l) => {
    const label = l.querySelector('[data-label]').value.trim();
    if (label) out.push({ label, qty: Math.max(1, Number(l.querySelector('[data-qty]').value) || 1), price: Number(l.querySelector('[data-price]').value) || 0 });
  });
  return out;
};
const subtotal = () => items().reduce((s, i) => s + i.price * i.qty, 0);
const payRows = () => [...document.querySelectorAll('#pay .line')].map((l) => ({ pct: Number(l.querySelector('[data-pct]').value) || 0, when: l.querySelector('[data-when]').value.trim() }));
const recRows = () => [...document.querySelectorAll('#recurring .line')].filter((l) => l.querySelector('[data-on]').checked).map((l) => { const r = RECURRING.find((x) => x.k === l.dataset.k); return { label: r.label, price: Number(l.querySelector('[data-price]').value) || 0, per: r.per, note: r.note }; });
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
  pay: payRows(), title: $('title').value, discLabel: $('disc-label').value, discAmount: $('disc-amount').value,
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
    client: u.client, title: u.title, items: items(), recurring: recRows(),
    discount: { label: u.discLabel.trim(), amount: Number(u.discAmount) || 0 },
    terms: { designs: u.terms[0], revisions: u.terms[1], days: u.terms[2], support: u.terms[3] },
    payment: payRows(), valid_until: u.valid, includes: u.includes, notes: u.notes, lead_id: LEAD_ID, ui: u
  });
});

/* טעינה */
$('valid').value = VALID_DEFAULT;
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
  if (PREFILL && /אנגלית|English/.test(PREFILL.plan || '')) document.querySelector('#addons [data-k="en"] [data-on]').checked = true;
  const extra = (PREFILL?.plan || '').match(/כולל (\d+) עמודים נוספים|כולל עמוד אחד נוסף/);
  if (extra) { const l = document.querySelector('#addons [data-k="page"]'); l.querySelector('[data-on]').checked = true; l.querySelector('[data-qty]').value = extra[1] || 1; }
  const A = PREFILL && PREFILL.auto;
  if (A) {
    Object.entries(A.addons || {}).forEach(([k, v]) => { const l = document.querySelector(`#addons [data-k="${k}"]`); if (!l || !v) return; l.querySelector('[data-on]').checked = true; if (typeof v === 'number' && l.querySelector('[data-qty]')) l.querySelector('[data-qty]').value = v; });
    (A.custom || []).forEach(customRow);
    Object.entries(A.recurring || {}).forEach(([k, v]) => { const l = document.querySelector(`#recurring [data-k="${k}"]`); if (l) l.querySelector('[data-on]').checked = !!v; });
    if (A.who && guess !== 'none') $('title').value = BASES[guess].label + ' עבור ' + A.who;
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
adm_page(($num ? $num . ' | ' : '') . 'הצעת מחיר | YYG', (string) ob_get_clean(), '
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
