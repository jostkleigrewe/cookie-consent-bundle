# Integrationsleitfaden

Diese Seite bündelt alle Integrationsmöglichkeiten: Twig‑Komponenten, Helper, Data‑Attributes, Controller‑Attribute und JS‑Events.

## Quick Start

```twig
{% if cookie_consent_required() %}
  {{ cookie_consent_modal() }}
{% endif %}
```

```twig
<twig:CookieConsentYoutubeEmbed video_id="dQw4w9WgXcQ" category="marketing" vendor="youtube" />
```

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/embed_consent.js';
```

## Wann nutze ich was?

| Anwendungsfall | Empfehlung |
| --- | --- |
| Iframes/Widgets absichern | Twig‑Embed‑Komponenten |
| Inline‑Skripte absichern | `data-consent-*` Attribute |
| Serverseitige Logik steuern | Twig‑Helper |
| Consent auf Routen erzwingen | Controller‑Attribute |
| Auf Änderungen reagieren | Browser‑Events |

## Kurzreferenz

| Bedarf | Nutzen | Beispiel |
| --- | --- | --- |
| CSS (CSP-sicher) | `cookie_consent_styles()` | `{{ cookie_consent_styles() }}` (im `<head>`) |
| Modal | `cookie_consent_modal()` | `{% if cookie_consent_required() %}{{ cookie_consent_modal() }}{% endif %}` |
| Modal öffnen | `<twig:CookieSettingsButton />` | `<twig:CookieSettingsButton label="Cookie-Einstellungen" />` |
| Kategorie prüfen | `cookie_consent_has()` | `{% if cookie_consent_has('analytics') %}` |
| Vendor prüfen | `cookie_consent_vendor_has()` | `{% if cookie_consent_vendor_has('marketing', 'youtube') %}` |
| Lazy‑Skripte | `data-consent-*` | `<script type="text/plain" data-consent-category="analytics" ...>` |
| Iframe/Embed | Twig‑Embed‑Komponente | `<twig:CookieConsentYoutubeEmbed video_id="..." category="marketing" />` |
| Route erzwingen | `#[ConsentRequired]` | `#[ConsentRequired] public function checkout()` |
| Stateless Route | `#[ConsentStateless]` | `#[ConsentStateless] public function health()` |

---

## 1) Modal-Rendering

Das Modal an der gewünschten Stelle rendern (meist im Base‑Layout):

```twig
{% if cookie_consent_required() %}
  {{ cookie_consent_modal() }}
{% endif %}
```

---

## 2) Settings-Button (Twig-Komponente)

```twig
<twig:CookieSettingsButton />
```

Stimulus‑Controller aktivieren:

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

## 3) Embed-Komponenten (Twig-Komponenten)

Alle Embed‑Komponenten akzeptieren optional `vendor` für Vendor‑Consent.

```twig
<twig:CookieConsentYoutubeEmbed
  video_id="dQw4w9WgXcQ"
  category="marketing"
  vendor="youtube"
/>
```

Verfügbare Komponenten:
- CookieConsentEmbed (generisch)
- CookieConsentYoutubeEmbed
- CookieConsentVimeoEmbed
- CookieConsentGoogleMapsEmbed
- CookieConsentSpotifyEmbed
- CookieConsentSoundCloudEmbed
- CookieConsentTwitterEmbed
- CookieConsentInstagramEmbed
- CookieConsentTikTokEmbed
- CookieConsentCalendlyEmbed
- CookieConsentFacebookPageEmbed
- CookieConsentLinkedInEmbed
- CookieConsentPinterestEmbed
- CookieConsentTypeformEmbed
- CookieConsentRecaptcha

Alternative mit `component()`:

```twig
{{ component('CookieConsentYoutubeEmbed', {
  video_id: 'dQw4w9WgXcQ',
  category: 'marketing',
  vendor: 'youtube'
}) }}
```

Damit Embeds ohne Seiten‑Reload funktionieren, den Embed‑Helper einbinden:

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/embed_consent.js';
```

---

## 4) Twig-Helper

### CSS einbinden (CSP-kompatibel)

```twig
{# Im <head> des Base-Templates #}
{{ cookie_consent_styles() }}
```

Dies rendert ein Standard-`<link rel="stylesheet">`-Tag. Nutze dies statt JavaScript-CSS-Imports bei strikter Content-Security-Policy (`style-src 'self'`).

### Consent in Templates prüfen

```twig
{% if cookie_consent_has('analytics') %}
  <script src="https://example.com/analytics.js"></script>
{% endif %}

{% if cookie_consent_vendor_has('marketing', 'google_ads') %}
  <script src="https://example.com/ads.js"></script>
{% endif %}

{% if cookie_consent_has_decision() %}
  <p>Consent gegeben: {{ cookie_consent_decided_at()|date('Y-m-d H:i') }}</p>
{% endif %}
```

---

## 5) Data-Attributes (Lazy Loading)

Data‑Attributes für Skripte oder HTML‑Blöcke:

```html
<script
  type="text/plain"
  data-consent-category="analytics"
  data-consent-vendor="matomo"
  data-consent-src="https://example.com/analytics.js"></script>
```

---

## 6) Controller-Attribute

Routen/Controller bis zur Einwilligung steuern:

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;

#[ConsentRequired]
public function checkout() { /* ... */ }

#[ConsentStateless]
public function health() { /* ... */ }
```

---

## 7) Browser-Events

Das Bundle dispatcht DOM‑Events:

```javascript
window.addEventListener('cookie-consent:changed', (event) => {
  console.log('Consent geändert', event.detail);
});

window.addEventListener('cookie-consent:open', () => {
  console.log('Consent-Modal geöffnet');
});
```

---

## 8) Stimulus-Controller

Diese Controller werden mitgeliefert:
- `cookie-consent` (Modal + Preferences)
- `cookie-consent-settings-button` (Settings‑Button)

Bei AssetMapper: Controller in `assets/controllers.json` aktivieren.
