<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use Doctrine\ORM\EntityManagerInterface;
use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Entity\CookieConsent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DoctrineOrmConsentStorageAdapter - ORM-basierter Consent-Storage
 *
 * Persistiert den aktuellen Consent als Entity in der Datenbank.
 *     Nutzt die Cookie-Consent-ID als Primary Key.
 *
 * Persists the current consent as an ORM entity in the database.
 *     Uses the consent ID cookie as primary key.
 */
final class DoctrineOrmConsentStorageAdapter implements ConsentStorageInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConsentIdProvider $idProvider,
        private readonly ConsentPolicy $policy,
    ) {
    }

    public function load(Request $request): ConsentState
    {
        $id = $this->idProvider->getId($request);
        if ($id === null) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        /** @var CookieConsent|null $consent */
        $consent = $this->entityManager->find(CookieConsent::class, $id);
        if ($consent === null) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        if ($consent->getPolicyVersion() !== $this->policy->getPolicyVersion()) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        return new ConsentState(
            $consent->getPreferences(),
            $this->policy->getPolicyVersion(),
            $consent->getDecidedAt()
        );
    }

    public function save(Request $request, Response $response, ConsentState $state): void
    {
        $id = $this->idProvider->ensureId($request, $response);

        /** @var CookieConsent|null $consent */
        $consent = $this->entityManager->find(CookieConsent::class, $id);
        if ($consent === null) {
            $consent = new CookieConsent($id);
        }

        $consent->setPreferences($state->getPreferences());
        $consent->setPolicyVersion($state->getPolicyVersion());
        $consent->setDecidedAt($state->getDecidedAt());

        $this->entityManager->persist($consent);
        $this->entityManager->flush();
    }
}
