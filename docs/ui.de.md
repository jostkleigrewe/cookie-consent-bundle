# Templates und UI (DE)

Language switch: [English](ui.en.md) | [Index](index.de.md)

## Modal-Template

Standard-Template (Tabler): `@CookieConsent/modal.html.twig`.

Integrierte Layout-Templates (nummeriert als Referenz):

1. Tabler (Standard): `@CookieConsent/styles/tabler/modal.html.twig`
2. Tabler A (Side-Panel): `@CookieConsent/styles/tabler/modal-a.html.twig`
3. Tabler B (Highlight-Panel): `@CookieConsent/styles/tabler/modal-b.html.twig`
4. Tabler C (Icon-Reihen): `@CookieConsent/styles/tabler/modal-c.html.twig`
5. Bootstrap: `@CookieConsent/styles/bootstrap/modal.html.twig`
6. Plain/Vanilla: `@CookieConsent/styles/plain/modal.html.twig`

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
