# Templates und UI (DE)

Language switch: [English](ui.en.md) | [Index](index.de.md)

## Modal-Template

Standard-Template (Tabler): `@CookieConsent/modal.html.twig`.

Integrierte Layout-Templates (nummeriert als Referenz):

1. Tabler Day (Standard): `@CookieConsent/styles/tabler/modal-day.html.twig`
2. Tabler Night: `@CookieConsent/styles/tabler/modal-night.html.twig`
3. Tabler Compact Day: `@CookieConsent/styles/tabler/modal-compact-day.html.twig`
4. Tabler Compact Night: `@CookieConsent/styles/tabler/modal-compact-night.html.twig`
5. Bootstrap: `@CookieConsent/styles/bootstrap/modal.html.twig`
6. Plain/Vanilla: `@CookieConsent/styles/plain/modal.html.twig`

Implementierungsdetail: Die Templates nutzen CSS-Varianten (z.B. `cookie-consent-variant-tabler` + `cookie-consent-variant-day`) und optionale Density-Modifier (z.B. `cookie-consent-density-compact`), um das Markup einheitlich zu halten.

Optionaler Reload nach Aenderung:

```yaml
cookie_consent:
  ui:
    reload_on_change: false
```

Template in der Config auswählen:

```yaml
cookie_consent:
  ui:
    template: '@CookieConsent/styles/plain/modal.html.twig'
```

Zum Anpassen in die App kopieren:

```
templates/bundles/CookieConsent/styles/plain/modal.html.twig
```

Optionale Links (im Footer verwendet):

```yaml
cookie_consent:
  ui:
    privacy_url: '/datenschutz' # optional
    imprint_url: '/impressum' # optional
```

Wenn keine Links gesetzt sind, wird der Link-Teil im Footer ausgeblendet.

## Übersetzungen

Translations liegen in `translations/messages.*.yaml` (Domain: `messages`).

## Twig-Helper

- `cookie_consent_modal()` rendert das Modal.
- `cookie_consent_has('analytics')` prüft eine Kategorie.
- `cookie_consent_preferences()` liefert die aktuelle Map.
- `cookie_consent_preferences_raw()` liefert die rohen Preferences (ohne Normalisierung).
- `cookie_consent_has_decision()` ist true, wenn eine Entscheidung getroffen wurde.
- `cookie_consent_decided_at()` liefert den Entscheidungszeitpunkt (oder null).
- `cookie_consent_required()` zeigt an, ob das Modal erzwungen ist.
- `cookie_consent_categories()` liefert die konfigurierten Kategorien.

Beispiele:

```twig
{% if cookie_consent_has_decision() %}
  <p>Consent entschieden am {{ cookie_consent_decided_at()|date('c') }}</p>
{% endif %}
```

```twig
{% set raw = cookie_consent_preferences_raw() %}
{% if raw.analytics is defined and raw.analytics %}
  {# analytics erlaubt durch explizite Entscheidung #}
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

## YouTube-Embed (ohne Reload)

Template:

```twig
{{ include('@CookieConsent/components/CookieConsentYoutubeEmbed.html.twig', {
  video_id: 'VIDEO_ID',
  category: 'marketing'
}) }}
```

JS-Helper (AssetMapper/Importmap):

```js
import '@jostkleigrewe/cookie-consent-bundle/embed_consent.js';
```

Inline-Alternative:

```html
<script>
  // Inline-Alternative: Inhalt aus assets/dist/embed_consent.js hier einfuegen.
</script>
```

Generisches Embed-Template:

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

## Settings-Button-Komponente

```twig
{{ component('CookieSettingsButton', { label: 'Datenschutzeinstellungen' }) }}
```

Der Button feuert ein `cookie-consent:open`-Event, das das Modal öffnet.

Wenn du Importmap/Stimulus nutzt, aktiviere den Controller in `assets/controllers.json`:

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

## Browser-Events

- `cookie-consent:open` öffnet das Modal.
- `cookie-consent:changed` feuert nach dem Speichern (Payload: `{ preferences }`).
