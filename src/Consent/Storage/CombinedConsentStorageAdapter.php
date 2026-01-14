<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DE: Kombiniert Cookie- und Doctrine-Storage.
 *     Read-Strategie: Cookie bevorzugen (schnell, keine DB), Fallback auf Doctrine.
 *     Write-Strategie: immer in beide schreiben (falls Doctrine verfügbar).
 * EN: Combines cookie and doctrine storage.
 *     Read strategy: prefer cookie (fast, no DB), fallback to doctrine.
 *     Write strategy: always write to both (if doctrine is available).
 */
final class CombinedConsentStorageAdapter implements ConsentStorageInterface
{
    public function __construct(
        private readonly CookieConsentStorageAdapter    $cookieStorage,
        private readonly DoctrineConsentStorageAdapter  $doctrineStorage,
    ) {
    }

    public function load(Request $request): ConsentState
    {
        $cookieState = $this->cookieStorage->load($request);

        // DE: Wenn im Cookie bereits eine Entscheidung vorliegt, nutzen wir diese (Browser-Quelle).
        // EN: If the cookie already contains a decision, we use it (browser source).
        if ($cookieState->getDecidedAt() !== null || $cookieState->getPreferences() !== []) {
            return $cookieState;
        }

        return $this->doctrineStorage->load($request);
    }

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
