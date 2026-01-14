<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Event;

use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * DE: Event, das beim Speichern von Consent-Praeferenzen dispatcht wird.
 * EN: Event dispatched when consent preferences are saved.
 */
final class ConsentChangedEvent extends Event
{
    public const NAME = 'cookie_consent.changed';

    public function __construct(
        private readonly ConsentState $state,
        private readonly ConsentPolicy $policy,
        private readonly string $action,
        private readonly ?Request $request,
    ) {
    }

    public function getState(): ConsentState
    {
        return $this->state;
    }

    public function getPolicy(): ConsentPolicy
    {
        return $this->policy;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
