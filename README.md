# Cookie Consent Bundle (Symfony 8)

[![Packagist Version](https://img.shields.io/packagist/v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![License](https://img.shields.io/packagist/l/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)

A Symfony 8 bundle for GDPR-compliant cookie consent with Twig helpers, a Stimulus controller, and flexible storage (cookie, doctrine, or both).

## Highlights

- Modal rendering via `{{ cookie_consent_modal() }}` and consent-aware Twig helpers.
- Stimulus controller with Turbo-friendly behavior and consent-aware DOM handling.
- Configurable categories, policy versioning, and session enforcement.
- Cookie, Doctrine, or combined storage; optional consent logging.
- Built-in templates (Tabler, Bootstrap, plain) with easy overrides.

## Documentation

Start here (short overview):

- English: [Docs index](docs/index.en.md)
- Deutsch: [Doku-Index](docs/index.de.md)

Core docs (most users only need these):

- Install: [EN](docs/installation.en.md) / [DE](docs/installation.de.md)
- Config: [EN](docs/configuration.en.md) / [DE](docs/configuration.de.md)
- UI/Templates: [EN](docs/ui.en.md) / [DE](docs/ui.de.md)
- How it works: [EN](docs/how-it-works.en.md) / [DE](docs/how-it-works.de.md)

Optional:

- Advanced (logging + analytics): [EN](docs/advanced.en.md) / [DE](docs/advanced.de.md)

Backlink from docs: see [README](README.md) in each index file.

## Quickstart

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

```php
// config/bundles.php
Jostkleigrewe\CookieConsentBundle\CookieConsentBundle::class => ['all' => true],
```

## Usage

Render the modal in your base layout:

```twig
{{ cookie_consent_modal() }}
```

Gate content by consent:

```twig
{% if cookie_consent_has('analytics') %}
  {# analytics script #}
{% endif %}
```

Lazy script loading:

```html
<script type="text/plain" data-consent-category="analytics" data-consent-src="https://example.com/analytics.js"></script>
```

Full installation and configuration guides:

- English install: [Installation](docs/installation.en.md)
- Deutsch install: [Installation](docs/installation.de.md)
- English config: [Configuration](docs/configuration.en.md)
- Deutsch config: [Konfiguration](docs/configuration.de.md)

## Contributing & Development

Local setup:

```bash
composer install
```

Run tests:

```bash
vendor/bin/phpunit
```

Notes:

- Bundle code lives in `src/`, templates in `templates/`, translations in `translations/`.
- Docs are bilingual under `docs/` with `.en.md` and `.de.md` suffixes.
- When adding new features, update the relevant EN/DE docs and config examples.
- See [UI EN](docs/ui.en.md) and [UI DE](docs/ui.de.md) for templates, helper functions, and the settings button.

## License

MIT
