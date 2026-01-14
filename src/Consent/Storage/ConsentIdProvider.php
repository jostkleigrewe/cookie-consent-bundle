<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use DateTimeImmutable;
use Jostkleigrewe\CookieConsentBundle\Consent\Config\IdentifierCookieConfig;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DE: Liefert eine stabile Consent-ID fuer DB-Storage.
 * EN: Provides a stable consent ID for DB storage.
 */
final readonly class ConsentIdProvider
{
    public function __construct(
        private IdentifierCookieConfig $identifierConfig
    )
    {
    }

    public function getId(Request $request): ?string
    {
        $id = $request->cookies->get($this->identifierConfig->name);
        if (!is_string($id) || $id === '') {
            return null;
        }

        return $id;
    }

    public function ensureId(Request $request, Response $response): string
    {
        $existing = $this->getId($request);
        if ($existing !== null) {
            return $existing;
        }

        $id = bin2hex(random_bytes(16));
        $cookie = Cookie::create(
            $this->identifierConfig->name,
            $id,
            $this->getExpiration(),
            $this->identifierConfig->path,
            $this->identifierConfig->domain,
            $this->identifierConfig->secure,
            $this->identifierConfig->httpOnly,
            false,
            $this->identifierConfig->sameSite
        );

        $response->headers->setCookie($cookie);

        return $id;
    }

    private function getExpiration(): DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('+%d seconds', $this->identifierConfig->lifetime));
    }
}
