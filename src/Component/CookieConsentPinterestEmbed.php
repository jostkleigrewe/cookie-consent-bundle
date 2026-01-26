<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentPinterestEmbed - Twig component for Pinterest embeds.
 *
 * @example
 * <twig:CookieConsentPinterestEmbed pin_url="https://www.pinterest.com/pin/..." category="marketing" vendor="pinterest" />
 *
 * @example
 * {{ component('CookieConsentPinterestEmbed', { pin_url: pin_url, category: 'marketing', vendor: 'pinterest' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentPinterestEmbed',
    template: '@CookieConsent/components/CookieConsentPinterestEmbed.html.twig'
)]
final class CookieConsentPinterestEmbed
{
    public string $pin_url;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $placeholder = null;
}
