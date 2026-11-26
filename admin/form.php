<?php
/*
 * YYG Web Design: צפייה בשאלון שלקוח מילא (אפיון או משוב על עיצובים). https://yygweb.com/admin/form.php?id=...
 */
declare(strict_types=1);
require __DIR__ . '/_lib.php';
adm_require();
$id = (string) ($_GET['id'] ?? '');
$rec = null;
foreach ((@file(data_dir() . '/forms.jsonl', FILE_IGNORE_NEW_LINES) ?: []) as $l) { $r = json_decode($l, true); if (is_array($r) && ($r['id'] ?? '') === $id) $rec = $r; }
if ($rec) $rec['files'] = form_files($rec);
if (!$rec) { http_response_code(404); adm_page('לא נמצא | YYG Web Design', adm_nav() . '<main class="wrap"><h1>השאלון לא נמצא</h1><p><a href="/admin/#forms">חזרה</a></p></main>'); }
$c = $rec['contact'];
$digits = preg_replace('/\D/', '', (string) $c['phone']);
$wa = preg_replace('/^0/', '972', $digits);
$title = $rec['form'] === 'intake' ? 'שאלון אפיון' : 'משוב על העיצובים';

/* טקסט מסודר להדבקה בצ'אט (למשל ל־Claude), כדי לבנות ממנו הצעת מחיר, תוכנית עבודה ואתר */
$fileNames = ['logo_file' => 'לוגו', 'photo_files' => 'תמונה', 'docs' => 'קובץ'];
$plain = '# ' . $title . ': ' . $c['name'] . ($c['business'] ? ' · ' . $c['business'] : '') . "\n"
    . 'תאריך: ' . fmt_time((int) $rec['t'], 'd/m/Y') . "\n"
    . 'טלפון: ' . ($c['phone'] ?: '-') . ' | מייל: ' . ($c['email'] ?: '-') . ($c['quote'] ? ' | הצעה: ' . $c['quote'] : '') . (($rec['lang'] ?? '') === 'en' ? ' | מולא באנגלית' : '') . "\n";
if (!empty($rec['files'])) {
    $plain .= 'קבצים מצורפים (' . count($rec['files']) . '): ' . implode(', ', array_map(static fn($f) => $f['name'] . ' (' . ($fileNames[$f['q']] ?? 'קובץ') . ')', $rec['files'])) . ". הקבצים עצמם בקובץ ZIP נפרד.\n";
}
$sec = null;
foreach ($rec['items'] as $it) {
    if ($it['s'] !== $sec) { $sec = $it['s']; $plain .= "\n## " . $sec . "\n"; }
    $plain .= '- ' . $it['q'] . "\n  " . str_replace("\n", "\n  ", $it['a']) . "\n";
}
$brief = "אני יהודה מ־YYG Web Design, סטודיו לעיצוב ובניית אתרים לעסקים קטנים ולמוסדות. למטה שאלון " . ($rec['form'] === 'intake' ? 'אפיון' : 'משוב על עיצובים') . " שלקוח מילא.\n"
    . ($rec['form'] === 'intake'
        ? "על בסיסו:\n1. המלץ על מסלול ובנה הצעת מחיר מפורטת לפי המחירון שלי, כולל תוספות שנדרשות לפי התשובות.\n2. בנה תוכנית עבודה: רשימת עמודים, המבנה של כל עמוד, יכולות, תוכן שחסר ומה לבקש מהלקוח, ולוחות זמנים לפי השלבים.\n3. כתוב טיוטת טקסטים לאתר, והצע 3 כיווני עיצוב שונים שמתאימים למה שהלקוח כתב.\n4. כתוב לי אילו שאלות כדאי לשאול את הלקוח בשיחה, איפה התשובות לא ברורות או חסרות.\n"
        : "על בסיסו: סכם מה לשנות בסבב העיצוב הבא, לפי סדר חשיבות, ואיזה כיוון לבחור.\n")
    . "\nהמחירון (מחירי התחלה, בלי תוספת מע״מ; המחיר הסופי בהצעה): דף נחיתה בסיסי 1,490 ₪ (עיצוב אחד, סבב תיקונים אחד, 4 ימי עבודה, 50%/50%); דף נחיתה מ־2,290 ₪ (7 ימי עבודה); אתר תדמית מ־4,900 ₪ (עד 6 עמודים, מסך עדכון, 14 ימי עבודה); אתר תדמית מורחב מ־6,900 ₪ (עד 12 עמודים ומדור תוכן, 20 ימי עבודה); חנות אונליין מ־7,900 ₪ (עד 30 מוצרים, 25 ימי עבודה); מערכת או חנות גדולה בהצעת מחיר; מותג מ־0 עד 100%: עוד 3,900 ₪ (6,190 / 8,800 / 10,800 / 11,800 ₪); יכולות: מסך עדכון לדף נחיתה 1,000 ₪, טופס הרשמה מתקדם 700 ₪, לוח זמנים 700 ₪, רשימת תפוצה ו־3 תבניות מייל 900 ₪, חשבונות משתמשים 2,000 ₪, תרומות באשראי 1,200 ₪, קמפיין עם מד יעד 800 ₪; לוגו בסיסי 590 ₪, לוגו מקצועי 1,500 ₪, מיתוג בסיסי 2,500 ₪; עמוד נוסף 400 ₪; כיוון עיצוב נוסף 400 ₪; סבב תיקונים או שעת עבודה 250 ₪; 10 מוצרים 250 ₪; שפה שנייה 30% (700 / 1,500 / 2,100 / 2,400 ₪); אספקה מהירה 25%+; \"הכול עליי\" 590 ₪ לשנה מהשנה השנייה (בחנות 890 ₪). תשלומים: 30% בהתחלה, 40% אחרי בחירת עיצוב, 30% בסוף. השנה הראשונה של דומיין, אחסון ומייל עסקי כלולה.\n"
    . "תשלום: 10% להתחלה, ואז 3 עיצובים מלאים; 40% אחרי שבוחרים עיצוב; 50% בסיום, לפני העלייה לאוויר. בבסיסי: 50% ו־50%. בכל מסלול מלא: 3 כיווני עיצוב, 3 סבבי תיקונים, 4 חודשי ליווי.\n\n";
ob_start(); ?>
<?= adm_nav() ?>
<main class="wrap">
<p><a class="muted" href="/admin/#forms">חזרה לדף הניהול</a></p>
<h1><?= h($title) ?>: <?= h($c['name']) ?></h1>
<p class="muted"><?= h(fmt_time((int) $rec['t'])) ?><?= $c['business'] ? ' · ' . h($c['business']) : '' ?><?= ($rec['lang'] ?? '') === 'en' ? ' · מהאתר באנגלית' : '' ?><?= $c['quote'] ? ' · הצעה ' . h($c['quote']) : '' ?></p>
<div class="acts">
 <?php if ($digits !== ''): ?><a class="btn gold" href="tel:<?= h(preg_replace('/[^\d+]/', '', (string) $c['phone'])) ?>"><bdi><?= h($c['phone']) ?></bdi></a><?php endif; ?>
 <?php if ($c['email']): ?><a class="btn" href="mailto:<?= h($c['email']) ?>"><bdi><?= h($c['email']) ?></bdi></a><?php endif; ?>
 <?php if ($rec['form'] === 'intake'): ?><a class="btn" href="/admin/quote.php?form=<?= h(rawurlencode($id)) ?>">הצעת מחיר מהשאלון</a><?php endif; ?>
 <button class="btn" type="button" onclick="window.print()">הדפסה</button>
 <form method="post" action="/admin/" style="display:inline" onsubmit="return confirm('למחוק את השאלון ואת הקבצים שצורפו אליו? אי אפשר לשחזר.')"><input type="hidden" name="do" value="form_delete"><input type="hidden" name="csrf" value="<?= h(adm_csrf()) ?>"><input type="hidden" name="id" value="<?= h($id) ?>"><button class="btn" type="submit" style="color:var(--bad)">מחיקת השאלון</button></form>
</div>
<div class="acts copy-acts">
 <button class="btn gold" type="button" data-copy="plain">העתקת השאלון</button>
 <button class="btn gold" type="button" data-copy="brief">העתקה עם הוראות לבניית הצעה ואתר</button>
 <?php if (!empty($rec['files'])): ?><a class="btn" href="/admin/file.php?id=<?= h(rawurlencode($id)) ?>&amp;zip=1">הורדת הקבצים (ZIP)</a><?php endif; ?>
 <a class="btn" id="dl-md" download="<?= h('שאלון-' . $c['name'] . '.md') ?>" href="#">הורדה כקובץ טקסט</a>
 <span class="muted" id="copy-st" role="status"></span>
</div>
<script>
(() => {
  const T = { plain: <?= json_encode($plain, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>, brief: <?= json_encode($brief . $plain, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?> };
  const st = document.getElementById('copy-st');
  document.querySelectorAll('[data-copy]').forEach((b) => b.addEventListener('click', async () => {
    const text = T[b.dataset.copy];
    try { await navigator.clipboard.writeText(text); st.textContent = 'הועתק. אפשר להדביק בצ׳אט.'; }
    catch (e) { const ta = document.createElement('textarea'); ta.value = text; document.body.append(ta); ta.select(); document.execCommand('copy'); ta.remove(); st.textContent = 'הועתק.'; }
  }));
  document.getElementById('dl-md').href = URL.createObjectURL(new Blob([T.brief], { type: 'text/markdown;charset=utf-8' }));
})();
</script>
<?php if (!empty($rec['flags'])): ?><p class="tag warn" style="margin-top:12px">לבדיקה: <?= h(implode(' · ', $rec['flags'])) ?></p><?php endif; ?>
<?php if (!empty($rec['files'])): $names = ['logo_file' => 'לוגו', 'photo_files' => 'תמונות', 'docs' => 'קבצים']; ?>
<section class="card" style="margin-top:14px"><div style="display:flex;justify-content:space-between;align-items:baseline;gap:10px;flex-wrap:wrap"><h3 style="color:#fff;font-size:1.05rem;margin:0">קבצים מצורפים (<?= count($rec['files']) ?>)</h3><a class="btn" href="/admin/file.php?id=<?= h(rawurlencode($id)) ?>&amp;zip=1">הורדת הכול (ZIP)</a></div>
 <div class="files">
 <?php foreach ($rec['files'] as $i => $f): $u = '/admin/file.php?id=' . rawurlencode($id) . '&n=' . $i; $isImg = preg_match('/\.(jpe?g|png|webp|gif)$/i', $f['path']); ?>
  <a class="file" href="<?= h($u) ?>" target="_blank" rel="noopener"><?php if ($isImg): ?><img src="<?= h($u) ?>" alt="" loading="lazy"><?php else: ?><span class="ext"><?= h(strtoupper(pathinfo($f['path'], PATHINFO_EXTENSION))) ?></span><?php endif; ?><small><?= h($names[$f['q']] ?? '') ?> · <?= h($f['name']) ?></small></a>
 <?php endforeach; ?>
 </div>
</section>
<?php endif; ?>
<?php $sec = null; foreach ($rec['items'] as $it): if ($it['s'] !== $sec): if ($sec !== null) echo '</dl></section>'; $sec = $it['s']; ?>
<section class="card" style="margin-top:14px"><h3 style="color:#fff;font-size:1.05rem"><?= h($sec) ?></h3><dl class="kv" style="grid-template-columns:1fr">
<?php endif; ?>
 <dt><?= h($it['q']) ?></dt><dd style="margin-bottom:8px"><?= h($it['a']) ?></dd>
<?php endforeach; if ($sec !== null) echo '</dl></section>'; ?>
<?php if (!$rec['items']): ?><p class="muted">הלקוח שלח רק פרטים, בלי תשובות.</p><?php endif; ?>
</main>
<?php adm_page($title . ': ' . $c['name'] . ' | YYG Web Design', (string) ob_get_clean(), '.copy-acts{margin-top:10px;align-items:center}.files{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-top:12px}.file{display:grid;gap:6px;padding:8px;border:1px solid var(--line);border-radius:10px;background:var(--bg2);text-decoration:none;overflow:hidden}.file img{width:100%;height:110px;object-fit:cover;border-radius:6px;background:#fff}.file .ext{display:grid;place-items:center;height:110px;border-radius:6px;background:var(--panel);font-weight:800;color:var(--muted)}.file small{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}@media print{.top,.acts{display:none}body{background:#fff;color:#000}.card{border:1px solid #ccc;background:#fff}.kv dt{color:#555}}');
