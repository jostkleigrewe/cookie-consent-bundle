<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

/**
 * DE: Waehlt den passenden Consent-Storage basierend auf der Konfiguration.
 * EN: Selects the appropriate consent storage based on configuration.
 */
final readonly class ConsentStorageFactory
{
    public function __construct(
        private CookieConsentStorageAdapter $cookieStorage,
        private ?DoctrineConsentStorageAdapter $doctrineStorage,
        private ?CombinedConsentStorageAdapter $combinedStorage,
    ) {
    }

    public function create(string $storage): ConsentStorageInterface
    {
        return match ($storage) {
            'doctrine' => $this->requireDoctrine($storage),
            'both' => $this->requireCombined($storage),
            default => $this->cookieStorage,
        };
    }

    private function requireDoctrine(string $storage): DoctrineConsentStorageAdapter
    {
        if ($this->doctrineStorage === null) {
            throw new \LogicException(sprintf(
                'cookie_consent.storage="%s" requires doctrine/dbal. Install doctrine/dbal or set storage="cookie".',
                $storage
            ));
        }

        return $this->doctrineStorage;
    }

    private function requireCombined(string $storage): CombinedConsentStorageAdapter
    {
        if ($this->combinedStorage === null) {
            throw new \LogicException(sprintf(
                'cookie_consent.storage="%s" requires doctrine/dbal. Install doctrine/dbal or set storage="cookie".',
                $storage
            ));
        }

        return $this->combinedStorage;
    }
}
