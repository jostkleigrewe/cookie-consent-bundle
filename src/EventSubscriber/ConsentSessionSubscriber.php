<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Jostkleigrewe\CookieConsentBundle\Consent\Service\ConsentManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ConsentSessionSubscriber - Verhindert Session-Cookies ohne Consent
 *
 * DE: Kernel-Event-Subscriber der Session-Cookies blockiert wenn kein Consent
 *     vorliegt. Ersetzt die echte Session durch eine Mock-Session im Speicher.
 *     So werden keine Session-Cookies gesetzt bis der Nutzer zustimmt.
 *
 * EN: Kernel event subscriber that blocks session cookies when no consent
 *     exists. Replaces the real session with a mock in-memory session.
 *     This way no session cookies are set until the user consents.
 *
 * Funktionsweise / How it works:
 * 1. Prueft ob Route Consent benoetigt (via ConsentRequirementResolver)
 * 2. Prueft ob Nutzer bereits Consent gegeben hat
 * 3. Wenn Consent fehlt: Ersetzt Session durch MockArraySessionStorage
 * 4. Setzt Request-Attribut '_cookie_consent_required' fuer Templates
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     enforcement:
 *         require_consent_for_session: true  # Session-Enforcement aktivieren
 *         stateless_paths: ['/api']          # Diese Pfade ignorieren
 */
final class ConsentSessionSubscriber implements EventSubscriberInterface
{
    /**
     * DE: Request-Attribut das anzeigt ob Consent erforderlich ist.
     *     Kann in Templates/Controllern abgefragt werden.
     * EN: Request attribute indicating if consent is required.
     *     Can be queried in templates/controllers.
     */
    public const ATTRIBUTE_REQUIRED = '_cookie_consent_required';

    /**
     * @param ConsentManager $consentManager DE: Consent-Service | EN: Consent service
     * @param ConsentRequirementResolver $requirementResolver DE: Prueft ob Consent noetig
     *                                                         EN: Checks if consent needed
     */
    public function __construct(
        private readonly ConsentManager $consentManager,
        private readonly ConsentRequirementResolver $requirementResolver,
    ) {
    }

    /**
     * DE: Registriert den Event-Listener.
     *     Priority 20: Nach Routing (Router hat Priority 32), vor Session-Start.
     *
     * EN: Registers the event listener.
     *     Priority 20: After routing (router has priority 32), before session start.
     *
     * @return array<string, array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        // DE: Priority muss nach dem Routing liegen, damit _controller/Attribute verfuegbar sind.
        // EN: Priority must run after routing so _controller/attributes are available.
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }

    /**
     * DE: Prueft ob Session-Cookie erlaubt ist und ersetzt ggf. durch Mock.
     *
     * EN: Checks if session cookie is allowed and replaces with mock if not.
     *
     * @param RequestEvent $event DE: Kernel-Request-Event | EN: Kernel request event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        // DE: Nur Haupt-Request verarbeiten (keine Sub-Requests)
        // EN: Only process main request (no sub-requests)
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // DE: Pruefe ob diese Route Consent benoetigt
        // EN: Check if this route requires consent
        if (!$this->requirementResolver->requiresConsent($request)) {
            return;
        }

        // DE: Pruefe ob Consent bereits vorhanden
        // EN: Check if consent already exists
        if ($this->consentManager->hasConsent($request)) {
            return;
        }

        // DE: Markiere Request als consent-pflichtig (fuer Templates)
        // EN: Mark request as consent-required (for templates)
        $request->attributes->set(self::ATTRIBUTE_REQUIRED, true);

        // DE: Session bereits gestartet? Dann nicht ersetzen.
        // EN: Session already started? Don't replace then.
        if ($request->hasSession()) {
            $session = $request->getSession();
            if ($session->isStarted()) {
                return;
            }
        }

        // DE: Echte Session durch Mock ersetzen -> kein Cookie wird gesetzt
        // EN: Replace real session with mock -> no cookie will be set
        $request->setSession(new Session(new MockArraySessionStorage()));
    }
}
