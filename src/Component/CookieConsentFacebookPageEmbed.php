<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentFacebookPageEmbed - Twig component for Facebook page embeds.
 *
 * @example
 * <twig:CookieConsentFacebookPageEmbed src="https://www.facebook.com/plugins/page.php?href=..." category="marketing" vendor="facebook" />
 *
 * @example
 * {{ component('CookieConsentFacebookPageEmbed', { src: page_url, category: 'marketing', vendor: 'facebook' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentFacebookPageEmbed',
    template: '@CookieConsent/components/CookieConsentFacebookPageEmbed.html.twig'
)]
final class CookieConsentFacebookPageEmbed
{
    public string $src;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $aspect_ratio = null;
    public ?string $placeholder = null;
}
