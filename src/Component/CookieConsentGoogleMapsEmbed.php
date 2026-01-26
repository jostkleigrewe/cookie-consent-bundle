<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentGoogleMapsEmbed - Twig component for Google Maps embeds.
 *
 * @example
 * <twig:CookieConsentGoogleMapsEmbed src="https://www.google.com/maps/embed?pb=..." category="marketing" vendor="google_maps" />
 *
 * @example
 * {{ component('CookieConsentGoogleMapsEmbed', { src: map_url, category: 'marketing', vendor: 'google_maps' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentGoogleMapsEmbed',
    template: '@CookieConsent/components/CookieConsentGoogleMapsEmbed.html.twig'
)]
final class CookieConsentGoogleMapsEmbed
{
    public string $src;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $aspect_ratio = null;
    public ?string $placeholder = null;
}
