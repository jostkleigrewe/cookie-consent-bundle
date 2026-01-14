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
