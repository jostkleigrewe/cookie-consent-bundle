<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Service;

use Jostkleigrewe\CookieConsentBundle\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Storage\ConsentStorageInterface;
use Jostkleigrewe\CookieConsentBundle\Event\ConsentChangedEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * ConsentManager - Zentrale API fuer Cookie-Consent
 *
 * Hauptservice fuer alle Consent-Operationen.
 *     Koordiniert Storage, Policy und Events.
 *     Stellt die oeffentliche API fuer Controller und Twig bereit.
 *
 * Main service for all consent operations.
 *     Coordinates storage, policy, and events.
 *     Provides the public API for controllers and Twig.
 *
 * @example
 * // Use in controller
 * public function index(ConsentManager $consentManager, Request $request): Response
 * {
 *     if ($consentManager->hasConsent($request)) {
 *         $prefs = $consentManager->getPreferences($request);
 *         if ($prefs['analytics']) {
 *             // Analytics-Code laden
 *         }
 *     }
 * }
 *
 * @example
 * // Save consent programmatically
 * $state = $consentManager->savePreferences($request, $response, [
 *     'necessary' => true,
 *     'analytics' => false,
 * ]);
 */
final class ConsentManager
{
    /**
     * @param ConsentStorageInterface $storage Storage backend (Cookie/Doctrine/Combined)
     * @param ConsentPolicy $policy Policy with categories and version
     * @param ConsentLogger $logger Optional audit logger
     * @param EventDispatcherInterface|null $eventDispatcher Event dispatcher for ConsentChangedEvent
     */
    public function __construct(
        private readonly ConsentStorageInterface $storage,
        private readonly ConsentPolicy $policy,
        private readonly ConsentLogger $logger,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    /**
     * Loads the current consent state from storage.
     *     Returns empty state if policy version doesn't match.
     *
     * @param Request $request Current HTTP request
     * @return ConsentState Consent state (may be empty)
     */
    public function getState(Request $request): ConsentState
    {
        $state = $this->storage->load($request);

        // On policy change: old consent is invalid
        if ($state->getPolicyVersion() !== $this->policy->getPolicyVersion()) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        return $state;
    }

    /**
     * Checks if the user has already made a consent decision.
     *
     * @param Request $request Current HTTP request
     * @return bool true if decision exists
     */
    public function hasConsent(Request $request): bool
    {
        return $this->getState($request)->hasDecision();
    }

    /**
     * Returns the normalized preferences.
     *     Considers required categories and defaults.
     *
     * @param Request $request Current HTTP request
     * @return array<string, array{allowed: bool, vendors: array<string, bool>}> Category => preferences
     *
     * @example
     * $prefs = $consentManager->getPreferences($request);
     * // ['necessary' => ['allowed' => true, 'vendors' => []], ...]
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
     * Saves custom preferences.
     *     Normalizes input, persists, logs, and dispatches event.
     *
     * @param Request $request Current HTTP request
     * @param Response $response Response for cookie header
     * @param array<string, array{allowed: bool, vendors: array<string, bool>}> $preferences Preferences to save
     * @param string $action Action for logging ('custom', 'accept_all', etc.)
     * @return ConsentState The saved consent state
     */
    public function savePreferences(Request $request, Response $response, array $preferences, string $action = 'custom'): ConsentState
    {
        $normalized = $this->policy->normalizePreferences($preferences);
        $state = ConsentState::empty($this->policy->getPolicyVersion())->withPreferences($normalized);

        $this->storage->save($request, $response, $state);
        $this->logger->log($action, $state, $this->policy, $request, $response);

        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(
                new ConsentChangedEvent($state, $this->policy, $action, $request),
                ConsentChangedEvent::NAME
            );
        }

        return $state;
    }

    /**
     * Accepts all cookie categories.
     *     Shortcut for savePreferences() with all categories enabled.
     *
     * @param Request $request Current HTTP request
     * @param Response $response Response for cookie header
     * @return ConsentState The saved consent state
     */
    public function acceptAll(Request $request, Response $response): ConsentState
    {
        return $this->savePreferences($request, $response, $this->policy->acceptAll(), 'accept_all');
    }

    /**
     * Rejects all optional cookies (only required categories remain).
     *     Shortcut for savePreferences() with only required=true categories.
     *
     * @param Request $request Current HTTP request
     * @param Response $response Response for cookie header
     * @return ConsentState The saved consent state
     */
    public function rejectOptional(Request $request, Response $response): ConsentState
    {
        return $this->savePreferences($request, $response, $this->policy->rejectOptional(), 'reject_optional');
    }

    /**
     * Returns the current policy (for Twig/controllers).
     *
     * @return ConsentPolicy The configured policy
     */
    public function getPolicy(): ConsentPolicy
    {
        return $this->policy;
    }
}
