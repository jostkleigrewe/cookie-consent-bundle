<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cookie_consent')]
class CookieConsent
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 32)]
    private string $id;

    /** @var array<string, bool> */
    #[ORM\Column(type: 'json')]
    private array $preferences = [];

    #[ORM\Column(name: 'policy_version', type: 'string', length: 16)]
    private string $policyVersion;

    #[ORM\Column(name: 'decided_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $decidedAt = null;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->policyVersion = '1';
    }

    public function getId(): string
    {
        return $this->id;
    }

    /** @return array<string, bool> */
    public function getPreferences(): array
    {
        return $this->preferences;
    }

    /** @param array<string, bool> $preferences */
    public function setPreferences(array $preferences): void
    {
        $this->preferences = $preferences;
    }

    public function getPolicyVersion(): string
    {
        return $this->policyVersion;
    }

    public function setPolicyVersion(string $policyVersion): void
    {
        $this->policyVersion = $policyVersion;
    }

    public function getDecidedAt(): ?\DateTimeImmutable
    {
        return $this->decidedAt;
    }

    public function setDecidedAt(?\DateTimeImmutable $decidedAt): void
    {
        $this->decidedAt = $decidedAt;
    }
}
