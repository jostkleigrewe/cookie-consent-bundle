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
 * Verarbeitet Consent-Aenderungen vom Frontend (Modal).
 *     Validiert CSRF-Token, parst Payload und speichert Praeferenzen.
 *     Antwortet mit JSON fuer asynchrone Verarbeitung.
 *
 * Processes consent changes from the frontend (modal).
 *     Validates CSRF token, parses payload, and saves preferences.
 *     Responds with JSON for async processing.
 *
 * Route: POST /_cookie-consent (cookie_consent_update)
 *
 * @example
 * // Frontend sends JSON payload
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
 * // Possible actions:
 * // - 'accept_all': Accept all categories
 * // - 'reject_optional': Only required categories
 * // - 'custom': Custom selection
 */
final readonly class CookieConsentController
{
    /**
     * @param ConsentManager $consentManager Consent service
     * @param ConsentCsrfTokenManager $csrfTokenManager CSRF validation
     */
    public function __construct(
        private ConsentManager $consentManager,
        private ConsentCsrfTokenManager $csrfTokenManager,
    ) {
    }

    /**
     * Processes consent update requests.
     *     1. Validates and parses JSON payload
     *     2. Verifies CSRF token
     *     3. Saves preferences based on action
     *     4. Returns new state as JSON
     *
     * @param Request $request HTTP request with JSON body
     * @return JsonResponse JSON with preferences, policy_version, decided_at
     *
     * @example
     * // Successful response
     * {
     *     "preferences": {"necessary": true, "analytics": true},
     *     "policy_version": "1.0",
     *     "decided_at": "2024-01-15T10:30:00+00:00"
     * }
     *
     * // Error response
     * {
     *     "error": "Invalid CSRF token.",
     *     "code": "csrf_invalid"
     * }
     */
    public function update(Request $request): JsonResponse
    {
        // Validate and parse payload
        try {
            $payload = ConsentUpdatePayload::fromRequest($request, $this->consentManager->getPolicy());
        } catch (ConsentUpdateException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
                'code' => $exception->getErrorCode(),
            ], $exception->getStatusCode());
        }

        // Verify CSRF token (same-origin)
        $token = new CsrfToken(ConsentCsrfTokenManager::TOKEN_ID, $payload->getCsrfToken());
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        // Create response (cookie will be attached here)
        $response = new JsonResponse();

        // Execute action
        $state = match ($payload->getAction()) {
            'accept_all' => $this->consentManager->acceptAll($request, $response),
            'reject_optional' => $this->consentManager->rejectOptional($request, $response),
            default => $this->consentManager->savePreferences($request, $response, $payload->getPreferences()),
        };

        // Success response with new state
        $response->setData([
            'preferences' => $state->getPreferences(),
            'policy_version' => $state->getPolicyVersion(),
            'decided_at' => $state->getDecidedAt()?->format(DATE_ATOM),
        ]);

        return $response;
    }
}
