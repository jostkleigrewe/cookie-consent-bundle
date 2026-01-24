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
 * DE: Stellt Twig-Funktionen fuer Consent-Abfragen und Modal-Rendering bereit.
 *     Ermoeglicht einfache Integration in Templates ohne Controller-Logik.
 *
 * EN: Provides Twig functions for consent queries and modal rendering.
 *     Enables easy template integration without controller logic.
 *
 * Verfuegbare Funktionen / Available functions:
 * - cookie_consent_modal()        - Rendert das Consent-Modal
 * - cookie_consent_has(category)  - Prueft ob Kategorie erlaubt
 * - cookie_consent_preferences()  - Gibt normalisierte Praeferenzen zurueck
 * - cookie_consent_preferences_raw() - Gibt rohe Praeferenzen zurueck
 * - cookie_consent_has_decision() - Prueft ob Entscheidung vorliegt
 * - cookie_consent_decided_at()   - Gibt Entscheidungszeitpunkt zurueck
 * - cookie_consent_required()     - Prueft ob Modal angezeigt werden muss
 * - cookie_consent_categories()   - Gibt alle Kategorien zurueck
 *
 * @example
 * {# DE: Modal im base.html.twig einbinden #}
 * {# EN: Include modal in base.html.twig #}
 * {{ cookie_consent_modal() }}
 *
 * @example
 * {# DE: Bedingt Analytics-Script laden #}
 * {# EN: Conditionally load analytics script #}
 * {% if cookie_consent_has('analytics') %}
 *     <script src="analytics.js"></script>
 * {% endif %}
 *
 * @example
 * {# DE: Praeferenzen als JSON fuer JavaScript #}
 * {# EN: Preferences as JSON for JavaScript #}
 * <script>
 *     window.cookiePreferences = {{ cookie_consent_preferences()|json_encode|raw }};
 * </script>
 */
final class ConsentTwigExtension extends AbstractExtension
{
    /**
     * @param Environment $twig DE: Twig-Environment fuer Rendering | EN: Twig environment for rendering
     * @param ConsentManager $consentManager DE: Consent-Service | EN: Consent service
     * @param ConsentPolicy $policy DE: Policy-Konfiguration | EN: Policy configuration
     * @param RequestStack $requestStack DE: Request-Stack | EN: Request stack
     * @param UrlGeneratorInterface $urlGenerator DE: URL-Generator | EN: URL generator
     * @param ConsentCsrfTokenManager $csrfTokenManager DE: CSRF-Manager | EN: CSRF manager
     * @param array{
     *     template: string,
     *     variant: string,
     *     theme: string,
     *     density: string,
     *     privacy_url: ?string,
     *     imprint_url: ?string,
     *     reload_on_change: bool
     * } $ui DE: UI-Konfiguration | EN: UI configuration
     * @param array{enabled: bool, mapping: array<string, string>} $googleConsentMode
     *        DE: Google Consent Mode Konfiguration | EN: Google Consent Mode configuration
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
     * DE: Registriert alle Twig-Funktionen.
     *
     * EN: Registers all Twig functions.
     *
     * @return TwigFunction[] DE: Liste der Twig-Funktionen | EN: List of Twig functions
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
     * DE: Rendert das Cookie-Consent-Modal mit optionalen Overrides.
     *
     * EN: Renders the cookie consent modal with optional overrides.
     *
     * @param array{variant?: string, theme?: string, density?: string} $overrides
     *        DE: Optionale UI-Overrides | EN: Optional UI overrides
     * @return string DE: Gerendertes HTML | EN: Rendered HTML
     *
     * @example
     * {# DE: Mit Default-Einstellungen #}
     * {{ cookie_consent_modal() }}
     *
     * @example
     * {# DE: Mit Override fuer Dark-Mode #}
     * {{ cookie_consent_modal({theme: 'night'}) }}
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

            // DE: Endpoint immer aus dem Router (Route-Name) generieren -> keine Param-Probleme.
            // EN: Always generate endpoint from router (route name) -> avoids parameter issues.
            'consent_endpoint' => $this->urlGenerator->generate('cookie_consent_update'),
            'csrf_token' => $this->csrfTokenManager->getToken(ConsentCsrfTokenManager::TOKEN_ID)->getValue(),

            'consent_required' => $this->isConsentRequired(),
            'privacy_url' => $this->ui['privacy_url'] ?? null,
            'imprint_url' => $this->ui['imprint_url'] ?? null,
            'reload_on_change' => (bool) ($this->ui['reload_on_change'] ?? false),

            // DE: Layout-Optionen mit Override-Moeglichkeit.
            // EN: Layout options with override capability.
            'variant' => $overrides['variant'] ?? $this->ui['variant'] ?? 'tabler',
            'theme' => $overrides['theme'] ?? $this->ui['theme'] ?? 'day',
            'density' => $overrides['density'] ?? $this->ui['density'] ?? 'normal',

            // DE: Google Consent Mode v2 Konfiguration.
            // EN: Google Consent Mode v2 configuration.
            'google_consent_mode' => $this->googleConsentMode,
        ]);
    }

    /**
     * DE: Prueft ob eine bestimmte Kategorie erlaubt ist.
     *
     * EN: Checks if a specific category is allowed.
     *
     * @param string $category DE: Kategoriename | EN: Category name
     * @return bool DE: true wenn erlaubt | EN: true if allowed
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
     * DE: Gibt die rohen (nicht normalisierten) Praeferenzen zurueck.
     *
     * EN: Returns the raw (non-normalized) preferences.
     *
     * @return array<string, bool> DE: Rohe Praeferenzen | EN: Raw preferences
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
     * DE: Prueft ob eine Consent-Entscheidung vorliegt.
     *
     * EN: Checks if a consent decision exists.
     *
     * @return bool DE: true wenn entschieden | EN: true if decided
     *
     * @example
     * {% if not cookie_consent_has_decision() %}
     *     {# Hinweis anzeigen dass Consent fehlt #}
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
     * DE: Gibt den Zeitpunkt der Consent-Entscheidung zurueck.
     *
     * EN: Returns the consent decision timestamp.
     *
     * @return \DateTimeImmutable|null DE: Zeitpunkt oder null | EN: Timestamp or null
     *
     * @example
     * {% if cookie_consent_decided_at() %}
     *     Entschieden am: {{ cookie_consent_decided_at()|date('d.m.Y') }}
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
     * DE: Gibt die normalisierten Praeferenzen zurueck.
     *
     * EN: Returns the normalized preferences.
     *
     * @return array<string, bool> DE: Kategorie => erlaubt | EN: Category => allowed
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
     * DE: Prueft ob das Consent-Modal angezeigt werden muss.
     *     Basiert auf Session-Enforcement-Logik.
     *
     * EN: Checks if the consent modal must be displayed.
     *     Based on session enforcement logic.
     *
     * @return bool DE: true wenn Modal erforderlich | EN: true if modal required
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
     * DE: Gibt alle konfigurierten Kategorien zurueck.
     *
     * EN: Returns all configured categories.
     *
     * @return array<string, array{label: ?string, description: ?string, required: bool, default: bool}>
     *
     * @example
     * {% for name, config in cookie_consent_categories() %}
     *     <label>
     *         {{ config.label }}
     *         {% if config.required %}(Pflicht){% endif %}
     *     </label>
     * {% endfor %}
     */
    public function getCategories(): array
    {
        return $this->policy->getCategories();
    }
}
