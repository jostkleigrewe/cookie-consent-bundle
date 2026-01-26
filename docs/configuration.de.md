# Konfiguration

[English](configuration.md) | [Zurück zur README](../README.de.md)

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

  # UI-Einstellungen
  ui:
    template: '@CookieConsent/styles/tabler/modal.html.twig'
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

  # Google Consent Mode v2
  google_consent_mode:
    enabled: false
    mapping:
      analytics_storage: analytics
      ad_storage: marketing
      ad_user_data: marketing
      ad_personalization: marketing
```

---

## Templates

### Mitgelieferte Templates

| Template | Pfad |
|----------|------|
| Tabler Day (Standard) | `@CookieConsent/styles/tabler/modal-day.html.twig` |
| Tabler Night | `@CookieConsent/styles/tabler/modal-night.html.twig` |
| Tabler Compact Day | `@CookieConsent/styles/tabler/modal-compact-day.html.twig` |
| Tabler Compact Night | `@CookieConsent/styles/tabler/modal-compact-night.html.twig` |
| Bootstrap | `@CookieConsent/styles/bootstrap/modal.html.twig` |
| Plain/Vanilla | `@CookieConsent/styles/plain/modal.html.twig` |

### Eigenes Template

Template in die App kopieren und anpassen:

```
templates/bundles/CookieConsentBundle/styles/plain/modal.html.twig
```

---

## Position

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

### Beispiele

```twig
{# Bedingte Inhalte #}
{% if cookie_consent_has('analytics') %}
  <script src="https://example.com/analytics.js"></script>
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
        data-consent-src="https://example.com/analytics.js"></script>
```

---

## Embed-Komponenten

Drittanbieter-Embeds mit integrierten Komponenten absichern:

```twig
{# YouTube #}
{{ include('@CookieConsent/components/CookieConsentYoutubeEmbed.html.twig', {
  video_id: 'VIDEO_ID',
  category: 'marketing'
}) }}

{# Google Maps #}
{{ include('@CookieConsent/components/CookieConsentGoogleMapsEmbed.html.twig', {
  src: 'https://www.google.com/maps/embed?pb=...'
}) }}
```

**Verfügbar:** YouTube, Vimeo, Google Maps, Spotify, SoundCloud, Twitter/X, Instagram, TikTok, Calendly, Facebook, LinkedIn, Pinterest, Typeform, reCAPTCHA.

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
        "fetch": "eager"
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
