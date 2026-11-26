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
TODAY = '2026-11-26'

# Same block as STYLE in guides.py, flipped for LTR (table cells align left).
STYLE = '''<style>
 .g-top{border-bottom:1px solid var(--line);background:var(--base)}
 .g-top .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:72px}
 .g-top img{height:48px;width:auto;max-width:none} .g-top .wrap>a:first-child{flex-shrink:0}
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

FOOT_SV = ' · '.join(f'<a class="foot-link" href="/en/services/{k}.html">{v}</a>' for k, v in [('landing-page', 'Landing pages'), ('business-website', 'Business websites'), ('online-store', 'Online stores'), ('institutions', 'Institutions and classes'), ('catering', 'Catering and food'), ('professionals', 'Trades and services'), ('logo-branding', 'Logo and branding')])


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
        'publisher': {'@type': 'Organization', 'name': 'YYG Web Design', 'logo': {'@type': 'ImageObject', 'url': SITE + '/assets/img/yyg-logo-320.webp'}},
        'image': SITE + '/assets/img/og-4.jpg',
    }]
    if faq:
        ld.append({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': [
            {'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}} for q, a in faq]})
    ld.append({'@context': 'https://schema.org', '@type': 'BreadcrumbList', 'itemListElement': [
        {'@type': 'ListItem', 'position': 1, 'name': 'YYG Web Design', 'item': SITE + '/en/'},
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
<meta property="og:image" content="{SITE}/assets/img/og-4.jpg">
<meta name="theme-color" content="#151920">
<link rel="icon" href="/assets/img/yyg-icon-96.png" type="image/png">
<link rel="preload" href="/assets/fonts/karantina-700-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/site.css?v=57">
{STYLE}
<script type="application/ld+json">{json.dumps(ld, ensure_ascii=False, separators=(',', ':'))}</script>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="g-top">
 <div class="wrap">
  <a href="/en/"><img src="/assets/img/yyg-logo-160.webp" width="160" height="122" alt="YYG Web Design, home page"></a>
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
  <p class="g-crumb"><a href="/en/">YYG Web Design</a> › <a href="/en/guides/">Guides</a></p>
  <h1>{h1}</h1>
  <p class="g-lead">{lead}</p>
  <p class="g-meta">Yehuda Gordon, web designer · Updated November 2026</p>
{body}
{faq_html}
  <section class="g-cta" aria-labelledby="cta-t">
   <h2 id="cta-t">Want to know what your website will cost?</h2>
   <p>The discovery questionnaire takes about 10 minutes, and at the end you see a price estimate from your answers right away. And if we’re a good fit, we start with 30%, and I’ll prepare 3 design directions for your website before you go ahead.</p>
   <a class="btn btn-gold" href="/en/questionnaire/" data-q="src=guide-{slug}">Get a quote: the questionnaire</a>
   <p style="margin-top:12px"><a class="text-link" href="/en/#plan" data-q="src=guide-{slug}">Just a ballpark: 3 questions in the calculator</a></p>
  </section>
{rel}
 </div>
</main>
<section class="news" aria-labelledby="news-title">
 <div class="wrap news-in">
  <div class="news-copy"><h2 id="news-title" class="news-t">A new guide once a month, straight to your inbox</h2><p class="news-s">Short tips for business owners, like this guide. No spam, and you can unsubscribe in one click.</p></div>
  <form class="news-form" method="post" action="/account/list.php">
   <input type="hidden" name="source" value="guide-en-{slug}"><input type="hidden" name="lang" value="en">
   <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
   <label class="field"><span>Email</span><input type="email" name="email" autocomplete="email" dir="ltr" required></label>
   <label class="news-consent"><input type="checkbox" name="consent" value="1" required><span>I agree to receive guides, tips and updates from YYG Web Design by email, including service offers. I can unsubscribe at any time.</span></label>
   <button class="btn btn-line" type="submit">Join</button>
  </form>
 </div>
</section>
<footer class="foot">
 <div class="wrap foot-in">
  <p class="foot-brand"><img src="/assets/img/yyg-logo-160.webp" width="160" height="122" alt="" loading="lazy"> YYG Web Design, Yehuda Gordon. Web design and development</p>
  <p><a class="foot-link" href="/en/services/">Services</a> · <a class="foot-link" href="/en/guides/">Guides</a> · <a class="foot-link" href="/en/accessibility.html">Accessibility statement</a> · <a class="foot-link" href="/en/privacy.html">Privacy policy</a> · <a class="foot-link" href="/en/terms.html">Terms</a> · <a class="foot-link" href="/account/?lang=en">My account</a></p>
  <p>{FOOT_SV}</p>
 </div>
</footer>
<script src="/assets/q.js" defer></script>
<script>if(location.hostname==="yygweb.com"&&navigator.sendBeacon){{try{{var q=new URLSearchParams(location.search);navigator.sendBeacon("/hit.php",new Blob([JSON.stringify({{e:"view",p:location.pathname,r:document.referrer,s:q.get("src")||""}})],{{type:"application/json"}}))}}catch(e){{}}}}</script>
</body>
</html>
'''


# ---------- Guide 1: what a website costs ----------
cost_body = '''
<h2>The short answer</h2>
<p>The price of a website depends mainly on three things: <b>how many pages</b> it has, <b>what the site needs to do</b> (just present information, take sign-ups, or sell), and <b>who updates it</b> afterward. Here are my starting prices, so you have a reference point. No VAT is added. The exact price depends on your needs and requirements, and it always comes in a clear written quote by email before we begin:</p>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>Type of website</th><th>Price</th><th>Delivery time</th><th>Best for</th></tr></thead>
<tbody>
<tr><td>Basic landing page</td><td>From ₪1,490</td><td>Up to 4 working days</td><td>One simple page, with one design and your own copy</td></tr>
<tr><td>Landing page</td><td>From ₪2,290</td><td>Up to 7 working days</td><td>A business with one service or promotion that wants the phone to ring</td></tr>
<tr><td>Business website</td><td>From ₪4,900</td><td>Up to 14 working days</td><td>A business with several services that wants to update its own content</td></tr>
<tr><td>Extended business website</td><td>From ₪6,900</td><td>Up to 20 working days</td><td>Up to 12 pages and a content section: news, articles, or events</td></tr>
<tr><td>Online store</td><td>From ₪7,900</td><td>Up to 25 working days</td><td>Anyone selling products, with credit card payments and shipping</td></tr>
<tr><td>Custom system or large store</td><td>By quote</td><td>By spec</td><td>A complex user area, connections to other systems, or more than 100 products</td></tr>
</tbody></table></div>
<p>Sign-ups, schedules, a mailing list, user accounts, and donations are modules you add to a site as needed, from ₪700 (details in <a class="text-link" href="/en/services/institutions.html">websites for institutions, nonprofits and classes</a>). A second language is 30% of the package price: ₪700 for a landing page, up to ₪2,400 for an online store.</p>

<h2>What makes a website more expensive, and what doesn’t</h2>
<h3>1. The number of pages</h3>
<p>Every page needs design, content, and testing on a phone. A landing page is one long page. A typical business website usually has 4 to 8 pages: home, services, about, FAQ, and contact.</p>
<h3>2. What the site does</h3>
<p>A site that only presents information is the least expensive. As soon as it needs to take sign-ups, send emails, manage user accounts, or accept payments, there’s more to build and more to test.</p>
<h3>3. Who updates the site</h3>
<p>If your content hardly ever changes, you can skip the update screen and save. If you post news, promotions, products, or schedules, an update screen is worth it: you make the updates yourself, without waiting on anyone.</p>
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
<p>I include <b>the first year of domain, hosting, and business email</b> in the price of the website. From the second year on, you choose: All-in Care, where I take care of everything for ₪590 a year (₪890 for a store), or a move to a hosting provider of your own. If you’d like me to make small changes for you, you can add maintenance of up to half an hour a month, for ₪199 a month. Either way, the domain is registered <b>in your name</b>, so the website is truly yours.</p>

<h2>How to avoid committing to a website you haven’t seen</h2>
<p>The biggest risk in building a website is paying a lot up front and finding out at the end that the design isn’t what you had in mind. With me, you pay in stages, and you see the design before you go ahead:</p>
<ol>
<li>After a short call and a formal quote, you pay <b>30%</b> to start, and for that you already get <b>3 complete design directions</b> for your website, using your own content.</li>
<li>Love one of the directions? You pay another 40%, and we move on to the build. And if you decide not to go ahead, the 30% covers the design work, and you don’t pay anything more.</li>
<li>You pay the remaining 30% at the end, <b>after you’ve reviewed and approved everything</b>, before the site goes live.</li>
</ol>
<p>With a basic landing page, which has one design, you pay 50% at the start and 50% before the site goes live.</p>
'''
cost_faq = [
    ('How much does a landing page cost?', 'My landing pages start at ₪2,290, and no VAT is added. That includes 3 design directions, 3 revision rounds, a contact form, and a click-to-call button, and it’s ready within 7 working days. A basic landing page, with one design and your own copy, starts at ₪1,490. The exact price depends on what the page needs, and it comes in the quote.'),
    ('Is VAT added to the price?', "No. I don't add VAT to my prices: the price in your quote is the price you pay."),
    ('Is there a monthly fee?', 'It isn’t required. The first year of domain, hosting, and business email is included in the price of the website. After that, All-in Care is ₪590 a year (₪890 for a store) if you’d like me to take care of everything. Maintenance of up to half an hour a month is your choice, for ₪199 a month.'),
    ('What if I need more pages later?', 'You can add pages even after the site is live. Each page beyond your package costs ₪400.'),
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
<li>You want to <b>get started fast</b> (with me, up to 7 working days) and on a small budget (from ₪2,290).</li>
<li>You’re running ads or handing out flyers, and you want everyone who arrives to do one thing.</li>
</ul>

<h2>What is a full website?</h2>
<p>Several pages with a menu: home, services, about, FAQ, contact, and sometimes a content section too. A business website has an update screen, where you update anything that changes on your own.</p>
<h3>A full website is the right fit when:</h3>
<ul>
<li>You have <b>several services</b>, and each one needs its own explanation.</li>
<li>You want to <b>show up on Google</b> for several different searches. Each page gets its own chance to appear.</li>
<li>You have content that changes: news, schedules, promotions, or projects.</li>
</ul>

<h2>What if you need sign-ups or sales?</h2>
<p>Organizations, institutions, and class providers that take sign-ups add <b>modules</b> to the site: an advanced sign-up form, a mailing list, user accounts, or donations, each with its own price. A site with a lot of content, up to 12 pages and a section for news, articles, or events, is an <b>extended business website</b>. And if you sell products, you need an <b>online store</b> with a cart, credit card payments, and shipping.</p>

<h2>A quick comparison</h2>
<div class="g-scroll"><table class="g-table">
<thead><tr><th></th><th>Landing page</th><th>Business website</th></tr></thead>
<tbody>
<tr><th>My starting price</th><td>From ₪2,290</td><td>From ₪4,900</td></tr>
<tr><th>Pages</th><td>One page, up to 7 sections</td><td>Up to 6 pages</td></tr>
<tr><th>Delivery time</th><td>Up to 7 working days</td><td>Up to 14 working days</td></tr>
<tr><th>Updating it yourself</th><td>I handle small changes, or an update screen for ₪1,000</td><td>You update the content that changes</td></tr>
<tr><th>Best for</th><td>One service, a promotion, a quick start</td><td>Several services, content, and a presence on Google</td></tr>
</tbody></table></div>

<h2>My tip</h2>
<p>Not sure? <b>You can start with a landing page and expand it into a full website later</b>, without throwing away what’s already been built. And if it’s still not clear, answer 3 questions in the price calculator on my website and get a recommendation for the type that fits.</p>
'''
lp_faq = [
    ('Can I start with a landing page and expand later?', 'Yes. The landing page becomes the home page, or one of the pages, of the full website, so you don’t start from scratch.'),
    ('Will a landing page show up on Google?', 'Yes, but usually for fewer searches than a full website, because it has less content and fewer pages.'),
    ('What’s the difference in price?', 'My landing pages start at ₪2,290, and a business website starts at ₪4,900. No VAT is added to the prices, and the exact price comes in the quote.'),
]

# ---------- Guide 4: how to choose who builds your website ----------
choose_body = '''
<h2>Why ask before you pay</h2>
<p>Most website disappointments don't start with the design. They start with a different understanding of what exactly you're buying: who writes the copy, how many revisions are included, and what it costs after the first year. A fifteen-minute call with these questions saves months of misunderstandings. You can ask them of any web designer, even if you don't end up choosing me.</p>

<h2>The 10 questions</h2>
<h3>1. What exactly is included in the price?</h3>
<p>How many pages, who writes the copy, whether there's a form, mobile optimization, Google readiness and an accessibility statement. A good answer is a written list in the quote, not "everything is included".</p>
<h3>2. Does the price include VAT?</h3>
<p>"From ₪3,000" can become ₪3,540 once VAT is added. Ask for the full price, as one number. I don't add VAT to my prices.</p>
<h3>3. Will I see a design before I pay most of the price?</h3>
<p>Ask how many designs you'll see, and what you pay at each stage. With me, you start with 30%, and for that you already see 3 complete designs with your own content. Only after you choose do you pay another 40%, and if you decide not to go ahead, you don't pay anything more.</p>
<h3>4. How many revision rounds are included, and what counts as a revision?</h3>
<p>Changing a color, a photo or some text is a revision. Changing the whole structure after you've approved it is new work. A good answer defines the number of rounds, and what goes into them.</p>
<h3>5. Whose name will the domain and website be in?</h3>
<p>The domain should be registered in your name, and the website should be yours, so you can move to another company without losing it. This is one of the most important questions, because it's hard to fix later.</p>
<h3>6. What does it cost after the first year?</h3>
<p>Domain, hosting, business email, plugin licenses and maintenance. Ask for one yearly number. With me, the first year is included, and after that it's ₪590 a year with All-in Care (₪890 for a store), or with your own hosting company.</p>
<h3>7. Can I update it myself, and who teaches me?</h3>
<p>Ask exactly what you'll be able to change yourself: prices, photos, promotions. And ask whether there's training and a written guide, or whether every change will cost you money.</p>
<h3>8. How long will it take, and from which day do you count?</h3>
<p>"Two weeks" can mean two weeks from the day you pay, or from the day the designer has all the materials. It's worth knowing up front. More on this in the guide <a class="text-link" href="/en/guides/how-long-to-build.html">how long it takes to build a website</a>.</p>
<h3>9. What happens after launch?</h3>
<p>How long support and bug fixes last, who backs up the site, and who you contact if something stops working. With me, there are 4 months of support after launch.</p>
<h3>10. Can I see live websites you've built?</h3>
<p>Not just screenshots: the address of a working site that you can open on your phone and check how fast it loads. And if possible, talk to a client.</p>

<h2>Signs to stop and check</h2>
<ul>
<li>A price without a list of what's included.</li>
<li>A domain registered in the designer's name.</li>
<li>"We'll do the copy later", without saying who will write it.</li>
<li>Pressure to sign today, "because the offer ends".</li>
<li>No live website to show.</li>
</ul>

<h2>How to compare quotes</h2>
<p>Put the quotes side by side, and check each one for:</p>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>What to check</th><th>Why it matters</th></tr></thead>
<tbody>
<tr><td>A full price as one number</td><td>To compare like with like, including VAT</td></tr>
<tr><td>What's included</td><td>Pages, copy, forms, Google and accessibility</td></tr>
<tr><td>Designs and revisions</td><td>How many directions you'll see, and how many times you can revise</td></tr>
<tr><td>Delivery time</td><td>And from which day it's counted</td></tr>
<tr><td>The second year</td><td>What it costs to continue, and what that includes</td></tr>
<tr><td>Ownership</td><td>Whose name the domain and website are in</td></tr>
</tbody></table></div>
<p>The cheapest quote isn't always the cheapest in the end, if you pay for everything separately in the second year. And the most expensive isn't always the best, if it has no clear list of what you get.</p>

<h2>What to ask yourself before you reach out</h2>
<p>The questions for a web designer work best when you know what you need. Five minutes with yourself before the call:</p>
<ul>
<li><b>What should the website achieve?</b> Calls, sign-ups, orders, or getting to know you.</li>
<li><b>Who are your customers?</b> Age, area, and which device they use, usually a phone.</li>
<li><b>Roughly how many pages?</b> One service or many, and whether there's content that changes.</li>
<li><b>Who will update the site?</b> You, someone on your team, or the designer.</li>
<li><b>What's the budget and the deadline?</b> Even a rough range helps you get an accurate quote.</li>
</ul>
<p>And what to ask for in writing: a list of what's included, the full price, the payment stages, the delivery time and from which day it's counted, and what the second year costs. A tidy quote with all of these is a good sign in itself.</p>

<h2>And what about me?</h2>
<p>You'll find my answers to all 10 questions on my website: what each package includes, the starting price and the payment stages, the 3 designs, the 3 revision rounds, delivery times and the second year. Want an exact number? The calculator on my site gives you a recommended package, a list of pages and an estimated price, without leaving your details. You can also read <a class="text-link" href="/en/guides/website-cost.html">how much a business website costs</a> and <a class="text-link" href="/en/guides/before-you-build.html">what to prepare before you build a website</a>, or browse my <a class="text-link" href="/en/services/">services</a>.</p>
'''
choose_faq = [
    ('What is the most important thing to check before choosing a web designer?', "That the domain and website will be in your name, and that there's a written list of what the price includes. Everything else can be fixed later; that's hard to fix."),
    ('Is it OK to pay a deposit?', "Yes, a deposit is normal. It's worth knowing up front what it pays for and when you'll see a design. With me, you start with 30%, and for that you get 3 complete designs. If you decide not to go ahead after the designs, you don't pay anything more."),
    ('How can I tell if a website someone built is fast?', "Open it on your phone, ideally not on fast Wi-Fi, and see how long it takes until the content appears. You can also check it with Google's free PageSpeed Insights tool."),
    ('Is a low price a bad sign?', "Not necessarily. But check what's included, who writes the copy, and what the second year costs. Sometimes that's where the difference is."),
    ('How many quotes should I ask for?', 'Two or three are enough to understand the market. More than that usually just confuses.'),
]

# ---------- Guide 5: how long it takes to build a website ----------
time_body = '''
<h2>The short answer</h2>
<p>The build itself takes days, not months. These are my delivery times, in business days, from the day I have all the materials:</p>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>Type of site</th><th>Delivery time</th><th>Starting price</th></tr></thead>
<tbody>
<tr><td>Basic landing page</td><td>Up to 4 days</td><td>₪1,490</td></tr>
<tr><td><a class="text-link" href="/en/services/landing-page.html">Landing page</a></td><td>Up to 7 days</td><td>₪2,290</td></tr>
<tr><td><a class="text-link" href="/en/services/business-website.html">Business website</a></td><td>Up to 14 days</td><td>₪4,900</td></tr>
<tr><td>Extended business website</td><td>Up to 20 days</td><td>₪6,900</td></tr>
<tr><td><a class="text-link" href="/en/services/online-store.html">Online store</a></td><td>Up to 25 days</td><td>₪7,900</td></tr>
<tr><td><a class="text-link" href="/en/services/institutions.html">A module: sign-ups, donations and more</a></td><td>2 to 4 more days</td><td>From ₪700</td></tr>
<tr><td><a class="text-link" href="/en/services/logo-branding.html">Brand from 0 to 100%</a></td><td>10 more days</td><td>An extra ₪3,900</td></tr>
</tbody></table></div>
<p>No VAT is added to the prices. Days spent waiting for your reply aren't counted. So what really decides how long it takes is mostly how quickly the materials and approvals come in.</p>
<p>In every package, part of the time is for the designs and part is for the build. For a landing page, for example, 3 days for the designs and 4 for the build. Need it sooner? Rush delivery, in half the time, is an extra 25%.</p>

<h2>The stages, one after another</h2>
<ol>
<li><b>A call and a quote</b>: what the business needs, and which package fits.</li>
<li><b>Materials</b>: logo, key points for the copy, photos, and sites you like.</li>
<li><b>3 designs</b> with your own content, and choosing a direction.</li>
<li><b>The build</b>: all the pages, forms and the update screen.</li>
<li><b>Revisions and testing</b> on phone and computer.</li>
<li><b>Connecting the domain, going live, and training</b>.</li>
</ol>

<h2>What slows things down</h2>
<ul>
<li><b>Materials that trickle in</b>: the logo today, photos next week, and text for one page two weeks later.</li>
<li><b>Copy nobody writes</b>: one of the most common delays. With me, I write it, and you just give key points and approve.</li>
<li><b>Too many decision makers</b>: when every change needs three people's approval, each round takes longer. It's worth choosing one person to approve.</li>
<li><b>Changing direction midway</b>: after you've chosen a design and the build has started, changing the whole structure sets the process back.</li>
<li><b>For a store: payment provider approval</b>, which takes a few days, and a product list that isn't ready yet.</li>
<li><b>A domain stuck with a previous provider</b>: transferring a domain can take a few days, and sometimes needs a transfer code from the old provider.</li>
</ul>

<h2>How to speed things up</h2>
<ul>
<li>Prepare the list from the guide <a class="text-link" href="/en/guides/before-you-build.html">what to prepare before you build a website</a> before the first call.</li>
<li>Send all the materials at once, even if they're not perfect.</li>
<li>Reply to each round within a day or two.</li>
<li>If there's a deadline, like a business opening or an event, mention it in the first call.</li>
<li>For a store: open an account with a payment provider on day one.</li>
</ul>

<h2>Example: what the weeks look like for a business website</h2>
<p>Say all the materials arrived on Sunday. In the first 5 days, I write the copy and prepare 3 designs. You choose a direction, and then in the next 9 days I build all the pages and connect the update screen. Toward the end there's a revision round, testing on phone and computer, connecting the domain, and training. If every round gets a reply within a day, it all fits into 14 business days. Each day spent waiting for a reply pushes everything back by a day.</p>

<h2>And a store?</h2>
<p>A store takes longer, mostly because of the preparation, not the build: a product list with prices, descriptions and photos, an account with a payment provider, and a shipping and returns policy. When all of these are ready up front, up to 25 business days is enough even for a store with dozens of products. If one of them is missing, that's what decides when the store goes live.</p>

<h2>What gets checked before launch</h2>
<ul>
<li>Every form sends, and inquiries reach the right inbox.</li>
<li>The site opens fast on a phone, and every button works with a finger.</li>
<li>The copy is approved, with no mistakes in phone numbers, addresses or prices.</li>
<li>The domain is connected, and the site runs on a secure connection.</li>
<li>There's an accessibility statement and a privacy policy.</li>
</ul>
<p>After launch, 4 months of support begin: bug fixes, questions, and small changes.</p>

<h2>What you can do while I build</h2>
<ul>
<li><b>Open a Google Business Profile</b>, so you can link it to the website on launch day.</li>
<li><b>Ask 2 or 3 happy clients for testimonials</b>, with their permission, so they're on the site from day one.</li>
<li><b>Make a list of who to tell</b> when the site goes live: existing clients, groups and suppliers.</li>
<li><b>Plan the next step</b>: a flyer with a QR code, an ad, or a link in your email signature.</li>
</ul>

<h2>And what about Google?</h2>
<p>A new website doesn't appear on Google the day it goes live. Google needs to find it, crawl it and understand what it's about. On launch day I register the site in Google's tools and submit a sitemap, which shortens that time. Showing up in the results usually takes days to weeks, and climbing in the rankings takes longer and depends on content, competition and reviews. That's why it's worth opening a Google Business Profile and asking happy clients for reviews.</p>
<p>Want to know how long your website will take, and which package fits? Answer 3 questions in the calculator, or read <a class="text-link" href="/en/guides/landing-or-website.html">landing page or full website</a> and <a class="text-link" href="/en/guides/choose-web-designer.html">how to choose who builds your website</a>.</p>
'''
time_faq = [
    ('How long does it take to build a landing page?', 'With me, up to 7 business days from the day I have all the materials: 3 for the designs and 4 for the build. A basic landing page, with one design, takes up to 4 business days.'),
    ('Can a website be built in a week?', 'A basic landing page, yes, within 4 business days. A landing page takes up to 7 business days, and a business website up to 14, about three weeks, if the materials and replies come quickly. Need it sooner? Rush delivery, in half the time, is an extra 25%.'),
    ('From which day is the delivery time counted?', "From the day I have all the materials: logo, key points for the copy and photos. Days spent waiting for your reply aren't counted."),
    ('When will the website show up on Google?', 'On launch day the site is registered in Google’s tools. Showing up in the results usually takes days to weeks, and rankings improve over time with good content.'),
    ('What delays a website build the most?', 'Usually not the build itself, but materials that come in slowly, copy nobody writes, and approvals from several people.'),
]

G = [
    ('website-cost', 'How much does a business website cost? Prices and what drives them',
     'What a business website costs in Israel: prices for a landing page, a business website, an extended business website, and an online store, plus what makes a site more expensive and the ongoing costs.',
     'How much does a business website cost?', 'Real prices, what drives them, and how to see the design before you commit to the full price.', cost_body, cost_faq),
    ('before-you-build', 'What to prepare before building a website: 9 things that save time and money',
     'A list of 9 things worth preparing before you build a website: sites you like, a goal, your audience, a dark or light background, a logo, photos, content, a domain, a timeline, and a budget. Plus how to reach out the right way.',
     'What to prepare before you build a website', 'Here are 9 things that get you to a website that fits you perfectly, faster. None of them are required, but each one helps.', prep_body, prep_faq),
    ('landing-or-website', 'Landing page or full website: which is right for your business?',
     'The difference between a landing page and a full website: who each one suits, prices, delivery times, and a quick comparison. Plus when you add sign-ups or need an online store.',
     'Landing page or full website?', 'What sets them apart, who each one is for, and how to decide without paying for what you don’t need.', lp_body, lp_faq),
    ('choose-web-designer', 'How to choose a web designer: 10 questions to ask first',
     '10 questions to ask a web designer before you pay: what is included, VAT, whose name the domain is in, revisions, and the second-year cost.',
     'How to choose who builds your website', '10 questions worth asking before you pay, and what a good answer looks like. Even if you don’t end up choosing me.', choose_body, choose_faq),
    ('how-long-to-build', 'How long does it take to build a business website?',
     'How long it takes to build a business website: landing page, business website, extended website or store. The stages, and what speeds it up or slows it down.',
     'How long does it take to build a business website?', 'Real delivery times, the stages, and what usually slows things down. Most delays have nothing to do with the build itself.', time_body, time_faq),
]

for slug, title, desc, h1, lead, body, faq in G:
    related = [(s, h) for s, _, _, h, *_ in G if s != slug]
    with open(f'{OUT}/{slug}.html', 'w', encoding='utf-8') as f:
        f.write(page(slug, title + ' | YYG Web Design', desc, h1, lead, body, faq, related))

cards = '\n'.join(f'<a class="g-card" href="/en/guides/{s}.html"><b>{h}</b><span>{l}</span></a>' for s, _, _, h, l, *_ in G)
with open(f'{OUT}/index.html', 'w', encoding='utf-8') as f:
    f.write(page('index', 'Guides to building a business website | YYG Web Design',
        'Guides for business owners and institutions: what a website costs, what to prepare, landing page or full site, choosing a web designer, and how long it takes.',
        'Guides to building a website', 'Short, clear answers to the questions business owners ask before they build a website.',
        f'<div class="g-cards">\n{cards}\n</div>'))

print('ok', [s for s, *_ in G] + ['index'])
