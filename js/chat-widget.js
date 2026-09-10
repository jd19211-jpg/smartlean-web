const CHAT_ENDPOINT = 'api/chat.php';

let chatHistory = [];
let chatGreetingLang = null;

function chatT(key) {
  const lang = currentLang();
  return translations[lang][key] || key;
}

function scrollChatToBottom(container) {
  container.scrollTop = container.scrollHeight;
}

function renderBubble(container, role, text) {
  const bubble = document.createElement('div');
  bubble.className = `chat-bubble ${role}`;
  bubble.textContent = text;
  container.appendChild(bubble);
  scrollChatToBottom(container);
  return bubble;
}

function ensureGreeting(container) {
  const lang = currentLang();
  if (chatHistory.length === 0) {
    const greeting = chatT('chat.greeting');
    chatHistory.push({ role: 'assistant', text: greeting });
    renderBubble(container, 'assistant', greeting);
    chatGreetingLang = lang;
  }
}

function initChatWidget() {
  const widget = document.getElementById('chat-widget');
  const toggle = document.getElementById('chat-toggle');
  const panel = document.getElementById('chat-panel');
  const messages = document.getElementById('chat-messages');
  const form = document.getElementById('chat-form');
  const input = document.getElementById('chat-input');
  const sendBtn = form.querySelector('.chat-send');

  toggle.addEventListener('click', () => {
    const isOpen = widget.classList.toggle('open');
    panel.hidden = !isOpen;
    toggle.setAttribute('aria-expanded', String(isOpen));
    if (isOpen) {
      ensureGreeting(messages);
      input.focus();
    }
  });

  document.addEventListener('langchange', () => {
    if (chatHistory.length === 1 && chatGreetingLang && chatGreetingLang !== currentLang()) {
      messages.innerHTML = '';
      chatHistory = [];
      ensureGreeting(messages);
    }
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const text = input.value.trim();
    if (!text) return;

    input.value = '';
    input.disabled = true;
    sendBtn.disabled = true;

    chatHistory.push({ role: 'user', text });
    renderBubble(messages, 'user', text);

    const typingBubble = renderBubble(messages, 'assistant typing', chatT('chat.thinking'));

    try {
      const response = await fetch(CHAT_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ messages: chatHistory, lang: currentLang() })
      });

      if (!response.ok) throw new Error('Chat backend not available');

      const data = await response.json();
      if (data.error) throw new Error(data.error);
      typingBubble.remove();

      const reply = data.reply || chatT('chat.unavailable');
      chatHistory.push({ role: 'assistant', text: reply });
      renderBubble(messages, 'assistant', reply);

      if (data.leadCaptured) {
        renderBubble(messages, 'assistant', chatT('chat.leadNotice'));
      }
    } catch (err) {
      typingBubble.remove();
      renderBubble(messages, 'assistant', chatT('chat.unavailable'));
    } finally {
      input.disabled = false;
      sendBtn.disabled = false;
      input.focus();
    }
  });
}

document.addEventListener('DOMContentLoaded', initChatWidget);
