# Cookie Consent Bundle

[![Packagist Version](https://img.shields.io/packagist/v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![License](https://img.shields.io/packagist/l/jostkleigrewe/cookie-consent-bundle)](LICENSE)

A Symfony 8 bundle for GDPR-compliant cookie consent with Twig integration, Stimulus.js, and flexible storage backends.

**[Deutsche Version](README.de.md)**

## Features

- **GDPR-Compliant** - Cookie consent with policy versioning and audit logging
- **Multiple Themes** - Tabler (light/dark), Bootstrap, or bring your own
- **Stimulus.js** - Turbo-friendly, no full page reload needed
- **Flexible Storage** - Cookie, Doctrine, or both combined
- **Session Protection** - Prevents session cookies without consent
- **Google Consent Mode v2** - Built-in GA4 and Google Ads integration
- **Embed Components** - YouTube, Vimeo, Google Maps, and more with consent gates
- **Twig Helpers** - `cookie_consent_has()`, `cookie_consent_modal()`, and more

## Requirements

- PHP 8.4+
- Symfony 8.0+
- Twig Bundle, Security Bundle, Stimulus Bundle

## Quick Start

### 1. Install

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

### 2. Configure assets

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/styles/cookie_consent.css';
```

```json
// assets/controllers.json
{
  "controllers": {
    "@jostkleigrewe/cookie-consent-bundle": {
      "cookie-consent": { "enabled": true, "fetch": "eager" }
    }
  }
}
```

### 3. Render the modal

```twig
{# templates/base.html.twig #}
{{ cookie_consent_modal() }}
```

### 4. Gate content by consent

```twig
{% if cookie_consent_has('analytics') %}
  <script src="https://example.com/analytics.js"></script>
{% endif %}
```

Or use lazy loading:

```html
<script type="text/plain" data-consent-category="analytics"
        data-consent-src="https://example.com/analytics.js"></script>
```

## Configuration

Create `config/packages/cookie_consent.yaml`:

```yaml
cookie_consent:
  policy_version: '1'
  storage: cookie  # cookie, doctrine, or both

  categories:
    necessary:
      label: Necessary
      required: true
      default: true
    analytics:
      label: Analytics
      default: false
    marketing:
      label: Marketing
      default: false

  ui:
    template: '@CookieConsent/styles/tabler/modal.html.twig'
    privacy_url: '/privacy'
    reload_on_change: false

  google_consent_mode:
    enabled: false
```

Increment `policy_version` when changing categories to require re-consent.

## Documentation

- **[Getting Started](docs/getting-started.md)** - Installation, assets, first steps
- **[Configuration](docs/configuration.md)** - All options, templates, Twig helpers
- **[Advanced](docs/advanced.md)** - Storage backends, session enforcement, logging, events

## Embed Components

Gate third-party content with built-in components:

```twig
{{ include('@CookieConsent/components/CookieConsentYoutubeEmbed.html.twig', {
  video_id: 'dQw4w9WgXcQ',
  category: 'marketing'
}) }}
```

Available: YouTube, Vimeo, Google Maps, Spotify, Twitter/X, Instagram, TikTok, and more.

## Contributing

```bash
composer install
composer ci
```

## License

MIT - see [LICENSE](LICENSE).

## Resources

- [Packagist](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
- [GitHub](https://github.com/jostkleigrewe/cookie-consent-bundle)
- [Report Issues](https://github.com/jostkleigrewe/cookie-consent-bundle/issues)
