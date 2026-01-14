# Konfiguration (DE)

Language switch: [English](configuration.en.md) | [Index](index.de.md)

Lege `config/packages/cookie_consent.yaml` an oder passe es an:

```yaml
cookie_consent:
  policy_version: '1'
  storage: cookie # cookie, doctrine, oder both
  cookie:
    name: cookie_consent
    lifetime: 15552000
    same_site: lax
    http_only: true
  identifier_cookie:
    name: cookie_consent_id
    lifetime: 31536000
    http_only: true
  categories:
    necessary:
      label: Necessary
      description: Required for basic site functionality.
      required: true
      default: true
    analytics:
      label: Analytics
      default: false
  ui:
    template: '@CookieConsent/styles/tabler/modal.html.twig' # tabler, bootstrap, oder plain
    privacy_url: '/datenschutz'
    imprint_url: '/impressum'
  enforcement:
    require_consent_for_session: true
    stateless_paths: ['/health']
    protected_paths: ['/checkout']
    stateless_routes: []
    protected_routes: []
  logging:
    enabled: false
    level: info
    anonymize_ip: true
```

Hinweis: Erhoehe `policy_version`, sobald sich Kategorien aendern (hinzufuegen/entfernen/umbenennen/required/default). Das erzwingt eine neue Zustimmung.

## Consent-abh. Inhalte

```twig
{% if cookie_consent_has('analytics') %}
  {# analytics script #}
{% endif %}
```

Lazy script loading:

```html
<script type="text/plain" data-consent-category="analytics" data-consent-src="https://example.com/analytics.js"></script>
```

## CSRF

Der Consent-Endpoint verwendet einen Same-Origin-CSRF-Check. Das Modal liefert das Token automatisch mit; keine weitere Konfiguration nötig.

## Routen

Endpoint ist `cookie_consent_update` unter `POST /_cookie-consent`.
Überschreibe ihn, indem du in der App eine Route mit gleichem Namen definierst.

Weiter: [Storage](storage.de.md), [Erzwingung](enforcement.de.md) und [UI](ui.de.md).

Siehe auch: [Arbeitsweise](how-it-works.de.md).
