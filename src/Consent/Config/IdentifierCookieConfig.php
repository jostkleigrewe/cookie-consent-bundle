<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Config;

/**
 * IdentifierCookieConfig - Konfiguration fuer das Identifier-Cookie
 *
 * Spezialisierte Cookie-Konfiguration fuer das Identifier-Cookie.
 *     Das Identifier-Cookie speichert die Nutzer-ID fuer das Doctrine-Backend.
 *     Erbt alle Einstellungen von CookieConfig.
 *
 * Specialized cookie configuration for the identifier cookie.
 *     The identifier cookie stores the user ID for the Doctrine backend.
 *     Inherits all settings from CookieConfig.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     identifier_cookie:
 *         name: cookie_consent_id
 *         lifetime: 31536000  # 1 Jahr
 *         http_only: true     # Aus Sicherheitsgruenden
 *
 * @see CookieConfig
 * @see ConsentIdProvider
 */
final class IdentifierCookieConfig extends CookieConfig
{
}
