<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use DateTimeImmutable;
use Jostkleigrewe\CookieConsentBundle\Consent\Config\IdentifierCookieConfig;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ConsentIdProvider - Generiert und verwaltet stabile Consent-IDs
 *
 * Liefert eine eindeutige, stabile ID fuer jeden Nutzer.
 *     Diese ID wird in einem separaten Cookie gespeichert und dient
 *     als Schluessel fuer die Datenbank-Speicherung (Doctrine-Backend).
 *
 * Provides a unique, stable ID for each user.
 *     This ID is stored in a separate cookie and serves
 *     as the key for database storage (Doctrine backend).
 *
 * The ID is a 32-character hex string (128-bit random):
 * - Cryptographically secure (random_bytes)
 * - Collision-resistant
 * - Privacy-friendly (no PII)
 *
 * @example
 * // Get ID from request (null if not present)
 * $id = $idProvider->getId($request);
 *
 * // Ensure ID (creates new one if needed)
 * $id = $idProvider->ensureId($request, $response);
 */
final readonly class ConsentIdProvider
{
    /**
     * @param IdentifierCookieConfig $identifierConfig Cookie configuration for ID
     */
    public function __construct(
        private IdentifierCookieConfig $identifierConfig
    ) {
    }

    /**
     * Gets the consent ID from the request cookie.
     *     Returns null if no ID present.
     *
     * @param Request $request HTTP request
     * @return string|null The ID or null
     */
    public function getId(Request $request): ?string
    {
        $id = $request->cookies->get($this->identifierConfig->name);
        if (!is_string($id) || $id === '') {
            return null;
        }

        return $id;
    }

    /**
     * Ensures an ID exists.
     *     Returns existing ID or creates new one.
     *     Sets cookie on response if new ID is created.
     *
     * @param Request $request HTTP request
     * @param Response $response HTTP response for cookie
     * @return string The (new or existing) ID
     */
    public function ensureId(Request $request, Response $response): string
    {
        // Use existing ID if present
        $existing = $this->getId($request);
        if ($existing !== null) {
            return $existing;
        }

        $existing = $this->getIdFromResponse($response);
        if ($existing !== null) {
            return $existing;
        }

        // Generate new cryptographically secure ID (32 hex chars)
        $id = bin2hex(random_bytes(16));

        // If secure=null, automatically use the request's HTTPS status.
        $secure = $this->identifierConfig->secure ?? $request->isSecure();

        // Create and set cookie
        $cookie = Cookie::create(
            $this->identifierConfig->name,
            $id,
            $this->getExpiration(),
            $this->identifierConfig->path,
            $this->identifierConfig->domain,
            $secure,
            $this->identifierConfig->httpOnly,
            false,
            $this->identifierConfig->sameSite
        );

        $response->headers->setCookie($cookie);

        return $id;
    }

    private function getIdFromResponse(Response $response): ?string
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() !== $this->identifierConfig->name) {
                continue;
            }

            $value = $cookie->getValue();
            if ($value === '') {
                return null;
            }

            return $value;
        }

        return null;
    }

    /**
     * Calculates the ID cookie expiration date.
     *
     * @return DateTimeImmutable Expiration date
     */
    private function getExpiration(): DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('+%d seconds', $this->identifierConfig->lifetime));
    }
}
