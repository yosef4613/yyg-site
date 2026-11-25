<?php
/*
 * YYG: משתמשים רשומים ורשימת התפוצה. משותף לאזור האישי, לרשימת התפוצה ולדף הניהול.
 * users.json: משתמשים (הסיסמה נשמרת רק כגיבוב password_hash).
 * subscribers.json: רשימת התפוצה. מצטרפים רק באישור כפול (קישור במייל), ובכל מייל יש קישור הסרה.
 */
declare(strict_types=1);
require_once dirname(__DIR__) . '/admin/_lib.php';
if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) { http_response_code(404); exit; }

const U_COOKIE = 'yyg_user';
const CONSENT_HE = 'אני מסכים/ה לקבל מ־YYG במייל מדריכים, טיפים ועדכונים, כולל הצעות לשירותים. אפשר להסיר את עצמי בכל רגע.';
const CONSENT_EN = 'I agree to receive guides, tips and updates from YYG by email, including service offers. I can unsubscribe at any time.';

function email_norm(string $e): string { return mb_strtolower(trim($e), 'UTF-8'); }
function phone_digits(string $p): string { $d = preg_replace('/\D/', '', $p); return preg_replace('/^972/', '0', $d); }
function T(string $he, string $en): string { return !empty($GLOBALS['EN']) ? $en : $he; }
function acc_url(string $path = '', array $q = []): string {
    if (!empty($GLOBALS['EN'])) $q = ['lang' => 'en'] + $q;
    return 'https://yygweb.com/account/' . $path . ($q ? '?' . http_build_query($q) : '');
}

/* ---------- משתמשים ---------- */
function users_load(): array { return json_load('users.json'); }
function user_by_email(array $users, string $email): ?string {
    $email = email_norm($email);
    foreach ($users as $uid => $u) if (($u['email'] ?? '') === $email) return (string) $uid;
    return null;
}
/* עוגיית כניסה חתומה ל־60 יום. sv הוא "גרסת כניסה": שינוי סיסמה מנתק את כל המכשירים */
function user_login_cookie(string $uid, int $sv): void {
    $exp = time() + 60 * 86400;
    $v = $uid . '.' . $exp . '.' . $sv;
    setcookie(U_COOKIE, $v . '.' . sign('u' . $v), ['expires' => $exp, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
}
function user_logout_cookie(): void { setcookie(U_COOKIE, '', ['expires' => 1, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']); }
function user_current(): ?array {
    $c = (string) ($_COOKIE[U_COOKIE] ?? '');
    if (!preg_match('/^([a-f0-9]{16})\.(\d{10})\.(\d{1,6})\.([a-f0-9]{64})$/', $c, $m)) return null;
    if ((int) $m[2] < time() || !hash_equals(sign('u' . $m[1] . '.' . $m[2] . '.' . $m[3]), $m[4])) return null;
    $users = users_load();
    $u = $users[$m[1]] ?? null;
    if (!$u || (int) ($u['sv'] ?? 0) !== (int) $m[3]) return null;
    return ['uid' => $m[1]] + $u;
}
function user_csrf(?array $u): string { return sign('ucsrf' . ($u['uid'] ?? 'anon') . ($u['sv'] ?? 0)); }
/* קישור חד־פעמי במייל (אימות או איפוס סיסמה): נשמר רק הגיבוב שלו */
function one_time_token(): array { $t = bin2hex(random_bytes(16)); return [$t, hash('sha256', $t)]; }

/* ---------- רשימת התפוצה ---------- */
function subs_load(): array { return json_load('subscribers.json'); }
function sub_links(array $s): array {
    $q = ['e' => $s['email'], 't' => $s['token']] + (($s['lang'] ?? 'he') === 'en' ? ['lang' => 'en'] : []);
    return [
        'confirm' => 'https://yygweb.com/account/list.php?' . http_build_query(['do' => 'confirm'] + $q),
        'unsub'   => 'https://yygweb.com/account/list.php?' . http_build_query(['do' => 'unsub'] + $q),
    ];
}
/* מצרף או מעדכן מנוי. $confirmed=true רק כשהמייל כבר אומת (למשל משתמש מחובר שאימת את המייל) */
function sub_upsert(string $email, string $name, string $source, string $lang, bool $confirmed): array {
    $email = email_norm($email);
    return data_update('subscribers.json', static function (array &$subs) use ($email, $name, $source, $lang, $confirmed): array {
        $s = $subs[$email] ?? ['email' => $email, 'token' => bin2hex(random_bytes(12)), 'created' => time()];
        if ($name !== '') $s['name'] = $name;
        $s['source'] = $s['source'] ?? $source;
        $s['lang'] = $lang;
        $s['consent'] = $lang === 'en' ? CONSENT_EN : CONSENT_HE;
        $s['consent_t'] = time();
        if ($confirmed) { $s['status'] = 'active'; $s['confirmed'] = $s['confirmed'] ?? time(); unset($s['unsub']); }
        elseif (($s['status'] ?? '') !== 'active') $s['status'] = 'pending';
        $subs[$email] = $s;
        return $s;
    });
}
function sub_set_status(string $email, string $status): void {
    $email = email_norm($email);
    data_update('subscribers.json', static function (array &$subs) use ($email, $status): void {
        if (!isset($subs[$email])) return;
        $subs[$email]['status'] = $status;
        if ($status === 'unsubscribed') $subs[$email]['unsub'] = time();
        if ($status === 'active') { $subs[$email]['confirmed'] = $subs[$email]['confirmed'] ?? time(); unset($subs[$email]['unsub']); }
    });
}
function sub_send_confirm(array $s): bool {
    $en = ($s['lang'] ?? 'he') === 'en';
    $l = sub_links($s);
    $body = $en
        ? '<p>Thanks for joining. To start receiving guides and tips, please confirm your email address:</p>' . mail_button($l['confirm'], 'Confirm my subscription') . '<p style="color:#5A6472;font-size:14px">If you didn\'t ask to join, just ignore this email and you won\'t receive anything.</p>'
        : '<p>תודה שהצטרפתם. כדי להתחיל לקבל מדריכים וטיפים, צריך רק לאשר את כתובת המייל:</p>' . mail_button($l['confirm'], 'אישור ההצטרפות') . '<p style="color:#5A6472;font-size:14px">אם לא ביקשתם להצטרף, פשוט התעלמו מהמייל הזה ולא יישלח אליכם כלום.</p>';
    return mail_send($s['email'], $en ? 'Please confirm your subscription to YYG' : 'אישור הצטרפות לרשימת התפוצה של YYG', mail_wrap($en ? 'One more click' : 'עוד לחיצה אחת', $body, '', $en),
        ($en ? "Confirm your subscription: " : "לאישור ההצטרפות: ") . $l['confirm']);
}

/* ---------- עמוד באזור האישי, בעיצוב של האתר ---------- */
function acc_page(string $title, string $body): void {
    $en = !empty($GLOBALS['EN']);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
    header('X-Frame-Options: DENY');
    echo '<!doctype html><html lang="' . ($en ? 'en' : 'he') . '" dir="' . ($en ? 'ltr' : 'rtl') . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">'
        . '<title>' . h($title) . ' | YYG</title><link rel="icon" href="/assets/img/icon-64.png" type="image/png"><link rel="stylesheet" href="/assets/site.css?v=39"><style>'
        . '.acc{padding:clamp(32px,6vw,72px) 0 clamp(56px,8vw,96px)}.acc .wrap{max-width:720px}.acc .logo{height:52px;width:auto}.acc-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:28px}'
        . '.acc h1{font-size:var(--step-2)}.acc h2{font-size:var(--step-1);margin-top:30px}.acc p{margin-top:10px;color:var(--muted)}.acc .lead{color:#C9D1DD}'
        . '.box{margin-top:18px;padding:clamp(18px,3vw,26px);background:var(--panel);border:1px solid var(--line-2);border-radius:16px}.box form{display:grid;gap:14px}'
        . '.tabs{display:flex;gap:8px;margin-top:20px}.tabs a{flex:1;text-align:center;padding:10px;border-radius:12px;border:1px solid var(--line-2);text-decoration:none;color:var(--muted);font-weight:700}.tabs a.on{background:var(--text);color:var(--base);border-color:var(--text)}'
        . '.chk{display:flex;gap:10px;align-items:flex-start;font-size:.95rem;color:#C9D1DD}.chk input{width:20px;height:20px;margin-top:3px;flex:none;accent-color:#E3B341}'
        . '.msg{padding:12px 14px;border-radius:12px;background:#1f3a2a;border:1px solid #2f6b45;color:#fff}.msg.bad{background:#3a1f1f;border-color:#6b3434}'
        . '.rows{display:grid;gap:8px;margin-top:12px}.rows a,.rows div{display:flex;justify-content:space-between;gap:10px;padding:12px 14px;border:1px solid var(--line);border-radius:12px;background:var(--base-2);text-decoration:none;color:var(--text)}'
        . '.hp{position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;clip:rect(0 0 0 0);pointer-events:none}.acc .btn{width:100%}.inline{display:flex;gap:10px;flex-wrap:wrap}.inline .btn{width:auto}'
        . '</style></head><body><main class="acc"><div class="wrap"><div class="acc-top"><a href="' . ($en ? '/en/' : '/') . '"><img class="logo" src="/assets/img/logo-160.webp" width="160" height="118" alt="' . ($en ? 'YYG, back to the home page' : 'YYG, חזרה לעמוד הבית') . '"></a>'
        . '<a class="text-link" href="/account/' . ($en ? '' : '?lang=en') . '" hreflang="' . ($en ? 'he' : 'en') . '" lang="' . ($en ? 'he' : 'en') . '">' . ($en ? 'עברית' : 'English') . '</a></div>'
        . $body . '</div></main></body></html>';
    exit;
}
