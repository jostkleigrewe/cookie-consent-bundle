<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Event;

use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * ConsentChangedEvent - Event bei Consent-Aenderungen
 *
 * Wird dispatcht wenn ein Nutzer seine Cookie-Praeferenzen aendert.
 *     Ermoeglicht Reaktionen auf Consent-Aenderungen, z.B.:
 *     - Tracking-Systeme aktivieren/deaktivieren
 *     - Externe Services benachrichtigen
 *     - Audit-Logs schreiben
 *     - Cache invalidieren
 *
 * Dispatched when a user changes their cookie preferences.
 *     Enables reactions to consent changes, e.g.:
 *     - Enable/disable tracking systems
 *     - Notify external services
 *     - Write audit logs
 *     - Invalidate cache
 *
 * Event-Name: 'cookie_consent.changed'
 *
 * @example
 * // Register event subscriber
 * class ConsentEventSubscriber implements EventSubscriberInterface
 * {
 *     public static function getSubscribedEvents(): array
 *     {
 *         return [ConsentChangedEvent::NAME => 'onConsentChanged'];
 *     }
 *
 *     public function onConsentChanged(ConsentChangedEvent $event): void
 *     {
 *         $state = $event->getState();
 *
 *         if ($state->isAllowed('analytics')) {
 *             $this->analyticsService->enable();
 *         } else {
 *             $this->analyticsService->disable();
 *         }
 *     }
 * }
 */
final class ConsentChangedEvent extends Event
{
    /**
     * Event name for the EventDispatcher.
     */
    public const NAME = 'cookie_consent.changed';

    /**
     * @param ConsentState $state The new consent state
     * @param ConsentPolicy $policy The current policy
     * @param string $action The performed action ('accept_all', 'reject_optional', 'custom')
     * @param Request|null $request The HTTP request (for context)
     */
    public function __construct(
        private readonly ConsentState $state,
        private readonly ConsentPolicy $policy,
        private readonly string $action,
        private readonly ?Request $request,
    ) {
    }

    /**
     * Returns the new consent state.
     *
     * @return ConsentState The new state
     */
    public function getState(): ConsentState
    {
        return $this->state;
    }

    /**
     * Returns the current policy.
     *
     * @return ConsentPolicy The policy
     */
    public function getPolicy(): ConsentPolicy
    {
        return $this->policy;
    }

    /**
     * Returns the performed action.
     *
     * @return string 'accept_all', 'reject_optional', or 'custom'
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Returns the HTTP request (may be null).
     *
     * @return Request|null The request or null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
