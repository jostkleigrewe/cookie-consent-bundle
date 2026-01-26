<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentTypeformEmbed - Twig component for Typeform embeds.
 *
 * @example
 * <twig:CookieConsentTypeformEmbed src="https://form.typeform.com/to/..." category="marketing" vendor="typeform" />
 *
 * @example
 * {{ component('CookieConsentTypeformEmbed', { src: typeform_url, category: 'marketing', vendor: 'typeform' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentTypeformEmbed',
    template: '@CookieConsent/components/CookieConsentTypeformEmbed.html.twig'
)]
final class CookieConsentTypeformEmbed
{
    public string $src;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $aspect_ratio = null;
    public ?string $placeholder = null;
}
