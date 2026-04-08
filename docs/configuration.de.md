# Konfiguration

[English](configuration.md) | [Zurück zur README](../README.de.md)

## Minimal-Konfiguration

```yaml
cookie_consent: ~
```

Nutzt alle Defaults: Tabler-Variante, Day-Theme, Standard-Kategorien.

---

## Vollständige Konfigurationsreferenz

Erstelle `config/packages/cookie_consent.yaml`:

```yaml
cookie_consent:
  # Bei Änderungen an Kategorien erhöhen, um erneute Einwilligung zu erzwingen
  policy_version: '1'

  # Speicher-Backend: cookie, doctrine oder both
  storage: cookie

  # Cookie-Einstellungen
  cookie:
    name: cookie_consent
    lifetime: 15552000        # 6 Monate
    same_site: lax
    http_only: true

  # Identifier-Cookie (für Doctrine-Speicherung)
  identifier_cookie:
    name: cookie_consent_id
    lifetime: 31536000        # 1 Jahr
    http_only: true

  # Einwilligungskategorien
  categories:
    necessary:
      label: Notwendig
      description: Erforderlich für grundlegende Website-Funktionen.
      required: true
      default: true
    analytics:
      label: Analyse
      description: Hilft uns zu verstehen, wie Besucher unsere Seite nutzen.
      default: false
    marketing:
      label: Marketing
      description: Wird für personalisierte Werbung verwendet.
      default: false
      vendors:
        google_ads:
          label: Google Ads
          description: Conversion-Tracking und Remarketing.
          default: false

  # UI-Einstellungen
  ui:
    template: '@CookieConsent/modal.html.twig'
    variant: tabler             # plain, bootstrap, tabler
    theme: day                  # day, night, auto
    density: normal             # normal, compact
    position: center            # center, bottom, top, left, right, top-left, top-right, bottom-left, bottom-right
    privacy_url: '/datenschutz'   # optional
    imprint_url: '/impressum'     # optional
    reload_on_change: false

  # Session-Erzwingung
  enforcement:
    require_consent_for_session: true
    stateless_paths: ['/health', '/api']
    stateless_routes: []
    protected_paths: ['/checkout']
    protected_routes: []

  # Audit-Logging
  logging:
    enabled: false
    level: info
    anonymize_ip: true
    retention_days: null

  # Google Consent Mode v2
  google_consent_mode:
    enabled: false
    mapping:
      analytics_storage: analytics
      ad_storage: marketing
      ad_user_data: marketing
      ad_personalization: marketing
```

Wenn `storage` auf `doctrine` oder `both` steht, ist Doctrine ORM erforderlich. Erstelle die Migrationen in der App (das Bundle liefert Entities, keine Migrationen):

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

---

## UI-Varianten

### `variant`

| Wert | Beschreibung |
|------|--------------|
| `tabler` | Tabler-UI-Framework-Stil mit Switch-Toggles und Badge |
| `bootstrap` | Bootstrap-5-kompatibel mit nativen Button-Klassen |
| `plain` | Framework-agnostisch, minimale Abhängigkeiten |

### `theme`

| Wert | Beschreibung |
|------|--------------|
| `day` | Helles Farbschema |
| `night` | Dunkles Farbschema |
| `auto` | Folgt der `prefers-color-scheme`-Media-Query |

### `density`

| Wert | Beschreibung |
|------|--------------|
| `normal` | Standard-Abstände und -Typografie |
| `compact` | Reduziertes Padding für kompaktere Darstellung |

### `position`

| Wert | Beschreibung |
|------|--------------|
| `center` | Zentriert (Standard) |
| `top` | Oben mittig |
| `bottom` | Unten mittig |
| `left` | Links mittig |
| `right` | Rechts mittig |
| `top-left` | Oben links |
| `top-right` | Oben rechts |
| `bottom-left` | Unten links |
| `bottom-right` | Unten rechts |

---

## Templates

### Mitgelieferte Templates (Legacy)

Aus Gründen der Rückwärtskompatibilität funktionieren die folgenden Template-Pfade weiterhin:

| Template | Pfad |
|----------|------|
| Tabler Day (Standard) | `@CookieConsent/styles/tabler/modal-day.html.twig` |
| Tabler Night | `@CookieConsent/styles/tabler/modal-night.html.twig` |
| Tabler Compact Day | `@CookieConsent/styles/tabler/modal-compact-day.html.twig` |
| Tabler Compact Night | `@CookieConsent/styles/tabler/modal-compact-night.html.twig` |
| Bootstrap | `@CookieConsent/styles/bootstrap/modal.html.twig` |
| Plain/Vanilla | `@CookieConsent/styles/plain/modal.html.twig` |

**Empfehlung:** Nutze stattdessen die neuen Optionen `variant`, `theme` und `density` statt separater Template-Pfade.

### Eigenes Template

Template in die App kopieren und anpassen:

```
templates/bundles/CookieConsentBundle/modal.html.twig
```

Oder einzelne Partials überschreiben:

```
templates/bundles/CookieConsentBundle/_partials/tabler/header.html.twig
```

---

## Vendoren

Vendoren sind optional pro Kategorie. Falls vorhanden, kann der Nutzer Anbieter innerhalb der Kategorie erlauben/ablehnen.
Die Vendor-Liste öffnet sich automatisch, wenn die Kategorie aktiviert wird, und schließt sich beim Deaktivieren.
Vendor-`default`-Werte werden angewendet, wenn eine Kategorie im Modal aktiviert wird.

```yaml
categories:
  marketing:
    vendors:
      google_ads:
        label: Google Ads
        default: false
```

---

## Twig-Helfer

| Funktion | Beschreibung |
|----------|--------------|
| `cookie_consent_modal()` | Rendert das Consent-Modal |
| `cookie_consent_has('category')` | Prüft, ob Kategorie zugestimmt wurde |
| `cookie_consent_preferences()` | Gibt alle aktuellen Präferenzen zurück |
| `cookie_consent_preferences_raw()` | Gibt rohe gespeicherte Präferenzen zurück |
| `cookie_consent_has_decision()` | Hat der Nutzer entschieden? |
| `cookie_consent_decided_at()` | Zeitstempel der Entscheidung (oder null) |
| `cookie_consent_required()` | Soll das Modal angezeigt werden? |
| `cookie_consent_categories()` | Gibt konfigurierte Kategorien zurück |
| `cookie_consent_vendor_has('category', 'vendor')` | Prüft Vendor-Zustimmung |

### Beispiele

```twig
{# Bedingte Inhalte #}
{% if cookie_consent_has('analytics') %}
  <script src="https://example.com/analytics.js"></script>
{% endif %}

{% if cookie_consent_vendor_has('marketing', 'google_ads') %}
  <script src="https://example.com/ads.js"></script>
{% endif %}

{# Entscheidungszeitpunkt anzeigen #}
{% if cookie_consent_has_decision() %}
  <p>Einwilligung erteilt: {{ cookie_consent_decided_at()|date('d.m.Y H:i') }}</p>
{% endif %}

{# Bedingtes Modal-Rendering #}
{% if cookie_consent_required() %}
  {{ cookie_consent_modal() }}
{% endif %}
```

---

## Lazy Script Loading

Skripte werden automatisch geladen, wenn die Einwilligung erteilt wird:

```html
<script type="text/plain"
        data-consent-category="analytics"
        data-consent-vendor="matomo"
        data-consent-src="https://example.com/analytics.js"></script>
```

---

## Embed-Komponenten

Alle Embed-Komponenten sind in **[Integration](integration.de.md)** dokumentiert (Verwendung, vollständige Liste, Best Practices).

Für Embeds ohne Seiten-Reload den Embed-Helfer einbinden:

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/embed_consent.js';
```

---

## Einstellungs-Button

Button zum erneuten Öffnen des Consent-Modals:

```twig
{{ component('CookieSettingsButton', { label: 'Cookie-Einstellungen' }) }}
```

Controller aktivieren:

```json
// assets/controllers.json
{
  "controllers": {
    "@jostkleigrewe/cookie-consent-bundle": {
      "cookie-consent-settings-button": {
        "enabled": true,
        "fetch": "lazy"
      }
    }
  }
}
```

---

## Google Consent Mode v2

Integration mit Google Analytics 4 und Google Ads.

### 1. Standard-Einwilligung setzen (vor GA-Laden)

```html
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('consent', 'default', {
    'analytics_storage': 'denied',
    'ad_storage': 'denied',
    'ad_user_data': 'denied',
    'ad_personalization': 'denied'
  });
</script>
```

### 2. In Konfiguration aktivieren

```yaml
cookie_consent:
  google_consent_mode:
    enabled: true
```

Das Bundle ruft automatisch `gtag('consent', 'update', ...)` auf, wenn sich Präferenzen ändern.

### Eigenes Kategorie-Mapping

```yaml
cookie_consent:
  categories:
    statistics:   # eigener Name
      label: Statistik
    advertising:  # eigener Name
      label: Werbung
  google_consent_mode:
    enabled: true
    mapping:
      analytics_storage: statistics
      ad_storage: advertising
      ad_user_data: advertising
      ad_personalization: advertising
```

---

## Browser-Events

| Event | Beschreibung | Payload |
|-------|--------------|---------|
| `cookie-consent:open` | Öffnet das Modal | - |
| `cookie-consent:changed` | Wird nach dem Speichern ausgelöst | `{ preferences }` |

```javascript
document.addEventListener('cookie-consent:changed', (e) => {
  console.log('Neue Präferenzen:', e.detail.preferences);
});
```

---

## Übersetzungen

Übersetzungsschlüssel befinden sich in `translations/messages.*.yaml`. Überschreibe sie durch eigene Übersetzungen in der Domain `messages`.

---

## Template-Showcase

Für die visuelle Überprüfung aller Varianten-Kombinationen aufrufen:

```
/_cookie-consent/showcase
```

Zeigt alle 12 Kombinationen (3 Varianten × 2 Themes × 2 Dichten) auf einer einzigen Seite an.
