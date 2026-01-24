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
 * ConsentManager - Zentrale API fuer Cookie-Consent
 *
 * DE: Hauptservice fuer alle Consent-Operationen.
 *     Koordiniert Storage, Policy und Events.
 *     Stellt die oeffentliche API fuer Controller und Twig bereit.
 *
 * EN: Main service for all consent operations.
 *     Coordinates storage, policy, and events.
 *     Provides the public API for controllers and Twig.
 *
 * @example
 * // DE: Im Controller verwenden
 * // EN: Use in controller
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
 * // DE: Consent programmatisch speichern
 * // EN: Save consent programmatically
 * $state = $consentManager->savePreferences($request, $response, [
 *     'necessary' => true,
 *     'analytics' => false,
 * ]);
 */
final class ConsentManager
{
    /**
     * @param ConsentStorageInterface $storage DE: Storage-Backend (Cookie/Doctrine/Combined)
     *                                         EN: Storage backend (Cookie/Doctrine/Combined)
     * @param ConsentPolicy $policy DE: Policy mit Kategorien und Version
     *                              EN: Policy with categories and version
     * @param ConsentLogger $logger DE: Optionaler Audit-Logger | EN: Optional audit logger
     * @param EventDispatcherInterface|null $eventDispatcher DE: Event-Dispatcher fuer ConsentChangedEvent
     *                                                        EN: Event dispatcher for ConsentChangedEvent
     */
    public function __construct(
        private readonly ConsentStorageInterface $storage,
        private readonly ConsentPolicy $policy,
        private readonly ConsentLogger $logger,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    /**
     * DE: Laedt den aktuellen Consent-Status aus dem Storage.
     *     Gibt leeren State zurueck wenn Policy-Version nicht uebereinstimmt.
     *
     * EN: Loads the current consent state from storage.
     *     Returns empty state if policy version doesn't match.
     *
     * @param Request $request DE: Aktueller HTTP-Request | EN: Current HTTP request
     * @return ConsentState DE: Consent-Status (kann leer sein) | EN: Consent state (may be empty)
     */
    public function getState(Request $request): ConsentState
    {
        $state = $this->storage->load($request);

        // DE: Bei Policy-Aenderung: alte Zustimmung ist ungueltig
        // EN: On policy change: old consent is invalid
        if ($state->getPolicyVersion() !== $this->policy->getPolicyVersion()) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        return $state;
    }

    /**
     * DE: Prueft ob der Nutzer bereits eine Consent-Entscheidung getroffen hat.
     *
     * EN: Checks if the user has already made a consent decision.
     *
     * @param Request $request DE: Aktueller HTTP-Request | EN: Current HTTP request
     * @return bool DE: true wenn Entscheidung vorliegt | EN: true if decision exists
     */
    public function hasConsent(Request $request): bool
    {
        return $this->getState($request)->hasDecision();
    }

    /**
     * DE: Gibt die normalisierten Praeferenzen zurueck.
     *     Beruecksichtigt Pflicht-Kategorien und Defaults.
     *
     * EN: Returns the normalized preferences.
     *     Considers required categories and defaults.
     *
     * @param Request $request DE: Aktueller HTTP-Request | EN: Current HTTP request
     * @return array<string, bool> DE: Kategorie => erlaubt/nicht erlaubt | EN: Category => allowed/not allowed
     *
     * @example
     * $prefs = $consentManager->getPreferences($request);
     * // ['necessary' => true, 'analytics' => false, 'marketing' => false]
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
     * DE: Speichert benutzerdefinierte Praeferenzen.
     *     Normalisiert die Eingabe, persistiert, loggt und dispatcht Event.
     *
     * EN: Saves custom preferences.
     *     Normalizes input, persists, logs, and dispatches event.
     *
     * @param Request $request DE: Aktueller HTTP-Request | EN: Current HTTP request
     * @param Response $response DE: Response fuer Cookie-Header | EN: Response for cookie header
     * @param array<string, bool> $preferences DE: Zu speichernde Praeferenzen | EN: Preferences to save
     * @param string $action DE: Aktion fuer Logging ('custom', 'accept_all', etc.)
     *                       EN: Action for logging ('custom', 'accept_all', etc.)
     * @return ConsentState DE: Der gespeicherte Consent-Status | EN: The saved consent state
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

    /**
     * DE: Akzeptiert alle Cookie-Kategorien.
     *     Shortcut fuer savePreferences() mit allen Kategorien aktiviert.
     *
     * EN: Accepts all cookie categories.
     *     Shortcut for savePreferences() with all categories enabled.
     *
     * @param Request $request DE: Aktueller HTTP-Request | EN: Current HTTP request
     * @param Response $response DE: Response fuer Cookie-Header | EN: Response for cookie header
     * @return ConsentState DE: Der gespeicherte Consent-Status | EN: The saved consent state
     */
    public function acceptAll(Request $request, Response $response): ConsentState
    {
        return $this->savePreferences($request, $response, $this->policy->acceptAll(), 'accept_all');
    }

    /**
     * DE: Lehnt alle optionalen Cookies ab (nur Pflicht-Kategorien bleiben).
     *     Shortcut fuer savePreferences() mit nur required=true Kategorien.
     *
     * EN: Rejects all optional cookies (only required categories remain).
     *     Shortcut for savePreferences() with only required=true categories.
     *
     * @param Request $request DE: Aktueller HTTP-Request | EN: Current HTTP request
     * @param Response $response DE: Response fuer Cookie-Header | EN: Response for cookie header
     * @return ConsentState DE: Der gespeicherte Consent-Status | EN: The saved consent state
     */
    public function rejectOptional(Request $request, Response $response): ConsentState
    {
        return $this->savePreferences($request, $response, $this->policy->rejectOptional(), 'reject_optional');
    }

    /**
     * DE: Gibt die aktuelle Policy zurueck (fuer Twig/Controller).
     *
     * EN: Returns the current policy (for Twig/controllers).
     *
     * @return ConsentPolicy DE: Die konfigurierte Policy | EN: The configured policy
     */
    public function getPolicy(): ConsentPolicy
    {
        return $this->policy;
    }
}
