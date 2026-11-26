<?php
/*
 * YYG Web Design: שרת MCP קטן לסוכני בינה מלאכותית. https://yygweb.com/mcp
 * Streamable HTTP בלי מצב (stateless): כל בקשה היא JSON-RPC אחת, והתשובה JSON.
 * לקריאה בלבד: מחירים, תהליך ותשלום, הערכת מחיר, דוגמאות ודרכי יצירת קשר. אין כאן גישה לשום נתון פרטי.
 * המקור לכל התשובות: assets/offer.json (אותו קובץ שממנו נבנים המחירון וגרסאות ה־Markdown).
 * כרטיס השרת: /.well-known/mcp/server-card.json
 */
declare(strict_types=1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Mcp-Session-Id, MCP-Protocol-Version');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'OPTIONS') { http_response_code(204); exit; }
if ($method !== 'POST') {
    /* GET: אין זרם אירועים מהשרת. מחזירים הסבר קצר */
    http_response_code(405);
    header('Allow: POST');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'This is an MCP endpoint (Streamable HTTP). Send JSON-RPC 2.0 requests with POST.', 'server_card' => 'https://yygweb.com/.well-known/mcp/server-card.json'], JSON_UNESCAPED_SLASHES);
    exit;
}

const VERSIONS = ['2025-11-25', '2025-06-18', '2025-03-26', '2024-11-05'];
$O = json_decode((string) @file_get_contents(__DIR__ . '/assets/offer.json'), true) ?: [];

function reply($id, array $result): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function fail($id, int $code, string $msg): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $msg]], JSON_UNESCAPED_SLASHES);
    exit;
}
function nis(int $n): string { return number_format($n) . ' ILS'; }
function L(array $v, string $lang): string { return (string) ($v[$lang] ?? $v['en'] ?? ''); }

$langProp = ['type' => 'string', 'enum' => ['he', 'en'], 'description' => 'Answer language: he (Hebrew) or en (English). Default en.'];
$TOOLS = [
    ['name' => 'get_packages_and_prices', 'title' => 'Packages and prices',
     'description' => 'All YYG Web Design website packages, add-ons and recurring costs, with prices in ILS. Prices are final, and no VAT is added.',
     'inputSchema' => ['type' => 'object', 'properties' => ['lang' => $langProp]], 'annotations' => ['readOnlyHint' => true]],
    ['name' => 'get_process_and_payment', 'title' => 'Process and payment',
     'description' => 'How a YYG Web Design project works step by step (3 designs before committing), and how payment works (10% / 40% / 50%).',
     'inputSchema' => ['type' => 'object', 'properties' => ['lang' => $langProp]], 'annotations' => ['readOnlyHint' => true]],
    ['name' => 'estimate_price', 'title' => 'Estimate a price',
     'description' => 'A rough starting estimate in ILS for a website. The final price comes in a formal quote after a short questionnaire.',
     'inputSchema' => ['type' => 'object', 'properties' => [
        'type' => ['type' => 'string', 'enum' => ['landing_basic', 'landing', 'site', 'club', 'store'], 'description' => 'landing_basic, landing (one page), site (website with an admin area), club (sign-ups and a mailing list), store (online store)'],
        'pages' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 40, 'description' => 'Number of pages (for site or club)'],
        'english' => ['type' => 'boolean', 'description' => 'Also an English version'],
        'logo' => ['type' => 'string', 'enum' => ['none', 'basic', 'pro'], 'description' => 'Need a logo: none, basic, or pro'],
        'lang' => $langProp], 'required' => ['type']], 'annotations' => ['readOnlyHint' => true]],
    ['name' => 'list_examples', 'title' => 'Examples',
     'description' => 'Live client websites and complete example projects by YYG Web Design, with links.',
     'inputSchema' => ['type' => 'object', 'properties' => ['lang' => $langProp]], 'annotations' => ['readOnlyHint' => true]],
    ['name' => 'get_contact', 'title' => 'Contact',
     'description' => 'How to reach YYG Web Design: phone (calls only), hours, the contact form, and the discovery questionnaire that leads to a formal quote.',
     'inputSchema' => ['type' => 'object', 'properties' => ['lang' => $langProp]], 'annotations' => ['readOnlyHint' => true]],
];

function call_tool(string $name, array $a, array $O): string {
    $lang = ($a['lang'] ?? 'en') === 'he' ? 'he' : 'en';
    $he = $lang === 'he';
    $from = $he ? 'מ־' : 'from ';
    switch ($name) {
        case 'get_packages_and_prices':
            $t = [($he ? 'מסלולים:' : 'Packages:')];
            foreach ($O['packages'] ?? [] as $p) $t[] = '- ' . L($p['name'], $lang) . ': ' . (!empty($p['from']) ? $from : '') . nis((int) $p['price']) . '. ' . L($p['summary'], $lang);
            $t[] = ''; $t[] = $he ? 'תוספות:' : 'Add-ons:';
            foreach ($O['addons'] ?? [] as $x) $t[] = '- ' . L($x['name'], $lang) . ': ' . (!empty($x['from']) ? $from : '') . nis((int) $x['price']);
            $t[] = ''; $t[] = $he ? 'תשלומים קבועים:' : 'Recurring:';
            foreach ($O['recurring'] ?? [] as $x) $t[] = '- ' . L($x['name'], $lang) . ': ' . nis((int) $x['price']) . ($x['per'] === 'year' ? ($he ? ' לשנה' : ' a year') : ($he ? ' לחודש' : ' a month'));
            $t[] = ''; $t[] = L($O['vat_note'] ?? [], $lang) . ' ' . L($O['included_everywhere'] ?? [], $lang);
            $t[] = L($O['launch_offer'] ?? [], $lang);
            $t[] = ($he ? 'המחירון המלא: ' : 'Full pricing: ') . 'https://yygweb.com/' . ($he ? '' : 'en/') . 'pricing/';
            return implode("\n", $t);
        case 'get_process_and_payment':
            $t = [$he ? 'איך עובדים:' : 'How it works:'];
            foreach ($O['process'] ?? [] as $i => $s) $t[] = ($i + 1) . '. ' . L($s, $lang);
            $t[] = ''; $t[] = ($he ? 'תשלום: ' : 'Payment: ') . L($O['payment'] ?? [], $lang);
            $t[] = L($O['included_everywhere'] ?? [], $lang);
            return implode("\n", $t);
        case 'estimate_price':
            $type = (string) ($a['type'] ?? '');
            $base = null; $pkg = null;
            foreach ($O['packages'] ?? [] as $p) if ($p['id'] === $type) { $pkg = $p; $base = (int) $p['price']; }
            if ($pkg === null) return $he ? 'סוג לא מוכר. אפשרויות: landing_basic, landing, site, club, store.' : 'Unknown type. Options: landing_basic, landing, site, club, store.';
            $total = $base; $lines = [L($pkg['name'], $lang) . ': ' . nis($base)];
            $quota = ['site' => 6, 'club' => 8][$type] ?? null;
            $pages = (int) ($a['pages'] ?? 0);
            if ($quota !== null && $pages > $quota) { $x = ($pages - $quota) * 350; $total += $x; $lines[] = ($he ? 'עמודים נוספים: ' : 'Extra pages: ') . ($pages - $quota) . ' × 350 = ' . nis($x); }
            if (!empty($a['english'])) { $x = ['landing_basic' => 500, 'landing' => 500, 'site' => 1000, 'club' => 1500, 'store' => 1800][$type]; $total += $x; $lines[] = ($he ? 'גרסה באנגלית: ' : 'English version: ') . nis($x); }
            $logo = (string) ($a['logo'] ?? 'none');
            if ($logo === 'basic') { $total += 590; $lines[] = ($he ? 'לוגו בסיסי: ' : 'Basic logo: ') . nis(590); }
            if ($logo === 'pro') { $total += 1500; $lines[] = ($he ? 'לוגו מקצועי: ' : 'Professional logo: ') . nis(1500); }
            $lines[] = ($he ? 'הערכה: ' : 'Estimate: ') . ($type === 'landing_basic' ? '' : $from) . nis($total) . ($he ? ' (סופי, בלי מע״מ)' : ' (final, no VAT added)');
            $lines[] = $he ? 'זו הערכה ראשונית. הצעת מחיר מסודרת מגיעה אחרי שאלון קצר: https://yygweb.com/questionnaire/' : 'This is a first estimate. A formal quote comes after a short questionnaire: https://yygweb.com/en/questionnaire/';
            return implode("\n", $lines);
        case 'list_examples':
            $t = [$he ? 'אתרים של לקוחות:' : 'Client websites:', '- hakef.com: ' . ($he ? 'קייטרינג בוטיק ובר פטיפורים בירושלים' : 'boutique catering and petit-four bars in Jerusalem'), '- https://xn--cebeaqd7a2ak.com/: ' . ($he ? 'מקהלת ילדים וחוגי פיתוח קול, עם חיפוש חוג, הרשמה וחשבונות' : "a boys' choir and voice classes, with a class search, sign-ups and accounts"), '', $he ? 'פרויקטים לדוגמה מלאים:' : 'Complete example projects:'];
            foreach ($O['examples'] ?? [] as $x) $t[] = '- ' . L($x['name'], $lang) . ': ' . ($he ? $x['url'] : str_replace('https://yygweb.com/', 'https://yygweb.com/en/', $x['url']));
            return implode("\n", $t);
        case 'get_contact':
            $b = $O['business'] ?? [];
            return implode("\n", [
                ($he ? 'טלפון (לשיחות בלבד): ' : 'Phone (calls only): ') . ($b['phone'] ?? ''),
                ($he ? 'מתי: ' : 'When: ') . L($b['hours'] ?? [], $lang),
                ($he ? 'טופס פנייה: ' : 'Contact form: ') . L($b['contact_page'] ?? [], $lang),
                ($he ? 'שאלון אפיון, שממנו מגיעה הצעת מחיר מסודרת: ' : 'Discovery questionnaire, which leads to a formal quote: ') . L($b['questionnaire'] ?? [], $lang),
            ]);
    }
    return '';
}

$raw = (string) file_get_contents('php://input');
if (strlen($raw) > 65536) fail(null, -32600, 'Request too large');
$req = json_decode($raw, true);
if (!is_array($req)) fail(null, -32700, 'Parse error');
if (isset($req[0])) fail(null, -32600, 'Batch requests are not supported');
$id = $req['id'] ?? null;
$m = (string) ($req['method'] ?? '');
$params = is_array($req['params'] ?? null) ? $req['params'] : [];

/* התראות (בלי id): מאשרים בלי גוף */
if (!array_key_exists('id', $req)) { http_response_code(202); exit; }

switch ($m) {
    case 'initialize':
        $want = (string) ($params['protocolVersion'] ?? '');
        reply($id, [
            'protocolVersion' => in_array($want, VERSIONS, true) ? $want : VERSIONS[1],
            'capabilities' => ['tools' => ['listChanged' => false]],
            'serverInfo' => ['name' => 'yygweb', 'title' => 'YYG Web Design web design studio', 'version' => '1.0.0'],
            'instructions' => 'Read-only information about YYG Web Design, a web design and development studio in Israel (Hebrew and English). Use get_packages_and_prices for prices (ILS, final, no VAT added), estimate_price for a rough estimate, and get_contact to point the user to the contact form or the discovery questionnaire. Pass lang "he" for Hebrew answers.',
        ]);
    case 'ping':
        reply($id, []);
    case 'tools/list':
        reply($id, ['tools' => $TOOLS]);
    case 'tools/call':
        $name = (string) ($params['name'] ?? '');
        $args = is_array($params['arguments'] ?? null) ? $params['arguments'] : [];
        if (!in_array($name, array_column($TOOLS, 'name'), true)) fail($id, -32602, 'Unknown tool: ' . $name);
        reply($id, ['content' => [['type' => 'text', 'text' => call_tool($name, $args, $O)]], 'isError' => false]);
    case 'resources/list':
        reply($id, ['resources' => []]);
    case 'prompts/list':
        reply($id, ['prompts' => []]);
}
fail($id, -32601, 'Method not found: ' . $m);
