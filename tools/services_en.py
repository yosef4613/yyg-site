"""English versions of the service pages (LTR), written to /en/services/.

Mirrors tools/services.py: same template, CSS classes and structure, translated
content. It does not import services.py (that script writes the Hebrew pages
when it runs) and never writes outside /en/services/.
Usage: python3 tools/services_en.py
"""
import html, json, os, pathlib, re

ROOT = pathlib.Path(__file__).resolve().parent.parent
OUT = ROOT / 'en' / 'services'
SITE = 'https://yygweb.com'
os.makedirs(OUT, exist_ok=True)

# Same block as STYLE in guides.py (read without running it, like services.py),
# flipped for LTR the same way as guides_en.py: table cells align left.
STYLE = re.search(r"STYLE = '''(.*?)'''", (ROOT / 'tools' / 'guides.py').read_text(encoding='utf-8'), re.S).group(1)
assert STYLE.count('text-align:right') == 1, 'STYLE in guides.py changed, check the LTR flip'
STYLE = STYLE.replace('text-align:right', 'text-align:left')


def esc(s):
    # Escapes text and double-quoted attribute values; apostrophes stay as typed.
    return html.escape(s, quote=False).replace('"', '&quot;')


COMMON_INCL = [
    'Start with 10% and get 3 complete design directions, with your own content',
    '3 revision rounds',
    'All the copy: written with the help of AI, personally edited by me and approved by you',
    'Fully mobile-friendly and fast to load',
    "Full Google readiness: titles, descriptions, a sitemap, structured data and registering the site in Google's tools",
    'Accessibility adjustments and an accessibility statement',
    'Domain, hosting and business email for the first year. The domain is registered in your name',
    '4 months of support after launch',
]
HOW = '''<h2>How it works</h2>
<ol>
<li><b>A short call</b>: what your business does, who it serves and what the website needs to achieve. Then you get a formal quote.</li>
<li><b>Start with 10%</b>: and get 3 designs with your own content. You scroll through them on your phone, just like a real website.</li>
<li><b>You choose a direction</b>: you pay another 40%, and we move on to the build.</li>
<li><b>Build and review</b>: you review and approve everything, and pay the remaining 50% before the site goes live.</li>
<li><b>Launch and training</b>, plus 4 months of support.</li>
</ol>'''


def ul(items):
    return '<ul>' + ''.join(f'<li>{i}</li>' for i in items) + '</ul>'


PAGES = [
    dict(
        slug='landing-page', kw='Landing page design',
        title='Landing page design for your business from ₪2,000 | Start with 10%, 3 designs | YYG',
        desc='Landing page design that brings in customers: start with 10% and see 3 designs, 3 revision rounds, and live within 4 business days. From ₪2,000, final price, no VAT is added, including the first year of domain and hosting.',
        h1='Landing page design that brings in customers', lead='One focused, fast page where everything leads to a single action: calling you or leaving their details.',
        price='2,000', price_n=2000, service='Landing page design',
        body='''<h2>Who a landing page is for</h2>
<ul>
<li>A business with <b>one main service</b> or a specific promotion.</li>
<li>Anyone advertising with a flyer, an ad or on Google who wants everyone who arrives to do one thing.</li>
<li>A new business that wants to <b>get started fast</b> on a small budget, and expand to a full website later.</li>
</ul>
<h2>What makes a good landing page</h2>
<ul>
<li><b>One clear headline</b>: who it's for, and what they get.</li>
<li><b>Proof</b>: your work, testimonials or numbers, before you ask for anything.</li>
<li><b>One button</b> that stands out, and appears again at the end of the page.</li>
<li><b>Speed on a phone</b>: most visitors are on their phones, and every second of waiting costs you customers.</li>
</ul>
<h2>What's included</h2>
{incl}
<p>One page with up to 7 sections, a contact form and a call button that's always visible. <b>Delivery time: up to 4 business days</b> from the day I receive the materials.</p>
<h2>Price</h2>
<p><b>From ₪2,000</b>, final price, no VAT is added. The final price depends on what the page needs, and it appears in a formal quote before we start. A second language (Hebrew or English): ₪500.</p>
{how}''',
        faq=[('How long does it take to build a landing page?', 'Up to 4 business days from the day I receive all the materials, not counting days spent waiting for your reply.'),
             ('Can I expand the landing page into a full website later?', "Yes. The landing page becomes the home page or one of the pages of the website, so we don't start from scratch."),
             ('Does a landing page show up on Google?', "Yes. It's built ready for Google, but it usually shows up for fewer searches than a full website, because it has fewer pages."),
             ('What do I need to prepare?', 'Your logo, a sentence about your business, the service you want to promote, and photos if you have them. I write the full copy.')],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='business-website', kw='Small business website design',
        title='Small business website design with an admin panel from ₪4,000 | Start with 10%, 3 designs | YYG',
        desc='Small business website design with an admin panel you update yourself: up to 6 pages, start with 10% and see 3 designs, and live within 10 business days. From ₪4,000, final price, no VAT is added.',
        h1='Small business website design you can update yourself', lead='A multi-page business website with a simple admin panel for updating whatever changes: services, prices, promotions and photos.',
        price='4,000', price_n=4000, service='Small business website design',
        body='''<h2>Who a website with an admin panel is for</h2>
<ul>
<li>A business with <b>several services</b>, each needing its own explanation.</li>
<li>Anyone who wants to <b>show up on Google for several searches</b>: each page gets its own chance.</li>
<li>Anyone who updates content: promotions, recipes, projects, news or schedules.</li>
</ul>
<h2>What's included</h2>
{incl}
<p>Up to 6 pages, an admin panel for updating the content that changes, forms and a content section, and one-on-one training with a written guide. <b>Delivery time: up to 10 business days</b>.</p>
<h2>An example from my work</h2>
<p>For HaKef SheBaAfiya (boutique dessert catering), I built a website with a catalog organized by category, a bar planner that sends the business an organized request, and a recipe template the owner updates on her own. The new site loads three times faster on a phone than the old one. <a class="text-link" href="/en/#work">Read the full story</a></p>
<h2>Price</h2>
<p><b>From ₪4,000</b>, final price, no VAT is added. Each additional page beyond 6: ₪350. A second language (Hebrew or English): ₪1,000.</p>
{how}''',
        faq=[('How long does it take to build a business website?', 'Up to 10 business days from the day I receive all the materials.'),
             ('Will I be able to update the website myself?', "Yes. There's a simple admin panel for updating the content that changes, and I walk you through it in a one-on-one session and a written guide."),
             ('Whose name are the website and domain in?', 'Yours. The website, the domain and the content belong to you, and you can move to another company at any time.'),
             ('Is there a monthly fee?', "No. The first year of domain, hosting and business email is included. After that, All-in Care is ₪590 a year if you'd like me to take care of everything.")],
        related=['landing-page', 'online-store'],
    ),
    dict(
        slug='online-store', kw='Online store development',
        title='Online store development from ₪7,000 | Up to 30 products included | YYG',
        desc='Online store development with a cart, credit card payments and shipping, plus product, inventory and order management. Up to 30 products that I add for you. Start with 10% and see 3 designs. From ₪7,000, final price, no VAT is added.',
        h1='Online store development that makes buying easy', lead="A catalog, a cart and credit card payments, with simple management of products, inventory and orders. Built for phones, because that's where people shop.",
        price='7,000', price_n=7000, service='Online store development',
        body='''<h2>What's included in your store</h2>
{incl}
<ul>
<li>A shopping cart and credit card payments (the payment processor's fees are separate).</li>
<li>Shipping, returns, terms of service and a privacy policy.</li>
<li>Product, inventory and order management, with one-on-one training.</li>
<li><b>Up to 30 products that I add for you.</b> Each additional 10 products: ₪200.</li>
</ul>
<p><b>Delivery time: up to 20 business days.</b> It's a good idea to open an account with a payment processor right at the start, because approval takes a few days.</p>
<h2>Price</h2>
<p><b>From ₪7,000</b>, final price, no VAT is added. A second language (Hebrew or English): ₪1,800.</p>
{how}''',
        faq=[('How much does it cost to build an online store?', 'With me, from ₪7,000, final price, no VAT is added. That includes up to 30 products that I add for you, plus the first year of domain, hosting and business email.'),
             ('How do I accept credit card payments?', 'Through an Israeli payment processor. You open an account with them, and I connect it to your store. Their fees are paid to them directly.'),
             ('Can I add products myself?', "Yes. There's simple management of products, inventory and orders, and I walk you through it.")],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='institutions', kw='Website for institutions, nonprofits and classes',
        title='Website for institutions, nonprofits and classes, with sign-ups and a mailing list | YYG',
        desc='A website for institutions, nonprofits and classes: a sign-up form, user accounts, a mailing list and 3 email templates. Content and images suited to your community. From ₪6,000, final price, no VAT is added.',
        h1='Website for institutions, nonprofits and classes', lead='A website that showcases your activities and takes organized sign-ups: parents and participants find what suits them and sign up right from their phones.',
        price='6,000', price_n=6000, service='Website for institutions, nonprofits and classes',
        body='''<h2>What an institution's website needs</h2>
<ul>
<li><b>Finding things fast</b>: a class, a lesson or an activity by age, location or topic.</li>
<li><b>Easy sign-up</b>: a full registration form, with email confirmation.</li>
<li><b>Keeping everyone updated</b>: an opt-in mailing list and ready-made email templates.</li>
<li><b>Content and images suited to your community</b>, and a clean site that's easy to approve on filtered internet.</li>
</ul>
<h2>What's included</h2>
{incl}
<p>Up to 8 pages, sign-ups, user accounts, a mailing list and 3 email templates. An admin panel for content, inquiries and subscribers. <b>Delivery time: up to 15 business days</b>.</p>
<h2>An example from my work</h2>
<p>For the Pirchei Shomayim Boys' Choir, I built a website with a class search by the child's age and city, a full registration form with the option to open an account, a song library with a player that doesn't slow the site down, and an English version. Before the build, they saw five designs and chose their favorite. <a class="text-link" href="/en/#work">See the full project</a></p>
<h2>Price</h2>
<p><b>From ₪6,000</b>, final price, no VAT is added. A second language (Hebrew or English): ₪1,500.</p>
{how}''',
        faq=[('Can the website take sign-ups and payments?', 'Sign-ups, yes, including email confirmation. Credit card payments can also be connected through a payment processor, as an add-on listed in the quote.'),
             ('Will the website suit a Torah-observant community?', "Yes. The content and images are chosen to suit your community, and the site is built clean and simple, so it's easy to approve on filtered internet."),
             ('Can I send updates to the people who signed up?', "Yes. There's an opt-in mailing list and 3 ready-made email templates, and you send the updates yourself.")],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='logo-branding', kw='Logo design and branding',
        title='Logo design and branding for your business from ₪590 | A full brand with a website | YYG',
        desc='Logo design and branding: a basic logo for ₪590, a professional logo with 3 sketches for ₪1,500, basic branding for ₪2,500, and Brand from 0 to 100% with a website, content, print and Google from ₪5,900. Final prices, no VAT is added.',
        h1='Logo design and branding for your business', lead='From a simple logo for a new business to a complete brand: logo, colors, fonts, website and content, all in one visual language.',
        price='590', price_n=590, service='Logo design and branding',
        body='''<h2>Logo, branding or a full brand: what's the difference?</h2>
<p>A <b>logo</b> is your business's mark. <b>Branding</b> is how your business looks everywhere: logo, colors, fonts and a business card. A <b>full brand</b> is everything ready to go: branding, website, content, print, social media and Google, and all you do is approve.</p>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>What</th><th>Price</th><th>What's included</th></tr></thead>
<tbody>
<tr><td>Basic logo</td><td>₪590</td><td>One sketch, one revision round, files for your website and social media</td></tr>
<tr><td>Professional logo</td><td>₪1,500</td><td>3 sketches, 3 revision rounds, all the files: vector, transparent background, a one-color version and an icon</td></tr>
<tr><td>Basic branding</td><td>₪2,500</td><td>A professional logo, colors, fonts, a style guide and a business card</td></tr>
<tr><td>Full brand + landing page</td><td>From ₪5,900</td><td>Branding, website, all the content, a business card and flyer, social media images, Google and 4 months of support</td></tr>
<tr><td>Full brand + website</td><td>From ₪7,900</td><td>Same as above, with a website with an admin panel (with sign-ups from ₪9,900, with a store from ₪10,900)</td></tr>
</tbody></table></div>
<p>All prices are final, and no VAT is added.</p>
<h2>Why it pays to start with the logo</h2>
<p>The website is designed around the logo: the colors, the fonts and the feel all grow out of it. When you start with the branding, your website, business card and sign look like one business, not a collection of unrelated pieces.</p>
<h2>How it works</h2>
<ol>
<li><b>A discovery call</b>: what your business does, who it serves, what sets you apart, and which colors and styles you like.</li>
<li><b>Sketches</b>: one direction for a basic logo, 3 directions for a professional logo and for branding.</li>
<li><b>Choosing and revisions</b>, and then files ready for every use.</li>
</ol>''',
        faq=[('How much does logo design cost?', 'A basic logo with one sketch costs ₪590. A professional logo with 3 sketches, 3 revision rounds and all the files costs ₪1,500. Final prices, no VAT is added.'),
             ("What's the difference between a logo and branding?", 'A logo is the mark. Branding is the whole visual language: logo, colors, fonts, a style guide and a business card, so your business looks the same everywhere.'),
             ('What is Brand from 0 to 100%?', 'You bring the idea for the business, and I build everything else: branding, website, all the content, print, social media and a Google Business Profile.'),
             ('Do I need a business name first?', 'Yes. The packages assume your business already has a name. Coming up with a name is separate work, with its own quote.')],
        related=['landing-page', 'business-website'],
    ),
]
NAMES = {p['slug']: p['h1'] for p in PAGES}


def page(p, is_index=False, cards=''):
    slug = 'index' if is_index else p['slug']
    url = f'{SITE}/en/services/' if is_index else f'{SITE}/en/services/{slug}.html'
    he_path = '/services/' if is_index else f'/services/{slug}.html'
    he_url = SITE + he_path
    title, desc, h1, lead = p['title'], p['desc'], p['h1'], p['lead']
    ld = []
    if not is_index:
        ld.append({'@context': 'https://schema.org', '@type': 'Service', 'name': p['service'], 'serviceType': p['kw'],
                   'description': desc, 'url': url, 'areaServed': {'@type': 'Country', 'name': 'Israel'}, 'inLanguage': 'en',
                   'provider': {'@type': 'ProfessionalService', 'name': 'YYG', 'url': SITE + '/en/', 'telephone': '+972-54-854-0700'},
                   'offers': {'@type': 'Offer', 'price': str(p['price_n']), 'priceCurrency': 'ILS', 'url': url}})
        ld.append({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': [
            {'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}} for q, a in p['faq']]})
    ld.append({'@context': 'https://schema.org', '@type': 'BreadcrumbList', 'itemListElement': [
        {'@type': 'ListItem', 'position': 1, 'name': 'YYG', 'item': SITE + '/en/'},
        {'@type': 'ListItem', 'position': 2, 'name': 'Services', 'item': SITE + '/en/services/'}]
        + ([] if is_index else [{'@type': 'ListItem', 'position': 3, 'name': h1, 'item': url}])})
    if is_index:
        body = cards
    else:
        body = p['body'].replace('{incl}', ul(COMMON_INCL)).replace('{how}', HOW)
        body += '\n<h2>Frequently asked questions</h2>\n' + '\n'.join(f'<details><summary>{esc(q)}</summary><p>{esc(a)}</p></details>' for q, a in p['faq'])
        body += '<aside class="g-rel" aria-labelledby="rel-t"><h2 id="rel-t">More services</h2><ul>' + ''.join(
            f'<li><a class="text-link" href="/en/services/{s}.html">{NAMES[s]}</a></li>' for s in p['related']) + \
            '<li><a class="text-link" href="/en/guides/website-cost.html">How much does a business website cost?</a></li></ul></aside>'
    return f'''<!doctype html>
<html lang="en" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<script>if(location.protocol==="http:"&&location.hostname==="yygweb.com")location.replace("https://yygweb.com"+location.pathname+location.search+location.hash)</script>
<title>{esc(title)}</title>
<meta name="description" content="{esc(desc)}">
<link rel="canonical" href="{url}">
<link rel="alternate" hreflang="he" href="{he_url}">
<link rel="alternate" hreflang="en" href="{url}">
<link rel="alternate" hreflang="x-default" href="{he_url}">
<meta property="og:type" content="website">
<meta property="og:locale" content="en_US">
<meta property="og:locale:alternate" content="he_IL">
<meta property="og:url" content="{url}">
<meta property="og:title" content="{esc(h1)}">
<meta property="og:description" content="{esc(desc)}">
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
   <a class="text-link" href="/en/services/">Services</a>
   <a class="text-link" href="/en/#plan">Price calculator</a>
   <a class="text-link" href="/en/#contact">Contact</a>
   <a class="text-link" href="{he_path}" hreflang="he" lang="he">עברית</a>
  </nav>
 </div>
</header>
<main id="main" class="g-main">
 <div class="wrap">
  <p class="g-crumb"><a href="/en/">YYG</a> › <a href="/en/services/">Services</a></p>
  <h1>{h1}</h1>
  <p class="g-lead">{lead}</p>
{body}
  <section class="g-cta" aria-labelledby="cta-t">
   <h2 id="cta-t">Want to know what it will cost you?</h2>
   <p>Answer 3 quick questions in the price calculator, no contact details required. Or leave your details, and I'll get back to you Sunday to Wednesday, 10:00–13:30 (Israel time).</p>
   <a class="btn btn-gold" href="/en/?src=svc-{slug}#plan">Open the price calculator</a>
   <p style="margin-top:12px"><a class="text-link" href="/en/#contact">Or leave your details</a> · <a class="text-link" href="tel:0548540700">054-854-0700</a> (calls only)</p>
  </section>
 </div>
</main>
<footer class="foot">
 <div class="wrap foot-in">
  <p class="foot-brand"><img src="/assets/img/logo-160.webp" width="160" height="118" alt="" loading="lazy"> YYG, Yehuda Gordon. Web design and development</p>
  <p><a class="foot-link" href="/en/services/">Services</a> · <a class="foot-link" href="/en/guides/">Guides</a> · <a class="foot-link" href="/en/accessibility.html">Accessibility statement</a> · <a class="foot-link" href="/en/privacy.html">Privacy policy</a></p>
 </div>
</footer>
<script>if(location.hostname==="yygweb.com"&&navigator.sendBeacon){{try{{var q=new URLSearchParams(location.search);navigator.sendBeacon("/hit.php",new Blob([JSON.stringify({{e:"view",p:location.pathname,r:document.referrer,s:q.get("src")||""}})],{{type:"application/json"}}))}}catch(e){{}}}}</script>
</body>
</html>
'''


for p in PAGES:
    (OUT / f"{p['slug']}.html").write_text(page(p), encoding='utf-8')
cards = '<div class="g-cards">\n' + '\n'.join(
    f'<a class="g-card" href="/en/services/{p["slug"]}.html"><b>{p["h1"]}</b><span>{p["lead"]}</span></a>' for p in PAGES) + '\n</div>'
(OUT / 'index.html').write_text(page(dict(title='Services: website design, online stores, logo design and branding | YYG',
    desc="All of YYG's services: landing page design, small business website design, online store development, websites for institutions, nonprofits and classes, and logo design and branding. Start with 10% and see 3 designs, with final prices, and no VAT is added.",
    h1='Services', lead='Websites, online stores, logos and branding for small businesses and institutions. With every service, you start with 10% and see the design before you go ahead.'), True, cards), encoding='utf-8')
print('ok', [p['slug'] for p in PAGES] + ['index'])
