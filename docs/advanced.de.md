# Advanced (DE)

Language switch: [English](advanced.en.md) | [Index](index.de.md)

Diese Seite bündelt optionales Logging und Analytics-Integrationen.

## Logging und Audit

Das Bundle kann Consent-Aktionen für DSGVO-Audits protokollieren.

```yaml
cookie_consent:
  logging:
    enabled: true
    level: info
    anonymize_ip: true
```

Protokollierte Felder:

- Aktion (`accept_all`, `reject_optional`, `custom`)
- Preferences-Map
- Policy-Version und Entscheidungszeitpunkt
- Akzeptierte/abgelehnte Kategorien
- Request-Kontext (IP, User-Agent, Referrer, Request-URI)

Wenn `anonymize_ip` aktiv ist, werden IP-Adressen anonymisiert.

## Analytics (GA Consent Mode v2)

Dieses Beispiel zeigt Google Analytics 4 mit Consent Mode v2. Es nutzt das Browser-Event des Bundles.

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

Wenn du den aktuellen Consent schon beim Laden anwenden willst:

```twig
<script>
  applyConsent({{ cookie_consent_preferences()|json_encode|raw }});
</script>
```
