<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

/**
 * ConsentStorageFactory - Factory fuer Storage-Backend-Auswahl
 *
 * DE: Waehlt das passende Storage-Backend basierend auf der Konfiguration.
 *     Wird vom DI-Container verwendet um den richtigen Adapter zu injizieren.
 *
 * EN: Selects the appropriate storage backend based on configuration.
 *     Used by DI container to inject the correct adapter.
 *
 * Verfuegbare Backends / Available backends:
 * - 'cookie': CookieConsentStorageAdapter (Standard)
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
     * @param CookieConsentStorageAdapter $cookieStorage DE: Cookie-Adapter | EN: Cookie adapter
     * @param DoctrineConsentStorageAdapter|null $doctrineStorage DE: Doctrine-Adapter (null wenn nicht installiert)
     *                                                             EN: Doctrine adapter (null if not installed)
     * @param CombinedConsentStorageAdapter|null $combinedStorage DE: Combined-Adapter
     *                                                             EN: Combined adapter
     */
    public function __construct(
        private CookieConsentStorageAdapter $cookieStorage,
        private ?DoctrineConsentStorageAdapter $doctrineStorage,
        private ?CombinedConsentStorageAdapter $combinedStorage,
    ) {
    }

    /**
     * DE: Erstellt den passenden Storage-Adapter basierend auf der Konfiguration.
     *
     * EN: Creates the appropriate storage adapter based on configuration.
     *
     * @param string $storage DE: Konfigurierter Storage-Typ | EN: Configured storage type
     * @return ConsentStorageInterface DE: Der passende Adapter | EN: The appropriate adapter
     *
     * @throws \LogicException DE: Wenn Doctrine benoetigt aber nicht installiert
     *                         EN: If Doctrine required but not installed
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
     * DE: Gibt Doctrine-Adapter zurueck oder wirft Exception.
     *
     * EN: Returns Doctrine adapter or throws exception.
     *
     * @param string $storage DE: Konfigurierter Wert (fuer Fehlermeldung)
     *                        EN: Configured value (for error message)
     * @return DoctrineConsentStorageAdapter
     *
     * @throws \LogicException DE: Wenn Doctrine nicht installiert
     *                         EN: If Doctrine not installed
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
     * DE: Gibt Combined-Adapter zurueck oder wirft Exception.
     *
     * EN: Returns Combined adapter or throws exception.
     *
     * @param string $storage DE: Konfigurierter Wert (fuer Fehlermeldung)
     *                        EN: Configured value (for error message)
     * @return CombinedConsentStorageAdapter
     *
     * @throws \LogicException DE: Wenn Doctrine nicht installiert
     *                         EN: If Doctrine not installed
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
