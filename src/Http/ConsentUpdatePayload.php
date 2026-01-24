<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Http;

use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ConsentUpdatePayload - Validierter Request-Payload fuer Consent-Updates
 *
 * DE: Immutables DTO das einen validierten Consent-Update-Request repraesentiert.
 *     Parst und validiert JSON-Payload aus dem Request:
 *     - csrf_token: Pflichtfeld, CSRF-Schutz
 *     - action: 'accept_all', 'reject_optional', oder 'custom'
 *     - preferences: Objekt mit Kategorie => boolean Paaren
 *
 * EN: Immutable DTO representing a validated consent update request.
 *     Parses and validates JSON payload from request:
 *     - csrf_token: Required, CSRF protection
 *     - action: 'accept_all', 'reject_optional', or 'custom'
 *     - preferences: Object with category => boolean pairs
 *
 * @example
 * // DE: Erwartetes JSON-Format
 * // EN: Expected JSON format
 * {
 *     "csrf_token": "abc123...",
 *     "action": "custom",
 *     "preferences": {
 *         "analytics": true,
 *         "marketing": false
 *     }
 * }
 *
 * @example
 * // DE: Im Controller verwenden
 * // EN: Use in controller
 * $payload = ConsentUpdatePayload::fromRequest($request, $policy);
 * $action = $payload->getAction();
 * $prefs = $payload->getPreferences();
 */
final readonly class ConsentUpdatePayload
{
    /**
     * DE: Aktion: Alle Cookies akzeptieren.
     * EN: Action: Accept all cookies.
     */
    public const ACTION_ACCEPT_ALL = 'accept_all';

    /**
     * DE: Aktion: Nur notwendige Cookies (optionale ablehnen).
     * EN: Action: Only necessary cookies (reject optional).
     */
    public const ACTION_REJECT_OPTIONAL = 'reject_optional';

    /**
     * DE: Aktion: Benutzerdefinierte Auswahl.
     * EN: Action: Custom selection.
     */
    public const ACTION_CUSTOM = 'custom';

    /**
     * @param string                $action DE: Die Aktion | EN: The action
     * @param array<string, bool>   $preferences DE: Die Praeferenzen | EN: The preferences
     * @param string                $csrfToken DE: Das CSRF-Token | EN: The CSRF token
     */
    private function __construct(
        private string  $action,
        private array   $preferences,
        private string  $csrfToken,
    ) {
    }

    /**
     * DE: Erstellt ein validiertes Payload-Objekt aus dem Request.
     *     Wirft ConsentUpdateException bei Validierungsfehlern.
     *
     * EN: Creates a validated payload object from the request.
     *     Throws ConsentUpdateException on validation errors.
     *
     * @param Request $request DE: HTTP-Request mit JSON-Body | EN: HTTP request with JSON body
     * @param ConsentPolicy $policy DE: Policy fuer Kategorie-Validierung
     *                               EN: Policy for category validation
     * @return self DE: Validiertes Payload | EN: Validated payload
     *
     * @throws ConsentUpdateException DE: Bei Validierungsfehlern | EN: On validation errors
     */
    public static function fromRequest(Request $request, ConsentPolicy $policy): self
    {
        // DE: JSON-Payload aus Request extrahieren
        // EN: Extract JSON payload from request
        $payload = $request->getPayload()->all();

        // DE: CSRF-Token validieren (Pflichtfeld)
        // EN: Validate CSRF token (required)
        $csrfToken = $payload['csrf_token'] ?? null;
        if (!is_string($csrfToken) || $csrfToken === '') {
            throw new ConsentUpdateException(
                'csrf_token_missing',
                Response::HTTP_FORBIDDEN,
                'Missing CSRF token.'
            );
        }

        // DE: Action validieren
        // EN: Validate action
        $action = $payload['action'] ?? self::ACTION_CUSTOM;
        if (!is_string($action)) {
            throw new ConsentUpdateException(
                'action_invalid',
                Response::HTTP_BAD_REQUEST,
                'Invalid action payload.'
            );
        }

        $allowedActions = [self::ACTION_ACCEPT_ALL, self::ACTION_REJECT_OPTIONAL, self::ACTION_CUSTOM];
        if (!in_array($action, $allowedActions, true)) {
            throw new ConsentUpdateException(
                'action_invalid',
                Response::HTTP_BAD_REQUEST,
                'Invalid action payload.'
            );
        }

        // DE: Preferences validieren
        // EN: Validate preferences
        $preferences = $payload['preferences'] ?? [];
        if (!is_array($preferences)) {
            throw new ConsentUpdateException(
                'preferences_invalid',
                Response::HTTP_BAD_REQUEST,
                'Invalid preferences payload.'
            );
        }

        // DE: Jede Praeferenz gegen Policy validieren
        // EN: Validate each preference against policy
        $allowedCategories = array_keys($policy->getCategories());
        foreach ($preferences as $key => $value) {
            // DE: Kategorie muss bekannt sein
            // EN: Category must be known
            if (!is_string($key) || !in_array($key, $allowedCategories, true)) {
                throw new ConsentUpdateException(
                    'preferences_unknown_category',
                    Response::HTTP_BAD_REQUEST,
                    'Unknown preference category.'
                );
            }

            // DE: Wert muss boolean sein
            // EN: Value must be boolean
            if (!is_bool($value)) {
                throw new ConsentUpdateException(
                    'preferences_invalid_value',
                    Response::HTTP_BAD_REQUEST,
                    'Invalid preference value.'
                );
            }
        }

        return new self($action, $preferences, $csrfToken);
    }

    /**
     * DE: Gibt die gewaehlte Aktion zurueck.
     *
     * EN: Returns the chosen action.
     *
     * @return string DE: 'accept_all', 'reject_optional', oder 'custom'
     *                EN: 'accept_all', 'reject_optional', or 'custom'
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * DE: Gibt die gewaehlten Praeferenzen zurueck.
     *
     * EN: Returns the chosen preferences.
     *
     * @return array<string, bool> DE: Kategorie => erlaubt | EN: Category => allowed
     */
    public function getPreferences(): array
    {
        return $this->preferences;
    }

    /**
     * DE: Gibt das CSRF-Token zurueck.
     *
     * EN: Returns the CSRF token.
     *
     * @return string DE: Das Token | EN: The token
     */
    public function getCsrfToken(): string
    {
        return $this->csrfToken;
    }
}
