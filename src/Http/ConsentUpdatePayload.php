<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Http;

use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DE: Validierter Request-Payload fuer den Consent-Update-Endpoint.
 * EN: Validated request payload for the consent update endpoint.
 */
final readonly class ConsentUpdatePayload
{
    public const ACTION_ACCEPT_ALL = 'accept_all';
    public const ACTION_REJECT_OPTIONAL = 'reject_optional';
    public const ACTION_CUSTOM = 'custom';

    /**
     * @param array<string, bool> $preferences
     */
    private function __construct(
        private string $action,
        private array $preferences,
        private string $csrfToken,
    ) {
    }

    public static function fromRequest(Request $request, ConsentPolicy $policy): self
    {
        $payload = $request->getPayload()->all();

        $csrfToken = $payload['csrf_token'] ?? null;
        if (!is_string($csrfToken) || $csrfToken === '') {
            throw new ConsentUpdateException(
                'csrf_token_missing',
                Response::HTTP_FORBIDDEN,
                'Missing CSRF token.'
            );
        }

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

        $preferences = $payload['preferences'] ?? [];
        if (!is_array($preferences)) {
            throw new ConsentUpdateException(
                'preferences_invalid',
                Response::HTTP_BAD_REQUEST,
                'Invalid preferences payload.'
            );
        }

        $allowedCategories = array_keys($policy->getCategories());
        foreach ($preferences as $key => $value) {
            if (!is_string($key) || !in_array($key, $allowedCategories, true)) {
                throw new ConsentUpdateException(
                    'preferences_unknown_category',
                    Response::HTTP_BAD_REQUEST,
                    'Unknown preference category.'
                );
            }

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

    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return array<string, bool>
     */
    public function getPreferences(): array
    {
        return $this->preferences;
    }

    public function getCsrfToken(): string
    {
        return $this->csrfToken;
    }
}
