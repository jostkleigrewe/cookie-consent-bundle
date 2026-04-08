# Erste Schritte

[English](getting-started.md) | [Zurück zur README](../README.de.md)

## Voraussetzungen

- PHP 8.4+
- Symfony 8.0+
- Twig Bundle
- Security Bundle (für CSRF und Firewall-Integration)
- Stimulus Bundle (`symfony/stimulus-bundle`)
- Twig Components (`symfony/ux-twig-component`)

## Installation

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

Bundle aktivieren (falls nicht automatisch registriert):

```php
// config/bundles.php
return [
    // ...
    Symfony\UX\TwigComponent\TwigComponentBundle::class => ['all' => true],
    Jostkleigrewe\CookieConsentBundle\CookieConsentBundle::class => ['all' => true],
];
```

## Konfiguration

Beispielkonfiguration ins Projekt kopieren:

```bash
cp vendor/jostkleigrewe/cookie-consent-bundle/docs/examples/cookie_consent.yaml config/packages/
```

Oder `config/packages/cookie_consent.yaml` manuell anlegen. Alle Optionen stehen in der [Konfiguration](configuration.de.md).

## Routen

Erstelle `config/routes/cookie_consent.yaml`:

```yaml
cookie_consent:
    resource:
        path: '@CookieConsentBundle/Controller/'
        namespace: Jostkleigrewe\CookieConsentBundle\Controller
    type: attribute
```

## Asset-Setup (AssetMapper)

Das Bundle registriert seine Assets über AssetMapper. Kein Build-Schritt nötig.

### 1. CSS importieren

Es gibt zwei Wege, das CSS einzubinden – wähle basierend auf den Anforderungen deines Projekts:

#### Option A: Twig-Helper (CSP-kompatibel, empfohlen)

Im `<head>` deines Base-Templates einfügen:

```twig
{# templates/base.html.twig #}
<head>
    {# ... #}
    {{ cookie_consent_styles() }}
</head>
```

Dies rendert ein Standard-`<link rel="stylesheet">`-Tag, vollständig kompatibel mit strikten Content-Security-Policy-Headern (`style-src 'self'`).

#### Option B: JavaScript-Import

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/styles/cookie_consent.css';
```

> **CSP-Hinweis:** Bei strikter Content-Security-Policy (`style-src 'self'` ohne `data:`) können Bundler CSS-Imports in `data:`-URLs konvertieren, die von der CSP blockiert werden. Nutze **Option A**, wenn Styling-Probleme in CSP-geschützten Umgebungen auftreten.

### 2. Stimulus-Controller aktivieren

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

### 3. Importmap initialisieren (falls noch nicht geschehen)

```bash
php bin/console importmap:install
```

### 4. Für Produktion kompilieren

```bash
php bin/console asset-map:compile
```

## Modal einbinden

Das Modal im Base-Layout einbinden:

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

Das Modal erscheint automatisch, wenn eine Einwilligung erforderlich ist.

## Installation prüfen

1. Cache leeren: `php bin/console cache:clear`
2. Seite im Browser öffnen
3. Das Consent-Modal sollte erscheinen

## Nächste Schritte

- **[Konfiguration](configuration.de.md)** - Kategorien, Templates und Verhalten anpassen
- **[Integration](integration.de.md)** - Komponenten, Helper, Attribute, Data-Attributes, Events
- **[Erweitert](advanced.de.md)** - Speicher-Backends, Session-Erzwingung, Logging
