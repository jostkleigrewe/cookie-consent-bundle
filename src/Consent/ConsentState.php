<?php

declare(strict_types=1);

namespace JostKleigrewe\CookieConsentBundle\Consent;

final class ConsentState
{
    /**
     * @param array<string, bool> $preferences
     */
    public function __construct(
        private array $preferences,
        private readonly string $policyVersion,
        private ?\DateTimeImmutable $decidedAt
    ) {
    }

    public static function empty(string $policyVersion): self
    {
        return new self([], $policyVersion, null);
    }

    public function hasDecision(): bool
    {
        return $this->decidedAt !== null;
    }

    /**
     * @return array<string, bool>
     */
    public function getPreferences(): array
    {
        return $this->preferences;
    }

    public function isAllowed(string $category): bool
    {
        return $this->preferences[$category] ?? false;
    }

    public function getPolicyVersion(): string
    {
        return $this->policyVersion;
    }

    public function getDecidedAt(): ?\DateTimeImmutable
    {
        return $this->decidedAt;
    }

    /**
     * @param array<string, bool> $preferences
     */
    public function withPreferences(array $preferences): self
    {
        return new self($preferences, $this->policyVersion, new \DateTimeImmutable('now'));
    }
}
