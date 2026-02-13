<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Config;

/**
 * EnforcementConfig - Konfiguration für Consent-Durchsetzung
 *
 * Immutables Wertobjekt mit Einstellungen zur Consent-Prüfung.
 *     Steuert welche Pfade/Routen Consent benötigen oder davon ausgenommen sind.
 *
 * Immutable value object with consent enforcement settings.
 *     Controls which paths/routes require consent or are exempt.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     enforcement:
 *         require_consent_for_session: true
 *         stateless_paths: ['/api', '/health']
 *         protected_routes: ['app_checkout']
 */
final readonly class EnforcementConfig
{
    /**
     * @param bool     $requireConsentForSession Whether session requires consent
     * @param string[] $statelessPaths           Paths that don't require consent
     * @param string[] $statelessRoutes          Routes that don't require consent
     * @param string[] $protectedPaths           Paths that require specific consent
     * @param string[] $protectedRoutes          Routes that require specific consent
     */
    public function __construct(
        public bool $requireConsentForSession,
        public array $statelessPaths,
        public array $statelessRoutes,
        public array $protectedPaths,
        public array $protectedRoutes,
    ) {
    }

    /**
     * DE: Erstellt EnforcementConfig aus einem Konfigurations-Array.
     * EN: Creates EnforcementConfig from a configuration array.
     *
     * @param array{
     *     require_consent_for_session: bool,
     *     stateless_paths: string[],
     *     stateless_routes: string[],
     *     protected_paths: string[],
     *     protected_routes: string[]
     * } $config Configuration array
     */
    public static function fromArray(array $config): self
    {
        return new self(
            requireConsentForSession: $config['require_consent_for_session'],
            statelessPaths: $config['stateless_paths'],
            statelessRoutes: $config['stateless_routes'],
            protectedPaths: $config['protected_paths'],
            protectedRoutes: $config['protected_routes'],
        );
    }

    /**
     * DE: Prüft ob ein Pfad stateless ist (kein Consent nötig).
     * EN: Checks if a path is stateless (no consent required).
     */
    public function isStatelessPath(string $path): bool
    {
        foreach ($this->statelessPaths as $statelessPath) {
            if (str_starts_with($path, $statelessPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * DE: Prüft ob eine Route stateless ist (kein Consent nötig).
     * EN: Checks if a route is stateless (no consent required).
     */
    public function isStatelessRoute(string $routeName): bool
    {
        return \in_array($routeName, $this->statelessRoutes, true);
    }
}
