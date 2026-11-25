<?php
/*
 * YYG: צפייה בשאלון שלקוח מילא (אפיון או משוב על עיצובים). https://yygweb.com/admin/form.php?id=...
 */
declare(strict_types=1);
require __DIR__ . '/_lib.php';
adm_require();
$id = (string) ($_GET['id'] ?? '');
$rec = null;
foreach ((@file(data_dir() . '/forms.jsonl', FILE_IGNORE_NEW_LINES) ?: []) as $l) { $r = json_decode($l, true); if (is_array($r) && ($r['id'] ?? '') === $id) $rec = $r; }
if (!$rec) { http_response_code(404); adm_page('לא נמצא | YYG', adm_nav() . '<main class="wrap"><h1>השאלון לא נמצא</h1><p><a href="/admin/#forms">חזרה</a></p></main>'); }
$c = $rec['contact'];
$digits = preg_replace('/\D/', '', (string) $c['phone']);
$wa = preg_replace('/^0/', '972', $digits);
$title = $rec['form'] === 'intake' ? 'שאלון אפיון' : 'משוב על העיצובים';
ob_start(); ?>
<?= adm_nav() ?>
<main class="wrap">
<p><a class="muted" href="/admin/#forms">חזרה לדף הניהול</a></p>
<h1><?= h($title) ?>: <?= h($c['name']) ?></h1>
<p class="muted"><?= h(fmt_time((int) $rec['t'])) ?><?= $c['business'] ? ' · ' . h($c['business']) : '' ?><?= ($rec['lang'] ?? '') === 'en' ? ' · מהאתר באנגלית' : '' ?><?= $c['quote'] ? ' · הצעה ' . h($c['quote']) : '' ?></p>
<div class="acts">
 <?php if ($digits !== ''): ?><a class="btn gold" href="tel:<?= h(preg_replace('/[^\d+]/', '', (string) $c['phone'])) ?>"><bdi><?= h($c['phone']) ?></bdi></a><a class="btn" href="https://wa.me/<?= h($wa) ?>" target="_blank" rel="noopener">וואטסאפ</a><?php endif; ?>
 <?php if ($c['email']): ?><a class="btn" href="mailto:<?= h($c['email']) ?>"><bdi><?= h($c['email']) ?></bdi></a><?php endif; ?>
 <?php if ($rec['form'] === 'intake'): ?><a class="btn" href="/admin/quote.php?form=<?= h(rawurlencode($id)) ?>">הצעת מחיר מהשאלון</a><?php endif; ?>
 <button class="btn" type="button" onclick="window.print()">הדפסה</button>
</div>
<?php $sec = null; foreach ($rec['items'] as $it): if ($it['s'] !== $sec): if ($sec !== null) echo '</dl></section>'; $sec = $it['s']; ?>
<section class="card" style="margin-top:14px"><h3 style="color:#fff;font-size:1.05rem"><?= h($sec) ?></h3><dl class="kv" style="grid-template-columns:1fr">
<?php endif; ?>
 <dt><?= h($it['q']) ?></dt><dd style="margin-bottom:8px"><?= h($it['a']) ?></dd>
<?php endforeach; if ($sec !== null) echo '</dl></section>'; ?>
<?php if (!$rec['items']): ?><p class="muted">הלקוח שלח רק פרטים, בלי תשובות.</p><?php endif; ?>
</main>
<?php adm_page($title . ': ' . $c['name'] . ' | YYG', (string) ob_get_clean(), '@media print{.top,.acts{display:none}body{background:#fff;color:#000}.card{border:1px solid #ccc;background:#fff}.kv dt{color:#555}}');
