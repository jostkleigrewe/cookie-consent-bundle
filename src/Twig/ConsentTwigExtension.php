<?php

declare(strict_types=1);

namespace JostKleigrewe\CookieConsentBundle\Twig;

use JostKleigrewe\CookieConsentBundle\Consent\ConsentManager;
use JostKleigrewe\CookieConsentBundle\Consent\ConsentPolicy;
use JostKleigrewe\CookieConsentBundle\EventSubscriber\ConsentSessionSubscriber;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ConsentTwigExtension extends AbstractExtension
{
    /**
     * @param array{layout: string, template: string} $ui
     * @param array{consent_endpoint: string} $routes
     */
    public function __construct(
        private readonly Environment $twig,
        private readonly ConsentManager $consentManager,
        private readonly ConsentPolicy $policy,
        private readonly RequestStack $requestStack,
        private readonly array $ui,
        private readonly array $routes,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cookie_consent_modal', [$this, 'renderModal'], ['is_safe' => ['html']]),
            new TwigFunction('cookie_consent_has', [$this, 'hasConsentFor']),
            new TwigFunction('cookie_consent_preferences', [$this, 'getPreferences']),
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
            'layout' => $this->ui['layout'],
            'categories' => $this->policy->getCategories(),
            'preferences' => $this->consentManager->getPreferences($request),
            'policy_version' => $this->policy->getPolicyVersion(),
            'consent_endpoint' => $this->routes['consent_endpoint'],
            'consent_required' => $this->isConsentRequired(),
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
