<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConsentManager
{
    public function __construct(
        private readonly ConsentStorageInterface $storage,
        private readonly ConsentPolicy $policy,
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
    public function savePreferences(Request $request, Response $response, array $preferences): ConsentState
    {
        $normalized = $this->policy->normalizePreferences($preferences);
        $state = ConsentState::empty($this->policy->getPolicyVersion())->withPreferences($normalized);
        $this->storage->save($request, $response, $state);

        return $state;
    }

    public function acceptAll(Request $request, Response $response): ConsentState
    {
        return $this->savePreferences($request, $response, $this->policy->acceptAll());
    }

    public function rejectOptional(Request $request, Response $response): ConsentState
    {
        return $this->savePreferences($request, $response, $this->policy->rejectOptional());
    }

    public function getPolicy(): ConsentPolicy
    {
        return $this->policy;
    }
}
