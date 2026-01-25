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
 * DE: Development-Controller der alle Template-Varianten auf einer Seite zeigt.
 *     Ermoeglicht schnelles visuelles Testing aller Kombinationen von:
 *     - Variants: tabler, bootstrap, plain
 *     - Themes: day, night
 *     - Densities: normal, compact
 *
 * EN: Development controller showing all template variants on one page.
 *     Enables quick visual testing of all combinations of:
 *     - Variants: tabler, bootstrap, plain
 *     - Themes: day, night
 *     - Densities: normal, compact
 *
 * Route: GET /_cookie-consent/showcase
 *
 * Insgesamt 12 Kombinationen (3 x 2 x 2) werden angezeigt.
 *
 * @example
 * // DE: Im Browser aufrufen
 * // EN: Open in browser
 * https://localhost/_cookie-consent/showcase
 */
final readonly class ShowcaseController
{
    /**
     * @param Environment $twig DE: Twig-Environment | EN: Twig environment
     * @param ConsentPolicy $policy DE: Policy fuer Kategorien | EN: Policy for categories
     * @param UrlGeneratorInterface $urlGenerator DE: URL-Generator | EN: URL generator
     * @param ConsentCsrfTokenManager $csrfTokenManager DE: CSRF-Manager | EN: CSRF manager
     */
    public function __construct(
        private Environment $twig,
        private ConsentPolicy $policy,
        private UrlGeneratorInterface $urlGenerator,
        private ConsentCsrfTokenManager $csrfTokenManager,
    ) {
    }

    /**
     * DE: Rendert die Showcase-Seite mit allen Template-Kombinationen.
     *
     * EN: Renders the showcase page with all template combinations.
     *
     * @return Response DE: HTML-Response mit Showcase | EN: HTML response with showcase
     */
    public function __invoke(): Response
    {
        // DE: Alle moeglichen Kombinationen definieren.
        // EN: Define all possible combinations.
        $variants = ['tabler', 'bootstrap', 'plain'];
        $themes = ['day', 'night'];
        $densities = ['normal', 'compact'];

        // DE: Kombinationen aufbauen
        // EN: Build combinations
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

        // DE: Beispiel-Daten fuer das Modal.
        // EN: Example data for the modal.
        $templateData = [
            'categories' => $this->policy->getCategories(),
            'preferences' => array_map(
                fn(array $cat) => $cat['default'],
                $this->policy->getCategories()
            ),
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

        // DE: Showcase-Template rendern
        // EN: Render showcase template
        $content = $this->twig->render('@CookieConsent/showcase.html.twig', [
            'combinations' => $combinations,
            'template_data' => $templateData,
        ]);

        return new Response($content);
    }
}
