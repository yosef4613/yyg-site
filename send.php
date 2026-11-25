<?php
/*
 * YYG: קבלת פנייה מהטופס באתר ושליחתה במייל, ישירות מהשרת בהוסטינגר.
 * - הדרך הראשית: שירות המייל של הוסטינגר, עם מפתח שמוגבל לתיבה yehuda@yygweb.com (בלי סיסמת התיבה).
 *   המפתח שמור ב־_private/mail-config.php, שלא נשמר ב־Git ונמצא בתיקייה חסומה.
 * - גיבוי: SMTP, אם הוגדר, ואחריו פונקציית המייל של השרת.
 * מחזיר JSON: {"success":"true"} או {"success":"false","message":"..."}
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

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out(bool $ok, string $msg = '', int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok ? 'true' : 'false', 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- אסימון: חותמת זמן חתומה. הדפדפן מבקש אותה כשהגולש מגיע לטופס ---------- */
function secret_key(): string {
    $f = data_dir() . '/secret.txt';
    $k = is_file($f) ? trim((string) @file_get_contents($f)) : '';
    if (strlen($k) < 32) {
        $k = bin2hex(random_bytes(32));
        if (!@file_put_contents($f, $k)) $k = hash('sha256', __FILE__ . php_uname());
    }
    return $k;
}
function make_token(): string { $t = (string) time(); return $t . '.' . hash_hmac('sha256', $t, secret_key()); }
/** מחזיר את גיל האסימון בשניות, או -1 אם הוא לא תקין */
function token_age(string $tok): int {
    if (!preg_match('/^(\d{9,11})\.([0-9a-f]{64})$/', $tok, $m)) return -1;
    if (!hash_equals(hash_hmac('sha256', $m[1], secret_key()), $m[2])) return -1;
    $age = time() - (int) $m[1];
    return ($age < 0 || $age > 6 * 3600) ? -1 : $age;
}
$o = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($o === 'https://yosef4613.github.io') {
    header('Access-Control-Allow-Origin: ' . $o);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
    header('Vary: Origin');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && isset($_GET['token'])) {
    echo json_encode(['token' => make_token()]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') out(false, 'method', 405);

// רק מהאתר שלנו (או מהעותק שלו בגיטהאב)
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$host = parse_url($origin, PHP_URL_HOST) ?: '';
if ($host !== '' && !preg_match('/(^|\.)yygweb\.com$/i', $host) && $host !== 'yosef4613.github.io' && $host !== 'localhost') out(false, 'origin', 403);

// קריאת הנתונים: JSON או טופס רגיל
$raw = file_get_contents('php://input') ?: '';
$in = json_decode($raw, true);
if (!is_array($in)) $in = $_POST;

// מלכודת לרובוטים: שדה נסתר שאנשים לא ממלאים
if (!empty($in['_honey'])) out(true);

$clean = static function ($v, int $max): string {
    $v = is_string($v) ? $v : '';
    $v = str_replace("\0", '', $v);
    $v = trim(preg_replace('/\r\n?/', "\n", $v));
    return mb_substr($v, 0, $max, 'UTF-8');
};
$f = [
    'name'     => $clean($in['name'] ?? '', 120),
    'phone'    => $clean($in['phone'] ?? '', 40),
    'business' => $clean($in['business'] ?? '', 160),
    'when'     => $clean($in['when'] ?? '', 120),
    'style'    => $clean($in['style'] ?? '', 60),
    'likes'    => $clean($in['likes'] ?? '', 1500),
    'notes'    => $clean($in['notes'] ?? '', 3000),
    'ready'    => $clean($in['ready'] ?? '', 1500),
    'plan'     => $clean($in['plan'] ?? '', 4000),
];
if ($f['name'] === '' || strlen(preg_replace('/\D/', '', $f['phone'])) < 9) out(false, 'missing', 422);

/* ---------- סינון ספאם ----------
   מלכודת זמן: מי ששולח פחות מ־3 שניות אחרי שהטופס נטען הוא רובוט, ונענה "בהצלחה" בלי שליחה.
   ניקוד חשד: ציון 3 ומעלה לא נשלח, ונשמר ביומן spam.log בתיקיית הנתונים שמחוץ לאתר. ציון 1 עד 2 נשלח עם סימון בנושא. */
$age = token_age(is_string($in['_token'] ?? null) ? $in['_token'] : '');
if ($age >= 0 && $age < 3) out(true);
$score = 0; $why = [];
if ($age < 0) { $score += 2; $why[] = 'בלי אסימון מהדף'; }
$free = $f['name'] . ' ' . $f['business'] . ' ' . $f['notes'];
if (!preg_match('/\p{Hebrew}/u', $free . $f['likes'])) { $score += 1; $why[] = 'בלי עברית'; }
if (preg_match_all('~https?://|www\.~i', $free) > 2) { $score += 2; $why[] = 'הרבה קישורים'; }
if (preg_match('~https?://|www\.~i', $f['name'])) { $score += 2; $why[] = 'קישור בשם'; }
if (preg_match('/[\p{Cyrillic}\p{Han}\p{Hiragana}\p{Katakana}\p{Thai}]/u', $free)) { $score += 2; $why[] = 'כתב זר'; }
$digits = preg_replace('/\D/', '', $f['phone']);
if (!preg_match('/^(0\d{8,9}|972\d{8,9})$/', $digits)) { $score += 1; $why[] = 'טלפון לא ישראלי'; }
if ($score >= 3) {
    @file_put_contents(data_dir() . '/spam.log', date('c') . ' [' . implode(', ', $why) . '] ' . json_encode($f, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    out(true);
}

// הגבלה פשוטה: עד 5 פניות ב־10 דקות מאותה כתובת
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0');
$ip = trim(explode(',', $ip)[0]);
$rl = sys_get_temp_dir() . '/yyg-rl-' . hash('sha256', $ip);
$now = time();
$hits = array_filter(array_map('intval', @file($rl, FILE_IGNORE_NEW_LINES) ?: []), static fn($t) => $t > $now - 600);
if (count($hits) >= 5) out(false, 'rate', 429);
$hits[] = $now;
@file_put_contents($rl, implode("\n", $hits));
// תקרה כללית: עד 40 פניות בשעה מכל הכתובות יחד, כדי ששטף של רובוטים לא יציף את התיבה
$gl = sys_get_temp_dir() . '/yyg-rl-all';
$all = array_filter(array_map('intval', @file($gl, FILE_IGNORE_NEW_LINES) ?: []), static fn($t) => $t > $now - 3600);
if (count($all) >= 40) out(false, 'busy', 429);
$all[] = $now;
@file_put_contents($gl, implode("\n", $all));

// הגדרות שליחה
$cfg = [
    'to'        => 'yehuda@yygweb.com',   // התיבה מעבירה עותק לג׳ימייל
    'from'      => 'site@yygweb.com',
    'from_name' => 'האתר של YYG',
    'smtp'      => null, // ['host' => 'smtp.hostinger.com', 'port' => 465, 'user' => '...', 'pass' => '...']
];
$cfg = array_merge($cfg, private_config());

// תוכן המייל
$labels = [
    'name' => 'שם', 'phone' => 'טלפון', 'business' => 'שם העסק', 'when' => 'מתי נוח שתתקשר',
    'style' => 'רקע מועדף', 'likes' => 'אתרים שהוא אוהב', 'notes' => 'הערות', 'ready' => 'רשימת ההכנה', 'plan' => 'המסלול והתכנית',
];
$e = static fn(string $s): string => nl2br(htmlspecialchars($s, ENT_QUOTES, 'UTF-8'));
$tel = preg_replace('/[^\d+]/', '', $f['phone']);
$rows = '';
$text = '';
foreach ($labels as $k => $label) {
    $v = $f[$k] !== '' ? $f[$k] : '—';
    $val = $k === 'phone' ? '<a href="tel:' . htmlspecialchars($tel, ENT_QUOTES) . '" style="color:#1a56db;font-weight:700">' . $e($v) . '</a>' : $e($v);
    $rows .= '<tr><th style="text-align:right;vertical-align:top;padding:10px 12px;background:#f3f4f6;border-bottom:1px solid #e5e7eb;white-space:nowrap">' . $label . '</th>'
           . '<td style="padding:10px 12px;border-bottom:1px solid #e5e7eb">' . $val . '</td></tr>';
    $text .= $label . ": " . $v . "\n";
}
$html = '<!doctype html><html lang="he" dir="rtl"><body style="margin:0;padding:24px;background:#f9fafb;font-family:Arial,sans-serif;color:#111827">'
      . '<div style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">'
      . '<div style="padding:16px 20px;background:#151920;color:#fff;font-size:18px;font-weight:700">פנייה חדשה מהאתר yygweb.com</div>'
      . '<table dir="rtl" style="width:100%;border-collapse:collapse;font-size:15px;line-height:1.5">' . $rows . '</table>'
      . '<div style="padding:14px 20px;color:#6b7280;font-size:13px">נשלח מהטופס באתר, ' . date('d/m/Y H:i') . '</div></div></body></html>';
$subject = ($score > 0 ? '[לבדיקה: ' . implode(', ', $why) . '] ' : '') . 'פנייה חדשה מהאתר: ' . preg_replace('/[\r\n]+/', ' ', $f['name']);

$sent = send_mail($cfg, $subject, $html, $text);
if (!$sent) out(false, 'send', 502);
@file_put_contents(data_dir() . '/leads.log', time() . "\n", FILE_APPEND | LOCK_EX);
out(true);

/* ---------- שליחה ---------- */
function enc(string $s): string { return '=?UTF-8?B?' . base64_encode($s) . '?='; }

function build_message(array $cfg, string $subject, string $html, string $text, string &$headersOut): string {
    $b = 'yyg' . bin2hex(random_bytes(8));
    $headers = [
        'From: ' . enc($cfg['from_name']) . ' <' . $cfg['from'] . '>',
        'Reply-To: ' . $cfg['from'],
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $b . '"',
        'X-Mailer: YYG site',
    ];
    $headersOut = implode("\r\n", $headers);
    return "--$b\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($text))
         . "--$b\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html))
         . "--$b--\r\n";
}

function send_mail_api(array $cfg, string $subject, string $html, string $text): bool {
    $api = $cfg['mail_api'];
    $url = 'https://api.mail.hostinger.com/api/v1/mailboxes/' . rawurlencode($api['mailbox']) . '/send';
    $payload = json_encode(['to' => [$cfg['to']], 'subject' => $subject, 'html' => $html, 'text' => $text, 'displayName' => $cfg['from_name']], JSON_UNESCAPED_UNICODE);
    $headers = ['Authorization: Bearer ' . $api['token'], 'Content-Type: application/json', 'Accept: application/json'];
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $payload, 'timeout' => 20, 'ignore_errors' => true]]);
        @file_get_contents($url, false, $ctx);
        $code = isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m) ? (int) $m[1] : 0;
    }
    return $code >= 200 && $code < 300;
}

function send_mail(array $cfg, string $subject, string $html, string $text): bool {
    if (!empty($cfg['mail_api']['token']) && send_mail_api($cfg, $subject, $html, $text)) return true;
    $headers = '';
    $body = build_message($cfg, $subject, $html, $text, $headers);
    if (!empty($cfg['smtp']['user'])) {
        $ok = smtp_send($cfg, $subject, $headers, $body);
        if ($ok) return true;
    }
    if (@mail($cfg['to'], enc($subject), $body, $headers, '-f' . $cfg['from'])) return true;
    return @mail($cfg['to'], enc($subject), $body, $headers);
}

function smtp_send(array $cfg, string $subject, string $headers, string $body): bool {
    $s = $cfg['smtp'];
    $fp = @stream_socket_client('ssl://' . $s['host'] . ':' . (int) $s['port'], $errno, $errstr, 15);
    if (!$fp) return false;
    stream_set_timeout($fp, 15);
    $read = static function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 1024)) !== false) { $data .= $line; if (isset($line[3]) && $line[3] === ' ') break; }
        return $data;
    };
    $cmd = static function (string $c, array $okCodes) use ($fp, $read): bool {
        if ($c !== '') fwrite($fp, $c . "\r\n");
        $r = $read();
        return in_array((int) substr($r, 0, 3), $okCodes, true);
    };
    $to = $cfg['to'];
    $ok = $cmd('', [220])
        && $cmd('EHLO yygweb.com', [250])
        && $cmd('AUTH LOGIN', [334])
        && $cmd(base64_encode($s['user']), [334])
        && $cmd(base64_encode($s['pass']), [235])
        && $cmd('MAIL FROM:<' . $cfg['from'] . '>', [250])
        && $cmd('RCPT TO:<' . $to . '>', [250, 251])
        && $cmd('DATA', [354]);
    if ($ok) {
        $msg = 'To: <' . $to . ">\r\nSubject: " . enc($subject) . "\r\nDate: " . date('r') . "\r\nMessage-ID: <" . bin2hex(random_bytes(12)) . "@yygweb.com>\r\n"
             . $headers . "\r\n\r\n" . $body;
        $msg = preg_replace('/^\./m', '..', $msg); // נקודה בתחילת שורה
        $ok = $cmd($msg . "\r\n.", [250]);
    }
    @fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
}
