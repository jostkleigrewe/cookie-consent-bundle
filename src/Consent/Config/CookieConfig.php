<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Config;

/**
 * CookieConfig - Konfiguration fuer Browser-Cookies
 *
 * DE: Immutables Wertobjekt mit allen Cookie-Einstellungen.
 *     Wird fuer das Consent-Cookie und das Identifier-Cookie verwendet.
 *
 * EN: Immutable value object with all cookie settings.
 *     Used for both consent cookie and identifier cookie.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     cookie:
 *         name: cookie_consent
 *         lifetime: 15552000   # 6 Monate in Sekunden
 *         path: /
 *         domain: null         # Aktuelle Domain
 *         secure: null         # Auto-detect HTTPS
 *         same_site: lax       # 'lax' | 'strict' | 'none'
 *         http_only: true      # Nicht per JavaScript lesbar
 */
class CookieConfig
{
    /**
     * @param string        $name       DE: Cookie-Name | EN: Cookie name
     * @param int           $lifetime   DE: Lebensdauer in Sekunden | EN: Lifetime in seconds
     * @param string        $path       DE: Cookie-Pfad | EN: Cookie path
     * @param string|null   $domain     DE: Cookie-Domain (null = aktuelle)
     *                                  EN: Cookie domain (null = current)
     * @param bool|null     $secure     DE: Nur ueber HTTPS (null = auto)
     *                                  EN: HTTPS only (null = auto)
     * @param string        $sameSite   DE: SameSite-Attribut ('lax', 'strict', 'none')
     *                                  EN: SameSite attribute ('lax', 'strict', 'none')
     * @param bool          $httpOnly   DE: HttpOnly-Flag (nicht per JS lesbar)
     *                                  EN: HttpOnly flag (not readable via JS)
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
     *     Wird vom DI-Container verwendet.
     *
     * EN: Creates CookieConfig from a configuration array.
     *     Used by the DI container.
     *
     * @param array{name: string, lifetime: int, path: string, domain: ?string, secure: ?bool, same_site: string, http_only: bool} $config
     *        DE: Konfigurationsarray | EN: Configuration array
     * @return static DE: Neue CookieConfig-Instanz | EN: New CookieConfig instance
     */
    public static function fromArray(array $config): static
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
