<?php
/*
 * YYG: פונקציות משותפות לדף הניהול, לבונה הצעות המחיר ולדף ההצעה שהלקוח רואה.
 * הנתונים נשמרים מחוץ לתיקיית האתר (yyg-data), כך שעדכון של האתר לא מוחק אותם.
 */
declare(strict_types=1);
if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) { http_response_code(404); exit; }

function data_dir(): string {
    $d = dirname(__DIR__, 2) . '/yyg-data';
    if (!is_dir($d)) @mkdir($d, 0700, true);
    return is_dir($d) && is_writable($d) ? $d : dirname(__DIR__) . '/_private';
}
function private_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $safe = data_dir() . '/mail-config.php';
    $fresh = dirname(__DIR__) . '/_private/mail-config.php';
    if (is_file($fresh) && (!is_file($safe) || filemtime($fresh) > filemtime($safe))) @copy($fresh, $safe);
    $f = is_file($safe) ? $safe : $fresh;
    return $cfg = is_file($f) ? (array) require $f : [];
}
function h($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function nis($n): string { return number_format((float) $n, 0, '.', ',') . ' ₪'; }
function tz(): DateTimeZone { return new DateTimeZone('Asia/Jerusalem'); }
function fmt_time(int $t, string $f = 'd/m/Y H:i'): string { return (new DateTime('@' . $t))->setTimezone(tz())->format($f); }

/* ---------- כניסה: עוגייה חתומה ל־30 יום ---------- */
const ADM_COOKIE = 'yyg_admin';
function adm_key(): string { return (string) (private_config()['stats_key'] ?? ''); }
function adm_sign(string $v): string { return hash_hmac('sha256', $v, adm_key()); }
function adm_authed(): bool {
    if (adm_key() === '') return false;
    $c = (string) ($_COOKIE[ADM_COOKIE] ?? '');
    if (!preg_match('/^(\d{10})\.([a-f0-9]{64})$/', $c, $m)) return false;
    return (int) $m[1] > time() && hash_equals(adm_sign($m[1]), $m[2]);
}
function adm_login(): void {
    $exp = (string) (time() + 30 * 86400);
    setcookie(ADM_COOKIE, $exp . '.' . adm_sign($exp), ['expires' => (int) $exp, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
}
function adm_logout(): void {
    foreach (['/', '/admin/'] as $p) setcookie(ADM_COOKIE, '', ['expires' => 1, 'path' => $p, 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
}
function adm_csrf(): string { return adm_sign('csrf' . substr((string) ($_COOKIE[ADM_COOKIE] ?? ''), 0, 10)); }
function adm_headers(): void {
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
}
/* מי שלא מחובר מועבר לדף הכניסה */
function adm_require(): void {
    adm_headers();
    if (!adm_authed()) { header('Location: /admin/', true, 303); exit; }
}

/* ---------- קבצי נתונים ---------- */
function json_load(string $name): array { return json_decode((string) @file_get_contents(data_dir() . '/' . $name), true) ?: []; }
function json_save(string $name, array $data): bool {
    $f = data_dir() . '/' . $name;
    $tmp = $f . '.tmp-' . bin2hex(random_bytes(4));
    if (@file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) === false) return false;
    return @rename($tmp, $f);
}
function leads_load(): array {
    $leads = [];
    foreach ((@file(data_dir() . '/leads.jsonl', FILE_IGNORE_NEW_LINES) ?: []) as $l) { $r = json_decode($l, true); if (is_array($r) && !empty($r['id'])) $leads[(string) $r['id']] = $r; }
    return $leads;
}

/* ---------- הצעות מחיר ---------- */
const Q_STATUSES = ['draft' => 'טיוטה', 'sent' => 'נשלחה', 'approved' => 'אושרה', 'declined' => 'לא רלוונטית'];
function quotes_load(): array { return json_load('quotes.json'); }
function quotes_save(array $q): bool { return json_save('quotes.json', $q); }
function quote_find_by_token(array $quotes, string $token): ?string {
    if (!preg_match('/^[a-f0-9]{24}$/', $token)) return null;
    foreach ($quotes as $num => $q) if (hash_equals((string) ($q['token'] ?? ''), $token)) return (string) $num;
    return null;
}
function quote_next_number(array $quotes): string {
    $y = (new DateTime('now', tz()))->format('Y');
    $n = 0;
    foreach (array_keys($quotes) as $k) if (preg_match('/^YYG-' . $y . '-(\d+)$/', (string) $k, $m)) $n = max($n, (int) $m[1]);
    return sprintf('YYG-%s-%03d', $y, $n + 1);
}
function quote_totals(array $q): array {
    $sub = 0.0;
    foreach ((array) ($q['items'] ?? []) as $it) $sub += (float) ($it['price'] ?? 0) * max(1, (int) ($it['qty'] ?? 1));
    $disc = min($sub, max(0.0, (float) ($q['discount']['amount'] ?? 0)));
    return ['sub' => $sub, 'discount' => $disc, 'total' => $sub - $disc];
}
function quote_url(array $q): string { return 'https://yygweb.com/q/?t=' . ($q['token'] ?? ''); }

/* ---------- שליחת מייל דרך תיבת המייל של האתר (Hostinger Mail API) ---------- */
function mail_send(string $to, string $subject, string $html, string $text, string $from = 'YYG · יהודה גורדון'): bool {
    $cfg = private_config();
    /* בסביבת בדיקה בלבד (מפתח שלא קיים בשרת): המייל נכתב לקובץ במקום להישלח */
    if (!empty($cfg['mail_outbox']) && is_dir((string) $cfg['mail_outbox'])) {
        return (bool) file_put_contents($cfg['mail_outbox'] . '/' . microtime(true) . '-' . bin2hex(random_bytes(3)) . '.txt', "To: $to\nSubject: $subject\n\n$text\n");
    }
    $api = $cfg['mail_api'] ?? null;
    if (!$api || empty($api['token']) || empty($api['mailbox']) || !filter_var($to, FILTER_VALIDATE_EMAIL) || !function_exists('curl_init')) return false;
    $url = 'https://api.mail.hostinger.com/api/v1/mailboxes/' . rawurlencode((string) $api['mailbox']) . '/send';
    $payload = json_encode(['to' => [$to], 'subject' => $subject, 'html' => $html, 'text' => $text, 'displayName' => $from], JSON_UNESCAPED_UNICODE);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $api['token'], 'Content-Type: application/json', 'Accept: application/json']]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code >= 200 && $code < 300;
}
/* מייל אליי */
function notify_me(string $subject, string $html, string $text): bool {
    return mail_send((string) (private_config()['to'] ?? ''), $subject, $html, $text, 'האתר של YYG');
}
/* מסגרת אחידה למיילים שיוצאים ללקוחות ולמנויים */
function mail_wrap(string $title, string $body, string $footer = '', bool $en = false): string {
    return '<!doctype html><html lang="' . ($en ? 'en' : 'he') . '" dir="' . ($en ? 'ltr' : 'rtl') . '"><body style="margin:0;padding:24px 12px;background:#F4F5F7;font-family:Arial,sans-serif;color:#151920">'
        . '<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #DCE0E6">'
        . '<div style="padding:16px 22px;background:#151920;color:#fff;font-size:17px;font-weight:700">YYG · ' . ($en ? 'Web design &amp; development' : 'עיצוב ובניית אתרים') . '</div>'
        . '<div style="padding:22px;font-size:16px;line-height:1.65"><h1 style="font-size:21px;margin:0 0 12px">' . h($title) . '</h1>' . $body . '</div>'
        . '<div style="padding:14px 22px;background:#F4F5F7;color:#5A6472;font-size:13px;line-height:1.6">' . ($footer !== '' ? $footer . '<br>' : '')
        . ($en ? 'YYG, Yehuda Gordon · 054-854-0700 · yehuda@yygweb.com · yygweb.com' : 'YYG, יהודה גורדון · 054-854-0700 · yehuda@yygweb.com · yygweb.com') . '</div></div></body></html>';
}
function mail_button(string $url, string $label): string {
    return '<p style="margin:20px 0"><a href="' . h($url) . '" style="display:inline-block;background:#E3B341;color:#1A1404;font-weight:700;text-decoration:none;padding:12px 22px;border-radius:10px">' . h($label) . '</a></p>';
}

/* ---------- סוד לחתימות, הגבלת קצב, ועדכון בטוח של קבצי נתונים ---------- */
function site_secret(): string {
    $f = data_dir() . '/secret-acc.txt';
    $k = is_file($f) ? trim((string) @file_get_contents($f)) : '';
    if (strlen($k) < 32) { $k = bin2hex(random_bytes(32)); @file_put_contents($f, $k, LOCK_EX); @chmod($f, 0600); }
    return $k;
}
function sign(string $v): string { return hash_hmac('sha256', $v, site_secret()); }
/* אסימון לטופס פתוח (הרשמה, כניסה): חותמת זמן חתומה. מגן מרובוטים ששולחים בלי לטעון את הדף */
function form_token(): string { $t = (string) time(); return $t . '.' . sign('form' . $t); }
function form_token_age(string $tok): int {
    if (!preg_match('/^(\d{9,11})\.([0-9a-f]{64})$/', $tok, $m) || !hash_equals(sign('form' . $m[1]), $m[2])) return -1;
    $age = time() - (int) $m[1];
    return ($age < 0 || $age > 12 * 3600) ? -1 : $age;
}
/* מחזיר false אם עברו את המכסה בחלון הזמן */
function rate_ok(string $bucket, int $max, int $window): bool {
    $d = data_dir() . '/rl';
    if (!is_dir($d)) @mkdir($d, 0700, true);
    $f = $d . '/' . substr(hash('sha256', $bucket), 0, 24);
    $now = time();
    $hits = array_filter(array_map('intval', @file($f, FILE_IGNORE_NEW_LINES) ?: []), static fn($t) => $t > $now - $window);
    if (count($hits) >= $max) return false;
    $hits[] = $now;
    @file_put_contents($f, implode("\n", $hits), LOCK_EX);
    return true;
}
function client_ip(): string { return trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '')))[0]); }
/* קריאה, שינוי ושמירה של קובץ נתונים תחת נעילה, כדי ששתי בקשות במקביל לא ימחקו זו לזו */
function data_update(string $name, callable $fn) {
    $lock = fopen(data_dir() . '/' . $name . '.lock', 'c');
    if ($lock) flock($lock, LOCK_EX);
    $data = json_load($name);
    $result = $fn($data);
    json_save($name, $data);
    if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
    return $result;
}

/* ---------- עיצוב משותף לדפי הניהול ---------- */
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
.pages .bar{grid-template-columns:minmax(0,1fr) 120px 40px}.pages .bar span:first-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 14px}.chips a{display:inline-flex;gap:6px;align-items:center;padding:6px 12px;border:1px solid var(--line);border-radius:99px;text-decoration:none;color:var(--text);font-size:.92rem}
.chips a.on{background:var(--text);color:var(--bg);border-color:var(--text)}
.lead{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:14px 16px;margin-bottom:10px}
.lead.st-new{border-color:#5b6578;box-shadow:inset -3px 0 0 var(--cyan)}
.lead-h{display:flex;flex-wrap:wrap;align-items:baseline;gap:4px 14px}.lead-h b{font-size:1.1rem}
.tag{display:inline-block;padding:1px 9px;border-radius:99px;font-size:.8rem;font-weight:700;background:var(--bg2);border:1px solid var(--line)}
.tag.warn{color:var(--warn);border-color:#6b5a26}.tag.bad{color:var(--bad);border-color:#6b3434}.tag.ok{color:var(--ok);border-color:#2f6b45}
.acts{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:40px;padding:6px 14px;border-radius:10px;border:1px solid var(--line);background:var(--bg2);color:var(--text);text-decoration:none;font:600 .95rem system-ui,Arial,sans-serif;cursor:pointer}
.btn.gold{background:var(--gold);border-color:var(--gold);color:#1A1404}
.kv{display:grid;grid-template-columns:max-content 1fr;gap:4px 14px;margin:10px 0 0;font-size:.95rem}.kv dt{color:var(--muted)}.kv dd{margin:0;white-space:pre-wrap;overflow-wrap:anywhere}
details{margin-top:8px}summary{cursor:pointer;color:var(--muted);font-weight:600}
form.st{display:grid;grid-template-columns:180px 1fr auto;gap:8px;margin-top:10px;align-items:start}
select,textarea,input[type=password],input[type=text],input[type=tel],input[type=email],input[type=number],input[type=date]{width:100%;min-height:40px;padding:8px 10px;border-radius:10px;border:1px solid var(--line);background:var(--bg);color:var(--text);font:400 .95rem system-ui,Arial,sans-serif}
textarea{min-height:40px;resize:vertical}
.links{display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(230px,1fr))}.links a{display:block;padding:12px 14px;border:1px solid var(--line);border-radius:12px;background:var(--panel);text-decoration:none}.links a small{display:block}
.spam{font-size:.88rem}.spam div{padding:8px 0;border-top:1px solid var(--line);overflow-wrap:anywhere}
.login{max-width:380px;margin:12vh auto;padding:24px;background:var(--panel);border:1px solid var(--line);border-radius:16px}.login form{display:grid;gap:10px;margin-top:14px}
.flash{padding:10px 14px;border-radius:10px;background:#1f3a2a;border:1px solid #2f6b45;margin:12px 0}
table.list{width:100%;border-collapse:collapse;font-size:.95rem}table.list th,table.list td{padding:10px 8px;border-bottom:1px solid var(--line);text-align:right;vertical-align:middle}table.list th{color:var(--muted);font-weight:600;font-size:.85rem}
.tscroll{overflow-x:auto;background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:4px 10px}
@media (max-width:640px){form.st{grid-template-columns:1fr}.pages .bar{grid-template-columns:minmax(0,1fr) 70px 34px}}';

function adm_page(string $title, string $body, string $extraCss = ''): void {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="he" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>' . h($title) . '</title><style>' . ADMIN_CSS . $extraCss . '</style></head><body>' . $body . '</body></html>';
    exit;
}
function adm_nav(int $newLeads = 0): string {
    return '<header class="top"><div class="wrap"><b>ניהול YYG</b><a href="/admin/#leads">פניות' . ($newLeads ? ($newLeads === 1 ? ' (חדשה אחת)' : " ($newLeads חדשות)") : '') . '</a><a href="/admin/#quotes">הצעות מחיר</a><a href="/admin/#users">משתמשים ורשימה</a><a href="/admin/#stats">כניסות</a><a href="/admin/#spam">ספאם</a><a href="/admin/#links">קישורים</a><a href="/admin/?logout=1">יציאה</a></div></header>';
}
