"""English versions of the guides (LTR), written to /en/guides/.

Mirrors tools/guides.py: same template, CSS classes and structure, translated
content. It does not import guides.py (that script writes the Hebrew pages and
the sitemap when it runs) and never writes outside /en/guides/.
"""
import json, os, html
import pathlib
root = str(pathlib.Path(__file__).resolve().parent.parent)
OUT = f'{root}/en/guides'
os.makedirs(OUT, exist_ok=True)
SITE = 'https://yygweb.com'
TODAY = '2026-11-25'

# Same block as STYLE in guides.py, flipped for LTR (table cells align left).
STYLE = '''<style>
 .g-top{border-bottom:1px solid var(--line);background:var(--base)}
 .g-top .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:72px}
 .g-top img{height:48px;width:auto}
 .g-top nav{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
 .g-main{padding:clamp(36px,6vw,72px) 0 clamp(56px,8vw,104px)}
 .g-main .wrap{max-width:780px}
 .g-crumb{font-size:.92rem;color:var(--muted)}
 .g-crumb a{color:var(--muted)}
 .g-main h1{margin-top:12px;font-family:var(--display);font-weight:700;font-size:clamp(2.3rem,1.6rem + 2.6vw,3.6rem);line-height:1}
 .g-lead{margin-top:16px;font-size:var(--step-1);color:#D7DDE6}
 .g-meta{margin-top:10px;font-size:.92rem;color:var(--muted)}
 .g-main h2{margin-top:40px;font-size:var(--step-2);line-height:1.25}
 .g-main h3{margin-top:24px;font-size:var(--step-1)}
 .g-main p,.g-main li{margin-top:12px;color:#C9D1DD;line-height:1.75}
 .g-main ul,.g-main ol{padding-inline-start:24px}
 .g-main ul{list-style:disc}
 .g-main ol{list-style:decimal}
 .g-main b{color:#fff}
 .g-table{width:100%;margin-top:16px;border-collapse:collapse;font-size:.98rem}
 .g-table th,.g-table td{padding:10px 12px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top}
 .g-table th{color:#fff;background:var(--panel)}
 .g-scroll{overflow-x:auto}
 .g-table td:nth-child(2){white-space:nowrap}
 .g-cta{margin-top:36px;padding:clamp(20px,3vw,28px);border:1px solid var(--line-2);border-radius:16px;background:var(--panel)}
 .g-cta h2{margin-top:0}
 .g-cta .btn{margin-top:16px}
 .g-main details{margin-top:4px}
 .g-rel{margin-top:40px}
 .g-rel ul{list-style:none;padding:0}
 .g-cards{display:grid;gap:14px;margin-top:24px}
 .g-card{display:block;padding:20px;border:1px solid var(--line);border-radius:14px;background:var(--panel);text-decoration:none;color:var(--text)}
 .g-card:hover{border-color:var(--gold)}
 .g-card b{display:block;font-size:var(--step-1)}
 .g-card span{display:block;margin-top:6px;color:var(--muted)}
</style>'''


def page(slug, title, desc, h1, lead, body, faq=None, related=()):
    if slug != 'index':
        url = f'{SITE}/en/guides/{slug}.html'
        he_path = f'/guides/{slug}.html'
    else:
        url = f'{SITE}/en/guides/'
        he_path = '/guides/'
    he_url = SITE + he_path
    ld = [{
        '@context': 'https://schema.org', '@type': 'Article', 'headline': h1, 'description': desc,
        'inLanguage': 'en', 'datePublished': TODAY, 'dateModified': TODAY, 'mainEntityOfPage': url,
        'author': {'@type': 'Person', 'name': 'Yehuda Gordon', 'url': SITE + '/en/'},
        'publisher': {'@type': 'Organization', 'name': 'YYG', 'logo': {'@type': 'ImageObject', 'url': SITE + '/assets/img/logo-320.webp'}},
        'image': SITE + '/assets/img/og.jpg',
    }]
    if faq:
        ld.append({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': [
            {'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}} for q, a in faq]})
    ld.append({'@context': 'https://schema.org', '@type': 'BreadcrumbList', 'itemListElement': [
        {'@type': 'ListItem', 'position': 1, 'name': 'YYG', 'item': SITE + '/en/'},
        {'@type': 'ListItem', 'position': 2, 'name': 'Guides', 'item': SITE + '/en/guides/'},
    ] + ([{'@type': 'ListItem', 'position': 3, 'name': h1, 'item': url}] if slug != 'index' else [])})
    faq_html = ''
    if faq:
        faq_html = '<h2>Frequently asked questions</h2>\n' + '\n'.join(
            f'<details><summary>{html.escape(q)}</summary><p>{html.escape(a)}</p></details>' for q, a in faq)
    rel = ''
    if related:
        rel = '<aside class="g-rel" aria-labelledby="rel-t"><h2 id="rel-t">More guides</h2><ul>' + ''.join(
            f'<li><a class="text-link" href="/en/guides/{s}.html">{t}</a></li>' for s, t in related) + '</ul></aside>'
    return f'''<!doctype html>
<html lang="en" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<script>if(location.protocol==="http:"&&location.hostname==="yygweb.com")location.replace("https://yygweb.com"+location.pathname+location.search+location.hash)</script>
<title>{html.escape(title)}</title>
<meta name="description" content="{html.escape(desc)}">
<link rel="canonical" href="{url}">
<link rel="alternate" hreflang="he" href="{he_url}">
<link rel="alternate" hreflang="en" href="{url}">
<link rel="alternate" hreflang="x-default" href="{he_url}">
<meta property="og:type" content="article">
<meta property="og:locale" content="en_US">
<meta property="og:locale:alternate" content="he_IL">
<meta property="og:url" content="{url}">
<meta property="og:title" content="{html.escape(h1)}">
<meta property="og:description" content="{html.escape(desc)}">
<meta property="og:image" content="{SITE}/assets/img/og.jpg">
<meta name="theme-color" content="#151920">
<link rel="icon" href="/assets/img/icon-64.png" type="image/png">
<link rel="preload" href="/assets/fonts/karantina-700-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/site.css?v=34">
{STYLE}
<script type="application/ld+json">{json.dumps(ld, ensure_ascii=False, separators=(',', ':'))}</script>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="g-top">
 <div class="wrap">
  <a href="/en/"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="YYG, home page"></a>
  <nav aria-label="Main">
   <a class="text-link" href="/en/guides/">Guides</a>
   <a class="text-link" href="/en/#plan">Price calculator</a>
   <a class="text-link" href="/en/#contact">Contact</a>
   <a class="text-link" href="{he_path}" hreflang="he" lang="he">עברית</a>
  </nav>
 </div>
</header>
<main id="main" class="g-main">
 <div class="wrap">
  <p class="g-crumb"><a href="/en/">YYG</a> › <a href="/en/guides/">Guides</a></p>
  <h1>{h1}</h1>
  <p class="g-lead">{lead}</p>
  <p class="g-meta">Yehuda Gordon, web designer · Updated November 2026</p>
{body}
{faq_html}
  <section class="g-cta" aria-labelledby="cta-t">
   <h2 id="cta-t">Want to know what your website will cost?</h2>
   <p>Answer 3 quick questions in the price calculator, no contact details required. And if we’re a good fit, we start with 10%, and I’ll prepare 3 design directions for your website before you go ahead.</p>
   <a class="btn btn-gold" href="/en/?src=guide-{slug}#plan">Open the price calculator</a>
  </section>
{rel}
 </div>
</main>
<footer class="foot">
 <div class="wrap foot-in">
  <p class="foot-brand"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="" loading="lazy"> YYG, Yehuda Gordon. Web design and development</p>
  <p><a class="foot-link" href="/en/guides/">Guides</a> · <a class="foot-link" href="/en/accessibility.html">Accessibility statement</a> · <a class="foot-link" href="/en/privacy.html">Privacy policy</a></p>
 </div>
</footer>
<script>if(location.hostname==="yygweb.com"&&navigator.sendBeacon){{try{{var q=new URLSearchParams(location.search);navigator.sendBeacon("/hit.php",new Blob([JSON.stringify({{e:"view",p:location.pathname,r:document.referrer,s:q.get("src")||""}})],{{type:"application/json"}}))}}catch(e){{}}}}</script>
</body>
</html>
'''


# ---------- Guide 1: what a website costs ----------
cost_body = '''
<h2>The short answer</h2>
<p>The price of a website depends mainly on three things: <b>how many pages</b> it has, <b>what the site needs to do</b> (just present information, take sign-ups, or sell), and <b>who updates it</b> afterward. Here are my starting prices, so you have a reference point. No VAT is added. The final price depends on your needs and requirements, and it’s always spelled out in a clear written quote before we begin:</p>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>Type of website</th><th>Price</th><th>Delivery time</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Landing page</td><td>From ₪2,000</td><td>Up to 4 working days</td><td>A business with one service or promotion that wants the phone to ring</td></tr>
<tr><td>Website with admin panel</td><td>From ₪4,000</td><td>Up to 10 working days</td><td>A business with several services that wants to update its own content</td></tr>
<tr><td>Website with sign-ups &amp; mailing list</td><td>From ₪6,000</td><td>Up to 15 working days</td><td>Organizations, institutions, and class providers that take sign-ups</td></tr>
<tr><td>Online store</td><td>From ₪7,000</td><td>Up to 20 working days</td><td>Anyone selling products, with credit card payments and shipping</td></tr>
</tbody></table></div>
<p>An English version alongside the Hebrew site is an add-on, from ₪500 for a landing page up to ₪1,800 for an online store.</p>

<h2>What makes a website more expensive, and what doesn’t</h2>
<h3>1. The number of pages</h3>
<p>Every page needs design, content, and testing on a phone. A landing page is one long page. A typical business website usually has 4 to 8 pages: home, services, about, FAQ, and contact.</p>
<h3>2. What the site does</h3>
<p>A site that only presents information is the least expensive. As soon as it needs to take sign-ups, send emails, manage user accounts, or accept payments, there’s more to build and more to test.</p>
<h3>3. Who updates the site</h3>
<p>If your content hardly ever changes, you can skip the admin panel and save. If you post news, promotions, products, or schedules, an admin panel is worth it: you make the updates yourself, without waiting on anyone.</p>
<h3>4. Custom design or a template</h3>
<p>A ready-made template costs less at first, but it looks like hundreds of other websites. A custom design is built around your business, your customers, and the action you want them to take.</p>
<h3>5. Accessibility</h3>
<p>Israeli accessibility regulations require many businesses and institutions to make their websites accessible. Building accessibility in from the start costs far less than retrofitting an existing site. I include it in every package.</p>

<h2>Ongoing costs once your site is ready</h2>
<ul>
<li><b>Domain</b> (your website’s address): a .com address usually costs a few dozen shekels a year.</li>
<li><b>Hosting</b> (the server your site lives on): usually a few hundred shekels a year, depending on the provider and the plan.</li>
<li><b>Business email</b> on your domain: optional. Some hosting providers include it free with the plan.</li>
</ul>
<p>I include <b>the first year of domain, hosting, and business email</b> in the price of the website. From the second year on, you choose: All-in Care, where I take care of everything for ₪590 a year, or a move to a hosting provider of your own. Either way, the domain is registered <b>in your name</b>, so the website is truly yours.</p>

<h2>How to avoid committing to a website you haven’t seen</h2>
<p>The biggest risk in building a website is paying a lot up front and finding out at the end that the design isn’t what you had in mind. Most designers ask for 30% to 50% before you see anything. I work differently:</p>
<ol>
<li>After a short call and a formal quote, you pay <b>just 10%</b> to start, and I prepare <b>3 design directions</b> for your website, using your own content.</li>
<li>Love one of the directions? You pay another 40%, and we move on to the build.</li>
<li>You pay the remaining 50% at the end, <b>after you’ve reviewed and approved everything</b>, before the site goes live.</li>
</ol>
'''
cost_faq = [
    ('How much does a landing page cost?', 'My landing pages start at ₪2,000, and no VAT is added. That includes 3 design directions, 3 revision rounds, a contact form, and a click-to-call button, and it’s ready within 4 working days. The final price depends on what the page needs.'),
    ('Does the price include VAT?', 'Yes. My prices are final, and no VAT is added on top.'),
    ('Is there a monthly fee?', 'There’s no monthly fee. The first year of domain, hosting, and business email is included in the price of the website. After that, All-in Care is ₪590 a year if you’d like me to take care of everything.'),
    ('What if I need more pages later?', 'You can add pages even after the site is live. Each page beyond your package costs ₪350.'),
]

# ---------- Guide 2: what to prepare ----------
prep_body = '''
<p>You don’t need to prepare anything to start the conversation. But every item on this list shortens the process, saves revision rounds, and helps us arrive at a website that truly fits you. You can also <a class="text-link" href="/en/#prep">check off this list on my home page</a>, and it will be attached to your inquiry.</p>

<h2>1. Two or three websites you like</h2>
<p>This is the single most helpful thing. They don’t have to be in your field. Write one sentence about what you liked: The colors? The layout? The big photos? The clean feel? <b>Even a website you didn’t like helps</b>, because it shows what to avoid.</p>

<h2>2. What the website needs to achieve</h2>
<p>Should people call you? Leave their details? Sign up for a class? Order a product? Choose <b>one main goal</b>. A website that tries to do everything equally ends up doing nothing well.</p>

<h2>3. Who your customers are</h2>
<p>Who visits the site, and what do they want to know? Tip: think about <b>the questions people ask you most often on the phone</b>. The answers usually make the best headings for your website.</p>

<h2>4. Design preferences</h2>
<p>A <b>dark or light</b> background? Colors you love, and colors you don’t? A classic, modern, or festive style? If you write this down in advance, the design directions you receive will be closer to what you imagined, and you won’t have to sift through examples that miss the mark. “I don’t know yet” is a perfectly good answer too: in that case, I’ll make the design directions clearly different from one another.</p>

<h2>5. A logo</h2>
<p>Your logo is the foundation of the design: the site’s colors, fonts, and overall feel all grow out of it. Send it in the best quality you have, ideally the original file from your designer. <b>Don’t have a logo yet?</b> It’s best to create a logo and branding first, then build the website around them. I also design logos: a basic logo for ₪590, or a professional logo with 3 concepts for ₪1,500. A basic brand identity with colors, fonts and a business card is ₪2,500.</p>

<h2>6. Real photos</h2>
<p>Photos of your work, your products, your place, or your team. <b>A good phone photo taken in daylight beats a stock photo</b>, because customers can tell right away what’s real. If you don’t have photos, we can start with a few and add more later.</p>

<h2>7. Your content, in bullet points</h2>
<p>What you offer, what it costs (if you want to publish prices), opening hours, service area, and common questions. <b>Bullet points are enough</b>. I write the full copy with the help of AI and edit it personally, and you approve it.</p>

<h2>8. A web address, if you have one</h2>
<p>If you already have a domain, let me know where it’s registered. Don’t have one yet? I’ll help you choose a name that’s short, easy to spell over the phone, and easy to remember.</p>

<h2>9. Timeline and budget</h2>
<p>Is there a date the site has to be live by? A launch, a season, an event? Write it down, and we’ll plan backward from it. Share a rough budget range too, so I can recommend the right package and nothing more than you need.</p>

<h2>How to reach out so you get exactly what you need</h2>
<ul>
<li><b>One sentence about your business</b>: what you do and for whom, as if you were explaining it to a neighbor.</li>
<li><b>Links to websites you liked</b>: there’s a field for them in the form.</li>
<li><b>What doesn’t work for you</b>: a color, a style, or anything that doesn’t fit your audience. This saves a revision round.</li>
<li><b>Not sure about something?</b> That’s fine. That’s what the call is for, and I’ll ask the questions.</li>
</ul>
'''
prep_faq = [
    ('Do I have to prepare the whole list before I reach out?', 'No. You can reach out without preparing anything at all. Each item on the list simply makes the process shorter.'),
    ('I don’t have a logo. Can you still build my website?', 'It’s best to start with the logo, because the website’s design is built around it. If you don’t have one, I can create a basic logo (₪590), a professional logo (₪1,500) or a basic brand identity (₪2,500) before the website.'),
    ('Who writes the website copy?', 'You send me the information in bullet points, and I write all the copy with the help of AI and edit it personally. You approve every word before the site goes live.'),
]

# ---------- Guide 3: landing page or full website ----------
lp_body = '''
<p>This is one of the first questions business owners ask. The answer comes down to a simple question: <b>how many different things do you want customers to do, and how much do you have to say?</b></p>

<h2>What is a landing page?</h2>
<p>One long, focused page that leads to a single action: calling, leaving details, or signing up. There’s no menu with lots of pages, and nothing to distract.</p>
<h3>A landing page is the right fit when:</h3>
<ul>
<li>You have <b>one main service</b> or a specific promotion.</li>
<li>You want to <b>get started fast</b> (with me, up to 4 working days) and on a small budget (from ₪2,000).</li>
<li>You’re running ads or handing out flyers, and you want everyone who arrives to do one thing.</li>
</ul>

<h2>What is a full website?</h2>
<p>Several pages with a menu: home, services, about, FAQ, contact, and sometimes a content section too. With an admin panel, you update anything that changes on your own.</p>
<h3>A full website is the right fit when:</h3>
<ul>
<li>You have <b>several services</b>, and each one needs its own explanation.</li>
<li>You want to <b>show up on Google</b> for several different searches. Each page gets its own chance to appear.</li>
<li>You have content that changes: news, schedules, promotions, or projects.</li>
</ul>

<h2>What if you need sign-ups or sales?</h2>
<p>Organizations, institutions, and class providers that take sign-ups need a <b>website with sign-ups &amp; mailing list</b>: a sign-up form, user accounts, and automatic emails. And if you sell products, you need an <b>online store</b> with a cart, credit card payments, and shipping.</p>

<h2>A quick comparison</h2>
<div class="g-scroll"><table class="g-table">
<thead><tr><th></th><th>Landing page</th><th>Website with admin panel</th></tr></thead>
<tbody>
<tr><th>My price</th><td>From ₪2,000</td><td>From ₪4,000</td></tr>
<tr><th>Pages</th><td>One page, up to 7 sections</td><td>Up to 6 pages</td></tr>
<tr><th>Delivery time</th><td>Up to 4 working days</td><td>Up to 10 working days</td></tr>
<tr><th>Updating it yourself</th><td>I handle small changes</td><td>You update the content that changes</td></tr>
<tr><th>Best for</th><td>One service, a promotion, a quick start</td><td>Several services, content, and a presence on Google</td></tr>
</tbody></table></div>

<h2>My tip</h2>
<p>Not sure? <b>You can start with a landing page and expand it into a full website later</b>, without throwing away what’s already been built. And if it’s still not clear, answer 3 questions in the price calculator on my website and get a recommendation for the type that fits.</p>
'''
lp_faq = [
    ('Can I start with a landing page and expand later?', 'Yes. The landing page becomes the home page, or one of the pages, of the full website, so you don’t start from scratch.'),
    ('Will a landing page show up on Google?', 'Yes, but usually for fewer searches than a full website, because it has less content and fewer pages.'),
    ('What’s the difference in price?', 'My landing pages start at ₪2,000, and a website with admin panel starts at ₪4,000. Prices are final, and no VAT is added.'),
]

G = [
    ('website-cost', 'How much does a business website cost? Prices and what drives them',
     'What a business website costs in Israel: prices for a landing page, a website with admin panel, a website with sign-ups & mailing list, and an online store, plus what makes a site more expensive and the ongoing costs.',
     'How much does a business website cost?', 'Real prices, what drives them, and how to see the design before you commit to the full price.', cost_body, cost_faq),
    ('before-you-build', 'What to prepare before building a website: 9 things that save time and money',
     'A list of 9 things worth preparing before you build a website: sites you like, a goal, your audience, a dark or light background, a logo, photos, content, a domain, a timeline, and a budget. Plus how to reach out the right way.',
     'What to prepare before you build a website', 'Here are 9 things that get you to a website that fits you perfectly, faster. None of them are required, but each one helps.', prep_body, prep_faq),
    ('landing-or-website', 'Landing page or full website: which is right for your business?',
     'The difference between a landing page and a full website: who each one suits, prices, delivery times, and a quick comparison. Plus when you need a website with sign-ups & mailing list or an online store.',
     'Landing page or full website?', 'What sets them apart, who each one is for, and how to decide without paying for what you don’t need.', lp_body, lp_faq),
]

for slug, title, desc, h1, lead, body, faq in G:
    related = [(s, h) for s, _, _, h, *_ in G if s != slug]
    with open(f'{OUT}/{slug}.html', 'w', encoding='utf-8') as f:
        f.write(page(slug, title + ' | YYG', desc, h1, lead, body, faq, related))

cards = '\n'.join(f'<a class="g-card" href="/en/guides/{s}.html"><b>{h}</b><span>{l}</span></a>' for s, _, _, h, l, *_ in G)
with open(f'{OUT}/index.html', 'w', encoding='utf-8') as f:
    f.write(page('index', 'Guides to building a business website | YYG',
        'Short guides for business owners and institutions: what a website costs, what to prepare before you build, and whether you need a landing page or a full website.',
        'Guides to building a website', 'Short, clear answers to the questions business owners ask before they build a website.',
        f'<div class="g-cards">\n{cards}\n</div>'))

print('ok', [s for s, *_ in G] + ['index'])
