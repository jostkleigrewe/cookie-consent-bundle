# Cookie Consent Bundle

## Quick start

1) Install bundle

```bash
composer require jostkleigrewe/cookie-consent-bundle
```

2) Add assets

```yaml
# config/packages/asset_mapper.yaml
framework:
  asset_mapper:
    paths:
      - vendor/jostkleigrewe/cookie-consent-bundle/assets
```

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

```js
// assets/app.js
import 'styles/cookie_consent.css';
```

3) Render modal

```twig
{{ cookie_consent_modal() }}
```

## Consent-aware content

```twig
{% if cookie_consent_has('analytics') %}
  <script src="https://example.com/analytics.js"></script>
{% endif %}
```

### Lazy script loading

```html
<script type="text/plain" data-consent-category="analytics" data-consent-src="https://example.com/analytics.js"></script>
```

## Session enforcement

The bundle can prevent stateful sessions when consent is required and missing. This is controlled by:

```yaml
cookie_consent:
  enforcement:
    require_consent_for_session: true
    stateless_paths: ['/health']
    protected_paths: ['/checkout']
```

To mark controllers:

```php
#[ConsentRequired]
class CheckoutController { ... }

#[ConsentStateless]
class HealthController { ... }
```

## Database storage

```yaml
cookie_consent:
  storage: doctrine
```

SQL schema:

```sql
CREATE TABLE cookie_consent (
  id VARCHAR(64) PRIMARY KEY,
  preferences JSON NOT NULL,
  policy_version VARCHAR(32) NOT NULL,
  decided_at DATETIME NULL
);
```

The ID is stored in `cookie_consent_id` and can be configured via `identifier_cookie`.

## UI customization

Override the Twig template or replace it entirely:

```yaml
cookie_consent:
  ui:
    template: '@CookieConsentBundle/modal.html.twig'
```

Copy the template from the bundle to your app and adjust layout/classes.
