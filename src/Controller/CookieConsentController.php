<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Controller;

use Jostkleigrewe\CookieConsentBundle\Consent\Service\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Http\ConsentUpdateException;
use Jostkleigrewe\CookieConsentBundle\Http\ConsentUpdatePayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Jostkleigrewe\CookieConsentBundle\Security\ConsentCsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * CookieConsentController - AJAX-Endpoint fuer Consent-Updates
 *
 * DE: Verarbeitet Consent-Aenderungen vom Frontend (Modal).
 *     Validiert CSRF-Token, parst Payload und speichert Praeferenzen.
 *     Antwortet mit JSON fuer asynchrone Verarbeitung.
 *
 * EN: Processes consent changes from the frontend (modal).
 *     Validates CSRF token, parses payload, and saves preferences.
 *     Responds with JSON for async processing.
 *
 * Route: POST /_cookie-consent (cookie_consent_update)
 *
 * @example
 * // DE: Frontend sendet JSON-Payload
 * // EN: Frontend sends JSON payload
 * fetch('/_cookie-consent', {
 *     method: 'POST',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         csrf_token: '...',
 *         action: 'custom',
 *         preferences: { analytics: true, marketing: false }
 *     })
 * });
 *
 * // DE: Moegliche Aktionen:
 * // EN: Possible actions:
 * // - 'accept_all': Alle Kategorien akzeptieren
 * // - 'reject_optional': Nur Pflicht-Kategorien
 * // - 'custom': Benutzerdefinierte Auswahl
 */
final readonly class CookieConsentController
{
    /**
     * @param ConsentManager $consentManager DE: Consent-Service | EN: Consent service
     * @param ConsentCsrfTokenManager $csrfTokenManager DE: CSRF-Validierung | EN: CSRF validation
     */
    public function __construct(
        private ConsentManager $consentManager,
        private ConsentCsrfTokenManager $csrfTokenManager,
    ) {
    }

    /**
     * DE: Verarbeitet Consent-Update-Requests.
     *     1. Validiert und parst den JSON-Payload
     *     2. Prueft CSRF-Token
     *     3. Speichert Praeferenzen basierend auf Aktion
     *     4. Gibt neuen State als JSON zurueck
     *
     * EN: Processes consent update requests.
     *     1. Validates and parses JSON payload
     *     2. Verifies CSRF token
     *     3. Saves preferences based on action
     *     4. Returns new state as JSON
     *
     * @param Request $request DE: HTTP-Request mit JSON-Body | EN: HTTP request with JSON body
     * @return JsonResponse DE: JSON mit preferences, policy_version, decided_at
     *                      EN: JSON with preferences, policy_version, decided_at
     *
     * @example
     * // DE: Erfolgreiche Antwort
     * // EN: Successful response
     * {
     *     "preferences": {"necessary": true, "analytics": true},
     *     "policy_version": "1.0",
     *     "decided_at": "2024-01-15T10:30:00+00:00"
     * }
     *
     * // DE: Fehler-Antwort
     * // EN: Error response
     * {
     *     "error": "Invalid CSRF token.",
     *     "code": "csrf_invalid"
     * }
     */
    public function update(Request $request): JsonResponse
    {
        // DE: Payload validieren und parsen
        // EN: Validate and parse payload
        try {
            $payload = ConsentUpdatePayload::fromRequest($request, $this->consentManager->getPolicy());
        } catch (ConsentUpdateException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
                'code' => $exception->getErrorCode(),
            ], $exception->getStatusCode());
        }

        // DE: CSRF-Token pruefen (Same-Origin)
        // EN: Verify CSRF token (same-origin)
        $token = new CsrfToken(ConsentCsrfTokenManager::TOKEN_ID, $payload->getCsrfToken());
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        // DE: Response erstellen (Cookie wird hier angehaengt)
        // EN: Create response (cookie will be attached here)
        $response = new JsonResponse();

        // DE: Aktion ausfuehren
        // EN: Execute action
        $state = match ($payload->getAction()) {
            'accept_all' => $this->consentManager->acceptAll($request, $response),
            'reject_optional' => $this->consentManager->rejectOptional($request, $response),
            default => $this->consentManager->savePreferences($request, $response, $payload->getPreferences()),
        };

        // DE: Erfolgreiche Antwort mit neuem State
        // EN: Success response with new state
        $response->setData([
            'preferences' => $state->getPreferences(),
            'policy_version' => $state->getPolicyVersion(),
            'decided_at' => $state->getDecidedAt()?->format(DATE_ATOM),
        ]);

        return $response;
    }
}
