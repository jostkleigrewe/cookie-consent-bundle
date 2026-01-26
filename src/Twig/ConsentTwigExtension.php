<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Twig;

use Jostkleigrewe\CookieConsentBundle\Consent\Service\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\EventSubscriber\ConsentSessionSubscriber;
use Jostkleigrewe\CookieConsentBundle\Security\ConsentCsrfTokenManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * ConsentTwigExtension - Twig-Funktionen fuer Cookie-Consent
 *
 * Stellt Twig-Funktionen fuer Consent-Abfragen und Modal-Rendering bereit.
 *
 * Provides Twig functions for consent queries and modal rendering.
 *     Enables easy template integration without controller logic.
 *
 * Available functions:
 * - cookie_consent_modal()        - Renders the consent modal
 * - cookie_consent_has(category)  - Checks whether a category is allowed
 * - cookie_consent_preferences()  - Returns normalized preferences
 * - cookie_consent_preferences_raw() - Returns raw preferences
 * - cookie_consent_has_decision() - Checks whether a decision exists
 * - cookie_consent_decided_at()   - Returns the decision timestamp
 * - cookie_consent_required()     - Checks whether the modal must be shown
 * - cookie_consent_categories()   - Returns all categories
 *
 * @example
 * {# Include modal in base.html.twig #}
 * {{ cookie_consent_modal() }}
 *
 * @example
 * {# Conditionally load analytics script #}
 * {% if cookie_consent_has('analytics') %}
 *     <script src="analytics.js"></script>
 * {% endif %}
 *
 * @example
 * {# Preferences as JSON for JavaScript #}
 * <script>
 *     window.cookiePreferences = {{ cookie_consent_preferences()|json_encode|raw }};
 * </script>
 */
final class ConsentTwigExtension extends AbstractExtension
{
    /**
     * @param Environment $twig Twig environment for rendering
     * @param ConsentManager $consentManager Consent service
     * @param ConsentPolicy $policy Policy configuration
     * @param RequestStack $requestStack Request stack
     * @param UrlGeneratorInterface $urlGenerator URL generator
     * @param ConsentCsrfTokenManager $csrfTokenManager CSRF manager
     * @param array{
     *     template: string,
     *     variant: string,
     *     theme: string,
     *     density: string,
     *     position: string,
     *     privacy_url: ?string,
     *     imprint_url: ?string,
     *     reload_on_change: bool
     * } $ui UI configuration
     * @param array{enabled: bool, mapping: array<string, string>} $googleConsentMode
     * Google Consent Mode configuration
     */
    public function __construct(
        private readonly Environment $twig,
        private readonly ConsentManager $consentManager,
        private readonly ConsentPolicy $policy,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ConsentCsrfTokenManager $csrfTokenManager,
        private readonly array $ui,
        private readonly array $googleConsentMode,
    ) {
    }

    /**
     * Registers all Twig functions.
     *
     * @return TwigFunction[] List of Twig functions
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('cookie_consent_modal', [$this, 'renderModal'], ['is_safe' => ['html']]),
            new TwigFunction('cookie_consent_has', [$this, 'hasConsentFor']),
            new TwigFunction('cookie_consent_preferences', [$this, 'getPreferences']),
            new TwigFunction('cookie_consent_preferences_raw', [$this, 'getRawPreferences']),
            new TwigFunction('cookie_consent_has_decision', [$this, 'hasDecision']),
            new TwigFunction('cookie_consent_decided_at', [$this, 'getDecidedAt']),
            new TwigFunction('cookie_consent_required', [$this, 'isConsentRequired']),
            new TwigFunction('cookie_consent_categories', [$this, 'getCategories']),
        ];
    }

    /**
     * Renders the cookie consent modal with optional overrides.
     *
     * @param array{variant?: string, theme?: string, density?: string, position?: string} $overrides
     * Optional UI overrides
     * @return string Rendered HTML
     *
     * @example
     */
    public function renderModal(array $overrides = []): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return '';
        }

        return $this->twig->render($this->ui['template'], [
            'categories' => $this->policy->getCategories(),
            'preferences' => $this->consentManager->getPreferences($request),
            'policy_version' => $this->policy->getPolicyVersion(),

            // Always generate endpoint from router (route name) -> avoids parameter issues.
            'consent_endpoint' => $this->urlGenerator->generate('cookie_consent_update'),
            'csrf_token' => $this->csrfTokenManager->getToken(ConsentCsrfTokenManager::TOKEN_ID)->getValue(),

            'consent_required' => $this->isConsentRequired(),
            'privacy_url' => $this->ui['privacy_url'],
            'imprint_url' => $this->ui['imprint_url'],
            'reload_on_change' => $this->ui['reload_on_change'],

            // Layout options with override capability.
            'variant' => $overrides['variant'] ?? $this->ui['variant'],
            'theme' => $overrides['theme'] ?? $this->ui['theme'],
            'density' => $overrides['density'] ?? $this->ui['density'],
            'position' => $overrides['position'] ?? $this->ui['position'],

            // Google Consent Mode v2 configuration.
            'google_consent_mode' => $this->googleConsentMode,
        ]);
    }

    /**
     * Checks if a specific category is allowed.
     *
     * @param string $category Category name
     * @return bool true if allowed
     *
     * @example
     * {% if cookie_consent_has('marketing') %}
     *     {# Marketing-Pixel einbinden #}
     * {% endif %}
     */
    public function hasConsentFor(string $category): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return false;
        }

        return $this->consentManager->getPreferences($request)[$category] ?? false;
    }

    /**
     * Returns the raw (non-normalized) preferences.
     *
     * @return array<string, bool> Raw preferences
     */
    public function getRawPreferences(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return [];
        }

        return $this->consentManager->getState($request)->getPreferences();
    }

    /**
     * Checks if a consent decision exists.
     *
     * @return bool true if decided
     *
     * @example
     * {% if not cookie_consent_has_decision() %}
     *     {# Show a hint that consent is missing #}
     * {% endif %}
     */
    public function hasDecision(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return false;
        }

        return $this->consentManager->hasConsent($request);
    }

    /**
     * Returns the consent decision timestamp.
     *
     * @return \DateTimeImmutable|null Timestamp or null
     *
     * @example
     * {% if cookie_consent_decided_at() %}
     *     Decided at: {{ cookie_consent_decided_at()|date('d.m.Y') }}
     * {% endif %}
     */
    public function getDecidedAt(): ?\DateTimeImmutable
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        return $this->consentManager->getState($request)->getDecidedAt();
    }

    /**
     * Returns the normalized preferences.
     *
     * @return array<string, bool> Category => allowed
     *
     * @example
     * {% set prefs = cookie_consent_preferences() %}
     * {{ prefs|json_encode }}
     */
    public function getPreferences(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return [];
        }

        return $this->consentManager->getPreferences($request);
    }

    /**
     * Checks if the consent modal must be displayed.
     *     Based on session enforcement logic.
     *
     * @return bool true if modal required
     */
    public function isConsentRequired(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return false;
        }

        return (bool) $request->attributes->get(ConsentSessionSubscriber::ATTRIBUTE_REQUIRED, false);
    }

    /**
     * Returns all configured categories.
     *
     * @return array<string, array{label: ?string, description: ?string, required: bool, default: bool}>
     *
     * @example
     * {% for name, config in cookie_consent_categories() %}
     *     <label>
     *         {{ config.label }}
     *         {% if config.required %}(required){% endif %}
     *     </label>
     * {% endfor %}
     */
    public function getCategories(): array
    {
        return $this->policy->getCategories();
    }
}
