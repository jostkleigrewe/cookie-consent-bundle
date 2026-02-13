<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Config;

/**
 * UiConfig - UI-Konfiguration für das Cookie-Consent-Modal
 *
 * Immutables Wertobjekt mit allen UI-Einstellungen.
 *     Steuert Template, Variante, Theme, Position und Links.
 *
 * Immutable value object with all UI settings.
 *     Controls template, variant, theme, position, and links.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     ui:
 *         variant: tabler
 *         theme: day
 *         position: center
 *         privacy_url: /privacy
 */
final readonly class UiConfig
{
    /**
     * @param string      $template        Twig template path
     * @param string      $variant         UI variant ('plain', 'bootstrap', 'tabler')
     * @param string      $theme           Color theme ('day', 'night', 'auto')
     * @param string      $density         Density mode ('normal', 'compact')
     * @param string      $position        Modal position ('center', 'bottom', etc.)
     * @param string|null $privacyUrl      Link to privacy policy
     * @param string|null $imprintUrl      Link to imprint/legal notice
     * @param bool        $reloadOnChange  Reload page when consent changes
     */
    public function __construct(
        public string $template,
        public string $variant,
        public string $theme,
        public string $density,
        public string $position,
        public ?string $privacyUrl,
        public ?string $imprintUrl,
        public bool $reloadOnChange,
    ) {
    }

    /**
     * DE: Erstellt UiConfig aus einem Konfigurations-Array.
     * EN: Creates UiConfig from a configuration array.
     *
     * @param array{
     *     template: string,
     *     variant: string,
     *     theme: string,
     *     density: string,
     *     position: string,
     *     privacy_url: ?string,
     *     imprint_url: ?string,
     *     reload_on_change: bool
     * } $config Configuration array
     */
    public static function fromArray(array $config): self
    {
        return new self(
            template: $config['template'],
            variant: $config['variant'],
            theme: $config['theme'],
            density: $config['density'],
            position: $config['position'],
            privacyUrl: $config['privacy_url'],
            imprintUrl: $config['imprint_url'],
            reloadOnChange: $config['reload_on_change'],
        );
    }
}
