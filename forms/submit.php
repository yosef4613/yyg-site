<?php
/*
 * YYG: קבלת שאלונים (אפיון ומשוב על עיצובים). POST עם JSON מ־/assets/forms.js.
 * כל שאלון נשמר מחוץ לתיקיית האתר (forms.jsonl) ונשלח אליי במייל. רואים אותם גם בדף הניהול.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/admin/_lib.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$out = static function (bool $ok, string $msg = '', int $code = 200): void { http_response_code($code); echo json_encode(['success' => $ok ? 'true' : 'false', 'message' => $msg], JSON_UNESCAPED_UNICODE); exit; };

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') $out(false, 'method', 405);
$origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? ''));
$host = (string) parse_url($origin, PHP_URL_HOST);
if ($host !== '' && !preg_match('/(^|\.)yygweb\.com$/i', $host) && $host !== 'localhost') $out(false, 'origin', 403);

$in = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in)) $out(false, 'bad', 400);
if (!empty($in['website'])) $out(true);                       // מלכודת לרובוטים
if ((int) ($in['t'] ?? 0) < 10000) $out(true);                // שאלון אמיתי לא נשלח תוך פחות מ־10 שניות מהרגע שהתחילו למלא
if (!rate_ok('forms-' . client_ip(), 6, 3600)) $out(false, 'rate', 429);

$s = static fn($v, int $max): string => mb_substr(trim(str_replace("\0", '', is_scalar($v) ? (string) $v : '')), 0, $max, 'UTF-8');
$form = in_array($in['form'] ?? '', ['intake', 'feedback'], true) ? $in['form'] : '';
if ($form === '') $out(false, 'form', 400);
$c = (array) ($in['contact'] ?? []);
$contact = ['name' => $s($c['name'] ?? '', 120), 'phone' => $s($c['phone'] ?? '', 40), 'email' => $s($c['email'] ?? '', 160), 'business' => $s($c['business'] ?? '', 160), 'quote' => $s($c['quote'] ?? '', 40)];
if (mb_strlen($contact['name'], 'UTF-8') < 2) $out(false, 'name', 422);
$phoneOk = strlen(preg_replace('/\D/', '', $contact['phone'])) >= 9;
$mailOk = (bool) filter_var($contact['email'], FILTER_VALIDATE_EMAIL);
if (!$phoneOk && !$mailOk) $out(false, 'contact', 422);
if (!$mailOk) $contact['email'] = '';
$items = [];
foreach (array_slice((array) ($in['items'] ?? []), 0, 160) as $it) {
    if (!is_array($it)) continue;
    $a = $s($it['a'] ?? '', 4000);
    if ($a === '') continue;
    $items[] = ['s' => $s($it['s'] ?? '', 120), 'q' => $s($it['q'] ?? '', 300), 'a' => $a];
}
$lang = ($in['lang'] ?? '') === 'en' ? 'en' : 'he';
$rec = ['id' => bin2hex(random_bytes(6)), 't' => time(), 'form' => $form, 'lang' => $lang, 'contact' => $contact, 'items' => $items];
if (@file_put_contents(data_dir() . '/forms.jsonl', json_encode($rec, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX) === false) $out(false, 'store', 500);

/* מייל אליי, מסודר לפי חלקים */
$title = $form === 'intake' ? 'שאלון אפיון' : 'משוב על העיצובים';
$link = 'https://yygweb.com/admin/form.php?id=' . $rec['id'];
$tel = preg_replace('/[^\d+]/', '', $contact['phone']);
$html = '<div dir="rtl" style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#111">'
    . '<h2 style="margin:0 0 8px">' . h($title) . ': ' . h($contact['name']) . ($contact['business'] ? ' · ' . h($contact['business']) : '') . '</h2>'
    . '<p style="margin:0 0 14px">' . ($contact['phone'] ? '<a href="tel:' . h($tel) . '">' . h($contact['phone']) . '</a> · ' : '') . ($contact['email'] ? h($contact['email']) . ' · ' : '') . ($contact['quote'] ? 'הצעה ' . h($contact['quote']) . ' · ' : '') . ($lang === 'en' ? 'מהאתר באנגלית · ' : '') . '<a href="' . h($link) . '">לשאלון בדף הניהול</a></p>';
$text = "$title: {$contact['name']}\n{$contact['phone']} {$contact['email']}\n$link\n";
$sec = null;
foreach ($items as $it) {
    if ($it['s'] !== $sec) { $sec = $it['s']; $html .= '<h3 style="margin:18px 0 6px;font-size:16px;border-bottom:2px solid #151920;padding-bottom:4px">' . h($sec) . '</h3>'; $text .= "\n== $sec ==\n"; }
    $html .= '<p style="margin:0 0 8px"><b>' . h($it['q']) . '</b><br>' . nl2br(h($it['a'])) . '</p>';
    $text .= "{$it['q']}\n{$it['a']}\n";
}
$html .= '</div>';
notify_me($title . ' מולא: ' . $contact['name'], $html, $text);
$out(true);
