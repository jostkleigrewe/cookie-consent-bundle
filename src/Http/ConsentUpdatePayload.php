<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Http;

use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ConsentUpdatePayload - Validierter Request-Payload fuer Consent-Updates
 *
 * Immutables DTO das einen validierten Consent-Update-Request repraesentiert.
 *     Parst und validiert JSON-Payload aus dem Request:
 *     - csrf_token: Required field, CSRF protection
 *     - action: 'accept_all', 'reject_optional', oder 'custom'
 *     - preferences: Objekt mit Kategorie => boolean Paaren
 *
 * Immutable DTO representing a validated consent update request.
 *     Parses and validates JSON payload from request:
 *     - csrf_token: Required, CSRF protection
 *     - action: 'accept_all', 'reject_optional', or 'custom'
 *     - preferences: Object with category => boolean pairs
 *
 * @example
 * // Expected JSON format
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
 * // Use in controller
 * $payload = ConsentUpdatePayload::fromRequest($request, $policy);
 * $action = $payload->getAction();
 * $prefs = $payload->getPreferences();
 */
final readonly class ConsentUpdatePayload
{
    /**
     * Action: Accept all cookies.
     */
    public const ACTION_ACCEPT_ALL = 'accept_all';

    /**
     * Action: Only necessary cookies (reject optional).
     */
    public const ACTION_REJECT_OPTIONAL = 'reject_optional';

    /**
     * Action: Custom selection.
     */
    public const ACTION_CUSTOM = 'custom';

    /**
     * @param string                $action The action
     * @param array<string, bool>   $preferences The preferences
     * @param string                $csrfToken The CSRF token
     */
    private function __construct(
        private string  $action,
        private array   $preferences,
        private string  $csrfToken,
    ) {
    }

    /**
     * Creates a validated payload object from the request.
     *     Throws ConsentUpdateException on validation errors.
     *
     * @param Request $request HTTP request with JSON body
     * @param ConsentPolicy $policy Policy for category validation
     * @return self Validated payload
     *
     * @throws ConsentUpdateException On validation errors
     */
    public static function fromRequest(Request $request, ConsentPolicy $policy): self
    {
        // Extract JSON payload from request
        $payload = $request->getPayload()->all();

        // Validate CSRF token (required)
        $csrfToken = $payload['csrf_token'] ?? null;
        if (!is_string($csrfToken) || $csrfToken === '') {
            throw new ConsentUpdateException(
                'csrf_token_missing',
                Response::HTTP_FORBIDDEN,
                'Missing CSRF token.'
            );
        }

        // Validate action
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

        // Validate preferences
        $preferences = $payload['preferences'] ?? [];
        if (!is_array($preferences)) {
            throw new ConsentUpdateException(
                'preferences_invalid',
                Response::HTTP_BAD_REQUEST,
                'Invalid preferences payload.'
            );
        }

        // Validate each preference against policy
        $allowedCategories = array_keys($policy->getCategories());
        foreach ($preferences as $key => $value) {
            // Category must be known
            if (!is_string($key) || !in_array($key, $allowedCategories, true)) {
                throw new ConsentUpdateException(
                    'preferences_unknown_category',
                    Response::HTTP_BAD_REQUEST,
                    'Unknown preference category.'
                );
            }

            // Value must be boolean
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
     * Returns the chosen action.
     *
     * @return string 'accept_all', 'reject_optional', or 'custom'
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Returns the chosen preferences.
     *
     * @return array<string, bool> Category => allowed
     */
    public function getPreferences(): array
    {
        return $this->preferences;
    }

    /**
     * Returns the CSRF token.
     *
     * @return string The token
     */
    public function getCsrfToken(): string
    {
        return $this->csrfToken;
    }
}
