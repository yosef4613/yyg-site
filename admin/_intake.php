<?php
/*
 * YYG Web Design: שאלון אפיון → טיוטת הצעת מחיר, והערכה מיידית ללקוח.
 * משמש את בונה ההצעות (admin/quote.php) ואת קבלת השאלון (forms/submit.php).
 * הקובץ רק מגדיר פונקציות. גישה ישירה אליו לא עושה כלום.
 */
declare(strict_types=1);

/* שאלון אפיון → מסלול, תוספות, שורות לתמחור ותשלומים קבועים. price=null פירושו "לתמחר": השורה נכנסת בלי מחיר ומסומנת */
/* לשאלונים ישנים בלי תשובות לפי מזהה: משחזרים מהטקסט של השאלות */
function raw_from_items(array $items): array {
    $map = ['package' => '/איזה מסלול|Which package/u', 'pages' => '/אילו עמודים|Which pages/u', 'features' => '/אילו יכולות|Which features/u', 'languages' => '/באילו שפות|Which languages/u',
        'logo' => '/^(לוגו|Logo)$/u', 'photos' => '/^(תמונות|Photos)$/u', 'texts' => '/הטקסטים לאתר|טקסטים לאתר|website text/iu', 'update_who' => '/מי יעדכן|Who will update/u',
        'goal_action' => '/הדבר הכי חשוב|most important thing/u', 'site_now' => '/יש אתר קיים|have a site now/u', 'site_url' => '/הכתובת של האתר הקיים|current site/u',
        'deadline' => '/מתי האתר צריך|need to be live/u', 'budget' => '/תקציב משוער|Rough budget/u', 'domain' => '/הדומיין|domain\)/iu', 'site_name' => '/השם שיופיע|name shown/u'];
    $multi = ['pages', 'features', 'languages'];
    $r = [];
    foreach ($items as $it) foreach ($map as $id => $re) {
        if (isset($r[$id]) || !preg_match($re, (string) $it['q'])) continue;
        $a = (string) $it['a'];
        if (in_array($id, $multi, true)) {
            $vals = array_map('trim', explode(', ', $a));
            foreach ($vals as $i => $v) if (preg_match('/^(אחר|Other): (.*)$/u', $v, $m)) { $vals[$i] = '__other'; $r[$id . '__other'] = $m[2]; }
            $r[$id] = $vals;
        } else $r[$id] = $a;
    }
    return $r;
}
function intake_to_quote(array $rec): array {
    $r = !empty($rec['raw']) ? (array) $rec['raw'] : raw_from_items((array) ($rec['items'] ?? []));
    $val = static fn(string $k): string => is_string($r[$k] ?? null) ? $r[$k] : '';
    $arr = static fn(string $k): array => is_array($r[$k] ?? null) ? $r[$k] : [];
    $any = static function (array $list, string $re): bool { foreach ($list as $x) if (preg_match($re, (string) $x)) return true; return false; };
    $pages = $arr('pages'); $feats = $arr('features'); $langs = $arr('languages');
    $needStore = $any($feats, '/אשראי|Card payments/u') || $any($pages, '/^(חנות|Store)$/u') || preg_match('/לקנות ולשלם|Buy and pay/u', $val('goal_action'));
    $needClub = $any($feats, '/הרשמה עם אישור|Sign-up with|רשימת תפוצה|Mailing list/u') || $any($pages, '/^(הרשמה|Sign-up)$|אזור אישי|Customer accounts/u');
    $pageCount = count(array_filter($pages, static fn($p) => $p !== '__other' && !preg_match('/המלצות|Testimonials|שאלות נפוצות|FAQ/u', $p))) + (in_array('__other', $pages, true) ? 1 : 0);
    $kind = $needStore ? 'store' : ($needClub ? 'club' : ($pageCount > 0 && $pageCount <= 2 ? 'landing' : 'site'));
    $pkg = $val('package');
    $hints = []; $custom = []; $addons = []; $recurring = [];
    $added = static function (string $t) use (&$hints): void { $hints[] = ['ok', $t]; };
    $todo = static function (string $label) use (&$custom, &$hints): void { $custom[] = ['label' => $label, 'qty' => 1, 'price' => null]; $hints[] = ['todo', $label]; };
    $note = static function (string $t) use (&$hints): void { $hints[] = ['note', $t]; };
    if (preg_match('/מותג|Brand/u', $pkg)) $base = 'brand_' . $kind;
    elseif (preg_match('/בסיסי|Basic/u', $pkg)) $base = 'landing_basic';
    elseif (preg_match('/חנות|store/iu', $pkg)) $base = 'store';
    elseif (preg_match('/הרשמה|sign-ups/iu', $pkg)) $base = 'club';
    elseif (preg_match('/אזור ניהול|admin panel/iu', $pkg)) $base = 'site';
    elseif (preg_match('/דף נחיתה|Landing/iu', $pkg)) $base = 'landing';
    else { $base = $kind; $note('הלקוח לא בחר מסלול. המסלול נבחר לפי מה שסימן בעמודים וביכולות.'); }
    $core = preg_replace('/^brand_/', '', $base);
    if ($pkg !== '' && $base !== $kind && !str_starts_with($base, 'brand_') && !($kind === 'landing' && $core === 'landing_basic')) $note('הלקוח בחר "' . $pkg . '", אבל לפי מה שסימן מתאים יותר מסלול אחר. כדאי לבדוק.');
    /* יכולות שלא כלולות במסלול שנבחר */
    if ($needStore && $core !== 'store') $todo('סליקה ותשלום באשראי באתר');
    if ($needClub && !in_array($core, ['club', 'store'], true)) {
        if ($core === 'site') { $custom[] = ['label' => 'הרשמה, חשבונות משתמש ורשימת תפוצה', 'qty' => 1, 'price' => 2000]; $added('הרשמה ורשימת תפוצה (ההפרש למסלול עם הרשמה, 2,000 ₪)'); }
        else $todo('הרשמה ורשימת תפוצה');
    }
    /* עמודים מעבר למכסה */
    $quota = ['site' => 6, 'club' => 8];
    $sectionsNote = $any($pages, '/המלצות|Testimonials|שאלות נפוצות|FAQ/u') ? ' (שאלות נפוצות והמלצות נספרו כחלקים בעמוד הבית, לא כעמודים נפרדים)' : '';
    if (isset($quota[$core]) && $pageCount > $quota[$core]) { $addons['page'] = $pageCount - $quota[$core]; $added(($pageCount - $quota[$core]) . ' עמודים נוספים: סימן ' . $pageCount . ' עמודים, ובמסלול כלולים עד ' . $quota[$core] . $sectionsNote); }
    elseif (isset($quota[$core]) && $pageCount > 0) $note('סימן ' . $pageCount . ' עמודים, ובמסלול כלולים עד ' . $quota[$core] . '. אין תוספת עמודים' . $sectionsNote . '.');
    if (in_array($core, ['landing', 'landing_basic'], true) && $pageCount > 3) $note('סימן ' . $pageCount . ' עמודים, אבל המסלול הוא דף נחיתה. אולי מתאים אתר.');
    /* שפות */
    $langList = array_values(array_filter($langs, static fn($l) => $l !== '__other'));
    if (in_array('__other', $langs, true) && $val('languages__other') !== '') $langList[] = $val('languages__other');
    if (count($langList) >= 2) {
        $addons['en'] = true; $added('שפה שנייה: ' . implode(' ו', array_slice($langList, 0, 2)));
        foreach (array_slice($langList, 2) as $l) $todo('שפה נוספת: ' . $l);
    }
    /* לוגו */
    $logo = $val('logo');
    if (!str_starts_with($base, 'brand_')) {
        if (preg_match('/אין, צריך|None, I need|לחדש|want a new/u', $logo)) { $addons['logo2'] = true; $added('לוגו מקצועי, 1,500 ₪ (אפשר להחליף ללוגו בסיסי ב־590 ₪)'); }
        elseif (preg_match('/רק תמונה|only have a picture/u', $logo)) $todo('שחזור הלוגו לקובץ איכותי');
    }
    /* תוכן ותמונות */
    if ($core === 'landing_basic' && preg_match('/שתכתוב|write it/u', $val('texts'))) $todo('כתיבת טקסטים (לא כלולה בבסיסי)');
    if (preg_match('/אין, צריך|None, I need/u', $val('photos'))) $todo('תמונות מתאימות מהמאגר');
    /* יכולות מיוחדות */
    if ($any($feats, '/זימון תורים|Appointment/u')) $todo('זימון תורים ביומן');
    if ($any($feats, '/נגן|player/u')) $todo('נגן מוזיקה או וידאו');
    if ($any($feats, '/מחשבון|calculator/u')) $todo('מחשבון או שאלון באתר');
    if (in_array('__other', $feats, true) && $val('features__other') !== '') $todo('יכולת נוספת: ' . $val('features__other'));
    if (in_array('__other', $pages, true) && $val('pages__other') !== '') $note('עמוד נוסף שהלקוח ביקש: ' . $val('pages__other'));
    /* דף נחיתה, והלקוח רוצה לעדכן בעצמו (שעות, מבצעים): בדף נחיתה אין אזור ניהול, אז מסך עדכון קטן */
    if (in_array($core, ['landing', 'landing_basic'], true) && preg_match('/אני או מישהו|Me or someone/u', $val('update_who'))) $todo('מסך עדכון קטן לבעל העסק' . ($val('update_what') !== '' ? ': ' . mb_substr($val('update_what'), 0, 60, 'UTF-8') . (mb_strlen($val('update_what'), 'UTF-8') > 60 ? '…' : '') : ''));
    /* תשלומים קבועים */
    if (preg_match('/שאתה תעדכן|like you to do/u', $val('update_who'))) { $recurring['maint'] = true; $added('תחזוקה חודשית (הלקוח רוצה שאתה תעדכן)'); }
    /* הערות לשיחה */
    if (preg_match('/^(כן|Yes)$/u', $val('site_now'))) $note('יש אתר קיים' . ($val('site_url') ? ' (' . $val('site_url') . ')' : '') . ': לתכנן הפניות כדי לשמור על הדירוג בגוגל.');
    if ($val('deadline') !== '' && !preg_match('/אין לחץ|No rush/u', $val('deadline'))) $note('לוח זמנים: ' . $val('deadline') . ($val('deadline_date') ? ', ' . $val('deadline_date') : '') . ($val('deadline_why') ? ' (' . $val('deadline_why') . ')' : '') . '.');
    if ($val('budget') !== '') $note('התקציב שהלקוח ציין: ' . $val('budget') . '.');
    if (preg_match('/צריך לרשום|needs registering/u', $val('domain'))) $note('צריך לרשום דומיין' . ($val('domain_wish') ? ': ' . rtrim($val('domain_wish'), '. ') : '') . '. כלול בשנה הראשונה.');
    $who = $val('site_name') ?: ($rec['contact']['business'] ?? '') ?: ($rec['contact']['name'] ?? '');
    return ['base' => $base, 'addons' => $addons, 'custom' => $custom, 'recurring' => $recurring, 'hints' => $hints, 'who' => $who];
}

/* ---------- הערכת מחיר מיידית ללקוח, בסוף שאלון האפיון ----------
   אותם כללים כמו בבונה ההצעות. מה שצריך תמחור אישי לא נכנס לסכום, ומופיע ברשימה "יתומחר בהצעה".
   המחירים כאן חייבים להתאים ל־BASES ול־ADDONS שב־admin/quote.php ול־assets/offer.json */
const EST_BASES = [
    'landing_basic' => [1290, 500, 'דף נחיתה בסיסי', 'Basic landing page'],
    'landing' => [2000, 500, 'דף נחיתה', 'Landing page'],
    'site' => [4000, 1000, 'אתר עם אזור ניהול', 'Website with an admin area'],
    'club' => [6000, 1500, 'אתר עם הרשמה ורשימת תפוצה', 'Website with sign-ups and a mailing list'],
    'store' => [7000, 1800, 'חנות אונליין', 'Online store'],
    'brand_landing' => [5900, 500, 'מותג מ־0 עד 100% עם דף נחיתה', 'Brand from 0 to 100% with a landing page'],
    'brand_site' => [7900, 1000, 'מותג מ־0 עד 100% עם אתר עם אזור ניהול', 'Brand from 0 to 100% with a website'],
    'brand_club' => [9900, 1500, 'מותג מ־0 עד 100% עם אתר עם הרשמה', 'Brand from 0 to 100% with a sign-up website'],
    'brand_store' => [10900, 1800, 'מותג מ־0 עד 100% עם חנות', 'Brand from 0 to 100% with a store'],
];
const EST_TODO_EN = [
    'סליקה ותשלום באשראי באתר' => 'Card payments on the site',
    'הרשמה ורשימת תפוצה' => 'Sign-ups and a mailing list',
    'שחזור הלוגו לקובץ איכותי' => 'Recreating the logo as a quality file',
    'כתיבת טקסטים (לא כלולה בבסיסי)' => 'Writing the texts (not included in the basic package)',
    'תמונות מתאימות מהמאגר' => 'Suitable stock photos',
    'זימון תורים ביומן' => 'Appointment booking',
    'נגן מוזיקה או וידאו' => 'A music or video player',
    'מחשבון או שאלון באתר' => 'A calculator or quiz on the site',
];
function intake_estimate(array $rec, string $lang = 'he'): array {
    $he = $lang !== 'en';
    $d = intake_to_quote($rec);
    $b = EST_BASES[$d['base']] ?? EST_BASES['site'];
    $lines = [[$he ? $b[2] : $b[3], $b[0]]];
    if (!empty($d['addons']['en'])) $lines[] = [$he ? 'גרסה בשפה שנייה' : 'A second language version', $b[1]];
    if (!empty($d['addons']['page'])) { $n = (int) $d['addons']['page']; $lines[] = [$he ? ($n === 1 ? 'עמוד נוסף' : "$n עמודים נוספים") : ($n === 1 ? '1 extra page' : "$n extra pages"), $n * 350]; }
    if (!empty($d['addons']['logo2'])) $lines[] = [$he ? 'לוגו מקצועי (או לוגו בסיסי ב־590 ₪)' : 'A professional logo (or a basic logo for ₪590)', 1500];
    $todo = [];
    foreach ($d['custom'] as $c) {
        $label = (string) $c['label'];
        if ($c['price'] !== null) { $lines[] = [$he ? $label : ($label === 'הרשמה, חשבונות משתמש ורשימת תפוצה' ? 'Sign-ups, user accounts and a mailing list' : $label), (int) $c['price']]; continue; }
        if ($he) { $todo[] = $label; continue; }
        if (isset(EST_TODO_EN[$label])) $todo[] = EST_TODO_EN[$label];
        elseif (str_starts_with($label, 'מסך עדכון קטן')) $todo[] = 'A small update screen for the owner';
        elseif (str_starts_with($label, 'שפה נוספת')) $todo[] = 'Another language';
        elseif (str_starts_with($label, 'יכולת נוספת')) $todo[] = 'Another feature you asked for';
        else $todo[] = 'Another item you asked for';
    }
    $yearly = [$he ? '"הכול עליי" מהשנה השנייה: 590 ₪ לשנה (דומיין, אחסון, מייל, אבטחה, גיבויים ותיקון תקלות)' : '"All-in Care" from the second year: ₪590 a year (domain, hosting, email, security, backups and fixes)'];
    if (!empty($d['recurring']['maint'])) $yearly[] = $he ? 'תחזוקה חודשית, כי ביקשתם שאני אעדכן: 199 ₪ לחודש' : 'Monthly maintenance, since you asked me to do the updates: ₪199 a month';
    return ['total' => array_sum(array_column($lines, 1)), 'lines' => $lines, 'todo' => array_values(array_unique($todo)), 'yearly' => $yearly, 'base' => $d['base']];
}
