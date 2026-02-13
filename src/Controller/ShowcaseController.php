<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Controller;

use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Security\ConsentCsrfTokenManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * ShowcaseController - Template-Showcase fuer visuelles Testing
 *
 * Development-Controller der alle Template-Varianten auf einer Seite zeigt.
 *     Ermoeglicht schnelles visuelles Testing aller Kombinationen von:
 *     - Variants: tabler, bootstrap, plain
 *     - Themes: day, night
 *     - Densities: normal, compact
 *
 * Development controller showing all template variants on one page.
 *     Enables quick visual testing of all combinations of:
 *     - Variants: tabler, bootstrap, plain
 *     - Themes: day, night
 *     - Densities: normal, compact
 *
 * Route: GET /_cookie-consent/showcase
 *
 * Displays 12 combinations (3 x 2 x 2).
 *
 * @example
 * // Open in browser
 * https://localhost/_cookie-consent/showcase
 */
final readonly class ShowcaseController
{
    /**
     * @param Environment $twig Twig environment
     * @param ConsentPolicy $policy Policy for categories
     * @param UrlGeneratorInterface $urlGenerator URL generator
     * @param ConsentCsrfTokenManager $csrfTokenManager CSRF manager
     */
    public function __construct(
        private Environment $twig,
        private ConsentPolicy $policy,
        private UrlGeneratorInterface $urlGenerator,
        private ConsentCsrfTokenManager $csrfTokenManager,
    ) {
    }

    /**
     * Renders the showcase page with all template combinations.
     *
     * @return Response HTML response with showcase
     */
    public function __invoke(): Response
    {
        $loader = $this->twig->getLoader();
        $layoutTemplate = $loader->exists('base.html.twig')
            ? 'base.html.twig'
            : '@CookieConsent/showcase_base.html.twig';

        // Define all possible combinations.
        $variants = ['tabler', 'bootstrap', 'plain'];
        $themes = ['day', 'night'];
        $densities = ['normal', 'compact'];

        // Build combinations
        $combinations = [];
        foreach ($variants as $variant) {
            foreach ($themes as $theme) {
                foreach ($densities as $density) {
                    $combinations[] = [
                        'variant' => $variant,
                        'theme' => $theme,
                        'density' => $density,
                        'label' => sprintf('%s / %s / %s', ucfirst($variant), ucfirst($theme), ucfirst($density)),
                    ];
                }
            }
        }

        // Example data for the modal.
        $templateData = [
            'categories' => $this->policy->getCategories(),
            'preferences' => $this->buildPreferences($this->policy->getCategories()),
            'policy_version' => $this->policy->getPolicyVersion(),
            'consent_endpoint' => $this->urlGenerator->generate('cookie_consent_update'),
            'csrf_token' => $this->csrfTokenManager->getToken(ConsentCsrfTokenManager::TOKEN_ID)->getValue(),
            'consent_required' => false,
            'privacy_url' => '#privacy',
            'imprint_url' => '#imprint',
            'reload_on_change' => false,
            'google_consent_mode' => [
                'enabled' => false,
                'mapping' => [
                    'analytics_storage' => 'analytics',
                    'ad_storage' => 'marketing',
                    'ad_user_data' => 'marketing',
                    'ad_personalization' => 'marketing',
                ],
            ],
        ];

        // Render showcase template
        $content = $this->twig->render('@CookieConsent/showcase.html.twig', [
            'layout_template' => $layoutTemplate,
            'combinations' => $combinations,
            'template_data' => $templateData,
        ]);

        return new Response($content);
    }

    /**
     * @param array<string, array<string, mixed>> $categories
     * @return array<string, array{allowed: bool, vendors: array<string, bool>}>
     */
    private function buildPreferences(array $categories): array
    {
        $preferences = [];

        foreach ($categories as $name => $category) {
            $allowed = (bool) ($category['default'] ?? false);
            if (!empty($category['required'])) {
                $allowed = true;
            }

            $vendors = [];
            foreach (($category['vendors'] ?? []) as $vendorName => $vendorConfig) {
                $vendorAllowed = (bool) ($vendorConfig['default'] ?? false);
                if (!empty($vendorConfig['required'])) {
                    $vendorAllowed = true;
                }
                $vendors[$vendorName] = $vendorAllowed;
            }

            $preferences[$name] = [
                'allowed' => $allowed,
                'vendors' => $vendors,
            ];
        }

        return $preferences;
    }
}
