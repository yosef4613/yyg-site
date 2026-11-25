<?php
/*
 * YYG: שליחת עדכון לרשימת התפוצה. https://yygweb.com/admin/mail.php
 * שני סוגים: "פוסט חדש מהאתר" (בוחרים מדריך, והמייל נבנה ממנו: כותרת, תקציר, זמן קריאה, עוד מדריכים)
 * ו"מייל חופשי". יש תצוגה מקדימה לפני השליחה.
 * נשלח לכל מנוי בנפרד (אף אחד לא רואה את הכתובות של האחרים), עם קישור הסרה אישי.
 * השליחה רצה במנות קטנות מהדפדפן, כדי לא להיתקע במגבלת הזמן של השרת.
 */
declare(strict_types=1);
require __DIR__ . '/_lib.php';
require dirname(__DIR__) . '/account/_acc.php';
adm_require();
$csrf = adm_csrf();
$POST = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

/* המדריכים באתר, ישר מהעמודים עצמם: כותרת, תקציר, תאריך וזמן קריאה */
function site_posts(string $lang): array {
    static $cache = [];
    if (isset($cache[$lang])) return $cache[$lang];
    $root = dirname(__DIR__);
    $out = [];
    foreach (glob($root . ($lang === 'en' ? '/en/guides' : '/guides') . '/*.html') ?: [] as $f) {
        $slug = basename($f, '.html');
        if ($slug === 'index') continue;
        $html = (string) file_get_contents($f);
        $get = static fn(string $re): string => preg_match($re, $html, $m) ? trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
        $title = $get('~<h1[^>]*>(.*?)</h1>~s');
        if ($title === '') continue;
        $main = preg_match('~<main[^>]*>(.*?)</main>~s', $html, $m) ? $m[1] : $html;
        $words = count(preg_split('/\s+/u', trim(strip_tags(preg_replace('~<(script|style)[^>]*>.*?</\1>~s', '', $main))), -1, PREG_SPLIT_NO_EMPTY));
        $out[$slug] = [
            'slug' => $slug, 'title' => $title,
            'lead' => $get('~<p class="g-lead">(.*?)</p>~s') ?: $get('~<meta name="description" content="([^"]*)"~'),
            'desc' => $get('~<meta name="description" content="([^"]*)"~'),
            'date' => $get('~"datePublished":"([^"]+)"~'),
            'min' => max(2, (int) round($words / 200)),
            'url' => 'https://yygweb.com/' . ($lang === 'en' ? 'en/' : '') . 'guides/' . $slug . '.html',
        ];
    }
    uasort($out, static fn($a, $b) => [$b['date'], $a['title']] <=> [$a['date'], $b['title']]);
    return $cache[$lang] = $out;
}
/* קישור מהמייל: מסומן במקור "newsletter", כדי שבדף הניהול יופיע כמה הגיעו מהמייל */
function nl_link(string $url, string $campaign = ''): string {
    return $url . (strpos($url, '?') === false ? '?' : '&') . 'src=newsletter' . ($campaign !== '' ? '-' . preg_replace('/[^a-z0-9-]/', '', $campaign) : '');
}

/* גוף המייל */
function campaign_html(array $c, ?array $sub): array {
    $en = ($c['lang'] ?? 'he') === 'en';
    $paras = static fn(string $t): array => array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', str_replace("\r", '', $t))), 'strlen'));
    $pHtml = static fn(array $ps): string => implode('', array_map(static fn($p) => '<p style="margin:0 0 14px">' . nl2br(h($p)) . '</p>', $ps));
    $unsub = $sub ? sub_links($sub)['unsub'] : 'https://yygweb.com/account/list.php';
    $footer = $en ? 'You received this email because you joined the YYG mailing list. <a href="' . h($unsub) . '" style="color:#5A6472">Unsubscribe</a>'
                  : 'קיבלתם את המייל כי הצטרפתם לרשימת התפוצה של YYG. <a href="' . h($unsub) . '" style="color:#5A6472">להסרה מהרשימה</a>';
    $sign = $en ? '<p style="margin:22px 0 0">All the best,<br><b>Yehuda Gordon</b> · YYG</p>' : '<p style="margin:22px 0 0">בברכה,<br><b>יהודה גורדון</b> · YYG</p>';

    if (($c['type'] ?? '') === 'post') {
        $posts = site_posts($en ? 'en' : 'he');
        $p = $posts[$c['post'] ?? ''] ?? null;
        if (!$p) return ['', ''];
        $url = nl_link($p['url'], $p['slug']);
        $intro = $paras((string) ($c['intro'] ?? ''));
        $card = '<div style="margin:18px 0 8px;padding:18px 20px;border:1px solid #DCE0E6;border-radius:12px;background:#FAFAF7">'
            . '<div style="font-size:13px;font-weight:700;color:#8A6A12">' . ($en ? 'New guide' : 'מדריך חדש') . ' · ' . ($en ? $p['min'] . ' min read' : 'כ־' . $p['min'] . ' דקות קריאה') . '</div>'
            . '<div style="margin:6px 0 8px;font-size:21px;font-weight:700;line-height:1.35"><a href="' . h($url) . '" style="color:#151920;text-decoration:none">' . h($p['title']) . '</a></div>'
            . '<p style="margin:0;color:#3D4552">' . h($p['lead']) . '</p>'
            . mail_button($url, $en ? 'Read the guide' : 'לקריאת המדריך') . '</div>';
        $more = array_slice(array_values(array_filter($posts, static fn($x) => $x['slug'] !== $p['slug'])), 0, 3);
        $moreHtml = $more ? '<p style="margin:22px 0 8px;font-weight:700">' . ($en ? 'More guides on the site' : 'עוד מדריכים באתר') . '</p><ul style="margin:0;padding-inline-start:20px">'
            . implode('', array_map(static fn($x) => '<li style="margin:0 0 6px"><a href="' . h(nl_link($x['url'], $p['slug'])) . '" style="color:#1a56db">' . h($x['title']) . '</a></li>', $more)) . '</ul>' : '';
        $html = mail_wrap((string) ($c['title'] ?: ($en ? 'A new guide on the site' : 'מדריך חדש באתר')), $pHtml($intro) . $card . $moreHtml . $sign, $footer, $en);
        $text = implode("\n\n", $intro) . "\n\n" . $p['title'] . "\n" . $p['lead'] . "\n" . $url
            . ($more ? "\n\n" . ($en ? 'More guides:' : 'עוד מדריכים:') . "\n" . implode("\n", array_map(static fn($x) => '- ' . $x['title'] . ': ' . nl_link($x['url'], $p['slug']), $more)) : '')
            . "\n\n" . ($en ? 'Yehuda Gordon, YYG' : 'יהודה גורדון, YYG') . "\n\n" . ($en ? 'Unsubscribe: ' : 'להסרה: ') . $unsub;
        return [$html, $text];
    }

    $body = $pHtml($paras((string) $c['body']));
    if (!empty($c['btn_url'])) $body .= mail_button((string) $c['btn_url'], (string) ($c['btn_label'] ?: ($en ? 'Read more' : 'לקריאה')));
    $text = (string) $c['body'] . (!empty($c['btn_url']) ? "\n\n" . $c['btn_url'] : '') . "\n\n" . ($en ? 'Unsubscribe: ' : 'להסרה: ') . $unsub;
    return [mail_wrap((string) $c['title'], $body . $sign, $footer, $en), $text];
}
function campaign_subject(array $c): string { return (!empty($c['ad']) ? (($c['lang'] ?? 'he') === 'en' ? 'Advertisement: ' : 'פרסומת: ') : '') . $c['subject']; }
function campaign_from_post(): array {
    $s = static fn(string $k, int $max): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max, 'UTF-8');
    $url = $s('btn_url', 300);
    $type = ($_POST['type'] ?? '') === 'post' ? 'post' : 'free';
    $lang = ($_POST['lang'] ?? 'he') === 'en' ? 'en' : 'he';
    return ['type' => $type, 'post' => preg_replace('/[^a-z0-9-]/', '', $s('post', 80)), 'intro' => $s('intro', 3000),
            'subject' => $s('subject', 150), 'title' => $s('title', 150), 'body' => $type === 'post' ? '' : $s('body', 8000), 'btn_label' => $s('btn_label', 60),
            'btn_url' => preg_match('~^https://~', $url) ? $url : '', 'ad' => !empty($_POST['ad']), 'lang' => $lang];
}
function campaign_error(array $c): string {
    if ($c['subject'] === '') return 'חסר נושא למייל.';
    if ($c['type'] === 'post') return isset(site_posts($c['lang'])[$c['post']]) ? '' : 'צריך לבחור מדריך.';
    return $c['body'] === '' ? 'חסר תוכן.' : '';
}
$json = static function (array $d): void { header('Content-Type: application/json; charset=utf-8'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; };

if ($POST) {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) $json(['ok' => false, 'error' => 'csrf']);
    $do = (string) ($_POST['do'] ?? '');
    if ($do === 'preview') {
        $c = campaign_from_post();
        [$html] = campaign_html($c, null);
        header('Content-Type: text/html; charset=utf-8');
        echo $html ?: '<p style="font-family:Arial;padding:20px;color:#5A6472" dir="rtl">בחרו מדריך או כתבו תוכן, והתצוגה תופיע כאן.</p>';
        exit;
    }
    if ($do === 'test' || $do === 'create') {
        $c = campaign_from_post();
        if ($err = campaign_error($c)) $json(['ok' => false, 'error' => $err]);
        if ($do === 'test') {
            [$html, $text] = campaign_html($c, null);
            $json(['ok' => notify_me('[בדיקה] ' . campaign_subject($c), $html, $text)]);
        }
        $to = [];
        foreach (subs_load() as $email => $sub) if (($sub['status'] ?? '') === 'active' && ($sub['lang'] ?? 'he') === $c['lang']) $to[] = (string) $email;
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
            if ($html !== '' && mail_send($email, campaign_subject($c), $html, $text)) $sent++; else $failed[] = $email;
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
$POSTS = ['he' => array_values(site_posts('he')), 'en' => array_values(site_posts('en'))];
$sentPosts = [];
foreach ($history as $c) if (($c['type'] ?? '') === 'post' && !empty($c['done'])) $sentPosts[($c['lang'] ?? 'he') . ':' . $c['post']] = (int) $c['created'];
ob_start(); ?>
<?= adm_nav() ?>
<main class="wrap">
<p><a class="muted" href="/admin/#users">חזרה לדף הניהול</a></p>
<h1>עדכון לרשימת התפוצה</h1>
<p class="muted">מנויים פעילים: <b><?= $active['he'] ?></b> בעברית, <b><?= $active['en'] ?></b> באנגלית. כל מנוי מקבל מייל נפרד עם קישור הסרה אישי. הקישורים במייל מסומנים, ובדף הניהול תראו כמה נכנסו מהמייל.</p>
<div class="mgrid">
<form id="mf" class="card" style="display:grid;gap:10px" onsubmit="return false">
 <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
 <div class="seg" role="radiogroup" aria-label="סוג המייל">
  <label><input type="radio" name="type" value="post" checked> פוסט חדש מהאתר</label>
  <label><input type="radio" name="type" value="free"> מייל חופשי</label>
 </div>
 <label>שפה<select name="lang"><option value="he">עברית (<?= $active['he'] ?> מנויים)</option><option value="en">אנגלית (<?= $active['en'] ?> מנויים)</option></select></label>
 <div data-for="post" style="display:grid;gap:10px">
  <label>איזה מדריך לשלוח?<select name="post" id="post"></select></label>
  <label>כמה מילים ממך, לפני המדריך <small>שורה ריקה מתחילה פסקה חדשה</small><textarea name="intro" id="intro" rows="4"></textarea></label>
 </div>
 <label>נושא המייל<input type="text" name="subject" id="subject" maxlength="150" required></label>
 <label style="display:flex;gap:8px;align-items:flex-start"><input type="checkbox" name="ad" value="1" checked style="width:18px;min-height:0;margin-top:4px"><span>להוסיף "פרסומת:" בתחילת הנושא. בדיוור שיווקי החוק בישראל דורש את זה. כדאי להשאיר מסומן, אלא אם בטוחים שהמייל אינו שיווקי.</span></label>
 <label>כותרת בתוך המייל<input type="text" name="title" id="title" maxlength="150"></label>
 <div data-for="free" style="display:grid;gap:10px" hidden>
  <label>התוכן <small>שורה ריקה מתחילה פסקה חדשה</small><textarea name="body" rows="9"></textarea></label>
  <div class="grid"><label>טקסט על הכפתור <small>לא חובה</small><input type="text" name="btn_label" placeholder="לקריאה"></label><label>קישור לכפתור <small>מתחיל ב־https</small><input type="text" name="btn_url" dir="ltr" placeholder="https://yygweb.com/..."></label></div>
 </div>
 <div class="acts"><button class="btn" type="button" id="test">שליחת בדיקה אליי</button><button class="btn gold" type="button" id="go">שליחה לכל המנויים</button></div>
 <p id="st" role="status"></p>
</form>
<section class="card prev"><h2 style="margin:0 0 8px;font-size:1rem">תצוגה מקדימה</h2><iframe id="pv" title="תצוגה מקדימה של המייל" sandbox=""></iframe></section>
</div>
<?php if ($history): ?>
<h2>עדכונים שנשלחו</h2>
<div class="tscroll"><table class="list"><thead><tr><th>תאריך</th><th>סוג</th><th>נושא</th><th>נשלחו</th><th>נכשלו</th></tr></thead><tbody>
<?php foreach ($history as $c): ?><tr><td><small><?= h(fmt_time((int) $c['created'], 'd/m/Y H:i')) ?></small></td><td><small><?= ($c['type'] ?? '') === 'post' ? 'פוסט' : 'חופשי' ?></small></td><td><?= h(campaign_subject($c)) ?></td><td><?= (int) $c['sent'] ?> / <?= count($c['to']) ?></td><td><?= count($c['failed']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</main>
<script>
const POSTS = <?= json_encode($POSTS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
const SENT = <?= json_encode($sentPosts) ?>;
const f = document.getElementById('mf'), st = document.getElementById('st'), pv = document.getElementById('pv');
const INTRO = { he: 'שלום,\n\nכתבתי מדריך חדש באתר, ונראה לי שהוא יעניין אתכם.', en: 'Hi,\n\nI wrote a new guide on the site, and I think you\'ll find it useful.' };
let touched = { subject: false, title: false, intro: false };
['subject', 'title', 'intro'].forEach((k) => f[k].addEventListener('input', () => { touched[k] = true; }));
const type = () => f.querySelector('[name=type]:checked').value;
const lang = () => f.lang.value;
const fillPosts = () => {
  const cur = f.post.value;
  f.post.innerHTML = '';
  POSTS[lang()].forEach((p) => {
    const sent = SENT[lang() + ':' + p.slug];
    f.post.add(new Option(p.title + (sent ? ' (כבר נשלח)' : ''), p.slug));
  });
  if ([...f.post.options].some((o) => o.value === cur)) f.post.value = cur;
};
const autoFill = () => {
  if (type() !== 'post') return;
  const p = POSTS[lang()].find((x) => x.slug === f.post.value);
  if (!p) return;
  if (!touched.subject) f.subject.value = (lang() === 'en' ? 'New guide: ' : 'מדריך חדש: ') + p.title;
  if (!touched.title) f.title.value = lang() === 'en' ? 'A new guide on the site' : 'מדריך חדש באתר';
  if (!touched.intro) f.intro.value = INTRO[lang()];
};
const showType = () => {
  f.querySelectorAll('[data-for]').forEach((el) => { el.hidden = el.dataset.for !== type(); });
  if (type() === 'post') autoFill();
  else { if (!touched.subject) f.subject.value = ''; if (!touched.title) f.title.value = ''; }
};
const post = async (extra) => { const fd = new FormData(f); for (const [k, v] of Object.entries(extra)) fd.set(k, v); return fetch('/admin/mail.php', { method: 'POST', body: fd }); };
let pt;
const preview = () => { clearTimeout(pt); pt = setTimeout(async () => { pv.srcdoc = await (await post({ do: 'preview' })).text(); }, 250); };
f.addEventListener('input', preview);
f.addEventListener('change', (e) => {
  if (e.target.name === 'lang') { touched = { subject: false, title: false, intro: false }; fillPosts(); autoFill(); }
  if (e.target.name === 'post') autoFill();
  if (e.target.name === 'type') showType();
  preview();
});
fillPosts(); showType(); preview();
const need = () => {
  if (!f.subject.value.trim()) { st.textContent = 'חסר נושא למייל.'; return false; }
  if (type() === 'free' && !f.body.value.trim()) { st.textContent = 'חסר תוכן.'; return false; }
  return true;
};
document.getElementById('test').addEventListener('click', async () => { if (!need()) return; st.textContent = 'שולח בדיקה…'; const j = await (await post({ do: 'test' })).json(); st.textContent = j.ok ? 'נשלחה בדיקה לתיבה שלך. כדאי לבדוק איך זה נראה בטלפון.' : ('לא נשלח: ' + (j.error || '')); });
document.getElementById('go').addEventListener('click', async () => {
  if (!need()) return;
  const n = lang() === 'en' ? <?= $active['en'] ?> : <?= $active['he'] ?>;
  if (type() === 'post' && SENT[lang() + ':' + f.post.value] && !confirm('המדריך הזה כבר נשלח לרשימה. לשלוח שוב?')) return;
  if (!confirm(`לשלוח ל־${n} מנויים? אי אפשר לבטל אחרי שהשליחה מתחילה.`)) return;
  const b = document.getElementById('go'); b.disabled = true;
  const c = await (await post({ do: 'create' })).json();
  if (!c.ok) { st.textContent = c.error || 'שגיאה'; b.disabled = false; return; }
  let j = { done: false };
  while (!j.done) { j = await (await post({ do: 'batch', id: c.id })).json(); if (!j.ok) { st.textContent = 'השליחה נעצרה: ' + (j.error || ''); return; } st.textContent = `נשלחו ${j.sent} מתוך ${j.total}` + (j.failed ? `, ${j.failed} נכשלו` : '') + (j.done ? '. הסתיים.' : '…'); }
});
</script>
<?php adm_page('עדכון לרשימת התפוצה | YYG', (string) ob_get_clean(), '[hidden]{display:none!important}.mgrid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;align-items:start;margin-top:14px}.prev{position:sticky;top:12px}.prev iframe{width:100%;height:760px;border:1px solid var(--line);border-radius:10px;background:#F4F5F7}.seg{display:flex;gap:8px;flex-wrap:wrap}.seg label{display:flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid var(--line);border-radius:10px;cursor:pointer}.seg input{width:auto;min-height:0}@media (max-width:900px){.mgrid{grid-template-columns:1fr}.prev{position:static}.prev iframe{height:600px}}'); ?>
