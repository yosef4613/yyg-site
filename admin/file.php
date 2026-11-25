<?php
/*
 * YYG: הורדת קובץ שלקוח צירף לשאלון. ?id=<מזהה השאלון>&n=<מספר הקובץ>, או &zip=1 לכל הקבצים יחד.
 * רק למנהל. תמונות נפתחות בדפדפן, כל השאר יורד כקובץ (כולל SVG, כדי שקוד בתוכו לא ירוץ).
 */
declare(strict_types=1);
require __DIR__ . '/_lib.php';
adm_require();
$id = (string) ($_GET['id'] ?? '');
$rec = null;
foreach ((@file(data_dir() . '/forms.jsonl', FILE_IGNORE_NEW_LINES) ?: []) as $l) { $r = json_decode($l, true); if (is_array($r) && ($r['id'] ?? '') === $id) $rec = $r; }
if (!$rec || empty($rec['files'])) { http_response_code(404); exit('לא נמצא'); }
$base = realpath(data_dir() . '/uploads');
$abs = static function (array $f) use ($base): ?string {
    $p = realpath(data_dir() . '/' . $f['path']);
    return ($p && $base && strpos($p, $base . DIRECTORY_SEPARATOR) === 0 && is_file($p)) ? $p : null;
};
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'unsafe-inline'");
if (isset($_GET['zip'])) {
    $tmp = tempnam(sys_get_temp_dir(), 'yygz');
    $z = new ZipArchive();
    $z->open($tmp, ZipArchive::OVERWRITE);
    foreach ($rec['files'] as $i => $f) if ($p = $abs($f)) $z->addFile($p, sprintf('%02d-%s', $i + 1, $f['name']));
    $z->close();
    header('Content-Type: application/zip');
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode('קבצים-' . ($rec['contact']['name'] ?? 'לקוח') . '.zip'));
    header('Content-Length: ' . filesize($tmp));
    readfile($tmp); @unlink($tmp); exit;
}
$n = (int) ($_GET['n'] ?? -1);
$f = $rec['files'][$n] ?? null;
$p = $f ? $abs($f) : null;
if (!$p) { http_response_code(404); exit('לא נמצא'); }
$ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
$images = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
$inline = isset($images[$ext]) && !isset($_GET['dl']);
header('Content-Type: ' . ($images[$ext] ?? 'application/octet-stream'));
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename*=UTF-8''" . rawurlencode($f['name']));
header('Content-Length: ' . filesize($p));
readfile($p);
