<?php

declare(strict_types=1);

namespace App\EventListener;

use Jostkleigrewe\CookieConsentBundle\Event\ConsentChangedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ConsentChangedEvent::NAME, method: 'onConsentChanged')]
final class ConsentChangedListener
{
    public function onConsentChanged(ConsentChangedEvent $event): void
    {
        $state = $event->getState();
        $action = $event->getAction();
        $request = $event->getRequest();

        // Example: react to consent changes (analytics, tags, etc.).
        $analyticsAllowed = $state->isAllowed('analytics');
        $clientIp = $request?->getClientIp();

        // Implement your integration here.
    }
}
