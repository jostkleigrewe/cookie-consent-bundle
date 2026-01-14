# Arbeitsweise (DE)

Language switch: [English](how-it-works.en.md) | [Index](index.de.md)

Dieses Dokument beschreibt die interne Arbeitsweise, die Security-Integration und die Konfiguration der Consent-Erzwingung.

## Überblick

- Rendert ein Consent-Modal via `{{ cookie_consent_modal() }}` und Stimulus-Controller.
- Bietet Twig-Helper für consent-abhängiges Rendering (`cookie_consent_has`, `cookie_consent_required`, etc.).
- Speichert Entscheidungen im Cookie, per Doctrine oder kombiniert, mit Policy-Version-Check.
- Optionales Logging und Dispatch des Events `cookie_consent.changed`.
- Interne Struktur: `Consent/Service` (Logik), `Consent/Storage` (Adapter), `Consent/Policy`, `Consent/Model`, `Consent/Config`.

## Request-Flow (Consent-Erzwingung)

1. Pro Main-Request prüft `ConsentSessionSubscriber`, ob Consent erforderlich ist.
2. `ConsentRequirementResolver` entscheidet anhand von:
   - Controller-Attributen (`#[ConsentStateless]`, `#[ConsentRequired]`)
   - `stateless_paths` / `stateless_routes`
   - `protected_paths` / `protected_routes`
   - `require_consent_for_session` + Stateless-Flag der Firewall
3. Wenn Consent erforderlich und nicht vorhanden ist, setzt das Bundle `_cookie_consent_required` auf der Request.
4. Wenn die Session noch nicht gestartet wurde, wird die Session durch `MockArraySessionStorage` ersetzt, um Session-Cookies zu vermeiden.

## Security-Integration

Das Bundle fragt über `FirewallMapInterface` die aktuelle Firewall-Konfiguration ab.

- Ist die Firewall stateless, ist Consent nicht erforderlich, außer Pfad/Route/Attribut erzwingen es.
- Ist die Firewall stateful und `require_consent_for_session` ist aktiv, ist Consent erforderlich.
- Falls das Security-Bundle nicht verfügbar ist, werden Stateful-Prüfungen übersprungen.

## Bereiche ohne Consent (stateless)

Nutze diese Optionen, wenn bestimmte Seiten nie Consent benötigen:

```yaml
cookie_consent:
  enforcement:
    stateless_paths: ['/health', '/status']
    stateless_routes: ['api_ping']
```

Oder per Attribut am Controller:

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;

#[ConsentStateless]
final class HealthController { ... }
```

## Geschützte Bereiche (immer Consent)

Nutze diese Optionen, wenn bestimmte Bereiche immer Consent erfordern:

```yaml
cookie_consent:
  enforcement:
    protected_paths: ['/checkout']
    protected_routes: ['checkout_start']
```

Oder per Attribut am Controller:

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;

#[ConsentRequired]
final class CheckoutController { ... }
```

## Werden Session-Cookies ohne Consent verhindert?

Ja, solange die Session noch nicht gestartet ist. Wenn Consent erforderlich ist und fehlt, ersetzt das Bundle die Session-Storage durch `MockArraySessionStorage`. Damit setzt Symfony kein echtes Session-Cookie.

Ist die Session in der gleichen Request bereits gestartet, wird sie nicht überschrieben.

## Consent-Update-Endpoint

- Route-Name: `cookie_consent_update`
- Methode: `POST /_cookie-consent`
- CSRF: Same-Origin-CSRF-Token erforderlich (kommt aus dem Modal)

Payload:

```json
{
  "action": "accept_all|reject_optional|custom",
  "preferences": {
    "analytics": true
  },
  "csrf_token": "..."
}
```

## Storage-Verhalten

- `cookie`: Speichert Entscheidungen im Cookie `cookie_consent` (JSON mit Version und Timestamp).
- `doctrine`: Speichert Preferences in der Tabelle `cookie_consent`, identifiziert über `cookie_consent_id`.
- `both`: Liest zuerst aus dem Cookie, fällt auf Doctrine zurück, schreibt in beide.
- Weicht die Policy-Version von `policy_version` ab, gilt der Consent als fehlend.
