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

## Asset Setup (AssetMapper)

The bundle registers its assets via AssetMapper. No build step required.

### 1. Import the CSS

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/styles/cookie_consent.css';
```

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

    {{ cookie_consent_modal() }}
</body>
</html>
```

The modal appears automatically when consent is required.

## Verify Installation

1. Clear the cache: `php bin/console cache:clear`
2. Visit your site in a browser
3. The consent modal should appear

## Next Steps

- **[Configuration](configuration.md)** - Customize categories, templates, and behavior
- **[Advanced](advanced.md)** - Storage backends, session enforcement, logging
