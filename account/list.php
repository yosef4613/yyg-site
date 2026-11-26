<?php
/*
 * YYG Web Design: רשימת התפוצה. https://yygweb.com/account/list.php
 * POST (JSON או טופס רגיל): הצטרפות. נשלח מייל עם קישור אישור, ורק אחריו המנוי פעיל.
 * ?do=confirm&e=&t=  אישור ההצטרפות
 * ?do=unsub&e=&t=    הסרה (עם כפתור, כדי שסורקי קישורים במייל לא יסירו בטעות)
 */
declare(strict_types=1);
require __DIR__ . '/_acc.php';
$isJson = stripos((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== false;
$in = $isJson ? (json_decode((string) file_get_contents('php://input'), true) ?: []) : $_POST + $_GET;
$EN = ($in['lang'] ?? '') === 'en';
$do = (string) ($in['do'] ?? '');

$reply = static function (bool $ok, string $msg) use ($isJson): void {
    if ($isJson) { header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); echo json_encode(['success' => $ok ? 'true' : 'false', 'message' => $msg], JSON_UNESCAPED_UNICODE); exit; }
    acc_page(T('רשימת התפוצה', 'Mailing list'), '<h1>' . ($ok ? T('כמעט סיימנו', 'Almost done') : T('משהו השתבש', 'Something went wrong')) . '</h1><p class="lead">' . h($msg) . '</p><p><a class="text-link" href="' . (T('/', '/en/')) . '">' . T('חזרה לאתר', 'Back to the site') . '</a></p>');
};

/* ---------- הצטרפות ---------- */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $do === '') {
    $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? ''));
    $host = (string) parse_url($origin, PHP_URL_HOST);
    if ($host !== '' && !preg_match('/(^|\.)yygweb\.com$/i', $host) && $host !== 'localhost') $reply(false, 'origin');
    if (!empty($in['website'])) $reply(true, T('שלחנו לכם מייל לאישור.', 'We sent you a confirmation email.'));
    $email = email_norm((string) ($in['email'] ?? ''));
    $name = mb_substr(trim((string) ($in['name'] ?? '')), 0, 80, 'UTF-8');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $reply(false, T('נא לכתוב כתובת מייל תקינה.', 'Please enter a valid email address.'));
    if (empty($in['consent'])) $reply(false, T('כדי להצטרף צריך לסמן את ההסכמה לקבל מיילים.', 'Please tick the consent box to join.'));
    if (!rate_ok('sub-ip-' . client_ip(), 6, 3600) || !rate_ok('sub-mail-' . $email, 3, 86400)) $reply(false, T('יותר מדי ניסיונות. נסו שוב מאוחר יותר.', 'Too many attempts. Please try again later.'));
    $source = preg_replace('/[^a-z0-9_-]/i', '', substr((string) ($in['source'] ?? 'site'), 0, 40)) ?: 'site';
    $s = sub_upsert($email, $name, $source, $EN ? 'en' : 'he', false);
    if ($s['status'] === 'active') $reply(true, T('הכתובת הזו כבר ברשימה. תודה!', 'This address is already on the list. Thanks!'));
    sub_send_confirm($s);
    $reply(true, T('שלחנו לכם מייל עם קישור לאישור. ההצטרפות תושלם אחרי הלחיצה עליו.', 'We sent you an email with a confirmation link. Click it to complete your subscription.'));
}

/* ---------- אישור והסרה ---------- */
$email = email_norm((string) ($in['e'] ?? ''));
$subs = subs_load();
$s = $subs[$email] ?? null;
$valid = $s && hash_equals((string) $s['token'], (string) ($in['t'] ?? ''));
if ($valid && ($s['lang'] ?? '') === 'en') $EN = true;   // מנוי באנגלית רואה את דף האישור וההסרה באנגלית
if (!$valid) acc_page(T('רשימת התפוצה', 'Mailing list'), '<h1>' . T('הקישור לא תקין', 'This link is not valid') . '</h1><p class="lead">' . T('ייתכן שהקישור לא הועתק עד הסוף. אפשר לכתוב לי ל־yehuda@yygweb.com.', 'The link may not have been copied in full. You can write to me at yehuda@yygweb.com.') . '</p>');

if ($do === 'confirm') {
    sub_set_status($email, 'active');
    acc_page(T('נרשמתם', 'You\'re in'), '<h1>' . T('נרשמתם, תודה!', 'You\'re in. Thank you!') . '</h1><p class="lead">' . T('מעכשיו מדריכים וטיפים חדשים יגיעו אליכם למייל. בכל מייל יש קישור להסרה.', 'New guides and tips will now reach your inbox. Every email has an unsubscribe link.') . '</p><p><a class="text-link" href="' . T('/guides/', '/en/guides/') . '">' . T('למדריכים שכבר באתר', 'Read the guides already on the site') . '</a></p>');
}
if ($do === 'unsub') {
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        sub_set_status($email, 'unsubscribed');
        acc_page(T('הוסרתם', 'Unsubscribed'), '<h1>' . T('הוסרתם מהרשימה', 'You\'ve been unsubscribed') . '</h1><p class="lead">' . T('לא יישלחו אליכם עוד מיילים מהרשימה. אם תתחרטו, אפשר להצטרף שוב מהאתר.', 'You won\'t receive more emails from the list. If you change your mind, you can join again from the site.') . '</p>');
    }
    acc_page(T('הסרה מהרשימה', 'Unsubscribe'), '<h1>' . T('להסיר את הכתובת מהרשימה?', 'Unsubscribe this address?') . '</h1><p class="lead"><bdi>' . h($email) . '</bdi></p>'
        . '<form method="post" class="box"><input type="hidden" name="do" value="unsub"><input type="hidden" name="e" value="' . h($email) . '"><input type="hidden" name="t" value="' . h((string) $s['token']) . '">' . ($EN ? '<input type="hidden" name="lang" value="en">' : '')
        . '<button class="btn btn-gold" type="submit">' . T('כן, להסיר אותי', 'Yes, unsubscribe me') . '</button></form>');
}
$reply(false, 'unknown');
