<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieSettingsButton - Twig-Komponente fuer den Einstellungen-Button
 *
 * DE: Symfony UX Twig-Komponente die einen Button/Link zum Oeffnen des
 *     Cookie-Consent-Modals rendert. Ideal fuer Footer, Datenschutzseiten
 *     oder andere Stellen wo Nutzer ihre Einstellungen aendern koennen.
 *
 * EN: Symfony UX Twig component that renders a button/link for opening the
 *     cookie consent modal. Ideal for footer, privacy pages, or other places
 *     where users can change their settings.
 *
 * Funktioniert mit dem Stimulus-Controller 'cookie_consent_settings_button_controller',
 * der das 'cookie-consent:open' Event dispatcht.
 *
 * @example
 * {# DE: Einfache Verwendung mit Standard-Label #}
 * {# EN: Simple usage with default label #}
 * <twig:CookieSettingsButton />
 *
 * @example
 * {# DE: Mit benutzerdefiniertem Label #}
 * {# EN: With custom label #}
 * <twig:CookieSettingsButton label="Cookie-Einstellungen aendern" />
 *
 * @example
 * {# DE: Mit benutzerdefinierter CSS-Klasse #}
 * {# EN: With custom CSS class #}
 * <twig:CookieSettingsButton label="Cookies" class="btn btn-sm btn-outline-secondary" />
 *
 * @example
 * {# DE: Im Footer #}
 * {# EN: In footer #}
 * <footer>
 *     <nav>
 *         <a href="/datenschutz">Datenschutz</a>
 *         <twig:CookieSettingsButton label="Cookie-Einstellungen" class="footer-link" />
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
     * DE: Das Button-Label (Translation-Key oder direkter Text).
     *     Standard: 'cookie_consent.settings' (wird uebersetzt).
     *
     * EN: The button label (translation key or direct text).
     *     Default: 'cookie_consent.settings' (will be translated).
     */
    public string $label = 'cookie_consent.settings';

    /**
     * DE: CSS-Klassen fuer den Button.
     *     Standard: 'cookie-consent-settings-button'.
     *
     * EN: CSS classes for the button.
     *     Default: 'cookie-consent-settings-button'.
     */
    public string $class = 'cookie-consent-settings-button';
}
