<?php
/*
 * YYG: עמוד נתונים פרטי. נפתח רק עם המפתח שמוגדר ב־_private/mail-config.php ('stats_key').
 * ?k=המפתח              עמוד בעברית
 * ?k=המפתח&format=json  אותם נתונים כ־JSON
 */
declare(strict_types=1);
/* הנתונים נשמרים מחוץ לתיקיית האתר, כדי שעדכון של האתר לא ימחק אותם */
/* קובץ ההגדרות הפרטי: נשמר גם בתיקיית הנתונים שמחוץ לאתר, כך שעדכון של האתר בלי הקובץ לא יפגע בטופס */
function private_config(): array {
    $safe = data_dir() . '/mail-config.php';
    $fresh = __DIR__ . '/_private/mail-config.php';
    if (is_file($fresh) && (!is_file($safe) || filemtime($fresh) > filemtime($safe))) @copy($fresh, $safe);
    $f = is_file($safe) ? $safe : $fresh;
    return is_file($f) ? (array) require $f : [];
}

function data_dir(): string {
    $d = dirname(__DIR__) . '/yyg-data';
    if (!is_dir($d)) @mkdir($d, 0700, true);
    return is_dir($d) && is_writable($d) ? $d : __DIR__ . '/_private';
}

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
$cfg = private_config();
$key = (string) ($cfg['stats_key'] ?? '');
if ($key === '' || !hash_equals($key, (string) ($_GET['k'] ?? ''))) { http_response_code(404); exit('Not found'); }
/* העמוד עבר לדף הניהול. הקישור הישן ממשיך לעבוד ומעביר לשם (חוץ מ־JSON) */
if (($_GET['format'] ?? '') !== 'json') { header('Location: /admin/?k=' . rawurlencode($key), true, 302); exit; }

$tz = new DateTimeZone('Asia/Jerusalem');
$dir = data_dir() . '/stats';
$rows = [];
foreach ([gmdate('Y-m', strtotime('-1 month')), gmdate('Y-m')] as $m) {
    $f = "$dir/$m.log";
    if (!is_file($f)) continue;
    foreach (file($f, FILE_IGNORE_NEW_LINES) ?: [] as $l) { $r = json_decode($l, true); if (is_array($r)) $rows[] = $r; }
}
$dayOf = static fn(int $t): string => (new DateTime('@' . $t))->setTimezone($tz)->format('Y-m-d');
$today = (new DateTime('now', $tz))->format('Y-m-d');
$since = static fn(int $days): string => (new DateTime('now', $tz))->modify('-' . ($days - 1) . ' days')->format('Y-m-d');

$sum = static function (array $rows, string $from) use ($dayOf): array {
    $v = []; $views = 0; $ev = ['plan' => [], 'packages' => [], 'contact' => [], 'planner' => [], 'pick' => [], 'lead' => []];
    foreach ($rows as $r) {
        if ($dayOf($r['t']) < $from) continue;
        $id = $dayOf($r['t']) . $r['v'];
        if ($r['e'] === 'view') { $views++; $v[$id] = 1; }
        elseif (isset($ev[$r['e']])) $ev[$r['e']][$id] = 1;
    }
    return ['visitors' => count($v), 'views' => $views] + array_map('count', $ev);
};
$periods = ['היום' => $sum($rows, $today), '7 ימים' => $sum($rows, $since(7)), '30 יום' => $sum($rows, $since(30))];

$byDay = []; $refs = []; $dev = ['m' => [], 'd' => []]; $src = [];
for ($i = 13; $i >= 0; $i--) $byDay[(new DateTime('now', $tz))->modify("-$i days")->format('Y-m-d')] = [];
foreach ($rows as $r) {
    if ($r['e'] !== 'view') continue;
    $d = $dayOf($r['t']); $id = $d . $r['v'];
    if (isset($byDay[$d])) $byDay[$d][$id] = 1;
    if ($d >= $since(30)) {
        $dev[$r['d']][$id] = 1;
        if ($r['r'] !== '') $refs[$r['r']] = ($refs[$r['r']] ?? 0) + 1;
        if (($r['s'] ?? '') !== '') $src[$r['s']] = ($src[$r['s']] ?? 0) + 1;
    }
}
$byDay = array_map('count', $byDay);
arsort($refs); arsort($src);

// פניות וספאם, מהיומנים של הטופס
$leads = 0; $spam = 0;
foreach ((file(data_dir() . '/leads.log', FILE_IGNORE_NEW_LINES) ?: []) as $l) if ($dayOf((int) $l) >= $since(30)) $leads++;
foreach ((file(data_dir() . '/spam.log', FILE_IGNORE_NEW_LINES) ?: []) as $l) { $t = strtotime(substr($l, 0, 25)); if ($t && $dayOf($t) >= $since(30)) $spam++; }

$data = ['periods' => $periods, 'by_day' => $byDay, 'referrers' => array_slice($refs, 0, 10, true), 'sources' => array_slice($src, 0, 10, true),
         'devices' => ['phone' => count($dev['m']), 'computer' => count($dev['d'])], 'leads_30d' => $leads, 'spam_blocked_30d' => $spam];
if (($_GET['format'] ?? '') === 'json') { header('Content-Type: application/json; charset=utf-8'); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); exit; }

$e = static fn($s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$max = max(1, ...array_values($byDay));
header('Content-Type: text/html; charset=utf-8');
?><!doctype html>
<html lang="he" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title>נתוני האתר | YYG</title>
<style>
:root{--bg:#151920;--panel:#262D3A;--line:#343C4B;--text:#E9EDF3;--muted:#A3ADBD;--gold:#E3B341;--cyan:#3FD8E6}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font:16px/1.5 system-ui,"Segoe UI",Arial,sans-serif}
.wrap{max-width:900px;margin:0 auto;padding:24px 16px 48px}h1{font-size:1.6rem;margin:0 0 4px}.muted{color:var(--muted)}
.grid{display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));margin-top:20px}
.card{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:16px}
.card h2{font-size:1rem;margin:0 0 10px;color:var(--muted)}
.big{font-size:2.2rem;font-weight:800;line-height:1}.row{display:flex;justify-content:space-between;gap:8px;padding:6px 0;border-top:1px solid var(--line)}
.row:first-of-type{border-top:0}.bars{display:grid;gap:6px;margin-top:6px}
.bar{display:grid;grid-template-columns:70px 1fr 34px;align-items:center;gap:8px;font-size:.9rem}
.bar i{display:block;height:12px;border-radius:99px;background:linear-gradient(270deg,#fff,var(--cyan))}
.wide{grid-column:1/-1}small{color:var(--muted)}
</style></head><body><div class="wrap">
<h1>נתוני האתר yygweb.com</h1>
<p class="muted">נספרים רק אנשים אמיתיים, בלי רובוטים. בלי עוגיות ובלי שמירת פרטים אישיים. עודכן: <?= $e((new DateTime('now', $tz))->format('d/m/Y H:i')) ?></p>
<div class="grid">
<?php foreach ($periods as $name => $p): ?>
 <div class="card"><h2><?= $e($name) ?></h2>
  <div class="big"><?= (int) $p['visitors'] ?></div><small>מבקרים, <?= (int) $p['views'] ?> צפיות</small>
  <div style="margin-top:10px">
   <div class="row"><span>הגיעו למחשבון המחיר</span><b><?= (int) $p['plan'] ?></b></div>
   <div class="row"><span>השתמשו במחשבון</span><b><?= (int) $p['planner'] ?></b></div>
   <div class="row"><span>הגיעו למסלולים</span><b><?= (int) $p['packages'] ?></b></div>
   <div class="row"><span>בחרו מסלול</span><b><?= (int) $p['pick'] ?></b></div>
   <div class="row"><span>הגיעו ליצירת קשר</span><b><?= (int) $p['contact'] ?></b></div>
   <div class="row"><span>השאירו פרטים</span><b><?= (int) $p['lead'] ?></b></div>
  </div>
 </div>
<?php endforeach; ?>
 <div class="card wide"><h2>מבקרים ב־14 הימים האחרונים</h2><div class="bars">
 <?php foreach ($byDay as $d => $n): ?><div class="bar"><span><?= $e((new DateTime($d))->format('d/m')) ?></span><span><i style="width:<?= max(2, round($n / $max * 100)) ?>%"></i></span><b><?= $n ?></b></div><?php endforeach; ?>
 </div></div>
 <div class="card"><h2>פניות ב־30 יום</h2><div class="big"><?= $leads ?></div><small>ספאם שנחסם: <?= $spam ?></small></div>
 <div class="card"><h2>מכשירים ב־30 יום</h2><div class="row"><span>טלפון</span><b><?= count($dev['m']) ?></b></div><div class="row"><span>מחשב</span><b><?= count($dev['d']) ?></b></div></div>
 <div class="card"><h2>מאיפה הגיעו (30 יום)</h2>
  <?php if (!$refs && !$src): ?><small>עוד אין נתונים. כניסה ישירה או מקוד QR לא מופיעה כאן.</small><?php endif; ?>
  <?php foreach ($src as $s => $n): ?><div class="row"><span>קמפיין: <?= $e($s) ?></span><b><?= $n ?></b></div><?php endforeach; ?>
  <?php foreach ($refs as $r => $n): ?><div class="row"><span><?= $e($r) ?></span><b><?= $n ?></b></div><?php endforeach; ?>
 </div>
</div></div></body></html>
