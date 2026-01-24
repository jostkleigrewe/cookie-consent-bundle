<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CombinedConsentStorageAdapter - Kombiniert Cookie- und Doctrine-Storage
 *
 * DE: Hybrid-Storage das beide Backends kombiniert:
 *     - Read: Cookie bevorzugen (schnell), Fallback auf Datenbank
 *     - Write: Immer in beide schreiben
 *
 *     Vorteile:
 *     - Schnelle Reads ohne DB-Query (wenn Cookie vorhanden)
 *     - Persistente Speicherung fuer Audit/Recovery
 *     - Consent bleibt erhalten auch wenn Cookie geloescht wird
 *
 * EN: Hybrid storage that combines both backends:
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
 *     storage: both  # Cookie + Datenbank
 */
final class CombinedConsentStorageAdapter implements ConsentStorageInterface
{
    /**
     * @param CookieConsentStorageAdapter $cookieStorage DE: Cookie-Backend | EN: Cookie backend
     * @param DoctrineConsentStorageAdapter $doctrineStorage DE: Doctrine-Backend | EN: Doctrine backend
     */
    public function __construct(
        private readonly CookieConsentStorageAdapter $cookieStorage,
        private readonly DoctrineConsentStorageAdapter $doctrineStorage,
    ) {
    }

    /**
     * DE: Laedt Consent-State mit Cookie-First-Strategie.
     *     1. Versuche aus Cookie zu laden (schnell, kein DB-Hit)
     *     2. Bei leerem Cookie: Fallback auf Datenbank
     *
     * EN: Loads consent state with cookie-first strategy.
     *     1. Try to load from cookie (fast, no DB hit)
     *     2. On empty cookie: fallback to database
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @return ConsentState DE: Geladener State | EN: Loaded state
     */
    public function load(Request $request): ConsentState
    {
        $cookieState = $this->cookieStorage->load($request);

        // DE: Wenn im Cookie bereits eine Entscheidung vorliegt, nutzen wir diese (Browser-Quelle).
        // EN: If the cookie already contains a decision, we use it (browser source).
        if ($cookieState->getDecidedAt() !== null || $cookieState->getPreferences() !== []) {
            return $cookieState;
        }

        // DE: Fallback auf Datenbank (z.B. nach Cookie-Loeschung)
        // EN: Fallback to database (e.g., after cookie deletion)
        return $this->doctrineStorage->load($request);
    }

    /**
     * DE: Speichert Consent-State in beide Backends.
     *     Cookie wird immer gesetzt (Frontend braucht es sofort).
     *     Datenbank wird fuer Persistenz/Audit aktualisiert.
     *
     * EN: Saves consent state to both backends.
     *     Cookie is always set (frontend needs it immediately).
     *     Database is updated for persistence/audit.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @param Response $response DE: HTTP-Response | EN: HTTP response
     * @param ConsentState $state DE: Zu speichernder State | EN: State to save
     */
    public function save(Request $request, Response $response, ConsentState $state): void
    {
        // DE: Cookie immer setzen (Front-End Enforcement braucht das unmittelbar).
        // EN: Always set the cookie (front-end enforcement needs it immediately).
        $this->cookieStorage->save($request, $response, $state);

        // DE: Zusätzlich in DB persistieren, damit Consent geräte-/session-übergreifend nutzbar ist.
        // EN: Additionally persist to DB so consent can be reused across sessions/devices.
        $this->doctrineStorage->save($request, $response, $state);
    }
}
