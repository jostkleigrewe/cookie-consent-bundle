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
 * - 'doctrine': Doctrine ORM adapter (preferred) or DBAL adapter
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
     * @param DoctrineOrmConsentStorageAdapter|null $ormStorage Doctrine ORM adapter (null if not installed)
     * @param DoctrineConsentStorageAdapter|null $dbalStorage Doctrine DBAL adapter (null if not installed)
     * @param CombinedConsentStorageAdapter|null $combinedStorage Combined adapter
     */
    public function __construct(
        private CookieConsentStorageAdapter $cookieStorage,
        private ?DoctrineOrmConsentStorageAdapter $ormStorage = null,
        private ?DoctrineConsentStorageAdapter $dbalStorage = null,
        private ?CombinedConsentStorageAdapter $combinedStorage = null,
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
     * @return ConsentStorageInterface
     *
     * @throws \LogicException If Doctrine not installed
     */
    private function requireDoctrine(string $storage): ConsentStorageInterface
    {
        $adapter = $this->ormStorage ?? $this->dbalStorage;
        if ($adapter === null) {
            throw new \LogicException(sprintf(
                'cookie_consent.storage="%s" requires doctrine/orm or doctrine/dbal. Install Doctrine or set storage="cookie".',
                $storage
            ));
        }

        return $adapter;
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
                'cookie_consent.storage="%s" requires doctrine/orm or doctrine/dbal. Install Doctrine or set storage="cookie".',
                $storage
            ));
        }

        return $this->combinedStorage;
    }
}
