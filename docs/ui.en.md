# Templates and UI (EN)

Language switch: [Deutsch](ui.de.md) | [Index](index.en.md)

## Modal template

Default template (Tabler): `@CookieConsent/modal.html.twig`.

Built-in layout templates (numbered for easy reference):

1. Tabler Day (default): `@CookieConsent/styles/tabler/modal-day.html.twig`
2. Tabler Night: `@CookieConsent/styles/tabler/modal-night.html.twig`
3. Tabler Compact Day: `@CookieConsent/styles/tabler/modal-compact-day.html.twig`
4. Tabler Compact Night: `@CookieConsent/styles/tabler/modal-compact-night.html.twig`
5. Bootstrap: `@CookieConsent/styles/bootstrap/modal.html.twig`
6. Plain/Vanilla: `@CookieConsent/styles/plain/modal.html.twig`

Implementation detail: the templates use CSS variants (e.g. `cookie-consent-variant-tabler` + `cookie-consent-variant-day`) and optional density modifiers (e.g. `cookie-consent-density-compact`) to keep the markup consistent.

Optional reload after change:

```yaml
cookie_consent:
  ui:
    reload_on_change: false
```

Pick a template in your config:

```yaml
cookie_consent:
  ui:
    template: '@CookieConsent/styles/plain/modal.html.twig'
```

To customize, copy a template into your app and adjust it:

```
templates/bundles/CookieConsent/styles/plain/modal.html.twig
```

Optional links (used in the footer):

```yaml
cookie_consent:
  ui:
    privacy_url: '/privacy' # optional
    imprint_url: '/imprint' # optional
```

If neither link is set, the footer links section is hidden.

## Translations

Translation keys live in `translations/messages.*.yaml` (domain: `messages`).

## Twig helpers

- `cookie_consent_modal()` renders the modal.
- `cookie_consent_has('analytics')` checks a category.
- `cookie_consent_preferences()` returns the current map.
- `cookie_consent_preferences_raw()` returns the raw stored preferences (no normalization).
- `cookie_consent_has_decision()` returns true when the user has made a decision.
- `cookie_consent_decided_at()` returns the decision timestamp (or null).
- `cookie_consent_required()` tells whether the modal should be shown.
- `cookie_consent_categories()` returns the configured categories.

Examples:

```twig
{% if cookie_consent_has_decision() %}
  <p>Consent decided at {{ cookie_consent_decided_at()|date('c') }}</p>
{% endif %}
```

```twig
{% set raw = cookie_consent_preferences_raw() %}
{% if raw.analytics is defined and raw.analytics %}
  {# analytics allowed by explicit decision #}
{% endif %}
```

```twig
{% if cookie_consent_required() %}
  {{ cookie_consent_modal() }}
{% endif %}
```

```twig
{% if cookie_consent_has('analytics') %}
  <script type="text/plain" data-consent-category="analytics" data-consent-src="https://example.com/analytics.js"></script>
{% endif %}
```

## YouTube embed (no reload)

Template:

```twig
{{ include('@CookieConsent/components/CookieConsentYoutubeEmbed.html.twig', {
  video_id: 'VIDEO_ID',
  category: 'marketing'
}) }}
```

JS helper (AssetMapper/Importmap):

```js
import '@jostkleigrewe/cookie-consent-bundle/embed_consent.js';
```

Inline alternative:

```html
<script>
  // Inline alternative: paste the contents of assets/dist/embed_consent.js here.
</script>
```

Generic embed template:

```twig
{{ include('@CookieConsent/components/CookieConsentEmbed.html.twig', {
  src: 'https://example.com/embed',
  title: 'Embedded content',
  category: 'marketing',
  type: 'iframe',
  allow: 'autoplay; encrypted-media',
  aspect_ratio: '16 / 9'
}) }}
```

Presets:

```twig
{{ include('@CookieConsent/components/CookieConsentYoutubeEmbed.html.twig', {
  video_id: 'VIDEO_ID',
  category: 'marketing'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentVimeoEmbed.html.twig', {
  video_id: 'VIDEO_ID',
  category: 'marketing'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentGoogleMapsEmbed.html.twig', {
  src: 'https://www.google.com/maps/embed?pb=...'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentSpotifyEmbed.html.twig', {
  src: 'https://open.spotify.com/embed/track/TRACK_ID',
  aspect_ratio: '16 / 9'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentSoundCloudEmbed.html.twig', {
  src: 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/TRACK_ID'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentTwitterEmbed.html.twig', {
  tweet_url: 'https://x.com/user/status/STATUS_ID'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentInstagramEmbed.html.twig', {
  post_url: 'https://www.instagram.com/p/POST_ID/'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentTikTokEmbed.html.twig', {
  video_url: 'https://www.tiktok.com/@user/video/VIDEO_ID'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentCalendlyEmbed.html.twig', {
  src: 'https://calendly.com/your-org/your-event'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentFacebookPageEmbed.html.twig', {
  src: 'https://www.facebook.com/plugins/page.php?href=...'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentLinkedInEmbed.html.twig', {
  post_url: 'https://www.linkedin.com/posts/USER_POST'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentPinterestEmbed.html.twig', {
  pin_url: 'https://www.pinterest.com/pin/PIN_ID/'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentTypeformEmbed.html.twig', {
  src: 'https://form.typeform.com/to/FORM_ID'
}) }}
```

```twig
{{ include('@CookieConsent/components/CookieConsentRecaptcha.html.twig') }}
```

## Settings button component

```twig
{{ component('CookieSettingsButton', { label: 'Cookie settings' }) }}
```

The button dispatches a `cookie-consent:open` event that opens the modal.

If you use Importmap/Stimulus, enable the controller in `assets/controllers.json`:

```json
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

## Browser events

- `cookie-consent:open` opens the modal.
- `cookie-consent:changed` fires after saving preferences (payload: `{ preferences }`).
