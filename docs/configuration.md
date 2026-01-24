# Configuration

[Deutsch](configuration.de.md) | [Back to README](../README.md)

## Minimal Configuration

```yaml
cookie_consent: ~
```

This uses all defaults: Tabler variant, day theme, standard categories.

---

## Full Configuration Reference

Create `config/packages/cookie_consent.yaml`:

```yaml
cookie_consent:
  # Increment when changing categories to require re-consent
  policy_version: '1'

  # Storage backend: cookie, doctrine, or both
  storage: cookie

  # Cookie settings
  cookie:
    name: cookie_consent
    lifetime: 15552000        # 6 months
    same_site: lax
    http_only: true

  # Identifier cookie (for Doctrine storage)
  identifier_cookie:
    name: cookie_consent_id
    lifetime: 31536000        # 1 year
    http_only: true

  # Consent categories
  categories:
    necessary:
      label: Necessary
      description: Required for basic site functionality.
      required: true
      default: true
    analytics:
      label: Analytics
      description: Help us understand how visitors use our site.
      default: false
    marketing:
      label: Marketing
      description: Used for personalized advertising.
      default: false

  # UI settings
  ui:
    template: '@CookieConsent/modal.html.twig'
    variant: tabler           # plain, bootstrap, tabler
    theme: day                # day, night, auto
    density: normal           # normal, compact
    privacy_url: '/privacy'   # optional
    imprint_url: '/imprint'   # optional
    reload_on_change: false

  # Session enforcement
  enforcement:
    require_consent_for_session: true
    stateless_paths: ['/health', '/api']
    stateless_routes: []
    protected_paths: ['/checkout']
    protected_routes: []

  # Audit logging
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

## UI Variants

### `variant`

| Value | Description |
|-------|-------------|
| `tabler` | Tabler UI framework style with switch toggles and badge |
| `bootstrap` | Bootstrap 5 compatible with native button classes |
| `plain` | Framework-agnostic with minimal dependencies |

### `theme`

| Value | Description |
|-------|-------------|
| `day` | Light color scheme |
| `night` | Dark color scheme |
| `auto` | Follows `prefers-color-scheme` media query |

### `density`

| Value | Description |
|-------|-------------|
| `normal` | Standard spacing and typography |
| `compact` | Reduced padding for smaller footprint |

---

## Templates

### Built-in Templates (Legacy)

For backwards compatibility, the following template paths still work:

| Template | Path |
|----------|------|
| Tabler Day (default) | `@CookieConsent/styles/tabler/modal-day.html.twig` |
| Tabler Night | `@CookieConsent/styles/tabler/modal-night.html.twig` |
| Tabler Compact Day | `@CookieConsent/styles/tabler/modal-compact-day.html.twig` |
| Tabler Compact Night | `@CookieConsent/styles/tabler/modal-compact-night.html.twig` |
| Bootstrap | `@CookieConsent/styles/bootstrap/modal.html.twig` |
| Plain/Vanilla | `@CookieConsent/styles/plain/modal.html.twig` |

**Recommended:** Use the new `variant`, `theme`, `density` options instead of separate template paths.

### Custom Template

Copy a template to your app and modify:

```
templates/bundles/CookieConsentBundle/modal.html.twig
```

Or override individual partials:

```
templates/bundles/CookieConsentBundle/_partials/tabler/header.html.twig
```

---

## Twig Helpers

| Function | Description |
|----------|-------------|
| `cookie_consent_modal()` | Renders the consent modal |
| `cookie_consent_modal({variant: 'bootstrap'})` | Renders with override options |
| `cookie_consent_has('category')` | Check if category is consented |
| `cookie_consent_preferences()` | Get all current preferences |
| `cookie_consent_preferences_raw()` | Get raw stored preferences |
| `cookie_consent_has_decision()` | User has made a decision? |
| `cookie_consent_decided_at()` | Timestamp of decision (or null) |
| `cookie_consent_required()` | Should the modal be shown? |
| `cookie_consent_categories()` | Get configured categories |

### Examples

```twig
{# Override variant/theme per instance #}
{{ cookie_consent_modal({variant: 'bootstrap', theme: 'night'}) }}

{# Conditional content #}
{% if cookie_consent_has('analytics') %}
  <script src="https://example.com/analytics.js"></script>
{% endif %}

{# Show decision timestamp #}
{% if cookie_consent_has_decision() %}
  <p>Consent given: {{ cookie_consent_decided_at()|date('Y-m-d H:i') }}</p>
{% endif %}

{# Conditional modal rendering #}
{% if cookie_consent_required() %}
  {{ cookie_consent_modal() }}
{% endif %}
```

---

## Lazy Script Loading

Scripts load automatically when consent is given:

```html
<script type="text/plain"
        data-consent-category="analytics"
        data-consent-src="https://example.com/analytics.js"></script>
```

---

## Embed Components

Gate third-party embeds with built-in components:

```twig
{# YouTube #}
{{ include('@CookieConsent/components/CookieConsentYoutubeEmbed.html.twig', {
  video_id: 'VIDEO_ID',
  category: 'marketing'
}) }}

{# Vimeo #}
{{ include('@CookieConsent/components/CookieConsentVimeoEmbed.html.twig', {
  video_id: 'VIDEO_ID',
  category: 'marketing'
}) }}

{# Google Maps #}
{{ include('@CookieConsent/components/CookieConsentGoogleMapsEmbed.html.twig', {
  src: 'https://www.google.com/maps/embed?pb=...'
}) }}

{# Generic embed #}
{{ include('@CookieConsent/components/CookieConsentEmbed.html.twig', {
  src: 'https://example.com/embed',
  title: 'Embedded content',
  category: 'marketing',
  type: 'iframe',
  allow: 'autoplay; encrypted-media',
  aspect_ratio: '16 / 9'
}) }}
```

**Available components:** YouTube, Vimeo, Google Maps, Spotify, SoundCloud, Twitter/X, Instagram, TikTok, Calendly, Facebook, LinkedIn, Pinterest, Typeform, reCAPTCHA.

For embeds to work without page reload, include the embed helper:

```javascript
// assets/app.js
import '@jostkleigrewe/cookie-consent-bundle/embed_consent.js';
```

---

## Settings Button

Add a button to re-open the consent modal:

```twig
{{ component('CookieSettingsButton', { label: 'Cookie settings' }) }}
```

Enable the controller:

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

Integrates with Google Analytics 4 and Google Ads.

### 1. Set default consent (before GA loads)

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

### 2. Enable in config

```yaml
cookie_consent:
  google_consent_mode:
    enabled: true
```

The bundle automatically calls `gtag('consent', 'update', ...)` when preferences change.

### Custom category mapping

```yaml
cookie_consent:
  categories:
    statistics:   # custom name
      label: Statistics
    advertising:  # custom name
      label: Advertising
  google_consent_mode:
    enabled: true
    mapping:
      analytics_storage: statistics
      ad_storage: advertising
      ad_user_data: advertising
      ad_personalization: advertising
```

---

## Browser Events

| Event | Description | Payload |
|-------|-------------|---------|
| `cookie-consent:open` | Opens the modal | - |
| `cookie-consent:changed` | Fires after saving | `{ preferences }` |

```javascript
document.addEventListener('cookie-consent:changed', (e) => {
  console.log('New preferences:', e.detail.preferences);
});
```

---

## Translations

Translation keys are in `translations/messages.*.yaml`. Override by creating your own translations in the `messages` domain.

---

## Template Showcase

For visual testing of all variant combinations, visit:

```
/_cookie-consent/showcase
```

This displays all 12 combinations (3 variants × 2 themes × 2 densities) on a single page.
