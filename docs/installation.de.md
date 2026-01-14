# Installation (DE)

Language switch: [English](installation.en.md) | [Index](index.de.md)

## Voraussetzungen

- PHP 8.4+
- Symfony 8
- Twig Bundle
- Security Bundle (für Firewall-Statelessness)
- CSRF-Komponente (`symfony/security-csrf`) für den Consent-Endpoint
- Twig Components (`symfony/ux-twig-component`) für den Settings-Button

Twig Components Bundle aktivieren, falls es nicht automatisch registriert wurde:

```php
// config/bundles.php
Symfony\\UX\\TwigComponent\\TwigComponentBundle::class => ['all' => true],
```

## Bundle installieren

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

Bundle aktivieren, falls nicht automatisch registriert:

```php
// config/bundles.php
Jostkleigrewe\CookieConsentBundle\CookieConsentBundle::class => ['all' => true],
```

## Assets mit Importmap (AssetMapper)

Das Bundle registriert `assets/dist` über AssetMapper.

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

Importmap einmalig initialisieren (falls noch nicht geschehen):

```bash
php bin/console importmap:install
```

Asset-Map für Produktion bauen:

```bash
php bin/console asset-map:compile
```

## Modal rendern

Modal im Basis-Layout einbinden:

```twig
{{ cookie_consent_modal() }}
```

Weiter: [Konfiguration](configuration.de.md).

Siehe auch: [Arbeitsweise](how-it-works.de.md).
