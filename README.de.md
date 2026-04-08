# Symfony Cookie Consent Bundle – DSGVO Cookie-Banner mit Google Consent Mode v2

[![Packagist Version](https://img.shields.io/packagist/v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![Packagist Downloads](https://img.shields.io/packagist/dt/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/jostkleigrewe/cookie-consent-bundle)](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
[![CI](https://github.com/jostkleigrewe/cookie-consent-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/jostkleigrewe/cookie-consent-bundle/actions/workflows/ci.yml)
[![Lizenz](https://img.shields.io/packagist/l/jostkleigrewe/cookie-consent-bundle)](LICENSE)

> **Ein modernes Symfony 8 Bundle für DSGVO-konforme Cookie-Einwilligung.** Mit Google Consent Mode v2 Unterstützung, Twig-Komponenten, Stimulus.js-Integration und AssetMapper-Kompatibilität. Ideal für Cookie-Banner, Consent-Modals und datenschutzkonforme Websites.

**[🇬🇧 English Version](README.md)** · **[📦 Packagist](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)** · **[📖 Dokumentation](docs/getting-started.de.md)**

## Warum dieses Bundle?

- ✅ Symfony-native Einbindung mit Twig, Stimulus und AssetMapper
- ✅ Vendor-Ebene + Consent Mode v2 für moderne Ad-Stacks
- ✅ Session-sicher: verhindert unerwünschte Session-Cookies

## Screenshot

![Cookie Consent Modal](docs/assets/cookie-consent-modal.png)

## Features

- 🎯 **DSGVO & GDPR konform** – Cookie-Einwilligung mit Richtlinien-Versionierung und Audit-Logging
- 📊 **Google Consent Mode v2** – Integrierte GA4, Google Ads und gtag-Unterstützung
- 🎨 **Mehrere Themes** – Tabler (hell/dunkel), Bootstrap 5 oder eigene Templates
- ⚡ **Stimulus.js & Turbo** – Hotwire-kompatibel, kein vollständiger Seiten-Reload nötig
- 🗂️ **AssetMapper-Ready** – Kein Webpack/Encore nötig, funktioniert sofort
- 🧭 **Flexible Speicherung** – Cookie-only, Doctrine ORM oder kombiniert (Hybrid)
- 🧩 **Vendor-Ebene** – Optionale Vendor-Toggles (Google Ads, Meta, etc.)
- 🛡️ **Session-Schutz** – Verhindert Session-Cookies ohne explizite Einwilligung
- 🎬 **Embed-Komponenten** – YouTube, Vimeo, Google Maps, Spotify, Instagram, TikTok mit Consent-Gates
- 🧪 **Twig-Helfer** – `cookie_consent_has()`, `cookie_consent_modal()` und mehr
- 📝 **Audit-Logging** – Consent-Änderungen mit optionaler Datenbank-Persistierung nachverfolgen

## Voraussetzungen

- PHP 8.4+
- Symfony 8.0+
- Twig Bundle, Security Bundle, Stimulus Bundle
- Doctrine ORM + DoctrineBundle (optional, nur für `storage: doctrine|both`)

## Kompatibilität

| Bundle Version | PHP       | Symfony   |
|----------------|-----------|-----------|
| 0.4.x          | 8.4+      | 8.0+      |
| 0.3.x          | 8.3+      | 7.1+      |
| 0.2.x          | 8.2+      | 7.0+      |

## Schnellstart

### 1. Installation

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

### 2. Routen registrieren

Erstelle `config/routes/cookie_consent.yaml`:

```yaml
cookie_consent:
    resource:
        path: '@CookieConsentBundle/Controller/'
        namespace: Jostkleigrewe\CookieConsentBundle\Controller
    type: attribute
```

Dies registriert den `/_cookie-consent` Endpunkt, der für Consent-Updates benötigt wird.

### 3. Assets konfigurieren

**Option A: Twig-Helper (CSP-kompatibel, empfohlen)**

```twig
{# templates/base.html.twig - im <head> #}
{{ cookie_consent_styles() }}
```

Dies rendert ein Standard-`<link>`-Tag, vollständig kompatibel mit strikten Content-Security-Policy-Headern.

**Option B: JavaScript-Import**

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/styles/cookie_consent.css';
```

> **Hinweis:** Bei strikter CSP (`style-src 'self'`) können Bundler CSS-Imports in `data:`-URLs konvertieren, die blockiert werden. Nutze Option A bei CSP-Problemen.

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

### 4. Modal einbinden

```twig
{# templates/base.html.twig #}
{{ cookie_consent_modal() }}
```

### 5. Inhalte nach Einwilligung steuern

```twig
{% if cookie_consent_has('analytics') %}
  <script src="https://example.com/analytics.js"></script>
{% endif %}
```

Oder mit Lazy Loading:

```html
<script type="text/plain" data-consent-category="analytics"
        data-consent-src="https://example.com/analytics.js"></script>
```

## Konfiguration

Erstelle `config/packages/cookie_consent.yaml`:

```yaml
cookie_consent:
  policy_version: '1'
  storage: cookie  # cookie, doctrine oder both

  categories:
    necessary:
      label: Notwendig
      required: true
      default: true
    analytics:
      label: Analyse
      default: false
    marketing:
      label: Marketing
      default: false
      vendors:
        google_ads:
          label: Google Ads
          default: false

  ui:
    variant: tabler        # plain | bootstrap | tabler
    theme: day             # day | night | auto
    density: normal        # normal | compact
    position: center
    privacy_url: '/datenschutz'
    reload_on_change: false

  logging:
    retention_days: null

  google_consent_mode:
    enabled: false
```

### Speicher-Modi

| Modus      | Beschreibung                                       | Anwendungsfall                    |
|------------|----------------------------------------------------|-----------------------------------|
| `cookie`   | Nur Browser-Cookie (Standard)                      | Einfache Seiten, keine DB nötig   |
| `doctrine` | Nur Datenbank via Doctrine ORM                     | Server-seitige Consent-Prüfung    |
| `both`     | Cookie + Datenbank (Cookie primär, DB als Backup)  | Voller Audit-Trail + schnell      |

Wenn `storage` auf `doctrine` oder `both` steht, erstelle die Migrationen in der App (das Bundle liefert Entities, keine Migrationen). Das erfordert Doctrine ORM:

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

Erhöhe `policy_version` bei Änderungen an den Kategorien, um eine erneute Einwilligung zu erzwingen.

Wenn `logging.retention_days` gesetzt ist, führe den Cleanup‑Command regelmäßig aus:

```bash
bin/console cookie-consent:cleanup
```

## Dokumentation

- **[Erste Schritte](docs/getting-started.de.md)** - Installation, Assets, erste Schritte
- **[Konfiguration](docs/configuration.de.md)** - Alle Optionen, Templates, Twig-Helfer
- **[Erweitert](docs/advanced.de.md)** - Speicher-Backends, Session-Erzwingung, Logging, Events
- **[Integration](docs/integration.de.md)** - Komponenten, Helper, Attribute, Data-Attributes, Events
- **[Changelog](CHANGELOG.md)** - Releases und Änderungen
- **[Contributing](CONTRIBUTING.de.md)** - Entwicklungsablauf und Guidelines

## Embed-Komponenten

Drittanbieter-Inhalte mit integrierten Komponenten absichern:

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

Verfügbar: YouTube, Vimeo, Google Maps, Spotify, Twitter/X, Instagram, TikTok und mehr.

## Integrationsübersicht

Siehe **[Integration](docs/integration.de.md)** für Komponenten, Helper, Data-Attributes, Controller-Attribute und Events.

## Fehlerbehebung

### Modal erscheint nicht
- Stelle sicher, dass `{{ cookie_consent_modal() }}` im Base-Template ist
- Browser-Konsole auf JavaScript-Fehler prüfen
- Stimulus-Controller geladen? `@jostkleigrewe/cookie-consent-bundle/cookie-consent`

### Assets laden nicht (404)
- `bin/console cache:clear` ausführen
- AssetMapper-Pfade prüfen: `bin/console debug:asset-map | grep cookie`
- `assets/app.js` importiert das CSS?

### Session-Cookie wird vor Consent erstellt
- `enforcement.require_consent_for_session` auf `true` prüfen
- Routen zu `stateless_routes` hinzufügen, wenn sie ohne Session funktionieren sollen
- `#[ConsentStateless]` Attribut auf stateless Controllern prüfen

### Doctrine-Speicherung funktioniert nicht
- Migrationen ausführen: `bin/console doctrine:migrations:diff && bin/console doctrine:migrations:migrate`
- `storage: doctrine` oder `storage: both` gesetzt?
- `doctrine/orm` und `doctrine/doctrine-bundle` installiert?

### Google Consent Mode aktualisiert nicht
- `google_consent_mode.enabled: true` gesetzt?
- `gtag` vor dem Consent-Modal geladen?
- Kategorie-Mapping stimmt mit deinen Kategorien überein?

### Tabler-Variante Styling-Probleme (fehlender Border-Radius, Labels unter Checkbox)
- **Ursache:** Tabler lädt nach dem Bundle-CSS und überschreibt `.modal-content` und `form-switch` Styles
- **Lösung:** Auf aktuelle Bundle-Version updaten (>= 0.4.2) mit Tabler-spezifischen Fixes
- **Manueller Fix:** CSS mit höherer Spezifität hinzufügen:
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

## Entwicklung

```bash
composer install
composer ci
```

## Lizenz

MIT – siehe [LICENSE](LICENSE).

## Ressourcen

- [Packagist](https://packagist.org/packages/jostkleigrewe/cookie-consent-bundle)
- [GitHub Repository](https://github.com/jostkleigrewe/cookie-consent-bundle)
- [Dokumentation](docs/getting-started.de.md)
- [Issues melden](https://github.com/jostkleigrewe/cookie-consent-bundle/issues)
- [Changelog](CHANGELOG.md)

## Suchbegriffe

Symfony Cookie Consent, DSGVO Cookie-Banner, GDPR Cookie-Modal, Google Consent Mode v2, Symfony 8 Bundle, Cookie-Verwaltung, Consent Management Platform, CMP, Twig Cookie-Komponente, Stimulus.js Cookie, AssetMapper, Doctrine Consent-Speicherung, YouTube Embed Consent, Datenschutz-Compliance, e-Privacy, Cookie-Einwilligung.
