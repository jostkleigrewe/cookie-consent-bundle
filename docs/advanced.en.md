# Advanced (EN)

Language switch: [Deutsch](advanced.de.md) | [Index](index.en.md)

This page covers optional logging and analytics integrations.

## Logging and audit

The bundle can log consent actions for GDPR audit purposes.

```yaml
cookie_consent:
  logging:
    enabled: true
    level: info
    anonymize_ip: true
```

Logged fields:

- Action (`accept_all`, `reject_optional`, `custom`)
- Preferences map
- Policy version and decision timestamp
- Accepted/rejected categories
- Request context (IP, User-Agent, Referrer, Request URI)

If `anonymize_ip` is enabled, IP addresses are anonymized.

## Analytics (GA Consent Mode v2)

This example shows Google Analytics 4 integration with Consent Mode v2. It uses the browser event emitted by the bundle.

```twig
{# templates/base.html.twig #}
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}

  gtag('consent', 'default', {
    'ad_storage': 'denied',
    'analytics_storage': 'denied',
    'ad_user_data': 'denied',
    'ad_personalization': 'denied'
  });

  function applyConsent(preferences) {
    const analytics = !!preferences.analytics;
    const marketing = !!preferences.marketing;
    gtag('consent', 'update', {
      'analytics_storage': analytics ? 'granted' : 'denied',
      'ad_storage': marketing ? 'granted' : 'denied',
      'ad_user_data': marketing ? 'granted' : 'denied',
      'ad_personalization': marketing ? 'granted' : 'denied'
    });
  }

  document.addEventListener('cookie-consent:changed', (event) => {
    applyConsent(event.detail.preferences || {});
  });
</script>
```

If you want to apply consent on initial page load, you can also render the current preferences:

```twig
<script>
  applyConsent({{ cookie_consent_preferences()|json_encode|raw }});
</script>
```
