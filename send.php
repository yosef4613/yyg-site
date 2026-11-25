<?php
/*
 * YYG: קבלת פנייה מהטופס באתר ושליחתה במייל, ישירות מהשרת בהוסטינגר.
 * - אם יש קובץ הגדרות (_private/mail-config.php) שולחים דרך תיבת מייל של הדומיין (SMTP), שזו הדרך שמגיעה הכי טוב לתיבה.
 * - אחרת שולחים בפונקציית המייל של השרת.
 * מחזיר JSON: {"success":"true"} או {"success":"false","message":"..."}
 */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out(bool $ok, string $msg = '', int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok ? 'true' : 'false', 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') out(false, 'method', 405);

// רק מהאתר שלנו
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$host = parse_url($origin, PHP_URL_HOST) ?: '';
if ($host !== '' && !preg_match('/(^|\.)yygweb\.com$/i', $host) && $host !== 'localhost') out(false, 'origin', 403);

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
    'likes'    => $clean($in['likes'] ?? '', 1500),
    'notes'    => $clean($in['notes'] ?? '', 3000),
    'ready'    => $clean($in['ready'] ?? '', 1500),
    'plan'     => $clean($in['plan'] ?? '', 4000),
];
if ($f['name'] === '' || strlen(preg_replace('/\D/', '', $f['phone'])) < 9) out(false, 'missing', 422);

// הגבלה פשוטה: עד 5 פניות ב־10 דקות מאותה כתובת
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0');
$ip = trim(explode(',', $ip)[0]);
$rl = sys_get_temp_dir() . '/yyg-rl-' . hash('sha256', $ip);
$now = time();
$hits = array_filter(array_map('intval', @file($rl, FILE_IGNORE_NEW_LINES) ?: []), static fn($t) => $t > $now - 600);
if (count($hits) >= 5) out(false, 'rate', 429);
$hits[] = $now;
@file_put_contents($rl, implode("\n", $hits));

// הגדרות שליחה
$cfg = [
    'to'        => 'yyrkg22@gmail.com',
    'from'      => 'site@yygweb.com',
    'from_name' => 'האתר של YYG',
    'smtp'      => null, // ['host' => 'smtp.hostinger.com', 'port' => 465, 'user' => '...', 'pass' => '...']
];
$cfgFile = __DIR__ . '/_private/mail-config.php';
if (is_file($cfgFile)) $cfg = array_merge($cfg, (array) require $cfgFile);

// תוכן המייל
$labels = [
    'name' => 'שם', 'phone' => 'טלפון', 'business' => 'שם העסק', 'when' => 'מתי נוח שתתקשר',
    'likes' => 'אתרים שהוא אוהב', 'notes' => 'הערות', 'ready' => 'רשימת ההכנה', 'plan' => 'המסלול והתכנית',
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
$subject = 'פנייה חדשה מהאתר: ' . preg_replace('/[\r\n]+/', ' ', $f['name']);

$sent = send_mail($cfg, $subject, $html, $text);
if (!$sent) out(false, 'send', 502);
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

function send_mail(array $cfg, string $subject, string $html, string $text): bool {
    $headers = '';
    $body = build_message($cfg, $subject, $html, $text, $headers);
    if (!empty($cfg['smtp']['user'])) {
        $ok = smtp_send($cfg, $subject, $headers, $body);
        if ($ok) return true;
    }
    return @mail($cfg['to'], enc($subject), $body, $headers, '-f' . $cfg['from']);
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
