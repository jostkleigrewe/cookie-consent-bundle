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

Doctrine ORM is required for `doctrine` or `both`. Generate migrations in your app (bundle ships entities, not migrations):

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

If you only use DBAL, create the table manually:

```sql
CREATE TABLE cookie_consent (
  id VARCHAR(32) PRIMARY KEY,
  preferences JSON NOT NULL,
  policy_version VARCHAR(16) NOT NULL,
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
    retention_days: 180
```

### Logged Data

- Action: `accept_all`, `reject_optional`, `custom`
- Preferences map
- Policy version and decision timestamp
- Accepted/rejected categories
- Request context (IP, User-Agent, Referrer, URI)
- User ID (if authenticated)

IPs are anonymized when `anonymize_ip: true`.

### Database Audit Log

When Doctrine ORM is available, audit entries are stored in `cookie_consent_log`.
The current state is stored in `cookie_consent`.

### Retention / Pruning

Use the configured retention window or override it:
Set `retention_days` to `null` to disable pruning.

```bash
bin/console cookie-consent:prune-logs
bin/console cookie-consent:prune-logs --days=90
```

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

## Vendor Checks (Twig + Data Attributes)

### Twig helpers

```twig
{% if cookie_consent_vendor_has('marketing', 'youtube') %}
  <script src="https://www.youtube.com/iframe_api"></script>
{% endif %}
```

### Data attributes for scripts/content

```html
<script
  type="text/plain"
  data-consent-category="marketing"
  data-consent-vendor="youtube"
  data-consent-mode="hide"
  src="https://www.youtube.com/iframe_api">
</script>
```

### Embed components

All embed components accept an optional `vendor` key for vendor-level consent.

```twig
{{ component('CookieConsentYoutubeEmbed', {
  video_id: 'dQw4w9WgXcQ',
  category: 'marketing',
  vendor: 'youtube'
}) }}
```

Examples for other components:

```twig
{{ component('CookieConsentVimeoEmbed', { video_id: '12345', category: 'marketing', vendor: 'vimeo' }) }}
{{ component('CookieConsentGoogleMapsEmbed', { src: map_url, category: 'marketing', vendor: 'google_maps' }) }}
{{ component('CookieConsentSpotifyEmbed', { src: spotify_url, category: 'marketing', vendor: 'spotify' }) }}
{{ component('CookieConsentInstagramEmbed', { post_url: post_url, category: 'marketing', vendor: 'instagram' }) }}
{{ component('CookieConsentTikTokEmbed', { video_url: video_url, category: 'marketing', vendor: 'tiktok' }) }}
{{ component('CookieConsentTwitterEmbed', { tweet_url: tweet_url, category: 'marketing', vendor: 'twitter' }) }}
{{ component('CookieConsentLinkedInEmbed', { post_url: post_url, category: 'marketing', vendor: 'linkedin' }) }}
{{ component('CookieConsentPinterestEmbed', { pin_url: pin_url, category: 'marketing', vendor: 'pinterest' }) }}
{{ component('CookieConsentSoundCloudEmbed', { src: soundcloud_url, category: 'marketing', vendor: 'soundcloud' }) }}
{{ component('CookieConsentFacebookPageEmbed', { src: page_url, category: 'marketing', vendor: 'facebook' }) }}
{{ component('CookieConsentRecaptcha', { category: 'marketing', vendor: 'recaptcha' }) }}
{{ component('CookieConsentTypeformEmbed', { src: typeform_url, category: 'marketing', vendor: 'typeform' }) }}
{{ component('CookieConsentCalendlyEmbed', { src: calendly_url, category: 'marketing', vendor: 'calendly' }) }}
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
    "marketing": {
      "allowed": true,
      "vendors": {
        "google_ads": true
      }
    }
  },
  "csrf_token": "..."
}
```

### Response

```json
{
  "success": true,
  "preferences": {
    "necessary": { "allowed": true, "vendors": {} },
    "analytics": { "allowed": true, "vendors": {} },
    "marketing": { "allowed": false, "vendors": {} }
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
