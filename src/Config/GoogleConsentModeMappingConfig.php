<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Config;

/**
 * GoogleConsentModeMappingConfig - Mapping für Google Consent Mode
 *
 * Immutables Wertobjekt für das Mapping von Google Consent Types auf Bundle-Kategorien.
 *
 * Immutable value object for mapping Google consent types to bundle categories.
 *
 * @see GoogleConsentModeConfig
 * @see https://developers.google.com/tag-platform/security/guides/consent
 */
final readonly class GoogleConsentModeMappingConfig implements \JsonSerializable
{
    /**
     * @param string $analyticsStorage   Category for analytics_storage
     * @param string $adStorage          Category for ad_storage
     * @param string $adUserData         Category for ad_user_data
     * @param string $adPersonalization  Category for ad_personalization
     */
    public function __construct(
        public string $analyticsStorage,
        public string $adStorage,
        public string $adUserData,
        public string $adPersonalization,
    ) {
    }

    /**
     * DE: Erstellt GoogleConsentModeMappingConfig aus einem Konfigurations-Array.
     * EN: Creates GoogleConsentModeMappingConfig from a configuration array.
     *
     * @param array{
     *     analytics_storage: string,
     *     ad_storage: string,
     *     ad_user_data: string,
     *     ad_personalization: string
     * } $config Configuration array
     */
    public static function fromArray(array $config): self
    {
        return new self(
            analyticsStorage: $config['analytics_storage'],
            adStorage: $config['ad_storage'],
            adUserData: $config['ad_user_data'],
            adPersonalization: $config['ad_personalization'],
        );
    }

    /**
     * DE: Gibt das Mapping als Array zurück (für gtag-Aufrufe).
     * EN: Returns the mapping as array (for gtag calls).
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'analytics_storage' => $this->analyticsStorage,
            'ad_storage' => $this->adStorage,
            'ad_user_data' => $this->adUserData,
            'ad_personalization' => $this->adPersonalization,
        ];
    }

    /**
     * DE: JSON-Serialisierung für Twig json_encode Filter.
     * EN: JSON serialization for Twig json_encode filter.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
