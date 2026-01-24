<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;

/**
 * ConsentRequirementResolver - Bestimmt ob ein Request Consent benoetigt
 *
 * DE: Zentrale Logik zur Bestimmung ob fuer einen Request Cookie-Consent
 *     erforderlich ist. Beruecksichtigt:
 *     - Controller-Attribute (#[ConsentRequired], #[ConsentStateless])
 *     - Konfigurierte Pfade und Routen
 *     - Symfony Firewall-Status (stateful vs stateless)
 *
 * EN: Central logic for determining if a request requires cookie consent.
 *     Considers:
 *     - Controller attributes (#[ConsentRequired], #[ConsentStateless])
 *     - Configured paths and routes
 *     - Symfony firewall status (stateful vs stateless)
 *
 * Entscheidungslogik / Decision logic:
 * 1. Stateless? (Attribut, Route, Pfad) -> Kein Consent
 * 2. Protected? (Attribut, Route, Pfad) -> Consent erforderlich
 * 3. Session-Enforcement aktiv? -> Pruefe Firewall
 * 4. Firewall ist stateful? -> Consent erforderlich
 */
final class ConsentRequirementResolver
{
    /**
     * @param array{
     *     require_consent_for_session: bool,
     *     stateless_paths: string[],
     *     stateless_routes: string[],
     *     protected_paths: string[],
     *     protected_routes: string[]
     * } $enforcement DE: Enforcement-Konfiguration | EN: Enforcement configuration
     * @param ControllerAttributeResolver $attributeResolver DE: Attribut-Resolver
     *                                                        EN: Attribute resolver
     * @param FirewallMapInterface|null $firewallMap DE: Symfony Firewall-Map (optional)
     *                                                EN: Symfony firewall map (optional)
     * @param LoggerInterface|null $logger DE: Logger fuer Debugging | EN: Logger for debugging
     */
    public function __construct(
        private readonly array $enforcement,
        private readonly ControllerAttributeResolver $attributeResolver,
        private readonly ?FirewallMapInterface $firewallMap = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * DE: Bestimmt ob der Request Cookie-Consent benoetigt.
     *
     * EN: Determines if the request requires cookie consent.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @return bool DE: true wenn Consent erforderlich | EN: true if consent required
     */
    public function requiresConsent(Request $request): bool
    {
        // DE: Schritt 1: Ist die Route explizit stateless?
        // EN: Step 1: Is the route explicitly stateless?
        if ($this->isStateless($request)) {
            return false;
        }

        // DE: Schritt 2: Ist die Route explizit protected?
        // EN: Step 2: Is the route explicitly protected?
        if ($this->isProtected($request)) {
            return true;
        }

        // DE: Schritt 3: Session-Enforcement aktiviert?
        // EN: Step 3: Session enforcement enabled?
        if (!$this->enforcement['require_consent_for_session']) {
            return false;
        }

        // DE: Schritt 4: Firewall-Status pruefen
        // EN: Step 4: Check firewall status
        return $this->isStatefulFirewall($request);
    }

    /**
     * DE: Prueft ob die Route als stateless markiert ist.
     *
     * EN: Checks if the route is marked as stateless.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @return bool DE: true wenn stateless | EN: true if stateless
     */
    private function isStateless(Request $request): bool
    {
        // DE: Attribut hat hoechste Prioritaet
        // EN: Attribute has highest priority
        if ($this->attributeResolver->isStateless($request)) {
            return true;
        }

        // DE: Dann konfigurierte Routes pruefen
        // EN: Then check configured routes
        if ($this->matchesRoute($request, $this->enforcement['stateless_routes'])) {
            return true;
        }

        // DE: Zuletzt konfigurierte Pfade pruefen
        // EN: Finally check configured paths
        return $this->matchesPath($request, $this->enforcement['stateless_paths']);
    }

    /**
     * DE: Prueft ob die Route als protected markiert ist.
     *
     * EN: Checks if the route is marked as protected.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @return bool DE: true wenn protected | EN: true if protected
     */
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

    /**
     * DE: Prueft ob der Request-Route-Name in der Liste enthalten ist.
     *
     * EN: Checks if the request route name is in the list.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @param string[] $routes DE: Liste von Route-Namen | EN: List of route names
     * @return bool DE: true wenn Match | EN: true if match
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
     * DE: Prueft ob der Request-Pfad mit einem Prefix beginnt.
     *
     * EN: Checks if the request path starts with a prefix.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @param string[] $paths DE: Liste von Pfad-Prefixen | EN: List of path prefixes
     * @return bool DE: true wenn Match | EN: true if match
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
     * DE: Prueft ob die Symfony Firewall fuer diesen Request stateful ist.
     *     Stateful = Session wird verwendet = Cookie-Consent noetig.
     *
     * EN: Checks if the Symfony firewall is stateful for this request.
     *     Stateful = session is used = cookie consent needed.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @return bool DE: true wenn stateful | EN: true if stateful
     */
    private function isStatefulFirewall(Request $request): bool
    {
        // DE: Keine Firewall-Map verfuegbar? Consent nicht erzwingen.
        // EN: No firewall map available? Don't enforce consent.
        if ($this->firewallMap === null) {
            $this->logger?->notice('Consent enforcement skipped: no firewall map available.');
            return false;
        }

        // DE: Firewall-Config fuer diesen Request holen
        // EN: Get firewall config for this request
        $config = $this->firewallMap->getFirewallConfig($request);
        if ($config === null) {
            $this->logger?->notice('Consent enforcement skipped: no firewall config matched the request.');
            return false;
        }

        // DE: Stateless Firewall? Kein Session-Cookie noetig.
        // EN: Stateless firewall? No session cookie needed.
        return !$config->isStateless();
    }
}
