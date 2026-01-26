<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentSoundCloudEmbed - Twig component for SoundCloud embeds.
 *
 * @example
 * <twig:CookieConsentSoundCloudEmbed src="https://w.soundcloud.com/player/?url=..." category="marketing" vendor="soundcloud" />
 *
 * @example
 * {{ component('CookieConsentSoundCloudEmbed', { src: soundcloud_url, category: 'marketing', vendor: 'soundcloud' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentSoundCloudEmbed',
    template: '@CookieConsent/components/CookieConsentSoundCloudEmbed.html.twig'
)]
final class CookieConsentSoundCloudEmbed
{
    public string $src;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $aspect_ratio = null;
    public ?string $placeholder = null;
}
