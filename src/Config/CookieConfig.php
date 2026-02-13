<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Config;

/**
 * CookieConfig - Konfiguration für Browser-Cookies
 *
 * Immutables Wertobjekt mit allen Cookie-Einstellungen.
 *     Wird für das Consent-Cookie und das Identifier-Cookie verwendet.
 *
 * Immutable value object with all cookie settings.
 *     Used for both consent cookie and identifier cookie.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     cookie:
 *         name: cookie_consent
 *         lifetime: 15552000   # 6 months in seconds
 *         path: /
 *         domain: null         # Current domain
 *         secure: null         # Auto-detect HTTPS
 *         same_site: lax       # 'lax' | 'strict' | 'none'
 *         http_only: true      # Not readable via JavaScript
 *
 * @phpstan-consistent-constructor
 */
class CookieConfig
{
    /**
     * @param string        $name Cookie name
     * @param int           $lifetime Lifetime in seconds
     * @param string        $path Cookie path
     * @param string|null   $domain Cookie domain (null = current)
     * @param bool|null     $secure HTTPS only (null = auto)
     * @param string        $sameSite SameSite attribute ('lax', 'strict', 'none')
     * @param bool          $httpOnly HttpOnly flag (not readable via JS)
     */
    public function __construct(
        public readonly string  $name,
        public readonly int     $lifetime,
        public readonly string  $path,
        public readonly ?string $domain,
        public readonly ?bool   $secure,
        public readonly string  $sameSite,
        public readonly bool    $httpOnly,
    ) {
    }

    /**
     * DE: Erstellt CookieConfig aus einem Konfigurations-Array.
     *     Das Array muss alle Keys enthalten (wird durch Symfony Config Definition garantiert).
     * EN: Creates CookieConfig from a configuration array.
     *     The array must contain all keys (guaranteed by Symfony Config Definition).
     *
     * @param array{name: string, lifetime: int, path: string, domain: ?string, secure: ?bool, same_site: string, http_only: bool} $config
     * @return static New CookieConfig instance
     */
    final public static function fromArray(array $config): static
    {
        return new static(
            name: $config['name'],
            lifetime: $config['lifetime'],
            path: $config['path'],
            domain: $config['domain'],
            secure: $config['secure'],
            sameSite: $config['same_site'],
            httpOnly: $config['http_only'],
        );
    }
}
