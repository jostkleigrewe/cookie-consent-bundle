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
 * DE: Liefert eine eindeutige, stabile ID fuer jeden Nutzer.
 *     Diese ID wird in einem separaten Cookie gespeichert und dient
 *     als Schluessel fuer die Datenbank-Speicherung (Doctrine-Backend).
 *
 * EN: Provides a unique, stable ID for each user.
 *     This ID is stored in a separate cookie and serves
 *     as the key for database storage (Doctrine backend).
 *
 * Die ID ist ein 32-Zeichen Hex-String (128-bit Random):
 * - Kryptographisch sicher (random_bytes)
 * - Keine Kollisionsgefahr
 * - Datenschutzfreundlich (keine PII)
 *
 * @example
 * // DE: ID aus Request holen (null wenn nicht vorhanden)
 * // EN: Get ID from request (null if not present)
 * $id = $idProvider->getId($request);
 *
 * // DE: ID sicherstellen (erstellt neue wenn noetig)
 * // EN: Ensure ID (creates new one if needed)
 * $id = $idProvider->ensureId($request, $response);
 */
final readonly class ConsentIdProvider
{
    /**
     * @param IdentifierCookieConfig $identifierConfig DE: Cookie-Konfiguration fuer ID
     *                                                  EN: Cookie configuration for ID
     */
    public function __construct(
        private IdentifierCookieConfig $identifierConfig
    ) {
    }

    /**
     * DE: Holt die Consent-ID aus dem Request-Cookie.
     *     Gibt null zurueck wenn keine ID vorhanden.
     *
     * EN: Gets the consent ID from the request cookie.
     *     Returns null if no ID present.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @return string|null DE: Die ID oder null | EN: The ID or null
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
     * DE: Stellt sicher dass eine ID existiert.
     *     Gibt existierende ID zurueck oder erstellt neue.
     *     Setzt Cookie auf Response wenn neue ID erstellt wird.
     *
     * EN: Ensures an ID exists.
     *     Returns existing ID or creates new one.
     *     Sets cookie on response if new ID is created.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @param Response $response DE: HTTP-Response fuer Cookie | EN: HTTP response for cookie
     * @return string DE: Die (neue oder existierende) ID | EN: The (new or existing) ID
     */
    public function ensureId(Request $request, Response $response): string
    {
        // DE: Existierende ID verwenden wenn vorhanden
        // EN: Use existing ID if present
        $existing = $this->getId($request);
        if ($existing !== null) {
            return $existing;
        }

        // DE: Neue kryptographisch sichere ID generieren (32 Hex-Zeichen)
        // EN: Generate new cryptographically secure ID (32 hex chars)
        $id = bin2hex(random_bytes(16));

        // DE: Wenn secure=null, automatisch HTTPS-Status des Requests verwenden.
        // EN: If secure=null, automatically use the request's HTTPS status.
        $secure = $this->identifierConfig->secure ?? $request->isSecure();

        // DE: Cookie erstellen und setzen
        // EN: Create and set cookie
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

    /**
     * DE: Berechnet das Ablaufdatum des ID-Cookies.
     *
     * EN: Calculates the ID cookie expiration date.
     *
     * @return DateTimeImmutable DE: Ablaufdatum | EN: Expiration date
     */
    private function getExpiration(): DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('+%d seconds', $this->identifierConfig->lifetime));
    }
}
