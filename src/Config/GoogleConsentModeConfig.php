<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Config;

/**
 * GoogleConsentModeConfig - Google Consent Mode v2 Konfiguration
 *
 * Immutables Wertobjekt für Google Consent Mode Integration.
 *     Wenn aktiviert, wird gtag('consent', 'update', ...) automatisch aufgerufen.
 *
 * Immutable value object for Google Consent Mode integration.
 *     When enabled, gtag('consent', 'update', ...) is called automatically.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     google_consent_mode:
 *         enabled: true
 *         mapping:
 *             analytics_storage: analytics
 *             ad_storage: marketing
 *
 * @see https://developers.google.com/tag-platform/security/guides/consent
 */
final readonly class GoogleConsentModeConfig
{
    /**
     * @param bool                            $enabled Whether Google Consent Mode is enabled
     * @param GoogleConsentModeMappingConfig $mapping Mapping from Google consent types to categories
     */
    public function __construct(
        public bool $enabled,
        public GoogleConsentModeMappingConfig $mapping,
    ) {
    }

    /**
     * DE: Erstellt GoogleConsentModeConfig aus einem Konfigurations-Array.
     * EN: Creates GoogleConsentModeConfig from a configuration array.
     *
     * @param array{
     *     enabled: bool,
     *     mapping: array{
     *         analytics_storage: string,
     *         ad_storage: string,
     *         ad_user_data: string,
     *         ad_personalization: string
     *     }
     * } $config Configuration array
     */
    public static function fromArray(array $config): self
    {
        return new self(
            enabled: $config['enabled'],
            mapping: GoogleConsentModeMappingConfig::fromArray($config['mapping']),
        );
    }
}
