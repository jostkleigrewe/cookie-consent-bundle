<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Jostkleigrewe\CookieConsentBundle\Service\ConsentManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ConsentSessionSubscriber - Verhindert Session-Cookies ohne Consent
 *
 * Kernel-Event-Subscriber der Session-Cookies blockiert wenn kein Consent
 *     vorliegt. Ersetzt die echte Session durch eine Mock-Session im Speicher.
 *
 * Kernel event subscriber that blocks session cookies when no consent
 *     exists. Replaces the real session with a mock in-memory session.
 *     This way no session cookies are set until the user consents.
 *
 * How it works:
 * 1. Checks whether the route requires consent (via ConsentRequirementResolver)
 * 2. Checks whether the user has already given consent
 * 3. If consent is missing: replace session with MockArraySessionStorage
 * 4. Set request attribute '_cookie_consent_required' for templates
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     enforcement:
 *         require_consent_for_session: true  # Enable session enforcement
 *         stateless_paths: ['/api']          # Ignore these paths
 */
final class ConsentSessionSubscriber implements EventSubscriberInterface
{
    /**
     * Request attribute indicating if consent is required.
     *     Can be queried in templates/controllers.
     */
    public const ATTRIBUTE_REQUIRED = '_cookie_consent_required';

    /**
     * @param ConsentManager $consentManager Consent service
     * @param ConsentRequirementResolver $requirementResolver Checks if consent needed
     */
    public function __construct(
        private readonly ConsentManager $consentManager,
        private readonly ConsentRequirementResolver $requirementResolver,
    ) {
    }

    /**
     * Registers the event listener.
     *     Priority 20: After routing (router has priority 32), before session start.
     *
     * @return array<string, array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        // Priority must run after routing so _controller/attributes are available.
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }

    /**
     * Checks if session cookie is allowed and replaces with mock if not.
     *
     * @param RequestEvent $event Kernel request event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        // Only process main request (no sub-requests)
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Check if this route requires consent
        if (!$this->requirementResolver->requiresConsent($request)) {
            return;
        }

        // Check if consent already exists
        if ($this->consentManager->hasConsent($request)) {
            return;
        }

        // Mark request as consent-required (for templates)
        $request->attributes->set(self::ATTRIBUTE_REQUIRED, true);

        // Session already started? Don't replace then.
        if ($request->hasSession()) {
            $session = $request->getSession();
            if ($session->isStarted()) {
                return;
            }
        }

        // Replace real session with mock -> no cookie will be set
        $request->setSession(new Session(new MockArraySessionStorage()));
    }
}
