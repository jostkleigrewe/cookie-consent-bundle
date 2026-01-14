<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\FirewallMapInterface;

final class ConsentRequirementResolver
{
    /**
     * @param array{require_consent_for_session: bool, stateless_paths: string[], stateless_routes: string[], protected_paths: string[], protected_routes: string[]} $enforcement
     */
    public function __construct(
        private readonly array $enforcement,
        private readonly ControllerAttributeResolver $attributeResolver,
        private readonly ?FirewallMapInterface $firewallMap = null,
    ) {
    }

    public function requiresConsent(Request $request): bool
    {
        if ($this->isStateless($request)) {
            return false;
        }

        if ($this->isProtected($request)) {
            return true;
        }

        if (!$this->enforcement['require_consent_for_session']) {
            return false;
        }

        return $this->isStatefulFirewall($request);
    }

    private function isStateless(Request $request): bool
    {
        if ($this->attributeResolver->isStateless($request)) {
            return true;
        }

        if ($this->matchesRoute($request, $this->enforcement['stateless_routes'])) {
            return true;
        }

        return $this->matchesPath($request, $this->enforcement['stateless_paths']);
    }

    private function isProtected(Request $request): bool
    {
        if ($this->attributeResolver->isRequired($request)) {
            return true;
        }

        if ($this->matchesRoute($request, $this->enforcement['protected_routes'])) {
            return true;
        }

        return $this->matchesPath($request, $this->enforcement['protected_paths']);
    }

    private function matchesRoute(Request $request, array $routes): bool
    {
        $route = $request->attributes->get('_route');
        if (!is_string($route) || $route === '') {
            return false;
        }

        return in_array($route, $routes, true);
    }

    private function matchesPath(Request $request, array $paths): bool
    {
        $path = $request->getPathInfo();
        foreach ($paths as $prefix) {
            if ($prefix !== '' && str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isStatefulFirewall(Request $request): bool
    {
        if ($this->firewallMap === null) {
            return false;
        }

        $config = $this->firewallMap->getFirewallConfig($request);
        if ($config === null) {
            return false;
        }

        return !$config->isStateless();
    }
}
