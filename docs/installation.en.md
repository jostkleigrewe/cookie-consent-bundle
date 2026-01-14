# Installation (EN)

Language switch: [Deutsch](installation.de.md) | [Index](index.en.md)

## Requirements

- PHP 8.4+
- Symfony 8
- Twig Bundle
- Security Bundle (used for firewall stateless checks)
- CSRF component (`symfony/security-csrf`) for the consent endpoint
- Twig Components (`symfony/ux-twig-component`) for the settings button

Enable the Twig Component bundle in your app if it is not auto-registered:

```php
// config/bundles.php
Symfony\\UX\\TwigComponent\\TwigComponentBundle::class => ['all' => true],
```

## Install the bundle

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

Enable the bundle if it is not auto-registered:

```php
// config/bundles.php
Jostkleigrewe\CookieConsentBundle\CookieConsentBundle::class => ['all' => true],
```

## Assets with Importmap (AssetMapper)

The bundle registers `assets/dist` via AssetMapper.

```js
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/styles/cookie_consent.css';
```

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

Initialize importmap once (if not already done):

```bash
php bin/console importmap:install
```

Build the asset map for production:

```bash
php bin/console asset-map:compile
```

## Render the modal

Add the modal to your base layout:

```twig
{{ cookie_consent_modal() }}
```

Next: [Configuration](configuration.en.md).

See also: [How it works](how-it-works.en.md).
