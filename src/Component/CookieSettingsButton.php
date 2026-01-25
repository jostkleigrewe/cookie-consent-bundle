<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieSettingsButton - Twig-Komponente fuer den Einstellungen-Button
 *
 * Symfony UX Twig component that renders a button/link for opening the
 *     cookie consent modal. Ideal for footer, privacy pages, or other places
 *     where users can change their settings.
 *
 * Works with the Stimulus controller 'cookie_consent_settings_button_controller',
 * which dispatches the 'cookie-consent:open' event.
 *
 * @example
 * {# Simple usage with default label #}
 * <twig:CookieSettingsButton />
 *
 * @example
 * {# With custom label #}
 * <twig:CookieSettingsButton label="Change cookie settings" />
 *
 * @example
 * {# With custom CSS class #}
 * <twig:CookieSettingsButton label="Cookies" class="btn btn-sm btn-outline-secondary" />
 *
 * @example
 * {# In footer #}
 * <footer>
 *     <nav>
 *         <a href="/privacy">Privacy policy</a>
 *         <twig:CookieSettingsButton label="Cookie settings" class="footer-link" />
 *     </nav>
 * </footer>
 *
 * @see templates/components/CookieSettingsButton.html.twig
 */
#[AsTwigComponent(
    name: 'CookieSettingsButton',
    template: '@CookieConsent/components/CookieSettingsButton.html.twig'
)]
final class CookieSettingsButton
{
    /**
     * The button label (translation key or direct text).
     *     Default: 'cookie_consent.settings' (will be translated).
     */
    public string $label = 'cookie_consent.settings';

    /**
     * CSS classes for the button.
     *     Default: 'cookie-consent-settings-button'.
     */
    public string $class = 'cookie-consent-settings-button';
}
