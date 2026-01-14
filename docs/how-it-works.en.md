# How It Works (EN)

Language switch: [Deutsch](how-it-works.de.md) | [Index](index.en.md)

This document explains how the bundle works internally, how it integrates with Symfony Security, and how to configure consent enforcement.

## Overview

- Renders a consent modal with `{{ cookie_consent_modal() }}` and a Stimulus controller.
- Provides Twig helpers for consent-aware rendering (`cookie_consent_has`, `cookie_consent_required`, etc.).
- Stores decisions in a cookie, Doctrine, or both, with a policy version check.
- Optionally logs consent actions and dispatches a `cookie_consent.changed` event.
- Internal structure: `Consent/Service` (business logic), `Consent/Storage` (adapters), `Consent/Policy`, `Consent/Model`, `Consent/Config`.

## Request flow (consent enforcement)

1. On each main request, `ConsentSessionSubscriber` checks if consent is required.
2. `ConsentRequirementResolver` decides based on:
   - Controller attributes (`#[ConsentStateless]`, `#[ConsentRequired]`)
   - `stateless_paths` / `stateless_routes`
   - `protected_paths` / `protected_routes`
   - `require_consent_for_session` + the firewall stateless flag
3. If consent is required and missing, the bundle marks the request with `_cookie_consent_required`.
4. If the session is not started yet, the session is replaced with `MockArraySessionStorage` to avoid issuing a real session cookie.

## Security integration

The bundle asks Symfony Security's `FirewallMapInterface` whether the matched firewall is stateless.

- If the firewall is stateless, consent is not required unless a path/route or attribute forces it.
- If the firewall is stateful and `require_consent_for_session` is true, consent is required.
- If the Security bundle is not available, stateful checks are skipped (no firewall info).

## Configuring no-consent areas (stateless zones)

Use these options when certain pages must never require consent:

```yaml
cookie_consent:
  enforcement:
    stateless_paths: ['/health', '/status']
    stateless_routes: ['api_ping']
```

Or opt out explicitly in controllers:

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentStateless;

#[ConsentStateless]
final class HealthController { ... }
```

## Configuring protected areas (always require consent)

Use this when certain routes always need consent:

```yaml
cookie_consent:
  enforcement:
    protected_paths: ['/checkout']
    protected_routes: ['checkout_start']
```

Or enforce via attributes:

```php
use Jostkleigrewe\CookieConsentBundle\Attribute\ConsentRequired;

#[ConsentRequired]
final class CheckoutController { ... }
```

## Are session cookies prevented without consent?

Yes, as long as the session is not already started. When consent is required and missing, the bundle swaps the session storage to `MockArraySessionStorage` for the request. This prevents Symfony from writing a real session cookie.

If a session has already been started earlier in the request, it is not overridden.

## Consent update endpoint

- Route name: `cookie_consent_update`
- Method: `POST /_cookie-consent`
- CSRF: same-origin CSRF token required (provided by the modal)

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

## Storage behavior

- `cookie`: stores decisions in `cookie_consent` (JSON payload with version and timestamp).
- `doctrine`: stores preferences in `cookie_consent` table, identified by `cookie_consent_id`.
- `both`: reads from cookie first, falls back to Doctrine, and writes to both.
- If the stored policy version differs from `policy_version`, consent is considered missing.
