<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Service;

use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\ConsentStorageInterface;
use Jostkleigrewe\CookieConsentBundle\Event\ConsentChangedEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * DE: Zentrale API fuer Consent-Status, Speicherung und Events.
 * EN: Central API for consent state, persistence, and events.
 */
final class ConsentManager
{
    public function __construct(
        private readonly ConsentStorageInterface $storage,
        private readonly ConsentPolicy $policy,
        private readonly ConsentLogger $logger,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function getState(Request $request): ConsentState
    {
        $state = $this->storage->load($request);
        if ($state->getPolicyVersion() !== $this->policy->getPolicyVersion()) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        return $state;
    }

    public function hasConsent(Request $request): bool
    {
        return $this->getState($request)->hasDecision();
    }

    /**
     * @return array<string, bool>
     */
    public function getPreferences(Request $request): array
    {
        $state = $this->getState($request);
        if (!$state->hasDecision()) {
            return $this->policy->normalizePreferences([]);
        }

        return $this->policy->normalizePreferences($state->getPreferences());
    }

    /**
     * @param array<string, bool> $preferences
     */
    public function savePreferences(Request $request, Response $response, array $preferences, string $action = 'custom'): ConsentState
    {
        $normalized = $this->policy->normalizePreferences($preferences);
        $state = ConsentState::empty($this->policy->getPolicyVersion())->withPreferences($normalized);
        $this->storage->save($request, $response, $state);
        $this->logger->log($action, $state, $this->policy, $request);
        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(
                new ConsentChangedEvent($state, $this->policy, $action, $request),
                ConsentChangedEvent::NAME
            );
        }

        return $state;
    }

    public function acceptAll(Request $request, Response $response): ConsentState
    {
        return $this->savePreferences($request, $response, $this->policy->acceptAll(), 'accept_all');
    }

    public function rejectOptional(Request $request, Response $response): ConsentState
    {
        return $this->savePreferences($request, $response, $this->policy->rejectOptional(), 'reject_optional');
    }

    public function getPolicy(): ConsentPolicy
    {
        return $this->policy;
    }
}
