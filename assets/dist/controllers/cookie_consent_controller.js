import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['modal', 'checkbox'];
  static values = {
    categories: String,
    preferences: String,
    endpoint: String,
    required: Boolean,
  };

  connect() {

    console.log('Cookie Consent Controller connected');

    this.handleTurboLoad = this.handleTurboLoad.bind(this);
    document.addEventListener('turbo:load', this.handleTurboLoad);

    this.applyConsent(this.parsedPreferences());
    this.showIfRequired();
  }

  disconnect() {
    console.log('Cookie Consent Controller disconnected');
    document.removeEventListener('turbo:load', this.handleTurboLoad);
  }

  handleTurboLoad() {
      console.log('Cookie Consent Controller handleTurboLoad');
    this.showIfRequired();
  }

  acceptAll() {
      console.log('Cookie Consent Controller acceptAll');
      this.submit('accept_all');
  }

  rejectOptional() {
      console.log('Cookie Consent Controller rejectOptional');
    this.submit('reject_optional');
  }

  save() {
      console.log('Cookie Consent Controller save');
    this.submit('custom', this.collectPreferences());
  }

  submit(action, preferences = null) {

      console.log('Submit ' + action, this.endpointValue);

    const body = { action };
    if (preferences) {
      body.preferences = preferences;
    }


    fetch(this.endpointValue, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(body),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data && data.preferences) {
          this.applyConsent(data.preferences);
        }
        this.hide();
      })
      .catch(() => {
        this.hide();
      });
  }

  showIfRequired() {
    if (this.requiredValue) {
      this.show();
    }
  }

  show() {
    if (!this.hasModalTarget) {
      return;
    }

    this.modalTarget.style.display = 'block';
    this.modalTarget.classList.add('show');
    this.modalTarget.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
  }

  hide() {
    if (!this.hasModalTarget) {
      return;
    }

    this.modalTarget.classList.remove('show');
    this.modalTarget.style.display = 'none';
    this.modalTarget.removeAttribute('aria-modal');
    document.body.classList.remove('modal-open');
  }

  applyConsent(preferences) {
    const normalized = preferences || {};

    document.querySelectorAll('[data-consent-category]').forEach((element) => {
      const category = element.dataset.consentCategory;
      const allowed = Boolean(normalized[category]);
      const mode = element.dataset.consentMode || 'hide';

      if (element.tagName === 'SCRIPT') {
        if (allowed && element.type === 'text/plain') {
          const script = document.createElement('script');
          if (element.dataset.consentSrc) {
            script.src = element.dataset.consentSrc;
          }
          script.text = element.textContent;
          element.replaceWith(script);
        }
        return;
      }

      if (allowed) {
        element.hidden = false;
      } else if (mode === 'remove') {
        element.remove();
      } else {
        element.hidden = true;
      }
    });
  }

  collectPreferences() {
    const preferences = {};
    this.checkboxTargets.forEach((checkbox) => {
      preferences[checkbox.value] = checkbox.checked;
    });

    return preferences;
  }

  parsedPreferences() {
    if (!this.hasPreferencesValue) {
      return {};
    }

    try {
      return JSON.parse(this.preferencesValue);
    } catch (error) {
      return {};
    }
  }
}
