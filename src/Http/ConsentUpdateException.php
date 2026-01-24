<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Http;

/**
 * ConsentUpdateException - Exception fuer Consent-Update-Validierungsfehler
 *
 * DE: Wird geworfen wenn die Validierung des Consent-Update-Payloads fehlschlaegt.
 *     Enthaelt strukturierte Fehlerinformationen fuer die JSON-Antwort:
 *     - errorCode: Maschinenlesbarer Fehlercode
 *     - statusCode: HTTP-Status-Code (400, 403, etc.)
 *     - message: Menschenlesbare Fehlermeldung
 *
 * EN: Thrown when consent update payload validation fails.
 *     Contains structured error information for JSON response:
 *     - errorCode: Machine-readable error code
 *     - statusCode: HTTP status code (400, 403, etc.)
 *     - message: Human-readable error message
 *
 * Moegliche Fehlercodes / Possible error codes:
 * - 'csrf_token_missing': CSRF-Token fehlt im Payload
 * - 'action_invalid': Ungueltige Aktion
 * - 'preferences_invalid': Praeferenzen-Format ungueltig
 * - 'preferences_unknown_category': Unbekannte Kategorie in Praeferenzen
 * - 'preferences_invalid_value': Wert ist nicht boolean
 *
 * @example
 * // DE: Im Controller verwenden
 * // EN: Use in controller
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
     * @param string $errorCode DE: Maschinenlesbarer Fehlercode | EN: Machine-readable error code
     * @param int $statusCode DE: HTTP-Status-Code | EN: HTTP status code
     * @param string $message DE: Menschenlesbare Meldung | EN: Human-readable message
     */
    public function __construct(
        private readonly string $errorCode,
        private readonly int $statusCode,
        string $message
    ) {
        parent::__construct($message);
    }

    /**
     * DE: Gibt den maschinenlesbaren Fehlercode zurueck.
     *
     * EN: Returns the machine-readable error code.
     *
     * @return string DE: Der Fehlercode | EN: The error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * DE: Gibt den HTTP-Status-Code zurueck.
     *
     * EN: Returns the HTTP status code.
     *
     * @return int DE: Der Status-Code (z.B. 400, 403) | EN: The status code (e.g., 400, 403)
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
