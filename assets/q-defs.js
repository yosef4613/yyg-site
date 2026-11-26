/* YYG Web Design: התוכן של שני השאלונים, בעברית ובאנגלית.
   סוגי שאלות: text, email, tel, url, date, textarea, radio, checks (אפשר max), scale (1 עד 5 עם תוויות בקצוות).
   other: true מוסיף "אחר" עם שדה טקסט. show: {id, in: [...]} מציג את השאלה רק אחרי תשובה מסוימת. */
window.YYG_FORMS = {
  intake: (t) => ({
    title: t('שאלון אפיון לאתר', 'Website discovery questionnaire'),
    done: t('תודה! השאלון הגיע אליי. אעבור עליו ואחזור אליכם תוך יום עבודה, עם שאלות אם יהיו, ועם הצעת מחיר מסודרת.', 'Thank you! Your questionnaire reached me. I\'ll go over it and get back to you within one business day, with any questions and a formal quote.'),
    steps: [
      { title: t('העסק', 'The business'), intro: t('כ־10 דקות. אפשר לדלג על כל שאלה, ומה שלא יודעים סוגרים יחד בשיחה. התשובות נשמרות אצלכם בדפדפן תוך כדי, כך שאפשר להפסיק ולחזור.', 'About 10 minutes. You can skip any question, and we\'ll settle anything you\'re unsure about on a call. Your answers are saved in your browser as you go, so you can stop and come back.'),
        qs: [
          { id: 'business', type: 'text', label: t('שם העסק', 'Business name'), auto: 'organization' },
          { id: 'biz_what', type: 'textarea', label: t('מה העסק עושה, במשפט או שניים?', 'What does the business do, in a sentence or two?'), hint: t('כמו שהייתם מסבירים לחבר.', 'The way you\'d explain it to a friend.') },
          { id: 'biz_field', type: 'radio', label: t('באיזה תחום?', 'Which field?'), other: true, options: [t('מוצרים או חנות', 'Products or a store'), t('שירות מקצועי', 'A professional service'), t('אוכל, קייטרינג ואירועים', 'Food, catering and events'), t('מוסד, עמותה או חוג', 'An institution, nonprofit or classes'), t('בריאות וטיפול', 'Health and therapy'), t('חינוך והוראה', 'Education and tutoring')] },
          { id: 'biz_area', type: 'radio', label: t('איפה אתם נותנים שירות?', 'Where do you serve customers?'), options: [t('בעיר או באזור מסוים', 'In a specific city or area'), t('בכל הארץ', 'Across the country'), t('אונליין', 'Online'), t('גם בחו״ל', 'Abroad too')] },
          { id: 'biz_area_where', type: 'text', label: t('באיזו עיר או אזור?', 'Which city or area?'), show: { id: 'biz_area', in: [t('בעיר או באזור מסוים', 'In a specific city or area')] } },
          { id: 'biz_age', type: 'radio', label: t('כמה זמן העסק קיים?', 'How long has the business existed?'), options: [t('מתחיל עכשיו', 'Just starting'), t('עד שנה', 'Up to a year'), t('שנה עד 5 שנים', '1 to 5 years'), t('יותר מ־5 שנים', 'More than 5 years')] },
          { id: 'biz_unique', type: 'textarea', label: t('למה לקוחות בוחרים בכם ולא במישהו אחר?', 'Why do customers choose you over someone else?'), hint: t('שלושה דברים מספיקים. זה הלב של האתר.', 'Three things are enough. This is the heart of the site.') },
          { id: 'competitors', type: 'textarea', label: t('מתחרים או עסקים דומים שאתם מכירים', 'Competitors or similar businesses you know'), hint: t('שם או קישור. גם אם האתר שלהם לא מוצא חן בעיניכם, זה עוזר.', 'A name or a link. Even if you don\'t like their site, it helps.') }
        ] },
      { title: t('השם והפרטים באתר', 'Name and details on the site'), intro: t('הפרטים שיופיעו באתר עצמו. מה שלא בטוח, נסגור יחד.', 'The details that will appear on the site itself. Anything you\'re unsure about, we\'ll settle together.'),
        qs: [
          { id: 'site_name', type: 'text', label: t('השם שיופיע באתר', 'The name shown on the site'), hint: t('בדיוק כמו שצריך להיכתב, כולל אותיות באנגלית אם יש.', 'Exactly as it should be written.') },
          { id: 'slogan', type: 'text', label: t('משפט קצר מתחת לשם (סלוגן)', 'A short line under the name (tagline)'), hint: t('אם אין, אציע כמה אפשרויות.', 'If you don\'t have one, I\'ll suggest a few.') },
          { id: 'domain_wish', type: 'text', ltr: true, label: t('כתובת האתר הרצויה', 'The web address you want'), hint: t('למשל mybusiness.co.il. אם כבר יש כתובת, כתבו אותה.', 'For example mybusiness.com. If you already have one, write it.') },
          { id: 'site_phone', type: 'tel', label: t('טלפון שיופיע באתר', 'Phone number to show on the site') },
          { id: 'site_whatsapp', type: 'tel', label: t('מספר וואטסאפ, אם שונה', 'WhatsApp number, if different') },
          { id: 'site_email', type: 'email', label: t('מייל שיופיע באתר', 'Email to show on the site') },
          { id: 'site_address', type: 'text', label: t('כתובת העסק', 'Business address'), hint: t('רק אם יש קבלת קהל או משלוחים מאזור מסוים.', 'Only if customers visit you, or you deliver from a specific area.') },
          { id: 'site_hours', type: 'text', label: t('שעות פעילות שיופיעו באתר', 'Opening hours to show on the site'), hint: t('למשל: א׳ עד ה׳, 9:00 עד 17:00', 'For example: Sun to Thu, 9:00 to 17:00') },
          { id: 'social', type: 'textarea', label: t('קישורים לרשתות ולפרופיל העסק בגוגל', 'Links to your social profiles and Google Business Profile') }
        ] },
      { title: t('הלקוחות', 'Your customers'),
        qs: [
          { id: 'cust_who', type: 'checks', label: t('מי הלקוחות שלכם?', 'Who are your customers?'), other: true, options: [t('לקוחות פרטיים', 'Private customers'), t('בעלי עסקים', 'Business owners'), t('מוסדות וארגונים', 'Institutions and organizations'), t('הורים', 'Parents'), t('צעירים', 'Young people'), t('מבוגרים', 'Older people'), t('ציבור דתי וחרדי', 'A religious community'), t('דוברי אנגלית', 'English speakers')] },
          { id: 'cust_need', type: 'textarea', label: t('איזה צורך או בעיה הלקוח בא לפתור אצלכם?', 'What need or problem does the customer come to solve with you?') },
          { id: 'cust_questions', type: 'textarea', label: t('מה לקוחות שואלים הכי הרבה לפני שהם מזמינים?', 'What do customers ask most before they order?'), hint: t('התשובות האלה יהפכו לשאלות הנפוצות באתר, וחוסכות לכם שיחות.', 'These become the site\'s FAQ, and save you calls.') },
          { id: 'cust_from', type: 'checks', label: t('איך לקוחות מגיעים אליכם היום?', 'How do customers find you today?'), other: true, options: [t('המלצות מפה לאוזן', 'Word of mouth'), t('גוגל', 'Google'), t('רשתות חברתיות', 'Social media'), t('קבוצות וואטסאפ', 'WhatsApp groups'), t('פרסום בעלונים ובעיתונים', 'Print ads'), t('חנות או שלט', 'A shop or a sign'), t('עוד אין לקוחות', 'No customers yet')] },
          { id: 'cust_sensitive', type: 'textarea', label: t('יש רגישויות של הקהל שחשוב לשמור עליהן?', 'Are there audience sensitivities to respect?'), hint: t('למשל סוג התמונות, ניסוחים או צבעים.', 'For example the kind of images, wording or colors.') }
        ] },
      { title: t('המטרה של האתר', 'What the site is for'),
        qs: [
          { id: 'goal_action', type: 'radio', label: t('מה הדבר הכי חשוב שגולש יעשה באתר?', 'What\'s the single most important thing a visitor should do?'), other: true, options: [t('להתקשר', 'Call'), t('לשלוח הודעת וואטסאפ', 'Send a WhatsApp message'), t('להשאיר פרטים בטופס', 'Leave details in a form'), t('להזמין או לבקש הצעת מחיר', 'Order or request a quote'), t('להירשם', 'Sign up'), t('לקנות ולשלם באתר', 'Buy and pay on the site'), t('להתרשם ולקרוא', 'Read and get to know us')] },
          { id: 'goal_success', type: 'textarea', label: t('איך תדעו שהאתר הצליח?', 'How will you know the site is working?'), hint: t('למשל: 10 פניות בחודש, 30 הרשמות לחוג, פחות שיחות עם אותן שאלות.', 'For example: 10 inquiries a month, 30 class sign-ups, fewer calls with the same questions.') },
          { id: 'goal_channels', type: 'checks', label: t('איך הכי נוח לכם לקבל פניות?', 'How do you prefer to receive inquiries?'), options: [t('טלפון', 'Phone'), t('וואטסאפ', 'WhatsApp'), t('מייל', 'Email'), t('טופס עם כל הפרטים', 'A form with all the details'), t('הזמנה מסודרת באתר', 'A structured order on the site')] }
        ] },
      { title: t('מה יהיה באתר', 'What goes on the site'),
        qs: [
          { id: 'pages', type: 'checks', label: t('אילו עמודים או חלקים צריך?', 'Which pages or sections do you need?'), other: true, options: [t('עמוד בית', 'Home'), t('אודות', 'About'), t('שירותים', 'Services'), t('מוצרים או קטלוג', 'Products or a catalog'), t('מחירון', 'Price list'), t('עבודות וגלריה', 'Work and gallery'), t('המלצות', 'Testimonials'), t('שאלות נפוצות', 'FAQ'), t('מדור תוכן או בלוג', 'Content section or blog'), t('צור קשר', 'Contact'), t('הרשמה', 'Sign-up'), t('חנות', 'Store'), t('אזור אישי ללקוחות', 'Customer accounts')] },
          { id: 'features', type: 'checks', label: t('אילו יכולות?', 'Which features?'), other: true, options: [t('טופס פנייה', 'Contact form'), t('כפתור וואטסאפ', 'WhatsApp button'), t('כפתור חיוג', 'Call button'), t('זימון תורים ביומן', 'Appointment booking'), t('תשלום באשראי', 'Card payments'), t('הרשמה עם אישור במייל', 'Sign-up with email confirmation'), t('רשימת תפוצה', 'Mailing list'), t('מפה והוראות הגעה', 'Map and directions'), t('גלריה עם הגדלה', 'Gallery with zoom'), t('נגן מוזיקה או וידאו', 'Music or video player'), t('מחשבון או שאלון', 'A calculator or questionnaire')] },
          { id: 'prices_show', type: 'radio', label: t('להציג מחירים באתר?', 'Show prices on the site?'), options: [t('כן, מחירים מלאים', 'Yes, full prices'), t('רק "החל מ־"', 'Only "from" prices'), t('לא, רק אחרי פנייה', 'No, only after an inquiry'), t('עוד לא החלטתי', 'Not sure yet')] },
          { id: 'update_who', type: 'radio', label: t('מי יעדכן את האתר אחרי שיעלה?', 'Who will update the site after launch?'), options: [t('אני או מישהו מהעסק', 'Me or someone in the business'), t('אשמח שאתה תעדכן', 'I\'d like you to do it'), t('כמעט לא צריך עדכונים', 'It hardly needs updates')] },
          { id: 'update_what', type: 'textarea', label: t('מה צריך להתעדכן לעיתים קרובות?', 'What needs updating often?'), hint: t('למשל מוצרים, מבצעים, אירועים, שעות פתיחה.', 'For example products, offers, events, opening hours.') }
        ] },
      { title: t('תוכן וחומרים', 'Content and materials'), intro: t('שלחו כמה שיותר: לוגו, תמונות וקבצים. ככל שיש לי יותר חומר, האתר מדויק יותר ומוכן מהר יותר. כל קובץ עד 60 MB, ועד 400 MB בסך הכול. יש יותר, או קבצים ענקיים? שימו אותם בתיקייה בגוגל דרייב והדביקו את הקישור בסוף השלב. קבצים לא נשמרים אם סוגרים את הדף, אז כדאי לצרף אותם כשמוכנים לשלוח.', 'Send as much as you can: logo, photos and files. The more material I have, the more accurate the site and the sooner it\'s ready. Up to 60 MB per file and 400 MB in total. More than that, or huge files? Put them in a Google Drive folder and paste the link at the end of this step. Files aren\'t kept if you close the page, so attach them when you\'re ready to send.'),
        qs: [
          { id: 'logo', type: 'radio', label: t('לוגו', 'Logo'), options: [t('יש לוגו בקובץ איכותי', 'I have a good-quality logo file'), t('יש רק תמונה או צילום של הלוגו', 'I only have a picture of it'), t('אין, צריך לוגו', 'None, I need a logo'), t('יש, אבל רוצים לחדש', 'I have one, but want a new one')] },
          { id: 'logo_file', type: 'file', max: 6, accept: '.svg,.ai,.eps,.pdf,.png,.jpg,.jpeg,.webp,.gif', label: t('צרפו את הלוגו', 'Attach your logo'), hint: t('הקובץ הכי איכותי שיש: SVG, AI, PDF או PNG. גם צילום ברור עוזר.', 'The best-quality file you have: SVG, AI, PDF or PNG. A clear photo helps too.'), show: { id: 'logo', in: [t('יש לוגו בקובץ איכותי', 'I have a good-quality logo file'), t('יש רק תמונה או צילום של הלוגו', 'I only have a picture of it'), t('יש, אבל רוצים לחדש', 'I have one, but want a new one')] } },
          { id: 'about_text', type: 'textarea', rows: 5, label: t('כמה משפטים עליכם, לעמוד "אודות"', 'A few sentences about you, for the About page'), hint: t('מי אתם, איך התחלתם, ומה חשוב לכם. גם נקודות מספיקות, ואני אנסח.', 'Who you are, how you started, and what matters to you. Bullet points are fine; I\'ll write it up.') },
          { id: 'services_text', type: 'textarea', rows: 6, label: t('השירותים או המוצרים, אחד בכל שורה', 'Your services or products, one per line'), hint: t('עם תיאור קצר, ומחיר אם רוצים להציג אותו באתר.', 'With a short description, and a price if you want it shown.') },
          { id: 'texts', type: 'radio', label: t('ושאר הטקסטים לאתר?', 'And the rest of the website text?'), options: [t('יש טקסטים מוכנים, אצרף', 'I have text ready, I\'ll attach it'), t('יש חלק', 'I have some'), t('אשמח שתכתוב', 'I\'d like you to write it')] },
          { id: 'docs', type: 'file', max: 12, accept: '.pdf,.doc,.docx,.txt,.rtf,.odt,.xls,.xlsx,.ppt,.pptx', label: t('קבצים: טקסטים, מחירון, תפריט או קטלוג', 'Files: text, price list, menu or catalog'), hint: t('Word, PDF או Excel. לא חובה.', 'Word, PDF or Excel. Optional.') },
          { id: 'photos', type: 'radio', label: t('תמונות', 'Photos'), options: [t('יש תמונות מקצועיות', 'I have professional photos'), t('יש תמונות מהטלפון', 'I have phone photos'), t('אין, צריך תמונות', 'None, I need photos')] },
          { id: 'photo_files', type: 'file', max: 50, resize: true, accept: '.jpg,.jpeg,.png,.webp,.heic,.heif,.gif', label: t('צרפו תמונות', 'Attach photos'), hint: t('עבודות, מוצרים, המקום והעסק. עד 50 תמונות, וכמה שיותר יותר טוב. אני מקטין אותן אוטומטית כדי שהשליחה תהיה מהירה. יש יותר? קישור לתיקייה בגוגל דרייב בסוף השלב.', 'Work, products, the place and the business. Up to 50 photos, and the more the better. They\'re shrunk automatically so sending is quick. Have more? A Google Drive folder link at the end of this step.'), show: { id: 'photos', in: [t('יש תמונות מקצועיות', 'I have professional photos'), t('יש תמונות מהטלפון', 'I have phone photos')] } },
          { id: 'reviews', type: 'radio', label: t('המלצות של לקוחות', 'Customer testimonials'), options: [t('יש', 'I have some'), t('אפשר לאסוף', 'I can collect some'), t('עוד אין', 'None yet')] },
          { id: 'reviews_text', type: 'textarea', rows: 4, label: t('ההמלצות עצמן', 'The testimonials themselves'), hint: t('שם הממליץ, ומה כתב. מה שיש.', 'The person\'s name and what they wrote. Whatever you have.'), show: { id: 'reviews', in: [t('יש', 'I have some')] } },
          { id: 'brand_colors', type: 'text', label: t('הצבעים של העסק, אם יש', 'Your brand colors, if any'), hint: t('למשל: זהב ושחור', 'For example: gold and black') },
          { id: 'assets_link', type: 'url', label: t('קישור לתיקייה עם עוד חומרים', 'A link to a folder with more materials'), hint: t('Google Drive, Dropbox או WeTransfer. מומלץ כשיש הרבה תמונות, סרטונים או קבצים גדולים. בגוגל דרייב: לחיצה ימנית על התיקייה, "שיתוף", ו"כל מי שיש לו את הקישור".', 'Google Drive, Dropbox or WeTransfer. Recommended when you have many photos, videos or large files. In Google Drive: right-click the folder, "Share", then "Anyone with the link".') }
        ] },
      { title: t('עיצוב ותחושה', 'Design and feel'),
        qs: [
          { id: 'feel', type: 'checks', max: 3, label: t('3 מילים שהאתר צריך לשדר', '3 words the site should convey'), hint: t('בחרו עד שלוש.', 'Pick up to three.'), options: [t('יוקרתי', 'Premium'), t('חם ומשפחתי', 'Warm and personal'), t('מקצועי ואמין', 'Professional and trustworthy'), t('מודרני', 'Modern'), t('קלאסי', 'Classic'), t('צעיר ושמח', 'Young and cheerful'), t('שקט ונקי', 'Calm and clean'), t('נועז ומרשים', 'Bold and striking'), t('טבעי', 'Natural')] },
          { id: 'bg', type: 'radio', label: t('רקע מועדף', 'Preferred background'), options: [t('בהיר', 'Light'), t('כהה', 'Dark'), t('אשמח לראות את שניהם', 'I\'d like to see both'), t('לא יודע/ת', 'Not sure')] },
          { id: 'sc_formal', type: 'scale', label: t('איפה האתר שלכם על הסקאלה?', 'Where does your site sit on the scale?'), from: t('רשמי', 'Formal'), to: t('חם ואישי', 'Warm and personal') },
          { id: 'sc_rich', type: 'scale', label: '', from: t('מינימליסטי', 'Minimal'), to: t('עשיר ומפורט', 'Rich and detailed') },
          { id: 'sc_bold', type: 'scale', label: '', from: t('שקט ומאופק', 'Quiet and restrained'), to: t('בולט ונועז', 'Bold and loud') },
          { id: 'likes', type: 'textarea', label: t('אתרים שאתם אוהבים, ומה אהבתם בהם', 'Sites you like, and what you like about them'), hint: t('קישור ומשפט. לא חייב להיות מהתחום שלכם.', 'A link and a sentence. They don\'t have to be in your field.') },
          { id: 'dislikes', type: 'textarea', label: t('מה אתם לא רוצים לראות באתר?', 'What don\'t you want on your site?'), hint: t('צבעים, סגנון, או דברים שמעצבנים אתכם באתרים אחרים.', 'Colors, styles, or things that annoy you on other sites.') },
          { id: 'motion', type: 'radio', label: t('תנועה ואנימציות', 'Motion and animation'), options: [t('מעט ועדין', 'A little, subtle'), t('בינוני', 'Some'), t('הרבה, שיהיה מרשים', 'Lots, make it impressive'), t('בלי', 'None')] }
        ] },
      { title: t('פרטים טכניים', 'Technical details'),
        qs: [
          { id: 'domain', type: 'radio', label: t('הכתובת (הדומיין) כבר שלכם?', 'Do you already own the web address (domain)?'), options: [t('כן, רשומה על שמי', 'Yes, it\'s registered to me'), t('לא, צריך לרשום', 'No, it needs registering'), t('לא יודע/ת', 'Not sure')] },
          { id: 'site_now', type: 'radio', label: t('יש אתר קיים?', 'Do you have a site now?'), options: [t('כן', 'Yes'), t('לא', 'No')] },
          { id: 'site_url', type: 'url', label: t('הכתובת של האתר הקיים', 'Your current site\'s address'), show: { id: 'site_now', in: [t('כן', 'Yes')] } },
          { id: 'site_good_bad', type: 'textarea', label: t('מה טוב בו, ומה לא?', 'What works in it, and what doesn\'t?'), hint: t('חשוב לי לדעת, כדי לשמור על מה שכבר עובד בגוגל.', 'This helps me keep what already works on Google.'), show: { id: 'site_now', in: [t('כן', 'Yes')] } },
          { id: 'biz_email', type: 'radio', label: t('מייל עסקי (שם@העסק)', 'Business email (name@yourbusiness)'), options: [t('יש', 'I have one'), t('אין, רוצה', 'None, I want one'), t('לא צריך', 'I don\'t need one')] },
          { id: 'languages', type: 'checks', label: t('באילו שפות?', 'Which languages?'), other: true, options: [t('עברית', 'Hebrew'), t('אנגלית', 'English'), t('יידיש', 'Yiddish'), t('רוסית', 'Russian'), t('צרפתית', 'French')] }
        ] },
      { title: t('זמנים ותקציב', 'Timing and budget'),
        qs: [
          { id: 'deadline', type: 'radio', label: t('מתי האתר צריך להיות באוויר?', 'When does the site need to be live?'), options: [t('אין לחץ', 'No rush'), t('תוך חודש', 'Within a month'), t('תוך שבועיים', 'Within two weeks'), t('יש תאריך מסוים', 'By a specific date')] },
          { id: 'deadline_date', type: 'date', label: t('איזה תאריך, ולמה?', 'Which date, and why?'), show: { id: 'deadline', in: [t('יש תאריך מסוים', 'By a specific date')] } },
          { id: 'deadline_why', type: 'text', label: '', hint: t('למשל השקה, אירוע או תחילת שנה.', 'For example a launch, an event or a new season.'), show: { id: 'deadline', in: [t('יש תאריך מסוים', 'By a specific date')] } },
          { id: 'package', type: 'radio', label: t('איזה מסלול מעניין אתכם?', 'Which package interests you?'), options: [t('דף נחיתה בסיסי (1,290 ₪)', 'Basic landing page (₪1,290)'), t('דף נחיתה (מ־2,000 ₪)', 'Landing page (from ₪2,000)'), t('אתר עם אזור ניהול (מ־4,000 ₪)', 'Website with admin panel (from ₪4,000)'), t('אתר עם הרשמה ורשימת תפוצה (מ־6,000 ₪)', 'Website with sign-ups (from ₪6,000)'), t('חנות אונליין (מ־7,000 ₪)', 'Online store (from ₪7,000)'), t('מותג מ־0 עד 100% (מ־5,900 ₪)', 'Brand from 0 to 100% (from ₪5,900)'), t('לא בטוח/ה, אשמח להמלצה', 'Not sure, I\'d like a recommendation')] },
          { id: 'budget', type: 'radio', label: t('תקציב משוער', 'Rough budget'), hint: t('עוזר לי להציע את מה שנכון לכם, ולא יותר.', 'Helps me suggest what\'s right for you, and no more.'), options: [t('עד 2,000 ₪', 'Up to ₪2,000'), t('2,000 עד 4,000 ₪', '₪2,000 to ₪4,000'), t('4,000 עד 7,000 ₪', '₪4,000 to ₪7,000'), t('מעל 7,000 ₪', 'Over ₪7,000'), t('עוד לא יודע/ת', 'Not sure yet')] },
          { id: 'deciders', type: 'text', label: t('מי עוד מעורב בהחלטות על האתר?', 'Who else is involved in decisions about the site?'), hint: t('שותף, ועד, הנהלה. כדי שכולם יראו את העיצובים בזמן.', 'A partner, a board, management. So everyone sees the designs in time.') },
          { id: 'extra', type: 'textarea', label: t('משהו נוסף שחשוב שאדע?', 'Anything else I should know?') }
        ] },
      { title: t('לאן לשלוח את ההצעה', 'Where to send the quote'), intro: t('אחרון: לאן לשלוח את ההצעה המסודרת. מיד אחרי השליחה רואים כאן הערכת מחיר לפי התשובות.', 'Last step: where to send the formal quote. Right after you send, you see a price estimate from your answers here.'),
        qs: [
          { id: 'name', type: 'text', label: t('שם', 'Name'), required: true, auto: 'name' },
          { id: 'email', type: 'email', label: t('מייל', 'Email'), required: true, hint: t('לכאן תגיע הצעת המחיר', 'Your quote will arrive here'), auto: 'email' },
          { id: 'phone', type: 'tel', label: t('טלפון', 'Phone'), hint: t('לא חובה', 'Optional'), auto: 'tel' },
          { id: 'when', type: 'radio', label: t('אם נצטרך לדבר בטלפון, מתי נוח לכם?', 'If we need to talk by phone, when suits you?'), options: [t('בבוקר', 'Morning'), t('בצהריים', 'Midday'), t('אחר הצהריים', 'Afternoon'), t('לא משנה', 'Any time')] }
        ] }
    ]
  }),

  feedback: (t) => {
    const parts = [t('צבעים', 'Colors'), t('פונטים', 'Fonts'), t('תמונות', 'Images'), t('מבנה ופריסה', 'Layout'), t('הכותרת הראשית', 'The main headline'), t('כפתורים', 'Buttons'), t('תנועה ואנימציות', 'Motion'), t('התחושה הכללית', 'The overall feel')];
    const design = (n) => ({ title: t(`עיצוב ${n}`, `Design ${n}`), intro: t(`מה לקחת מעיצוב ${n} הלאה, ומה להשאיר מאחור. גם בעיצוב שלא בחרתם יש לפעמים דבר אחד ששווה לשמור.`, `What to take forward from design ${n}, and what to leave behind. Even a design you didn't pick may have one thing worth keeping.`),
      qs: [
        { id: `d${n}_like`, type: 'checks', label: t('מה אהבתם?', 'What did you like?'), options: parts },
        { id: `d${n}_dislike`, type: 'checks', label: t('מה פחות?', 'What didn\'t work?'), options: parts },
        { id: `d${n}_note`, type: 'textarea', label: t('הערה חופשית', 'Notes'), hint: t('הכי עוזר: "את X במקום Y". למשל: "את הכותרת מעיצוב 2, בצבעים של עיצוב 1".', 'Most helpful: "X instead of Y". For example: "the headline from design 2, in the colors of design 1".') }
      ] });
    return {
      title: t('משוב על העיצובים', 'Design feedback'),
      done: t('תודה! המשוב הגיע אליי. אעבור עליו, ואחזור אליכם תוך יום עבודה עם הכיוון לסבב הבא.', 'Thank you! Your feedback reached me. I\'ll go over it and get back to you within one business day with the direction for the next round.'),
      steps: [
        { title: t('פרטים', 'Your details'), intro: t('את השאלון הזה ממלאים אחרי שמקבלים 3 עיצובים של האתר. כ־5 דקות. כדאי לפתוח את העיצובים בחלון אחר, ולעבור עליהם גם בטלפון.', 'You fill this in after you receive 3 designs of your site. About 5 minutes. Keep the designs open in another window, and look at them on your phone too.'),
          qs: [
            { id: 'name', type: 'text', label: t('שם', 'Name'), required: true, auto: 'name' },
            { id: 'email', type: 'email', label: t('מייל', 'Email'), required: true, auto: 'email' },
            { id: 'phone', type: 'tel', label: t('טלפון', 'Phone'), hint: t('לא חובה', 'Optional'), auto: 'tel' },
            { id: 'business', type: 'text', label: t('שם העסק', 'Business name'), auto: 'organization' },
            { id: 'quote', type: 'text', label: t('מספר ההצעה', 'Quote number'), hint: t('לא חובה. מופיע בהצעת המחיר, למשל YYG-2026-001', 'Optional. It\'s on your quote, for example YYG-2026-001'), ltr: true }
          ] },
        { title: t('הבחירה', 'Your choice'),
          qs: [
            { id: 'choice', type: 'radio', label: t('איזה עיצוב הכי קרוב למה שרציתם?', 'Which design is closest to what you wanted?'), options: [t('עיצוב 1', 'Design 1'), t('עיצוב 2', 'Design 2'), t('עיצוב 3', 'Design 3'), t('שילוב של כמה', 'A mix of several'), t('אף אחד עדיין', 'None yet')] },
            { id: 'rate1', type: 'scale', label: t('כמה כל עיצוב מרגיש נכון לעסק שלכם?', 'How right does each design feel for your business?'), prefix: t('עיצוב 1', 'Design 1'), from: t('בכלל לא', 'Not at all'), to: t('בדיוק', 'Exactly') },
            { id: 'rate2', type: 'scale', label: '', prefix: t('עיצוב 2', 'Design 2'), from: t('בכלל לא', 'Not at all'), to: t('בדיוק', 'Exactly') },
            { id: 'rate3', type: 'scale', label: '', prefix: t('עיצוב 3', 'Design 3'), from: t('בכלל לא', 'Not at all'), to: t('בדיוק', 'Exactly') },
            { id: 'first', type: 'textarea', label: t('מה הרגשתם בשנייה הראשונה, בעיצוב שהכי אהבתם?', 'What did you feel in the first second, on the design you liked most?'), hint: t('הרושם הראשון של הלקוחות שלכם יהיה דומה.', 'Your customers\' first impression will be similar.') }
          ] },
        design(1), design(2), design(3),
        { title: t('הכיוון לסבב הבא', 'Direction for the next round'), intro: t('האמצע של כל סקאלה הוא "בדיוק ככה".', 'The middle of each scale means "just right".'),
          qs: [
            { id: 'colors', type: 'radio', label: t('צבעים', 'Colors'), other: true, options: [t('להשאיר כמו שהם', 'Keep them'), t('בהירים יותר', 'Lighter'), t('כהים יותר', 'Darker'), t('צבעים אחרים', 'Different colors')] },
            { id: 'dir_warm', type: 'scale', label: t('לאן לזוז?', 'Which way to move?'), from: t('יותר רשמי', 'More formal'), to: t('יותר חם', 'Warmer') },
            { id: 'dir_detail', type: 'scale', label: '', from: t('פחות פרטים', 'Less detail'), to: t('יותר פרטים', 'More detail') },
            { id: 'dir_bold', type: 'scale', label: '', from: t('יותר שקט', 'Quieter'), to: t('יותר בולט', 'Bolder') },
            { id: 'dir_modern', type: 'scale', label: '', from: t('יותר קלאסי', 'More classic'), to: t('יותר מודרני', 'More modern') },
            { id: 'fonts', type: 'radio', label: t('פונטים', 'Fonts'), options: [t('מתאימים', 'They work'), t('צריך קריא יותר', 'More readable'), t('צריך מיוחד יותר', 'More distinctive')] },
            { id: 'images', type: 'radio', label: t('תמונות', 'Images'), options: [t('מתאימות', 'They work'), t('להחליף לתמונות שלנו', 'Use our own photos'), t('תמונות אחרות מהמאגר', 'Different stock photos')] },
            { id: 'images_note', type: 'textarea', label: t('מה לשנות בתמונות?', 'What should change in the images?'), show: { id: 'images', in: [t('להחליף לתמונות שלנו', 'Use our own photos'), t('תמונות אחרות מהמאגר', 'Different stock photos')] } }
          ] },
        { title: t('התוכן', 'The content'),
          qs: [
            { id: 'texts_ok', type: 'radio', label: t('הטקסטים', 'The text'), options: [t('מדויקים', 'Accurate'), t('צריך תיקונים', 'Needs corrections'), t('חסר תוכן', 'Something\'s missing')] },
            { id: 'texts_fix', type: 'textarea', label: t('מה לתקן או להוסיף?', 'What should be corrected or added?'), hint: t('אפשר להעתיק משפט מהעיצוב, ולכתוב איך הוא צריך להיות.', 'You can copy a sentence from the design and write how it should read.') },
            { id: 'missing', type: 'textarea', label: t('משהו חסר באתר?', 'Is anything missing from the site?'), hint: t('עמוד, שירות, מידע, כפתור.', 'A page, a service, information, a button.') }
          ] },
        { title: t('לסיום', 'To finish'),
          qs: [
            { id: 'mobile', type: 'radio', label: t('בדקתם בטלפון?', 'Did you check on your phone?'), options: [t('כן, והכול נוח', 'Yes, it all works'), t('כן, ומשהו לא נוח', 'Yes, and something isn\'t comfortable'), t('עוד לא', 'Not yet')] },
            { id: 'mobile_note', type: 'textarea', label: t('מה לא נוח בטלפון?', 'What isn\'t comfortable on the phone?'), show: { id: 'mobile', in: [t('כן, ומשהו לא נוח', 'Yes, and something isn\'t comfortable')] } },
            { id: 'top3', type: 'textarea', label: t('3 הדברים הכי חשובים לשנות, לפי הסדר', 'The 3 most important changes, in order'), hint: t('אם יש רק דבר אחד, גם מצוין.', 'If there\'s only one, that\'s great too.') },
            { id: 'call', type: 'radio', label: t('לעבור יחד על המשוב?', 'Go over the feedback together?'), options: [t('אשמח לשיחה קצרה', 'I\'d like a short call'), t('אפשר להמשיך בלי שיחה', 'Go ahead without a call')] },
            { id: 'ready', type: 'checks', label: t('מוכנים להתקדם?', 'Ready to move on?'), options: [t('אני מאשר/ת להתקדם עם הכיוון שבחרתי. אחרי זה משלמים 40% ומתחילים לבנות.', 'I approve moving forward with the direction I chose. Next, the 40% payment, and the build begins.')] },
            { id: 'extra', type: 'textarea', label: t('משהו נוסף?', 'Anything else?') }
          ] }
      ]
    };
  }
};
