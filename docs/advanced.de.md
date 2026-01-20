# Erweiterte Themen

[English](advanced.md) | [Zurück zur README](../README.de.md)

## Speicher-Backends

### Cookie-Speicherung (Standard)

Kein Setup erforderlich. Einwilligung wird in einem HttpOnly-Cookie gespeichert.

```yaml
cookie_consent:
  storage: cookie
  cookie:
    name: cookie_consent
    lifetime: 15552000  # 6 Monate
```

### Doctrine-Speicherung

Einwilligung in einer Datenbank speichern für serverseitigen Zugriff und Audit-Trails.

```yaml
cookie_consent:
  storage: doctrine
```

Tabelle erstellen:

```sql
CREATE TABLE cookie_consent (
  id VARCHAR(64) PRIMARY KEY,
  preferences JSON NOT NULL,
  policy_version VARCHAR(32) NOT NULL,
  decided_at DATETIME NULL
);
```

Die Nutzer-ID wird im `cookie_consent_id`-Cookie gespeichert.

### Kombinierte Speicherung

Schreibt in Cookie und Datenbank; liest bevorzugt aus Cookie, Fallback auf Datenbank.

```yaml
cookie_consent:
  storage: both
```

---

## Session-Erzwingung

Wenn Einwilligung erforderlich, aber nicht vorhanden ist, verhindert das Bundle Session-Cookies.

### Funktionsweise

1. `ConsentSessionSubscriber` prüft jeden Request
2. `ConsentRequirementResolver` wertet Regeln aus (Pfade, Routen, Attribute)
3. Falls Einwilligung erforderlich und fehlend: Session-Storage wird durch `MockArraySessionStorage` ersetzt
4. Kein Session-Cookie wird geschrieben

### Konfiguration

```yaml
cookie_consent:
  enforcement:
    require_consent_for_session: true
    stateless_paths: ['/health', '/api']      # Nie Einwilligung erforderlich
    stateless_routes: ['api_status']
    protected_paths: ['/checkout']            # Immer Einwilligung erforderlich
    protected_routes: ['checkout_payment']
```

### Controller-Attribute

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;

#[ConsentStateless]
class HealthController
{
    // Keine Einwilligung erforderlich
}

#[ConsentRequired]
class CheckoutController
{
    // Immer Einwilligung erforderlich
}

class ProductController
{
    #[ConsentStateless]
    public function api(): Response
    {
        // Keine Einwilligung für diese Action
    }
}
```

Attribute überschreiben Pfad-/Routen-Konfiguration.

### Security-Integration

Das Bundle prüft Symfonys Security-Firewall-Konfiguration:

- **Stateless Firewall**: Keine Einwilligung erforderlich (außer durch Pfad/Route/Attribut erzwungen)
- **Stateful Firewall** + `require_consent_for_session: true`: Einwilligung erforderlich
- **Kein Security Bundle**: Stateful-Prüfungen werden übersprungen

---

## Audit-Logging

Consent-Aktionen für DSGVO-Compliance protokollieren.

```yaml
cookie_consent:
  logging:
    enabled: true
    level: info
    anonymize_ip: true
```

### Protokollierte Daten

- Aktion: `accept_all`, `reject_optional`, `custom`
- Präferenzen-Map
- Richtlinienversion und Entscheidungszeitstempel
- Akzeptierte/abgelehnte Kategorien
- Request-Kontext (IP, User-Agent, Referrer, URI)

IPs werden anonymisiert, wenn `anonymize_ip: true`.

---

## Events

### Server-seitiges Event

Das Bundle löst `ConsentChangedEvent` aus, wenn Präferenzen gespeichert werden:

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
        // Mit externen Systemen synchronisieren, Nutzerprofil aktualisieren, etc.
    }
}
```

### Browser-Events

```javascript
// Wird nach dem Speichern der Präferenzen ausgelöst
document.addEventListener('cookie-consent:changed', (e) => {
  console.log('Präferenzen:', e.detail.preferences);
});

// Modal programmatisch öffnen
document.dispatchEvent(new CustomEvent('cookie-consent:open'));
```

---

## API-Endpunkt

### Route

- **Name:** `cookie_consent_update`
- **Methode:** `POST /_cookie-consent`
- **CSRF:** Erforderlich (vom Modal bereitgestellt)

### Request-Payload

```json
{
  "action": "accept_all",
  "preferences": {},
  "csrf_token": "..."
}
```

Aktionen: `accept_all`, `reject_optional`, `custom`

Für `custom` Präferenzen mitgeben:

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

## Architektur-Überblick

```
ConsentManager (Orchestrator)
├── ConsentStorageInterface (Strategy Pattern)
│   ├── CookieConsentStorageAdapter
│   ├── DoctrineConsentStorageAdapter
│   └── CombinedConsentStorageAdapter
├── ConsentPolicy (Kategorien, Versionierung)
├── ConsentLogger (Audit-Logging)
└── Events (ConsentChangedEvent)

Request-Flow:
1. ConsentSessionSubscriber (Priorität 20)
2. ConsentRequirementResolver
3. ControllerAttributeResolver
4. → MockArraySessionStorage (falls Consent fehlt)
```

---

## Richtlinien-Versionierung

`policy_version` erhöhen bei:

- Hinzufügen oder Entfernen von Kategorien
- Ändern von `required` oder `default` Werten
- Umbenennen von Kategorien

```yaml
cookie_consent:
  policy_version: '2'  # War '1'
```

Nutzer mit alter Einwilligung werden erneut aufgefordert.
