<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Storage;

use Jostkleigrewe\CookieConsentBundle\Model\ConsentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ConsentStorageInterface - Vertrag fuer Consent-Speicher-Backends
 *
 * Definiert die Schnittstelle fuer alle Storage-Implementierungen.
 *     Ermoeglicht austauschbare Backends (Cookie, Doctrine, Combined).
 *
 * Defines the interface for all storage implementations.
 *     Enables swappable backends (Cookie, Doctrine, Combined).
 *
 * Implementations:
 * - CookieConsentStorageAdapter: Browser cookie (default)
 * - DoctrineConsentStorageAdapter: Database via DBAL
 * - CombinedConsentStorageAdapter: Cookie + database
 *
 * @example
 * // Custom storage implementation
 * class RedisConsentStorageAdapter implements ConsentStorageInterface
 * {
 *     public function load(Request $request): ConsentState
 *     {
 *         $key = $this->getKey($request);
 *         $data = $this->redis->get($key);
 *         // ... parse and return ConsentState
 *     }
 *
 *     public function save(Request $request, Response $response, ConsentState $state): void
 *     {
 *         $key = $this->getKey($request);
 *         $this->redis->set($key, json_encode($state));
 *     }
 * }
 */
interface ConsentStorageInterface
{
    /**
     * Loads the consent state from storage.
     *     Returns empty state if no consent exists.
     *
     * @param Request $request HTTP request (for cookie access, session, etc.)
     * @return ConsentState Loaded state (or empty)
     */
    public function load(Request $request): ConsentState;

    /**
     * Saves the consent state.
     *     Implementations may set cookies, write to DB, etc.
     *
     * @param Request $request HTTP request
     * @param Response $response HTTP response (for cookie headers)
     * @param ConsentState $state State to save
     */
    public function save(Request $request, Response $response, ConsentState $state): void;
}
