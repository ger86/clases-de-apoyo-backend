const ASSISTANT_ROLE = 'assistant';
const USER_ROLE = 'user';
const MIN_TEXTAREA_HEIGHT = 72;
const MAX_TEXTAREA_HEIGHT = 180;

let mathJaxPromise;

function normalizeAssistantMessage(text) {
  const normalizedText = text
    .replace(/\r\n/g, '\n')
    .replace(/\\\[(.*?)\\\]/gs, (_, block) => `$$${block.trim()}$$`)
    .replace(/^ {0,3}#{1,6}\s+/gm, '')
    .replace(/\*\*(.*?)\*\*/gs, '$1')
    .replace(/__(.*?)__/gs, '$1')
    .replace(/```[\w-]*\n?/g, '')
    .replace(/`([^`]+)`/g, '$1')
    .replace(/^>\s?/gm, '')
    .replace(/\n{3,}/g, '\n\n')
    .trim();

  return normalizedText || text.trim();
}

function hasMathMarkup(text) {
  return /(\$\$[\s\S]+?\$\$)|(\$[^$]+?\$)|(\\\([\s\S]+?\\\))|(\\\[[\s\S]+?\\\])|(\\(?:frac|sqrt|cdot|times|lambda|alpha|beta|gamma|theta|pi|sum|int|begin|end|left|right|neq|geq|leq))/.test(
    text,
  );
}

function escapeHtml(text) {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

function ensureMathJax() {
  if (window.MathJax?.typesetPromise) {
    return Promise.resolve(window.MathJax);
  }

  if (mathJaxPromise) {
    return mathJaxPromise;
  }

  window.MathJax = window.MathJax || {
    tex: {
      inlineMath: [
        ['$', '$'],
        ['\\(', '\\)'],
      ],
      displayMath: [
        ['$$', '$$'],
        ['\\[', '\\]'],
      ],
    },
    svg: {
      fontCache: 'global',
    },
  };

  mathJaxPromise = new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js';
    script.async = true;
    script.onload = () => {
      const startupPromise = window.MathJax?.startup?.promise;

      if (!startupPromise) {
        resolve(window.MathJax);
        return;
      }

      startupPromise.then(() => resolve(window.MathJax)).catch(reject);
    };
    script.onerror = () => reject(new Error('No se ha podido cargar MathJax.'));
    document.head.appendChild(script);
  });

  return mathJaxPromise;
}

function buildParagraphsHtml(text) {
  return escapeHtml(text)
    .split(/\n{2,}/)
    .filter(Boolean)
    .map(
      paragraph =>
        `<p>${paragraph.replace(/\n/g, '<br />').replace(/ {2}/g, ' &nbsp;')}</p>`,
    )
    .join('');
}

function renderAssistantBody(container, text) {
  const normalizedText = normalizeAssistantMessage(text);

  if (!hasMathMarkup(normalizedText)) {
    container.textContent = normalizedText;
    return;
  }

  container.innerHTML = buildParagraphsHtml(normalizedText);

  ensureMathJax()
    .then(() => {
      if (window.MathJax?.typesetClear) {
        window.MathJax.typesetClear([container]);
      }

      return window.MathJax?.typesetPromise
        ? window.MathJax.typesetPromise([container])
        : Promise.resolve();
    })
    .catch(() => {
      container.textContent = normalizedText;
    });
}

function createMessage(role, text) {
  return {
    id: `${role}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    role,
    text,
  };
}

function createElement(tagName, className) {
  const element = document.createElement(tagName);

  if (className) {
    element.className = className;
  }

  return element;
}

function getErrorMessage(error) {
  if (error instanceof Error && error.message.trim() !== '') {
    return error.message;
  }

  return 'No se ha podido consultar la IA. Inténtalo de nuevo.';
}

function parseErrorMessage(responseBody) {
  if (!responseBody || typeof responseBody.message !== 'string') {
    return null;
  }

  return responseBody.message.trim() || null;
}

function createMessageElement(message) {
  const isUser = message.role === USER_ROLE;
  const row = createElement(
    'div',
    `file-ai-message-row ${isUser ? 'file-ai-message-row-user' : 'file-ai-message-row-assistant'}`,
  );
  const bubble = createElement(
    'div',
    `file-ai-message-bubble ${isUser ? 'file-ai-message-bubble-user' : 'file-ai-message-bubble-assistant'}`,
  );
  const role = createElement(
    'p',
    `file-ai-message-role ${isUser ? 'file-ai-message-role-user' : ''}`,
  );
  const body = createElement(
    'div',
    `file-ai-message-body ${isUser ? 'file-ai-message-body-user' : ''}`,
  );

  role.textContent = isUser ? 'Tú' : 'Profesor IA';

  if (isUser) {
    body.textContent = message.text;
  } else {
    renderAssistantBody(body, message.text);
  }

  bubble.append(role, body);
  row.appendChild(bubble);

  return row;
}

function createLoadingElement() {
  const row = createElement(
    'div',
    'file-ai-message-row file-ai-message-row-assistant',
  );
  const bubble = createElement(
    'div',
    'file-ai-message-bubble file-ai-message-bubble-assistant file-ai-loading-bubble',
  );
  const spinner = createElement('span', 'file-ai-loading-spinner');
  const text = createElement('span', 'file-ai-loading-text');

  text.textContent = 'Analizando el archivo...';

  bubble.append(spinner, text);
  row.appendChild(bubble);

  return row;
}

function scrollMessagesToBottom(elements) {
  requestAnimationFrame(() => {
    elements.messages.scrollTop = elements.messages.scrollHeight;
  });
}

function adjustTextareaHeight(textarea) {
  textarea.style.height = 'auto';
  const nextHeight = Math.min(
    MAX_TEXTAREA_HEIGHT,
    Math.max(MIN_TEXTAREA_HEIGHT, textarea.scrollHeight),
  );
  textarea.style.height = `${nextHeight}px`;
}

function initChat(root) {
  const elements = {
    root,
    open: root.querySelector('[data-file-ai-open]'),
    close: root.querySelector('[data-file-ai-close]'),
    backdrop: root.querySelector('[data-file-ai-backdrop]'),
    drawer: root.querySelector('[data-file-ai-drawer]'),
    messages: root.querySelector('[data-file-ai-messages]'),
    empty: root.querySelector('[data-file-ai-empty]'),
    error: root.querySelector('[data-file-ai-error]'),
    form: root.querySelector('[data-file-ai-form]'),
    input: root.querySelector('[data-file-ai-input]'),
    submit: root.querySelector('[data-file-ai-submit]'),
  };

  if (
    !elements.open ||
    !elements.close ||
    !elements.backdrop ||
    !elements.drawer ||
    !elements.messages ||
    !elements.empty ||
    !elements.error ||
    !elements.form ||
    !elements.input ||
    !elements.submit
  ) {
    return;
  }

  const config = {
    endpoint: root.dataset.fileAiEndpoint || '',
    csrfToken: root.dataset.fileAiCsrfToken || '',
  };

  if (config.endpoint === '' || config.csrfToken === '') {
    return;
  }

  const state = {
    isOpen: false,
    isSending: false,
    errorMessage: '',
    messages: [],
  };

  const render = () => {
    elements.drawer.classList.toggle('file-ai-drawer-visible', state.isOpen);
    elements.backdrop.classList.toggle('file-ai-backdrop-visible', state.isOpen);
    elements.drawer.setAttribute('aria-hidden', state.isOpen ? 'false' : 'true');
    document.body.classList.toggle('file-ai-chat-open', state.isOpen);

    elements.submit.disabled = state.isSending || elements.input.value.trim() === '';
    elements.submit.classList.toggle(
      'file-ai-send-button-disabled',
      elements.submit.disabled,
    );

    if (state.errorMessage) {
      elements.error.hidden = false;
      elements.error.textContent = state.errorMessage;
    } else {
      elements.error.hidden = true;
      elements.error.textContent = '';
    }

    const previousMessages = Array.from(
      elements.messages.querySelectorAll('[data-file-ai-message]'),
    );
    previousMessages.forEach(message => message.remove());

    elements.empty.hidden = state.messages.length > 0 || state.isSending;

    state.messages.forEach(message => {
      const messageElement = createMessageElement(message);
      messageElement.dataset.fileAiMessage = '1';
      elements.messages.appendChild(messageElement);
    });

    if (state.isSending) {
      const loadingElement = createLoadingElement();
      loadingElement.dataset.fileAiMessage = '1';
      elements.messages.appendChild(loadingElement);
    }

    scrollMessagesToBottom(elements);
  };

  const openChat = () => {
    state.isOpen = true;
    render();

    requestAnimationFrame(() => {
      elements.input.focus();
    });
  };

  const closeChat = () => {
    state.isOpen = false;
    render();
  };

  const sendMessage = async () => {
    const question = elements.input.value.trim();

    if (question === '' || state.isSending) {
      return;
    }

    state.errorMessage = '';
    state.isSending = true;
    state.messages = [...state.messages, createMessage(USER_ROLE, question)];
    elements.input.value = '';
    adjustTextareaHeight(elements.input);
    render();

    try {
      const response = await fetch(config.endpoint, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': config.csrfToken,
        },
        body: JSON.stringify({
          messages: state.messages.map(({role, text}) => ({role, text})),
        }),
      });

      const responseBody = await response
        .json()
        .catch(() => ({}));

      if (!response.ok) {
        throw new Error(
          parseErrorMessage(responseBody) ||
            'No se ha podido consultar la IA. Inténtalo de nuevo.',
        );
      }

      const answer =
        typeof responseBody.answer === 'string' && responseBody.answer.trim() !== ''
          ? responseBody.answer.trim()
          : 'No he podido sacar una respuesta útil del archivo. Prueba con otra pregunta sobre el documento.';

      state.messages = [...state.messages, createMessage(ASSISTANT_ROLE, answer)];
    } catch (error) {
      state.errorMessage = getErrorMessage(error);
    } finally {
      state.isSending = false;
      render();
    }
  };

  elements.open.addEventListener('click', openChat);
  elements.close.addEventListener('click', closeChat);
  elements.backdrop.addEventListener('click', closeChat);

  elements.form.addEventListener('submit', event => {
    event.preventDefault();
    void sendMessage();
  });

  elements.input.addEventListener('input', () => {
    adjustTextareaHeight(elements.input);
    render();
  });

  elements.input.addEventListener('keydown', event => {
    if (event.key === 'Escape' && state.isOpen) {
      closeChat();
      return;
    }

    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      void sendMessage();
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && state.isOpen) {
      closeChat();
    }
  });

  adjustTextareaHeight(elements.input);
  render();
}

export default {
  init() {
    document.querySelectorAll('[data-file-ai-chat]').forEach(initChat);
  },
};
