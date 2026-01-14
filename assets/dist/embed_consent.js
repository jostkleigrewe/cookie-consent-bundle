// assets/dist/embed_consent.js

(function () {
  function parsePreferences(raw) {
    if (!raw) {
      return null;
    }

    try {
      return JSON.parse(raw);
    } catch (error) {
      console.warn('[CookieConsent] Failed to parse preferences:', error.message);
      return null;
    }
  }

  function renderEmbed(el) {
    if (el.dataset.rendered === 'true') {
      return;
    }

    const type = el.dataset.type || 'iframe';
    const src = el.dataset.src || '';
    const title = el.dataset.title || 'Embedded content';
    const callback = el.dataset.callback || '';
    const scriptSrc = el.dataset.scriptSrc || '';

    if (type === 'iframe') {
      const iframe = document.createElement('iframe');
      iframe.src = src;
      iframe.title = title;
      iframe.loading = 'lazy';
      iframe.allowFullscreen = true;
      iframe.style.width = '100%';
      iframe.style.aspectRatio = el.dataset.aspectRatio || '16 / 9';
      iframe.style.border = '0';
      if (el.dataset.allow) {
        iframe.allow = el.dataset.allow;
      }
      el.dataset.rendered = 'true';
      el.replaceWith(iframe);
      return;
    }

    if (type === 'html') {
      const wrapper = document.createElement('div');
      wrapper.innerHTML = el.dataset.html || '';
      el.dataset.rendered = 'true';
      el.replaceWith(wrapper);
      appendScript(scriptSrc);
      callCallback(callback, wrapper);
      return;
    }

    if (type === 'script') {
      const script = document.createElement('script');
      script.src = src;
      script.async = true;
      el.dataset.rendered = 'true';
      el.replaceWith(script);
    }
  }

  function appendScript(src) {
    if (!src) {
      return;
    }

    if (document.querySelector(`script[data-consent-embed-src="${src}"]`)) {
      return;
    }

    const script = document.createElement('script');
    script.src = src;
    script.async = true;
    script.dataset.consentEmbedSrc = src;
    document.head.appendChild(script);
  }

  function callCallback(path, element) {
    if (!path) {
      return;
    }

    const target = path.split('.').reduce((acc, key) => (acc ? acc[key] : null), window);
    if (typeof target !== 'function') {
      return;
    }

    try {
      target(element);
    } catch (error) {
      try {
        target();
      } catch (innerError) {
        console.warn('[CookieConsent] Embed callback failed:', innerError.message);
      }
    }
  }

  function applyConsent(preferences) {
    if (!preferences) {
      return;
    }

    document.querySelectorAll('[data-cookie-consent-embed]').forEach((el) => {
      const category = el.dataset.category || 'marketing';
      if (preferences[category]) {
        renderEmbed(el);
      }
    });
  }

  document.addEventListener('cookie-consent:changed', (event) => {
    applyConsent((event.detail || {}).preferences || {});
  });

  document.querySelectorAll('[data-cookie-consent-embed]').forEach((el) => {
    const preferences = parsePreferences(el.dataset.preferences);
    if (preferences) {
      applyConsent(preferences);
    }
  });
})();
