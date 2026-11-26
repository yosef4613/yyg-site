<?php
/*
 * YYG Web Design: האזור האישי. https://yygweb.com/account/  (באנגלית: ?lang=en)
 * הרשמה (עם אימות מייל), כניסה, שכחתי סיסמה, ובאזור עצמו: הצעות המחיר שלי, רשימת התפוצה, פרטים, סיסמה ומחיקת חשבון.
 */
declare(strict_types=1);
require __DIR__ . '/_acc.php';
$EN = (($_GET['lang'] ?? $_POST['lang'] ?? '') === 'en');
$POST = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
$do = (string) ($_POST['do'] ?? $_GET['do'] ?? '');
$me = user_current();
/* הרמז לכותרת האתר ("האזור האישי" במקום "כניסה"): גם לחיבור מלפני שהיה רמז, ומתנקה כשהחיבור פג */
if ($me && ($_COOKIE['yyg_in'] ?? '') !== '1') setcookie('yyg_in', '1', ['expires' => time() + 60 * 86400, 'path' => '/', 'secure' => true, 'samesite' => 'Lax']);
if (!$me && isset($_COOKIE['yyg_in'])) setcookie('yyg_in', '', ['expires' => 1, 'path' => '/', 'secure' => true, 'samesite' => 'Lax']);
$langField = $EN ? '<input type="hidden" name="lang" value="en">' : '';
$go = static function (string $q = '') use ($EN): void { header('Location: /account/' . ($EN ? '?lang=en' . ($q ? '&' . $q : '') : ($q ? '?' . $q : '')), true, 303); exit; };
$msgPage = static function (string $title, string $text) : void {
    acc_page($title, '<h1>' . h($title) . '</h1><p class="lead">' . $text . '</p><p><a class="text-link" href="' . h(acc_url()) . '">' . T('לכניסה לאזור האישי', 'Go to your account') . '</a></p>');
};
/* הטקסט של המייל נכתב בשפת הדף (T), ולכן גם המסגרת והשולח בשפת הדף */
$send = static function (array $u, string $subject, string $title, string $body, string $text) use ($EN): bool {
    return mail_send($u['email'], $subject, mail_wrap($title, $body, '', $EN), $text, mail_from($EN));
};
$err = '';

/* ---------- פעולות בלי כניסה ---------- */
if ($POST && in_array($do, ['register', 'login', 'forgot', 'resend', 'reset'], true)) {
    if (!empty($_POST['website'])) $go();
    if ($do !== 'reset' && form_token_age((string) ($_POST['ft'] ?? '')) < 2) $err = T('הטופס פג תוקף. נסו שוב.', 'The form expired. Please try again.');
}

if ($POST && $do === 'register' && $err === '') {
    $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 80, 'UTF-8');
    $email = email_norm((string) ($_POST['email'] ?? ''));
    $phone = mb_substr(trim((string) ($_POST['phone'] ?? '')), 0, 30, 'UTF-8');
    $pass = (string) ($_POST['password'] ?? '');
    if (mb_strlen($name, 'UTF-8') < 2) $err = T('נא לכתוב שם.', 'Please enter your name.');
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = T('נא לכתוב כתובת מייל תקינה.', 'Please enter a valid email address.');
    elseif (strlen($pass) < 8) $err = T('הסיסמה צריכה להיות באורך 8 תווים לפחות.', 'Your password needs at least 8 characters.');
    elseif (!rate_ok('reg-' . client_ip(), 5, 3600)) $err = T('יותר מדי ניסיונות. נסו שוב בעוד שעה.', 'Too many attempts. Please try again in an hour.');
    else {
        $news = !empty($_POST['news']);
        [$tok, $hash] = one_time_token();
        $lang = $EN ? 'en' : 'he';
        $existing = user_by_email(users_load(), $email);
        if ($existing !== null) {
            /* לא מגלים בדף שהכתובת רשומה. שולחים לבעל הכתובת מייל עם קישור כניסה ואיפוס */
            $u = users_load()[$existing];
            $send($u, T('כבר יש לכם חשבון ב־YYG Web Design', 'You already have a YYG Web Design account'), T('כבר יש לכם חשבון', 'You already have an account'),
                '<p>' . T('מישהו ניסה להירשם עם הכתובת הזו. אם זה הייתם אתם, אפשר פשוט להיכנס, או לאפס את הסיסמה:', 'Someone tried to sign up with this address. If it was you, just log in, or reset your password:') . '</p>' . mail_button(acc_url('', ['do' => 'forgot']), T('איפוס סיסמה', 'Reset my password')),
                acc_url('', ['do' => 'forgot']));
        } else {
            $uid = bin2hex(random_bytes(8));
            data_update('users.json', static function (array &$users) use ($uid, $email, $name, $phone, $pass, $hash, $lang, $news): void {
                $users[$uid] = ['email' => $email, 'name' => $name, 'phone' => $phone, 'pass' => password_hash($pass, PASSWORD_DEFAULT), 'created' => time(), 'verified' => false, 'sv' => 1, 'lang' => $lang, 'newsletter' => $news, 'vt' => $hash, 'vt_exp' => time() + 48 * 3600];
            });
            if ($news) sub_upsert($email, $name, 'register', $lang, false);
            $link = acc_url('', ['do' => 'verify', 'u' => $uid, 't' => $tok]);
            $send(['email' => $email, 'lang' => $lang], T('אישור ההרשמה ל־YYG Web Design', 'Confirm your YYG Web Design account'), T('ברוכים הבאים, ' . $name, 'Welcome, ' . $name),
                '<p>' . T('עוד לחיצה אחת, ואתם בפנים. הקישור בתוקף ל־48 שעות.', 'One more click and you\'re in. The link is valid for 48 hours.') . '</p>' . mail_button($link, T('אישור המייל וכניסה', 'Confirm and log in'))
                . ($news ? '<p style="color:#5A6472;font-size:14px">' . T('האישור יצרף אתכם גם לרשימת התפוצה, כמו שביקשתם.', 'This also adds you to the mailing list, as you asked.') . '</p>' : ''),
                $link);
            notify_me('משתמש חדש נרשם לאתר: ' . $name, '<div dir="rtl" style="font-family:Arial,sans-serif">' . h($name) . ' (' . h($email) . ') נרשם/ה לאתר' . ($news ? ' והצטרף/ה לרשימת התפוצה' : '') . '. <a href="https://yygweb.com/admin/#users">לדף הניהול</a></div>', "$name ($email) נרשם/ה לאתר.");
        }
        $msgPage(T('בדקו את המייל', 'Check your email'), T('שלחנו מייל ל־', 'We sent an email to ') . '<bdi>' . h($email) . '</bdi>' . T('. לחצו על הקישור שבו כדי לסיים. לא הגיע? כדאי לבדוק גם בספאם.', '. Click the link in it to finish. Didn\'t get it? Check your spam folder too.'));
    }
}

if ($do === 'verify' && !$POST) {
    $uid = (string) ($_GET['u'] ?? ''); $tok = (string) ($_GET['t'] ?? '');
    $ok = data_update('users.json', static function (array &$users) use ($uid, $tok): ?array {
        $u = $users[$uid] ?? null;
        if (!$u || empty($u['vt']) || (int) ($u['vt_exp'] ?? 0) < time() || !hash_equals((string) $u['vt'], hash('sha256', $tok))) return null;
        $users[$uid]['verified'] = true;
        unset($users[$uid]['vt'], $users[$uid]['vt_exp']);
        return $users[$uid];
    });
    if (!$ok) $msgPage(T('הקישור לא תקין', 'This link is not valid'), T('ייתכן שהקישור כבר נוצל או שפג תוקפו. אפשר להיכנס ולבקש מייל אימות חדש.', 'It may have been used already or expired. Log in to request a new confirmation email.'));
    if (!empty($ok['newsletter'])) sub_upsert($ok['email'], $ok['name'], 'register', $ok['lang'] ?? 'he', true);
    user_login_cookie($uid, (int) $ok['sv']);
    $go('welcome=1');
}

if ($POST && $do === 'login' && $err === '') {
    $email = email_norm((string) ($_POST['email'] ?? ''));
    $pass = (string) ($_POST['password'] ?? '');
    if (!rate_ok('login-ip-' . client_ip(), 12, 900) || !rate_ok('login-mail-' . $email, 6, 900)) $err = T('יותר מדי ניסיונות. נסו שוב בעוד רבע שעה.', 'Too many attempts. Please try again in 15 minutes.');
    else {
        $users = users_load();
        $uid = user_by_email($users, $email);
        $u = $uid ? $users[$uid] : null;
        if (!$u || !password_verify($pass, (string) $u['pass'])) $err = T('המייל או הסיסמה לא נכונים.', 'Wrong email or password.');
        elseif (empty($u['verified'])) $err = T('צריך קודם לאשר את המייל. לא הגיע? אפשר לבקש שוב למטה.', 'Please confirm your email first. Didn\'t get it? You can ask for it again below.');
        else {
            data_update('users.json', static function (array &$users) use ($uid, $pass): void {
                $users[$uid]['last_login'] = time();
                if (password_needs_rehash((string) $users[$uid]['pass'], PASSWORD_DEFAULT)) $users[$uid]['pass'] = password_hash($pass, PASSWORD_DEFAULT);
            });
            user_login_cookie($uid, (int) $u['sv']);
            $go();
        }
    }
}

if ($POST && ($do === 'forgot' || $do === 'resend') && $err === '') {
    $email = email_norm((string) ($_POST['email'] ?? ''));
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && rate_ok('mail-' . $do . '-' . $email, 3, 3600) && rate_ok('mail-ip-' . client_ip(), 10, 3600)) {
        $uid = user_by_email(users_load(), $email);
        if ($uid) {
            [$tok, $hash] = one_time_token();
            $u = data_update('users.json', static function (array &$users) use ($uid, $hash, $do): array {
                if ($do === 'forgot') { $users[$uid]['rt'] = $hash; $users[$uid]['rt_exp'] = time() + 3600; }
                elseif (empty($users[$uid]['verified'])) { $users[$uid]['vt'] = $hash; $users[$uid]['vt_exp'] = time() + 48 * 3600; }
                return $users[$uid];
            });
            if ($do === 'forgot') {
                $link = acc_url('', ['do' => 'reset', 'u' => $uid, 't' => $tok]);
                $send($u, T('איפוס סיסמה ב־YYG Web Design', 'Reset your YYG Web Design password'), T('איפוס סיסמה', 'Reset your password'), '<p>' . T('לחצו כדי לבחור סיסמה חדשה. הקישור בתוקף לשעה. אם לא ביקשתם, אפשר להתעלם.', 'Click to choose a new password. The link is valid for one hour. If you didn\'t ask for this, you can ignore it.') . '</p>' . mail_button($link, T('בחירת סיסמה חדשה', 'Choose a new password')), $link);
            } elseif (empty($u['verified'])) {
                $link = acc_url('', ['do' => 'verify', 'u' => $uid, 't' => $tok]);
                $send($u, T('אישור ההרשמה ל־YYG Web Design', 'Confirm your YYG Web Design account'), T('אישור המייל', 'Confirm your email'), mail_button($link, T('אישור המייל וכניסה', 'Confirm and log in')), $link);
            }
        }
    }
    $msgPage(T('בדקו את המייל', 'Check your email'), T('אם הכתובת רשומה אצלנו, שלחנו אליה מייל עם קישור. כדאי לבדוק גם בספאם.', 'If this address is registered, we sent it an email with a link. Check your spam folder too.'));
}

if ($do === 'reset') {
    $uid = (string) ($_POST['u'] ?? $_GET['u'] ?? ''); $tok = (string) ($_POST['t'] ?? $_GET['t'] ?? '');
    $u = users_load()[$uid] ?? null;
    $valid = $u && !empty($u['rt']) && (int) ($u['rt_exp'] ?? 0) >= time() && hash_equals((string) $u['rt'], hash('sha256', $tok));
    if (!$valid) $msgPage(T('הקישור לא תקין', 'This link is not valid'), T('ייתכן שפג תוקפו (שעה). אפשר לבקש קישור חדש ב"שכחתי סיסמה".', 'It may have expired (one hour). You can ask for a new one under "Forgot password".'));
    if ($POST) {
        $pass = (string) ($_POST['password'] ?? '');
        if (strlen($pass) < 8) $err = T('הסיסמה צריכה להיות באורך 8 תווים לפחות.', 'Your password needs at least 8 characters.');
        else {
            $sv = data_update('users.json', static function (array &$users) use ($uid, $pass): int {
                $users[$uid]['pass'] = password_hash($pass, PASSWORD_DEFAULT);
                $users[$uid]['sv'] = (int) ($users[$uid]['sv'] ?? 1) + 1;   // מנתק מכשירים אחרים
                $users[$uid]['verified'] = true;
                unset($users[$uid]['rt'], $users[$uid]['rt_exp']);
                return $users[$uid]['sv'];
            });
            user_login_cookie($uid, $sv);
            $go('saved=1');
        }
    }
    acc_page(T('סיסמה חדשה', 'New password'), '<h1>' . T('בחירת סיסמה חדשה', 'Choose a new password') . '</h1>' . ($err ? '<p class="msg bad" role="alert">' . h($err) . '</p>' : '')
        . '<div class="box"><form method="post" action="/account/">' . $langField . '<input type="hidden" name="do" value="reset"><input type="hidden" name="u" value="' . h($uid) . '"><input type="hidden" name="t" value="' . h($tok) . '">'
        . '<label class="field"><span>' . T('סיסמה חדשה', 'New password') . ' <small>' . T('8 תווים לפחות', 'at least 8 characters') . '</small></span><input type="password" name="password" autocomplete="new-password" minlength="8" required></label>'
        . '<button class="btn btn-gold" type="submit">' . T('שמירה וכניסה', 'Save and log in') . '</button></form></div>');
}

/* ---------- כניסה והרשמה עם Google ---------- */
if ($POST && $do === 'google') {
    $toAdmin = ($_POST['to'] ?? '') === 'admin';
    $g = rate_ok('google-' . client_ip(), 20, 900) ? google_verify((string) ($_POST['credential'] ?? '')) : null;
    if (!$g) {
        if ($toAdmin) { header('Location: /admin/?g=bad', true, 303); exit; }
        $err = T('הכניסה עם Google לא הצליחה. אפשר לנסות שוב, או להיכנס עם מייל וסיסמה.', 'Signing in with Google didn\'t work. Please try again, or log in with your email and password.');
    } elseif ($toAdmin && !is_owner_email($g['email'])) {
        /* מדף הכניסה לניהול: רק בעל האתר. לא פותחים חשבון למי שאינו */
        header('Location: /admin/?g=no', true, 303); exit;
    } else {
        $lang = $EN ? 'en' : 'he';
        $uid = data_update('users.json', static function (array &$users) use ($g, $lang): string {
            $uid = user_by_email($users, $g['email']);
            if ($uid === null) {
                do { $uid = bin2hex(random_bytes(8)); } while (isset($users[$uid]));
                $users[$uid] = ['email' => $g['email'], 'name' => $g['name'] !== '' ? $g['name'] : strstr($g['email'], '@', true), 'phone' => '', 'pass' => '', 'created' => time(), 'verified' => true, 'sv' => 1, 'lang' => $lang, 'via' => 'google'];
            }
            /* Google כבר אימת את המייל, אז גם חשבון שחיכה לאישור במייל נפתח */
            $users[$uid]['verified'] = true;
            unset($users[$uid]['vt'], $users[$uid]['vt_exp']);
            $users[$uid]['google'] = $g['sub'];
            $users[$uid]['last_login'] = time();
            return $uid;
        });
        $u = users_load()[$uid];
        user_login_cookie($uid, (int) $u['sv']);
        if ($toAdmin) { header('Location: ' . adm_after_login(), true, 303); exit; }
        $go();
    }
}

/* ---------- פעולות של משתמש מחובר ---------- */
if ($POST && $me && in_array($do, ['logout', 'profile', 'password', 'news', 'delete', 'admin_link', 'admin_unlink'], true)) {
    if (!hash_equals(user_csrf($me), (string) ($_POST['csrf'] ?? ''))) $go();
    $uid = $me['uid'];
    if ($do === 'logout') { if (is_owner_email($me['email'])) adm_logout(); user_logout_cookie(); $go('bye=1'); }
    /* רק מדפדפן שכבר מחובר לניהול: החשבון הזה יקבל גישה לניהול (למשל הג׳ימייל, לכניסה עם Google) */
    if (($do === 'admin_link' || $do === 'admin_unlink') && adm_authed()) {
        $email = $me['email'];
        data_update('admin-accounts.json', static function (array &$a) use ($email, $do): void { if ($do === 'admin_link') $a[$email] = ['t' => time()]; else unset($a[$email]); });
        $go('saved=1');
    }
    if ($do === 'profile') {
        $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 80, 'UTF-8');
        $phone = mb_substr(trim((string) ($_POST['phone'] ?? '')), 0, 30, 'UTF-8');
        if (mb_strlen($name, 'UTF-8') >= 2) data_update('users.json', static function (array &$users) use ($uid, $name, $phone): void { $users[$uid]['name'] = $name; $users[$uid]['phone'] = $phone; });
        $go('saved=1');
    }
    if ($do === 'password') {
        $cur = (string) ($_POST['current'] ?? ''); $new = (string) ($_POST['password'] ?? '');
        if ((string) $me['pass'] !== '' && !password_verify($cur, (string) $me['pass'])) $err = T('הסיסמה הנוכחית לא נכונה.', 'Your current password is wrong.');
        elseif (strlen($new) < 8) $err = T('הסיסמה החדשה צריכה להיות באורך 8 תווים לפחות.', 'Your new password needs at least 8 characters.');
        else {
            $sv = data_update('users.json', static function (array &$users) use ($uid, $new): int { $users[$uid]['pass'] = password_hash($new, PASSWORD_DEFAULT); $users[$uid]['sv'] = (int) ($users[$uid]['sv'] ?? 1) + 1; return $users[$uid]['sv']; });
            user_login_cookie($uid, $sv);
            $go('saved=1');
        }
    }
    if ($do === 'news') {
        $on = !empty($_POST['on']);
        data_update('users.json', static function (array &$users) use ($uid, $on): void { $users[$uid]['newsletter'] = $on; });
        if ($on) sub_upsert($me['email'], $me['name'], 'account', $me['lang'] ?? 'he', true); else sub_set_status($me['email'], 'unsubscribed');
        $go('saved=1');
    }
    if ($do === 'delete') {
        if (empty($_POST['sure'])) $err = T('כדי למחוק צריך לסמן את התיבה.', 'Tick the box to confirm deletion.');
        else {
            data_update('users.json', static function (array &$users) use ($uid): void { unset($users[$uid]); });
            sub_set_status($me['email'], 'unsubscribed');
            user_logout_cookie();
            $go('deleted=1');
        }
    }
    $me = user_current();
}

/* ---------- תצוגה ---------- */
$ft = form_token();
$hp = '<input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">';
$flash = isset($_GET['saved']) ? T('נשמר.', 'Saved.') : (isset($_GET['bye']) ? T('יצאתם מהחשבון.', 'You\'re logged out.') : (isset($_GET['deleted']) ? T('החשבון נמחק.', 'Your account was deleted.') : ''));

if (!$me) {
    $tab = in_array($_GET['tab'] ?? $do, ['register', 'forgot', 'resend'], true) ? ($_GET['tab'] ?? $do) : 'login';
    $q = static fn(string $t): string => '/account/?' . ($EN ? 'lang=en&' : '') . 'tab=' . $t;
    $body = '<h1>' . T('האזור האישי', 'Your account') . '</h1><p class="lead">' . T('כאן רואים את הצעות המחיר שלכם, מאשרים אותן, ומנהלים את ההרשמה למדריכים במייל.', 'See and approve your quotes, and manage your email subscription to new guides.') . '</p>'
        . ($flash ? '<p class="msg">' . h($flash) . '</p>' : '') . ($err ? '<p class="msg bad" role="alert">' . h($err) . '</p>' : '')
        . '<nav class="tabs"><a href="' . $q('login') . '" class="' . ($tab === 'login' ? 'on' : '') . '">' . T('כניסה', 'Log in') . '</a><a href="' . $q('register') . '" class="' . ($tab === 'register' ? 'on' : '') . '">' . T('הרשמה', 'Sign up') . '</a></nav><div class="box">';
    $gBtn = google_button('account', $EN);
    $or = $gBtn !== '' ? $gBtn . '<p class="or">' . T('או עם מייל', 'or with email') . '</p>' : '';
    if ($tab === 'register') {
        $body .= $or . '<form method="post" action="/account/">' . $langField . $hp . '<input type="hidden" name="do" value="register"><input type="hidden" name="ft" value="' . h($ft) . '">'
            . '<label class="field"><span>' . T('שם', 'Name') . '</span><input name="name" autocomplete="name" required value="' . h($_POST['name'] ?? '') . '"></label>'
            . '<label class="field"><span>' . T('מייל', 'Email') . '</span><input type="email" name="email" autocomplete="email" dir="ltr" required value="' . h($_POST['email'] ?? '') . '"></label>'
            . '<label class="field"><span>' . T('טלפון', 'Phone') . ' <small>' . T('לא חובה', 'optional') . '</small></span><input type="tel" name="phone" autocomplete="tel" dir="ltr" value="' . h($_POST['phone'] ?? '') . '"></label>'
            . '<label class="field"><span>' . T('סיסמה', 'Password') . ' <small>' . T('8 תווים לפחות', 'at least 8 characters') . '</small></span><input type="password" name="password" autocomplete="new-password" minlength="8" required></label>'
            . '<label class="chk"><input type="checkbox" name="news" value="1"' . (!empty($_POST['news']) ? ' checked' : '') . '><span>' . h(T(CONSENT_HE, CONSENT_EN)) . '</span></label>'
            . '<button class="btn btn-gold" type="submit">' . T('הרשמה', 'Sign up') . '</button>'
            . '<p style="margin:0;font-size:.9rem">' . T('ההרשמה כפופה ל', 'By signing up you accept the ') . '<a class="text-link" href="' . T('/privacy.html', '/en/privacy.html') . '">' . T('מדיניות הפרטיות', 'privacy policy') . '</a>.</p></form>';
    } elseif ($tab === 'forgot' || $tab === 'resend') {
        $body .= '<form method="post" action="/account/">' . $langField . $hp . '<input type="hidden" name="do" value="' . $tab . '"><input type="hidden" name="ft" value="' . h($ft) . '">'
            . '<p style="margin:0">' . ($tab === 'forgot' ? T('נשלח לכם קישור לבחירת סיסמה חדשה.', 'We\'ll send you a link to choose a new password.') : T('נשלח לכם שוב את מייל האישור.', 'We\'ll send the confirmation email again.')) . '</p>'
            . '<label class="field"><span>' . T('מייל', 'Email') . '</span><input type="email" name="email" autocomplete="email" dir="ltr" required></label>'
            . '<button class="btn btn-gold" type="submit">' . T('שליחה', 'Send') . '</button></form>';
    } else {
        $body .= $or . '<form method="post" action="/account/">' . $langField . $hp . '<input type="hidden" name="do" value="login"><input type="hidden" name="ft" value="' . h($ft) . '">'
            . '<label class="field"><span>' . T('מייל', 'Email') . '</span><input type="email" name="email" autocomplete="email" dir="ltr" required value="' . h($_POST['email'] ?? '') . '"></label>'
            . '<label class="field"><span>' . T('סיסמה', 'Password') . '</span><input type="password" name="password" autocomplete="current-password" required></label>'
            . '<button class="btn btn-gold" type="submit">' . T('כניסה', 'Log in') . '</button>'
            . '<p style="margin:0;font-size:.92rem"><a class="text-link" href="' . $q('forgot') . '">' . T('שכחתי סיסמה', 'Forgot password') . '</a> · <a class="text-link" href="' . $q('resend') . '">' . T('לא קיבלתי מייל אישור', 'I didn\'t get a confirmation email') . '</a></p></form>';
    }
    acc_page(T('האזור האישי', 'Your account'), $body . '</div>');
}

/* ---------- האזור האישי ---------- */
$csrf = user_csrf($me);
$hidden = $langField . '<input type="hidden" name="csrf" value="' . h($csrf) . '">';
$myQuotes = [];
/* רק לפי המייל, שאומת בהרשמה. לא לפי טלפון, כי טלפון לא מאומת ומישהו יכול לרשום מספר של אחר */
foreach (quotes_load() as $num => $q) {
    $qe = email_norm((string) ($q['client']['email'] ?? ''));
    if ($qe !== '' && $qe === $me['email']) $myQuotes[$num] = $q;
}
$sub = subs_load()[$me['email']] ?? null;
$subOn = $sub && ($sub['status'] ?? '') === 'active';
$subPending = $sub && ($sub['status'] ?? '') === 'pending';
$body = '<h1>' . T('שלום ', 'Hi ') . h($me['name']) . '</h1>'
    /* מי מחובר, ויציאה מיד למעלה: כדי להחליף חשבון או להיכנס עם Google */
    . '<form method="post" action="/account/" class="who">' . $hidden . '<input type="hidden" name="do" value="logout"><span>' . T('מחוברים בתור', 'Logged in as') . ' <bdi>' . h($me['email']) . '</bdi></span> · <button type="submit">' . T('יציאה', 'Log out') . '</button></form>'
    . (isset($_GET['welcome']) ? '<p class="msg">' . T('המייל אושר, ואתם בפנים. ברוכים הבאים!', 'Your email is confirmed and you\'re in. Welcome!') . '</p>' : '')
    . ($flash ? '<p class="msg">' . h($flash) . '</p>' : '') . ($err ? '<p class="msg bad" role="alert">' . h($err) . '</p>' : '')
    /* רק בדפדפן שמחובר לניהול. חשבון של בעל האתר: קישור לניהול. חשבון של לקוח: תיבה סגורה שאומרת במפורש שזה חשבון רגיל */
    . (!adm_authed() ? '' : (is_owner_email($me['email'])
        ? '<div class="box" lang="he" dir="rtl"><p style="margin:0"><b>ניהול האתר.</b> החשבון הזה מחובר לניהול: כשנכנסים אליו (גם עם Google), נכנסים גם לניהול.</p>'
          . '<div class="inline"><a class="btn btn-gold" href="/admin/">לדף הניהול</a>'
          . (isset(owner_linked()[$me['email']]) ? '<form method="post" action="/account/">' . $hidden . '<input type="hidden" name="do" value="admin_unlink"><button class="btn" type="submit">להסיר את הגישה לניהול</button></form>' : '')
          . '</div></div>'
        : '<details class="box" lang="he" dir="rtl"><summary><span style="font-weight:400"><b>הדפדפן הזה מחובר לניהול.</b> החשבון הזה (<bdi>' . h($me['email']) . '</bdi>) הוא חשבון לקוח רגיל, בלי גישה לניהול.</span></summary>'
          . '<p>את התיבה הזאת רואה רק מי שמחובר לניהול באותו דפדפן. לקוחות לא רואים אותה. אם זה חשבון שלך (למשל הג׳ימייל שלך) ורוצים להיכנס דרכו גם לניהול, אפשר לתת לו גישה:</p>'
          . '<div class="inline"><form method="post" action="/account/" onsubmit="return confirm(\'לתת לחשבון הזה גישה מלאה לניהול האתר?\')">' . $hidden . '<input type="hidden" name="do" value="admin_link"><button class="btn" type="submit">לתת לחשבון הזה גישה לניהול</button></form>'
          . '<a class="text-link" href="/admin/">לדף הניהול</a></div></details>'))
    . '<h2>' . T('הצעות המחיר שלי', 'My quotes') . '</h2>';
if ($myQuotes) {
    $body .= '<div class="rows">';
    foreach (array_reverse($myQuotes, true) as $num => $q) {
        $t = quote_totals($q);
        $st = ($q['status'] ?? '') === 'approved' ? T('אושרה', 'Approved') : T('ממתינה לאישור', 'Awaiting approval');
        $body .= '<a href="' . h(quote_url($q)) . '"><span><b>' . h($q['title'] ?: $num) . '</b><br><small>' . h($num) . ' · ' . h($st) . '</small></span><b>' . h(nis_lang($t['total'], $EN)) . '</b></a>';
    }
    $body .= '</div>';
} else {
    $body .= '<p>' . T('עוד אין כאן הצעות. הצעות שאשלח למייל שרשום בחשבון יופיעו כאן.', 'No quotes yet. Quotes I send to the email in your account will show up here.') . ' <a class="text-link" href="' . T('/#plan', '/en/#plan') . '">' . T('למחשבון המחיר', 'Try the price calculator') . '</a></p>';
}
$body .= '<h2>' . T('מדריכים במייל', 'Guides by email') . '</h2><div class="box"><form method="post" action="/account/">' . $hidden . '<input type="hidden" name="do" value="news">'
    . '<p style="margin:0">' . ($subOn ? T('אתם ברשימת התפוצה. מדריכים וטיפים חדשים מגיעים אליכם למייל.', 'You\'re on the mailing list. New guides and tips reach your inbox.') : ($subPending ? T('ההצטרפות ממתינה לאישור במייל. אפשר גם לאשר כאן:', 'Your subscription is waiting for email confirmation. You can also confirm here:') : T('אתם לא ברשימת התפוצה.', 'You\'re not on the mailing list.'))) . '</p>'
    . ($subOn ? '<button class="btn" type="submit">' . T('הסרה מהרשימה', 'Unsubscribe') . '</button>'
              : '<label class="chk"><input type="checkbox" name="on" value="1" required><span>' . h(T(CONSENT_HE, CONSENT_EN)) . '</span></label><button class="btn btn-gold" type="submit">' . T('הצטרפות לרשימה', 'Join the list') . '</button>')
    . '</form></div>'
    . '<h2>' . T('הפרטים שלי', 'My details') . '</h2><div class="box"><form method="post" action="/account/">' . $hidden . '<input type="hidden" name="do" value="profile">'
    . '<label class="field"><span>' . T('שם', 'Name') . '</span><input name="name" autocomplete="name" required value="' . h($me['name']) . '"></label>'
    . '<label class="field"><span>' . T('מייל', 'Email') . '</span><input type="email" value="' . h($me['email']) . '" dir="ltr" disabled></label>'
    . '<label class="field"><span>' . T('טלפון', 'Phone') . '</span><input type="tel" name="phone" autocomplete="tel" dir="ltr" value="' . h($me['phone'] ?? '') . '"></label>'
    . '<button class="btn" type="submit">' . T('שמירה', 'Save') . '</button></form></div>'
    . '<h2>' . T('סיסמה', 'Password') . '</h2><div class="box"><form method="post" action="/account/">' . $hidden . '<input type="hidden" name="do" value="password">'
    . ((string) $me['pass'] === '' ? '<p style="margin:0">' . T('נכנסתם עם Google, ולכן אין צורך בסיסמה. אפשר לבחור סיסמה, כדי להיכנס גם עם המייל.', 'You signed in with Google, so you don\'t need a password. You can choose one to also log in with your email.') . '</p>'
        : '<label class="field"><span>' . T('סיסמה נוכחית', 'Current password') . '</span><input type="password" name="current" autocomplete="current-password" required></label>')
    . '<label class="field"><span>' . T('סיסמה חדשה', 'New password') . ' <small>' . T('8 תווים לפחות', 'at least 8 characters') . '</small></span><input type="password" name="password" autocomplete="new-password" minlength="8" required></label>'
    . '<button class="btn" type="submit">' . T('החלפת סיסמה', 'Change password') . '</button></form></div>'
    . '<div class="inline" style="margin-top:28px"><form method="post" action="/account/">' . $hidden . '<input type="hidden" name="do" value="logout"><button class="btn" type="submit">' . T('יציאה', 'Log out') . '</button></form></div>'
    . '<details style="margin-top:28px"><summary class="text-link">' . T('מחיקת החשבון', 'Delete my account') . '</summary><div class="box"><form method="post" action="/account/">' . $hidden . '<input type="hidden" name="do" value="delete">'
    . '<p style="margin:0">' . T('החשבון והפרטים בו יימחקו, ותוסרו מרשימת התפוצה. הצעות מחיר שכבר נשלחו נשמרות אצלי לצורך העבודה.', 'Your account and its details will be deleted, and you\'ll be removed from the mailing list. Quotes already sent stay with me for the work.') . '</p>'
    . '<label class="chk"><input type="checkbox" name="sure" value="1" required><span>' . T('כן, למחוק את החשבון', 'Yes, delete my account') . '</span></label><button class="btn" type="submit">' . T('מחיקה', 'Delete') . '</button></form></div></details>';
acc_page(T('האזור האישי', 'Your account'), $body);
