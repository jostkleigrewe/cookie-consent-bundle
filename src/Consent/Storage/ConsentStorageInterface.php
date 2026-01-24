<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ConsentStorageInterface - Vertrag fuer Consent-Speicher-Backends
 *
 * DE: Definiert die Schnittstelle fuer alle Storage-Implementierungen.
 *     Ermoeglicht austauschbare Backends (Cookie, Doctrine, Combined).
 *
 * EN: Defines the interface for all storage implementations.
 *     Enables swappable backends (Cookie, Doctrine, Combined).
 *
 * Implementierungen / Implementations:
 * - CookieConsentStorageAdapter: Browser-Cookie (Standard)
 * - DoctrineConsentStorageAdapter: Datenbank via DBAL
 * - CombinedConsentStorageAdapter: Cookie + Datenbank
 *
 * @example
 * // DE: Eigene Storage-Implementierung
 * // EN: Custom storage implementation
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
     * DE: Laedt den Consent-Status aus dem Storage.
     *     Gibt leeren State zurueck wenn kein Consent vorhanden.
     *
     * EN: Loads the consent state from storage.
     *     Returns empty state if no consent exists.
     *
     * @param Request $request DE: HTTP-Request (fuer Cookie-Zugriff, Session, etc.)
     *                         EN: HTTP request (for cookie access, session, etc.)
     * @return ConsentState DE: Geladener State (oder leer) | EN: Loaded state (or empty)
     */
    public function load(Request $request): ConsentState;

    /**
     * DE: Speichert den Consent-Status.
     *     Implementierungen koennen Cookies setzen, DB schreiben, etc.
     *
     * EN: Saves the consent state.
     *     Implementations may set cookies, write to DB, etc.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @param Response $response DE: HTTP-Response (fuer Cookie-Header) | EN: HTTP response (for cookie headers)
     * @param ConsentState $state DE: Zu speichernder State | EN: State to save
     */
    public function save(Request $request, Response $response, ConsentState $state): void;
}
