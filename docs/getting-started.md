# Getting Started

[Deutsch](getting-started.de.md) | [Back to README](../README.md)

## Requirements

- PHP 8.4+
- Symfony 8.0+
- Twig Bundle
- Security Bundle (for CSRF and firewall integration)
- Stimulus Bundle (`symfony/stimulus-bundle`)
- Twig Components (`symfony/ux-twig-component`)

## Installation

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

Enable the bundle if not auto-registered:

```php
// config/bundles.php
return [
    // ...
    Symfony\UX\TwigComponent\TwigComponentBundle::class => ['all' => true],
    Jostkleigrewe\CookieConsentBundle\CookieConsentBundle::class => ['all' => true],
];
```

## Configuration

Copy the example configuration to your project:

```bash
cp vendor/jostkleigrewe/cookie-consent-bundle/docs/examples/cookie_consent.yaml config/packages/
```

Or create `config/packages/cookie_consent.yaml` manually. See [Configuration](configuration.md) for all options.

## Routes

Create `config/routes/cookie_consent.yaml`:

```yaml
cookie_consent:
    resource:
        path: '@CookieConsentBundle/Controller/'
        namespace: Jostkleigrewe\CookieConsentBundle\Controller
    type: attribute
```

## Asset Setup (AssetMapper)

The bundle registers its assets via AssetMapper. No build step required.

### 1. Import the CSS

There are two ways to include the CSS – choose based on your project's requirements:

#### Option A: Twig helper (CSP-compatible, recommended)

Add to your base template's `<head>`:

```twig
{# templates/base.html.twig #}
<head>
    {# ... #}
    {{ cookie_consent_styles() }}
</head>
```

This renders a standard `<link rel="stylesheet">` tag, fully compatible with strict Content-Security-Policy headers (`style-src 'self'`).

#### Option B: JavaScript import

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/styles/cookie_consent.css';
```

> **CSP Note:** When using strict Content-Security-Policy (`style-src 'self'` without `data:`), bundlers may convert CSS imports to `data:` URLs, which get blocked by the CSP. Use **Option A** if you encounter styling issues in CSP-protected environments.

### 2. Enable the Stimulus controller

```json
// assets/controllers.json
{
  "controllers": {
    "@jostkleigrewe/cookie-consent-bundle": {
      "cookie-consent": {
        "enabled": true,
        "fetch": "eager"
      }
    }
  }
}
```

### 3. Initialize importmap (if not already done)

```bash
php bin/console importmap:install
```

### 4. Compile for production

```bash
php bin/console asset-map:compile
```

## Render the Modal

Add the modal to your base layout:

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html>
<head>...</head>
<body>
    {% block body %}{% endblock %}

    {% if cookie_consent_required() %}
        {{ cookie_consent_modal() }}
    {% endif %}
</body>
</html>
```

The modal appears automatically when consent is required.

## Integration Overview

For components, helpers, data attributes, controller attributes, and events, see:
**[Integration](integration.md)**.

## Verify Installation

1. Clear the cache: `php bin/console cache:clear`
2. Visit your site in a browser
3. The consent modal should appear

## Next Steps

- **[Configuration](configuration.md)** - Customize categories, templates, and behavior
- **[Integration](integration.md)** - Components, helpers, attributes, data attributes, events
- **[Advanced](advanced.md)** - Storage backends, session enforcement, logging
