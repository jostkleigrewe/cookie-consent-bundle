# Cookie Consent Bundle (Symfony 8)

Symfony 8 bundle for GDPR-compliant cookie consent with Twig, Stimulus, and Turbo integration. Includes a Tabler-inspired modal UI and flexible configuration.

## Features

- Cookie-only or database-backed consent storage
- Twig helpers for consent checks and modal rendering
- Stimulus controller for modal actions and consent toggles
- Turbo-friendly events (auto-open on `turbo:load`)
- Configurable categories (necessary, functional, analytics, marketing)
- Session blocking when consent is required and missing
- Attribute- and config-based stateless/protected areas

## Requirements

- PHP 8.5+
- Symfony 8
- Twig Bundle
- Security Bundle (for session enforcement based on firewall statelessness)

## Installation

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

Enable the bundle (if not auto-registered):

```php
// config/bundles.php
JostKleigrewe\CookieConsentBundle\CookieConsentBundle::class => ['all' => true],
```

## Assets (Stimulus + CSS)

### AssetMapper (recommended)

```yaml
# config/packages/asset_mapper.yaml
framework:
  asset_mapper:
    paths:
      - vendor/jostkleigrewe/cookie-consent-bundle/assets
```

Enable the Stimulus controller:

```json
// assets/controllers.json
{
  "controllers": {
    "cookie-consent": {
      "enabled": true,
      "fetch": "lazy"
    }
  }
}
```

Include the CSS in your app entry:

```js
// assets/app.js
import 'cookie-consent-bundle/styles/cookie_consent.css';
```

### Webpack Encore

If you use Encore, copy or import the assets from `vendor/jostkleigrewe/cookie-consent-bundle/assets`.

## Usage

Add the modal to your base layout:

```twig
{{ cookie_consent_modal() }}
```

Toggle content based on consent:

```twig
{% if cookie_consent_has('analytics') %}
  {# analytics script #}
{% endif %}
```

For lazy-loading scripts, use `data-consent-category`:

```html
<script type="text/plain" data-consent-category="analytics" data-consent-src="https://example.com/analytics.js"></script>
```

## Configuration

```yaml
# config/packages/cookie_consent.yaml
cookie_consent:
  policy_version: '1'
  storage: cookie # or doctrine
  cookie:
    name: cookie_consent
    lifetime: 15552000
    same_site: lax
  categories:
    necessary:
      label: Necessary
      required: true
    analytics:
      label: Analytics
      default: false
  ui:
    template: '@CookieConsentBundle/modal.html.twig'
    layout: tabler
  enforcement:
    require_consent_for_session: true
    stateless_paths: ['/health', '/webhook']
    protected_paths: ['/checkout']
```

## Database storage

Set `storage: doctrine` and create a table:

```sql
CREATE TABLE cookie_consent (
  id VARCHAR(64) PRIMARY KEY,
  preferences JSON NOT NULL,
  policy_version VARCHAR(32) NOT NULL,
  decided_at DATETIME NULL
);
```

The bundle stores a random identifier cookie (`cookie_consent_id`) and persists consent by that ID.

## Attributes

```php
use JostKleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use JostKleigrewe\CookieConsentBundle\Attribute\ConsentStateless;

#[ConsentRequired]
class CheckoutController { ... }

#[ConsentStateless]
class HealthController { ... }
```

## Overriding the UI

Override `@CookieConsentBundle/modal.html.twig` in your app or change the `ui.template` config.

## License

MIT
