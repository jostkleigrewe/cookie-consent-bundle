# Session Enforcement (EN)

Language switch: [Deutsch](enforcement.de.md) | [Index](index.en.md)

When consent is required and missing, the bundle prevents stateful sessions for the request.

## Configuration

```yaml
cookie_consent:
  enforcement:
    require_consent_for_session: true
    stateless_paths: ['/health']
    protected_paths: ['/checkout']
    stateless_routes: []
    protected_routes: []
```

- `stateless_paths` and `stateless_routes` are always allowed without consent.
- `protected_paths` and `protected_routes` always require consent.

## Attributes

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;

#[ConsentRequired]
class CheckoutController { ... }

#[ConsentStateless]
class HealthController { ... }
```

Attributes can be placed on controllers or actions and override path/route rules.

## Symfony event

The bundle dispatches `cookie_consent.changed` when preferences are saved. Use it for server-side integrations.
