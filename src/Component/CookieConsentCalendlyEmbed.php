<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentCalendlyEmbed - Twig component for Calendly embeds.
 *
 * @example
 * <twig:CookieConsentCalendlyEmbed src="https://calendly.com/..." category="marketing" vendor="calendly" />
 *
 * @example
 * {{ component('CookieConsentCalendlyEmbed', { src: calendly_url, category: 'marketing', vendor: 'calendly' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentCalendlyEmbed',
    template: '@CookieConsent/components/CookieConsentCalendlyEmbed.html.twig'
)]
final class CookieConsentCalendlyEmbed
{
    public string $src;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $aspect_ratio = null;
    public ?string $placeholder = null;
}
