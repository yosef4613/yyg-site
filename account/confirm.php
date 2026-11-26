<?php
/*
 * YYG Web Design: אישור המייל של מי שהשאיר פנייה באתר. https://yygweb.com/account/confirm.php?l=<פנייה>&t=<אסימון>
 * הקישור מגיע ללקוח במייל מיד אחרי שהשאיר פרטים. האישור עצמו בלחיצה על כפתור (POST),
 * כדי שסורקי קישורים בתיבות מייל לא יאשרו במקום האדם.
 * אחרי האישור: הלקוח רואה (ומקבל במייל) את הטלפון שלי ואת הקישור לשאלון האפיון, ואני מקבל מייל קצר.
 */
declare(strict_types=1);
require __DIR__ . '/_acc.php';
$in = $_POST + $_GET;
$EN = ($in['lang'] ?? '') === 'en';
$id = preg_replace('/[^a-f0-9]/', '', (string) ($in['l'] ?? ''));
$tok = preg_replace('/[^a-f0-9]/', '', (string) ($in['t'] ?? ''));
$leads = $id !== '' ? leads_load() : [];
$lead = $leads[$id] ?? null;
$valid = $lead && $tok !== '' && !empty($lead['ctok']) && hash_equals((string) $lead['ctok'], hash('sha256', $tok));

if (!$valid) {
    acc_page(T('אישור הפנייה', 'Confirm your inquiry'), '<h1>' . T('הקישור לא תקין', 'This link isn\'t valid') . '</h1><p class="lead">'
        . T('אולי הוא נחתך בהעתקה. אפשר לפתוח שוב את הקישור מהמייל, או לכתוב לי ישירות: ', 'It may have been cut off when copied. Open the link from the email again, or write to me directly: ')
        . '<a class="text-link" href="mailto:yehuda@yygweb.com">yehuda@yygweb.com</a></p><p><a class="text-link" href="' . T('/', '/en/') . '">' . T('חזרה לאתר', 'Back to the site') . '</a></p>');
}

$name = (string) ($lead['name'] ?? '');
$qLink = 'https://yygweb.com/' . ($EN ? 'en/' : '') . 'questionnaire/?' . http_build_query(['name' => $name, 'email' => $lead['email'] ?? '', 'phone' => $lead['phone'] ?? '', 'business' => $lead['business'] ?? '']);
$phone = '054-854-0700';
$hours = T('בימים א׳ עד ד׳, בין 10:00 ל־13:30', 'Sunday to Wednesday, 10:00 to 13:30');

/* קישור לשאלון מהמייל: הלחיצה עצמה מאשרת שהמייל אמיתי, ומשם ישר לשאלון עם הפרטים שכבר יש */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && ($in['go'] ?? '') === 'q') {
    $first = data_update('lead-confirm.json', static function (array &$all) use ($id): bool {
        if (isset($all[$id])) { $all[$id]['q'] = $all[$id]['q'] ?? time(); return false; }
        $all[$id] = ['t' => time(), 'q' => time()];
        return true;
    });
    if ($first) {
        $admin = 'https://yygweb.com/admin/#lead-' . $id;
        notify_me('✓ פתח/ה את השאלון מהמייל: ' . $name,
            '<div dir="rtl" style="font-family:Arial,sans-serif;font-size:16px;line-height:1.6"><p><b>' . h($name) . '</b>' . (!empty($lead['business']) ? ' (' . h($lead['business']) . ')' : '') . ' לחץ/ה על הקישור לשאלון האפיון במייל. המייל <bdi>' . h($lead['email'] ?? '') . '</bdi> אמיתי.</p>'
            . '<p>כשהשאלון יישלח, תקבל אותו במייל נפרד.</p><p><a href="' . h($admin) . '">לפנייה בדף הניהול</a></p></div>',
            "$name פתח/ה את השאלון מהמייל.\n$admin");
    }
    header('Location: ' . $qLink, true, 302);
    exit;
}

/* האישור עצמו */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($in['do'] ?? '') === 'confirm') {
    $first = data_update('lead-confirm.json', static function (array &$all) use ($id): bool {
        if (isset($all[$id])) return false;
        $all[$id] = ['t' => time()];
        return true;
    });
    if ($first) {
        $admin = 'https://yygweb.com/admin/#lead-' . $id;
        notify_me('✓ אישר/ה את המייל: ' . $name,
            '<div dir="rtl" style="font-family:Arial,sans-serif;font-size:16px;line-height:1.6"><p><b>' . h($name) . '</b>' . (!empty($lead['business']) ? ' (' . h($lead['business']) . ')' : '') . ' אישר/ה את כתובת המייל <bdi>' . h($lead['email'] ?? '') . '</bdi>. זו פנייה אמיתית.</p>'
            . '<p>טלפון: <a href="tel:' . h(preg_replace('/[^\d+]/', '', (string) ($lead['phone'] ?? ''))) . '">' . h($lead['phone'] ?? '') . '</a></p><p><a href="' . h($admin) . '">לפנייה בדף הניהול</a></p></div>',
            "$name אישר/ה את המייל. טלפון: " . ($lead['phone'] ?? '') . "\n$admin");
        $body = $EN
            ? '<p>Thank you for confirming. I\'ll get back to you within one business day.</p>'
              . '<p><b>Want to talk sooner?</b> Call me at <a href="tel:0548540700" style="color:#1a56db;font-weight:700">' . $phone . '</a>, ' . h($hours) . '.</p>'
              . '<p><b>The next step:</b> a short discovery questionnaire, about 10 minutes. With it I come to our call prepared, with a formal quote.</p>' . mail_button($qLink, 'To the questionnaire')
            : '<p>תודה שאישרתם. אחזור אליכם תוך יום עבודה.</p>'
              . '<p><b>רוצים לדבר כבר עכשיו?</b> אפשר להתקשר אליי: <a href="tel:0548540700" style="color:#1a56db;font-weight:700">' . $phone . '</a>, ' . h($hours) . '.</p>'
              . '<p><b>הצעד הבא:</b> שאלון אפיון קצר, כ־10 דקות. איתו אני מגיע לשיחה מוכן, עם הצעת מחיר מסודרת.</p>' . mail_button($qLink, 'לשאלון האפיון');
        mail_send((string) $lead['email'], $EN ? 'My details, and the next step | YYG Web Design' : 'הפרטים שלי, והצעד הבא | YYG Web Design',
            mail_wrap($EN ? "Thank you, $name" : "תודה, $name", $body, '', $EN),
            ($EN ? "Thank you, $name.\nPhone: $phone ($hours)\nQuestionnaire: " : "תודה, $name.\nטלפון: $phone ($hours)\nשאלון האפיון: ") . $qLink);
    }
    header('Location: /account/confirm.php?' . http_build_query(['l' => $id, 't' => $tok] + ($EN ? ['lang' => 'en'] : [])), true, 303);
    exit;
}

/* אחרי האישור: הפרטים והצעד הבא */
if (isset(json_load('lead-confirm.json')[$id])) {
    acc_page(T('הפנייה אושרה', 'Inquiry confirmed'), '<h1>' . h(T("תודה, $name. הפנייה אושרה", "Thank you, $name. You're confirmed")) . '</h1>'
        . '<p class="lead">' . T('אחזור אליכם תוך יום עבודה. שלחתי לכם גם מייל עם הפרטים האלה.', 'I\'ll get back to you within one business day. I also emailed you these details.') . '</p>'
        . '<div class="box"><h2 style="margin-top:0">' . T('רוצים לדבר כבר עכשיו?', 'Want to talk sooner?') . '</h2><p>' . T('אפשר להתקשר אליי ', 'You can call me ') . h($hours) . '.</p>'
        . '<p><a class="btn btn-line" href="tel:0548540700" dir="ltr">' . $phone . '</a></p></div>'
        . '<div class="box"><h2 style="margin-top:0">' . T('הצעד הבא: שאלון האפיון', 'The next step: the discovery questionnaire') . '</h2><p>' . T('כ־10 דקות, והתשובות נשמרות תוך כדי. איתו אני מגיע לשיחה מוכן, עם הצעת מחיר מסודרת.', 'About 10 minutes, and your answers are saved as you go. With it I come to our call prepared, with a formal quote.') . '</p>'
        . '<p><a class="btn btn-gold" href="' . h($qLink) . '">' . T('לשאלון האפיון', 'To the questionnaire') . '</a></p></div>'
        . '<p><a class="text-link" href="' . T('/', '/en/') . '">' . T('חזרה לאתר', 'Back to the site') . '</a></p>');
}

/* לפני האישור: כפתור אחד */
acc_page(T('אישור הפנייה', 'Confirm your inquiry'), '<h1>' . h(T("שלום $name", "Hi $name")) . '</h1>'
    . '<p class="lead">' . T('לחיצה אחת כדי לאשר שהפנייה מהאתר באמת מכם. אחריה תקבלו את מספר הטלפון שלי ואת הצעד הבא.', 'One click to confirm that the inquiry on the site is really from you. Then you\'ll get my phone number and the next step.') . '</p>'
    . '<div class="box"><form method="post" action="/account/confirm.php"><input type="hidden" name="do" value="confirm"><input type="hidden" name="l" value="' . h($id) . '"><input type="hidden" name="t" value="' . h($tok) . '">'
    . ($EN ? '<input type="hidden" name="lang" value="en">' : '') . '<button class="btn btn-gold" type="submit">' . T('כן, זה אני. לאישור הפנייה', 'Yes, it\'s me. Confirm') . '</button></form></div>');
