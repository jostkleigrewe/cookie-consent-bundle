# Session-Erzwingung (DE)

Language switch: [English](enforcement.en.md) | [Index](index.de.md)

Wenn Consent erforderlich ist und fehlt, verhindert das Bundle stateful Sessions für die Anfrage.

## Konfiguration

```yaml
cookie_consent:
  enforcement:
    require_consent_for_session: true
    stateless_paths: ['/health']
    protected_paths: ['/checkout']
    stateless_routes: []
    protected_routes: []
```

- `stateless_paths` und `stateless_routes` sind immer ohne Consent erlaubt.
- `protected_paths` und `protected_routes` erfordern immer Consent.

## Attribute

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;

#[ConsentRequired]
class CheckoutController { ... }

#[ConsentStateless]
class HealthController { ... }
```

Attribute können auf Controller oder Actions gesetzt werden und überschreiben Pfad/Route-Regeln.

## Symfony-Event

Das Bundle dispatcht `cookie_consent.changed`, wenn Preferences gespeichert werden. Nutze das für serverseitige Integrationen.
