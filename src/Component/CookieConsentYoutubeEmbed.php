<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentYoutubeEmbed - Twig component for YouTube embeds.
 *
 * @example
 * <twig:CookieConsentYoutubeEmbed video_id="dQw4w9WgXcQ" category="marketing" vendor="youtube" />
 *
 * @example
 * {{ component('CookieConsentYoutubeEmbed', { video_id: 'dQw4w9WgXcQ', category: 'marketing', vendor: 'youtube' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentYoutubeEmbed',
    template: '@CookieConsent/components/CookieConsentYoutubeEmbed.html.twig'
)]
final class CookieConsentYoutubeEmbed
{
    public string $video_id;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $placeholder = null;
}
