<?php
/*
 * YYG: שליחת עדכון לרשימת התפוצה. https://yygweb.com/admin/mail.php
 * נשלח לכל מנוי בנפרד (אף אחד לא רואה את הכתובות של האחרים), עם קישור הסרה אישי.
 * השליחה רצה במנות קטנות מהדפדפן, כדי לא להיתקע במגבלת הזמן של השרת.
 */
declare(strict_types=1);
require __DIR__ . '/_lib.php';
require dirname(__DIR__) . '/account/_acc.php';
adm_require();
$csrf = adm_csrf();
$POST = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

/* גוף המייל מטקסט פשוט: פסקאות לפי שורה ריקה, וכפתור אם יש קישור */
function campaign_html(array $c, ?array $sub): array {
    $en = ($c['lang'] ?? 'he') === 'en';
    $paras = array_filter(array_map('trim', preg_split('/\n\s*\n/', str_replace("\r", '', (string) $c['body']))), 'strlen');
    $body = implode('', array_map(static fn($p) => '<p style="margin:0 0 14px">' . nl2br(h($p)) . '</p>', $paras));
    if (!empty($c['btn_url'])) $body .= mail_button((string) $c['btn_url'], (string) ($c['btn_label'] ?: ($en ? 'Read more' : 'לקריאה')));
    $unsub = $sub ? sub_links($sub)['unsub'] : 'https://yygweb.com/account/list.php';
    $footer = $en ? 'You received this email because you joined the YYG mailing list. <a href="' . h($unsub) . '" style="color:#5A6472">Unsubscribe</a>'
                  : 'קיבלתם את המייל כי הצטרפתם לרשימת התפוצה של YYG. <a href="' . h($unsub) . '" style="color:#5A6472">להסרה מהרשימה</a>';
    $text = (string) $c['body'] . (!empty($c['btn_url']) ? "\n\n" . $c['btn_url'] : '') . "\n\n" . ($en ? 'Unsubscribe: ' : 'להסרה: ') . $unsub;
    return [mail_wrap((string) $c['title'], $body, $footer, $en), $text];
}
function campaign_subject(array $c): string { return (!empty($c['ad']) ? (($c['lang'] ?? 'he') === 'en' ? 'Advertisement: ' : 'פרסומת: ') : '') . $c['subject']; }
function campaign_from_post(): array {
    $s = static fn(string $k, int $max): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max, 'UTF-8');
    $url = $s('btn_url', 300);
    return ['subject' => $s('subject', 150), 'title' => $s('title', 150), 'body' => $s('body', 8000), 'btn_label' => $s('btn_label', 60),
            'btn_url' => preg_match('~^https://~', $url) ? $url : '', 'ad' => !empty($_POST['ad']), 'lang' => ($_POST['lang'] ?? 'he') === 'en' ? 'en' : 'he'];
}
$json = static function (array $d): void { header('Content-Type: application/json; charset=utf-8'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; };

if ($POST) {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) $json(['ok' => false, 'error' => 'csrf']);
    $do = (string) ($_POST['do'] ?? '');
    if ($do === 'test' || $do === 'create') {
        $c = campaign_from_post();
        if ($c['subject'] === '' || $c['body'] === '') $json(['ok' => false, 'error' => 'חסרים נושא או תוכן.']);
        if ($do === 'test') {
            [$html, $text] = campaign_html($c, null);
            $json(['ok' => notify_me('[בדיקה] ' . campaign_subject($c), $html, $text)]);
        }
        $to = [];
        foreach (subs_load() as $email => $sub) if (($sub['status'] ?? '') === 'active' && ($c['lang'] === 'all' || ($sub['lang'] ?? 'he') === $c['lang'])) $to[] = (string) $email;
        if (!$to) $json(['ok' => false, 'error' => 'אין מנויים פעילים בשפה הזו.']);
        $id = date('Ymd-His') . '-' . bin2hex(random_bytes(2));
        data_update('campaigns.json', static function (array &$all) use ($id, $c, $to): void { $all[$id] = $c + ['created' => time(), 'to' => $to, 'sent' => 0, 'failed' => [], 'done' => false]; });
        $json(['ok' => true, 'id' => $id, 'total' => count($to)]);
    }
    if ($do === 'batch') {
        $id = (string) ($_POST['id'] ?? '');
        $all = json_load('campaigns.json');
        if (!isset($all[$id])) $json(['ok' => false, 'error' => 'לא נמצא']);
        $c = $all[$id];
        $subs = subs_load();
        $start = (int) $c['sent'] + count($c['failed']);
        $slice = array_slice($c['to'], $start, 12);
        $sent = 0; $failed = [];
        foreach ($slice as $email) {
            $sub = $subs[$email] ?? null;
            if (!$sub || ($sub['status'] ?? '') !== 'active') { $failed[] = $email; continue; }   // הוסר בינתיים
            [$html, $text] = campaign_html($c, $sub);
            if (mail_send($email, campaign_subject($c), $html, $text)) $sent++; else $failed[] = $email;
            usleep(250000);
        }
        $state = data_update('campaigns.json', static function (array &$all) use ($id, $sent, $failed): array {
            $all[$id]['sent'] += $sent;
            $all[$id]['failed'] = array_merge($all[$id]['failed'], $failed);
            $all[$id]['done'] = $all[$id]['sent'] + count($all[$id]['failed']) >= count($all[$id]['to']);
            if ($all[$id]['done']) $all[$id]['finished'] = time();
            return $all[$id];
        });
        $json(['ok' => true, 'sent' => $state['sent'], 'failed' => count($state['failed']), 'total' => count($state['to']), 'done' => $state['done']]);
    }
    $json(['ok' => false, 'error' => 'פעולה לא מוכרת']);
}

$subs = subs_load();
$active = ['he' => 0, 'en' => 0];
foreach ($subs as $s) if (($s['status'] ?? '') === 'active') $active[($s['lang'] ?? 'he') === 'en' ? 'en' : 'he']++;
$history = array_reverse(json_load('campaigns.json'), true);
ob_start(); ?>
<?= adm_nav() ?>
<main class="wrap">
<p><a class="muted" href="/admin/#users">חזרה לדף הניהול</a></p>
<h1>עדכון לרשימת התפוצה</h1>
<p class="muted">מנויים פעילים: <b><?= $active['he'] ?></b> בעברית, <b><?= $active['en'] ?></b> באנגלית. כל מנוי מקבל מייל נפרד עם קישור הסרה אישי.</p>
<form id="mf" class="card" style="display:grid;gap:10px;margin-top:14px" onsubmit="return false">
 <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
 <label>שפה<select name="lang"><option value="he">עברית (<?= $active['he'] ?>)</option><option value="en">אנגלית (<?= $active['en'] ?>)</option></select></label>
 <label>נושא המייל<input type="text" name="subject" maxlength="150" required placeholder="למשל: מדריך חדש: כמה זמן לוקח לבנות אתר"></label>
 <label style="display:flex;gap:8px;align-items:flex-start"><input type="checkbox" name="ad" value="1" checked style="width:18px;min-height:0;margin-top:4px"><span>להוסיף "פרסומת:" בתחילת הנושא. בדיוור שיווקי החוק בישראל דורש את זה. כדאי להשאיר מסומן, אלא אם בטוחים שהמייל אינו שיווקי.</span></label>
 <label>כותרת בתוך המייל<input type="text" name="title" maxlength="150"></label>
 <label>התוכן <small>שורה ריקה מתחילה פסקה חדשה</small><textarea name="body" rows="9" required></textarea></label>
 <div class="grid"><label>טקסט על הכפתור <small>לא חובה</small><input type="text" name="btn_label" placeholder="לקריאת המדריך"></label><label>קישור לכפתור <small>מתחיל ב־https</small><input type="text" name="btn_url" dir="ltr" placeholder="https://yygweb.com/guides/..."></label></div>
 <div class="acts"><button class="btn" type="button" id="test">שליחת בדיקה אליי</button><button class="btn gold" type="button" id="go">שליחה לכל המנויים</button></div>
 <p id="st" role="status"></p>
</form>
<?php if ($history): ?>
<h2>עדכונים שנשלחו</h2>
<div class="tscroll"><table class="list"><thead><tr><th>תאריך</th><th>נושא</th><th>נשלחו</th><th>נכשלו</th></tr></thead><tbody>
<?php foreach ($history as $c): ?><tr><td><small><?= h(fmt_time((int) $c['created'], 'd/m/Y H:i')) ?></small></td><td><?= h(campaign_subject($c)) ?></td><td><?= (int) $c['sent'] ?> / <?= count($c['to']) ?></td><td><?= count($c['failed']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</main>
<script>
const f = document.getElementById('mf'), st = document.getElementById('st');
const post = async (extra) => { const fd = new FormData(f); for (const [k, v] of Object.entries(extra)) fd.set(k, v); const r = await fetch('/admin/mail.php', { method: 'POST', body: fd }); return r.json(); };
const need = () => { if (!f.subject.value.trim() || !f.body.value.trim()) { st.textContent = 'חסרים נושא או תוכן.'; return false; } return true; };
document.getElementById('test').addEventListener('click', async () => { if (!need()) return; st.textContent = 'שולח בדיקה…'; const j = await post({ do: 'test' }); st.textContent = j.ok ? 'נשלחה בדיקה לתיבה שלך. כדאי לבדוק איך זה נראה בטלפון.' : ('לא נשלח: ' + (j.error || '')); });
document.getElementById('go').addEventListener('click', async () => {
  if (!need()) return;
  const n = f.lang.value === 'en' ? <?= $active['en'] ?> : <?= $active['he'] ?>;
  if (!confirm(`לשלוח ל־${n} מנויים? אי אפשר לבטל אחרי שהשליחה מתחילה.`)) return;
  const b = document.getElementById('go'); b.disabled = true;
  const c = await post({ do: 'create' });
  if (!c.ok) { st.textContent = c.error || 'שגיאה'; b.disabled = false; return; }
  let j = { done: false };
  while (!j.done) { j = await post({ do: 'batch', id: c.id }); if (!j.ok) { st.textContent = 'השליחה נעצרה: ' + (j.error || ''); return; } st.textContent = `נשלחו ${j.sent} מתוך ${j.total}` + (j.failed ? `, ${j.failed} נכשלו` : '') + (j.done ? '. הסתיים.' : '…'); }
});
</script>
<?php adm_page('עדכון לרשימת התפוצה | YYG', (string) ob_get_clean());
