const LEAD_EMAIL = 'jd19211@gmail.com';
const FORMSPREE_ENDPOINT = 'https://formspree.io/f/YOUR_FORM_ID';

const translations = {
  sk: {
    'nav.home': 'Domov',
    'nav.about': 'O mne',
    'nav.services': 'Oblasti',
    'nav.process': 'Ako pracujem',
    'nav.cases': 'Referencie',
    'nav.contact': 'Kontakt',
    'hero.title': 'Igor',
    'hero.subtitle': 'Som Igor a pomôžem vám zlepšiť efektivitu, odstrániť plytvanie a zautomatizovať procesy pomocou overených metód Lean, Six Sigma a moderných nástrojov automatizácie.',
    'hero.cta': 'Dohodnúť konzultáciu',
    'hero.secondary': 'Pozrieť oblasti',
    'about.label': 'O mne',
    'about.heading': 'Praktický prístup k zlepšovaniu procesov',
    'about.body1': '„Zameriavam sa na to, aby firmy mali jasnú víziu, kam sa chcú dostať, správne nastavené procesy bez plytvania a čo najviac automatizovaných opakujúcich sa činností. Kombinujem praktický hands-on prístup — som v tom s vami, žiadna teória od stola. Podporujem zavádzanie zlepšovacích techník Lean a Six Sigma, doplnených modernými AI agentmi a automatizačnými nástrojmi, ktoré fungujú v reálnej prevádzke.“',
    'about.body2': 'Viac ako 15 rokov riadim projekty a zlepšovanie prevádzkových procesov — v oprave a reverznej logistike, ako projektový manažér v energetike, a v posledných rokoch ako transformačný konzultant. Som Ing. elektrotechniky so skúsenosťami naprieč EMEA, NAM aj LatAm a spájam Lean Six Sigma prístup s modernou automatizáciou a AI, aby zlepšenia reálne fungovali v prevádzke, nie len na papieri.',
    'services.label': 'Oblasti spolupráce',
    'services.heading': 'Ako tieto oblasti menia a pomáhajú',
    'services.management.title': 'Management',
    'services.management.body': 'Management nastavuje jasnú víziu, stratégiu a spôsob jej realizácie tak, aby ju poznal celý tím a dala sa merať.',
    'services.management.tag2': 'SMART ciele',
    'services.management.tag3': 'GROW model',
    'services.management.tag4': 'Jedno miesto pravdy',
    'services.management.tag5': 'Projektový manažment',
    'services.lean.body': 'Lean odstraňuje plytvanie a zjednodušuje procesy pomocou overených techník.',
    'services.lean.tag2': '5x Prečo',
    'services.lean.tag3': '8 typov plytvania',
    'services.sixsigma.body': 'Six Sigma znižuje variabilitu vo výrobe, procesoch aj výstupoch, aby boli výsledky konzistentné a predvídateľné.',
    'services.sixsigma.tag2': 'CTQ Trees',
    'services.sixsigma.tag3': 'Pareto',
    'services.automation.title': 'AI agenti a Automatizácia',
    'services.automation.body': 'Automatizácia zbavuje tím opakujúcich sa úloh pomocou RPA a AI agentov, aby mal čas na prácu s vyššou hodnotou.',
    'services.automation.tag3': 'AI agenti',
    'services.automation.tag4': 'Chatboty',
    'process.label': 'Postup',
    'process.heading': 'Ako pracujem',
    'process.step1.title': 'Definovanie',
    'process.step1.body': 'Spoločne definujeme problém, cieľ a očakávaný prínos.',
    'process.step2.title': 'Meranie',
    'process.step2.body': 'Zmapujeme súčasný stav procesu a zozbierame potrebné dáta.',
    'process.step3.title': 'Analýza',
    'process.step3.body': 'Nájdeme hlavné príčiny plytvania a variability.',
    'process.step4.title': 'Zlepšenie',
    'process.step4.body': 'Navrhneme a zavedieme riešenie — proces, štandard alebo automatizáciu.',
    'process.step5.title': 'Udržanie',
    'process.step5.body': 'Nastavíme meranie a štandardy, aby zlepšenie vydržalo aj po skončení spolupráce.',
    'cases.label': 'Prípadové štúdie',
    'cases.heading': 'Ako to vyzeralo v praxi',
    'cases.case1.metric': '[napr. -30 %]',
    'cases.case1.metricLabel': '[čo sa zlepšilo]',
    'cases.case1.title': '[Názov projektu / klient]',
    'cases.case1.body': '[Opíšte, aký problém ste riešili, čo ste urobili a aký bol výsledok — 2 až 3 vety.]',
    'cases.case2.metric': '[napr. -30 %]',
    'cases.case2.metricLabel': '[čo sa zlepšilo]',
    'cases.case2.title': '[Názov projektu / klient]',
    'cases.case2.body': '[Opíšte, aký problém ste riešili, čo ste urobili a aký bol výsledok — 2 až 3 vety.]',
    'cases.case3.metric': '[napr. -30 %]',
    'cases.case3.metricLabel': '[čo sa zlepšilo]',
    'cases.case3.title': '[Názov projektu / klient]',
    'cases.case3.body': '[Opíšte, aký problém ste riešili, čo ste urobili a aký bol výsledok — 2 až 3 vety.]',
    'contact.label': 'Kontakt',
    'contact.heading': 'Poďme to prebrať',
    'contact.subtitle': 'Napíšte mi, s čím potrebujete pomôcť, a ozvem sa vám čo najskôr.',
    'contact.emailNote': '[Doplňte prípadne priamy email / telefón / LinkedIn.]',
    'contact.form.name': 'Meno',
    'contact.form.email': 'Email',
    'contact.form.company': 'Spoločnosť (nepovinné)',
    'contact.form.message': 'Ako vám môžem pomôcť?',
    'contact.form.submit': 'Odoslať',
    'contact.form.sending': 'Odosielam…',
    'contact.form.success': 'Ďakujem! Ozvem sa vám čo najskôr.',
    'contact.form.error': 'Niečo sa pokazilo. Skúste to prosím znova, alebo mi napíšte priamo na ' + LEAD_EMAIL + '.',
    'contact.form.mailtoNotice': 'Otvorí sa váš emailový klient s predvyplnenou správou.',
    'footer.text': '© 2026 Igor. Všetky práva vyhradené.',
    'chat.toggleLabel': 'Otvoriť chat',
    'chat.title': 'Opýtajte sa ma',
    'chat.subtitle': 'AI asistent · Management, Lean, Six Sigma, Automatizácia',
    'chat.greeting': 'Dobrý deň! Som AI asistent Igora. Opýtajte sa ma na management, Lean, Six Sigma alebo automatizáciu procesov.',
    'chat.placeholder': 'Napíšte správu…',
    'chat.send': 'Odoslať',
    'chat.thinking': 'Píše…',
    'chat.unavailable': 'AI asistent momentálne nie je pripojený. Napíšte mi prosím priamo cez formulár nižšie alebo na ' + LEAD_EMAIL + '.',
    'chat.leadNotice': 'Ďakujem, mám váš kontakt — ozvem sa vám čo najskôr.'
  },
  en: {
    'nav.home': 'Home',
    'nav.about': 'About',
    'nav.services': 'Services',
    'nav.process': 'How I Work',
    'nav.cases': 'Case Studies',
    'nav.contact': 'Contact',
    'hero.title': 'Igor',
    'hero.subtitle': "I'm Igor, and I'll help you improve efficiency, eliminate waste, and automate your processes using proven Lean, Six Sigma methods and modern automation tools.",
    'hero.cta': 'Book a Consultation',
    'hero.secondary': 'See Services',
    'about.label': 'About',
    'about.heading': 'A practical approach to process improvement',
    'about.body1': '"I focus on giving companies a clear vision of where they want to go, well-designed waste-free processes, and as much automation of repetitive work as possible. I take a practical, hands-on approach — I\'m in it with you, no theory from behind a desk. I support introducing Lean and Six Sigma improvement techniques, backed by modern AI agents and automation tools that work in real operations."',
    'about.body2': "For more than 15 years I've managed projects and operational process improvement — in repair and reverse logistics, as a project manager in the energy sector, and in recent years as a transformation consultant. I hold an engineering degree in electrical engineering and have experience across EMEA, NAM, and LatAm, combining a Lean Six Sigma approach with modern automation and AI so improvements actually work in operations, not just on paper.",
    'services.label': 'Areas of Expertise',
    'services.heading': 'How these areas change and help',
    'services.management.title': 'Management',
    'services.management.body': 'Management sets a clear vision, strategy, and way of executing it so the whole team understands it and it can be measured.',
    'services.management.tag2': 'SMART goals',
    'services.management.tag3': 'GROW model',
    'services.management.tag4': 'Single source of truth',
    'services.management.tag5': 'Project management',
    'services.lean.body': 'Lean eliminates waste and simplifies processes using proven techniques.',
    'services.lean.tag2': '5 Whys',
    'services.lean.tag3': '8 wastes',
    'services.sixsigma.body': 'Six Sigma reduces variability in production, processes, and outputs so results are consistent and predictable.',
    'services.sixsigma.tag2': 'CTQ Trees',
    'services.sixsigma.tag3': 'Pareto',
    'services.automation.title': 'AI Agents & Automation',
    'services.automation.body': 'Automation frees the team from repetitive tasks using RPA and AI agents, for higher-value work.',
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
    'cases.case1.metric': '[e.g. -30%]',
    'cases.case1.metricLabel': '[what improved]',
    'cases.case1.title': '[Project name / client]',
    'cases.case1.body': '[Describe the problem you solved, what you did, and the result — 2-3 sentences.]',
    'cases.case2.metric': '[e.g. -30%]',
    'cases.case2.metricLabel': '[what improved]',
    'cases.case2.title': '[Project name / client]',
    'cases.case2.body': '[Describe the problem you solved, what you did, and the result — 2-3 sentences.]',
    'cases.case3.metric': '[e.g. -30%]',
    'cases.case3.metricLabel': '[what improved]',
    'cases.case3.title': '[Project name / client]',
    'cases.case3.body': '[Describe the problem you solved, what you did, and the result — 2-3 sentences.]',
    'contact.label': 'Contact',
    'contact.heading': "Let's talk",
    'contact.subtitle': "Tell me what you need help with, and I'll get back to you as soon as possible.",
    'contact.emailNote': '[Optionally add a direct email / phone / LinkedIn.]',
    'contact.form.name': 'Name',
    'contact.form.email': 'Email',
    'contact.form.company': 'Company (optional)',
    'contact.form.message': 'How can I help you?',
    'contact.form.submit': 'Send',
    'contact.form.sending': 'Sending…',
    'contact.form.success': "Thank you! I'll get back to you as soon as possible.",
    'contact.form.error': 'Something went wrong. Please try again, or email me directly at ' + LEAD_EMAIL + '.',
    'contact.form.mailtoNotice': 'Your email client will open with a pre-filled message.',
    'footer.text': '© 2026 Igor. All rights reserved.',
    'chat.toggleLabel': 'Open chat',
    'chat.title': 'Ask me anything',
    'chat.subtitle': 'AI assistant · Management, Lean, Six Sigma, Automation',
    'chat.greeting': "Hi! I'm Igor's AI assistant. Ask me about management, Lean, Six Sigma, or process automation.",
    'chat.placeholder': 'Type a message…',
    'chat.send': 'Send',
    'chat.thinking': 'Typing…',
    'chat.unavailable': 'The AI assistant is not connected yet. Please use the form below or email me directly at ' + LEAD_EMAIL + '.',
    'chat.leadNotice': "Thanks, I have your contact info — I'll get back to you as soon as possible."
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
    const name = data.get('name');
    const email = data.get('email');
    const company = data.get('company');
    const message = data.get('message');

    if (FORMSPREE_ENDPOINT.includes('YOUR_FORM_ID')) {
      const subject = encodeURIComponent(`Nová správa z webu od ${name}`);
      const body = encodeURIComponent(
        `Meno: ${name}\nEmail: ${email}\nSpoločnosť: ${company || '-'}\n\n${message}`
      );
      window.location.href = `mailto:${LEAD_EMAIL}?subject=${subject}&body=${body}`;
      status.textContent = translations[lang]['contact.form.mailtoNotice'];
      return;
    }

    submitBtn.disabled = true;
    status.textContent = translations[lang]['contact.form.sending'];

    try {
      const response = await fetch(FORMSPREE_ENDPOINT, {
        method: 'POST',
        headers: { Accept: 'application/json' },
        body: data
      });
      if (response.ok) {
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
