# Advanced Topics

[Deutsch](advanced.de.md) | [Back to README](../README.md)

## Storage Backends

### Cookie Storage (default)

No setup required. Consent is stored in an HttpOnly cookie.

```yaml
cookie_consent:
  storage: cookie
  cookie:
    name: cookie_consent
    lifetime: 15552000  # 6 months
```

### Doctrine Storage

Store consent in a database for server-side access and audit trails.

```yaml
cookie_consent:
  storage: doctrine
```

Create the table:

```sql
CREATE TABLE cookie_consent (
  id VARCHAR(64) PRIMARY KEY,
  preferences JSON NOT NULL,
  policy_version VARCHAR(32) NOT NULL,
  decided_at DATETIME NULL
);
```

The user identifier is stored in `cookie_consent_id` cookie.

### Combined Storage

Write to both cookie and database; read prefers cookie, falls back to database.

```yaml
cookie_consent:
  storage: both
```

---

## Session Enforcement

When consent is required but missing, the bundle prevents session cookies.

### How It Works

1. `ConsentSessionSubscriber` checks each request
2. `ConsentRequirementResolver` evaluates rules (paths, routes, attributes)
3. If consent required and missing: session storage is replaced with `MockArraySessionStorage`
4. No session cookie is written

### Configuration

```yaml
cookie_consent:
  enforcement:
    require_consent_for_session: true
    stateless_paths: ['/health', '/api']      # Never require consent
    stateless_routes: ['api_status']
    protected_paths: ['/checkout']            # Always require consent
    protected_routes: ['checkout_payment']
```

### Controller Attributes

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;

#[ConsentStateless]
class HealthController
{
    // No consent required
}

#[ConsentRequired]
class CheckoutController
{
    // Always requires consent
}

class ProductController
{
    #[ConsentStateless]
    public function api(): Response
    {
        // No consent for this action
    }
}
```

Attributes override path/route configuration.

### Security Integration

The bundle checks Symfony Security's firewall configuration:

- **Stateless firewall**: Consent not required (unless forced by path/route/attribute)
- **Stateful firewall** + `require_consent_for_session: true`: Consent required
- **No Security Bundle**: Stateful checks skipped

---

## Audit Logging

Log consent actions for GDPR compliance.

```yaml
cookie_consent:
  logging:
    enabled: true
    level: info
    anonymize_ip: true
```

### Logged Data

- Action: `accept_all`, `reject_optional`, `custom`
- Preferences map
- Policy version and decision timestamp
- Accepted/rejected categories
- Request context (IP, User-Agent, Referrer, URI)

IPs are anonymized when `anonymize_ip: true`.

---

## Events

### Server-side Event

The bundle dispatches `ConsentChangedEvent` when preferences are saved:

```php
use Jostkleigrewe\CookieConsentBundle\Event\ConsentChangedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class ConsentListener
{
    public function __invoke(ConsentChangedEvent $event): void
    {
        $preferences = $event->getPreferences();
        $action = $event->getAction();
        // Sync with external systems, update user profile, etc.
    }
}
```

### Browser Events

```javascript
// Fires after preferences are saved
document.addEventListener('cookie-consent:changed', (e) => {
  console.log('Preferences:', e.detail.preferences);
});

// Open the modal programmatically
document.dispatchEvent(new CustomEvent('cookie-consent:open'));
```

---

## API Endpoint

### Route

- **Name:** `cookie_consent_update`
- **Method:** `POST /_cookie-consent`
- **CSRF:** Required (provided by modal)

### Request Payload

```json
{
  "action": "accept_all",
  "preferences": {},
  "csrf_token": "..."
}
```

Actions: `accept_all`, `reject_optional`, `custom`

For `custom`, include preferences:

```json
{
  "action": "custom",
  "preferences": {
    "analytics": true,
    "marketing": false
  },
  "csrf_token": "..."
}
```

### Response

```json
{
  "success": true,
  "preferences": {
    "necessary": true,
    "analytics": true,
    "marketing": false
  }
}
```

---

## Architecture Overview

```
ConsentManager (orchestrator)
├── ConsentStorageInterface (strategy pattern)
│   ├── CookieConsentStorageAdapter
│   ├── DoctrineConsentStorageAdapter
│   └── CombinedConsentStorageAdapter
├── ConsentPolicy (categories, versioning)
├── ConsentLogger (audit logging)
└── Events (ConsentChangedEvent)

Request Flow:
1. ConsentSessionSubscriber (priority 20)
2. ConsentRequirementResolver
3. ControllerAttributeResolver
4. → MockArraySessionStorage (if consent missing)
```

---

## Policy Versioning

Increment `policy_version` when:

- Adding or removing categories
- Changing `required` or `default` values
- Renaming categories

```yaml
cookie_consent:
  policy_version: '2'  # Was '1'
```

Users with old consent will be prompted again.
