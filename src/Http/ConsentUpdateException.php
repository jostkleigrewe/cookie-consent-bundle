<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Http;

/**
 * ConsentUpdateException - Exception fuer Consent-Update-Validierungsfehler
 *
 * Wird geworfen wenn die Validierung des Consent-Update-Payloads fehlschlaegt.
 *     Enthaelt strukturierte Fehlerinformationen fuer die JSON-Antwort:
 *     - errorCode: Maschinenlesbarer Fehlercode
 *     - statusCode: HTTP-Status-Code (400, 403, etc.)
 *     - message: Menschenlesbare Fehlermeldung
 *
 * Thrown when consent update payload validation fails.
 *     Contains structured error information for JSON response:
 *     - errorCode: Machine-readable error code
 *     - statusCode: HTTP status code (400, 403, etc.)
 *     - message: Human-readable error message
 *
 * Possible error codes:
 * - 'csrf_token_missing': CSRF token missing in payload
 * - 'action_invalid': Invalid action
 * - 'preferences_invalid': Invalid preferences format
 * - 'preferences_unknown_category': Unknown category in preferences
 * - 'preferences_invalid_value': Value is not boolean
 *
 * @example
 * // Use in controller
 * try {
 *     $payload = ConsentUpdatePayload::fromRequest($request, $policy);
 * } catch (ConsentUpdateException $e) {
 *     return new JsonResponse([
 *         'error' => $e->getMessage(),
 *         'code' => $e->getErrorCode(),
 *     ], $e->getStatusCode());
 * }
 */
final class ConsentUpdateException extends \RuntimeException
{
    /**
     * @param string $errorCode Machine-readable error code
     * @param int $statusCode HTTP status code
     * @param string $message Human-readable message
     */
    public function __construct(
        private readonly string $errorCode,
        private readonly int $statusCode,
        string $message
    ) {
        parent::__construct($message);
    }

    /**
     * Returns the machine-readable error code.
     *
     * @return string The error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Returns the HTTP status code.
     *
     * @return int The status code (e.g., 400, 403)
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
