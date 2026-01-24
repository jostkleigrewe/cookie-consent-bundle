<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * CookieConsentBundle - DSGVO/GDPR Cookie Consent Bundle
 *
 * DE: Symfony Bundle fuer DSGVO-konforme Cookie-Zustimmung.
 *     Stellt Modal, Storage-Backends, Event-System und Twig-Integration bereit.
 *     Unterstuetzt Cookie-only, Doctrine-DB oder kombinierte Speicherung.
 *
 * EN: Symfony bundle for GDPR-compliant cookie consent.
 *     Provides modal, storage backends, event system, and Twig integration.
 *     Supports cookie-only, Doctrine DB, or combined storage.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     policy_version: '1.0'
 *     storage: cookie  # oder 'doctrine' oder 'both'
 *     categories:
 *         necessary:
 *             label: 'Notwendig'
 *             required: true
 *         analytics:
 *             label: 'Statistiken'
 *             default: false
 *
 * @see https://github.com/jostkleigrewe/cookie-consent-bundle
 */
final class CookieConsentBundle extends AbstractBundle
{
    /**
     * DE: Laedt die Bundle-Konfigurationsschema-Definition.
     *     Definiert alle verfuegbaren Konfigurationsoptionen.
     *
     * EN: Loads the bundle configuration schema definition.
     *     Defines all available configuration options.
     *
     * @param DefinitionConfigurator $definition DE: Konfigurator fuer das Schema | EN: Schema configurator
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    /**
     * DE: Laedt Services und setzt Parameter basierend auf der Konfiguration.
     *     Prueft ob Doctrine verfuegbar ist, wenn DB-Storage konfiguriert wurde.
     *
     * EN: Loads services and sets parameters based on configuration.
     *     Checks if Doctrine is available when DB storage is configured.
     *
     * @param array<string, mixed> $config DE: Aufgeloeste Bundle-Konfiguration | EN: Resolved bundle configuration
     * @param ContainerConfigurator $container DE: Container-Konfigurator | EN: Container configurator
     * @param ContainerBuilder $builder DE: Container-Builder | EN: Container builder
     *
     * @throws \LogicException DE: Wenn Doctrine fehlt aber DB-Storage konfiguriert ist
     *                         EN: If Doctrine is missing but DB storage is configured
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // DE: Parameter setzen (fuer die Nutzung in services.php oder Controllern).
        // EN: Set parameters (for usage in services.php or controllers).
        foreach ($config as $key => $value) {
            $container->parameters()->set('cookie_consent.' . $key, $value);
        }

        // DE: Service-Definitionen laden
        // EN: Load service definitions
        $loader = new PhpFileLoader($builder, new FileLocator($this->getPath() . '/config'));
        $loader->load('services.php');

        // DE: Pruefe Doctrine-Abhaengigkeit fuer DB-Storage
        // EN: Check Doctrine dependency for DB storage
        $storage = $config['storage'] ?? 'cookie';

        $needsDoctrine = in_array($storage, ['doctrine', 'both'], true);
        if ($needsDoctrine && !class_exists(\Doctrine\DBAL\Connection::class)) {
            throw new \LogicException(sprintf(
                'cookie_consent.storage="%s" requires doctrine/dbal. Install doctrine/dbal or set storage="cookie".',
                $storage
            ));
        }
    }

    /**
     * DE: Registriert Twig-Template-Pfad und AssetMapper-Pfad bei anderen Bundles.
     *     Wird VOR loadExtension ausgefuehrt (prepend).
     *
     * EN: Registers Twig template path and AssetMapper path with other bundles.
     *     Executed BEFORE loadExtension (prepend).
     *
     * @param ContainerConfigurator $container DE: Container-Konfigurator | EN: Container configurator
     * @param ContainerBuilder $builder DE: Container-Builder | EN: Container builder
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // DE: Twig-Namespace @CookieConsent registrieren
        // EN: Register @CookieConsent Twig namespace
        $container->extension('twig', [
            'paths' => [
                $this->getPath() . '/templates' => 'CookieConsent',
            ],
        ]);

        if (!$builder->hasExtension('framework')) {
            return;
        }

        // DE: Assets fuer Symfony AssetMapper registrieren (kein Build-Step noetig)
        // EN: Register assets for Symfony AssetMapper (no build step needed)
        $builder->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    $this->getPath() . '/assets/dist' => '@jostkleigrewe/cookie-consent-bundle',
                ],
            ],
        ]);
    }

    /**
     * DE: Registriert die Bundle-Routen (z.B. POST /_cookie-consent).
     *
     * EN: Registers bundle routes (e.g., POST /_cookie-consent).
     *
     * @param RoutingConfigurator $routes DE: Routing-Konfigurator | EN: Routing configurator
     */
    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getPath() . '/config/routes.php');
    }
}
