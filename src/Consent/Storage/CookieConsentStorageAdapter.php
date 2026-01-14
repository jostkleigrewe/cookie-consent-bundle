<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use Jostkleigrewe\CookieConsentBundle\Consent\Config\CookieConfig;
use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DE: Speichert Consent im Browser-Cookie.
 * EN: Stores consent in a browser cookie.
 */
final class CookieConsentStorageAdapter implements ConsentStorageInterface
{
    public function __construct(
        private readonly CookieConfig $config,
        private readonly string $policyVersion,
    ) {
    }

    public function load(Request $request): ConsentState
    {
        $raw = $request->cookies->get($this->config->name);
        if (!is_string($raw) || $raw === '') {
            return ConsentState::empty($this->policyVersion);
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ConsentState::empty($this->policyVersion);
        }

        $storedVersion = $data['version'] ?? null;
        if (!is_string($storedVersion) || $storedVersion !== $this->policyVersion) {
            return ConsentState::empty($this->policyVersion);
        }

        $preferences = $data['preferences'] ?? [];
        if (!is_array($preferences)) {
            $preferences = [];
        }

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

    public function save(Request $request, Response $response, ConsentState $state): void
    {
        try {
            $payload = json_encode([
                'version' => $state->getPolicyVersion(),
                'preferences' => $state->getPreferences(),
                'decided_at' => $state->getDecidedAt()?->format(DATE_ATOM),
            ], JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Failed to encode consent cookie payload.', 0, $exception);
        }

        $cookie = Cookie::create(
            $this->config->name,
            $payload,
            $this->getExpiration(),
            $this->config->path,
            $this->config->domain,
            $this->config->secure,
            $this->config->httpOnly,
            false,
            $this->config->sameSite
        );

        $response->headers->setCookie($cookie);
    }

    private function getExpiration(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(sprintf('+%d seconds', $this->config->lifetime));
    }
}
