<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConsentIdProvider
{
    public function __construct(private readonly IdentifierCookieConfig $config)
    {
    }

    public function getId(Request $request): ?string
    {
        $id = $request->cookies->get($this->config->name);
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
            $this->config->name,
            $id,
            $this->getExpiration(),
            $this->config->path,
            $this->config->domain,
            $this->config->secure,
            $this->config->httpOnly,
            false,
            $this->config->sameSite
        );

        $response->headers->setCookie($cookie);

        return $id;
    }

    private function getExpiration(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(sprintf('+%d seconds', $this->config->lifetime));
    }
}
