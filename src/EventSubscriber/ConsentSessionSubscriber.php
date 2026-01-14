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
 * DE: Erzwingt Consent und verhindert Session-Cookies ohne Zustimmung.
 * EN: Enforces consent and prevents session cookies without consent.
 */
final class ConsentSessionSubscriber implements EventSubscriberInterface
{
    public const ATTRIBUTE_REQUIRED = '_cookie_consent_required';

    public function __construct(
        private readonly ConsentManager $consentManager,
        private readonly ConsentRequirementResolver $requirementResolver,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // DE: Priority muss nach dem Routing liegen, damit _controller/Attribute verfuegbar sind.
        // EN: Priority must run after routing so _controller/attributes are available.
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }

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

        $request->attributes->set(self::ATTRIBUTE_REQUIRED, true);

        if ($request->hasSession()) {
            $session = $request->getSession();
            if ($session->isStarted()) {
                return;
            }
        }

        $request->setSession(new Session(new MockArraySessionStorage()));
    }
}
