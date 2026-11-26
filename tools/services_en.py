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
    'Start with 30% and get 3 complete design directions, with your own content',
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
<li><b>Start with 30%</b>: and get 3 designs with your own content. You scroll through them on your phone, just like a real website.</li>
<li><b>You choose a direction</b>: you pay another 40%, and we move on to the build.</li>
<li><b>Build and review</b>: you review and approve everything, and pay the remaining 30% before the site goes live.</li>
<li><b>Launch and training</b>, plus 4 months of support.</li>
</ol>'''


def ul(items):
    return '<ul>' + ''.join(f'<li>{i}</li>' for i in items) + '</ul>'


PAGES = [
    dict(
        slug='landing-page', kw='Landing page design',
        title='Landing page design from ₪2,290 | 3 designs | YYG Web Design',
        desc='Landing page design that brings in customers: start with 30% and see 3 designs, 3 revision rounds, live within 7 business days. From ₪2,290, no VAT is added.',
        h1='Landing page design that brings in customers', lead='One focused, fast page where everything leads to a single action: calling you or leaving their details.',
        price='2,290', price_n=2290, service='Landing page design',
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
<p>One page with up to 7 sections, a contact form and a call button that's always visible. <b>Delivery time: up to 7 business days</b> from the day I receive the materials: 3 for the designs and 4 for the build.</p>
<h2>Price</h2>
<p><b>From ₪2,290</b>, no VAT is added. This is a starting price: the exact price depends on what the page needs, and it comes in a formal quote by email before we start. A second language (Hebrew or English): ₪700.</p>
{how}''',
        faq=[('Is there a cheaper option?', 'Yes. A basic landing page starts at ₪1,490: one page with up to 5 sections, one design, one revision round, you provide the text, and one month of support. It is ready within 4 business days: 2 for the design and 2 for the build. You pay 50% at the start and 50% before the site goes live. My next 5 clients also get a launch price of 10% off, in return for letting me show their site in my work with a written testimonial.'),
             ('How long does it take to build a landing page?', 'Up to 7 business days from the day I receive all the materials: 3 for the designs and 4 for the build. Days spent waiting for your reply are not counted.'),
             ('Can I expand the landing page into a full website later?', "Yes. The landing page becomes the home page or one of the pages of the website, so we don't start from scratch."),
             ('Does a landing page show up on Google?', "Yes. It's built ready for Google, but it usually shows up for fewer searches than a full website, because it has fewer pages."),
             ('What do I need to prepare?', 'Your logo, a sentence about your business, the service you want to promote, and photos if you have them. I write the full copy.')],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='business-website', kw='Small business website design',
        title='Business website design from ₪4,900 | YYG Web Design',
        desc='A business website with an update screen for the content that changes: up to 6 pages, start with 30% and see 3 designs, live within 14 business days. From ₪4,900.',
        h1='Small business website design you can update yourself', lead='A multi-page business website with a simple update screen for whatever changes: services, prices, promotions and photos.',
        price='4,900', price_n=4900, service='Small business website design',
        body='''<h2>Who a business website is for</h2>
<ul>
<li>A business with <b>several services</b>, each needing its own explanation.</li>
<li>Anyone who wants to <b>show up on Google for several searches</b>: each page gets its own chance.</li>
<li>Anyone who updates content: promotions, recipes, projects or opening hours.</li>
</ul>
<h2>What's included</h2>
{incl}
<p>Up to 6 pages, an update screen for the content that changes, forms, and training: a one-on-one call and a written guide. <b>Delivery time: up to 14 business days</b>: 5 for the designs and 9 for the build.</p>
<h2>Extended business website: more pages and a content section</h2>
<p>Need more room? An extended business website has <b>up to 12 pages and a content section</b>: news, articles or events, plus everything in the business website. It starts at ₪6,900, and it's ready within 20 business days: 6 for the designs and 14 for the build. That's less than adding 6 pages to a business website (₪2,400), and the content section is already included.</p>
<h2>An example from my work</h2>
<p>For HaKef SheBaAfiya (boutique dessert catering), I built a website with a catalog organized by category, a bar planner that sends the business an organized request, and a recipe template the owner updates on her own. The new site loads three times faster on a phone than the old one. <a class="text-link" href="/en/#work">Read the full story</a></p>
<h2>Price</h2>
<p><b>From ₪4,900</b>, no VAT is added. An extended business website from ₪6,900. Each additional page: ₪400. A second language (Hebrew or English): 30% of the package, which is ₪1,500 for a business website and ₪2,100 for an extended one.</p>
{how}''',
        faq=[('How long does it take to build a business website?', 'A business website takes up to 14 business days from the day I receive all the materials: 5 for the designs and 9 for the build. An extended business website takes up to 20 business days.'),
             ('Will I be able to update the website myself?', "Yes. There's a simple update screen for the content that changes, and I walk you through it in a one-on-one session and a written guide."),
             ('Whose name are the website and domain in?', 'Yours. The website, the domain and the content belong to you, and you can move to another company at any time.'),
             ('Is there a monthly fee?', "It isn't required. The first year of domain, hosting and business email is included. After that, All-in Care is ₪590 a year if you'd like me to take care of everything. If you'd also like me to make small changes for you, there's maintenance of up to half an hour a month, for ₪199 a month.")],
        related=['landing-page', 'online-store'],
    ),
    dict(
        slug='online-store', kw='Online store development',
        title='Online store development from ₪7,900 | YYG Web Design',
        desc='An online store with a cart, card payments and shipping, plus a screen for products, stock and orders. Up to 30 products included. From ₪7,900, no VAT is added.',
        h1='Online store development that makes buying easy', lead="A catalog, a cart and credit card payments, with simple management of products, inventory and orders. Built for phones, because that's where people shop.",
        price='7,900', price_n=7900, service='Online store development',
        body='''<h2>What's included in your store</h2>
{incl}
<ul>
<li>A shopping cart and credit card payments (the payment processor's fees are separate).</li>
<li>Shipping, returns, terms of service and a privacy policy.</li>
<li>A screen for products, inventory and orders, with one-on-one training.</li>
<li><b>Up to 30 products that I add for you.</b> Each additional 10 products: ₪250.</li>
</ul>
<p><b>Delivery time: up to 25 business days</b>: 6 for the designs and 19 for the build. It's a good idea to open an account with a payment processor right at the start, because approval takes a few days.</p>
<h2>Price</h2>
<p><b>From ₪7,900</b>, no VAT is added. A second language (Hebrew or English): ₪2,400. A large store, with more than 100 products, a complex user area or connections to other systems, is priced by quote.</p>
{how}''',
        faq=[('How much does it cost to build an online store?', 'With me, from ₪7,900, no VAT is added. That includes up to 30 products that I add for you, plus the first year of domain, hosting and business email. The exact price comes in the quote by email.'),
             ('How do I accept credit card payments?', 'Through an Israeli payment processor. You open an account with them, and I connect it to your store. Their fees are paid to them directly.'),
             ('Can I add products myself?', "Yes. There's simple management of products, inventory and orders, and I walk you through it.")],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='institutions', kw='Website for institutions, nonprofits and classes',
        title='Websites for institutions, nonprofits and classes | YYG Web Design',
        desc='Websites for institutions, nonprofits and classes: a business website from ₪4,900, plus only the modules you need: sign-ups, schedules, a mailing list, user accounts and donations. No VAT is added.',
        h1='Website for institutions, nonprofits and classes', lead='A website that showcases your activities and takes organized sign-ups: parents and participants find what suits them and sign up right from their phones.',
        price='4,900', price_n=4900, service='Website for institutions, nonprofits and classes',
        body='''<h2>What an institution's website needs</h2>
<ul>
<li><b>Finding things fast</b>: a class, a lesson or an activity by age, location or topic.</li>
<li><b>Easy sign-up</b>: a full registration form, with email confirmation.</li>
<li><b>Keeping everyone updated</b>: an opt-in mailing list and ready-made email templates.</li>
<li><b>Taking donations</b>: by card, one-time or monthly, and sometimes a campaign with a goal.</li>
<li><b>Content and images suited to your community</b>, and a clean site that's easy to approve on filtered internet.</li>
</ul>
<h2>What's included</h2>
{incl}
<p>The base is a <a class="text-link" href="/en/services/business-website.html">business website</a>: up to 6 pages, an update screen for the content that changes, forms and training. An institution with a lot of content chooses an extended business website: up to 12 pages and a content section for news, articles or events. Sign-ups, schedules, a mailing list, user accounts and donations are <b>modules</b>: you add only what your institution needs. <b>Delivery time: up to 14 business days for a business website, and up to 20 for an extended one</b>, plus 2 to 4 days per module.</p>
<h2>An example from my work</h2>
<p>For the Pirchei Shomayim Boys' Choir, I built a website with a class search by the child's age and city, a full registration form with the option to open an account, a song library with a player that doesn't slow the site down, and an English version. Before the build, they saw five designs and chose their favorite. <a class="text-link" href="/en/#work">See the full project</a></p>
<h2>Price: a package plus modules</h2>
<p><b>A business website from ₪4,900</b>, or <b>an extended business website from ₪6,900</b>, plus the modules you need:</p>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>Module</th><th>Price</th><th>What it gives you</th></tr></thead>
<tbody>
<tr><td>Advanced sign-up form</td><td>₪700</td><td>The fields you need, an automatic confirmation email and Excel export</td></tr>
<tr><td>An updatable schedule or events</td><td>₪700</td><td>Prayer times, lessons, classes or activities that you update yourself</td></tr>
<tr><td>A mailing list and 3 email templates</td><td>₪900</td><td>Updates for people who signed up, with consent and an unsubscribe link</td></tr>
<tr><td>User accounts</td><td>₪2,000</td><td>Login, a profile and history</td></tr>
<tr><td>Card donations</td><td>₪1,200</td><td>One-time and monthly donations</td></tr>
<tr><td>A campaign</td><td>₪800</td><td>A goal meter that shows how much has been raised, and a donor list</td></tr>
</tbody></table></div>
<p>For example, a nonprofit with donations and a campaign, like the <a class="text-link" href="/en/demo/donate/">example nonprofit website</a>: a business website ₪4,900, donations ₪1,200 and a campaign ₪800, ₪6,900 in total. A synagogue with prayer times, donations and a campaign, like the <a class="text-link" href="/en/demo/shul/">example synagogue website</a>: ₪4,900 plus ₪700, ₪1,200 and ₪800, ₪7,600 in total.</p>
<p>No VAT is added to any price. These are starting prices, and the exact price comes in the quote by email. A second language (Hebrew or English): 30% of the package, which is ₪1,500 for a business website and ₪2,100 for an extended one.</p>
{how}''',
        faq=[('Can the website take sign-ups and payments?', 'Yes. An advanced sign-up form, with an automatic confirmation email and Excel export, is a ₪700 module. Card donations, one-time and monthly, are a ₪1,200 module. Payment for a class or an event is connected through a payment processor, as an add-on listed in the quote.'),
             ('Will the website suit a Torah-observant community?', "Yes. The content and images are chosen to suit your community, and the site is built clean and simple, so it's easy to approve on filtered internet."),
             ('Can I send updates to the people who signed up?', "Yes. An opt-in mailing list with 3 ready-made email templates is a ₪900 module, and you send the updates yourself.")],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='logo-branding', kw='Logo design and branding',
        title='Logo design and branding from ₪590 | YYG Web Design',
        desc='Logo design and branding: basic logo ₪590, professional logo with 3 sketches ₪1,500, basic branding ₪2,500, and a full brand with a website from ₪6,190.',
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
<tr><td>Full brand + landing page</td><td>From ₪6,190</td><td>Branding, website, all the content, a business card and flyer, social media images, Google and 4 months of support</td></tr>
<tr><td>Full brand + website</td><td>From ₪8,800</td><td>Same as above, with a business website (with an extended business website from ₪10,800, with a store from ₪11,800)</td></tr>
</tbody></table></div>
<p>The full brand adds ₪3,900 to the price of any website package. No VAT is added to any price.</p>
<h2>Why it pays to start with the logo</h2>
<p>The website is designed around the logo: the colors, the fonts and the feel all grow out of it. When you start with the branding, your website, business card and sign look like one business, not a collection of unrelated pieces.</p>
<h2>How it works</h2>
<ol>
<li><b>A discovery call</b>: what your business does, who it serves, what sets you apart, and which colors and styles you like.</li>
<li><b>Sketches</b>: one direction for a basic logo, 3 directions for a professional logo and for branding.</li>
<li><b>Choosing and revisions</b>, and then files ready for every use.</li>
</ol>''',
        faq=[('How much does logo design cost?', 'A basic logo with one sketch costs ₪590. A professional logo with 3 sketches, 3 revision rounds and all the files costs ₪1,500. No VAT is added.'),
             ("What's the difference between a logo and branding?", 'A logo is the mark. Branding is the whole visual language: logo, colors, fonts, a style guide and a business card, so your business looks the same everywhere.'),
             ('What is Brand from 0 to 100%?', 'You bring the idea for the business, and I build everything else: branding, website, all the content, print, social media and a Google Business Profile. It adds ₪3,900 to any website package, for example ₪6,190 with a landing page.'),
             ('Do I need a business name first?', 'Yes. The packages assume your business already has a name. Coming up with a name is separate work, with its own quote.')],
        related=['landing-page', 'business-website'],
    ),
]
# ---------- Expansion: more useful content per page, more questions, and two pages by business type ----------
PAY_FAQ = ('How does payment work?', "In three steps. 30% to start, and for that you get 3 complete designs with your own content. You choose a direction and pay another 40%. You pay the remaining 30% at the end, after you approve everything and before the site goes live. If you decide not to go ahead after the designs, the 30% covers the design work, and you don't pay anything more.")
NONE_FAQ = ("What if none of the designs feel right?", "You mark what you liked in a feedback form, and I work it into the next direction, within the revision rounds. If you still decide not to go ahead, the 30% covers the design work, and you don't pay anything more.")
WHY = '''<h2>Why build with me</h2>
<ul>
<li><b>See it before you go ahead</b>: you start with 30%, and for that you already get 3 complete designs with your own content. You only move on to the build after you choose. And if you decide not to go ahead after the designs, you don't pay anything more.</li>
<li><b>No surprises in the price</b>: no VAT is added, and the first year of domain, hosting and business email is included. The full price appears in a formal quote by email before we start.</li>
<li><b>A fast website</b>: built with clean code, without a heavy theme or unnecessary plugins, so it loads fast even on a phone and a weak connection.</li>
<li><b>Everything in your name</b>: the domain, the website and the content belong to you, and you can move to another company at any time.</li>
<li><b>Clear copy</b>: I write it in your customers' language, without jargon, and you approve every word.</li>
<li><b>Real support</b>: 4 months after launch, for bugs, questions and small changes.</li>
</ul>'''

MORE = {
'landing-page': '''<h2>What affects the price of a landing page</h2>
<p>The price starts at ₪2,290, and anything that can add to it is known up front and appears in the quote:</p>
<ul>
<li><b>The amount of content</b>: up to 7 sections are included. A longer page, for example with many services or a detailed price list, is usually a better fit for a <a class="text-link" href="/en/services/business-website.html">multi-page website</a>.</li>
<li><b>An update screen</b>: if something on the page changes all the time, like opening hours or a promotion, you can add a screen where you update it yourself, for ₪1,000.</li>
<li><b>A second language</b>: ₪700.</li>
<li><b>An extra design</b>: if you'd like to see another direction after the 3 designs, each extra design is ₪400. An extra revision round: ₪250.</li>
<li><b>Rush delivery</b>: in half the time, for an extra 25%.</li>
<li><b>A logo</b>: if you don't have one yet, you can add a <a class="text-link" href="/en/services/logo-branding.html">basic logo for ₪590 or a professional one for ₪1,500</a>.</li>
</ul>
<p>What doesn't change the price: the copy (I write it), mobile optimization, Google readiness, and the first year of domain, hosting and business email. All of it is already included.</p>
<h2>Common landing page mistakes</h2>
<ul>
<li><b>Too many buttons</b>: "call", "WhatsApp", "sign up" and "follow us" on the same screen. With many options, many people choose none. A good page has one main action.</li>
<li><b>A headline about the business, not the customer</b>: "Welcome" instead of what the customer gets and why to choose you.</li>
<li><b>A long form</b>: every unnecessary field costs inquiries. For most businesses, a name and a phone number are enough to call back.</li>
<li><b>A heavy page</b>: a huge video or dozens of photos that slow down loading on a phone, exactly where most visitors are.</li>
<li><b>No proof</b>: promises without work, testimonials or numbers. A visitor who doesn't know you needs a reason to believe.</li>
</ul>
<h2>What I need from you</h2>
<p>Very little: your logo if you have one, a sentence or two about your business, the service you want to promote, and what sets you apart. Real photos of your work help a lot. I prepare everything else, including the copy. The full list is in the guide <a class="text-link" href="/en/guides/before-you-build.html">what to prepare before you build a website</a>.</p>
<h2>After the page is live</h2>
<p>During the 4 months of support, I fix bugs and answer questions. A landing page has no update screen unless you added one, so I make small text or image changes for you. If you later want to expand to a full website, the page becomes the home page, and we don't start from scratch. Not sure what fits? Read <a class="text-link" href="/en/guides/landing-or-website.html">landing page or full website</a>, or answer 3 questions in the <a class="text-link" href="/en/#plan">price calculator</a>.</p>
<p>You can see how I work in the two projects on the site: <a class="text-link" href="/en/#work">HaKef SheBaAfiya and Pirchei Shomayim</a>.</p>''',

'business-website': '''<h2>Which pages a business website usually has</h2>
<ul>
<li><b>Home</b>: who you are, who you serve, and what to do next.</li>
<li><b>Services</b>: a page for each main service, so Google gets to know each one.</li>
<li><b>Work or projects</b>: proof that it works.</li>
<li><b>About</b>: the face behind the business.</li>
<li><b>FAQ</b>: saves repeat calls, and helps you show up on Google for questions people search.</li>
<li><b>Contact</b>: phone, a short form, WhatsApp, and a map if people come to you.</li>
</ul>
<p>Up to 6 pages are included, and each extra page is ₪400, so you can plan exactly how many you need. Need a lot more? An extended business website includes up to 12 pages and a content section. The <a class="text-link" href="/en/#plan">price calculator</a> suggests a list of pages by business type.</p>
<h2>What an update screen is, and what you update in it</h2>
<p>An update screen (some call it an admin panel) is a simple screen where you update whatever changes, without touching the design: prices, hours, promotions, photos, new projects or recipes. At HaKef SheBaAfiya, for example, the owner adds a new recipe on her own, and it appears on the site in the same design as everything else. I set up in advance what you update yourself and what stays fixed and protected, so the site can't be broken by accident.</p>
<h2>What affects the price</h2>
<ul>
<li><b>The number of pages</b>: up to 6 are included, and each extra page is ₪400. Up to 12 with an extended business website.</li>
<li><b>What the site does</b>: presenting, forms and inquiries are included. Sign-ups, a schedule, a mailing list, user accounts or donations are modules you add as needed, from ₪700 (details in <a class="text-link" href="/en/services/institutions.html">websites for institutions, nonprofits and classes</a>). Selling with online payment is an <a class="text-link" href="/en/services/online-store.html">online store</a>.</li>
<li><b>A second language</b>: 30% of the package, which is ₪1,500.</li>
<li><b>Rush delivery</b>: in half the time, for an extra 25%.</li>
<li><b>Logo and branding</b>, if you don't have them yet: <a class="text-link" href="/en/services/logo-branding.html">logo design and branding</a>, or Brand from 0 to 100% for an extra ₪3,900.</li>
</ul>
<p>The prices here are starting prices. The exact price comes in a formal quote by email before we start, so you know exactly what you're paying for.</p>
<h2>Timeline</h2>
<p>Up to 14 business days from the day I have all the materials: 5 days for the 3 designs, and after you choose a direction, 9 days for building all the pages, connecting the update screen, testing on phone and computer, and training. The clock starts on the day I receive the logo, the key points for the copy and the photos. More on what speeds things up or slows them down in the guide <a class="text-link" href="/en/guides/how-long-to-build.html">how long it takes to build a website</a>.</p>
<h2>Who it suits by business type</h2>
<p>There are separate pages with tips for specific business types: <a class="text-link" href="/en/services/catering.html">catering and food businesses</a>, <a class="text-link" href="/en/services/professionals.html">trades and service providers</a>, and <a class="text-link" href="/en/services/institutions.html">institutions, nonprofits and classes</a>.</p>''',

'online-store': '''<h2>Who an online store is for</h2>
<ul>
<li>A business that sells <b>products at fixed prices</b> and wants to be paid up front.</li>
<li>Anyone who sells today over WhatsApp or the phone, and answers the same questions again and again: what's available, how much, and how to pay.</li>
<li>Anyone who wants to <b>ship nationwide</b>, or offer organized self-pickup.</li>
</ul>
<p>If every order is custom and has no fixed price, like a designed cake or an event, a site with a catalog and a quote request is sometimes better. That's how the HaKef SheBaAfiya site works. More in <a class="text-link" href="/en/services/catering.html">websites for catering and food businesses</a>.</p>
<h2>What affects the price</h2>
<ul>
<li><b>The number of products</b>: up to 30 products that I add are included, and each extra 10 are ₪250. After launch, you add products yourself at no cost. A store with more than 100 products is priced by quote.</li>
<li><b>Product options</b>: sizes, colors or add-ons for each product take more setup, and that goes into the quote.</li>
<li><b>Shipping</b>: a flat rate, a rate by area, or self-pickup.</li>
<li><b>A second language</b>: ₪2,400.</li>
</ul>
<h2>What to prepare for a store</h2>
<ul>
<li><b>A product list</b>: name, price, a short description and a photo. A simple spreadsheet is enough.</li>
<li><b>An account with a payment provider</b>. It's worth opening one right at the start, because approval takes a few days.</li>
<li><b>A shipping and returns policy</b>: shipping cost, delivery time, and how returns work. I prepare the terms based on that, and you approve them.</li>
</ul>
<h2>Timeline</h2>
<p>Up to 25 business days from the day I have all the materials: 6 for the designs and 19 for the build. What usually stretches out a store isn't the build, but the product list and the payment provider's approval, so it's worth starting on both on day one. More in the guide <a class="text-link" href="/en/guides/how-long-to-build.html">how long it takes to build a website</a>.</p>
<h2>After the store is live</h2>
<p>I walk you through managing products, inventory and orders, and I stay available during the 4 months of support. It's also worth opening a Google Business Profile and linking it to the store, so people find you on Maps too. Not sure whether you need a store or a website? The <a class="text-link" href="/en/#plan">price calculator</a> recommends one based on what people need to do on your site.</p>''',

'institutions': '''<h2>What good sign-up looks like</h2>
<ul>
<li><b>Find first, then sign up</b>: a parent chooses an age and a city, and sees only what fits. At Pirchei Shomayim, choosing a class fills in the branch on the form automatically.</li>
<li><b>A form that's as short as possible</b>: at first, ask only for what you need to get back to people, and complete the rest in a call.</li>
<li><b>Email confirmation</b>: whoever signs up knows their request arrived, and the office gets an organized request with all the details.</li>
<li><b>Simple management</b>: a screen with all the sign-ups, and Excel export in one click.</li>
</ul>
<h2>A mailing list, done right</h2>
<p>The mailing list is built with explicit consent: people who sign up check that they want updates, and every email has an unsubscribe link. That way you only send to people who want to hear from you, which is also what Israeli law requires for promotional email. The mailing list module (₪900) includes 3 ready email templates, for example a new class opening, an event reminder and a general update, and you send them yourself.</p>
<h2>Donations and campaigns</h2>
<p>With the donations module (₪1,200), people donate by card, one-time or monthly, through a payment provider. Its fees are paid to it directly. The campaign module (₪800) adds a goal meter that shows how much has already been raised, and a donor list. That way everyone who visits sees the campaign is live, and joins in.</p>
<h2>What affects the price</h2>
<ul>
<li><b>The base</b>: a business website with up to 6 pages, or an extended business website with up to 12 pages and a content section. Each extra page is ₪400.</li>
<li><b>The modules</b>: only what you need, from ₪700 per module.</li>
<li><b>The number of branches or classes</b>, and whether you need search by age, city or topic.</li>
<li><b>Payment for classes or events</b>: connecting a payment provider, at an extra cost shown in the quote.</li>
<li><b>A second language</b>: 30% of the package, which is ₪1,500 for a business website and ₪2,100 for an extended one.</li>
</ul>
<h2>Timeline</h2>
<p>Up to 14 business days for a business website, and up to 20 for an extended one. Each module adds 2 to 4 days: user accounts 4, donations 3, and the rest 2. The clock starts on the day I have all the materials: the list of classes or activities, the branches, hours if they can be published, suitable photos and the logo. It's worth publishing as much information as helps people decide, like the day, an approximate time and the location, to save repeat questions.</p>
<h2>What I need from you</h2>
<ul>
<li>A list of classes, lessons or activities, with ages and locations.</li>
<li>What to ask at sign-up, and what can wait for a call.</li>
<li>Who receives the inquiries by email, and who manages the site.</li>
<li>Photos suited to your audience, if you have them. If not, illustrations or photos of the venues can be enough.</li>
</ul>
<p>More about the process in the guides <a class="text-link" href="/en/guides/before-you-build.html">what to prepare before you build a website</a> and <a class="text-link" href="/en/guides/website-cost.html">how much it costs to build a website</a>, or in the <a class="text-link" href="/en/#plan">price calculator</a>.</p>''',

'logo-branding': '''<h2>What you get at the end: the files</h2>
<ul>
<li><b>Vector</b> (SVG or PDF): for signs, print and any size, without the logo breaking into pixels.</li>
<li><b>Transparent background</b> (PNG): for the website, presentations and documents.</li>
<li><b>One color</b>: for stamps, engraving and black-and-white printing.</li>
<li><b>An icon</b>: for profile pictures, the site icon and WhatsApp Business.</li>
</ul>
<p>With the basic logo you get files for the website and social media. With the professional logo and branding you get every type.</p>
<h2>What makes a good logo for a small business</h2>
<ul>
<li><b>Simple</b>: readable even at icon size on a phone.</li>
<li><b>Works in one color</b>: a logo that only looks good in color suffers in cheap printing or on a stamp.</li>
<li><b>Fits the audience</b>: a kindergarten logo and an accounting firm logo shouldn't feel the same.</li>
<li><b>Doesn't lean on a trend</b>: effects that look current today get dated fast.</li>
</ul>
<h2>Basic or professional logo?</h2>
<p>A <b>basic logo</b> suits a business that's just starting out, needs a tidy mark for its website and social media, and has a rough idea of what it wants. A <b>professional logo</b> fits when the logo will stay with you for years: on a sign, a vehicle, packaging and documents. There, the 3 directions give you a real choice, and the 3 revision rounds let you fine-tune it. If you're also planning a website, <b>basic branding</b> saves you work: the colors, fonts and style guide go straight into designing the site.</p>
<h2>Brand from 0 to 100%: what it means in practice</h2>
<p>You bring the idea and the name of the business, and I build everything else in one language: logo and branding, a <a class="text-link" href="/en/services/landing-page.html">landing page</a> or a <a class="text-link" href="/en/services/business-website.html">website</a>, all the copy, a business card and flyer, social media images, and a Google Business Profile. The full brand adds ₪3,900 to the price of the website package, and payment works in stages like a website: 30% to start, 40% after you choose a direction, and 30% at the end.</p>
<h2>How long it takes</h2>
<p>It mainly depends on how quickly answers come back between sketches and revisions. The exact schedule appears in the quote. With a full brand, the logo and the website are built back to back, so work on the website starts as soon as the branding is approved. The brand adds up to 10 business days to the website's timeline.</p>''',
}

EXTRA_FAQ = {
'landing-page': [('How much does a landing page cost?', 'From ₪2,290, no VAT is added. It includes 3 designs, 3 revision rounds, the copy, and the first year of domain, hosting and business email. The exact price comes in the quote by email.'), PAY_FAQ, NONE_FAQ],
'business-website': [('How much does a business website cost?', 'A business website starts at ₪4,900, no VAT is added, including up to 6 pages, an update screen, 3 designs, 3 revision rounds and the first year of domain, hosting and business email. An extended business website, with up to 12 pages and a content section, starts at ₪6,900. The exact price comes in the quote by email.'), PAY_FAQ, ('Who writes the copy?', 'I do, with the help of AI and personal editing. You give me the key points, and you approve every text before it goes live.')],
'online-store': [PAY_FAQ, ('Can I start with fewer products?', 'Yes. You can go live with your main products and add more yourself later from the products screen.'), ('How long does it take to build a store?', 'Up to 25 business days from the day I have all the materials, including the product list: 6 for the designs and 19 for the build. Open an account with a payment provider at the start, because approval takes a few days.'), ('Is there a monthly fee for the store?', "There's no store subscription fee. The first year of domain, hosting and business email is included, then ₪890 a year with All-in Care, or with your own hosting company. Per-transaction fees go to the payment provider.")],
'institutions': [('How much does a website for an institution or class cost?', 'A business website starts at ₪4,900, and an extended business website at ₪6,900. You add only the modules you need: an advanced sign-up form ₪700, a schedule ₪700, a mailing list ₪900, user accounts ₪2,000, donations ₪1,200 and a campaign ₪800. No VAT is added.'), PAY_FAQ, ('How long does it take to build a website for an institution?', 'Up to 14 business days for a business website, and up to 20 for an extended one, plus 2 to 4 days per module. The clock starts on the day I have all the materials: the list of classes or activities, the branches, photos and the logo.'), ('Can I add an English version?', 'Yes. A second language is 30% of the package price: ₪1,500 for a business website and ₪2,100 for an extended one. Pirchei Shomayim, for example, has a full English version.')],
'logo-branding': [('How many sketches do I get?', 'One sketch and one revision round with the basic logo. 3 sketches and 3 revision rounds with the professional logo and branding.'), ('Do I need a logo before building a website?', "It's recommended, because the website is designed around the logo: the colors and feel come from it. If you don't have one, you can order it together with the website."), ('Can I order a logo without a website?', 'Yes. You can order a logo or branding on its own, and add a website later in the same design language.')],
}

for _p in PAGES:
    _p['body'] = _p['body'] + '\n' + MORE[_p['slug']]
    _p['faq'] = _p['faq'] + EXTRA_FAQ[_p['slug']]
for _p, _extra in (('landing-page', 'professionals'), ('business-website', 'catering'), ('online-store', 'catering')):
    next(x for x in PAGES if x['slug'] == _p)['related'].append(_extra)

PAGES += [
    dict(
        slug='catering', kw='Website design for catering businesses',
        title='Websites for catering and food businesses from ₪2,290 | YYG Web Design',
        desc='Websites for caterers, bakeries and food businesses: a menu or catalog with photos, ordering on WhatsApp or by email, and an event quantity planner.',
        h1='Websites for catering and food businesses', lead='A website that shows your food at its best, answers the repeat questions in advance, and turns a visitor into an organized order on WhatsApp or by email.',
        price='2,290', price_n=2290, service='Website design for catering and food businesses',
        body='''<h2>Who it's for</h2>
<ul>
<li>Event catering, dessert bars, bakeries and designed cakes.</li>
<li>Platters, gift boxes and food delivery.</li>
<li>A bakery, an eatery or a private chef who takes orders in advance.</li>
</ul>
<h2>What a food business website needs</h2>
<ul>
<li><b>Real photos</b> of your food. They sell better than any text.</li>
<li><b>An organized menu or catalog</b> by category, with a page for each main product.</li>
<li><b>Answers to the repeat questions</b>: kosher certification, minimum order, how far ahead to order, delivery areas, and a starting price or range.</li>
<li><b>Easy ordering</b>: a WhatsApp button with a ready message, and next to it a form that sends the request by email, for people without WhatsApp.</li>
<li><b>Speed on a phone</b>: a site with many photos has to be built right, or it loads slowly and loses people.</li>
</ul>
<h2>An example from my work: HaKef SheBaAfiya</h2>
<p>For the boutique dessert caterer HaKef SheBaAfiya, I built a website with a catalog by category and a page for each product, plus a <b>bar planner</b>: the customer picks the number of guests, the type of event, kosher level and serving style, and gets an estimate of quantities by type of dessert. From the planner, they continue to an organized request with all the details and the calculation, sent on WhatsApp or straight to the business's email. There's also a recipe template the owner updates on her own. The new site loads three times faster on a phone than the old one.</p>
<p><a class="text-link" href="https://hakef.com/" target="_blank" rel="noopener">Visit the live HaKef SheBaAfiya site<span class="sr"> (opens in a new window)</span></a> · <a class="text-link" href="/en/#work">Read the full story</a></p>
<h2>Which package fits a food business</h2>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>What you need</th><th>Package</th><th>Price</th></tr></thead>
<tbody>
<tr><td>One page: what you offer, photos, and ordering on WhatsApp or by email</td><td><a class="text-link" href="/en/services/landing-page.html">Landing page</a></td><td>From ₪2,290</td></tr>
<tr><td>A menu or catalog, a page per product, and updating prices, promotions and recipes yourself</td><td><a class="text-link" href="/en/services/business-website.html">Business website</a></td><td>From ₪4,900</td></tr>
<tr><td>Selling with online payment: boxes, platters or deliveries at fixed prices</td><td><a class="text-link" href="/en/services/online-store.html">Online store</a></td><td>From ₪7,900</td></tr>
</tbody></table></div>
<p>A quantity planner like the one at HaKef SheBaAfiya usually fits a business website, and its price is set in the quote based on what it needs to calculate. No VAT is added to any price.</p>
<h2>What's included in every package</h2>
{incl}
<h2>What to prepare</h2>
<ul>
<li>Photos of your food, as real as possible. Professional photography isn't included, but good phone photos in daylight can work nicely too.</li>
<li>A list of products or dishes, with a price or price range if you publish prices.</li>
<li>Your kosher certification details, if any.</li>
<li>Delivery areas, minimum order, and how far ahead people need to order.</li>
</ul>
<p>More ideas in the guide <a class="text-link" href="/en/guides/before-you-build.html">what to prepare before you build a website</a>.</p>
{how}''',
        faq=[('How much does a catering website cost?', 'A landing page from ₪2,290, a business website with a menu and an update screen from ₪4,900, and a store with online payment from ₪7,900. No VAT is added to the prices.'),
             ('Can I get orders on WhatsApp?', "Yes. The button opens a ready message with the order details. Next to it is a form that sends the same request by email, for people without WhatsApp."),
             ('Can I take payment on the site?', 'Yes, with the store package, through an Israeli payment provider. If every order is custom, it is usually better to receive a request and agree on the price in a call.'),
             ('Can I update the menu and prices myself?', 'Yes, with a business website, from the update screen. On a landing page, I make small changes for you, or you add an update screen to it for ₪1,000.'),
             ('How long does it take to build a website for a food business?', 'A landing page up to 7 business days, a business website up to 14, and a store up to 25, from the day I have all the materials.'),
             ('Do I need professional photography?', "It isn't required. Professional photography isn't included in the price, and good, real phone photos can be enough. I help choose and arrange them."),
             PAY_FAQ],
        related=['business-website', 'online-store', 'landing-page'],
    ),
    dict(
        slug='professionals', kw='Website design for trades and service providers',
        title='Websites for trades and service providers from ₪2,290 | YYG Web Design',
        desc='Websites for electricians, plumbers, consultants, therapists and private tutors: a call button, a service area, testimonials and a short form. Start with 30%.',
        h1='Websites for trades and service providers', lead='A website that makes your phone ring: anyone who finds you on Google understands right away what you do, where, and why to choose you, and calls with one tap.',
        price='2,290', price_n=2290, service='Website design for trades and service providers',
        body='''<h2>Who it's for</h2>
<ul>
<li>Tradespeople in the field: electricians, plumbers, technicians and renovators.</li>
<li>Consultants and service providers: accountants, mortgage advisers, lawyers and coaches.</li>
<li>Therapists and private tutors who see clients by appointment.</li>
</ul>
<h2>What a tradesperson's website needs</h2>
<ul>
<li><b>A call button that's always visible</b> on a phone, with WhatsApp next to it. Most people looking for a tradesperson search on their phone, and don't want to hunt for a way to get in touch.</li>
<li><b>A clear service area</b>: which cities and neighborhoods you cover, so the right people reach out.</li>
<li><b>Services and prices</b>: a clear list, and a starting price if possible. It filters out irrelevant inquiries.</li>
<li><b>Proof</b>: client testimonials, Google reviews, and photos of real work.</li>
<li><b>A short form</b>: name, phone and what they need. Not ten fields.</li>
<li><b>Availability</b>: when you answer, and what to do in an emergency.</li>
</ul>
<h2>Google: your Business Profile and a page per service</h2>
<p>A local tradesperson is found on Google in two ways: in the regular results and on Maps. For Maps you need a <b>Google Business Profile</b> with a service area, hours and reviews, linked to your website. The website strengthens the profile, and the profile brings people to the website. With a multi-page website, you can also build a page for each main service, because someone searching for "electrical panel replacement" is looking for something more specific than "electrician".</p>
<h2>Which package fits</h2>
<div class="g-scroll"><table class="g-table">
<thead><tr><th>What you need</th><th>Package</th><th>Price</th></tr></thead>
<tbody>
<tr><td>One or two services, and the whole goal is a phone call</td><td><a class="text-link" href="/en/services/landing-page.html">Landing page</a></td><td>From ₪2,290</td></tr>
<tr><td>Several services, a page for each, work samples and FAQ</td><td><a class="text-link" href="/en/services/business-website.html">Business website</a></td><td>From ₪4,900</td></tr>
<tr><td>Sign-ups for workshops, courses or groups, and a mailing list</td><td><a class="text-link" href="/en/services/institutions.html">Business website with modules</a></td><td>From ₪6,500</td></tr>
</tbody></table></div>
<p>No VAT is added to any price. Are your own copy and one design enough? A basic landing page starts at ₪1,490.</p>
<h2>What's included in every package</h2>
{incl}
<h2>An example: the site you're reading</h2>
<p>This website is built the same way: it explains what I do and for whom, shows real work, gives a starting price and a <a class="text-link" href="/en/#plan">price calculator</a>, and lets you leave a name and email wherever you make a decision, with the next step arriving by email right away. <a class="text-link" href="/en/#work">See my work</a></p>
<h2>What to prepare</h2>
<ul>
<li>A list of your services, with a sentence on what each includes.</li>
<li>Your service area: cities or neighborhoods.</li>
<li>Photos of your work, before and after if you have them.</li>
<li>2 or 3 client testimonials, with their permission.</li>
<li>A license, certification or experience worth showing.</li>
</ul>
{how}''',
        faq=[('How much does a website for a tradesperson cost?', 'A landing page from ₪2,290, and a multi-page business website from ₪4,900. No VAT is added, and prices include the first year of domain, hosting and business email. A basic landing page, with one design and your own copy, starts at ₪1,490.'),
             ('Do I need a website if I have a Google Business Profile?', "The profile is very important, but limited. On a website you explain each service, show work and prices, and get organized inquiries. They work best together."),
             ('How do I get more calls from my website?', 'A call button that is always visible on a phone, a clear service area, real testimonials and a short form. And a fast site, because a slow site on a phone loses people before they even see the button.'),
             ('How long does it take to build a website for a tradesperson?', 'A landing page up to 7 business days, and a business website up to 14, from the day I have all the materials.'),
             ('Can I add WhatsApp?', 'Yes. A WhatsApp button with a ready message, next to the call button and the form.'),
             ('I work in several cities. Does that matter?', "It's worth listing them clearly on the site and in your Google Business Profile. With a multi-page website, you can add a page for your main areas, at ₪400 per page beyond 6."),
             PAY_FAQ],
        related=['landing-page', 'business-website', 'logo-branding'],
    ),
]
# Same as SPEC in services.py: sections that close each page, after "How it works", then WHY on every page except the logo.
SPEC = {
'catering': '''<h2>Common mistakes on food business websites</h2>
<ul>
<li><b>Stock photos</b> instead of your real food. Customers notice quickly, and it hurts trust.</li>
<li><b>A menu as a PDF file</b> that's hard to read and zoom on a phone. A menu built into the site is easier, and Google can read it too.</li>
<li><b>No price at all</b>: without even a starting price or range, some people simply won't reach out.</li>
<li><b>Phone-only ordering</b> during limited hours. Someone planning an event in the evening needs a way to leave a request right away.</li>
</ul>''',
'online-store': '''<h2>Common mistakes in small stores</h2>
<ul>
<li><b>A shipping cost that only shows up at the end</b>. It's better to show it on the product page.</li>
<li><b>Inconsistent photos</b>: different backgrounds, lighting and sizes make the store look messy.</li>
<li><b>A long checkout</b>: every unnecessary step loses buyers. Checkout should be short and easy on a phone.</li>
<li><b>No clear returns policy</b> to reassure first-time buyers.</li>
</ul>''',
'professionals': '''<h2>Common mistakes on tradespeople's websites</h2>
<ul>
<li><b>A phone number you can't tap</b> on a phone, or one that only appears at the bottom of the page.</li>
<li><b>No service area</b>: inquiries from cities you don't cover, and wasted time on both sides.</li>
<li><b>Anonymous testimonials</b>, or "a happy customer". A testimonial with a first name and city, with the client's permission, is far more convincing.</li>
<li><b>A site that hasn't been updated in years</b>: old prices, or services you no longer offer.</li>
</ul>''',
'logo-branding': '''<h2>How to prepare for the discovery call</h2>
<ul>
<li>One sentence that explains what the business does, and for whom.</li>
<li>Three words that describe the feeling you want: for example warm, professional, young.</li>
<li>Logos you like, and ones you don't, and why.</li>
<li>Where the logo will appear: website, sign, vehicle, packaging, stickers or documents.</li>
<li>Colors you want, or colors you'd rather avoid.</li>
</ul>
<p>You don't need to come with a ready idea. That's what sketches are for.</p>
<h2>Where you'll use the logo once it's ready</h2>
<ul>
<li><b>On your website and Google Business Profile</b>: the full logo at the top of the site, and the icon as the profile picture.</li>
<li><b>On WhatsApp Business and social media</b>: a square version that still reads in a small circle.</li>
<li><b>On a business card and flyer</b>: the printer will ask for a vector file, and you'll already have it.</li>
<li><b>On a sign, a vehicle and packaging</b>: a one-color version, and sometimes versions for dark and light backgrounds.</li>
</ul>
<h2>Why design your logo with someone who builds websites</h2>
<p>Because a logo first has to work on a screen: small on a phone, on dark and light backgrounds, and as the site icon. I plan it for all of these from the start, and when we get to the website, the colors and fonts are already in place. That way everything looks like one business.</p>''',
'institutions': '''<h2>What makes an institution's website trustworthy</h2>
<ul>
<li><b>Complete, accurate details</b>: up-to-date addresses, hours and contact people.</li>
<li><b>Photos from your activities</b>, with the permission of the participants or parents.</li>
<li><b>Who's behind it</b>: the management, the staff and the mission.</li>
</ul>''',
}
for _p in PAGES:
    _p['body'] = _p['body'] + '\n' + SPEC.get(_p['slug'], '') + '\n' + (WHY if _p['slug'] != 'logo-branding' else '')
GUIDE_LINKS = [('website-cost', 'How much does a business website cost?'), ('how-long-to-build', 'How long does it take to build a website?'), ('choose-web-designer', 'How to choose who builds your website')]
NAMES = {p['slug']: p['h1'] for p in PAGES}
SHORT = {'landing-page': 'Landing pages', 'business-website': 'Business websites', 'online-store': 'Online stores', 'institutions': 'Institutions and classes', 'catering': 'Catering and food', 'professionals': 'Trades and services', 'logo-branding': 'Logo and branding'}
FOOT_SV = ' · '.join(f'<a class="foot-link" href="/en/services/{k}.html">{v}</a>' for k, v in SHORT.items())


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
                   'provider': {'@type': 'ProfessionalService', 'name': 'YYG Web Design', 'url': SITE + '/en/', 'telephone': '+972-54-854-0700'},
                   'offers': {'@type': 'Offer', 'price': str(p['price_n']), 'priceCurrency': 'ILS', 'url': url}})
        ld.append({'@context': 'https://schema.org', '@type': 'FAQPage', 'mainEntity': [
            {'@type': 'Question', 'name': q, 'acceptedAnswer': {'@type': 'Answer', 'text': a}} for q, a in p['faq']]})
    ld.append({'@context': 'https://schema.org', '@type': 'BreadcrumbList', 'itemListElement': [
        {'@type': 'ListItem', 'position': 1, 'name': 'YYG Web Design', 'item': SITE + '/en/'},
        {'@type': 'ListItem', 'position': 2, 'name': 'Services', 'item': SITE + '/en/services/'}]
        + ([] if is_index else [{'@type': 'ListItem', 'position': 3, 'name': h1, 'item': url}])})
    if is_index:
        body = cards
    else:
        body = p['body'].replace('{incl}', ul(COMMON_INCL)).replace('{how}', HOW)
        body += '\n<h2>Frequently asked questions</h2>\n' + '\n'.join(f'<details><summary>{esc(q)}</summary><p>{esc(a)}</p></details>' for q, a in p['faq'])
        body += '<aside class="g-rel" aria-labelledby="rel-t"><h2 id="rel-t">More services and guides</h2><ul>' + ''.join(
            f'<li><a class="text-link" href="/en/services/{s}.html">{NAMES[s]}</a></li>' for s in p['related']) + ''.join(
            f'<li><a class="text-link" href="/en/guides/{g}.html">{t}</a></li>' for g, t in GUIDE_LINKS) + '</ul></aside>'
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
   <a class="text-link" href="/en/services/">Services</a>
   <a class="text-link" href="/en/#plan">Price calculator</a>
   <a class="text-link" href="/en/#contact">Contact</a>
   <a class="text-link" href="{he_path}" hreflang="he" lang="he">עברית</a>
  </nav>
 </div>
</header>
<main id="main" class="g-main">
 <div class="wrap">
  <p class="g-crumb"><a href="/en/">YYG Web Design</a> › <a href="/en/services/">Services</a></p>
  <h1>{h1}</h1>
  <p class="g-lead">{lead}</p>
{body}
  <section class="g-cta" aria-labelledby="cta-t">
   <h2 id="cta-t">Want to know what it will cost you?</h2>
   <p>The discovery questionnaire takes about 10 minutes, and at the end you see a price estimate from your answers right away. The formal quote arrives by email within one business day.</p>
   <a class="btn btn-gold" href="/en/questionnaire/" data-q="src=svc-{slug}">Get a quote: the questionnaire</a>
   <p style="margin-top:12px"><a class="text-link" href="/en/#plan" data-q="src=svc-{slug}">Just a ballpark: 3 questions in the calculator</a> · <a class="text-link" href="/en/#contact">Or leave your details</a> · <a class="text-link" href="tel:0548540700">054-854-0700</a> (calls only)</p>
  </section>
 </div>
</main>
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


for p in PAGES:
    (OUT / f"{p['slug']}.html").write_text(page(p), encoding='utf-8')
cards = '<div class="g-cards">\n' + '\n'.join(
    f'<a class="g-card" href="/en/services/{p["slug"]}.html"><b>{p["h1"]}</b><span>{p["lead"]}</span></a>' for p in PAGES) + '\n</div>'
(OUT / 'index.html').write_text(page(dict(title='Services: websites, online stores, logos and branding | YYG Web Design',
    desc="YYG Web Design services: landing pages, business websites, online stores, sites for institutions, catering and trades, and logo design and branding. Start with 30%.",
    h1='Services', lead='Websites, online stores, logos and branding for small businesses and institutions. With websites, you start with 30% and see 3 designs before you go ahead.'), True, cards), encoding='utf-8')
print('ok', [p['slug'] for p in PAGES] + ['index'])
