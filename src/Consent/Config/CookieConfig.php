<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Config;

/**
 * CookieConfig - Konfiguration fuer Browser-Cookies
 *
 * Immutables Wertobjekt mit allen Cookie-Einstellungen.
 *     Wird fuer das Consent-Cookie und das Identifier-Cookie verwendet.
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
 */
/**
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
     * Creates CookieConfig from a configuration array.
     *     Used by the DI container.
     *
     * @param array{name: string, lifetime: int, path: string, domain: ?string, secure: ?bool, same_site: string, http_only: bool} $config
     * Configuration array
     * @return static New CookieConfig instance
     */
    final public static function fromArray(array $config): static
    {
        return new static(
            $config['name'],
            $config['lifetime'],
            $config['path'],
            $config['domain'],
            $config['secure'],
            $config['same_site'],
            $config['http_only']
        );
    }
}
