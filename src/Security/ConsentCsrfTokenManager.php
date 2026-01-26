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
 * Spezialisierter CSRF-Token-Manager fuer den Cookie-Consent-Endpoint.
 *     Verwendet Symfony's SameOriginCsrfTokenManager fuer session-lose
 *     CSRF-Validierung basierend auf Origin/Referer-Headern.
 *
 * Specialized CSRF token manager for the cookie consent endpoint.
 *     Uses Symfony's SameOriginCsrfTokenManager for session-less
 *     CSRF validation based on Origin/Referer headers.
 *
 * Why session-less?
 * - Consent must work before the session is allowed
 * - Session-based CSRF tokens would require a session cookie
 * - Same-origin validation is sufficient for this use case
 *
 * @example
 * // Generate token in Twig
 * <input type="hidden" name="csrf_token" value="{{ csrf_token('cookie_consent') }}">
 *
 * // Validate token in controller
 * $token = new CsrfToken('cookie_consent', $request->get('csrf_token'));
 * if (!$csrfManager->isTokenValid($token)) {
 *     throw new AccessDeniedHttpException('Invalid CSRF token');
 * }
 */
final class ConsentCsrfTokenManager implements CsrfTokenManagerInterface
{
    /**
     * The only allowed token ID for this manager.
     */
    public const TOKEN_ID = 'cookie_consent';

    /**
     * The underlying SameOrigin CSRF manager.
     */
    private SameOriginCsrfTokenManager $manager;

    /**
     * @param RequestStack          $requestStack Request stack for Origin/Referer
     * @param LoggerInterface|null  $logger Optional logger
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
     * Returns a CSRF token for the consent form.
     *
     * @param string $tokenId Must be 'cookie_consent'
     * @return CsrfToken The generated token
     *
     * @throws \InvalidArgumentException If tokenId invalid
     */
    public function getToken(string $tokenId): CsrfToken
    {
        $this->assertTokenId($tokenId);

        return $this->manager->getToken(self::TOKEN_ID);
    }

    /**
     * Generates a new CSRF token (invalidates old one).
     *
     * @param string $tokenId Must be 'cookie_consent'
     * @return CsrfToken The new token
     *
     * @throws \InvalidArgumentException If tokenId invalid
     */
    public function refreshToken(string $tokenId): CsrfToken
    {
        $this->assertTokenId($tokenId);

        return $this->manager->refreshToken(self::TOKEN_ID);
    }

    /**
     * Removes a CSRF token.
     *
     * @param string $tokenId Must be 'cookie_consent'
     * @return string|null The old token value or null
     *
     * @throws \InvalidArgumentException If tokenId invalid
     */
    public function removeToken(string $tokenId): ?string
    {
        $this->assertTokenId($tokenId);

        return $this->manager->removeToken(self::TOKEN_ID);
    }

    /**
     * Validates a CSRF token.
     *     Checks token ID and delegates to SameOriginCsrfTokenManager.
     *
     * @param CsrfToken $token Token to validate
     * @return bool true if valid
     */
    public function isTokenValid(CsrfToken $token): bool
    {
        // Only accept our token ID
        if ($token->getId() !== self::TOKEN_ID) {
            return false;
        }

        return $this->manager->isTokenValid($token);
    }

    /**
     * Ensures the token ID is 'cookie_consent'.
     *
     * @param string $tokenId ID to check
     *
     * @throws \InvalidArgumentException On invalid ID
     */
    private function assertTokenId(string $tokenId): void
    {
        if ($tokenId !== self::TOKEN_ID) {
            throw new \InvalidArgumentException('Invalid CSRF token id.');
        }
    }
}
