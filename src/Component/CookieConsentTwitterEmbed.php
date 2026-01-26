<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentTwitterEmbed - Twig component for Twitter/X embeds.
 *
 * @example
 * <twig:CookieConsentTwitterEmbed tweet_url="https://twitter.com/user/status/..." category="marketing" vendor="twitter" />
 *
 * @example
 * {{ component('CookieConsentTwitterEmbed', { tweet_url: tweet_url, category: 'marketing', vendor: 'twitter' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentTwitterEmbed',
    template: '@CookieConsent/components/CookieConsentTwitterEmbed.html.twig'
)]
final class CookieConsentTwitterEmbed
{
    public string $tweet_url;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $placeholder = null;
}
