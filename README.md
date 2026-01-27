# Cookie Consent Bundle

[![Packagist Version](https://img.shields.io/packagist/v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![CI](https://github.com/jostkleigrewe/cookie-consent-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/jostkleigrewe/cookie-consent-bundle/actions/workflows/ci.yml)
[![GitHub Release](https://img.shields.io/github/v/release/jostkleigrewe/cookie-consent-bundle)](https://github.com/jostkleigrewe/cookie-consent-bundle/releases)
[![License](https://img.shields.io/packagist/l/jostkleigrewe/cookie-consent-bundle)](LICENSE)

A Symfony 8 bundle for GDPR-compliant cookie consent with Twig integration, Stimulus.js, and flexible storage backends.

**[Deutsche Version](README.de.md)**

## Why this bundle?

- ✅ Symfony-native consent handling with Twig, Stimulus, and AssetMapper
- ✅ Vendor-level toggles + Consent Mode v2 for modern ad stacks
- ✅ Session-safe by design: prevents unwanted session cookies

## Screenshot

![Cookie Consent Modal](docs/assets/cookie-consent-modal.png)

## Features

- 🎯 **GDPR-Compliant** - Cookie consent with policy versioning and audit logging
- 🎨 **Multiple Themes** - Tabler (light/dark), Bootstrap, or bring your own
- ⚡ **Stimulus.js** - Turbo-friendly, no full page reload needed
- 🧭 **Flexible Storage** - Cookie, Doctrine, or both combined
- 🧩 **Vendor-Level Consent** - Optional per-vendor toggles inside categories
- 🛡️ **Session Protection** - Prevents session cookies without consent
- 📊 **Google Consent Mode v2** - Built-in GA4 and Google Ads integration
- 🎬 **Embed Components** - YouTube, Vimeo, Google Maps, and more with consent gates
- 🧪 **Twig Helpers** - `cookie_consent_has()`, `cookie_consent_modal()`, and more

## Requirements

- PHP 8.4+
- Symfony 8.0+
- Twig Bundle, Security Bundle, Stimulus Bundle
- Doctrine ORM + DoctrineBundle (optional, only for `storage: doctrine|both`)

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
      vendors:
        google_ads:
          label: Google Ads
          default: false

  ui:
    template: '@CookieConsent/styles/tabler/modal.html.twig'
    position: center
    privacy_url: '/privacy'
    reload_on_change: false

  logging:
    retention_days: null

  google_consent_mode:
    enabled: false
```

If `storage` is set to `doctrine` or `both`, generate migrations in your app (bundle ships entities, not migrations). This requires Doctrine ORM:

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

Increment `policy_version` when changing categories to require re-consent.

If `logging.retention_days` is set, run the cleanup command regularly:

```bash
bin/console cookie-consent:cleanup
```

## Documentation

- **[Getting Started](docs/getting-started.md)** - Installation, assets, first steps
- **[Configuration](docs/configuration.md)** - All options, templates, Twig helpers
- **[Advanced](docs/advanced.md)** - Storage backends, session enforcement, logging, events
- **[Integration](docs/integration.md)** - Components, helpers, attributes, data attributes, events
- **[Changelog](CHANGELOG.md)** - Releases and notable changes
- **[Contributing](CONTRIBUTING.md)** - Development workflow and guidelines

## Embed Components

Gate third-party content with built-in components:

```twig
<twig:CookieConsentYoutubeEmbed
  video_id="dQw4w9WgXcQ"
  category="marketing"
  vendor="youtube"
/>
```

Alternative:

```twig
{{ component('CookieConsentYoutubeEmbed', {
  video_id: 'dQw4w9WgXcQ',
  category: 'marketing',
  vendor: 'youtube'
}) }}
```

Available: YouTube, Vimeo, Google Maps, Spotify, Twitter/X, Instagram, TikTok, and more.

## Integration Overview

See **[Integration](docs/integration.md)** for Twig components, helpers, data attributes, controller attributes, and events.

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
