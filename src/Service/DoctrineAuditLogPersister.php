<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Jostkleigrewe\CookieConsentBundle\Config\LoggingConfig;
use Jostkleigrewe\CookieConsentBundle\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Storage\ConsentIdProvider;
use Jostkleigrewe\CookieConsentBundle\Entity\CookieConsent;
use Jostkleigrewe\CookieConsentBundle\Entity\CookieConsentLog;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DoctrineAuditLogPersister implements AuditLogPersisterInterface
{
    /**
     * @param EntityManagerInterface $entityManager Doctrine entity manager
     * @param ConsentIdProvider $idProvider Consent ID provider
     * @param LoggingConfig $logging Logging configuration DTO
     * @param object|null $tokenStorage Security token storage (optional)
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConsentIdProvider $idProvider,
        private readonly LoggingConfig $logging,
        private readonly ?object $tokenStorage = null,
    ) {
    }

    public function persist(
        string $action,
        ConsentState $state,
        ConsentPolicy $policy,
        Request $request,
        Response $response
    ): void {
        $consentId = $this->idProvider->ensureId($request, $response);

        /** @var CookieConsent|null $consent */
        $consent = $this->entityManager->find(CookieConsent::class, $consentId);
        if ($consent === null) {
            $consent = new CookieConsent($consentId);
        }

        $consent->setPreferences($state->getPreferences());
        $consent->setPolicyVersion($state->getPolicyVersion());
        $consent->setDecidedAt($state->getDecidedAt());

        $decidedAt = $state->getDecidedAt() ?? new \DateTimeImmutable('now');
        $log = new CookieConsentLog(
            $consent,
            $action,
            $state->getPreferences(),
            $policy->getPolicyVersion(),
            $decidedAt
        );

        $ipAddress = $request->getClientIp();
        if ($this->logging->anonymizeIp && $ipAddress !== null) {
            $ipAddress = IpUtils::anonymize($ipAddress);
        }

        $log->setIpAddress($ipAddress);
        $log->setUserAgent($request->headers->get('User-Agent'));
        $log->setReferrer($request->headers->get('Referer'));
        $log->setRequestUri($request->getRequestUri());

        $userId = $this->resolveUserId();
        if ($userId !== null) {
            $log->setUserId($userId);
        }

        $this->entityManager->persist($consent);
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    private function resolveUserId(): ?string
    {
        if ($this->tokenStorage === null || !method_exists($this->tokenStorage, 'getToken')) {
            return null;
        }

        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return null;
        }

        $user = $token->getUser();
        if (!is_object($user)) {
            return null;
        }

        if (method_exists($user, 'getUserIdentifier')) {
            $identifier = $user->getUserIdentifier();
            return $identifier !== '' ? (string) $identifier : null;
        }

        if (method_exists($user, 'getUsername')) {
            $identifier = $user->getUsername();
            return $identifier !== '' ? (string) $identifier : null;
        }

        if (method_exists($user, '__toString')) {
            $identifier = (string) $user;
            return $identifier !== '' ? $identifier : null;
        }

        return null;
    }
}
