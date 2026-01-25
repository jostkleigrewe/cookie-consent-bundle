<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

/**
 * ConsentStorageFactory - Factory fuer Storage-Backend-Auswahl
 *
 * Waehlt das passende Storage-Backend basierend auf der Konfiguration.
 *     Wird vom DI-Container verwendet um den richtigen Adapter zu injizieren.
 *
 * Selects the appropriate storage backend based on configuration.
 *     Used by DI container to inject the correct adapter.
 *
 * Available backends:
 * - 'cookie': CookieConsentStorageAdapter (default)
 * - 'doctrine': DoctrineConsentStorageAdapter
 * - 'both': CombinedConsentStorageAdapter
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     storage: both  # 'cookie' | 'doctrine' | 'both'
 */
final readonly class ConsentStorageFactory
{
    /**
     * @param CookieConsentStorageAdapter $cookieStorage Cookie adapter
     * @param DoctrineConsentStorageAdapter|null $doctrineStorage Doctrine adapter (null if not installed)
     * @param CombinedConsentStorageAdapter|null $combinedStorage Combined adapter
     */
    public function __construct(
        private CookieConsentStorageAdapter $cookieStorage,
        private ?DoctrineConsentStorageAdapter $doctrineStorage,
        private ?CombinedConsentStorageAdapter $combinedStorage,
    ) {
    }

    /**
     * Creates the appropriate storage adapter based on configuration.
     *
     * @param string $storage Configured storage type
     * @return ConsentStorageInterface The appropriate adapter
     *
     * @throws \LogicException If Doctrine required but not installed
     */
    public function create(string $storage): ConsentStorageInterface
    {
        return match ($storage) {
            'doctrine' => $this->requireDoctrine($storage),
            'both' => $this->requireCombined($storage),
            default => $this->cookieStorage,
        };
    }

    /**
     * Returns Doctrine adapter or throws exception.
     *
     * @param string $storage Configured value (for error message)
     * @return DoctrineConsentStorageAdapter
     *
     * @throws \LogicException If Doctrine not installed
     */
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

    /**
     * Returns Combined adapter or throws exception.
     *
     * @param string $storage Configured value (for error message)
     * @return CombinedConsentStorageAdapter
     *
     * @throws \LogicException If Doctrine not installed
     */
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
