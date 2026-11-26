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
        title='Landing page design from ₪2,000 | 3 designs | YYG Web Design',
        desc='Landing page design that brings in customers: start with 10% and see 3 designs, 3 revision rounds, live within 4 business days. From ₪2,000, no VAT is added.',
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
        faq=[('Is there a cheaper option?', 'Yes. A basic landing page costs ₪1,290: one design, one revision round, you provide the text, and one month of support. You pay 50% at the start and 50% at the end. My next 5 clients also get a launch price of 10% off, in return for letting me show their site in my work with a written testimonial.'),
             ('How long does it take to build a landing page?', 'Up to 4 business days from the day I receive all the materials, not counting days spent waiting for your reply.'),
             ('Can I expand the landing page into a full website later?', "Yes. The landing page becomes the home page or one of the pages of the website, so we don't start from scratch."),
             ('Does a landing page show up on Google?', "Yes. It's built ready for Google, but it usually shows up for fewer searches than a full website, because it has fewer pages."),
             ('What do I need to prepare?', 'Your logo, a sentence about your business, the service you want to promote, and photos if you have them. I write the full copy.')],
        related=['business-website', 'logo-branding'],
    ),
    dict(
        slug='business-website', kw='Small business website design',
        title='Small business website design from ₪4,000 | YYG Web Design',
        desc='A business website with an admin panel you update yourself: up to 6 pages, start with 10% and see 3 designs, live within 10 business days. From ₪4,000.',
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
        title='Online store development from ₪7,000 | YYG Web Design',
        desc='An online store with a cart, card payments and shipping, plus product and order management. Up to 30 products included. From ₪7,000, no VAT is added.',
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
        title='Websites for institutions, nonprofits and classes | YYG Web Design',
        desc='Websites for institutions, nonprofits and classes: sign-up, user accounts, a mailing list and 3 email templates. From ₪6,000, final price, no VAT is added.',
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
        title='Logo design and branding from ₪590 | YYG Web Design',
        desc='Logo design and branding: basic logo ₪590, professional logo with 3 sketches ₪1,500, basic branding ₪2,500, and a full brand with a website from ₪5,900.',
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
# ---------- Expansion: more useful content per page, more questions, and two pages by business type ----------
PAY_FAQ = ('How does payment work?', 'In three steps. 10% to start, and then you get 3 complete designs with your own content. You choose a direction and pay another 40%. You pay the remaining 50% at the end, after you approve everything and before the site goes live.')
NONE_FAQ = ("What if none of the designs feel right?", "You mark what you liked in a feedback form, and I work it into the next direction, within the revision rounds. If you still decide not to go ahead, the 10% covers the design work, and you don't pay anything more.")
WHY = '''<h2>Why build with me</h2>
<ul>
<li><b>See it before you go ahead</b>: you start with just 10% and get 3 complete designs with your own content. You only move on to the build after you choose. In the market, people often pay 30% to 50% before they see anything.</li>
<li><b>A final price, known up front</b>: no VAT is added, and the first year of domain, hosting and business email is included. The full price appears in a formal quote before we start.</li>
<li><b>A fast website</b>: built with clean code, without a heavy theme or unnecessary plugins, so it loads fast even on a phone and a weak connection.</li>
<li><b>Everything in your name</b>: the domain, the website and the content belong to you, and you can move to another company at any time.</li>
<li><b>Clear copy</b>: I write it in your customers' language, without jargon, and you approve every word.</li>
<li><b>Real support</b>: 4 months after launch, for bugs, questions and small changes.</li>
</ul>'''

MORE = {
'landing-page': '''<h2>What affects the price of a landing page</h2>
<p>The price starts at ₪2,000, and anything that can add to it is known up front and appears in the quote:</p>
<ul>
<li><b>The amount of content</b>: up to 7 sections are included. A longer page, for example with many services or a detailed price list, is usually a better fit for a <a class="text-link" href="/en/services/business-website.html">multi-page website</a>.</li>
<li><b>A second language</b>: ₪500.</li>
<li><b>An extra design</b>: if you'd like to see another direction after the 3 designs, each extra design is ₪400.</li>
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
<p>During the 4 months of support, I fix bugs and answer questions. A landing page has no admin panel, so I make small text or image changes for you. If you later want to expand to a full website, the page becomes the home page, and we don't start from scratch. Not sure what fits? Read <a class="text-link" href="/en/guides/landing-or-website.html">landing page or full website</a>, or answer 3 questions in the <a class="text-link" href="/en/#plan">price calculator</a>.</p>
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
<p>Up to 6 pages are included, and each extra page is ₪350, so you can plan exactly how many you need. The <a class="text-link" href="/en/#plan">price calculator</a> suggests a list of pages by business type.</p>
<h2>What an admin panel is, and what you update in it</h2>
<p>An admin panel is a simple screen where you update whatever changes, without touching the design: prices, hours, promotions, photos, new projects or recipes. At HaKef SheBaAfiya, for example, the owner adds a new recipe on her own, and it appears on the site in the same design as everything else. I set up in advance what you update yourself and what stays fixed and protected, so the site can't be broken by accident.</p>
<h2>What affects the price</h2>
<ul>
<li><b>The number of pages</b>: up to 6 are included, and each extra page is ₪350.</li>
<li><b>What the site does</b>: presenting and inquiries are included. Sign-ups, user accounts or a mailing list are the <a class="text-link" href="/en/services/institutions.html">website with sign-ups</a> package, and selling with online payment is an <a class="text-link" href="/en/services/online-store.html">online store</a>.</li>
<li><b>A second language</b>: ₪1,000.</li>
<li><b>Logo and branding</b>, if you don't have them yet: <a class="text-link" href="/en/services/logo-branding.html">logo design and branding</a>.</li>
</ul>
<p>The final price appears in a formal quote before we start, so you know exactly what you're paying for.</p>
<h2>Timeline</h2>
<p>Up to 10 business days from the day I have all the materials: first 3 designs, and after you choose a direction, building all the pages, connecting the admin panel, testing on phone and computer, and training. The clock starts on the day I receive the logo, the key points for the copy and the photos. More on what speeds things up or slows them down in the guide <a class="text-link" href="/en/guides/how-long-to-build.html">how long it takes to build a website</a>.</p>
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
<li><b>The number of products</b>: up to 30 products that I add are included, and each extra 10 are ₪200. After launch, you add products yourself at no cost.</li>
<li><b>Product options</b>: sizes, colors or add-ons for each product take more setup, and that goes into the quote.</li>
<li><b>Shipping</b>: a flat rate, a rate by area, or self-pickup.</li>
<li><b>A second language</b>: ₪1,800.</li>
</ul>
<h2>What to prepare for a store</h2>
<ul>
<li><b>A product list</b>: name, price, a short description and a photo. A simple spreadsheet is enough.</li>
<li><b>An account with a payment provider</b>. It's worth opening one right at the start, because approval takes a few days.</li>
<li><b>A shipping and returns policy</b>: shipping cost, delivery time, and how returns work. I prepare the terms based on that, and you approve them.</li>
</ul>
<h2>Timeline</h2>
<p>Up to 20 business days from the day I have all the materials. What usually stretches out a store isn't the build, but the product list and the payment provider's approval, so it's worth starting on both on day one. More in the guide <a class="text-link" href="/en/guides/how-long-to-build.html">how long it takes to build a website</a>.</p>
<h2>After the store is live</h2>
<p>I walk you through managing products, inventory and orders, and I stay available during the 4 months of support. It's also worth opening a Google Business Profile and linking it to the store, so people find you on Maps too. Not sure whether you need a store or a website? The <a class="text-link" href="/en/#plan">price calculator</a> recommends one based on what people need to do on your site.</p>''',

'institutions': '''<h2>What good sign-up looks like</h2>
<ul>
<li><b>Find first, then sign up</b>: a parent chooses an age and a city, and sees only what fits. At Pirchei Shomayim, choosing a class fills in the branch on the form automatically.</li>
<li><b>A form that's as short as possible</b>: at first, ask only for what you need to get back to people, and complete the rest in a call.</li>
<li><b>Email confirmation</b>: whoever signs up knows their request arrived, and the office gets an organized request with all the details.</li>
<li><b>Simple management</b>: an admin area with all the requests and their status, and the subscriber list.</li>
</ul>
<h2>A mailing list, done right</h2>
<p>The mailing list is built with explicit consent: people who sign up check that they want updates, and every email has an unsubscribe link. That way you only send to people who want to hear from you, which is also what Israeli law requires for promotional email. 3 ready email templates are included, for example a new class opening, an event reminder and a general update, and you send them yourself from the admin area.</p>
<h2>What affects the price</h2>
<ul>
<li><b>The number of pages</b>: up to 8 are included, and each extra page is ₪350.</li>
<li><b>The number of branches or classes</b>, and whether you need search by age, city or topic.</li>
<li><b>Online payment</b>: connecting a payment provider, at an extra cost shown in the quote.</li>
<li><b>A second language</b>: ₪1,500.</li>
</ul>
<h2>Timeline</h2>
<p>Up to 15 business days from the day I have all the materials: the list of classes or activities, the branches, hours if they can be published, suitable photos and the logo. It's worth publishing as much information as helps people decide, like the day, an approximate time and the location, to save repeat questions.</p>
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
<p>You bring the idea and the name of the business, and I build everything else in one language: logo and branding, a <a class="text-link" href="/en/services/landing-page.html">landing page</a> or a <a class="text-link" href="/en/services/business-website.html">website</a>, all the copy, a business card and flyer, social media images, and a Google Business Profile. For a full brand, payment works in stages like a website: 10% to start, 40% after you choose a direction, and 50% at the end.</p>
<h2>How long it takes</h2>
<p>It mainly depends on how quickly answers come back between sketches and revisions. The exact schedule appears in the quote. With a full brand, the logo and the website are built back to back, so work on the website starts as soon as the branding is approved.</p>''',
}

EXTRA_FAQ = {
'landing-page': [('How much does a landing page cost?', 'From ₪2,000, final price, no VAT is added. It includes 3 designs, 3 revision rounds, the copy, and the first year of domain, hosting and business email.'), PAY_FAQ, NONE_FAQ],
'business-website': [('How much does a business website cost?', 'From ₪4,000, final price, no VAT is added, including up to 6 pages, an admin panel, 3 designs, 3 revision rounds and the first year of domain, hosting and business email.'), PAY_FAQ, ('Who writes the copy?', 'I do, with the help of AI and personal editing. You give me the key points, and you approve every text before it goes live.')],
'online-store': [PAY_FAQ, ('Can I start with fewer products?', 'Yes. You can go live with your main products and add more yourself later from the admin panel.'), ('How long does it take to build a store?', 'Up to 20 business days from the day I have all the materials, including the product list. Open an account with a payment provider at the start, because approval takes a few days.'), ('Is there a monthly fee for the store?', "There's no store subscription fee. The first year of domain, hosting and business email is included, then ₪590 a year with All-in Care, or with your own hosting company. Per-transaction fees go to the payment provider.")],
'institutions': [('How much does a website for an institution or class cost?', 'From ₪6,000, final price, no VAT is added, including up to 8 pages, sign-up, user accounts, a mailing list and 3 email templates.'), PAY_FAQ, ('How long does it take to build a website for an institution?', 'Up to 15 business days from the day I have all the materials: the list of classes or activities, the branches, photos and the logo.'), ('Can I add an English version?', 'Yes, for ₪1,500. Pirchei Shomayim, for example, has a full English version.')],
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
        title='Websites for catering and food businesses from ₪2,000 | YYG Web Design',
        desc='Websites for caterers, bakeries and food businesses: a menu or catalog with photos, ordering on WhatsApp or by email, and an event quantity planner.',
        h1='Websites for catering and food businesses', lead='A website that shows your food at its best, answers the repeat questions in advance, and turns a visitor into an organized order on WhatsApp or by email.',
        price='2,000', price_n=2000, service='Website design for catering and food businesses',
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
<tr><td>One page: what you offer, photos, and ordering on WhatsApp or by email</td><td><a class="text-link" href="/en/services/landing-page.html">Landing page</a></td><td>From ₪2,000</td></tr>
<tr><td>A menu or catalog, a page per product, and updating prices, promotions and recipes yourself</td><td><a class="text-link" href="/en/services/business-website.html">Website with admin panel</a></td><td>From ₪4,000</td></tr>
<tr><td>Selling with online payment: boxes, platters or deliveries at fixed prices</td><td><a class="text-link" href="/en/services/online-store.html">Online store</a></td><td>From ₪7,000</td></tr>
</tbody></table></div>
<p>A quantity planner like the one at HaKef SheBaAfiya usually fits a website with an admin panel, and its price is set in the quote based on what it needs to calculate. All prices are final, and no VAT is added.</p>
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
        faq=[('How much does a catering website cost?', 'A landing page from ₪2,000, a website with a menu and admin panel from ₪4,000, and a store with online payment from ₪7,000. Prices are final, and no VAT is added.'),
             ('Can I get orders on WhatsApp?', "Yes. The button opens a ready message with the order details. Next to it is a form that sends the same request by email, for people without WhatsApp."),
             ('Can I take payment on the site?', 'Yes, with the store package, through an Israeli payment provider. If every order is custom, it is usually better to receive a request and agree on the price in a call.'),
             ('Can I update the menu and prices myself?', 'Yes, with a website with an admin panel. On a landing page, I make small changes for you.'),
             ('How long does it take to build a website for a food business?', 'A landing page up to 4 business days, a website with an admin panel up to 10, and a store up to 20, from the day I have all the materials.'),
             ('Do I need professional photography?', "It isn't required. Professional photography isn't included in the price, and good, real phone photos can be enough. I help choose and arrange them."),
             PAY_FAQ],
        related=['business-website', 'online-store', 'landing-page'],
    ),
    dict(
        slug='professionals', kw='Website design for trades and service providers',
        title='Websites for trades and service providers from ₪2,000 | YYG Web Design',
        desc='Websites for electricians, plumbers, consultants, therapists and private tutors: a call button, a service area, testimonials and a short form. Start with 10%.',
        h1='Websites for trades and service providers', lead='A website that makes your phone ring: anyone who finds you on Google understands right away what you do, where, and why to choose you, and calls with one tap.',
        price='2,000', price_n=2000, service='Website design for trades and service providers',
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
<tr><td>One or two services, and the whole goal is a phone call</td><td><a class="text-link" href="/en/services/landing-page.html">Landing page</a></td><td>From ₪2,000</td></tr>
<tr><td>Several services, a page for each, work samples and FAQ</td><td><a class="text-link" href="/en/services/business-website.html">Website with admin panel</a></td><td>From ₪4,000</td></tr>
<tr><td>Sign-ups for workshops, courses or groups, and a mailing list</td><td><a class="text-link" href="/en/services/institutions.html">Website with sign-ups</a></td><td>From ₪6,000</td></tr>
</tbody></table></div>
<p>All prices are final, and no VAT is added.</p>
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
        faq=[('How much does a website for a tradesperson cost?', 'A landing page from ₪2,000, and a multi-page website from ₪4,000. Prices are final, no VAT is added, and they include the first year of domain, hosting and business email.'),
             ('Do I need a website if I have a Google Business Profile?', "The profile is very important, but limited. On a website you explain each service, show work and prices, and get organized inquiries. They work best together."),
             ('How do I get more calls from my website?', 'A call button that is always visible on a phone, a clear service area, real testimonials and a short form. And a fast site, because a slow site on a phone loses people before they even see the button.'),
             ('How long does it take to build a website for a tradesperson?', 'A landing page up to 4 business days, and a multi-page website up to 10, from the day I have all the materials.'),
             ('Can I add WhatsApp?', 'Yes. A WhatsApp button with a ready message, next to the call button and the form.'),
             ('I work in several cities. Does that matter?', "It's worth listing them clearly on the site and in your Google Business Profile. With a multi-page website, you can add a page for your main areas, at ₪350 per page beyond 6."),
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
<meta property="og:image" content="{SITE}/assets/img/og-3.jpg">
<meta name="theme-color" content="#151920">
<link rel="icon" href="/assets/img/yyg-icon-96.png" type="image/png">
<link rel="preload" href="/assets/fonts/karantina-700-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/site.css?v=51">
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
  <p><a class="foot-link" href="/en/services/">Services</a> · <a class="foot-link" href="/en/guides/">Guides</a> · <a class="foot-link" href="/en/accessibility.html">Accessibility statement</a> · <a class="foot-link" href="/en/privacy.html">Privacy policy</a> · <a class="foot-link" href="/account/?lang=en">My account</a></p>
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
    desc="YYG Web Design services: landing pages, business websites, online stores, sites for institutions, catering and trades, and logo design and branding. Start with 10%.",
    h1='Services', lead='Websites, online stores, logos and branding for small businesses and institutions. With every service, you start with 10% and see the design before you go ahead.'), True, cards), encoding='utf-8')
print('ok', [p['slug'] for p in PAGES] + ['index'])
