<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * CookieConsentEmbed - Twig component for generic consent-gated embeds.
 *
 * @example
 * <twig:CookieConsentEmbed src="https://example.com/embed" category="marketing" />
 *
 * @example
 * {{ component('CookieConsentEmbed', { src: 'https://example.com/embed', category: 'marketing' }) }}
 */
#[AsTwigComponent(
    name: 'CookieConsentEmbed',
    template: '@CookieConsent/components/CookieConsentEmbed.html.twig'
)]
final class CookieConsentEmbed
{
    public string $category = 'marketing';
    public ?string $vendor = null;
    public string $type = 'iframe';
    public ?string $src = null;
    public ?string $title = null;
    public ?string $allow = null;
    public ?string $aspect_ratio = null;
    public ?string $html = null;
    public ?string $script_src = null;
    public ?string $callback = null;
    public ?string $placeholder = null;
    public ?string $button_label = null;
}
