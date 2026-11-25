<?php
/*
 * YYG: דף הניהול של יהודה. https://yygweb.com/admin/
 * כניסה: הקישור האישי (?k=המפתח) או הקלדת המפתח. אחרי הכניסה נשמרת עוגייה חתומה ל־30 יום, והמפתח יורד מהכתובת.
 * מה יש כאן: פניות עם סטטוס והערה, הצעות מחיר, נתוני כניסות, ספאם שנחסם, וקישורים מהירים.
 */
declare(strict_types=1);
require __DIR__ . '/_lib.php';
require dirname(__DIR__) . '/account/_acc.php';
adm_headers();
if (adm_key() === '') { http_response_code(503); exit('חסר מפתח בהגדרות'); }

/* ---------- כניסה ---------- */
$ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''))[0]);
$failFile = data_dir() . '/admin-fail-' . substr(hash('sha256', $ip), 0, 16);
$fails = array_filter(array_map('intval', @file($failFile, FILE_IGNORE_NEW_LINES) ?: []), static fn($t) => $t > time() - 3600);
$tryKey = trim((string) ($_GET['k'] ?? ($_POST['k'] ?? '')));
if ($tryKey !== '') {
    if (count($fails) >= 10) { http_response_code(429); exit('יותר מדי ניסיונות. נסו שוב בעוד שעה.'); }
    if (hash_equals(adm_key(), $tryKey)) { adm_login(); @unlink($failFile); header('Location: /admin/', true, 303); exit; }
    $fails[] = time(); @file_put_contents($failFile, implode("\n", $fails));
}
if (isset($_GET['logout'])) { adm_logout(); header('Location: /admin/', true, 303); exit; }

/* כניסה עם קישור במייל: לוחצים, מקבלים מייל לתיבה של העסק (שמעבירה עותק לג׳ימייל), ולוחצים על הקישור.
   הקישור חד־פעמי ובתוקף ל־15 דקות. הוא פותח דף עם כפתור (ולא נכנס מיד), כדי שסורקי קישורים במייל לא ינצלו אותו. */
$adminMail = (string) (private_config()['to'] ?? '');
$maskMail = static fn(string $m): string => preg_replace('/^(.).*?(@.*)$/u', '$1•••$2', $m);
$magicFile = 'admin-magic.json';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['do'] ?? '') === 'magic_send') {
    $msg = 'שלחנו קישור כניסה ל־<bdi>' . h($maskMail($adminMail)) . '</bdi>. הוא מגיע גם לג׳ימייל שלך. הקישור בתוקף ל־15 דקות.';
    if (!rate_ok('admin-magic-ip-' . $ip, 4, 900) || !rate_ok('admin-magic-all', 10, 3600)) $msg = 'כבר שלחנו כמה קישורים. כדאי לבדוק את המייל (גם בספאם), או לנסות שוב בעוד רבע שעה.';
    else {
        $tok = bin2hex(random_bytes(20));
        data_update($magicFile, static function (array &$m) use ($tok): void {
            $m = array_filter($m, static fn($x) => (int) ($x['exp'] ?? 0) > time() && empty($x['used']));
            $m[] = ['h' => hash('sha256', $tok), 'exp' => time() + 900];
            $m = array_slice(array_values($m), -5);
        });
        $link = 'https://yygweb.com/admin/?magic=' . $tok;
        mail_send($adminMail, 'כניסה לדף הניהול של YYG', mail_wrap('כניסה לדף הניהול', '<p>לחצו על הכפתור כדי להיכנס. הקישור בתוקף ל־15 דקות, ועובד פעם אחת.</p>' . mail_button($link, 'כניסה לדף הניהול') . '<p style="color:#5A6472;font-size:14px">לא ביקשתם להיכנס? אפשר להתעלם מהמייל. בלי הקישור אי אפשר להיכנס.</p>'), "כניסה לדף הניהול: $link");
    }
    adm_page('כניסה לניהול | YYG', '<div class="login"><h1>בדקו את המייל</h1><p>' . $msg . '</p><p class="muted">לא הגיע תוך דקה? כדאי לבדוק בתיקיית הספאם.</p><p><a class="btn" href="/admin/">חזרה</a></p></div>');
}
$magic = (string) ($_POST['magic'] ?? $_GET['magic'] ?? '');
if ($magic !== '' && preg_match('/^[a-f0-9]{40}$/', $magic)) {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        adm_page('כניסה לניהול | YYG', '<div class="login"><h1>כניסה לדף הניהול</h1><p class="muted">לחצו כדי להיכנס. הדפדפן הזה יזכור אתכם ל־30 יום.</p><form method="post" action="/admin/"><input type="hidden" name="magic" value="' . h($magic) . '"><button class="btn gold" type="submit">כניסה</button></form></div>');
    }
    $ok = data_update($magicFile, static function (array &$m) use ($magic): bool {
        foreach ($m as $i => $x) if (empty($x['used']) && (int) $x['exp'] > time() && hash_equals((string) $x['h'], hash('sha256', $magic))) { $m[$i]['used'] = time(); return true; }
        return false;
    });
    if ($ok) { adm_login(); header('Location: /admin/', true, 303); exit; }
    adm_page('כניסה לניהול | YYG', '<div class="login"><h1>הקישור כבר לא בתוקף</h1><p class="muted">הקישור עובד פעם אחת ובמשך 15 דקות. אפשר לבקש קישור חדש.</p><form method="post" action="/admin/"><input type="hidden" name="do" value="magic_send"><button class="btn gold" type="submit">שלחו לי קישור חדש</button></form></div>');
}

if (!adm_authed()) {
    adm_page('כניסה לניהול | YYG', '<div class="login"><h1>דף הניהול של YYG</h1>'
        . '<p class="muted">נשלח לך קישור כניסה למייל <bdi>' . h($maskMail($adminMail)) . '</bdi>, שמגיע גם לג׳ימייל שלך. לוחצים עליו ונכנסים. הדפדפן יזכור אותך ל־30 יום.</p>'
        . '<form method="post" action="/admin/"><input type="hidden" name="do" value="magic_send"><button class="btn gold" type="submit">שלחו לי קישור כניסה למייל</button></form>'
        . '<details' . ($tryKey !== '' ? ' open' : '') . ' style="margin-top:18px"><summary>כניסה עם מפתח</summary>'
        . ($tryKey !== '' ? '<p style="color:var(--bad)">המפתח לא נכון.</p>' : '')
        . '<form method="post" action="/admin/"><label>מפתח<input type="password" name="k" autocomplete="current-password" required></label><button class="btn" type="submit">כניסה</button></form></details></div>');
}

/* ---------- נתונים ---------- */
$tz = tz();
$dir = data_dir();
$e = static fn($s): string => h($s);
$fmt = static fn(int $t, string $f = 'd/m H:i'): string => fmt_time($t, $f);
$csrf = adm_csrf();
const STATUSES = ['new' => 'חדש', 'called' => 'דיברנו', 'quote' => 'נשלחה הצעה', 'won' => 'לקוח', 'lost' => 'לא רלוונטי'];

$leads = leads_load();
$statusFile = "$dir/lead-status.json";
$status = json_decode((string) @file_get_contents($statusFile), true) ?: [];
$quotes = quotes_load();
$forms = [];
foreach ((@file("$dir/forms.jsonl", FILE_IGNORE_NEW_LINES) ?: []) as $l) { $r = json_decode($l, true); if (is_array($r) && !empty($r['id'])) $forms[$r['id']] = $r; }

/* שמירת סטטוס והערה */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['do'] ?? '') === 'status') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) { http_response_code(400); exit('הטופס פג תוקף. רעננו את הדף ונסו שוב.'); }
    $id = (string) ($_POST['id'] ?? '');
    $st = (string) ($_POST['st'] ?? 'new');
    if (isset($leads[$id]) && isset(STATUSES[$st])) {
        $status[$id] = ['st' => $st, 'note' => mb_substr(trim((string) ($_POST['note'] ?? '')), 0, 2000, 'UTF-8'), 'u' => time()];
        @file_put_contents($statusFile, json_encode($status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }
    header('Location: /admin/?saved=1' . (isset($_GET['s']) ? '&s=' . urlencode((string) $_GET['s']) : '') . '#lead-' . rawurlencode($id), true, 303);
    exit;
}
$stOf = static fn(string $id): string => $status[$id]['st'] ?? 'new';

/* מחיקת פנייה, שאלון או הצעת מחיר (למשל בדיקות) */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && in_array($_POST['do'] ?? '', ['lead_delete', 'form_delete', 'quote_delete'], true)) {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) { http_response_code(400); exit('הטופס פג תוקף. רעננו את הדף ונסו שוב.'); }
    $did = (string) ($_POST['id'] ?? '');
    $where = '';
    if ($_POST['do'] === 'lead_delete') { jsonl_remove('leads.jsonl', $did); unset($status[$did]); @file_put_contents($statusFile, json_encode($status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX); $where = 'leads'; }
    if ($_POST['do'] === 'form_delete') { jsonl_remove('forms.jsonl', $did); form_files_remove($did); $where = 'forms'; }
    if ($_POST['do'] === 'quote_delete') { $qq = quotes_load(); unset($qq[$did]); quotes_save($qq); $where = 'quotes'; }
    header('Location: /admin/?deleted=1#' . $where, true, 303); exit;
}

/* רשימת התפוצה: הסרה ידנית, וייצוא משתמשים ומנויים */
$users = users_load();
$subs = subs_load();
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['do'] ?? '') === 'sub_off') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) { http_response_code(400); exit('הטופס פג תוקף.'); }
    sub_set_status((string) ($_POST['email'] ?? ''), 'unsubscribed');
    header('Location: /admin/?saved=1#users', true, 303); exit;
}
if (in_array($_GET['export'] ?? '', ['subs', 'users'], true)) {
    $which = $_GET['export'];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="yyg-' . $which . '-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $cell = static fn($v): string => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'" . $v : (string) $v;
    if ($which === 'subs') {
        fputcsv($out, ['מייל', 'שם', 'סטטוס', 'שפה', 'מקור', 'הצטרף', 'אישר', 'נוסח ההסכמה']);
        foreach ($subs as $sb) fputcsv($out, array_map($cell, [$sb['email'], $sb['name'] ?? '', $sb['status'] ?? '', $sb['lang'] ?? '', $sb['source'] ?? '', fmt_time((int) ($sb['created'] ?? 0)), !empty($sb['confirmed']) ? fmt_time((int) $sb['confirmed']) : '', $sb['consent'] ?? '']));
    } else {
        fputcsv($out, ['שם', 'מייל', 'טלפון', 'נרשם', 'מייל מאומת', 'כניסה אחרונה', 'רשימת תפוצה']);
        foreach ($users as $u) fputcsv($out, array_map($cell, [$u['name'] ?? '', $u['email'] ?? '', $u['phone'] ?? '', fmt_time((int) ($u['created'] ?? 0)), !empty($u['verified']) ? 'כן' : 'לא', !empty($u['last_login']) ? fmt_time((int) $u['last_login']) : '', !empty($u['newsletter']) ? 'כן' : 'לא']));
    }
    exit;
}

/* ייצוא הפניות לאקסל */
if (($_GET['export'] ?? '') === '1') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="yyg-leads-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['תאריך', 'שם', 'טלפון', 'שם העסק', 'מתי נוח', 'רקע מועדף', 'אתרים שאוהב', 'הערות', 'מסלול ותכנית', 'סטטוס', 'הערה שלי']);
    /* תא שמתחיל ב־= + - @ יכול להפוך לנוסחה באקסל: מוסיפים לפניו גרש */
    $cell = static fn($v): string => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'" . $v : (string) $v;
    foreach (array_reverse($leads, true) as $id => $r) fputcsv($out, array_map($cell, [$fmt((int) $r['t'], 'd/m/Y H:i'), $r['name'] ?? '', $r['phone'] ?? '', $r['business'] ?? '', $r['when'] ?? '', $r['style'] ?? '', $r['likes'] ?? '', $r['notes'] ?? '', $r['plan'] ?? '', STATUSES[$stOf((string) $id)], $status[$id]['note'] ?? '']));
    exit;
}

/* כניסות: חודש קודם ונוכחי */
$rows = [];
foreach ([gmdate('Y-m', strtotime('first day of last month')), gmdate('Y-m')] as $m) {
    foreach ((@file("$dir/stats/$m.log", FILE_IGNORE_NEW_LINES) ?: []) as $l) { $r = json_decode($l, true); if (is_array($r)) $rows[] = $r; }
}
$dayOf = static fn(int $t): string => (new DateTime('@' . $t))->setTimezone($tz)->format('Y-m-d');
$today = (new DateTime('now', $tz))->format('Y-m-d');
$since = static fn(int $days): string => (new DateTime('now', $tz))->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
$EV = ['plan' => 'הגיעו למחשבון המחיר', 'planner' => 'השתמשו במחשבון', 'packages' => 'הגיעו למסלולים', 'pick' => 'בחרו מסלול', 'contact' => 'הגיעו ליצירת קשר', 'lead' => 'השאירו פרטים'];
$sum = static function (string $from) use ($rows, $dayOf, $EV): array {
    $v = []; $views = 0; $ev = array_fill_keys(array_keys($EV), []);
    foreach ($rows as $r) {
        if ($dayOf((int) $r['t']) < $from) continue;
        $id = $dayOf((int) $r['t']) . $r['v'];
        if ($r['e'] === 'view') { $views++; $v[$id] = 1; } elseif (isset($ev[$r['e']])) $ev[$r['e']][$id] = 1;
    }
    return ['visitors' => count($v), 'views' => $views] + array_map('count', $ev);
};
$periods = ['היום' => $sum($today), '7 ימים' => $sum($since(7)), '30 יום' => $sum($since(30))];
$byDay = [];
for ($i = 13; $i >= 0; $i--) $byDay[(new DateTime('now', $tz))->modify("-$i days")->format('Y-m-d')] = [];
$pages = []; $refs = []; $src = []; $dev = ['m' => [], 'd' => []];
foreach ($rows as $r) {
    if ($r['e'] !== 'view') continue;
    $d = $dayOf((int) $r['t']); $id = $d . $r['v'];
    if (isset($byDay[$d])) $byDay[$d][$id] = 1;
    if ($d < $since(30)) continue;
    $dev[$r['d']][$id] = 1;
    $pages[$r['p'] ?? '/'][$id] = 1;
    if (($r['r'] ?? '') !== '') $refs[$r['r']] = ($refs[$r['r']] ?? 0) + 1;
    if (($r['s'] ?? '') !== '') $src[$r['s']] = ($src[$r['s']] ?? 0) + 1;
}
$byDay = array_map('count', $byDay);
$pages = array_map('count', $pages); arsort($pages); arsort($refs); arsort($src);
$PAGE_NAMES = ['/' => 'עמוד הבית', '/index.html' => 'עמוד הבית', '/en/' => 'עמוד הבית באנגלית', '/guides/' => 'מדריכים', '/en/guides/' => 'מדריכים באנגלית', '/services/' => 'שירותים', '/en/services/' => 'שירותים באנגלית',
    '/guides/website-cost.html' => 'מדריך: כמה עולה אתר', '/guides/before-you-build.html' => 'מדריך: מה להכין לפני שבונים', '/guides/landing-or-website.html' => 'מדריך: דף נחיתה או אתר',
    '/services/landing-page.html' => 'שירות: דף נחיתה', '/services/business-website.html' => 'שירות: אתר לעסק', '/services/online-store.html' => 'שירות: חנות', '/services/institutions.html' => 'שירות: מוסדות', '/services/logo-branding.html' => 'שירות: לוגו ומיתוג'];
$SRC_NAMES = ['guide-before-you-build' => 'מהמדריך "מה להכין לפני שבונים"', 'guide-website-cost' => 'מהמדריך "כמה עולה אתר"', 'guide-landing-or-website' => 'מהמדריך "דף נחיתה או אתר"', 'guide-index' => 'מעמוד המדריכים'];

/* ספאם שנחסם */
$spam = array_slice(array_reverse(@file("$dir/spam.log", FILE_IGNORE_NEW_LINES) ?: []), 0, 30);

/* ---------- תצוגה ---------- */
$filter = (string) ($_GET['s'] ?? 'all');
$counts = array_fill_keys(array_keys(STATUSES), 0);
foreach ($leads as $id => $r) $counts[$stOf((string) $id)]++;
$new = $counts['new'];
$max = max(1, ...array_values($byDay));
$maxP = max(1, ...array_values($pages ?: [0]));
ob_start(); ?>
<?= adm_nav($new) ?>
<main class="wrap">
<h1>שלום יהודה</h1>
<p class="muted">עודכן <?= $e((new DateTime('now', $tz))->format('d/m/Y H:i')) ?>. נספרים רק אנשים אמיתיים, בלי רובוטים ובלי עוגיות.</p>
<?php if (isset($_GET['saved'])): ?><p class="flash">נשמר.</p><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><p class="flash">נמחק.</p><?php endif; ?>

<div class="kpis">
 <a class="card" href="#leads"><h3>פניות חדשות</h3><div class="big"><?= $new ?></div><small>מחכות לטיפול</small></a>
 <div class="card"><h3>פניות ב־30 יום</h3><div class="big"><?= count(array_filter($leads, static fn($r) => $dayOf((int) $r['t']) >= $since(30))) ?></div><small>מכל הטפסים</small></div>
 <a class="card" href="#stats"><h3>מבקרים היום</h3><div class="big"><?= (int) $periods['היום']['visitors'] ?></div><small><?= (int) $periods['7 ימים']['visitors'] ?> ב־7 ימים</small></a>
 <a class="card" href="#stats"><h3>השתמשו במחשבון</h3><div class="big"><?= (int) $periods['30 יום']['planner'] ?></div><small>ב־30 יום</small></a>
</div>

<h2 id="leads">פניות</h2>
<div class="chips">
 <a href="?s=all#leads" class="<?= $filter === 'all' ? 'on' : '' ?>">הכול <b><?= count($leads) ?></b></a>
 <?php foreach (STATUSES as $k => $label): ?><a href="?s=<?= $k ?>#leads" class="<?= $filter === $k ? 'on' : '' ?>"><?= $e($label) ?> <b><?= $counts[$k] ?></b></a><?php endforeach; ?>
 <a href="?export=1">הורדה לאקסל</a>
</div>
<?php if (!$leads): ?><p class="muted">עוד אין פניות שמורות. מעכשיו כל פנייה מהאתר נשמרת כאן, גם אם המייל לא הגיע.</p><?php endif; ?>
<?php foreach (array_reverse($leads, true) as $id => $r):
    $st = $stOf((string) $id); if ($filter !== 'all' && $filter !== $st) continue;
    $digits = preg_replace('/\D/', '', (string) ($r['phone'] ?? ''));
    $wa = preg_replace('/^0/', '972', $digits); ?>
 <article class="lead st-<?= $st ?>" id="lead-<?= $e($id) ?>">
  <div class="lead-h"><b><?= $e($r['name'] ?? '') ?></b><span class="muted"><?= $e($fmt((int) $r['t'], 'd/m/Y H:i')) ?></span><span class="tag"><?= $e(STATUSES[$st]) ?></span>
   <?php if (!empty($r['score'])): ?><span class="tag warn">חשד לספאם: <?= $e(implode(', ', (array) ($r['why'] ?? []))) ?></span><?php endif; ?>
   <?php if (empty($r['sent'])): ?><span class="tag bad">המייל לא נשלח, הפנייה נשמרה רק כאן</span><?php endif; ?>
   <?php if (($r['lang'] ?? '') === 'en'): ?><span class="tag">מהאתר באנגלית</span><?php endif; ?></div>
  <div class="acts"><a class="btn gold" href="tel:<?= $e(preg_replace('/[^\d+]/', '', (string) ($r['phone'] ?? ''))) ?>"><bdi><?= $e($r['phone'] ?? '') ?></bdi></a><a class="btn" href="/admin/quote.php?lead=<?= $e(rawurlencode((string) $id)) ?>">הצעת מחיר</a><button class="btn" type="button" data-copy-link="<?= $e('https://yygweb.com/questionnaire/?' . http_build_query(['name' => $r['name'] ?? '', 'phone' => $r['phone'] ?? ''])) ?>">העתקת קישור לשאלון</button><form method="post" class="del" onsubmit="return confirm('למחוק את הפנייה? אי אפשר לשחזר.')"><input type="hidden" name="do" value="lead_delete"><input type="hidden" name="csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="id" value="<?= $e((string) $id) ?>"><button class="btn" type="submit" aria-label="מחיקה">מחיקה</button></form></div>
  <dl class="kv">
   <?php foreach (['business' => 'שם העסק', 'when' => 'מתי נוח', 'style' => 'רקע מועדף'] as $k => $label): if (($r[$k] ?? '') === '') continue; ?><dt><?= $label ?></dt><dd><?= $e($r[$k]) ?></dd><?php endforeach; ?>
  </dl>
  <details><summary>כל הפרטים: מסלול, תכנית והערות</summary><dl class="kv">
   <?php foreach (['plan' => 'מסלול ותכנית', 'likes' => 'אתרים שאוהב', 'notes' => 'הערות', 'ready' => 'רשימת ההכנה'] as $k => $label): if (($r[$k] ?? '') === '') continue; ?><dt><?= $label ?></dt><dd><?= $e($r[$k]) ?></dd><?php endforeach; ?>
  </dl></details>
  <form class="st" method="post" action="/admin/<?= $filter !== 'all' ? '?s=' . $e($filter) : '' ?>">
   <input type="hidden" name="do" value="status"><input type="hidden" name="id" value="<?= $e($id) ?>"><input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
   <select name="st" aria-label="סטטוס"><?php foreach (STATUSES as $k => $label): ?><option value="<?= $k ?>"<?= $k === $st ? ' selected' : '' ?>><?= $e($label) ?></option><?php endforeach; ?></select>
   <textarea name="note" rows="1" placeholder="הערה שלי: מה סיכמנו, מתי לחזור" aria-label="הערה"><?= $e($status[$id]['note'] ?? '') ?></textarea>
   <button class="btn" type="submit">שמירה</button>
  </form>
 </article>
<?php endforeach; ?>

<h2 id="forms">שאלונים</h2>
<div class="acts" style="margin:0 0 12px"><a class="btn" href="https://yygweb.com/questionnaire/" target="_blank" rel="noopener">שאלון האפיון באתר</a><a class="btn" href="https://yygweb.com/feedback/" target="_blank" rel="noopener">שאלון המשוב באתר</a></div>
<?php if (!$forms): ?><p class="muted">עוד לא מולאו שאלונים. כל שאלון שלקוח שולח מגיע אליך במייל ומופיע כאן.</p><?php else: ?>
<div class="tscroll"><table class="list"><thead><tr><th>תאריך</th><th>שאלון</th><th>לקוח</th><th>טלפון</th><th></th></tr></thead><tbody>
<?php foreach (array_reverse($forms, true) as $fid => $fr): ?><tr><td><small><?= $e(fmt_time((int) $fr['t'], 'd/m H:i')) ?></small></td><td><a href="/admin/form.php?id=<?= $e(rawurlencode((string) $fid)) ?>"><?= $fr['form'] === 'intake' ? 'אפיון' : 'משוב על עיצובים' ?></a></td><td><?= $e($fr['contact']['name']) ?><?= $fr['contact']['business'] ? ' <small>· ' . $e($fr['contact']['business']) . '</small>' : '' ?><?= !empty($fr['files']) ? ' <span class="tag">' . count($fr['files']) . ' קבצים</span>' : '' ?><?= !empty($fr['flags']) ? ' <span class="tag warn">לבדיקה</span>' : '' ?></td><td><bdi><?= $e($fr['contact']['phone']) ?></bdi></td><td><form method="post" class="del" onsubmit="return confirm('למחוק את השאלון ואת הקבצים שצורפו אליו? אי אפשר לשחזר.')"><input type="hidden" name="do" value="form_delete"><input type="hidden" name="csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="id" value="<?= $e((string) $fid) ?>"><button class="btn" type="submit" aria-label="מחיקה">מחיקה</button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>

<h2 id="quotes">הצעות מחיר</h2>
<div class="acts" style="margin:0 0 12px"><a class="btn gold" href="/admin/quote.php">הצעה חדשה</a></div>
<?php if (!$quotes): ?><p class="muted">עוד אין הצעות. אפשר להתחיל מהכפתור "הצעת מחיר" ליד כל פנייה, או מהצעה חדשה.</p><?php else: ?>
<div class="tscroll"><table class="list"><thead><tr><th>מספר</th><th>לקוח</th><th>סכום</th><th>סטטוס</th><th>צפיות</th><th>עודכנה</th><th></th></tr></thead><tbody>
<?php foreach (array_reverse($quotes, true) as $num => $q): $tq = quote_totals($q); $qs = (string) ($q['status'] ?? 'draft'); ?>
 <tr><td><a href="/admin/quote.php?id=<?= $e(rawurlencode((string) $num)) ?>"><?= $e($num) ?></a></td><td><?= $e($q['client']['name'] ?? '') ?><?= !empty($q['client']['business']) ? ' <small>· ' . $e($q['client']['business']) . '</small>' : '' ?></td><td><?= $e(nis($tq['total'])) ?></td>
 <td><span class="tag<?= $qs === 'approved' ? ' ok' : ($qs === 'declined' ? ' bad' : '') ?>"><?= $e(Q_STATUSES[$qs] ?? $qs) ?></span></td><td><?= (int) ($q['views'] ?? 0) ?></td><td><small><?= $e($fmt((int) ($q['updated'] ?? $q['created'] ?? time()), 'd/m H:i')) ?></small></td><td><form method="post" class="del" onsubmit="return confirm('למחוק את ההצעה? הקישור שנשלח ללקוח יפסיק לעבוד? אי אפשר לשחזר.')"><input type="hidden" name="do" value="quote_delete"><input type="hidden" name="csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="id" value="<?= $e((string) $num) ?>"><button class="btn" type="submit" aria-label="מחיקה">מחיקה</button></form></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>

<h2 id="users">משתמשים ורשימת תפוצה</h2>
<?php $subCount = ['active' => 0, 'pending' => 0, 'unsubscribed' => 0]; foreach ($subs as $sb) { $k = (string) ($sb['status'] ?? 'pending'); if (isset($subCount[$k])) $subCount[$k]++; }
      $verified = count(array_filter($users, static fn($u) => !empty($u['verified']))); ?>
<div class="kpis">
 <div class="card"><h3>משתמשים רשומים</h3><div class="big"><?= count($users) ?></div><small><?= $verified ?> אישרו מייל</small></div>
 <div class="card"><h3>ברשימת התפוצה</h3><div class="big"><?= $subCount['active'] ?></div><small>מנויים פעילים</small></div>
 <div class="card"><h3>ממתינים לאישור</h3><div class="big"><?= $subCount['pending'] ?></div><small>עוד לא לחצו על הקישור</small></div>
 <div class="card"><h3>הוסרו</h3><div class="big"><?= $subCount['unsubscribed'] ?></div><small>ביקשו להסיר</small></div>
</div>
<div class="acts" style="margin:12px 0"><a class="btn gold" href="/admin/mail.php">שליחת עדכון לרשימה</a><a class="btn" href="?export=subs">המנויים לאקסל</a><a class="btn" href="?export=users">המשתמשים לאקסל</a><a class="btn" href="https://yygweb.com/account/" target="_blank" rel="noopener">האזור האישי באתר</a></div>
<?php if ($users): ?><details><summary>משתמשים רשומים (<?= count($users) ?>)</summary><div class="tscroll"><table class="list"><thead><tr><th>שם</th><th>מייל</th><th>טלפון</th><th>נרשם</th><th>מאומת</th><th>רשימה</th></tr></thead><tbody>
<?php foreach (array_reverse($users, true) as $u): ?><tr><td><?= $e($u['name'] ?? '') ?></td><td><bdi><?= $e($u['email'] ?? '') ?></bdi></td><td><bdi><?= $e($u['phone'] ?? '') ?></bdi></td><td><small><?= $e(fmt_time((int) ($u['created'] ?? 0), 'd/m/Y')) ?></small></td><td><?= !empty($u['verified']) ? 'כן' : 'לא' ?></td><td><?= ($subs[$u['email'] ?? '']['status'] ?? '') === 'active' ? 'כן' : 'לא' ?></td></tr><?php endforeach; ?>
</tbody></table></div></details><?php endif; ?>
<?php if ($subs): ?><details><summary>רשימת התפוצה (<?= count($subs) ?>)</summary><div class="tscroll"><table class="list"><thead><tr><th>מייל</th><th>שם</th><th>סטטוס</th><th>מקור</th><th>הצטרף</th><th></th></tr></thead><tbody>
<?php foreach (array_reverse($subs, true) as $sb): $sst = (string) ($sb['status'] ?? 'pending'); ?><tr><td><bdi><?= $e($sb['email']) ?></bdi></td><td><?= $e($sb['name'] ?? '') ?></td><td><span class="tag<?= $sst === 'active' ? ' ok' : ($sst === 'unsubscribed' ? ' bad' : '') ?>"><?= ['active' => 'פעיל', 'pending' => 'ממתין', 'unsubscribed' => 'הוסר'][$sst] ?? $e($sst) ?></span></td><td><small><?= $e($sb['source'] ?? '') ?></small></td><td><small><?= $e(fmt_time((int) ($sb['created'] ?? 0), 'd/m/Y')) ?></small></td>
<td><?php if ($sst !== 'unsubscribed'): ?><form method="post" onsubmit="return confirm('להסיר את הכתובת מהרשימה?')"><input type="hidden" name="do" value="sub_off"><input type="hidden" name="csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="email" value="<?= $e($sb['email']) ?>"><button class="btn" type="submit">הסרה</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></details><?php endif; ?>

<h2 id="stats">כניסות</h2>
<div class="grid" style="margin-bottom:12px">
<?php foreach ($periods as $name => $p): ?>
 <div class="card"><h3><?= $e($name) ?></h3><div class="big"><?= (int) $p['visitors'] ?></div><small>מבקרים, <?= (int) $p['views'] ?> צפיות</small>
  <div style="margin-top:8px"><?php foreach ($EV as $k => $label): ?><div class="row"><span><?= $e($label) ?></span><b><?= (int) $p[$k] ?></b></div><?php endforeach; ?></div></div>
<?php endforeach; ?>
</div>

<div class="grid">
 <div class="card" style="grid-column:1/-1"><h3>מבקרים ב־14 הימים האחרונים</h3><div class="bars">
 <?php foreach ($byDay as $d => $n): ?><div class="bar"><span><?= $e((new DateTime($d))->format('d/m')) ?></span><span><i style="width:<?= max(2, round($n / $max * 100)) ?>%"></i></span><b><?= $n ?></b></div><?php endforeach; ?>
 </div></div>
 <div class="card pages" style="grid-column:1/-1"><h3>העמודים הנצפים ב־30 יום (מבקרים)</h3>
  <?php if (!$pages): ?><small>עוד אין נתונים.</small><?php endif; ?>
  <div class="bars"><?php foreach (array_slice($pages, 0, 15, true) as $p => $n): ?><div class="bar"><span title="<?= $e($p) ?>"><?= $e($PAGE_NAMES[$p] ?? $p) ?></span><span><i style="width:<?= max(2, round($n / $maxP * 100)) ?>%"></i></span><b><?= $n ?></b></div><?php endforeach; ?></div>
</div>
 <div class="card"><h3>מאיפה הגיעו (30 יום)</h3>
  <?php if (!$refs && !$src): ?><small>עוד אין נתונים. כניסה ישירה, מהודעה או מקוד QR לא מופיעה כאן.</small><?php endif; ?>
  <?php foreach ($src as $s => $n): ?><div class="row"><span><?= $e($SRC_NAMES[$s] ?? ('קמפיין: ' . $s)) ?></span><b><?= $n ?></b></div><?php endforeach; ?>
  <?php foreach ($refs as $r => $n): ?><div class="row"><span><bdi><?= $e($r) ?></bdi></span><b><?= $n ?></b></div><?php endforeach; ?></div>
 <div class="card"><h3>מכשירים (30 יום)</h3><div class="row"><span>טלפון</span><b><?= count($dev['m']) ?></b></div><div class="row"><span>מחשב</span><b><?= count($dev['d']) ?></b></div></div>
</div>

<h2 id="spam">ספאם שנחסם</h2>
<div class="card spam"><?php if (!$spam): ?><small>לא נחסם כלום. מצוין.</small><?php endif; ?>
 <?php foreach ($spam as $l): ?><div><?= $e(mb_substr($l, 0, 400, 'UTF-8')) ?></div><?php endforeach; ?></div>

<h2 id="links">קישורים מהירים</h2>
<div class="links">
 <a href="https://yygweb.com/" target="_blank" rel="noopener">האתר<small>yygweb.com</small></a>
 <a href="https://search.google.com/search-console" target="_blank" rel="noopener">Google Search Console<small>איך מוצאים אותך בגוגל</small></a>
 <a href="https://business.google.com/" target="_blank" rel="noopener">פרופיל העסק בגוגל<small>ביקורות ומפות</small></a>
 <a href="https://www.bing.com/webmasters" target="_blank" rel="noopener">Bing Webmaster<small>רישום גם בבינג</small></a>
 <a href="https://mail.hostinger.com/" target="_blank" rel="noopener">תיבת המייל<small>yehuda@yygweb.com</small></a>
 <a href="https://hpanel.hostinger.com/" target="_blank" rel="noopener">Hostinger<small>אחסון, דומיינים ומיילים</small></a>
 <a href="https://xn--cebeaqd7a2ak.com/manage/" target="_blank" rel="noopener">ניהול פרחי שמיים<small>פניות, מנויים ומשתמשים</small></a>
 <a href="https://hakef.com/wp-admin/" target="_blank" rel="noopener">ניהול הכיף שבאפיה<small>מתכונים ורשימת תפוצה</small></a>
</div>
</main>
<script>document.querySelectorAll('[data-copy-link]').forEach((b) => b.addEventListener('click', async () => { try { await navigator.clipboard.writeText(b.dataset.copyLink); b.textContent = 'הועתק'; } catch (e) { prompt('העתיקו את הקישור:', b.dataset.copyLink); } }));</script>
<?php adm_page('ניהול | YYG', (string) ob_get_clean());
