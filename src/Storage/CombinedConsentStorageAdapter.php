<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Storage;

use Jostkleigrewe\CookieConsentBundle\Model\ConsentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CombinedConsentStorageAdapter - Kombiniert Cookie- und Doctrine-Storage
 *
 * Hybrid-Storage das beide Backends kombiniert:
 *     - Read: Cookie bevorzugen (schnell), Fallback auf Datenbank
 *     - Write: Immer in beide schreiben
 *
 * Hybrid storage that combines both backends:
 *     - Read: Prefer cookie (fast), fallback to database
 *     - Write: Always write to both
 *
 *     Benefits:
 *     - Fast reads without DB query (when cookie present)
 *     - Persistent storage for audit/recovery
 *     - Consent preserved even if cookie is deleted
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     storage: both  # cookie + database
 */
final class CombinedConsentStorageAdapter implements ConsentStorageInterface
{
    public function __construct(
        private readonly CookieConsentStorageAdapter $cookieStorage,
        private readonly ConsentStorageInterface $doctrineStorage,
    ) {
    }

    /**
     * Loads consent state with cookie-first strategy.
     *     1. Try to load from cookie (fast, no DB hit)
     *     2. On empty cookie: fallback to database
     *
     * @param Request $request HTTP request
     * @return ConsentState Loaded state
     */
    public function load(Request $request): ConsentState
    {
        $cookieState = $this->cookieStorage->load($request);

        // If the cookie already contains a decision, we use it (browser source).
        if ($cookieState->getDecidedAt() !== null || $cookieState->getPreferences() !== []) {
            return $cookieState;
        }

        // Fallback to database (e.g., after cookie deletion)
        return $this->doctrineStorage->load($request);
    }

    /**
     * Saves consent state to both backends.
     *     Cookie is always set (frontend needs it immediately).
     *     Database is updated for persistence/audit.
     *
     * @param Request $request HTTP request
     * @param Response $response HTTP response
     * @param ConsentState $state State to save
     */
    public function save(Request $request, Response $response, ConsentState $state): void
    {
        // Always set the cookie (front-end enforcement needs it immediately).
        $this->cookieStorage->save($request, $response, $state);

        // Additionally persist to DB so consent can be reused across sessions/devices.
        $this->doctrineStorage->save($request, $response, $state);
    }
}
