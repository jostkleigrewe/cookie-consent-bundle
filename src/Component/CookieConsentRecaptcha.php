<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentRecaptcha - Twig component for reCAPTCHA loader.
 *
 * @example
 * <twig:CookieConsentRecaptcha category="marketing" vendor="recaptcha" />
 *
 * @example
 * {{ component('CookieConsentRecaptcha', { category: 'marketing', vendor: 'recaptcha' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentRecaptcha',
    template: '@CookieConsent/components/CookieConsentRecaptcha.html.twig'
)]
final class CookieConsentRecaptcha
{
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $placeholder = null;
}
