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
 * DE: Twig-Extension mit Consent-Helpern und Modal-Rendering.
 * EN: Twig extension exposing consent helpers and modal rendering.
 */
final class ConsentTwigExtension extends AbstractExtension
{
    /**
     * @param array{template: string, privacy_url: string, imprint_url: string} $ui
     */
    public function __construct(
        private readonly Environment                $twig,
        private readonly ConsentManager             $consentManager,
        private readonly ConsentPolicy              $policy,
        private readonly RequestStack               $requestStack,
        private readonly UrlGeneratorInterface      $urlGenerator,
        private readonly ConsentCsrfTokenManager    $csrfTokenManager,
        private readonly array                      $ui,
    ) {
    }

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

    public function renderModal(): string
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
            'privacy_url' => $this->ui['privacy_url'] ?? '/privacy',
            'imprint_url' => $this->ui['imprint_url'] ?? '/imprint',
        ]);
    }

    public function hasConsentFor(string $category): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return false;
        }

        return $this->consentManager->getPreferences($request)[$category] ?? false;
    }

    /**
     * @return array<string, bool>
     */
    public function getRawPreferences(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return [];
        }

        return $this->consentManager->getState($request)->getPreferences();
    }

    public function hasDecision(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return false;
        }

        return $this->consentManager->hasConsent($request);
    }

    public function getDecidedAt(): ?\DateTimeImmutable
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        return $this->consentManager->getState($request)->getDecidedAt();
    }

    /**
     * @return array<string, bool>
     */
    public function getPreferences(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return [];
        }

        return $this->consentManager->getPreferences($request);
    }

    public function isConsentRequired(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return false;
        }

        return (bool) $request->attributes->get(ConsentSessionSubscriber::ATTRIBUTE_REQUIRED, false);
    }

    /**
     * @return array<string, array{label: ?string, description: ?string, required: bool, default: bool}>
     */
    public function getCategories(): array
    {
        return $this->policy->getCategories();
    }
}
