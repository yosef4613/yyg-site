<?php
/*
 * YYG Web Design: מונה כניסות פרטי.
 * סופר רק דפדפנים אמיתיים (הדף שולח אות אחרי שנטען), בלי עוגיות ובלי לשמור כתובת IP.
 * מזהה מבקר = טביעה חד־כיוונית של IP+דפדפן עם מלח שמתחלף כל יום, כך שאי אפשר לעקוב אחרי אדם בין ימים.
 */
declare(strict_types=1);
/* הנתונים נשמרים מחוץ לתיקיית האתר, כדי שעדכון של האתר לא ימחק אותם */
function data_dir(): string {
    $d = dirname(__DIR__) . '/yyg-data';
    if (!is_dir($d)) @mkdir($d, 0700, true);
    return is_dir($d) && is_writable($d) ? $d : __DIR__ . '/_private';
}

header('Cache-Control: no-store');
http_response_code(204);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') exit;
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($ua === '' || preg_match('/bot|crawl|spider|slurp|headless|lighthouse|preview|python|curl|wget|httpclient|scan|monitor|facebookexternalhit|whatsapp|telegram/i', $ua)) exit;
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
if (!preg_match('~^https://(www\.)?yygweb\.com(/|$)~i', $origin)) exit;

$in = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($in)) exit;
$type = in_array($in['e'] ?? '', ['view', 'plan', 'packages', 'contact', 'planner', 'pick', 'lead'], true) ? $in['e'] : '';
if ($type === '') exit;
$page = preg_replace('~[^a-z0-9/._-]~i', '', substr((string) ($in['p'] ?? '/'), 0, 60)) ?: '/';
$ref = '';
if (!empty($in['r']) && is_string($in['r'])) {
    $h = strtolower((string) parse_url($in['r'], PHP_URL_HOST));
    if ($h !== '' && !preg_match('/(^|\.)yygweb\.com$/', $h)) $ref = substr(preg_replace('/^www\./', '', $h), 0, 60);
}
$dev = preg_match('/Mobi|Android|iPhone|iPad/i', $ua) ? 'm' : 'd';
$utm = preg_replace('/[^a-z0-9_-]/i', '', substr((string) ($in['s'] ?? ''), 0, 30));

$dir = data_dir() . '/stats';
if (!is_dir($dir)) @mkdir($dir, 0700, true);
$day = gmdate('Y-m-d');
$saltFile = "$dir/salt-$day";
$salt = @file_get_contents($saltFile);
if (!$salt) {
    $salt = bin2hex(random_bytes(16));
    @file_put_contents($saltFile, $salt);
    foreach (glob("$dir/salt-*") ?: [] as $old) if ($old !== $saltFile) @unlink($old); // מלח של אתמול נמחק
}
/* הכתובת התקינה האחרונה ב־X-Forwarded-For (מה־CDN), כמו client_ip() בספרייה המשותפת */
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
foreach (array_reverse(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))) as $p) { $p = trim($p); if (filter_var($p, FILTER_VALIDATE_IP)) { $ip = $p; break; } }
$vid = substr(hash('sha256', $salt . $ip . $ua), 0, 12);
$line = json_encode(['t' => time(), 'e' => $type, 'p' => $page, 'r' => $ref, 'd' => $dev, 'v' => $vid, 's' => $utm], JSON_UNESCAPED_UNICODE);
$logFile = "$dir/" . gmdate('Y-m') . '.log';
if (@filesize($logFile) < 50 * 1024 * 1024) @file_put_contents($logFile, $line . "\n", FILE_APPEND | LOCK_EX);   // תקרה: 50MB בחודש
