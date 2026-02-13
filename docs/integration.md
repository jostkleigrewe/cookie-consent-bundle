# Integration Guide

This page summarizes all integration options in one place: Twig components, helpers, data attributes, controller attributes, and JS events.

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

## When to use what

| Use case | Recommended |
| --- | --- |
| Gate iframes/widgets | Twig embed components |
| Gate inline scripts | `data-consent-*` attributes |
| Gate server-rendered logic | Twig helpers |
| Enforce consent on routes | Controller attributes |
| React to changes | Browser events |

## Quick reference

| Need | Use | Example |
| --- | --- | --- |
| CSS (CSP-safe) | `cookie_consent_styles()` | `{{ cookie_consent_styles() }}` (in `<head>`) |
| Modal | `cookie_consent_modal()` | `{% if cookie_consent_required() %}{{ cookie_consent_modal() }}{% endif %}` |
| Re-open modal | `<twig:CookieSettingsButton />` | `<twig:CookieSettingsButton label="Cookie settings" />` |
| Check category | `cookie_consent_has()` | `{% if cookie_consent_has('analytics') %}` |
| Check vendor | `cookie_consent_vendor_has()` | `{% if cookie_consent_vendor_has('marketing', 'youtube') %}` |
| Lazy scripts | `data-consent-*` | `<script type="text/plain" data-consent-category="analytics" ...>` |
| Iframe/embed | Twig embed component | `<twig:CookieConsentYoutubeEmbed video_id="..." category="marketing" />` |
| Route control | `#[ConsentRequired]` | `#[ConsentRequired] public function checkout()` |
| Stateless route | `#[ConsentStateless]` | `#[ConsentStateless] public function health()` |

---

## 1) Modal Rendering

Render the consent modal where you want it to appear (usually in your base layout):

```twig
{% if cookie_consent_required() %}
  {{ cookie_consent_modal() }}
{% endif %}
```

---

## 2) Settings Button (Twig Component)

```twig
<twig:CookieSettingsButton />
```

Enable the Stimulus controller:

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

## 3) Embed Components (Twig Components)

All embed components accept optional `vendor` for vendor-level consent.

```twig
<twig:CookieConsentYoutubeEmbed
  video_id="dQw4w9WgXcQ"
  category="marketing"
  vendor="youtube"
/>
```

Available components:
- CookieConsentEmbed (generic)
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

Alternative with `component()`:

```twig
{{ component('CookieConsentYoutubeEmbed', {
  video_id: 'dQw4w9WgXcQ',
  category: 'marketing',
  vendor: 'youtube'
}) }}
```

For embeds to work without page reload, include the embed helper:

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/embed_consent.js';
```

---

## 4) Twig Helpers

### Include CSS (CSP-compatible)

```twig
{# In <head> of your base template #}
{{ cookie_consent_styles() }}
```

This renders a standard `<link rel="stylesheet">` tag. Use this instead of JavaScript CSS imports when you have a strict Content-Security-Policy (`style-src 'self'`).

### Check consent in templates

```twig
{% if cookie_consent_has('analytics') %}
  <script src="https://example.com/analytics.js"></script>
{% endif %}

{% if cookie_consent_vendor_has('marketing', 'google_ads') %}
  <script src="https://example.com/ads.js"></script>
{% endif %}

{% if cookie_consent_has_decision() %}
  <p>Consent given: {{ cookie_consent_decided_at()|date('Y-m-d H:i') }}</p>
{% endif %}
```

---

## 5) Data Attributes (Lazy Loading)

Use data attributes for scripts or HTML blocks:

```html
<script
  type="text/plain"
  data-consent-category="analytics"
  data-consent-vendor="matomo"
  data-consent-src="https://example.com/analytics.js"></script>
```

---

## 6) Controller Attributes

Restrict routes or controllers until consent is available:

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;

#[ConsentRequired]
public function checkout() { /* ... */ }

#[ConsentStateless]
public function health() { /* ... */ }
```

---

## 7) Browser Events

The bundle dispatches DOM events you can listen for:

```javascript
window.addEventListener('cookie-consent:changed', (event) => {
  console.log('Consent changed', event.detail);
});

window.addEventListener('cookie-consent:open', () => {
  console.log('Consent modal opened');
});
```

---

## 8) Stimulus Controllers

The bundle ships these controllers:
- `cookie-consent` (modal + preferences)
- `cookie-consent-settings-button` (settings button)

If you use AssetMapper, ensure the controllers are enabled in `assets/controllers.json`.
