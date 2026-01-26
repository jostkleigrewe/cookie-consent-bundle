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
 * Symfony Bundle fuer DSGVO-konforme Cookie-Zustimmung.
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
     * Loads the bundle configuration schema definition.
     *     Defines all available configuration options.
     *
     * @param DefinitionConfigurator $definition Schema configurator
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    /**
     * Loads services and sets parameters based on configuration.
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
        // Set parameters (for usage in services.php or controllers).
        foreach ($config as $key => $value) {
            $container->parameters()->set('cookie_consent.' . $key, $value);
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
     * @param ContainerConfigurator $container Container configurator
     * @param ContainerBuilder $builder Container builder
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Register @CookieConsent Twig namespace
        $container->extension('twig', [
            'paths' => [
                $this->getPath() . '/templates' => 'CookieConsent',
            ],
        ]);

        if (!$builder->hasExtension('framework')) {
            return;
        }

        // Register assets for Symfony AssetMapper (no build step needed)
        $builder->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    $this->getPath() . '/assets/dist' => '@jostkleigrewe/cookie-consent-bundle',
                ],
            ],
        ]);
    }

    /**
     * Registers bundle routes (e.g., POST /_cookie-consent).
     *
     * @param RoutingConfigurator $routes Routing configurator
     */
    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getPath() . '/config/routes.php');
    }
}
