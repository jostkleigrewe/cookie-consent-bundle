<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentLinkedInEmbed - Twig component for LinkedIn embeds.
 *
 * @example
 * <twig:CookieConsentLinkedInEmbed post_url="https://www.linkedin.com/posts/..." category="marketing" vendor="linkedin" />
 *
 * @example
 * {{ component('CookieConsentLinkedInEmbed', { post_url: post_url, category: 'marketing', vendor: 'linkedin' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentLinkedInEmbed',
    template: '@CookieConsent/components/CookieConsentLinkedInEmbed.html.twig'
)]
final class CookieConsentLinkedInEmbed
{
    public string $post_url;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $placeholder = null;
}
