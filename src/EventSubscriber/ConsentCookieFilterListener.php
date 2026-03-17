<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Jostkleigrewe\CookieConsentBundle\Service\ConsentManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * DE: Filtert den Session-Cookie aus der Response wenn kein Consent vorliegt.
 *     Im Gegensatz zum alten ConsentSessionSubscriber wird die Session NICHT
 *     ersetzt — sie funktioniert intern normal (Security, OIDC, CSRF).
 *     Nur der Cookie wird aus der HTTP-Response entfernt.
 *
 *     Der Cookie wird NICHT gefiltert wenn:
 *     - Der User authentifiziert ist (Session ist "strictly necessary", ePrivacy Art. 5(3))
 *     - Die Response ein Redirect ist (Auth-Flows brauchen Session-State)
 *
 * EN: Filters the session cookie from the response when no consent exists.
 *     Unlike the old ConsentSessionSubscriber, the session is NOT replaced —
 *     it works normally internally (security, OIDC, CSRF).
 *     Only the cookie is removed from the HTTP response.
 *
 *     The cookie is NOT filtered when:
 *     - The user is authenticated (session is "strictly necessary", ePrivacy Art. 5(3))
 *     - The response is a redirect (auth flows need session state)
 *
 * How it works:
 * 1. kernel.request (prio 20): Check consent requirement, set request attribute
 * 2. kernel.response (prio -1001): Remove session cookie if no consent
 *    (runs AFTER Symfony's SessionListener at -1000 which sets the cookie)
 */
final class ConsentCookieFilterListener implements EventSubscriberInterface
{
    /**
     * DE: Request-Attribut das anzeigt ob Consent erforderlich ist.
     *     Kann in Templates/Controllern abgefragt werden.
     * EN: Request attribute indicating if consent is required.
     *     Can be queried in templates/controllers.
     */
    public const ATTRIBUTE_REQUIRED = '_cookie_consent_required';

    public function __construct(
        private readonly ConsentManager $consentManager,
        private readonly ConsentRequirementResolver $requirementResolver,
        private readonly ?TokenStorageInterface $tokenStorage = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // DE: Request: Consent-Attribut setzen (nach Routing bei Prio 32)
            // EN: Request: Set consent attribute (after routing at prio 32)
            KernelEvents::REQUEST => ['onKernelRequest', 20],

            // DE: Response: Session-Cookie entfernen wenn kein Consent
            //     Prio -1001 = NACH Symfonys SessionListener (-1000)
            // EN: Response: Remove session cookie if no consent
            //     Prio -1001 = AFTER Symfony's SessionListener (-1000)
            KernelEvents::RESPONSE => ['onKernelResponse', -1001],
        ];
    }

    /**
     * DE: Setzt das Request-Attribut für Templates (Consent-Banner anzeigen).
     *     Greift NICHT mehr in die Session ein.
     * EN: Sets the request attribute for templates (show consent banner).
     *     No longer modifies the session.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->requirementResolver->requiresConsent($request)) {
            return;
        }

        if ($this->consentManager->hasConsent($request)) {
            return;
        }

        // DE: Request als "Consent erforderlich" markieren (für Templates)
        // EN: Mark request as "consent required" (for templates)
        $request->attributes->set(self::ATTRIBUTE_REQUIRED, true);
    }

    /**
     * DE: Entfernt den Session-Cookie aus der Response wenn kein Consent vorliegt.
     *     Läuft NACH Symfonys SessionListener (-1000), der den Cookie setzt.
     * EN: Removes the session cookie from the response when no consent exists.
     *     Runs AFTER Symfony's SessionListener (-1000) which sets the cookie.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        // DE: Wurde im Request als "consent required" markiert?
        // EN: Was marked as "consent required" in the request phase?
        if (!$event->getRequest()->attributes->get(self::ATTRIBUTE_REQUIRED, false)) {
            return;
        }

        // DE: Redirects nicht filtern — Auth-Flows (OIDC) brauchen den Session-Cookie
        //     für State/Nonce über die Redirect-Kette hinweg.
        // EN: Don't filter on redirects — auth flows (OIDC) need the session cookie
        //     to preserve state/nonce across the redirect chain.
        $response = $event->getResponse();
        if ($response->isRedirection()) {
            return;
        }

        // DE: Authentifizierte User behalten den Session-Cookie — er ist
        //     "strictly necessary" für die Anwendung (ePrivacy Art. 5(3)).
        // EN: Authenticated users keep the session cookie — it is
        //     "strictly necessary" for the application (ePrivacy Art. 5(3)).
        if ($this->isUserAuthenticated()) {
            return;
        }

        // DE: Session-Cookie-Name ermitteln
        // EN: Determine session cookie name
        $request = $event->getRequest();
        $sessionName = $request->hasSession(true)
            ? $request->getSession()->getName()
            : session_name();

        // DE: Session-Cookie aus der Response entfernen
        // EN: Remove session cookie from the response
        $response->headers->removeCookie($sessionName, '/', null);
    }

    private function isUserAuthenticated(): bool
    {
        $token = $this->tokenStorage?->getToken();
        if ($token === null) {
            return false;
        }

        return $token->getUser() instanceof UserInterface;
    }
}
