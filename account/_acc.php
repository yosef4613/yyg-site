<?php
/*
 * YYG Web Design: משתמשים רשומים ורשימת התפוצה. משותף לאזור האישי, לרשימת התפוצה ולדף הניהול.
 * users.json: משתמשים (הסיסמה נשמרת רק כגיבוב password_hash).
 * subscribers.json: רשימת התפוצה. מצטרפים רק באישור כפול (קישור במייל), ובכל מייל יש קישור הסרה.
 */
declare(strict_types=1);
require_once dirname(__DIR__) . '/admin/_lib.php';
if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) { http_response_code(404); exit; }

const U_COOKIE = 'yyg_user';
const CONSENT_HE = 'אני מסכים/ה לקבל מ־YYG Web Design במייל מדריכים, טיפים ועדכונים, כולל הצעות לשירותים. אפשר להסיר את עצמי בכל רגע.';
const CONSENT_EN = 'I agree to receive guides, tips and updates from YYG Web Design by email, including service offers. I can unsubscribe at any time.';

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
    /* רמז לדפי האתר (בלי שום הרשאה): בכותרת כתוב "האזור האישי" במקום "כניסה" */
    setcookie('yyg_in', '1', ['expires' => $exp, 'path' => '/', 'secure' => true, 'samesite' => 'Lax']);
    /* בעל האתר שנכנס לחשבון שלו נכנס גם לניהול */
    $u = users_load()[$uid] ?? null;
    if ($u && is_owner_email((string) ($u['email'] ?? ''))) adm_login();
}
function user_logout_cookie(): void {
    setcookie(U_COOKIE, '', ['expires' => 1, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
    setcookie('yyg_in', '', ['expires' => 1, 'path' => '/', 'secure' => true, 'samesite' => 'Lax']);
}

/* ---------- בעל האתר: המייל של העסק, וחשבונות שקיבלו גישה לניהול מתוך האזור האישי ---------- */
function owner_linked(): array { return json_load('admin-accounts.json'); }
function is_owner_email(string $email): bool {
    $email = email_norm($email);
    if ($email === '') return false;
    return $email === email_norm((string) (private_config()['to'] ?? '')) || isset(owner_linked()[$email]);
}

/* ---------- כניסה עם Google ----------
 * כפתור "המשך עם Google" (Google Identity Services, חלון קופץ לבחירת חשבון). Google מחזיר אסימון חתום,
 * ואנחנו בודקים אותו מול Google: שהוא נועד לאתר הזה (aud), שהמייל מאומת, שהוא בתוקף, ושה־nonce שלו הוא זה שנתנו לדפדפן הזה.
 * ה־Client ID נשמר במסך הניהול (הגדרות), או ב־google_client_id בהגדרות הפרטיות. בלעדיו הכפתור לא מוצג. */
function google_client_id(): string {
    $id = (string) (json_load('settings.json')['google_client_id'] ?? (private_config()['google_client_id'] ?? ''));
    return preg_match('/^[0-9]{6,}-[a-z0-9]{10,}\.apps\.googleusercontent\.com$/', $id) ? $id : '';
}
function google_nonce(): string {
    $n = (string) ($_COOKIE['yyg_gn'] ?? '');
    if (!preg_match('/^[a-f0-9]{32}$/', $n)) {
        $n = bin2hex(random_bytes(16));
        setcookie('yyg_gn', $n, ['expires' => time() + 1800, 'path' => '/account/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
    }
    return $n;
}
/* to: account (האזור האישי) או admin (דף הכניסה לניהול) */
function google_button(string $to, bool $en): string {
    $cid = google_client_id();
    if ($cid === '') return '';
    return '<div class="g-wrap"><div id="g_id_onload" data-client_id="' . h($cid) . '" data-context="signin" data-ux_mode="popup" data-callback="yygGoogle" data-nonce="' . h(google_nonce()) . '" data-auto_prompt="false" data-itp_support="true"></div>'
        . '<div class="g_id_signin" data-type="standard" data-shape="pill" data-theme="filled_black" data-text="continue_with" data-size="large" data-logo_alignment="left" data-width="300" data-locale="' . ($en ? 'en' : 'he') . '"></div>'
        . '<form id="g-form" method="post" action="/account/"><input type="hidden" name="do" value="google"><input type="hidden" name="to" value="' . ($to === 'admin' ? 'admin' : 'account') . '"><input type="hidden" name="credential" value="">' . ($en ? '<input type="hidden" name="lang" value="en">' : '') . '</form>'
        . '<script>function yygGoogle(r){var f=document.getElementById("g-form");f.credential.value=r.credential;f.submit();}</script>'
        . '<script src="https://accounts.google.com/gsi/client" async></script></div>';
}
/* בדיקת האסימון מול Google. מחזיר מייל ושם, או null */
function google_verify(string $jwt): ?array {
    $cid = google_client_id();
    $nonce = (string) ($_COOKIE['yyg_gn'] ?? '');
    if ($cid === '' || $nonce === '' || strlen($jwt) > 4096 || !preg_match('/^[\w-]+\.[\w-]+\.[\w-]+$/', $jwt)) return null;
    $url = (string) (private_config()['google_tokeninfo'] ?? 'https://oauth2.googleapis.com/tokeninfo') . '?id_token=' . rawurlencode($jwt);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5]);
    $res = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $t = $code === 200 && is_string($res) ? json_decode($res, true) : null;
    if (!is_array($t)) return null;
    $verified = ($t['email_verified'] ?? '') === 'true' || ($t['email_verified'] ?? '') === true;
    if (($t['aud'] ?? '') !== $cid || !in_array($t['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)
        || (int) ($t['exp'] ?? 0) < time() || !$verified || !hash_equals($nonce, (string) ($t['nonce'] ?? ''))) return null;
    $email = email_norm((string) ($t['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return null;
    setcookie('yyg_gn', '', ['expires' => 1, 'path' => '/account/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
    return ['email' => $email, 'name' => mb_substr(trim((string) ($t['name'] ?? '')), 0, 80, 'UTF-8'), 'sub' => substr((string) ($t['sub'] ?? ''), 0, 64)];
}
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
    return mail_send($s['email'], $en ? 'Please confirm your subscription to YYG Web Design' : 'אישור הצטרפות לרשימת התפוצה של YYG Web Design', mail_wrap($en ? 'One more click' : 'עוד לחיצה אחת', $body, '', $en),
        ($en ? "Confirm your subscription: " : "לאישור ההצטרפות: ") . $l['confirm'], mail_from($en));
}

/* ---------- עמוד באזור האישי, בעיצוב של האתר ---------- */
function acc_page(string $title, string $body): void {
    $en = !empty($GLOBALS['EN']);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
    header('X-Frame-Options: DENY');
    echo '<!doctype html><html lang="' . ($en ? 'en' : 'he') . '" dir="' . ($en ? 'ltr' : 'rtl') . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">'
        . '<title>' . h($title) . ' | YYG Web Design</title><link rel="icon" href="/assets/img/yyg-icon-96.png" type="image/png"><link rel="stylesheet" href="/assets/site.css?v=49"><style>'
        . '.acc{padding:clamp(32px,6vw,72px) 0 clamp(56px,8vw,96px)}.acc .wrap{max-width:720px}.acc .logo{height:52px;width:auto}.acc-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:28px}'
        . '.acc h1{font-size:var(--step-2)}.acc h2{font-size:var(--step-1);margin-top:30px}.acc p{margin-top:10px;color:var(--muted)}.acc .lead{color:#C9D1DD}'
        . '.box{margin-top:18px;padding:clamp(18px,3vw,26px);background:var(--panel);border:1px solid var(--line-2);border-radius:16px}.box form{display:grid;gap:14px}'
        . '.tabs{display:flex;gap:8px;margin-top:20px}.tabs a{flex:1;text-align:center;padding:10px;border-radius:12px;border:1px solid var(--line-2);text-decoration:none;color:var(--muted);font-weight:700}.tabs a.on{background:var(--text);color:var(--base);border-color:var(--text)}'
        . '.chk{display:flex;gap:10px;align-items:flex-start;font-size:.95rem;color:#C9D1DD}.chk input{width:20px;height:20px;margin-top:3px;flex:none;accent-color:#E3B341}'
        . '.msg{padding:12px 14px;border-radius:12px;background:#1f3a2a;border:1px solid #2f6b45;color:#fff}.msg.bad{background:#3a1f1f;border-color:#6b3434}'
        . '.rows{display:grid;gap:8px;margin-top:12px}.rows a,.rows div{display:flex;justify-content:space-between;gap:10px;padding:12px 14px;border:1px solid var(--line);border-radius:12px;background:var(--base-2);text-decoration:none;color:var(--text)}'
        . '.hp{position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;clip:rect(0 0 0 0);pointer-events:none}.acc .btn{width:100%}.inline{display:flex;gap:10px 18px;flex-wrap:wrap;align-items:center}.inline .btn{width:auto}'
        . '.g-wrap{display:grid;justify-items:center;gap:6px;min-height:44px}.g_id_signin{color-scheme:light}.or{display:flex;align-items:center;gap:12px;color:var(--muted);font-size:.9rem;margin:16px 0 0}.or::before,.or::after{content:"";flex:1;border-top:1px solid var(--line-2)}.who{margin-top:8px;color:var(--muted);font-size:.95rem}.who button{background:none;border:0;padding:0;font:inherit;font-weight:700;color:#fff;cursor:pointer;text-decoration:underline;text-decoration-color:#E3B341;text-underline-offset:4px}'
        . '</style></head><body><main class="acc"><div class="wrap"><div class="acc-top"><a href="' . ($en ? '/en/' : '/') . '"><img class="logo" src="/assets/img/yyg-logo-160.webp" width="160" height="122" alt="' . ($en ? 'YYG Web Design, back to the home page' : 'YYG Web Design, חזרה לעמוד הבית') . '"></a>'
        . '<span class="inline">' . (adm_authed() ? '<a class="text-link" href="/admin/" lang="he">ניהול</a>' : '')
        . '<a class="text-link" href="/account/' . ($en ? '' : '?lang=en') . '" hreflang="' . ($en ? 'he' : 'en') . '" lang="' . ($en ? 'he' : 'en') . '">' . ($en ? 'עברית' : 'English') . '</a></span></div>'
        . $body . '</div></main></body></html>';
    exit;
}
