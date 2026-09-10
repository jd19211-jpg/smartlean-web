# Igor — Lead Gen Site

Static site (no build step): `index.html`, `css/styles.css`, `js/script.js`.

## Run locally

```
python -m http.server 8080
```
Then open http://localhost:8080

## Before going live

1. **Contact form → real email delivery.** Right now the form falls back to opening the visitor's email client (mailto). To get proper email notifications instead:
   - Sign up free at https://formspree.io, create a form, get your form ID.
   - In `js/script.js`, replace `YOUR_FORM_ID` in `FORMSPREE_ENDPOINT` with your real ID.
   - Leads will then be emailed to `jd19211@gmail.com` (change `LEAD_EMAIL` in the same file if needed).

2. **Fill in placeholders** (marked in gold/italic on the page):
   - About section bio (`about.body2` in `js/script.js`, both `sk` and `en`) — years of experience, certifications, industries.
   - Your photo — replace the "Vaša fotka" placeholder box in `index.html` (`.about-photo`) with an `<img>`.
   - Contact section note (`contact.emailNote`) — add direct email/phone/LinkedIn if you want them listed.

3. **Add a real surname/title** if you want one — currently just "Igor" per your instruction.

## AI chat widget

The floating chat button (bottom-right) is already built and wired up in the frontend
(`js/chat-widget.js`). Locally, with no backend deployed, it shows a graceful
"not connected yet" message — that's expected until you complete the steps below.

Architecture: the widget calls `POST api/chat.php` (relative path, so it works no matter
which folder you deploy the site into) which calls the Gemini API directly for fast
replies. If a visitor's message contains an email address, the script also fires your
**n8n webhook** (fire-and-forget) with the full transcript, so n8n can log/notify you —
the chat reply itself never waits on n8n.

**The Gemini key and n8n webhook URL must never be pasted into `js/` files** — they live
only in `api/config.php` on the server, which `.htaccess` blocks from direct HTTP access.

### 1. Get a Gemini API key
Sign up / sign in at https://aistudio.google.com/apikey and create a free API key.

### 2. Set up the n8n lead-capture workflow
1. Get n8n running — either https://n8n.cloud (free trial) or self-hosted.
2. In n8n, import [`n8n/lead-capture-workflow.json`](n8n/lead-capture-workflow.json)
   (Workflows → Import from File).
3. Open the **Send Email** node and attach your own SMTP (or swap it for a Gmail node) —
   the imported template has no credentials by design.
4. Activate the workflow, then copy the **Webhook** node's production URL
   (looks like `https://your-instance.app.n8n.cloud/webhook/lead-capture`).

### 3. Deploy on your Wedos (baterierychle.cz) hosting
Your hosting is standard PHP + MySQL shared hosting, so this runs there directly —
no separate account or service needed for the chat backend itself:

1. Upload the whole project folder via FTP into wherever you want it to live for now
   (e.g. `public_html/igor/`) — it can move later, the site uses only relative paths.
2. Edit `api/config.php` **directly on the server** (or edit locally then upload just
   that one file) and fill in the two values:
   ```php
   define('GEMINI_API_KEY', 'your-real-key-here');
   define('N8N_WEBHOOK_URL', 'your-n8n-webhook-url-here');
   ```
3. Confirm `api/.htaccess` uploaded too — it's what blocks `config.php` from being
   requested directly in a browser.
4. Open the site at whatever URL it landed on (e.g. `https://baterierychle.cz/igor/`)
   and test the chat widget for real.

That's it — no build step, no deploy command, just files on the server. PHP on Wedos
needs `curl` enabled for outbound HTTPS calls to Gemini/n8n, which is standard on their
shared hosting; if requests fail, that's the first thing to check with their support.

### Alternative: Vercel + Node (not needed for your current setup)
An equivalent Node.js version of the backend also exists at `api/chat.js`, for a
Vercel-style deploy (`npx vercel deploy`, with `GEMINI_API_KEY`/`N8N_WEBHOOK_URL` set as
environment variables in the Vercel dashboard). Ignore this unless you later move off
Wedos — `api/chat.php` is the one actually wired up and active right now.

### Testing locally
`python -m http.server` only serves static files (no PHP), so `api/chat.php` will 404
locally and the widget will show its fallback message — that's expected. To see the real
AI replies, test directly on the Wedos deployment once `config.php` is filled in.
