const EMAIL_REGEX = /[\w.+-]+@[\w-]+\.[\w.-]+/;

const SYSTEM_PROMPT = {
  sk: `Si AI asistent na webe Igora, konzultanta pre Management, Lean, Six Sigma a Automatizáciu (UiPath, n8n, AI agenti).
Tvoja úloha je stručne a vecne odpovedať na otázky návštevníkov o týchto oblastiach a o tom, ako môže Igor pomôcť ich firme.
Ak návštevník prejaví reálny záujem o spoluprácu, zdvorilo ho požiadaj o email, aby sa mu Igor mohol ozvať.
Odpovedaj v slovenčine, stručne (max 3-4 vety), profesionálne a priateľsky. Nevymýšľaj si konkrétne ceny, termíny ani referencie, ktoré nepoznáš.`,
  en: `You are the AI assistant on Igor's website, a consultant for Management, Lean, Six Sigma, and Automation (UiPath, n8n, AI agents).
Your job is to answer visitor questions about these areas concisely and helpfully, and explain how Igor can help their company.
If a visitor shows genuine interest in working together, politely ask for their email so Igor can follow up.
Reply in English, concisely (max 3-4 sentences), professional and friendly. Do not invent specific prices, availability, or references you don't know.`
};

module.exports = async function handler(req, res) {
  if (req.method !== 'POST') {
    res.status(405).json({ error: 'Method not allowed' });
    return;
  }

  const apiKey = process.env.GEMINI_API_KEY;
  if (!apiKey) {
    res.status(500).json({ error: 'Server not configured: missing GEMINI_API_KEY' });
    return;
  }

  const { messages, lang } = req.body || {};
  if (!Array.isArray(messages) || messages.length === 0) {
    res.status(400).json({ error: 'messages array required' });
    return;
  }

  const language = lang === 'en' ? 'en' : 'sk';
  const systemPrompt = SYSTEM_PROMPT[language];

  try {
    const geminiResponse = await fetch(
      `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${apiKey}`,
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          systemInstruction: { parts: [{ text: systemPrompt }] },
          contents: messages.map((m) => ({
            role: m.role === 'assistant' ? 'model' : 'user',
            parts: [{ text: String(m.text || '').slice(0, 2000) }]
          }))
        })
      }
    );

    const geminiData = await geminiResponse.json();

    if (!geminiResponse.ok) {
      res.status(502).json({ error: 'Gemini API error', detail: geminiData });
      return;
    }

    const reply = geminiData?.candidates?.[0]?.content?.parts?.[0]?.text
      || (language === 'sk'
        ? 'Prepáčte, momentálne neviem odpovedať. Skúste to prosím znova.'
        : "Sorry, I couldn't generate a reply. Please try again.");

    const lastUserMessage = [...messages].reverse().find((m) => m.role === 'user');
    const emailMatch = lastUserMessage ? String(lastUserMessage.text || '').match(EMAIL_REGEX) : null;
    let leadCaptured = false;

    if (emailMatch && process.env.N8N_WEBHOOK_URL) {
      leadCaptured = true;
      try {
        await fetch(process.env.N8N_WEBHOOK_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            email: emailMatch[0],
            lang: language,
            transcript: messages,
            timestamp: new Date().toISOString()
          })
        });
      } catch (webhookErr) {
        leadCaptured = false;
      }
    }

    res.status(200).json({ reply, leadCaptured });
  } catch (err) {
    res.status(500).json({ error: 'Unexpected server error' });
  }
};
