<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentVimeoEmbed - Twig component for Vimeo embeds.
 *
 * @example
 * <twig:CookieConsentVimeoEmbed video_id="12345" category="marketing" vendor="vimeo" />
 *
 * @example
 * {{ component('CookieConsentVimeoEmbed', { video_id: '12345', category: 'marketing', vendor: 'vimeo' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentVimeoEmbed',
    template: '@CookieConsent/components/CookieConsentVimeoEmbed.html.twig'
)]
final class CookieConsentVimeoEmbed
{
    public string $video_id;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $placeholder = null;
}
