# Configuration (EN)

Language switch: [Deutsch](configuration.de.md) | [Index](index.en.md)

Create or update `config/packages/cookie_consent.yaml`:

```yaml
cookie_consent:
  policy_version: '1'
  storage: cookie # cookie, doctrine, or both
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
    template: '@CookieConsent/styles/tabler/modal.html.twig' # tabler, bootstrap, or plain
    privacy_url: '/privacy'
    imprint_url: '/imprint'
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

Note: increment `policy_version` whenever you change categories (add/remove/rename/required/default). This forces users to re-consent.

## Consent-aware content

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

The consent update endpoint uses a same-origin CSRF check. The modal includes the token automatically; no additional configuration is needed.

## Routes

The update endpoint is `cookie_consent_update` at `POST /_cookie-consent`.
Override it by defining a route with the same name in your app.

Next: [Storage](storage.en.md), [Enforcement](enforcement.en.md), and [UI](ui.en.md).

See also: [How it works](how-it-works.en.md).
