<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DE: Audit-Log für Consent-Änderungen (Action, IP, UserAgent, Zeitstempel).
 * EN: Audit log for consent changes (action, IP, user agent, timestamp).
 */
#[ORM\Entity]
#[ORM\Table(name: 'cookie_consent_log')]
#[ORM\Index(columns: ['consent_id'], name: 'idx_cookie_consent_log_consent_id')]
#[ORM\Index(columns: ['decided_at'], name: 'idx_cookie_consent_log_decided_at')]
final class CookieConsentLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CookieConsent::class)]
    #[ORM\JoinColumn(name: 'consent_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private CookieConsent $consent;

    #[ORM\Column(type: 'string', length: 32)]
    private string $action;

    /** @var array<string, array{allowed: bool, vendors: array<string, bool>}> */
    #[ORM\Column(type: 'json')]
    private array $preferences = [];

    #[ORM\Column(name: 'policy_version', type: 'string', length: 16)]
    private string $policyVersion;

    #[ORM\Column(name: 'decided_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $decidedAt;

    #[ORM\Column(name: 'ip_address', type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(name: 'user_agent', type: 'text', nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(name: 'request_uri', type: 'string', length: 2048, nullable: true)]
    private ?string $requestUri = null;

    #[ORM\Column(name: 'referrer', type: 'string', length: 2048, nullable: true)]
    private ?string $referrer = null;

    #[ORM\Column(name: 'user_id', type: 'string', length: 255, nullable: true)]
    private ?string $userId = null;

    /**
     * @param array<string, array{allowed: bool, vendors: array<string, bool>}> $preferences
     */
    public function __construct(CookieConsent $consent, string $action, array $preferences, string $policyVersion, \DateTimeImmutable $decidedAt)
    {
        $this->consent = $consent;
        $this->action = $action;
        $this->preferences = $preferences;
        $this->policyVersion = $policyVersion;
        $this->decidedAt = $decidedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConsent(): CookieConsent
    {
        return $this->consent;
    }

    public function setConsent(CookieConsent $consent): void
    {
        $this->consent = $consent;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /** @return array<string, array{allowed: bool, vendors: array<string, bool>}> */
    public function getPreferences(): array
    {
        return $this->preferences;
    }

    /** @param array<string, array{allowed: bool, vendors: array<string, bool>}> $preferences */
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

    public function getDecidedAt(): \DateTimeImmutable
    {
        return $this->decidedAt;
    }

    public function setDecidedAt(\DateTimeImmutable $decidedAt): void
    {
        $this->decidedAt = $decidedAt;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function getRequestUri(): ?string
    {
        return $this->requestUri;
    }

    public function setRequestUri(?string $requestUri): void
    {
        $this->requestUri = $requestUri;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    public function setReferrer(?string $referrer): void
    {
        $this->referrer = $referrer;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }
}
