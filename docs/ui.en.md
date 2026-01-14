# Templates and UI (EN)

Language switch: [Deutsch](ui.de.md) | [Index](index.en.md)

## Modal template

Default template (Tabler): `@CookieConsent/modal.html.twig`.

Built-in layout templates (numbered for easy reference):

1. Tabler (default): `@CookieConsent/styles/tabler/modal.html.twig`
2. Tabler A (side panel): `@CookieConsent/styles/tabler/modal-a.html.twig`
3. Tabler B (highlight panel): `@CookieConsent/styles/tabler/modal-b.html.twig`
4. Tabler C (icon rows): `@CookieConsent/styles/tabler/modal-c.html.twig`
5. Bootstrap: `@CookieConsent/styles/bootstrap/modal.html.twig`
6. Plain/Vanilla: `@CookieConsent/styles/plain/modal.html.twig`

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
