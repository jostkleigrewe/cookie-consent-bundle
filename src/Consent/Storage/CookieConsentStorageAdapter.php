<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use Jostkleigrewe\CookieConsentBundle\Consent\Config\CookieConfig;
use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CookieConsentStorageAdapter - Speichert Consent im Browser-Cookie
 *
 * DE: Standard-Storage-Backend das Consent-Daten im Browser speichert.
 *     Schnell, kein Server-State noetig, funktioniert ohne Datenbank.
 *     Nachteil: Consent geht verloren wenn Cookies geloescht werden.
 *
 * EN: Default storage backend that stores consent data in the browser.
 *     Fast, no server state needed, works without database.
 *     Downside: Consent is lost when cookies are deleted.
 *
 * Cookie-Format (JSON):
 * {
 *     "version": "1.0",
 *     "preferences": {"necessary": true, "analytics": false},
 *     "decided_at": "2024-01-15T10:30:00+00:00"
 * }
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     storage: cookie  # Standard
 *     cookie:
 *         name: cookie_consent
 *         lifetime: 15552000  # 6 Monate
 *         same_site: lax
 *         http_only: true
 */
final class CookieConsentStorageAdapter implements ConsentStorageInterface
{
    /**
     * @param CookieConfig $config DE: Cookie-Konfiguration | EN: Cookie configuration
     * @param string $policyVersion DE: Aktuelle Policy-Version | EN: Current policy version
     */
    public function __construct(
        private readonly CookieConfig $config,
        private readonly string $policyVersion,
    ) {
    }

    /**
     * DE: Laedt Consent-State aus dem Browser-Cookie.
     *     Prueft Policy-Version und gibt leeren State bei Mismatch.
     *
     * EN: Loads consent state from browser cookie.
     *     Checks policy version and returns empty state on mismatch.
     *
     * @param Request $request DE: HTTP-Request mit Cookies | EN: HTTP request with cookies
     * @return ConsentState DE: Geladener oder leerer State | EN: Loaded or empty state
     */
    public function load(Request $request): ConsentState
    {
        // DE: Cookie auslesen
        // EN: Read cookie
        $raw = $request->cookies->get($this->config->name);
        if (!is_string($raw) || $raw === '') {
            return ConsentState::empty($this->policyVersion);
        }

        // DE: JSON parsen
        // EN: Parse JSON
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ConsentState::empty($this->policyVersion);
        }

        // DE: Policy-Version pruefen - bei Mismatch ist alter Consent ungueltig
        // EN: Check policy version - on mismatch old consent is invalid
        $storedVersion = $data['version'] ?? null;
        if (!is_string($storedVersion) || $storedVersion !== $this->policyVersion) {
            return ConsentState::empty($this->policyVersion);
        }

        // DE: Praeferenzen extrahieren
        // EN: Extract preferences
        $preferences = $data['preferences'] ?? [];
        if (!is_array($preferences)) {
            $preferences = [];
        }

        // DE: Entscheidungszeitpunkt parsen
        // EN: Parse decision timestamp
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
     * DE: Speichert Consent-State als Browser-Cookie.
     *     Setzt Cookie-Header auf der Response.
     *
     * EN: Saves consent state as browser cookie.
     *     Sets cookie header on the response.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @param Response $response DE: HTTP-Response fuer Cookie-Header | EN: HTTP response for cookie header
     * @param ConsentState $state DE: Zu speichernder State | EN: State to save
     *
     * @throws \RuntimeException DE: Wenn JSON-Encoding fehlschlaegt | EN: If JSON encoding fails
     */
    public function save(Request $request, Response $response, ConsentState $state): void
    {
        // DE: State als JSON serialisieren
        // EN: Serialize state as JSON
        try {
            $payload = json_encode([
                'version' => $state->getPolicyVersion(),
                'preferences' => $state->getPreferences(),
                'decided_at' => $state->getDecidedAt()?->format(DATE_ATOM),
            ], JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Failed to encode consent cookie payload.', 0, $exception);
        }

        // DE: Wenn secure=null, automatisch HTTPS-Status des Requests verwenden.
        // EN: If secure=null, automatically use the request's HTTPS status.
        $secure = $this->config->secure ?? $request->isSecure();

        // DE: Cookie erstellen mit allen konfigurierten Optionen
        // EN: Create cookie with all configured options
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
     * DE: Berechnet das Ablaufdatum des Cookies.
     *
     * EN: Calculates the cookie expiration date.
     *
     * @return \DateTimeImmutable DE: Ablaufdatum | EN: Expiration date
     */
    private function getExpiration(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(sprintf('+%d seconds', $this->config->lifetime));
    }
}
