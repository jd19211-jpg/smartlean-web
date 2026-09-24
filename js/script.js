const LEAD_EMAIL = 'jd19211@gmail.com';
const CONTACT_ENDPOINT = 'api/contact.php';

const translations = {
  sk: {
    'nav.home': 'Domov',
    'nav.about': 'O mne',
    'nav.services': 'Oblasti',
    'nav.process': 'Ako pracujem',
    'nav.cases': 'Referencie',
    'nav.contact': 'Kontakt',
    'hero.title': 'Igor',
    'hero.subtitle': 'Som Igor a pomôžem vám odstrániť plytvanie a variabilitu, zlepšiť vaše procesy a automatizovať ich zavedením overených metód Lean, Six Sigma a modernej AI agentickej automatizácie.',
    'hero.cta': 'Dohodnúť konzultáciu',
    'hero.secondary': 'Pozrieť oblasti',
    'about.label': 'O mne',
    'about.heading': 'Praktický prístup k zlepšovaniu procesov',
    'about.body1': '„Zameriavam sa na to, aby firmy mali jasnú víziu, kam sa chcú dostať, správne nastavené procesy bez plytvania a čo najviac automatizovaných opakujúcich sa činností. Kombinujem praktický hands-on prístup — som v tom s vami, žiadna teória od stola. Podporujem zavádzanie zlepšovacích techník Lean a Six Sigma, doplnených modernými AI agentmi a automatizačnými nástrojmi, ktoré fungujú v reálnej prevádzke.“',
    'about.body2': 'Viac ako 15 rokov riadim projekty a zlepšovanie prevádzkových procesov — v oprave spotrebnej elektroniky pre významných OEM a s tým spojenej reverznej logistiky, ako projektový manažér v energetike, pri implementácii softvéru pre štátne organizácie aj pri návrhu a dodaní riešenia pre FMCG zákazníka, a v posledných rokoch ako transformačný konzultant — vrátane obratu firmy zo straty do zisku za 4 mesiace. Som certifikovaný Lean Six Sigma Green Belt so skúsenosťami naprieč EMEA, NAM aj LatAm.',
    'services.label': 'Oblasti spolupráce',
    'services.heading': 'Ako tieto oblasti menia a pomáhajú',
    'services.management.title': 'Management',
    'services.management.body': 'Management nastavuje jasnú víziu, stratégiu a spôsob jej realizácie tak, aby ju poznal celý tím a dala sa merať.',
    'services.management.quote': '„Úlohou managementu je nájsť ‚core of the core‘ toho, čo firma vytvára ako hodnotu. Všetko ostatné je balast.“',
    'services.management.tag2': 'SMART ciele',
    'services.management.tag3': 'GROW model',
    'services.management.tag4': 'Jedno miesto pravdy',
    'services.management.tag5': 'Projektový manažment',
    'services.lean.body': 'Lean odstraňuje plytvanie a zjednodušuje procesy pomocou overených techník.',
    'services.lean.quote': '„Väčšina firiem nemá problém s ľuďmi. Má problém s procesmi, ktoré škodia výsledkom a frustrujú ľudí.“',
    'services.lean.tag2': '5x Prečo',
    'services.lean.tag3': '8 typov plytvania',
    'services.sixsigma.body': 'Six Sigma znižuje variabilitu vo výrobe, procesoch aj výstupoch, aby boli výsledky konzistentné a predvídateľné.',
    'services.sixsigma.quote': '„Zákazníka nezaujíma váš aritmetický priemer. Zaujíma ho kvalita každého výrobku, ktorý mu dodáte.“',
    'services.sixsigma.tag2': 'CTQ Trees',
    'services.sixsigma.tag3': 'Pareto',
    'services.automation.title': 'AI agenti a Automatizácia',
    'services.automation.body': 'Automatizácia zbavuje tím opakujúcich sa úloh pomocou RPA a AI agentov, aby mal čas na prácu s vyššou hodnotou.',
    'services.automation.quote': '„Automatizácia a AI agenti nenahrádzajú ľudí. Robia prácu, ktorú nemusia robiť ľudia.“',
    'services.automation.tag3': 'AI agenti',
    'services.automation.tag4': 'Chatboty',
    'process.label': 'Postup',
    'process.heading': 'Ako pracujem',
    'process.step1.title': '(D) Definovanie',
    'process.step1.body': 'Spoločne definujeme problém, cieľ a očakávaný prínos.',
    'process.step2.title': '(M) Meranie',
    'process.step2.body': 'Zmapujeme súčasný stav procesu a zozbierame potrebné dáta.',
    'process.step3.title': '(A) Analýza',
    'process.step3.body': 'Nájdeme hlavné príčiny plytvania a variability.',
    'process.step4.title': '(I) Zlepšenie',
    'process.step4.body': 'Navrhneme a zavedieme riešenie — proces, štandard alebo automatizáciu.',
    'process.step5.title': '(C) Udržanie',
    'process.step5.body': 'Nastavíme meranie a štandardy, aby zlepšenie vydržalo aj po skončení spolupráce.',
    'cases.label': 'Prípadové štúdie',
    'cases.heading': 'Ako to vyzeralo v praxi',
    'cases.ai.metric': '~2 000 h + 24/7',
    'cases.ai.metricLabel': 'ušetrených hodín ročne a AI asistent nonstop',
    'cases.ai.title': 'RPA roboty a AI asistent namiesto rutiny',
    'cases.ai.body': 'Ľudia ročne strávili približne 2 000 hodín prepisovaním dát z jedného systému do druhého. Systémy boli v tej istej firme, ale nemali medzi sebou žiadne prepojenie, API ani priame volanie. Túto prácu prevzali RPA roboty a ľudia sa mohli venovať práci, ktorá potrebuje ich úsudok. Komunikáciu s návštevníkmi webu rieši AI asistent: odpovedá na otázky, overí voľný termín v kalendári a po potvrdení sám zarezervuje stretnutie a pošle pozvánku. Vyskúšajte ho vpravo dole.',
    'cases.management.metric': '4 mesiace',
    'cases.management.metricLabel': 'zo straty do zisku',
    'cases.management.title': 'Transformácia servisnej firmy',
    'cases.management.body': 'Servisná firma v oblasti opráv elektroniky bola v strate. Kľúčom bola zmena spôsobu myslenia a riadenia: zaviedli sme pravidelné plánovanie a vizuálny manažment, aby tímy videli plán aj skutočný výkon. Prevádzkové náklady klesli a počet FTE sa znížil o 20 %. Do 4 mesiacov sa firma dostala zo straty do zisku.',
    'cases.lean.metric': 'až 4×',
    'cases.lean.metricLabel': 'vyššia produktivita na niektorých pozíciách',
    'cases.lean.title': 'Nový tok práce v servise elektroniky',
    'cases.lean.body': 'V tej istej firme sme prestavali tok práce v dielni. Zaviedli sme kombináciu one-piece flow a dávkového spracovania (batch flow), metodiku 5S a výraznú vizualizáciu výrobného prostredia, aby bol stav práce viditeľný na prvý pohľad. Produktivita na niektorých pozíciách vzrástla až 4×.',
    'cases.sixsigma.metric': '9 000 → <600 ks',
    'cases.sixsigma.metricLabel': 'backlog opráv za 9 mesiacov',
    'cases.sixsigma.title': 'Backlog v servise elektroniky pre OEM výrobcov',
    'cases.sixsigma.body': 'Na sklade čakalo na opravu približne 9 000 zariadení, mnohé viac ako pol roka. Z dát o modeloch a typoch opráv sme vypočítali potrebnú kapacitu, zabezpečili materiál, preškolili ľudí a vytvorili samostatnú skupinu na backlog, bez straty kapacity pre bežné opravy. Zamerali sme sa aj na kvalitu, pretože pri internej chybovosti 5–6 % treba o toľko vyššiu kapacitu. Za 5 mesiacov klesol backlog na 2 000 kusov a za 9 mesiacov pod 600.',
    'contact.label': 'Kontakt',
    'contact.heading': 'Poďme to prebrať',
    'contact.subtitle': 'Napíšte mi, s čím potrebujete pomôcť, a ozvem sa vám čo najskôr.',
    'contact.form.name': 'Meno',
    'contact.form.email': 'Email',
    'contact.form.company': 'Spoločnosť (nepovinné)',
    'contact.form.message': 'Ako vám môžem pomôcť?',
    'contact.form.submit': 'Odoslať',
    'contact.form.sending': 'Odosielam…',
    'contact.form.success': 'Ďakujem! Ozvem sa vám čo najskôr.',
    'contact.form.error': 'Niečo sa pokazilo. Skúste to prosím znova, alebo mi napíšte priamo na ' + LEAD_EMAIL + '.',
    'footer.text': '© 2026 Igor. Všetky práva vyhradené.',
    'chat.toggleLabel': 'Otvoriť chat',
    'chat.title': 'Opýtajte sa ma',
    'chat.subtitle': 'AI asistent · Management, Lean, Six Sigma, Automatizácia',
    'chat.greeting': 'Dobrý deň! Som AI asistent Igora. Opýtajte sa ma na management, Lean, Six Sigma alebo automatizáciu procesov.',
    'chat.placeholder': 'Napíšte správu…',
    'chat.send': 'Odoslať',
    'chat.thinking': 'Píše…',
    'chat.unavailable': 'AI asistent momentálne nie je pripojený. Napíšte mi prosím priamo cez formulár nižšie alebo na ' + LEAD_EMAIL + '.'
  },
  en: {
    'nav.home': 'Home',
    'nav.about': 'About',
    'nav.services': 'Services',
    'nav.process': 'How I Work',
    'nav.cases': 'Case Studies',
    'nav.contact': 'Contact',
    'hero.title': 'Igor',
    'hero.subtitle': "I'm Igor, and I'll help you eliminate waste and variability, improve your processes, and automate them by implementing proven Lean, Six Sigma methods and modern AI agentic automation.",
    'hero.cta': 'Book a Consultation',
    'hero.secondary': 'See Services',
    'about.label': 'About',
    'about.heading': 'A practical approach to process improvement',
    'about.body1': '"I focus on giving companies a clear vision of where they want to go, well-designed waste-free processes, and as much automation of repetitive work as possible. I take a practical, hands-on approach — I\'m in it with you, no theory from behind a desk. I support introducing Lean and Six Sigma improvement techniques, backed by modern AI agents and automation tools that work in real operations."',
    'about.body2': "For more than 15 years I've managed projects and operational process improvement — in consumer electronics repair for major OEMs and the associated reverse logistics, as a project manager in the energy sector, implementing software for public-sector organizations and designing and delivering a solution for an FMCG client, and in recent years as a transformation consultant — including turning a company from a loss to a profit in 4 months. I'm a certified Lean Six Sigma Green Belt with experience across EMEA, NAM, and LatAm.",
    'services.label': 'Areas of Expertise',
    'services.heading': 'How these areas change and help',
    'services.management.title': 'Management',
    'services.management.body': 'Management sets a clear vision, strategy, and way of executing it so the whole team understands it and it can be measured.',
    'services.management.quote': '"The job of management is to find the \'core of the core\' of the value the company creates. Everything else is ballast."',
    'services.management.tag2': 'SMART goals',
    'services.management.tag3': 'GROW model',
    'services.management.tag4': 'Single source of truth',
    'services.management.tag5': 'Project management',
    'services.lean.body': 'Lean eliminates waste and simplifies processes using proven techniques.',
    'services.lean.quote': '"Most companies don\'t have a people problem. They have a process problem that hurts results and frustrates people."',
    'services.lean.tag2': '5 Whys',
    'services.lean.tag3': '8 wastes',
    'services.sixsigma.body': 'Six Sigma reduces variability in production, processes, and outputs so results are consistent and predictable.',
    'services.sixsigma.quote': '"Customers don\'t care about your arithmetic average. They care about the quality of every product you deliver."',
    'services.sixsigma.tag2': 'CTQ Trees',
    'services.sixsigma.tag3': 'Pareto',
    'services.automation.title': 'AI Agents & Automation',
    'services.automation.body': 'Automation frees the team from repetitive tasks using RPA and AI agents, for higher-value work.',
    'services.automation.quote': '"Automation and AI agents don\'t replace people. They do the work people don\'t have to do."',
    'services.automation.tag3': 'AI agents',
    'services.automation.tag4': 'Chatbots',
    'process.label': 'Process',
    'process.heading': 'How I Work',
    'process.step1.title': 'Define',
    'process.step1.body': 'We define the problem, goal, and expected benefit together.',
    'process.step2.title': 'Measure',
    'process.step2.body': 'We map the current state of the process and gather the necessary data.',
    'process.step3.title': 'Analyze',
    'process.step3.body': 'We identify the main root causes of waste and variability.',
    'process.step4.title': 'Improve',
    'process.step4.body': 'We design and implement the solution — a process, a standard, or automation.',
    'process.step5.title': 'Control',
    'process.step5.body': 'We set up measurement and standards so the improvement holds after the engagement ends.',
    'cases.label': 'Case Studies',
    'cases.heading': 'What it looked like in practice',
    'cases.ai.metric': '~2,000 h + 24/7',
    'cases.ai.metricLabel': 'hours saved per year and an AI assistant around the clock',
    'cases.ai.title': 'RPA robots and an AI assistant instead of routine',
    'cases.ai.body': 'People spent around 2,000 hours a year retyping data from one system into another. The systems belonged to the same company but had no integration, API or direct calls between them. RPA robots took over this work, freeing people for work that needs their judgement. Website visitors are handled by an AI assistant: it answers questions, checks for a free slot in the calendar and, once confirmed, books the meeting and sends the invitation on its own. Try it in the bottom right corner.',
    'cases.management.metric': '4 months',
    'cases.management.metricLabel': 'from loss to profit',
    'cases.management.title': 'Transforming a service company',
    'cases.management.body': 'An electronics repair service company was losing money. The key was changing how people thought and how the company was managed: we introduced regular planning and visual management so teams could see both the plan and actual performance. Operating costs went down and headcount (FTE) was reduced by 20%. Within 4 months the company went from loss to profit.',
    'cases.lean.metric': 'up to 4×',
    'cases.lean.metricLabel': 'higher productivity in some roles',
    'cases.lean.title': 'A new workflow in electronics repair',
    'cases.lean.body': 'In the same company we rebuilt the workflow on the shop floor. We introduced a combination of one-piece flow and batch flow, 5S and strong visual management of the production floor, so the status of work was visible at a glance. Productivity in some roles increased up to 4×.',
    'cases.sixsigma.metric': '9,000 → <600 units',
    'cases.sixsigma.metricLabel': 'repair backlog in 9 months',
    'cases.sixsigma.title': 'Repair backlog for OEM electronics',
    'cases.sixsigma.body': 'Around 9,000 devices were waiting for repair, many for more than six months. Using data on models and repair types, we calculated the capacity needed, secured materials, retrained staff and set up a dedicated backlog team, without taking capacity away from standard repairs. We also focused on quality, because an internal failure rate of 5–6% requires that much extra capacity. Within 5 months the backlog dropped to 2,000 units, and within 9 months to under 600.',
    'contact.label': 'Contact',
    'contact.heading': "Let's talk",
    'contact.subtitle': "Tell me what you need help with, and I'll get back to you as soon as possible.",
    'contact.form.name': 'Name',
    'contact.form.email': 'Email',
    'contact.form.company': 'Company (optional)',
    'contact.form.message': 'How can I help you?',
    'contact.form.submit': 'Send',
    'contact.form.sending': 'Sending…',
    'contact.form.success': "Thank you! I'll get back to you as soon as possible.",
    'contact.form.error': 'Something went wrong. Please try again, or email me directly at ' + LEAD_EMAIL + '.',
    'footer.text': '© 2026 Igor. All rights reserved.',
    'chat.toggleLabel': 'Open chat',
    'chat.title': 'Ask me anything',
    'chat.subtitle': 'AI assistant · Management, Lean, Six Sigma, Automation',
    'chat.greeting': "Hi! I'm Igor's AI assistant. Ask me about management, Lean, Six Sigma, or process automation.",
    'chat.placeholder': 'Type a message…',
    'chat.send': 'Send',
    'chat.thinking': 'Typing…',
    'chat.unavailable': 'The AI assistant is not connected yet. Please use the form below or email me directly at ' + LEAD_EMAIL + '.'
  }
};

function applyLanguage(lang) {
  document.documentElement.lang = lang;
  document.querySelectorAll('[data-i18n]').forEach((el) => {
    const key = el.getAttribute('data-i18n');
    const value = translations[lang][key];
    if (value !== undefined) el.textContent = value;
  });
  document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
    const key = el.getAttribute('data-i18n-placeholder');
    const value = translations[lang][key];
    if (value !== undefined) el.placeholder = value;
  });
  document.querySelectorAll('[data-i18n-aria-label]').forEach((el) => {
    const key = el.getAttribute('data-i18n-aria-label');
    const value = translations[lang][key];
    if (value !== undefined) el.setAttribute('aria-label', value);
  });
  document.getElementById('lang-toggle').textContent = lang === 'sk' ? 'EN' : 'SK';
  try { localStorage.setItem('lang', lang); } catch (e) {}
  document.dispatchEvent(new CustomEvent('langchange', { detail: { lang } }));
}

function initLanguage() {
  let lang = 'sk';
  try {
    lang = localStorage.getItem('lang') || 'sk';
  } catch (e) {}
  applyLanguage(lang);

  document.getElementById('lang-toggle').addEventListener('click', () => {
    const current = document.documentElement.lang === 'sk' ? 'sk' : 'en';
    applyLanguage(current === 'sk' ? 'en' : 'sk');
  });
}

function initNav() {
  const toggle = document.getElementById('nav-toggle');
  const nav = document.getElementById('main-nav');
  toggle.addEventListener('click', () => {
    const isOpen = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });
  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      nav.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
}

function currentLang() {
  return document.documentElement.lang === 'en' ? 'en' : 'sk';
}

function initForm() {
  const form = document.getElementById('lead-form');
  const status = document.getElementById('form-status');
  const submitBtn = document.getElementById('form-submit');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const lang = currentLang();
    const data = new FormData(form);
    data.append('lang', lang);

    submitBtn.disabled = true;
    status.textContent = translations[lang]['contact.form.sending'];

    try {
      const response = await fetch(CONTACT_ENDPOINT, {
        method: 'POST',
        headers: { Accept: 'application/json' },
        body: data
      });
      const result = await response.json().catch(() => ({}));
      if (response.ok && result.ok) {
        status.textContent = translations[lang]['contact.form.success'];
        form.reset();
      } else {
        status.textContent = translations[lang]['contact.form.error'];
      }
    } catch (err) {
      status.textContent = translations[lang]['contact.form.error'];
    } finally {
      submitBtn.disabled = false;
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initLanguage();
  initNav();
  initForm();
});
