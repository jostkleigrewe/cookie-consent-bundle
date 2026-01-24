<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Controller;

use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Security\ConsentCsrfTokenManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * DE: Controller fuer die Template-Showcase-Seite.
 * EN: Controller for the template showcase page.
 *
 * Zeigt alle Varianten-Kombinationen auf einer Seite fuer visuelles Testing.
 */
final readonly class ShowcaseController
{
    public function __construct(
        private Environment $twig,
        private ConsentPolicy $policy,
        private UrlGeneratorInterface $urlGenerator,
        private ConsentCsrfTokenManager $csrfTokenManager,
    ) {
    }

    public function __invoke(): Response
    {
        // DE: Alle moeglichen Kombinationen.
        // EN: All possible combinations.
        $variants = ['tabler', 'bootstrap', 'plain'];
        $themes = ['day', 'night'];
        $densities = ['normal', 'compact'];

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
                fn(array $cat) => $cat['default'] ?? false,
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

        $content = $this->twig->render('@CookieConsent/showcase.html.twig', [
            'combinations' => $combinations,
            'template_data' => $templateData,
        ]);

        return new Response($content);
    }
}
