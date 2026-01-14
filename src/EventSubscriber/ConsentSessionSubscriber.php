<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\EventSubscriber;

use Jostkleigrewe\CookieConsentBundle\Consent\ConsentManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

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

        $request->setSession(new Session(new MockArraySessionStorage()));
    }
}
