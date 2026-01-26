<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentTikTokEmbed - Twig component for TikTok embeds.
 *
 * @example
 * <twig:CookieConsentTikTokEmbed video_url="https://www.tiktok.com/@user/video/..." category="marketing" vendor="tiktok" />
 *
 * @example
 * {{ component('CookieConsentTikTokEmbed', { video_url: video_url, category: 'marketing', vendor: 'tiktok' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentTikTokEmbed',
    template: '@CookieConsent/components/CookieConsentTikTokEmbed.html.twig'
)]
final class CookieConsentTikTokEmbed
{
    public string $video_url;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $placeholder = null;
}
