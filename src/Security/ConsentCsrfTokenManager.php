<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\SameOriginCsrfTokenManager;

/**
 * ConsentCsrfTokenManager - CSRF-Schutz fuer Consent-Endpoint
 *
 * DE: Spezialisierter CSRF-Token-Manager fuer den Cookie-Consent-Endpoint.
 *     Verwendet Symfony's SameOriginCsrfTokenManager fuer session-lose
 *     CSRF-Validierung basierend auf Origin/Referer-Headern.
 *
 * EN: Specialized CSRF token manager for the cookie consent endpoint.
 *     Uses Symfony's SameOriginCsrfTokenManager for session-less
 *     CSRF validation based on Origin/Referer headers.
 *
 * Warum session-los? / Why session-less?
 * - Consent muss funktionieren BEVOR die Session erlaubt ist
 * - Session-basierte CSRF-Tokens wuerden Session-Cookie erfordern
 * - SameOrigin-Validierung reicht fuer diesen Use-Case
 *
 * @example
 * // DE: Token in Twig generieren
 * // EN: Generate token in Twig
 * <input type="hidden" name="csrf_token" value="{{ csrf_token('cookie_consent') }}">
 *
 * // DE: Token im Controller validieren
 * // EN: Validate token in controller
 * $token = new CsrfToken('cookie_consent', $request->get('csrf_token'));
 * if (!$csrfManager->isTokenValid($token)) {
 *     throw new AccessDeniedHttpException('Invalid CSRF token');
 * }
 */
final class ConsentCsrfTokenManager implements CsrfTokenManagerInterface
{
    /**
     * DE: Die einzige erlaubte Token-ID fuer diesen Manager.
     * EN: The only allowed token ID for this manager.
     */
    public const TOKEN_ID = 'cookie_consent';

    /**
     * DE: Der zugrunde liegende SameOrigin CSRF-Manager.
     * EN: The underlying SameOrigin CSRF manager.
     */
    private SameOriginCsrfTokenManager $manager;

    /**
     * @param RequestStack          $requestStack   DE: Request-Stack fuer Origin/Referer
     *                                              EN: Request stack for Origin/Referer
     * @param LoggerInterface|null  $logger         DE: Optionaler Logger | EN: Optional logger
     */
    public function __construct(RequestStack $requestStack, ?LoggerInterface $logger = null)
    {
        $this->manager = new SameOriginCsrfTokenManager(
            $requestStack,
            $logger,
            null,
            [self::TOKEN_ID]
        );
    }

    /**
     * DE: Gibt ein CSRF-Token fuer die Consent-Form zurueck.
     *
     * EN: Returns a CSRF token for the consent form.
     *
     * @param string $tokenId DE: Muss 'cookie_consent' sein | EN: Must be 'cookie_consent'
     * @return CsrfToken DE: Das generierte Token | EN: The generated token
     *
     * @throws \InvalidArgumentException DE: Wenn tokenId ungueltig | EN: If tokenId invalid
     */
    public function getToken(string $tokenId): CsrfToken
    {
        $this->assertTokenId($tokenId);

        return $this->manager->getToken(self::TOKEN_ID);
    }

    /**
     * DE: Generiert ein neues CSRF-Token (invalidiert altes).
     *
     * EN: Generates a new CSRF token (invalidates old one).
     *
     * @param string $tokenId DE: Muss 'cookie_consent' sein | EN: Must be 'cookie_consent'
     * @return CsrfToken DE: Das neue Token | EN: The new token
     *
     * @throws \InvalidArgumentException DE: Wenn tokenId ungueltig | EN: If tokenId invalid
     */
    public function refreshToken(string $tokenId): CsrfToken
    {
        $this->assertTokenId($tokenId);

        return $this->manager->refreshToken(self::TOKEN_ID);
    }

    /**
     * DE: Entfernt ein CSRF-Token.
     *
     * EN: Removes a CSRF token.
     *
     * @param string $tokenId DE: Muss 'cookie_consent' sein | EN: Must be 'cookie_consent'
     * @return string|null DE: Der alte Token-Wert oder null | EN: The old token value or null
     *
     * @throws \InvalidArgumentException DE: Wenn tokenId ungueltig | EN: If tokenId invalid
     */
    public function removeToken(string $tokenId): ?string
    {
        $this->assertTokenId($tokenId);

        return $this->manager->removeToken(self::TOKEN_ID);
    }

    /**
     * DE: Validiert ein CSRF-Token.
     *     Prueft Token-ID und delegiert an SameOriginCsrfTokenManager.
     *
     * EN: Validates a CSRF token.
     *     Checks token ID and delegates to SameOriginCsrfTokenManager.
     *
     * @param CsrfToken $token DE: Zu validierendes Token | EN: Token to validate
     * @return bool DE: true wenn gueltig | EN: true if valid
     */
    public function isTokenValid(CsrfToken $token): bool
    {
        // DE: Nur unser Token-ID akzeptieren
        // EN: Only accept our token ID
        if ($token->getId() !== self::TOKEN_ID) {
            return false;
        }

        return $this->manager->isTokenValid($token);
    }

    /**
     * DE: Stellt sicher dass die Token-ID 'cookie_consent' ist.
     *
     * EN: Ensures the token ID is 'cookie_consent'.
     *
     * @param string $tokenId DE: Zu pruefende ID | EN: ID to check
     *
     * @throws \InvalidArgumentException DE: Bei ungueltiger ID | EN: On invalid ID
     */
    private function assertTokenId(string $tokenId): void
    {
        if ($tokenId !== self::TOKEN_ID) {
            throw new \InvalidArgumentException('Invalid CSRF token id.');
        }
    }
}
