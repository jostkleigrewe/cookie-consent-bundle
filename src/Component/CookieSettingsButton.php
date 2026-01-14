<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * DE: Twig-Komponente fuer den "Cookie-Einstellungen"-Button (oeffnet das Modal).
 * EN: Twig component for the "cookie settings" button (opens the modal).
 */
#[AsTwigComponent(
    name: 'CookieSettingsButton',
    template: '@CookieConsent/components/CookieSettingsButton.html.twig'
)]
final class CookieSettingsButton
{
    public string $label = 'cookie_consent.settings';
    public string $class = 'cookie-consent-settings-button';
}
