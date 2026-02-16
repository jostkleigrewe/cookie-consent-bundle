<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * CookieConsentBundle - DSGVO/GDPR Cookie Consent Bundle
 *
 * Symfony Bundle für DSGVO-konforme Cookie-Zustimmung.
 *     Stellt Modal, Storage-Backends, Event-System und Twig-Integration bereit.
 *
 * Symfony bundle for GDPR-compliant cookie consent.
 *     Provides modal, storage backends, event system, and Twig integration.
 *     Supports cookie-only, Doctrine DB, or combined storage.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     policy_version: '1.0'
 *     storage: cookie  # or 'doctrine' or 'both'
 *     categories:
 *         necessary:
 *             label: 'Necessary'
 *             required: true
 *         analytics:
 *             label: 'Analytics'
 *             default: false
 *
 * @see https://github.com/jostkleigrewe/cookie-consent-bundle
 */
final class CookieConsentBundle extends AbstractBundle
{
    /**
     * DE: Gibt den Pfad zum Bundle-Root zurück.
     * EN: Returns the path to the bundle root.
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * DE: Lädt das Konfigurations-Schema des Bundles.
     *     Definiert alle verfügbaren Konfigurationsoptionen.
     * EN: Loads the bundle configuration schema definition.
     *     Defines all available configuration options.
     *
     * @param DefinitionConfigurator $definition Schema configurator
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    /**
     * DE: Lädt Services und setzt Parameter basierend auf der Konfiguration.
     *     Prüft ob Doctrine verfügbar ist wenn DB-Storage konfiguriert wurde.
     * EN: Loads services and sets parameters based on configuration.
     *     Checks if Doctrine is available when DB storage is configured.
     *
     * @param array<string, mixed> $config Resolved bundle configuration
     * @param ContainerConfigurator $container Container configurator
     * @param ContainerBuilder $builder Container builder
     *
     * @throws \LogicException If Doctrine is missing but DB storage is configured
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // DE: Parameter für Services setzen. Config-Arrays werden als Ganzes übergeben
        //     und über Config-DTOs (z.B. UiConfig, LoggingConfig) typsicher verarbeitet.
        // EN: Set parameters for services. Config arrays are passed as-is
        //     and processed type-safely via Config DTOs (e.g. UiConfig, LoggingConfig).
        foreach ($config as $key => $value) {
            $container->parameters()->set('cookie_consent.' . $key, $value);

            // DE: Nested Arrays auch als einzelne Parameter setzen (für services.php).
            //     Symfony unterstützt keine %param.nested.key% Notation.
            // EN: Also set nested arrays as individual parameters (for services.php).
            //     Symfony does not support %param.nested.key% notation.
            if (\is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    // DE: Nur skalare Werte als Parameter (keine tiefen Arrays).
                    // EN: Only scalar values as parameters (no deep arrays).
                    if (!\is_array($nestedValue)) {
                        $container->parameters()->set(
                            sprintf('cookie_consent.%s.%s', $key, $nestedKey),
                            $nestedValue
                        );
                    }
                }
            }
        }

        // Load service definitions
        $loader = new PhpFileLoader($builder, new FileLocator($this->getPath() . '/config'));
        $loader->load('services.php');

        // Check Doctrine dependency for DB storage
        $storage = $config['storage'] ?? 'cookie';

        $needsDoctrine = in_array($storage, ['doctrine', 'both'], true);
        if ($needsDoctrine && !class_exists(\Doctrine\DBAL\Connection::class)) {
            throw new \LogicException(sprintf(
                'cookie_consent.storage="%s" requires doctrine/dbal or doctrine/orm. Install Doctrine or set storage="cookie".',
                $storage
            ));
        }
    }

    /**
     * Registers Twig template path and AssetMapper path with other bundles.
     *     Executed BEFORE loadExtension (prepend).
     *
     * DE: Registriert Twig-Namespace und optional AssetMapper-Pfad.
     * EN: Registers Twig namespace and optionally AssetMapper path.
     *
     * @param ContainerConfigurator $container Container configurator
     * @param ContainerBuilder $builder Container builder
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // DE: @CookieConsent Twig-Namespace immer registrieren
        // EN: Always register @CookieConsent Twig namespace
        $container->extension('twig', [
            'paths' => [
                $this->getPath() . '/templates' => 'CookieConsent',
            ],
        ]);

        // DE: AssetMapper nur registrieren wenn verfügbar
        // EN: Only register AssetMapper if available
        if (!$this->isAssetMapperAvailable($builder)) {
            return;
        }

        $builder->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    $this->getPath() . '/assets/dist' => '@jostkleigrewe/cookie-consent-bundle',
                ],
            ],
        ]);
    }

    /**
     * Checks if Symfony AssetMapper is available in the host application.
     *
     * DE: Prüft ob AssetMapper verfügbar ist (Interface existiert + FrameworkBundle config).
     * EN: Checks if AssetMapper is available (interface exists + FrameworkBundle config).
     *
     * @param ContainerBuilder $builder Container builder
     * @return bool true if AssetMapper can be used
     */
    private function isAssetMapperAvailable(ContainerBuilder $builder): bool
    {
        if (!interface_exists(AssetMapperInterface::class)) {
            return false;
        }

        // DE: Prüfe ob FrameworkBundle AssetMapper-Config hat
        // EN: Check if FrameworkBundle has AssetMapper config
        $bundlesMetadata = $builder->getParameter('kernel.bundles_metadata');
        if (!\is_array($bundlesMetadata) || !isset($bundlesMetadata['FrameworkBundle'])) {
            return false;
        }

        return is_file($bundlesMetadata['FrameworkBundle']['path'] . '/Resources/config/asset_mapper.php');
    }
}
