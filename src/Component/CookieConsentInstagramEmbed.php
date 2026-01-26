<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentInstagramEmbed - Twig component for Instagram embeds.
 *
 * @example
 * <twig:CookieConsentInstagramEmbed post_url="https://www.instagram.com/p/..." category="marketing" vendor="instagram" />
 *
 * @example
 * {{ component('CookieConsentInstagramEmbed', { post_url: post_url, category: 'marketing', vendor: 'instagram' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentInstagramEmbed',
    template: '@CookieConsent/components/CookieConsentInstagramEmbed.html.twig'
)]
final class CookieConsentInstagramEmbed
{
    public string $post_url;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $placeholder = null;
}
