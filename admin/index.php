<?php
/*
 * YYG: דף הניהול של יהודה. https://yygweb.com/admin/
 * כניסה: הקישור האישי (?k=המפתח) או הקלדת המפתח. אחרי הכניסה נשמרת עוגייה חתומה ל־30 יום, והמפתח יורד מהכתובת.
 * מה יש כאן: פניות עם סטטוס והערה, נתוני כניסות, ספאם שנחסם, וקישורים מהירים.
 * הנתונים נשמרים מחוץ לתיקיית האתר (yyg-data), כך שעדכון של האתר לא מוחק אותם.
 */
declare(strict_types=1);

function data_dir(): string {
    $d = dirname(__DIR__, 2) . '/yyg-data';
    if (!is_dir($d)) @mkdir($d, 0700, true);
    return is_dir($d) && is_writable($d) ? $d : dirname(__DIR__) . '/_private';
}
function private_config(): array {
    $safe = data_dir() . '/mail-config.php';
    $fresh = dirname(__DIR__) . '/_private/mail-config.php';
    if (is_file($fresh) && (!is_file($safe) || filemtime($fresh) > filemtime($safe))) @copy($fresh, $safe);
    $f = is_file($safe) ? $safe : $fresh;
    return is_file($f) ? (array) require $f : [];
}

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

$cfg = private_config();
$key = (string) ($cfg['stats_key'] ?? '');
if ($key === '') { http_response_code(503); exit('חסר מפתח בהגדרות'); }

/* ---------- כניסה ---------- */
const COOKIE = 'yyg_admin';
$sign = static fn(string $v): string => hash_hmac('sha256', $v, $key);
$authed = static function () use ($sign): bool {
    $c = (string) ($_COOKIE[COOKIE] ?? '');
    if (!preg_match('/^(\d{10})\.([a-f0-9]{64})$/', $c, $m)) return false;
    return (int) $m[1] > time() && hash_equals($sign($m[1]), $m[2]);
};
$login = static function () use ($sign): void {
    $exp = (string) (time() + 30 * 86400);
    setcookie(COOKIE, $exp . '.' . $sign($exp), ['expires' => (int) $exp, 'path' => '/admin/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
};
$ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''))[0]);
$failFile = data_dir() . '/admin-fail-' . substr(hash('sha256', $ip), 0, 16);
$fails = array_filter(array_map('intval', @file($failFile, FILE_IGNORE_NEW_LINES) ?: []), static fn($t) => $t > time() - 3600);
$tryKey = (string) ($_GET['k'] ?? ($_POST['k'] ?? ''));
if ($tryKey !== '') {
    if (count($fails) >= 10) { http_response_code(429); exit('יותר מדי ניסיונות. נסו שוב בעוד שעה.'); }
    if (hash_equals($key, $tryKey)) { $login(); @unlink($failFile); header('Location: /admin/', true, 303); exit; }
    $fails[] = time(); @file_put_contents($failFile, implode("\n", $fails));
}
if (isset($_GET['logout'])) { setcookie(COOKIE, '', ['expires' => 1, 'path' => '/admin/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']); header('Location: /admin/', true, 303); exit; }

$e = static fn($s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$page = static function (string $title, string $body) use ($e): void {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="he" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>' . $e($title) . '</title><style>' . ADMIN_CSS . '</style></head><body>' . $body . '</body></html>';
    exit;
};
const ADMIN_CSS = ':root{--bg:#151920;--bg2:#1B212B;--panel:#262D3A;--line:#343C4B;--text:#E9EDF3;--muted:#A3ADBD;--gold:#E3B341;--cyan:#3FD8E6;--ok:#7BD88F;--warn:#F2C95A;--bad:#F08A8A}
*{box-sizing:border-box}html{scroll-behavior:smooth;scroll-padding-top:70px}body{margin:0;background:var(--bg);color:var(--text);font:16px/1.55 system-ui,"Segoe UI",Arial,sans-serif}
a{color:#fff}.wrap{max-width:1040px;margin:0 auto;padding:20px 16px 64px}
.top{position:sticky;top:0;z-index:5;background:rgba(21,25,32,.94);backdrop-filter:blur(8px);border-bottom:1px solid var(--line)}
.top .wrap{display:flex;align-items:center;gap:6px 16px;flex-wrap:wrap;padding-block:12px}
.top b{font-size:1.05rem;margin-inline-end:auto}.top a{color:var(--muted);text-decoration:none;font-weight:600;font-size:.95rem;padding:6px 2px}.top a:hover{color:#fff}
h1{font-size:1.5rem;margin:8px 0 2px}h2{font-size:1.2rem;margin:36px 0 12px}.muted{color:var(--muted)}small{color:var(--muted)}
.grid{display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}
.kpis{display:grid;gap:10px;grid-template-columns:repeat(4,minmax(0,1fr));margin-top:16px}.kpis .card{text-decoration:none;padding:12px 14px}.kpis .big{font-size:1.8rem}
@media (max-width:700px){.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
.card{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:16px}.card h3{font-size:.95rem;margin:0 0 8px;color:var(--muted)}
.big{font-size:2.1rem;font-weight:800;line-height:1.1}.row{display:flex;justify-content:space-between;gap:10px;padding:6px 0;border-top:1px solid var(--line)}.row:first-of-type{border-top:0}
.bars{display:grid;gap:6px}.bar{display:grid;grid-template-columns:64px 1fr 40px;align-items:center;gap:8px;font-size:.9rem}
.bar i{display:block;height:12px;border-radius:99px;background:linear-gradient(270deg,#fff,var(--cyan))}
.pages .bar{grid-template-columns:minmax(0,1fr) 120px 40px}.pages .bar span:first-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;direction:ltr;text-align:right}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 14px}.chips a{display:inline-flex;gap:6px;align-items:center;padding:6px 12px;border:1px solid var(--line);border-radius:99px;text-decoration:none;color:var(--text);font-size:.92rem}
.chips a.on{background:var(--text);color:var(--bg);border-color:var(--text)}
.lead{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:14px 16px;margin-bottom:10px}
.lead.st-new{border-color:#5b6578;box-shadow:inset 3px 0 0 var(--cyan)}
.lead-h{display:flex;flex-wrap:wrap;align-items:baseline;gap:4px 14px}.lead-h b{font-size:1.1rem}
.tag{display:inline-block;padding:1px 9px;border-radius:99px;font-size:.8rem;font-weight:700;background:var(--bg2);border:1px solid var(--line)}
.tag.warn{color:var(--warn);border-color:#6b5a26}.tag.bad{color:var(--bad);border-color:#6b3434}
.acts{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}.btn{display:inline-flex;align-items:center;min-height:40px;padding:6px 14px;border-radius:10px;border:1px solid var(--line);background:var(--bg2);color:var(--text);text-decoration:none;font:600 .95rem system-ui,Arial,sans-serif;cursor:pointer}
.btn.gold{background:var(--gold);border-color:var(--gold);color:#1A1404}
.kv{display:grid;grid-template-columns:max-content 1fr;gap:4px 14px;margin:10px 0 0;font-size:.95rem}.kv dt{color:var(--muted)}.kv dd{margin:0;white-space:pre-wrap;overflow-wrap:anywhere}
details{margin-top:8px}summary{cursor:pointer;color:var(--muted);font-weight:600}
form.st{display:grid;grid-template-columns:180px 1fr auto;gap:8px;margin-top:10px;align-items:start}
select,textarea,input[type=password]{width:100%;min-height:40px;padding:8px 10px;border-radius:10px;border:1px solid var(--line);background:var(--bg);color:var(--text);font:400 .95rem system-ui,Arial,sans-serif}
textarea{min-height:40px;resize:vertical}
.links{display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(230px,1fr))}.links a{display:block;padding:12px 14px;border:1px solid var(--line);border-radius:12px;background:var(--panel);text-decoration:none}.links a small{display:block}
.spam{font-size:.88rem;direction:rtl}.spam div{padding:8px 0;border-top:1px solid var(--line);overflow-wrap:anywhere}
.login{max-width:380px;margin:12vh auto;padding:24px;background:var(--panel);border:1px solid var(--line);border-radius:16px}.login form{display:grid;gap:10px;margin-top:14px}
.flash{padding:10px 14px;border-radius:10px;background:#1f3a2a;border:1px solid #2f6b45;margin:12px 0}
@media (max-width:640px){form.st{grid-template-columns:1fr}.pages .bar{grid-template-columns:minmax(0,1fr) 70px 34px}}';

if (!$authed()) {
    $page('כניסה לניהול | YYG', '<div class="login"><h1>דף הניהול של YYG</h1><p class="muted">נכנסים עם הקישור האישי, או מקלידים כאן את המפתח.</p>'
        . ($tryKey !== '' ? '<p style="color:var(--bad)">המפתח לא נכון.</p>' : '')
        . '<form method="post" action="/admin/"><label>מפתח<input type="password" name="k" autocomplete="current-password" required></label><button class="btn gold" type="submit">כניסה</button></form></div>');
}

/* ---------- נתונים ---------- */
$tz = new DateTimeZone('Asia/Jerusalem');
$dir = data_dir();
$fmt = static fn(int $t, string $f = 'd/m H:i'): string => (new DateTime('@' . $t))->setTimezone($tz)->format($f);
$csrf = $sign('csrf' . substr((string) ($_COOKIE[COOKIE] ?? ''), 0, 10));
const STATUSES = ['new' => 'חדש', 'called' => 'דיברנו', 'quote' => 'נשלחה הצעה', 'won' => 'לקוח', 'lost' => 'לא רלוונטי'];

$leads = [];
foreach ((@file("$dir/leads.jsonl", FILE_IGNORE_NEW_LINES) ?: []) as $l) { $r = json_decode($l, true); if (is_array($r) && !empty($r['id'])) $leads[$r['id']] = $r; }
$statusFile = "$dir/lead-status.json";
$status = json_decode((string) @file_get_contents($statusFile), true) ?: [];

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

/* ייצוא הפניות לאקסל */
if (isset($_GET['export'])) {
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
<header class="top"><div class="wrap"><b>ניהול YYG</b><a href="#leads">פניות<?= $new ? ($new === 1 ? ' (חדשה אחת)' : " ($new חדשות)") : '' ?></a><a href="#stats">כניסות</a><a href="#spam">ספאם</a><a href="#links">קישורים</a><a href="?logout=1">יציאה</a></div></header>
<main class="wrap">
<h1>שלום יהודה</h1>
<p class="muted">עודכן <?= $e((new DateTime('now', $tz))->format('d/m/Y H:i')) ?>. נספרים רק אנשים אמיתיים, בלי רובוטים ובלי עוגיות.</p>
<?php if (isset($_GET['saved'])): ?><p class="flash">נשמר.</p><?php endif; ?>

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
  <div class="acts"><a class="btn gold" href="tel:<?= $e(preg_replace('/[^\d+]/', '', (string) ($r['phone'] ?? ''))) ?>"><bdi><?= $e($r['phone'] ?? '') ?></bdi></a><a class="btn" href="https://wa.me/<?= $e($wa) ?>" target="_blank" rel="noopener">וואטסאפ</a></div>
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
  <?php if (!$refs && !$src): ?><small>עוד אין נתונים. כניסה ישירה, מוואטסאפ או מקוד QR לא מופיעה כאן.</small><?php endif; ?>
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
<?php $page('ניהול | YYG', (string) ob_get_clean());
