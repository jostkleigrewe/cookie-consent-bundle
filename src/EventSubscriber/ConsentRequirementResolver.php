<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Jostkleigrewe\CookieConsentBundle\Config\EnforcementConfig;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;

/**
 * ConsentRequirementResolver - Bestimmt ob ein Request Consent benötigt
 *
 * Zentrale Logik zur Bestimmung ob für einen Request Cookie-Consent
 *     erforderlich ist. Berücksichtigt:
 *     - Controller-Attribute (#[ConsentRequired], #[ConsentStateless])
 *     - Configured paths and routes
 *     - Symfony Firewall-Status (stateful vs stateless)
 *
 * Central logic for determining if a request requires cookie consent.
 *     Considers:
 *     - Controller attributes (#[ConsentRequired], #[ConsentStateless])
 *     - Configured paths and routes
 *     - Symfony firewall status (stateful vs stateless)
 *
 * Decision logic:
 * 1. Stateless? (attribute, route, path) -> no consent
 * 2. Protected? (attribute, route, path) -> consent required
 * 3. Session enforcement enabled? -> check firewall
 * 4. Firewall is stateful? -> consent required
 */
final class ConsentRequirementResolver
{
    /**
     * @param EnforcementConfig $enforcement Enforcement configuration DTO
     * @param ControllerAttributeResolver $attributeResolver Attribute resolver
     * @param FirewallMapInterface|null $firewallMap Symfony firewall map (optional)
     * @param LoggerInterface|null $logger Logger for debugging
     */
    public function __construct(
        private readonly EnforcementConfig $enforcement,
        private readonly ControllerAttributeResolver $attributeResolver,
        private readonly ?FirewallMapInterface $firewallMap = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Determines if the request requires cookie consent.
     *
     * @param Request $request HTTP request
     * @return bool true if consent required
     */
    public function requiresConsent(Request $request): bool
    {
        // Step 1: Is the route explicitly stateless?
        if ($this->isStateless($request)) {
            return false;
        }

        // Step 2: Is the route explicitly protected?
        if ($this->isProtected($request)) {
            return true;
        }

        // Step 3: Session enforcement enabled?
        if (!$this->enforcement->requireConsentForSession) {
            return false;
        }

        // Step 4: Check firewall status
        return $this->isStatefulFirewall($request);
    }

    /**
     * Checks if the route is marked as stateless.
     *
     * @param Request $request HTTP request
     * @return bool true if stateless
     */
    private function isStateless(Request $request): bool
    {
        // Attribute has highest priority
        if ($this->attributeResolver->isStateless($request)) {
            return true;
        }

        // Then check configured routes
        if ($this->matchesRoute($request, $this->enforcement->statelessRoutes)) {
            return true;
        }

        // Finally check configured paths
        return $this->matchesPath($request, $this->enforcement->statelessPaths);
    }

    /**
     * Checks if the route is marked as protected.
     *
     * @param Request $request HTTP request
     * @return bool true if protected
     */
    private function isProtected(Request $request): bool
    {
        if ($this->attributeResolver->isRequired($request)) {
            return true;
        }

        if ($this->matchesRoute($request, $this->enforcement->protectedRoutes)) {
            return true;
        }

        return $this->matchesPath($request, $this->enforcement->protectedPaths);
    }

    /**
     * Checks if the request route name is in the list.
     *
     * @param Request $request HTTP request
     * @param string[] $routes List of route names
     * @return bool true if match
     */
    private function matchesRoute(Request $request, array $routes): bool
    {
        $route = $request->attributes->get('_route');
        if (!is_string($route) || $route === '') {
            return false;
        }

        return in_array($route, $routes, true);
    }

    /**
     * Checks if the request path starts with a prefix.
     *
     * @param Request $request HTTP request
     * @param string[] $paths List of path prefixes
     * @return bool true if match
     */
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

    /**
     * Checks if the Symfony firewall is stateful for this request.
     *     Stateful = session is used = cookie consent needed.
     *
     * @param Request $request HTTP request
     * @return bool true if stateful
     */
    private function isStatefulFirewall(Request $request): bool
    {
        // No firewall map available? Don't enforce consent.
        if ($this->firewallMap === null) {
            $this->logger?->notice('Consent enforcement skipped: no firewall map available.');
            return false;
        }

        if (!method_exists($this->firewallMap, 'getFirewallConfig')) {
            $this->logger?->notice('Consent enforcement skipped: firewall map does not expose config.');
            return false;
        }

        // Get firewall config for this request
        $config = $this->firewallMap->getFirewallConfig($request);
        if ($config === null) {
            $this->logger?->notice('Consent enforcement skipped: no firewall config matched the request.');
            return false;
        }

        // Stateless firewall? No session cookie needed.
        return !$config->isStateless();
    }
}
