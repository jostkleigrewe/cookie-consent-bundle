<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Storage;

use Jostkleigrewe\CookieConsentBundle\Config\CookieConfig;
use Jostkleigrewe\CookieConsentBundle\Model\ConsentState;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CookieConsentStorageAdapter - Speichert Consent im Browser-Cookie
 *
 * Standard-Storage-Backend das Consent-Daten im Browser speichert.
 *     Schnell, kein Server-State noetig, funktioniert ohne Datenbank.
 *     Nachteil: Consent geht verloren wenn Cookies geloescht werden.
 *
 * Default storage backend that stores consent data in the browser.
 *     Fast, no server state needed, works without database.
 *     Downside: Consent is lost when cookies are deleted.
 *
 * Cookie format (JSON):
 * {
 *     "version": "1.0",
 *     "preferences": {
 *         "necessary": {"allowed": true, "vendors": {}},
 *         "analytics": {"allowed": false, "vendors": {}}
 *     },
 *     "decided_at": "2024-01-15T10:30:00+00:00"
 * }
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     storage: cookie  # default
 *     cookie:
 *         name: cookie_consent
 *         lifetime: 15552000  # 6 months
 *         same_site: lax
 *         http_only: true
 */
final class CookieConsentStorageAdapter implements ConsentStorageInterface
{
    /**
     * @param CookieConfig $config Cookie configuration
     * @param string $policyVersion Current policy version
     */
    public function __construct(
        private readonly CookieConfig $config,
        private readonly string $policyVersion,
    ) {
    }

    /**
     * Loads consent state from browser cookie.
     *     Checks policy version and returns empty state on mismatch.
     *
     * @param Request $request HTTP request with cookies
     * @return ConsentState Loaded or empty state
     */
    public function load(Request $request): ConsentState
    {
        // Read cookie
        $raw = $request->cookies->get($this->config->name);
        if (!is_string($raw) || $raw === '') {
            return ConsentState::empty($this->policyVersion);
        }

        // Parse JSON
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ConsentState::empty($this->policyVersion);
        }

        // Check policy version - on mismatch old consent is invalid
        $storedVersion = $data['version'] ?? null;
        if (!is_string($storedVersion) || $storedVersion !== $this->policyVersion) {
            return ConsentState::empty($this->policyVersion);
        }

        // Extract preferences
        $preferences = $data['preferences'] ?? [];
        if (!is_array($preferences)) {
            $preferences = [];
        }

        // Parse decision timestamp
        $decidedAt = null;
        if (!empty($data['decided_at'])) {
            try {
                $decidedAt = new \DateTimeImmutable($data['decided_at']);
            } catch (\Exception) {
                $decidedAt = null;
            }
        }

        return new ConsentState($preferences, $this->policyVersion, $decidedAt);
    }

    /**
     * Saves consent state as browser cookie.
     *     Sets cookie header on the response.
     *
     * @param Request $request HTTP request
     * @param Response $response HTTP response for cookie header
     * @param ConsentState $state State to save
     *
     * @throws \RuntimeException If JSON encoding fails
     */
    public function save(Request $request, Response $response, ConsentState $state): void
    {
        // Serialize state as JSON
        try {
            $payload = json_encode([
                'version' => $state->getPolicyVersion(),
                'preferences' => $state->getPreferences(),
                'decided_at' => $state->getDecidedAt()?->format(DATE_ATOM),
            ], JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Failed to encode consent cookie payload.', 0, $exception);
        }

        // If secure=null, automatically use the request's HTTPS status.
        $secure = $this->config->secure ?? $request->isSecure();

        // Create cookie with all configured options
        $cookie = Cookie::create(
            $this->config->name,
            $payload,
            $this->getExpiration(),
            $this->config->path,
            $this->config->domain,
            $secure,
            $this->config->httpOnly,
            false,
            $this->config->sameSite
        );

        $response->headers->setCookie($cookie);
    }

    /**
     * Calculates the cookie expiration date.
     *
     * @return \DateTimeImmutable Expiration date
     */
    private function getExpiration(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(sprintf('+%d seconds', $this->config->lifetime));
    }
}
