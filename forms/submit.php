<?php
/*
 * YYG Web Design: קבלת שאלונים (אפיון ומשוב על עיצובים) מ־/assets/forms.js.
 * POST מסוג multipart: שדה payload (JSON) וקבצים מצורפים (files[] + fileq[] לשאלה של כל קובץ).
 * כל שאלון נשמר מחוץ לתיקיית האתר (forms.jsonl, והקבצים ב־uploads/<מזהה>/), נשלח אליי במייל, ומופיע בדף הניהול.
 * אף שאלון של אדם לא נזרק בשקט: מה שנראה חשוד נשמר ונשלח עם סימון "לבדיקה". רק שדה נסתר שמולא נרשם כספאם.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/admin/_lib.php';
require dirname(__DIR__) . '/admin/_intake.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$out = static function (bool $ok, string $msg = '', int $code = 200, array $extra = []): void { http_response_code($code); echo json_encode(['success' => $ok ? 'true' : 'false', 'message' => $msg] + $extra, JSON_UNESCAPED_UNICODE); exit; };

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') $out(false, 'method', 405);
$origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? ''));
$host = (string) parse_url($origin, PHP_URL_HOST);
if ($host !== '' && !preg_match('/(^|\.)yygweb\.com$/i', $host) && $host !== 'localhost') $out(false, 'origin', 403);

$in = isset($_POST['payload']) ? json_decode((string) $_POST['payload'], true) : json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in)) $out(false, 'bad', 400);
$s = static fn($v, int $max): string => mb_substr(trim(str_replace("\0", '', is_scalar($v) ? (string) $v : '')), 0, $max, 'UTF-8');
$c = (array) ($in['contact'] ?? []);
$contact = ['name' => $s($c['name'] ?? '', 120), 'phone' => $s($c['phone'] ?? '', 40), 'email' => $s($c['email'] ?? '', 160), 'business' => $s($c['business'] ?? '', 160), 'quote' => $s($c['quote'] ?? '', 40)];

/* שדה נסתר שמולא: רובוט. נרשם ביומן הספאם (רואים אותו בדף הניהול) ולא נשלח */
if (!empty($in['hp']) || !empty($in['website'])) {
    @file_put_contents(data_dir() . '/spam.log', date('c') . ' [שאלון: שדה נסתר מולא] ' . json_encode($contact, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    $out(true);
}
if (!rate_ok('forms-' . client_ip(), 8, 3600) || !rate_ok('forms-all', 40, 86400)) $out(false, 'rate', 429);   // גם תקרה יומית כללית

$form = in_array($in['form'] ?? '', ['intake', 'feedback'], true) ? $in['form'] : '';
if ($form === '') $out(false, 'form', 400);
if (mb_strlen($contact['name'], 'UTF-8') < 2) $out(false, 'name', 422);
$mailOk = (bool) filter_var($contact['email'], FILTER_VALIDATE_EMAIL);
if (!$mailOk) $out(false, 'contact', 422);   // מייל חובה, טלפון לא חובה
$items = [];
foreach (array_slice((array) ($in['items'] ?? []), 0, 200) as $it) {
    if (!is_array($it)) continue;
    $a = $s($it['a'] ?? '', 6000);
    if ($a === '') continue;
    $items[] = ['s' => $s($it['s'] ?? '', 120), 'q' => $s($it['q'] ?? '', 300), 'a' => $a];
}
/* התשובות לפי מזהה שאלה (בשביל בונה ההצעות) */
$raw = [];
foreach ((array) ($in['raw'] ?? []) as $k => $v) {
    if (!preg_match('/^[a-z0-9_]{1,40}$/', (string) $k) || count($raw) >= 150) continue;
    if (is_array($v)) $raw[$k] = array_slice(array_values(array_map(static fn($x) => $s($x, 300), array_filter($v, 'is_scalar'))), 0, 40);
    elseif (is_scalar($v)) $raw[$k] = $s($v, 6000);
}
$flags = [];
if ((int) ($in['t'] ?? 0) < 10000) $flags[] = 'נשלח מהר מאוד';
$lang = ($in['lang'] ?? '') === 'en' ? 'en' : 'he';
$id = bin2hex(random_bytes(6));

/* ---------- קבצים מצורפים ---------- */
/* סוגי הקבצים והמגבלות: FORM_FILE_TYPES ב־admin/_lib.php. קבצים חדשים מגיעים אחרי השאלון, אחד־אחד, דרך forms/upload.php */
$files = [];
$skipped = [];
if (!empty($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
    $dir = data_dir() . '/uploads/' . $id;
    $total = 0;
    foreach ($_FILES['files']['name'] as $i => $orig) {
        $qid = (string) ($_POST['fileq'][$i] ?? '');
        $err = (int) ($_FILES['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        $tmp = (string) ($_FILES['files']['tmp_name'][$i] ?? '');
        $size = (int) ($_FILES['files']['size'][$i] ?? 0);
        $orig = $s(basename(str_replace('\\', '/', (string) $orig)), 120);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if ($err !== UPLOAD_ERR_OK || !is_uploaded_file($tmp) || !isset(FORM_FILE_TYPES[$qid]) || !in_array($ext, FORM_FILE_TYPES[$qid], true) || $size > FORM_FILE_MAX_MB * 1048576 || $total + $size > FORM_FILES_TOTAL_MB * 1048576) { $skipped[] = $orig; continue; }
        /* תמונה צריכה להיות באמת תמונה */
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) && @getimagesize($tmp) === false) { $skipped[] = $orig; continue; }
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        $safe = preg_replace('/[^\p{L}\p{N}._-]+/u', '-', pathinfo($orig, PATHINFO_FILENAME)) ?: 'file';
        $name = sprintf('%02d-%s.%s', count($files) + 1, mb_substr($safe, 0, 60, 'UTF-8'), $ext);
        if (!@move_uploaded_file($tmp, $dir . '/' . $name)) { $skipped[] = $orig; continue; }
        @chmod($dir . '/' . $name, 0600);
        $total += $size;
        $files[] = ['q' => $qid, 'name' => $orig, 'size' => $size, 'path' => 'uploads/' . $id . '/' . $name];
    }
}
if ($skipped) $flags[] = 'קבצים שלא התקבלו: ' . implode(', ', $skipped);

$rec = ['id' => $id, 't' => time(), 'form' => $form, 'lang' => $lang, 'contact' => $contact, 'items' => $items, 'raw' => $raw, 'files' => $files, 'flags' => $flags];
if (@file_put_contents(data_dir() . '/forms.jsonl', json_encode($rec, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX) === false) $out(false, 'store', 500);

/* ---------- הערכת מחיר מיידית (רק בשאלון האפיון): הלקוח רואה אותה מיד, ואני מקבל אותה במייל עם קישור לטיוטת ההצעה ---------- */
$est = null;
if ($form === 'intake') { try { $est = intake_estimate($rec, $lang); } catch (Throwable $e) { $est = null; } }

/* ---------- מייל אליי, מסודר לפי חלקים ---------- */
$title = $form === 'intake' ? 'שאלון אפיון' : 'משוב על העיצובים';
$link = 'https://yygweb.com/admin/form.php?id=' . $id;
$tel = preg_replace('/[^\d+]/', '', $contact['phone']);
$html = '<div dir="rtl" style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#111">'
    . ($flags ? '<p style="background:#FBF0DE;padding:8px 12px;border-radius:8px">לבדיקה: ' . h(implode(' · ', $flags)) . '</p>' : '')
    . '<h2 style="margin:0 0 8px">' . h($title) . ': ' . h($contact['name']) . ($contact['business'] ? ' · ' . h($contact['business']) : '') . '</h2>'
    . '<p style="margin:0 0 14px">' . ($contact['phone'] ? '<a href="tel:' . h($tel) . '">' . h($contact['phone']) . '</a> · ' : '') . ($contact['email'] ? h($contact['email']) . ' · ' : '') . ($contact['quote'] ? 'הצעה ' . h($contact['quote']) . ' · ' : '') . ($lang === 'en' ? 'מהאתר באנגלית · ' : '') . '<a href="' . h($link) . '">לשאלון ולקבצים בדף הניהול</a></p>';
$text = "$title: {$contact['name']}\n{$contact['phone']} {$contact['email']}\n$link\n";
$coming = max(0, min(FORM_FILES_MAX, (int) ($in['files_expected']['count'] ?? 0)));
$comingMb = max(0, round((float) ($in['files_expected']['bytes'] ?? 0) / 1048576, 1));
if ($coming) {
    $html .= '<p style="margin:0 0 14px;padding:8px 12px;background:#EEF2F8;border-radius:8px">הלקוח מעלה עכשיו ' . $coming . ' קבצים (' . h((string) $comingMb) . ' MB). כשההעלאה תסתיים יגיע מייל נפרד עם מה שהגיע.</p>';
    $text .= "הלקוח מעלה עכשיו $coming קבצים ($comingMb MB). יגיע מייל נפרד.\n";
}
if ($est) {
    $qlink = 'https://yygweb.com/admin/quote.php?form=' . rawurlencode($id);
    $html .= '<p style="margin:0 0 14px;padding:10px 14px;background:#EEF6EE;border-radius:8px">הלקוח ראה הערכה של <b>' . h(nis((int) $est['total'])) . '</b>' . ($est['todo'] ? ', ועוד לתמחור: ' . h(implode(', ', $est['todo'])) : '') . '.<br>' . mail_button($qlink, 'לטיוטת ההצעה מהשאלון') . '</p>';
    $text .= 'הערכה שהלקוח ראה: ' . nis((int) $est['total']) . "\nטיוטת הצעה: $qlink\n";
}
if ($files) {
    $html .= '<p style="margin:0 0 14px"><b>קבצים מצורפים (' . count($files) . '):</b> ' . h(implode(', ', array_column($files, 'name'))) . '. <a href="' . h($link) . '">לצפייה ולהורדה</a></p>';
    $text .= 'קבצים: ' . implode(', ', array_column($files, 'name')) . "\n";
}
$sec = null;
foreach ($items as $it) {
    if ($it['s'] !== $sec) { $sec = $it['s']; $html .= '<h3 style="margin:18px 0 6px;font-size:16px;border-bottom:2px solid #151920;padding-bottom:4px">' . h($sec) . '</h3>'; $text .= "\n== $sec ==\n"; }
    $html .= '<p style="margin:0 0 8px"><b>' . h($it['q']) . '</b><br>' . nl2br(h($it['a'])) . '</p>';
    $text .= "{$it['q']}\n{$it['a']}\n";
}
$html .= '</div>';
notify_me(($flags ? '[לבדיקה] ' : '') . $title . ' מולא: ' . $contact['name'], $html, $text);
$out(true, $skipped ? 'skipped' : '', 200, ['id' => $id, 'up' => sign('up' . $id)] + ($est ? ['estimate' => $est] : []));
