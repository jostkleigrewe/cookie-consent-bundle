<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentSpotifyEmbed - Twig component for Spotify embeds.
 *
 * @example
 * <twig:CookieConsentSpotifyEmbed src="https://open.spotify.com/embed/..." category="marketing" vendor="spotify" />
 *
 * @example
 * {{ component('CookieConsentSpotifyEmbed', { src: spotify_url, category: 'marketing', vendor: 'spotify' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentSpotifyEmbed',
    template: '@CookieConsent/components/CookieConsentSpotifyEmbed.html.twig'
)]
final class CookieConsentSpotifyEmbed
{
    public string $src;
    public string $category = 'marketing';
    public ?string $vendor = null;
    public ?string $title = null;
    public ?string $aspect_ratio = null;
    public ?string $placeholder = null;
}
