<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\SameOriginCsrfTokenManager;

/**
 * DE: CSRF-Manager fuer den Consent-Endpoint mit Same-Origin-Validierung.
 * EN: CSRF manager for the consent endpoint using same-origin validation.
 */
final class ConsentCsrfTokenManager implements CsrfTokenManagerInterface
{
    public const TOKEN_ID = 'cookie_consent';

    private SameOriginCsrfTokenManager $manager;

    public function __construct(RequestStack $requestStack, ?LoggerInterface $logger = null)
    {
        $this->manager = new SameOriginCsrfTokenManager(
            $requestStack,
            $logger,
            null,
            [self::TOKEN_ID]
        );
    }

    public function getToken(string $tokenId): CsrfToken
    {
        $this->assertTokenId($tokenId);

        return $this->manager->getToken(self::TOKEN_ID);
    }

    public function refreshToken(string $tokenId): CsrfToken
    {
        $this->assertTokenId($tokenId);

        return $this->manager->refreshToken(self::TOKEN_ID);
    }

    public function removeToken(string $tokenId): ?string
    {
        $this->assertTokenId($tokenId);

        return $this->manager->removeToken(self::TOKEN_ID);
    }

    public function isTokenValid(CsrfToken $token): bool
    {
        if ($token->getId() !== self::TOKEN_ID) {
            return false;
        }

        return $this->manager->isTokenValid($token);
    }

    private function assertTokenId(string $tokenId): void
    {
        if ($tokenId !== self::TOKEN_ID) {
            throw new \InvalidArgumentException('Invalid CSRF token id.');
        }
    }
}
