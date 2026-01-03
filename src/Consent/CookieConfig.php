<?php

declare(strict_types=1);

namespace JostKleigrewe\CookieConsentBundle\Consent;

final class CookieConfig
{
    public function __construct(
        public readonly string $name,
        public readonly int $lifetime,
        public readonly string $path,
        public readonly ?string $domain,
        public readonly ?bool $secure,
        public readonly string $sameSite,
        public readonly bool $httpOnly,
    ) {
    }

    /**
     * @param array{name: string, lifetime: int, path: string, domain: ?string, secure: ?bool, same_site: string, http_only: bool} $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['name'],
            $config['lifetime'],
            $config['path'],
            $config['domain'],
            $config['secure'],
            $config['same_site'],
            $config['http_only']
        );
    }
}
