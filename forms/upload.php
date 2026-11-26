<?php
/*
 * YYG Web Design: העלאת קבצים לשאלון שכבר נשלח. כל קובץ בבקשה נפרדת, כדי שכמות הקבצים וגודלם לא יחסמו את השליחה
 * (השרת מקבל עד 20 קבצים בבקשה אחת, ובקשה ענקית מטלפון בסלולרי נתקעת).
 * forms/submit.php מחזיר id ואסימון (up). אחר כך:
 *   POST multipart: id, up, q (logo_file|photo_files|docs), file   → שומר קובץ אחד ב־uploads/<id>/, ורושם אותו ב־manifest.jsonl
 *   POST: id, up, done=1, failed (JSON של שמות קבצים שלא עלו)     → שולח לי מייל מסכם אחד
 * המגבלות: FORM_FILE_* ב־admin/_lib.php.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/admin/_lib.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$out = static function (bool $ok, string $msg = '', int $code = 200, array $extra = []): void { http_response_code($code); echo json_encode(['success' => $ok ? 'true' : 'false', 'message' => $msg] + $extra, JSON_UNESCAPED_UNICODE); exit; };

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') $out(false, 'method', 405);
$origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? ''));
$host = (string) parse_url($origin, PHP_URL_HOST);
if ($host !== '' && !preg_match('/(^|\.)yygweb\.com$/i', $host) && $host !== 'localhost') $out(false, 'origin', 403);
/* בקשה גדולה מהמותר בשרת מגיעה בלי שדות בכלל */
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) $out(false, 'too_big', 413);
if (!rate_ok('up-' . client_ip(), 240, 3600)) $out(false, 'rate', 429);

$id = (string) ($_POST['id'] ?? '');
if (!preg_match('/^[a-f0-9]{12}$/', $id) || !hash_equals(sign('up' . $id), (string) ($_POST['up'] ?? ''))) $out(false, 'token', 403);
/* השאלון חייב להיות קיים, ומהיממה האחרונה */
$rec = null;
foreach (array_reverse(@file(data_dir() . '/forms.jsonl', FILE_IGNORE_NEW_LINES) ?: []) as $l) { $r = json_decode($l, true); if (is_array($r) && ($r['id'] ?? '') === $id) { $rec = $r; break; } }
if (!$rec || time() - (int) $rec['t'] > 86400) $out(false, 'expired', 410);

$dir = data_dir() . '/uploads/' . $id;
$manifest = $dir . '/manifest.jsonl';
$have = [];
foreach ((@file($manifest, FILE_IGNORE_NEW_LINES) ?: []) as $l) { $f = json_decode($l, true); if (is_array($f)) $have[] = $f; }
$count = count($have) + count((array) ($rec['files'] ?? []));
$total = array_sum(array_map(static fn($f) => (int) ($f['size'] ?? 0), $have));

/* ---------- סיום: מייל מסכם אחד אליי ---------- */
if (!empty($_POST['done'])) {
    if (is_file($dir . '/done')) $out(true, 'already');
    $failed = array_slice(array_values(array_filter(array_map(static fn($x) => mb_substr(trim((string) $x), 0, 120, 'UTF-8'), (array) json_decode((string) ($_POST['failed'] ?? '[]'), true)))), 0, 80);
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    @file_put_contents($dir . '/done', (string) time());
    $c = (array) $rec['contact'];
    $link = 'https://yygweb.com/admin/form.php?id=' . $id;
    $names = ['logo_file' => 'לוגו', 'photo_files' => 'תמונות', 'docs' => 'מסמכים'];
    $byQ = [];
    foreach ($have as $f) $byQ[$names[$f['q']] ?? 'קבצים'][] = $f['name'];
    $html = '<div dir="rtl" style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#111">'
        . '<h2 style="margin:0 0 8px">הקבצים לשאלון של ' . h((string) ($c['name'] ?? '')) . (!empty($c['business']) ? ' · ' . h((string) $c['business']) : '') . '</h2>'
        . '<p style="margin:0 0 10px">הגיעו <b>' . count($have) . ' קבצים</b> (' . h((string) round($total / 1048576, 1)) . ' MB). ' . mail_button($link, 'לצפייה ולהורדה') . '</p>';
    foreach ($byQ as $k => $list) $html .= '<p style="margin:0 0 6px"><b>' . h($k) . ' (' . count($list) . '):</b> ' . h(implode(', ', $list)) . '</p>';
    if ($failed) $html .= '<p style="margin:12px 0 0;padding:8px 12px;background:#FBF0DE;border-radius:8px"><b>לא הגיעו (' . count($failed) . '):</b> ' . h(implode(', ', $failed)) . '. הלקוח קיבל הנחיה לשלוח אותם במייל או בקישור לדרייב.</p>';
    $html .= '</div>';
    $text = 'הקבצים לשאלון של ' . ($c['name'] ?? '') . ': הגיעו ' . count($have) . ' קבצים' . ($failed ? '. לא הגיעו: ' . implode(', ', $failed) : '') . "\n$link";
    notify_me(($failed ? '[חסרים קבצים] ' : '') . 'קבצים לשאלון: ' . ($c['name'] ?? '') . ' (' . count($have) . ')', $html, $text);
    $out(true);
}

/* ---------- קובץ אחד ---------- */
$q = (string) ($_POST['q'] ?? '');
$f = $_FILES['file'] ?? null;
if (!isset(FORM_FILE_TYPES[$q]) || !is_array($f)) $out(false, 'bad', 400);
$err = (int) ($f['error'] ?? UPLOAD_ERR_NO_FILE);
if (in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) $out(false, 'too_big', 413);
if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) $f['tmp_name'])) $out(false, 'upload', 400);
$size = (int) $f['size'];
$orig = mb_substr(basename(str_replace('\\', '/', (string) $f['name'])), 0, 120, 'UTF-8');
$ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
if (!in_array($ext, FORM_FILE_TYPES[$q], true)) $out(false, 'type', 415);
if ($size > FORM_FILE_MAX_MB * 1048576) $out(false, 'too_big', 413);
if ($count >= FORM_FILES_MAX || $total + $size > FORM_FILES_TOTAL_MB * 1048576) $out(false, 'full', 413);
if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) && @getimagesize((string) $f['tmp_name']) === false) $out(false, 'type', 415);
if (!is_dir($dir)) @mkdir($dir, 0700, true);
$safe = preg_replace('/[^\p{L}\p{N}._-]+/u', '-', pathinfo($orig, PATHINFO_FILENAME)) ?: 'file';
$name = sprintf('%02d-%s.%s', $count + 1, mb_substr($safe, 0, 60, 'UTF-8'), $ext);
if (!@move_uploaded_file((string) $f['tmp_name'], $dir . '/' . $name)) $out(false, 'store', 500);
@chmod($dir . '/' . $name, 0600);
@file_put_contents($manifest, json_encode(['q' => $q, 'name' => $orig, 'size' => $size, 'path' => 'uploads/' . $id . '/' . $name, 't' => time()], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
$out(true, '', 200, ['n' => $count + 1]);
