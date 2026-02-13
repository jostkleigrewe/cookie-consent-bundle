# Symfony Cookie Consent Bundle – GDPR/DSGVO Cookie Banner with Google Consent Mode v2

[![Packagist Version](https://img.shields.io/packagist/v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![Packagist Downloads](https://img.shields.io/packagist/dt/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![CI](https://github.com/jostkleigrewe/cookie-consent-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/jostkleigrewe/cookie-consent-bundle/actions/workflows/ci.yml)
[![License](https://img.shields.io/packagist/l/jostkleigrewe/cookie-consent-bundle)](LICENSE)

> **A modern Symfony 8 bundle for GDPR/DSGVO-compliant cookie consent management.** Includes Google Consent Mode v2 support, Twig components, Stimulus.js integration, and AssetMapper compatibility. Perfect for cookie banners, consent modals, and privacy-compliant websites.

**[🇩🇪 Deutsche Version](README.de.md)** · **[📦 Packagist](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)** · **[📖 Documentation](docs/getting-started.md)**

## Why this bundle?

- ✅ Symfony-native consent handling with Twig, Stimulus, and AssetMapper
- ✅ Vendor-level toggles + Consent Mode v2 for modern ad stacks
- ✅ Session-safe by design: prevents unwanted session cookies

## Screenshot

![Cookie Consent Modal](docs/assets/cookie-consent-modal.png)

## Features

- 🎯 **GDPR & DSGVO Compliant** – Cookie consent with policy versioning and audit logging
- 📊 **Google Consent Mode v2** – Built-in GA4, Google Ads, and gtag integration
- 🎨 **Multiple Themes** – Tabler (light/dark), Bootstrap 5, or custom templates
- ⚡ **Stimulus.js & Turbo** – Hotwire-compatible, no full page reload needed
- 🗂️ **AssetMapper Ready** – No Webpack/Encore required, works out of the box
- 🧭 **Flexible Storage** – Cookie-only, Doctrine ORM, or combined (hybrid)
- 🧩 **Vendor-Level Consent** – Optional per-vendor toggles (Google Ads, Meta, etc.)
- 🛡️ **Session Protection** – Prevents session cookies without explicit consent
- 🎬 **Embed Components** – YouTube, Vimeo, Google Maps, Spotify, Instagram, TikTok with consent gates
- 🧪 **Twig Helpers** – `cookie_consent_has()`, `cookie_consent_modal()`, and more
- 📝 **Audit Logging** – Track consent changes with optional database persistence

## Requirements

- PHP 8.4+
- Symfony 8.0+
- Twig Bundle, Security Bundle, Stimulus Bundle
- Doctrine ORM + DoctrineBundle (optional, only for `storage: doctrine|both`)

## Compatibility

| Bundle Version | PHP       | Symfony   |
|----------------|-----------|-----------|
| 0.4.x          | 8.4+      | 8.0+      |
| 0.3.x          | 8.3+      | 7.1+      |
| 0.2.x          | 8.2+      | 7.0+      |

## Quick Start

### 1. Install

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

### 2. Register routes

Create `config/routes/cookie_consent.yaml`:

```yaml
cookie_consent:
    resource: '@CookieConsentBundle/config/routes.php'
```

This registers the `/_cookie-consent` endpoint required for consent updates.

### 3. Configure assets

**Option A: Twig helper (CSP-compatible, recommended)**

```twig
{# templates/base.html.twig - in <head> #}
{{ cookie_consent_styles() }}
```

This renders a standard `<link>` tag, fully compatible with strict Content-Security-Policy headers.

**Option B: JavaScript import**

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/styles/cookie_consent.css';
```

> **Note:** With strict CSP (`style-src 'self'`), bundlers may convert CSS imports to `data:` URLs, which can be blocked. Use Option A if you encounter CSP issues.

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

### 4. Render the modal

```twig
{# templates/base.html.twig #}
{{ cookie_consent_modal() }}
```

### 5. Gate content by consent

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

### Storage Modes

| Mode       | Description                                        | Use Case                          |
|------------|----------------------------------------------------|-----------------------------------|
| `cookie`   | Browser cookie only (default)                      | Simple sites, no DB required      |
| `doctrine` | Database only via Doctrine ORM                     | Server-side consent verification  |
| `both`     | Cookie + Database (cookie as primary, DB as backup)| Full audit trail + fast access    |

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

## Troubleshooting

### Modal doesn't appear
- Ensure `{{ cookie_consent_modal() }}` is in your base template
- Check browser console for JavaScript errors
- Verify Stimulus controller is loaded: `@jostkleigrewe/cookie-consent-bundle/cookie-consent`

### Assets not loading (404)
- Run `bin/console cache:clear`
- Check AssetMapper paths: `bin/console debug:asset-map | grep cookie`
- Ensure `assets/app.js` imports the CSS

### Session cookie created before consent
- Check `enforcement.require_consent_for_session` is `true`
- Add routes to `stateless_routes` if they should work without session
- Verify `#[ConsentStateless]` attribute on stateless controllers

### Doctrine storage not working
- Run migrations: `bin/console doctrine:migrations:diff && bin/console doctrine:migrations:migrate`
- Check `storage: doctrine` or `storage: both` is set
- Verify `doctrine/orm` and `doctrine/doctrine-bundle` are installed

### Google Consent Mode not updating
- Ensure `google_consent_mode.enabled: true`
- Check `gtag` is loaded before the consent modal
- Verify category mapping matches your categories

### Tabler variant styling issues (missing border-radius, labels below checkbox)
- **Cause:** Tabler loads after bundle CSS and overrides `.modal-content` and `form-switch` styles
- **Solution:** Update to latest bundle version (>= 0.4.2) which includes Tabler-specific fixes
- **Manual fix:** Add to your CSS with higher specificity:
```css
.cookie-consent-modal.cookie-consent-variant-tabler .modal-content {
    border: 0;
    border-radius: var(--cc-radius, 18px);
    box-shadow: var(--cc-shadow, 0 24px 60px rgba(15, 23, 42, 0.25));
}
.cookie-consent-variant-tabler .cookie-consent-toggle.form-switch {
    display: block;
    padding-left: 2.5rem;
}
.cookie-consent-variant-tabler .cookie-consent-toggle.form-switch .form-check-input {
    float: left;
    margin-left: -2.5rem;
}
```

## Contributing

```bash
composer install
composer ci
```

## License

MIT - see [LICENSE](LICENSE).

## Resources

- [Packagist](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
- [GitHub Repository](https://github.com/jostkleigrewe/cookie-consent-bundle)
- [Documentation](docs/getting-started.md)
- [Report Issues](https://github.com/jostkleigrewe/cookie-consent-bundle/issues)
- [Changelog](CHANGELOG.md)

## Keywords

Symfony cookie consent, GDPR cookie banner, DSGVO cookie modal, Google Consent Mode v2, Symfony 8 bundle, cookie management, consent management platform, CMP, Twig cookie component, Stimulus.js cookie, AssetMapper, Doctrine consent storage, YouTube embed consent, privacy compliance, e-privacy.
