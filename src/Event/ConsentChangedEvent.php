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
 * DE: Wird dispatcht wenn ein Nutzer seine Cookie-Praeferenzen aendert.
 *     Ermoeglicht Reaktionen auf Consent-Aenderungen, z.B.:
 *     - Tracking-Systeme aktivieren/deaktivieren
 *     - Externe Services benachrichtigen
 *     - Audit-Logs schreiben
 *     - Cache invalidieren
 *
 * EN: Dispatched when a user changes their cookie preferences.
 *     Enables reactions to consent changes, e.g.:
 *     - Enable/disable tracking systems
 *     - Notify external services
 *     - Write audit logs
 *     - Invalidate cache
 *
 * Event-Name: 'cookie_consent.changed'
 *
 * @example
 * // DE: Event-Subscriber registrieren
 * // EN: Register event subscriber
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
     * DE: Event-Name fuer den EventDispatcher.
     * EN: Event name for the EventDispatcher.
     */
    public const NAME = 'cookie_consent.changed';

    /**
     * @param ConsentState $state DE: Der neue Consent-State | EN: The new consent state
     * @param ConsentPolicy $policy DE: Die aktuelle Policy | EN: The current policy
     * @param string $action DE: Die ausgefuehrte Aktion ('accept_all', 'reject_optional', 'custom')
     *                       EN: The performed action ('accept_all', 'reject_optional', 'custom')
     * @param Request|null $request DE: Der HTTP-Request (fuer Kontext)
     *                               EN: The HTTP request (for context)
     */
    public function __construct(
        private readonly ConsentState $state,
        private readonly ConsentPolicy $policy,
        private readonly string $action,
        private readonly ?Request $request,
    ) {
    }

    /**
     * DE: Gibt den neuen Consent-State zurueck.
     *
     * EN: Returns the new consent state.
     *
     * @return ConsentState DE: Der neue State | EN: The new state
     */
    public function getState(): ConsentState
    {
        return $this->state;
    }

    /**
     * DE: Gibt die aktuelle Policy zurueck.
     *
     * EN: Returns the current policy.
     *
     * @return ConsentPolicy DE: Die Policy | EN: The policy
     */
    public function getPolicy(): ConsentPolicy
    {
        return $this->policy;
    }

    /**
     * DE: Gibt die ausgefuehrte Aktion zurueck.
     *
     * EN: Returns the performed action.
     *
     * @return string DE: 'accept_all', 'reject_optional', oder 'custom'
     *                EN: 'accept_all', 'reject_optional', or 'custom'
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * DE: Gibt den HTTP-Request zurueck (kann null sein).
     *
     * EN: Returns the HTTP request (may be null).
     *
     * @return Request|null DE: Der Request oder null | EN: The request or null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
